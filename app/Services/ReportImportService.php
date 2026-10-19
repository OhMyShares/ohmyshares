<?php

namespace App\Services;

use App\Enums\Currency;
use App\Exceptions\ImportException;
use App\Models\Plugin;
use App\Models\Portfolio;
use App\Models\Trade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OhMyShares\Plugin\Dtos\ImportResult;
use OhMyShares\Plugin\Dtos\ReportFormat;
use OhMyShares\Plugin\Dtos\Trade as TradeDto;
use OhMyShares\Plugin\Exceptions\ReportException;
use OhMyShares\Plugin\ReportImporterInterface;
use Throwable;

/**
 * Imports broker reports via plugins implementing ReportImporterInterface
 */
class ReportImportService
{
    const FORMAT_SEPARATOR = ':';

    public function __construct(private readonly PluginService $plugins)
    {
    }

    /**
     * Report formats of all enabled plugins, id is "<plugin id>:<format id>"
     *
     * @return array<int, array{id: string, title: string, description: ?string, extensions: string[], plugin: string}>
     */
    public function formats(): array
    {
        $list = [];
        foreach ($this->importers() as $plugin) {
            foreach ($this->pluginFormats($plugin) as $format) {
                $list[] = [
                    'id' => $plugin->id.self::FORMAT_SEPARATOR.$format->id,
                    'title' => __($format->title),
                    'description' => $format->description,
                    'extensions' => $format->extensions,
                    'plugin' => $plugin->package,
                ];
            }
        }
        return $list;
    }

    public function hasFormat(string $format): bool
    {
        return in_array($format, array_column($this->formats(), 'id'), true);
    }

    /**
     * Parses the file by the plugin and saves trades into the portfolio
     *
     * @return string[] notices for the user
     * @throws ImportException
     */
    public function import(Portfolio $portfolio, string $format, string $filePath): array
    {
        [$importer, $formatId] = $this->resolve($format);
        try {
            $result = $importer->parseReport($formatId, $filePath);
        } catch (ReportException $e) {
            throw new ImportException($e->getMessage(), $format.': '.$e->getMessage());
        }
        return $this->save($portfolio, $result);
    }

    /**
     * Saves parsed trades in a single transaction, trades already imported (same txn) are skipped
     *
     * @return string[] notices for the user
     * @throws ImportException
     */
    public function save(Portfolio $portfolio, ImportResult $result): array
    {
        $ids = [];
        $duplicates = 0;
        config(['app.trades_ignore_items' => true]);
        try {
            DB::transaction(function () use ($portfolio, $result, &$ids, &$duplicates): void {
                foreach ($result->trades as $index => $dto) {
                    $txn = $dto->txn !== null ? $portfolio->id.self::FORMAT_SEPARATOR.$dto->txn : null;
                    $existingId = $txn !== null ? Trade::where('txn', $txn)->value('id') : null;
                    if ($existingId) {
                        $ids[$dto->txn] = $existingId;
                        $duplicates++;
                        continue;
                    }
                    $relatedId = $dto->relatedTxn !== null ? ($ids[$dto->relatedTxn] ?? null) : null;
                    try {
                        $trade = $this->makeTrade($portfolio, $dto, $txn, $relatedId);
                        $trade->save();
                    } catch (Throwable $e) {
                        throw new ImportException(__('parsers.trade_import_error', ['row' => $index + 1]), $e->getMessage());
                    }
                    if ($dto->txn !== null) {
                        $ids[$dto->txn] = $trade->id;
                    }
                }
                $portfolio->calcItems();
                $portfolio->invalidateCache();
            });
        } finally {
            config(['app.trades_ignore_items' => false]);
        }
        $notices = $result->notices;
        if ($duplicates) {
            $notices[] = __('parsers.duplicates_skipped', ['count' => $duplicates]);
        }
        return $notices;
    }

    private function makeTrade(Portfolio $portfolio, TradeDto $dto, ?string $txn, ?int $relatedId): Trade
    {
        $trade = new Trade([
            'user_id' => $portfolio->user_id,
            'portfolio_id' => $portfolio->id,
            'type' => $dto->type->value,
            'asset' => $dto->assetType->value,
            'ticker' => $dto->ticker,
            'exchange' => $dto->exchange,
            'cost' => $dto->cost,
            'shares' => $dto->shares,
            'sum' => $dto->sum,
            'fee' => $dto->fee,
            'currency_code' => $this->currency($dto->currency),
            'fee_currency_code' => $this->currency($dto->feeCurrency),
            'notes' => $dto->notes !== null ? Str::limit($dto->notes, 255, '') : null,
            'created_at' => $dto->timestamp,
            'txn' => $txn,
            'related_trade_id' => $relatedId,
        ]);
        $trade->is_during_import = true;
        return $trade;
    }

    private function currency(?string $code): ?string
    {
        return $code !== null ? Currency::findCode($code) : null;
    }

    /**
     * @return array{0: ReportImporterInterface, 1: string}
     * @throws ImportException
     */
    private function resolve(string $format): array
    {
        [$pluginId, $formatId] = array_pad(explode(self::FORMAT_SEPARATOR, $format, 2), 2, '');
        foreach ($this->importers() as $plugin) {
            if ((string)$plugin->id !== $pluginId) {
                continue;
            }
            $importer = $plugin->getPlugin();
            $ids = array_map(fn(ReportFormat $f): string => $f->id, $this->pluginFormats($plugin));
            if ($importer instanceof ReportImporterInterface && in_array($formatId, $ids, true)) {
                return [$importer, $formatId];
            }
        }
        throw new ImportException(__('parsers.incorrect_parser'), 'Unknown report format '.$format);
    }

    /**
     * @return Collection<int, Plugin>
     */
    private function importers(): Collection
    {
        return $this->plugins->enabled()
            ->filter(fn(Plugin $plugin): bool => $plugin->getPlugin() instanceof ReportImporterInterface);
    }

    /**
     * @return ReportFormat[]
     */
    private function pluginFormats(Plugin $plugin): array
    {
        $importer = $plugin->getPlugin();
        if (!$importer instanceof ReportImporterInterface) {
            return [];
        }
        try {
            return array_values(array_filter($importer->reportFormats(), fn($f): bool => $f instanceof ReportFormat));
        } catch (Throwable $e) {
            Log::error('Plugin '.$plugin->package.' reportFormats failed: '.$e->getMessage());
            return [];
        }
    }
}

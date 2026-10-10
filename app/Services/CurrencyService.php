<?php
namespace App\Services;

use App\Models\CurrencyRate;
use App\Models\Plugin;
use DateTimeInterface;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OhMyShares\Plugin\CurrencyRatesProviderInterface;
use OhMyShares\Plugin\Dtos\CurrencyRate as CurrencyRateDto;
use Throwable;

/**
 * Converts amounts between currencies using locally stored rates,
 * rates are supplied by plugins implementing CurrencyRatesProviderInterface
 */
class CurrencyService
{
    const CACHE_KEY = 'currency_rates';

    const CACHE_TTL = 600;

    const CODE_PATTERN = '/^[A-Z0-9]{2,10}$/';

    const DATE_PATTERN = '/^\d{4}-\d{2}-\d{2}$/';

    public function __construct(private readonly PluginService $plugins)
    {
    }

    /**
     * Converts the amount, returns 0 if there is no rate between currencies
     */
    public function convert(float|int|string|null $val, string $from, string $to): float
    {
        $rate = $this->rate($from, $to);
        return $rate === null ? 0.0 : (float)$val * $rate;
    }

    /**
     * Price of 1 unit of $from expressed in $to, null if currencies are not linked by rates
     */
    public function rate(string $from, string $to): ?float
    {
        $from = mb_strtoupper($from);
        $to = mb_strtoupper($to);
        if ($from === $to) {
            return 1.0;
        }
        $graph = $this->graph();
        $factors = [$from => 1.0];
        $queue = [$from];
        while ($queue) {
            $code = array_shift($queue);
            foreach ($graph[$code] ?? [] as $next => $factor) {
                if (isset($factors[$next])) {
                    continue;
                }
                $factors[$next] = $factors[$code] * $factor;
                if ($next === $to) {
                    return $factors[$next];
                }
                $queue[] = $next;
            }
        }
        return null;
    }

    /**
     * Codes of currencies having at least one rate
     *
     * @return string[]
     */
    public function currencies(): array
    {
        return array_keys($this->graph());
    }

    /**
     * Latest stored rates: [base => [currency => price of 1 currency unit in base]]
     *
     * @return array<string, array<string, float>>
     */
    public function getRates(): array
    {
        return Cache::memo()->remember(self::CACHE_KEY, self::CACHE_TTL, function (): array {
            $latest = CurrencyRate::query()
                ->select('base', 'currency')
                ->selectRaw('MAX(date) as date')
                ->groupBy('base', 'currency');
            $models = CurrencyRate::query()
                ->joinSub($latest, 'latest', fn(JoinClause $join): JoinClause => $join
                    ->on('currency_rates.base', '=', 'latest.base')
                    ->on('currency_rates.currency', '=', 'latest.currency')
                    ->on('currency_rates.date', '=', 'latest.date'))
                ->get(['currency_rates.base', 'currency_rates.currency', 'currency_rates.rate']);
            $rates = [];
            foreach ($models as $model) {
                $rates[$model->base][$model->currency] = $model->rate;
            }
            return $rates;
        });
    }

    /**
     * Requests rates from all enabled provider plugins and stores them
     *
     * @return int Number of stored rates
     */
    public function update(?DateTimeInterface $date = null): int
    {
        $date ??= now();
        $count = 0;
        foreach ($this->providers() as $plugin) {
            try {
                $count += $this->store($plugin->getPlugin()->currencyRates($date), $plugin->package);
            } catch (Throwable $e) {
                Log::error('Plugin '.$plugin->package.' currencyRates failed: '.$e->getMessage());
            }
        }
        return $count;
    }

    /**
     * @param CurrencyRateDto[] $rates
     * @return int Number of stored rates
     */
    public function store(array $rates, ?string $source = null): int
    {
        $now = now();
        $rows = [];
        foreach ($rates as $rate) {
            if (!$rate instanceof CurrencyRateDto) {
                continue;
            }
            $base = mb_strtoupper(trim($rate->base));
            $currency = mb_strtoupper(trim($rate->currency));
            if ($base === $currency
                || !is_finite($rate->rate) || $rate->rate <= 0
                || !preg_match(self::CODE_PATTERN, $base)
                || !preg_match(self::CODE_PATTERN, $currency)
                || !preg_match(self::DATE_PATTERN, $rate->date)) {
                continue;
            }
            $rows[$base.'/'.$currency.'/'.$rate->date] = [
                'base' => $base,
                'currency' => $currency,
                'date' => $rate->date,
                'rate' => $rate->rate,
                'source' => $source,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if ($rows) {
            CurrencyRate::upsert(array_values($rows), ['base', 'currency', 'date'], ['rate', 'source', 'updated_at']);
            Cache::memo()->forget(self::CACHE_KEY);
        }
        return count($rows);
    }

    /**
     * Enabled plugins implementing CurrencyRatesProviderInterface
     *
     * @return Collection<int, Plugin>
     */
    public function providers(): Collection
    {
        return $this->plugins->enabled()
            ->filter(fn(Plugin $plugin): bool => $plugin->getPlugin() instanceof CurrencyRatesProviderInterface);
    }

    /**
     * Conversion factors between directly linked currencies: [from => [to => factor]]
     *
     * @return array<string, array<string, float>>
     */
    private function graph(): array
    {
        $graph = [];
        foreach ($this->getRates() as $base => $rates) {
            foreach ($rates as $currency => $rate) {
                if ($rate > 0) {
                    $graph[$currency][$base] = $rate;
                    $graph[$base][$currency] = 1 / $rate;
                }
            }
        }
        return $graph;
    }
}

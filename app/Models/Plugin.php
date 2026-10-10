<?php

namespace App\Models;

use App\Enums\Currency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use DateTimeInterface;
use OhMyShares\Plugin\Dtos\History as HistoryDto;
use OhMyShares\Plugin\Enums\AssetType;
use OhMyShares\Plugin\Dtos\Ticker as TickerDto;
use OhMyShares\Plugin\HostInterface;
use OhMyShares\Plugin\PluginInterface;
use Throwable;

/**
 * @property int $id
 * @property string $package
 * @property string $version
 * @property string $title
 * @property string $remote
 * @property string $source
 * @property bool $is_enabled
 * @property array|null $config
 *
 * @method static Builder|Plugin enabled()
 */
class Plugin extends Model implements HostInterface
{
    use HasFactory;

    protected $table = 'plugins';

    protected $casts = [
        'config' => 'array',
        'is_enabled' => 'boolean',
    ];

    protected $fillable = [
        'remote',
    ];

    protected $appends = [
        'config_schema',
        'description',
    ];

    private ?PluginInterface $_plugin = null;

    private ?string $_loadError = null;

    private ?array $_manifest = null;

    private static bool $_sdkPreloaded = false;

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public static function readComposer(string $path): array
    {
        $file = rtrim($path, '/').'/composer.json';
        if (!File::exists($file)) {
            return [];
        }
        $data = json_decode(File::get($file), true);
        return is_array($data) ? $data : [];
    }

    public static function resolveClassName(array $composer): ?string
    {
        $class = Arr::get($composer, 'extra.ohmyshares.class');
        if ($class) {
            return ltrim($class, '\\');
        }
        $psr4 = Arr::get($composer, 'autoload.psr-4', []);
        $namespace = Arr::first(array_keys($psr4));
        return $namespace ? $namespace.'Plugin' : null;
    }

    public function manifest(): array
    {
        $this->_manifest ??= $this->source ? self::readComposer($this->source) : [];
        return $this->_manifest;
    }

    public function getConfigSchemaAttribute(): array
    {
        return Arr::get($this->manifest(), 'extra.ohmyshares.config', []);
    }

    public function getDescriptionAttribute(): ?string
    {
        return Arr::get($this->manifest(), 'description');
    }

    public function getLoadError(): ?string
    {
        return $this->_loadError;
    }

    /**
     * Loads all host SDK classes, so SDK copies bundled in plugins' vendor directories
     * (their autoloaders are prepended) never shadow the host version.
     */
    public static function preloadSdk(): void
    {
        if (self::$_sdkPreloaded) {
            return;
        }
        self::$_sdkPreloaded = true;
        $namespace = substr(PluginInterface::class, 0, strrpos(PluginInterface::class, '\\') + 1);
        $dir = dirname((new \ReflectionClass(PluginInterface::class))->getFileName());
        foreach (File::allFiles($dir) as $file) {
            if ($file->getExtension() === 'php') {
                class_exists($namespace.str_replace('/', '\\', substr($file->getRelativePathname(), 0, -4)));
            }
        }
    }

    public function loadPlugin(): bool
    {
        $this->_plugin = null;
        $this->_loadError = null;
        $this->_manifest = null;
        try {
            self::preloadSdk();
            $autoload = $this->source.'/vendor/autoload.php';
            if (!File::exists($autoload)) {
                throw new \RuntimeException('vendor/autoload.php not found, run composer install');
            }
            require_once $autoload;
            $className = self::resolveClassName($this->manifest());
            if (!$className || !class_exists($className)) {
                throw new \RuntimeException('Plugin class '.($className ?: '<undefined>').' not found');
            }
            $plugin = new $className;
            if (!$plugin instanceof PluginInterface) {
                throw new \RuntimeException($className.' must implement '.PluginInterface::class);
            }
            $plugin->setHost($this);
            $plugin->afterLoad();
            $this->_plugin = $plugin;
            return true;
        } catch (Throwable $e) {
            $this->_loadError = $e->getMessage();
            Log::error('Plugin '.($this->package ?: $this->source).' failed to load: '.$e->getMessage());
        }
        return false;
    }

    public function getPlugin(): ?PluginInterface
    {
        if (!$this->_plugin && !$this->_loadError) {
            $this->loadPlugin();
        }
        return $this->_plugin;
    }

    public function getConfig($key): mixed
    {
        return Arr::get($this->config, $key);
    }

    public function getTicker(string $ticker, ?string $exchange = null): ?TickerDto
    {
        $model = Ticker::findModel($ticker, $exchange)
            ?? Ticker::where('isin', $ticker)
                ->when($exchange !== null, fn(Builder $query): Builder => $query->where('exchange', $exchange))
                ->orderBy('exchange')
                ->first();
        return $model?->getDto();
    }

    public function getHistory(string $ticker, AssetType $assetType,
                               ?DateTimeInterface $dateStart = null,
                               ?DateTimeInterface $dateEnd = null,
                               ?string $exchange = null): array
    {
        $query = History::where('ticker', $ticker)->where('asset', $assetType->value)->orderBy('date');
        if ($exchange !== null) {
            $query->where('exchange', $exchange);
        }
        if ($dateStart) {
            $query->where('date', '>=', $dateStart->format('Y-m-d'));
        }
        if ($dateEnd) {
            $query->where('date', '<=', $dateEnd->format('Y-m-d'));
        }
        return $query->get()->map(fn(History $model): \OhMyShares\Plugin\Dtos\History => $this->historyDto($model))->all();
    }

    public function log(string $message, string $level = 'info'): void
    {
        if (!in_array($level, ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'])) {
            $level = 'info';
        }
        Log::log($level, 'Plugin '.$this->package.': '.$message);
    }

    private function historyDto(History $model): HistoryDto
    {
        $dto = new HistoryDto;
        $dto->ticker = $model->ticker;
        if (property_exists($dto, 'exchange')) {
            $dto->exchange = $model->exchange ?? '';
        }
        $dto->date = $model->date;
        $dto->assetType = AssetType::from($model->asset);
        $dto->open = $model->open;
        $dto->close = $model->close;
        $dto->high = $model->high;
        $dto->low = $model->low;
        $dto->volume = $model->volume;
        $dto->dividends = $model->dividends;
        return $dto;
    }

    public function addTicker(TickerDto $ticker): bool
    {
        $model = Ticker::where('ticker', mb_strtoupper($ticker->ticker))
            ->where('exchange', $ticker->exchange ?? '')
            ->first();
        if (!$model) {
            $model = new Ticker;
            $model->ticker = $ticker->ticker;
            $model->exchange = $ticker->exchange;
        }
        $model->name = $ticker->name;
        $model->isin = $ticker->isin;
        $model->exchange = $ticker->exchange;
        $model->currency_code = Currency::findCode($ticker->currency);
        $model->type = $ticker->type->value;
        return $model->save();
    }

    public function addHistory(HistoryDto $history): bool
    {
        $exchange = property_exists($history, 'exchange') ? $history->exchange : null;
        $exchange ??= Ticker::findModel($history->ticker)?->exchange ?? '';
        /** @var History $model */
        $model = History::firstOrCreate([
            'ticker' => $history->ticker,
            'exchange' => $exchange,
            'asset' => $history->assetType->value,
            'date' => $history->date,
        ]);
        if ($history->open) {
            $model->open = $history->open;
        }
        if ($history->close) {
            $model->close = $history->close;
        }
        if ($history->high) {
            $model->high = $history->high;
        }
        if ($history->low) {
            $model->low = $history->low;
        }
        if ($history->dividends) {
            $model->dividends = $history->dividends;
        }
        return $model->save();
    }
}

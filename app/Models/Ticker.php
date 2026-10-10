<?php

namespace App\Models;

use App\Enums\Currency;
use App\Models\Interfaces\TickerInfoInterface;
use App\Models\Traits\HistoryTrait;
use App\Services\PluginService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use OhMyShares\Plugin\Dtos\Ticker as TickerDto;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OhMyShares\Plugin\Enums\AssetType;
use OhMyShares\Plugin\Enums\TickerType;

/**
 * @property string $ticker
 * @property string $name
 * @property string $isin
 * @property string $exchange
 * @property string $category
 * @property string $country
 * @property int $sector_id
 * @property string $currency_code
 * @property int $type
 * @property float $nominal
 * @property float $coupon_percent
 * @property bool $is_hidden
 * @property string $info_url
 * @property string $disclosure_url
 *
 * @property-read string $typeLabel
 * @property-read string $currencyCode
 * @property-read string $currencySign
 */
class Ticker extends Model implements TickerInfoInterface
{
    use HistoryTrait;

    protected $_last_history;

    protected $primaryKey = 'ticker';
    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = true;

    protected $appends = [
        'name_short', 'type_label',
        'currency_sign'
    ];

    protected $fillable = [
        'ticker', 'exchange', 'name', 'isin', 'type', 'currency_code',
    ];

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('ticker', $this->getAttribute('ticker'))
            ->where('exchange', $this->getAttribute('exchange') ?? '');
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Ticker $model): void {
            $model->beforeCreateOrUpdate();
        });

        static::updating(function (Ticker $model): void {
            $model->beforeCreateOrUpdate();
        });
    }

    public function beforeCreateOrUpdate(): void
    {
        $this->ticker = mb_strtoupper($this->ticker);
        $this->exchange ??= '';
    }

    /**
     * Find by company name only
     */
    public static function findByName($text): ?string
    {
        $model = self::where('name', 'like', $text . '%')
            ->orderBy(DB::raw('length(ticker)'))
            ->first();
        if ($model) {
            return (string)$model->ticker;
        }
        return null;
    }

    /**
     * Find ticker symbol by ISIN
     */
    public static function findByIsin($isin): ?string
    {
        $model = self::where('isin', $isin)->first();
        if ($model) {
            return (string)$model->ticker;
        }
        return null;
    }

    /**
     * Find by exact ticker symbol and optional exchange.
     * Without exchange returns a single (any) match for the symbol.
     */
    public static function findModel($ticker, ?string $exchange = null): ?Ticker
    {
        $ticker = mb_strtoupper($ticker);
        $key = 'ticker_'.$ticker.'_'.($exchange ?? '');
        return Cache::remember($key, 60, function () use ($ticker, $exchange) {
            $query = self::where('ticker', $ticker);
            if ($exchange !== null) {
                $query->where('exchange', $exchange);
            }
            return $query->orderBy('exchange')->first();
        });
    }

    /**
     * Fuzzy lookup by ticker prefix or ISIN for user-typed queries
     */
    public static function findFuzzy($ticker): ?Ticker
    {
        return self::where('ticker', 'like', $ticker.'%')
            //->orWhere('name', 'like', '%'.$ticker.'%')
            ->orWhere('isin', 'like', '%'.$ticker.'%')
            ->orderBy(DB::raw('length(ticker)'))
            ->orderBy('exchange')
            ->first();
    }

    public static function getSuggestions($text): Collection|array
    {
        /** @var Builder $q */
        $q = self::where('ticker', 'like', $text.'%')
            ->orWhere('name', 'like', '%'.$text.'%')
            ->orWhere('isin', 'like', '%'.$text.'%');
        if (mb_strlen($text) > 4) {
            $q->orWhereRaw('MATCH(`ticker`, `name`, `isin`) AGAINST (? IN BOOLEAN MODE)', [$text.'*']);
        }
        return $q->limit(10)->get();
    }

    public function getHistoryQuery(): Builder
    {
        return History::query()
            ->where('asset', AssetType::Shares->value)
            ->where('exchange', $this->exchange ?? '')
            ->where(function ($q): void {
                $q->where('ticker', $this->ticker)
                ->orWhere('ticker', 'like', $this->ticker.'.%');
            });
    }

    /**
     * Get history data from finance-data-getter
     */
    public function updateHistory(): bool
    {
        if ($this->type != TickerType::Stock->value) {
            return false;
        }
        if (!$this->isLastRequestExpired()) {
            return false;
        }
        $end = Carbon::today()->addDay();
        $last_history = $this->lastHistory();
        if ($last_history && $last_history->date) {
            $start = Carbon::createFromFormat("Y-m-d", $last_history->date);
        } else {
            $start = Carbon::now()->subMonth()->startOfMonth();
        }
        $first_trade_date = $this->firstTradeDate();
        if ($first_trade_date && $first_trade_date->lt($start) && $this->isHistoryMissingSince($first_trade_date)) {
            $start = $first_trade_date;
        }
        app(PluginService::class)->onHistoryRequest($this->getDto(), $start, $end);
        $this->setLastRequest();
        return true;
    }

    public function firstTradeDate(): ?Carbon
    {
        $timestamp = Trade::where('ticker', $this->ticker)
            ->whereIn('exchange', array_unique([$this->exchange ?? '', '']))
            ->whereIn('type', [Trade::TYPE_BUY, Trade::TYPE_SELL])
            ->min('created_at');
        return $timestamp ? Carbon::createFromTimestamp($timestamp)->startOfDay() : null;
    }

    public function isHistoryMissingSinceFirstTrade(): bool
    {
        $date = $this->firstTradeDate();
        return $date !== null && $this->isHistoryMissingSince($date);
    }

    private function isHistoryMissingSince(Carbon $date): bool
    {
        $first_date = $this->getHistoryQuery()->whereNotNull('close')->min('date');
        if (!$first_date) {
            return true;
        }
        return Carbon::createFromFormat('Y-m-d', $first_date)->startOfDay()->gt($date->copy()->addWeek());
    }

    public function getDto(): TickerDto
    {
        $dto = new TickerDto;
        $dto->ticker = $this->ticker;
        $dto->type = TickerType::from($this->type);
        $dto->isin = $this->isin;
        $dto->country = $this->country;
        $dto->currency = $this->currency_code ?? '';
        $dto->exchange = $this->exchange ?? '';
        $dto->name = $this->name ?? '';
        $dto->nominal = $this->nominal;
        $dto->coupon_percent = $this->coupon_percent;
        $dto->disclosure_url = $this->disclosure_url;
        $dto->info_url = $this->info_url;
        return $dto;
    }

    public static function typeLabels(): array
    {
        return [
            TickerType::Stock->value => __('Stock'),
            TickerType::ETF->value => __('ETF'),
            TickerType::Future->value => __('Futures'),
            TickerType::Currency->value => __('Currency'),
            TickerType::Index->value => __('Index'),
            TickerType::MutualFond->value => __('Mutual Fond'),
            TickerType::Bond->value => __('Bond'),
            TickerType::Crypto->value => __('Cryptocurrency'),
        ];
    }

    public function getTypeLabelAttribute()
    {
        return Arr::get(self::typeLabels(), $this->type);
    }

    public static function abbreviationsList(): array
    {
        return [
            'corporation' => 'Corp.',
            'limited' => 'Ltd.',
            'общество с ограниченной ответственностью' => 'ООО',
            'открытое акционерное общество' => 'ОАО',
            'публичное акционерное общество' => 'ПАО',
            'акционерное общество' => 'АО',
            'российской федерации' => 'РФ',
            'министерство финансов' => 'Мин. фин.',
            'управляющая компания' => 'УК',
        ];
    }

    /**
     * Shorted company name without long abbreviations
     */
    public function getNameShortAttribute(): string
    {
        $name = $this->name;
        foreach (self::abbreviationsList() as $long => $short) {
            $name = preg_replace('/'.$long.'/ui', $short, $name);
        }
        return $name;
    }

    public function getAssetAttribute(): int
    {
        return AssetType::Shares->value;
    }

    public function getCurrencySignAttribute(): ?string
    {
        return Currency::signOf($this->currency_code);
    }

    public function getUpdatedAtColumn()
    {
        return null;
    }

    public function sector()
    {
        return $this->hasOne(Sector::class, 'id', 'sector_id');
    }

    public function dividends()
    {
        return $this->getHistoryQuery()
            ->select(['date', 'dividends'])
            ->where('dividends', '<>', 0)
            ->orderBy('date')
            ->get();
    }
}

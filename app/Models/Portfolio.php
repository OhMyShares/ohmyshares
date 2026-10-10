<?php

namespace App\Models;

use App\Analytics\Analytics;
use App\Components\Uid;
use App\Enums\Currency;
use DateTimeInterface;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

/**
 * @property int $id
 * @property int $user_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $opened_at
 * @property string $currency_code
 * @property int $account_type
 * @property string $name
 * @property float $fee_percent
 * @property float $goal
 * @property string $notes
 * @property User $user
 * @property PortfolioItem[] $items
 */
#[OA\Schema(title: 'Portfolio', description: 'Portfolio model')]
class Portfolio extends Model
{
    use HasFactory;

    public const TYPE_BROKER = 1;

    /**
     * @var Analytics
     */
    public $analytics;

    protected $fillable = [
        'name',
        'currency_code',
        'account_type',
        'user_id',
        'opened_at',
        'fee_percent',
        'goal',
        'notes',
    ];

    protected $hidden = [
        //'opened_at',
        'id',
    ];

    protected $dateFormat = 'U';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'opened_at' => 'datetime',
        'goal' => 'float',
    ];

    protected $appends = [
        'currency_sign',
        'uid',
        'is_locked',
        'last_import_status',
        'last_import_info',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat);
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            $model->opened_at ??= date('Y-m-d');
        });

        static::updated(function (self $model): void {
            $model->invalidateCache();
        });

        self::deleting(function (self $model): bool {
            $model->invalidateCache();
            return $model->beforeDelete();
        });
    }

    public function beforeDelete(): bool
    {
        DB::beginTransaction();
        try {
            Trade::where('portfolio_id', $this->id)->delete(); // this not firing events
        } catch (Exception) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    public function invalidateCache(): void
    {
        Cache::forget('sharesCostTotal_' . $this->id);
        Cache::forget('portfolio_' . $this->id . '_growth');
        Cache::forget('portfolio_' . $this->id . '_income_by_years');
        Cache::forget('portfolios_index_' . $this->user_id);
        foreach ($this->items as $item) {
            $item->invalidateCache();
        }
    }

    public function getUidAttribute(): string
    {
        return Uid::encode($this->id);
    }

    public static function findByUid($uid): bool
    {
        $id = Uid::decode($uid);
        if ($id) {
            return Portfolio::find($id);
        }
        return false;
    }

    public function setFeePercentAttribute($value): void
    {
        if (is_null($value)) {
            $this->attributes['fee_percent'] = 0;
            return;
        }
        $this->attributes['fee_percent'] = $value;
    }

    public function setOpenedAtAttribute($value): void
    {
        if (is_null($value)) {
            $this->attributes['opened_at'] = date('Y-m-d');
            return;
        }
        $this->attributes['opened_at'] = $value;
    }

    public static function accountTypes(): array
    {
        return [
            self::TYPE_BROKER => __('Broker'),
        ];
    }

    public function accountType()
    {
        return Arr::get(self::accountTypes(), $this->account_type);
    }

    public function currencySign(): ?string
    {
        return Currency::signOf($this->currency_code);
    }

    public function getCurrencySignAttribute(): ?string
    {
        return $this->currencySign();
    }


    public function getIsLockedAttribute(): bool
    {
        $lock = Cache::lock('import_report_portfolio_' . $this->id);
        if ($lock->get()) {
            $lock->release();
            return false;
        }
        return true;
    }

    public function getLastImportStatusAttribute()
    {
        return Cache::get('import_report_status_' . $this->id);
    }

    public function getLastImportInfoAttribute()
    {
        return Cache::get('import_report_notices_' . $this->id);
    }

    /**
     * Summary cost of all items
     * Slow method (do not use in cycles)
     */
    public function sharesCostTotal(): int
    {
        return Cache::remember('sharesCostTotal_' . $this->id, 60, function (): int|float {
            $total = 0;
            foreach ($this->items as $item) {
                if ($item->ticker == Trade::TICKER_MONEY || $item->count != 0) {
                    $total += $item->currentCost();
                }
            }
            return $total;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trades()
    {
        return $this->hasMany(Trade::class);
    }

    public function items()
    {
        return $this->hasMany(PortfolioItem::class)
            ->orderBy('asset', 'desc')
            ->orderBy('count', 'desc')
            ->orderBy('ticker', 'asc');
    }

    public function findItem($ticker, $asset, $currency_code, ?string $exchange = null)
    {
        $query = PortfolioItem::query()
            ->where('portfolio_id', $this->id)
            ->where('ticker', $ticker)
            ->where('asset', $asset)
            ->where('currency_code', $currency_code);
        if ($exchange !== null) {
            $query->where('exchange', $exchange);
        }
        return $query->first();
    }

    public function calcItems(): void
    {
        foreach ($this->trades as $trade) {
            $trade->updatePortfolioItem();
        }
        foreach ($this->items as $item) {
            $item->calc();
            if ($item->trades == 0) {
                $item->delete();
            } else {
                $item->save();
            }
        }
    }

    public function content(): array
    {
        $content = $this->items;
        $summary = [
            'total_cost' => 0,
            'total_gain' => 0,
            'total_gain_percent' => 0,
        ];

        // calc commons
        foreach ($this->items as $key => $item) {
            //$content[$key]['current_price'] = round($item->currentPrice(), 2);
            //$content[$key]['current_cost'] = round($item->currentCost(), 2);
            //$content[$key]['current_gain'] = round($item->currentGain(), 2);
            //$content[$key]['current_gain_percent'] = $item->currentGainPercent();
            //$content[$key]['part_percent'] = 0; //$item->partPercent($this);
            $ticker_info = $item->tickerInfo();
            if ($ticker_info) {
                $content[$key]['title'] = $ticker_info->nameShort;
            }

            $summary['total_cost'] += $content[$key]['current_cost'];
            $summary['total_gain'] += $content[$key]['current_gain'];
            $summary['total_gain_percent'] += $content[$key]['current_gain_percent'];
        }

        // calc part_percent
        foreach ($content as $key => $item) {
            $total_cost = $summary['total_cost'];
            $current_cost = $content[$key]['current_cost'];
            if (
                $total_cost != 0
                && $current_cost > 0
            ) {
                //if ($item->currency_code == $this->currency_code) {
                $content[$key]['part_percent'] = round($current_cost / $total_cost * 100, 2);
                //}
            }
        }

        $summary['total_cost'] = round(@$summary['total_cost'], 2);
        $summary['total_gain'] = round(@$summary['total_gain'], 2);
        $summary['total_gain_percent'] = 0;
        if ($summary['total_cost'] != 0) {
            $summary['total_gain_percent'] = round($summary['total_gain'] / $summary['total_cost'] * 100, 2);
        }

        return [
            'content' => $content,
            'summary' => $summary,
            'analytics' => $this->calcAnalytics($content),
        ];
    }

    public function calcAnalytics($content): array
    {
        $analytics = new Analytics($this, $content);
        return [
            'asset_types' => $analytics->byAssetTypes(),
            'sectors' => $analytics->bySectors(),
            'earnings' => $analytics->byEarnings(),
            'currencies' => $analytics->byCurrencies(),
            'summary' => $analytics->summary(),
            //'profit_by_years' => $analytics->byProfitByYears(),
            'total_earnings' => $analytics->totalEarnings(),
            //'total_taxes' => $analytics->totalTaxes(),
        ];
    }

    public function calcGrowth()
    {
        $analytics = new Analytics($this);
        return $analytics->growth();
    }

    public function calcIncomeByYears(): array
    {
        $analytics = new Analytics($this);
        return $analytics->incomeByYears();
    }

    public function calcGoalAchievementPercent(): float|int
    {
        // $growth = $analytics->growth();
        // $last_month = array_pop($growth);
        // $total = Arr::get($last_month, 'cumulative', 0);
        if ($this->goal) {
            return round($this->sharesCostTotal() / $this->goal * 100);
        }
        return 0;
    }

    public function availableYears(): array
    {
        $years = DB::table('trades')
            ->select(DB::raw('YEAR(FROM_UNIXTIME(created_at)) as year'))
            ->distinct()
            ->where('portfolio_id', $this->id)
            ->get()
            ->toArray();
        return Arr::pluck($years, 'year');
    }

    public function clear(): void
    {
        Trade::where('portfolio_id', $this->id)->delete();
        PortfolioItem::where('portfolio_id', $this->id)->delete();
    }

    public function duplicate(): ?self
    {
        config(['app.trades_ignore_funds' => true]);
        config(['app.trades_ignore_events' => true]);

        $new = false;
        DB::beginTransaction();
        try {
            $new = $this->replicate()->fill([
                'name' => $this->name . ' (' . __('Copy') . ')',
            ]);
            $new->save();
            foreach ($this->trades as $trade) {
                $new->trades()->save($trade->replicate()->fill([
                    'created_at' => $trade->created_at
                ]));
            }
            $new->calcItems();
        } catch (Exception $e) {
            Log::error('Error while portfolio cloning: ' . $e->getMessage());
            DB::rollBack();
            return null;
        } finally {
            config(['app.trades_ignore_funds' => false]);
            config(['app.trades_ignore_events' => false]);
        }

        DB::commit();

        return $new;
    }
}

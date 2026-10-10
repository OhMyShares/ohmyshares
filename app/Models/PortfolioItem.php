<?php

namespace App\Models;

use App\Components\Uid;
use App\Enums\Currency;
use App\Jobs\GetHistory;
use App\Services\CurrencyService;
use DateTimeInterface;
use Error;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OhMyShares\Plugin\Enums\AssetType;

/**
 * @property int $id
 * @property int $asset
 * @property int $portfolio_id
 * @property string $ticker
 * @property string $exchange
 * @property float $current_cost
 * @property float $current_cost_native
 * @property string $currency_code
 * @property int $count
 * @property float $sum
 * @property float $sum_converted
 * @property float $average_price
 * @property float $earned_dividends
 * @property float $earned_coupons
 * @property float $paid_fees
 * @property float $paid_taxes
 * @property float $realized_gain
 * @property Portfolio|null $portfolio
 * @property Trade[]|null $trades
 */
class PortfolioItem extends Model
{
    protected $fillable = [
        'portfolio_id',
        'ticker',
        'exchange',
        'asset',
        'count',
        'average_price',
        'currency_code',
    ];

    //public $timestamps = false;
    protected $dateFormat = 'U';

    protected $hidden = [
        'id', 'created_at', 'updated_at',
        'portfolio_id',
        'portfolio',
    ];

    protected $casts = [
        'sum' => 'float',
        'average_price' => 'float',
        'earned_dividends' => 'float',
        'earned_coupons' => 'float',
        'paid_fees' => 'float',
        'paid_taxes' => 'float',
        'realized_gain' => 'float',
    ];

    protected $appends = [
        'currency_sign',
        //'sum_converted',
        'current_price',
        'current_cost',
        'current_cost_native',
        'current_gain',
        'current_gain_native',
        'current_gain_percent',
        'earnings',
        'earnings_native',
        'uid',
        'part_percent'
    ];

    protected $_price;
    protected $_current_price;

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format($this->dateFormat);
    }

    public static function boot(): void
    {
        parent::boot();
        self::deleting(fn($model) => $model->beforeDelete());
    }

    public function beforeDelete(): bool
    {
        DB::beginTransaction();
        try {
            Trade::where('portfolio_id', $this->portfolio_id)
                ->where('ticker', $this->ticker)
                ->where('exchange', $this->exchange ?? '')
                ->where('asset', $this->asset)
                ->get()
                ->each(function($trade): void {
                    $trade->delete();
                });
        } catch (Exception) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    public function changeTicker(string $ticker, ?string $exchange = null): self
    {
        $ticker = mb_strtoupper(trim($ticker));
        $exchange ??= $this->exchange ?? '';
        if ($ticker === $this->ticker && $exchange === ($this->exchange ?? '')) {
            return $this;
        }

        return DB::transaction(function () use ($ticker, $exchange): self {
            Trade::where('portfolio_id', $this->portfolio_id)
                ->where('ticker', $this->ticker)
                ->where('exchange', $this->exchange ?? '')
                ->where('currency_code', $this->currency_code)
                ->update(['ticker' => $ticker, 'exchange' => $exchange]);

            $this->invalidateCache();
            self::whereKey($this->id)->delete();

            $item = self::firstOrNew([
                'portfolio_id' => $this->portfolio_id,
                'ticker' => $ticker,
                'exchange' => $exchange,
                'currency_code' => $this->currency_code,
            ]);
            $item->calc();
            $item->save();

            $this->portfolio?->invalidateCache();

            return $item;
        });
    }

    public function invalidateCache(): void
    {
        Cache::forget('currentCost_'.$this->id);
        Cache::forget('sumConverted_'.$this->id);
        Cache::forget('portfolioItem_portfolio_'.$this->portfolio_id);
    }

    public function calc(): void
    {
        $row = Trade::select([
            DB::raw('ROUND(AVG(asset)) as asset'),
            DB::raw('COUNT(id) as trades'),
        ])
            ->whereNotIn('type', [Trade::TYPE_DIVIDEND, Trade::TYPE_COUPON])
            ->where('portfolio_id', $this->portfolio_id)
            ->where('ticker', $this->ticker)
            ->where('exchange', $this->exchange ?? '')
            ->where('currency_code', $this->currency_code)
            ->first();

        $this->asset = (int)$row->asset;
        $this->trades = (int)$row->trades;
        $this->calcValues();
    }

    /**
     * Calculating for item:
     * count, sum (net invested incl. fees), average_price (weighted average of open
     * position), realized_gain, earned_dividends, earned_coupons, paid_fees, paid_taxes
     */
    public function calcValues(): void
    {
        $qty = 0.0;
        $basis = 0.0;
        $realized = 0.0;
        $net_invested = 0.0;
        $cumulative_dividends = 0;
        $cumulative_coupons = 0;
        $cumulative_fees = 0;
        $cumulative_taxes = 0;
        $trades = Trade::select(['type', 'shares', 'sum', 'fee', 'fee_currency_code'])
            ->where('portfolio_id', $this->portfolio_id)
            ->where('ticker', $this->ticker)
            ->where('exchange', $this->exchange ?? '')
            ->where('currency_code', $this->currency_code)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
        foreach ($trades as $trade) {
            $fee = $trade->type == Trade::TYPE_BUY || $trade->type == Trade::TYPE_SELL
                ? ($trade->fee_currency_code == $this->currency_code ? (float)$trade->fee : 0)
                : 0;
            if ($trade->type == Trade::TYPE_BUY) {
                $shares = (float)$trade->shares;
                $price = $shares != 0 ? ($trade->sum + $fee) / $shares : 0;
                if ($qty < 0 && $shares > 0) {
                    $closed = min($shares, -$qty);
                    $short_avg = $basis / $qty;
                    $realized += ($short_avg - $price) * $closed;
                    $basis += $short_avg * $closed;
                    $qty += $closed;
                    $shares -= $closed;
                }
                $basis += $price * $shares;
                $qty += $shares;
                $net_invested += $trade->sum + $fee;
                $cumulative_fees += $fee;
            }
            if ($trade->type == Trade::TYPE_SELL) {
                $shares = abs((float)$trade->shares);
                $proceeds = abs((float)$trade->sum) - $fee;
                $price = $shares != 0 ? $proceeds / $shares : 0;
                if ($qty > 0 && $shares > 0) {
                    $closed = min($shares, $qty);
                    $avg = $basis / $qty;
                    $realized += ($price - $avg) * $closed;
                    $basis -= $avg * $closed;
                    $qty -= $closed;
                    $shares -= $closed;
                }
                $basis -= $price * $shares;
                $qty -= $shares;
                $net_invested -= $proceeds;
                $cumulative_fees += $fee;
            }
            if ($trade->type == Trade::TYPE_DIVIDEND) {
                $cumulative_dividends += $trade->sum;
            }
            if ($trade->type == Trade::TYPE_COUPON) {
                $cumulative_coupons += $trade->sum;
            }
            if (in_array($trade->type, [Trade::TYPE_FEE, Trade::TYPE_TAXES])) {
                if ($trade->type == Trade::TYPE_FEE) {
                    $cumulative_fees += abs((float)$trade->sum);
                } else {
                    $cumulative_taxes += abs((float)$trade->sum);
                }
                $net_invested += $trade->sum;
            }
            if (in_array($trade->type, [Trade::TYPE_CREDIT, Trade::TYPE_DEBIT])) {
                $net_invested += $trade->sum;
            }
        }
        if ($qty == 0) {
            $basis = 0;
        }
        $this->count = $qty;
        $this->sum = round($net_invested, 10);
        $this->average_price = $qty != 0 ? abs($basis / $qty) : 0;
        $this->realized_gain = round($realized, 10);
        $this->earned_dividends = $cumulative_dividends;
        $this->earned_coupons = $cumulative_coupons;
        $this->paid_fees = $cumulative_fees;
        $this->paid_taxes = $cumulative_taxes;
    }

    /**
     * Returns current price of entity
     */
    public function findPrice(): ?float
    {
        if ($this->_price) {
            return $this->_price;
        }
        if ($this->ticker == Trade::TICKER_MONEY) {
            return 0;
        }
        $ticker_info = $this->tickerInfo();
        if ($ticker_info) {
            $last_price = $ticker_info->lastPrice();
            if ($last_price) {
                if ($ticker_info->isOutdated()) {
                    GetHistory::dispatch(ticker: $ticker_info->ticker, exchange: $ticker_info->exchange);
                }
                $this->_price = $last_price;
                return $this->_price;
            } else {
                // if no price found
                GetHistory::dispatch(ticker: $ticker_info->ticker, exchange: $ticker_info->exchange);
            }
        }
        return $this->_price;
    }

    public function tickerInfo(): ?Ticker
    {
        return Ticker::findModel($this->ticker, $this->exchange);
    }

    public function currentPrice(): float
    {
        if (!$this->_current_price) {
            $this->_current_price = (float)$this->findPrice();
            if ($this->_current_price == 0 && $this->count != 0) {
                $this->_current_price = abs($this->sum / $this->count);
            }
        }
        return $this->_current_price;
    }

    public function getCurrentPriceAttribute(): float
    {
        return (float)$this->currentPrice();
    }

    public function currentCost()
    {
        /** @var CurrencyService $currencyService */
        $currencyService = App::make('currencyService');
        return Cache::remember('currentCost_'.$this->id, 30, function () use ($currencyService) {
            $value = $this->current_cost_native;
            // convert currency if it not matches portfolio
            if ($this->portfolio && $this->currency_code != $this->portfolio->currency_code) {
                return $currencyService
                    ->convert($value, $this->currency_code, $this->portfolio->currency_code);
            }
            return $value;
        });
    }

    public function getCurrentCostAttribute(): float
    {
        return (float)round($this->currentCost(), 2);
    }

    /**
     * Item cost in native currency
     */
    public function getCurrentCostNativeAttribute(): float
    {
        if ($this->ticker == Trade::TICKER_MONEY) {
            return $this->sum;
        }
        return (float)round(bcmul($this->count, $this->currentPrice(), 5), 2);
    }

    public function getSumConvertedAttribute()
    {
        /** @var CurrencyService $currencyService */
        $currencyService = App::make('currencyService');
        return Cache::remember('sumConverted_'.$this->id, 30, function () use ($currencyService) {
            if ($this->portfolio && $this->currency_code != $this->portfolio->currency_code) {
                return $currencyService
                    ->convert($this->sum, $this->currency_code, $this->portfolio->currency_code);
            }
            return $this->sum;
        });
    }

    public function currentGain(): int|float
    {
        return $this->currentCost() - $this->sum_converted;
    }

    public function getCurrentGainAttribute(): float
    {
        return (float)round($this->currentGain(), 2);
    }

    public function getCurrentGainNativeAttribute(): float
    {
        return round($this->current_cost_native - $this->sum, 2);
    }

    public function currentGainPercent(): float
    {
        $sum = $this->currentCost() - $this->currentGain();
        if ($sum == 0 || $this->currentGain() + $sum == 0) {
            return 0;
        }
        return round( $this->currentGain() / $sum * 100, 2);
    }

    public function getCurrentGainPercentAttribute(): float
    {
        return $this->currentGainPercent();
    }

    /**
     * Percent of item in portfolio
     * Slow method (do not use in cycles)
     * @param $portfolio
     */
    public function partPercent($portfolio): float
    {
        $total = $portfolio->sharesCostTotal();
        $cost = $this->currentCost();
        if ($total != 0 && $cost > 0) {
            return round($cost / $total  * 100, 2);
        }
        return 0;
    }

    public function getPartPercentAttribute(): float
    {
        $portfolio = Cache::remember('portfolioItem_portfolio_'.$this->portfolio_id, 60, fn() => $this->portfolio);
        return $this->partPercent($portfolio);
    }

    public function getCurrencySignAttribute(): ?string
    {
        return Currency::signOf($this->currency_code);
    }

    public function getAssetLabelAttribute()
    {
        $list = AssetType::array();
        return Arr::get($list, $this->asset);
    }

    public function getUidAttribute(): string
    {
        return Uid::encode($this->id);
    }

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }

    /**
     * Earned dividends/coupons converted to portfolio's currency
     */
    public function getEarningsAttribute(): float
    {
        /** @var CurrencyService $currencyService */
        $currencyService = App::make('currencyService');
        $portfolio = Cache::remember('portfolioItem_portfolio_'.$this->portfolio_id, 60, fn() => $this->portfolio);
        $value = $this->earned_dividends + $this->earned_coupons;
        // convert currency if it not matches portfolio
        if ($portfolio && $this->currency_code != $portfolio->currency_code) {
            $res = $currencyService
                ->convert($value, $this->currency_code, $portfolio->currency_code);
            return round($res, 2);
        }
        return $value;
    }

    public function getEarningsNativeAttribute(): float
    {
        return $this->earned_dividends + $this->earned_coupons;
    }
}

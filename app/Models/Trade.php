<?php

namespace App\Models;

use App\Components\Uid;
use App\Enums\Currency;
use App\Services\PluginService;
use Cerbero\QueryFilters\FiltersRecords;
use DateTimeInterface;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OhMyShares\Plugin\Enums\AssetType;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @property int $id
 * @property int $user_id
 * @property int $portfolio_id
 * @property int $type
 * @property int $asset
 * @property string $ticker
 * @property string $exchange
 * @property float $cost
 * @property string $currency_code
 * @property float $shares
 * @property float $sum
 * @property float $fee
 * @property string $fee_currency_code
 * @property int $related_trade_id
 * @property string $notes
 * @property int $created_at
 * @property int $txn
 * @property bool $is_wo_money
 * @property bool $is_during_import
 * @property Portfolio $portfolio
 * @property User $user
 */
class Trade extends Model
{
    use FiltersRecords;
    use HasFactory;

    // operation type
    public const TYPE_BUY = 1;
    public const TYPE_SELL = 2; // expense
    public const TYPE_DIVIDEND = 3;
    public const TYPE_FEE = 4; //expense
    public const TYPE_COUPON = 5;
    public const TYPE_TAXES = 6; // expense
    public const TYPE_CREDIT = 7; // Incomes (Any)
    public const TYPE_DEBIT = 8; // Expenses (Any)

    // special ticker for money operations
    public const TICKER_MONEY = '$$';

    protected $fillable = [
    	'user_id',
    	'portfolio_id',
    	'portfolio_uid',
        'type',
        'asset',
    	'ticker',
        'exchange',
    	'cost',
    	'shares',
    	'fee',
        'fee_currency_code',
    	'sum',
        'related_trade_id',
    	'notes',
        'created_at',
        'currency_code',
        'txn',
        'is_wo_money',
    ];

    protected $hidden = [
        'portfolio_id',
    ];

    protected $appends = [
        'currency_sign',
        'fee_currency_sign',
        'sum_display',
        'portfolio_uid'
    ];

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $casts = [
        'created_at' => 'datetime',
        'sum' => 'float',
        'fee' => 'float',
        'is_ignore_funds_sufficiency' => 'boolean',
        'is_wo_money' => 'boolean',
    ];

    /**
     * @var bool Auto creating related trades like money decreasing, taxes etc
     */
    public bool $is_related_records_enabled = true;

    /**
     * @var bool Flag to mark trade processed by import, used to disable some checks when import
     */
    public bool $is_during_import = false;


    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format($this->dateFormat);
    }

    public static function boot(): void
    {
        parent::boot();

        if (config('app.trades_ignore_events')) {
            return;
        }

        static::creating(function (Trade $model): void {
            if (empty($model->created_at)) {
                $model->setCreatedAt(time());
            }
            if (empty($model->currency_code)) {
                $model->currency_code = $model->portfolio->currency_code;
            }
            if (empty($model->fee_currency_code)) {
                $model->fee_currency_code = $model->portfolio->currency_code;
            }
            $model->beforeCreateOrUpdate();
        });

        self::updating(function(Trade $model): void {
            $model->beforeCreateOrUpdate();
            //$model->updateRelatedItems();
        });

        static::created(function (Trade $model): void {
            $model->createRelatedItems();
            $model->updatePortfolioItem();
            $model->invalidateCache();
            if (empty($model->related_trade_id)) {
                app(PluginService::class)->onTradeAdded($model);
            }
        });

        static::updated(function (Trade $model): void {
            $model->updateRelatedItems();
            $model->updatePortfolioItem();
            $model->invalidateCache();
            if ($model->portfolio) {
                $model->portfolio->calcItems();
            }
            if ($model->wasChanged('portfolio_id')) {
                $prev_portfolio_id = $model->getOriginal('portfolio_id');
                $prevPortfolio = Portfolio::find($prev_portfolio_id);
                if ($prevPortfolio) {
                    $prevPortfolio->calcItems();
                }
            }
        });

        self::deleting(function($model) {
            $model->invalidateCache();
            return $model->beforeDelete();
        });

        self::deleted(fn($model) => $model->afterDelete());
    }

    public function invalidateCache(): void
    {
        Cache::forget('portfolio_'.$this->portfolio_id.'_growth');
    }

    /**
     * Actions before create/update
     */
    public function beforeCreateOrUpdate(): void
    {
        // Special $$ ticker for money
        if (in_array($this->type, [
            self::TYPE_CREDIT, self::TYPE_DEBIT,
            self::TYPE_FEE, self::TYPE_TAXES
        ])) {
            $this->ticker = self::TICKER_MONEY;
            $this->cost = null;
            $this->shares = null;
            $this->asset = AssetType::Money->value;
        }
        $this->calcSum();

        if (empty($this->fee)) {
            $this->fee = 0;
        }
        $this->ticker = mb_strtoupper($this->ticker);
        $this->exchange ??= '';
        if (empty($this->asset)) {
            $this->asset = AssetType::Shares->value;
        }

        if (!$this->is_during_import
            && !$this->is_wo_money
            && !config('app.trades_ignore_funds')
            && !$this->isIgnoreFunds()
            && !$this->isSufficientFunds()) {
            throw new HttpException(402, __('messages.no_funds_trade_adding_error'));
        }
    }

    /**
     * Checks sufficient funds to add entity
     * This check bypass money, dividends, coupons
     */
    public function isSufficientFunds(): bool
    {
        if ($this->portfolio && !$this->isMoney() && $this->type == self::TYPE_BUY) {
            $item = $this->portfolio->findItem(self::TICKER_MONEY, AssetType::Money->value, $this->currency_code);
            // if not found or low balance
            if (!$item || ($item->sum <= $this->sum)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks that user's setting to ignore sufficiency of funds enabled
     */
    public function isIgnoreFunds(): bool
    {
        if ($this->user) {
            return $this->user->is_ignore_funds_sufficiency;
        }
        return true;
    }

    public function createRelatedItems(): void
    {
        $this->createRelatedMoneyItem();
        $this->createRelatedFeeItem();
    }

    /**
     * Creating related money operations, excl. fees
     */
    public function createRelatedMoneyItem(): bool
    {
        if (!$this->is_related_records_enabled) {
            return false;
        }
        if ($this->is_wo_money) {
            return false;
        }
        // creating money operation for such trade types
        if (in_array($this->type, [self::TYPE_BUY, self::TYPE_SELL, self::TYPE_DIVIDEND, self::TYPE_COUPON])) {
            // money add/subtract
            $money = new Trade([
                'type' => $this->type == self::TYPE_BUY ? self::TYPE_DEBIT : self::TYPE_CREDIT,
                'sum' => $this->sum,
                'user_id' => $this->user_id,
                'portfolio_id' => $this->portfolio_id,
                'ticker' => self::TICKER_MONEY,
                'currency_code' => $this->currency_code,
                'related_trade_id' => $this->id,
                'created_at' => $this->created_at,
            ]);
            $money->calcSum();
            return $money->save();
        }
        return false;
    }

    /**
     * Creating related fees operations
     */
    public function createRelatedFeeItem(): bool
    {
        if (!$this->is_related_records_enabled) {
            return false;
        }
        if ($this->is_wo_money) {
            return false;
        }
        if (!empty($this->fee)) {
            $fee = new Trade([
                'type' => self::TYPE_FEE,
                'sum' => $this->fee,
                'user_id' => $this->user_id,
                'portfolio_id' => $this->portfolio_id,
                'ticker' => self::TICKER_MONEY,
                'currency_code' => $this->fee_currency_code,
                'related_trade_id' => $this->id,
                'created_at' => $this->created_at,
            ]);
            return $fee->save();
        }
        return false;
    }

    /**
     * Updating relating money operations
     */
    public function updateRelatedItems(): void
    {
        Trade::where('related_trade_id', $this->id)->delete();
        $this->createRelatedItems();
    }

    /**
     * Create or update PortfolioItem
     */
    public function updatePortfolioItem()
    {
        $item = PortfolioItem::where('ticker', $this->ticker)
            ->where('exchange', $this->exchange ?? '')
            ->where('portfolio_id', $this->portfolio_id)
            ->where('currency_code', $this->currency_code)
            ->first();
        if (!$item) {
            $item = new PortfolioItem();
        }
        $item->portfolio_id = $this->portfolio_id;
        $item->currency_code = $this->currency_code;
        $item->ticker = $this->ticker;
        $item->exchange = $this->exchange ?? '';
        $item->calc();
        if ($item->trades == 0) {
            return $item->delete();
        }
        return $item->save();
    }

    public function beforeDelete(): bool
    {
        if ($this->related_trade_id) {
            abort(403,'Record can not be deleted. You must delete related record first.');
        }

        DB::beginTransaction();
        try {
            Trade::where('related_trade_id', $this->id)->delete(); // this not firing events
        } catch (Exception) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    public function afterDelete(): void
    {
        if ($this->portfolio) {
            $this->portfolio->calcItems();
        }
    }

    public static function types(): array
    {
        return [
            self::TYPE_BUY => __('Buy'),
            self::TYPE_SELL => __('Sell'),
            self::TYPE_DIVIDEND => __('Dividend'),
            self::TYPE_COUPON => __('Coupon'),
            self::TYPE_FEE => __('Fee'),
            self::TYPE_TAXES => __('Taxes'),
            self::TYPE_CREDIT => __('Credit'),
            self::TYPE_DEBIT => __('Debit'),
        ];
    }

    public static function incomeTypes(): array
    {
        return [self::TYPE_BUY, self::TYPE_DIVIDEND, self::TYPE_COUPON, self::TYPE_CREDIT];
    }

    public static function expenseTypes(): array
    {
        return [self::TYPE_SELL, self::TYPE_FEE, self::TYPE_TAXES, self::TYPE_DEBIT];
    }

    public static function linkTypeAsset(): array
    {
        return [
            self::TYPE_BUY => [AssetType::Shares->value, AssetType::Bonds->value,
                AssetType::Crypto->value, AssetType::Custom->value],
            self::TYPE_SELL => [AssetType::Shares->value, AssetType::Bonds->value,
                AssetType::Crypto->value, AssetType::Custom->value],
            self::TYPE_CREDIT => [AssetType::Money->value, AssetType::Crypto->value],
            self::TYPE_DEBIT => [AssetType::Money->value, AssetType::Crypto->value],
            self::TYPE_DIVIDEND => [AssetType::Shares->value],
            self::TYPE_COUPON => [AssetType::Bonds->value],
            self::TYPE_TAXES => [AssetType::Money->value],
            self::TYPE_FEE => [AssetType::Money->value, AssetType::Crypto->value],
        ];
    }

    public static function linkAssetType(): array
    {
        return [
            AssetType::Money->value => [self::TYPE_CREDIT, self::TYPE_DEBIT, self::TYPE_TAXES, self::TYPE_FEE],
            AssetType::Crypto->value => [self::TYPE_BUY, self::TYPE_SELL],
            AssetType::Shares->value => [self::TYPE_BUY, self::TYPE_SELL, self::TYPE_DIVIDEND],
            AssetType::Bonds->value => [self::TYPE_BUY, self::TYPE_SELL, self::TYPE_COUPON],
            AssetType::Custom->value => [self::TYPE_BUY, self::TYPE_SELL],
        ];
    }

    public function getCurrencySignAttribute(): ?string
    {
        return Currency::signOf($this->currency_code);
    }

    public function getFeeCurrencySignAttribute(): ?string
    {
        return Currency::signOf($this->fee_currency_code);
    }

    public function getSumDisplayAttribute(): float
    {
        return abs($this->sum);
    }

    public function isIncome(): bool
    {
        return in_array($this->type, self::incomeTypes());
    }

    public function isExpense(): bool
    {
        return in_array($this->type, self::expenseTypes());
    }

    public function calcSum(): float
    {
        // Auto Sum for trades
        if (in_array($this->type, [self::TYPE_BUY, self::TYPE_SELL])) {
            $this->sum = $this->cost * $this->shares;
        }

        if ($this->isExpense()) {
            $this->sum = -1 * abs($this->sum);
            $this->shares = -1 * abs($this->shares);
        } else {
            $this->sum = abs($this->sum);
        }

        return $this->sum;
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isMoney(): bool
    {
        return $this->ticker == self::TICKER_MONEY
            && $this->asset == AssetType::Money->value;
    }

    public function getPortfolioUidAttribute(): string
    {
        return Uid::encode($this->portfolio_id);
    }

    public function setPortfolioUidAttribute($value): void
    {
        $this->portfolio_id = Uid::decode($value);
    }
}

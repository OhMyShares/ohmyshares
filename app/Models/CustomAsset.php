<?php

namespace App\Models;

use App\Enums\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CustomAsset extends Model
{
    protected $fillable = [
        'user_id',
        'currency_code',
        'ticker',
        'name',
        'info',
    ];

    public $timestamps = false;

    protected $appends = [
        'currency_sign',
        'price',
    ];

    protected $hidden = [
        'user_id'
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($model): void {
            $model->beforeCreateOrUpdate();
        });

        static::updating(function ($model): void {
            $model->beforeCreateOrUpdate();
        });
    }

    public function beforeCreateOrUpdate(): void
    {
        $this->ticker = mb_strtoupper($this->ticker);
        $this->currency_code = mb_strtoupper($this->currency_code);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lastPrice(): float|int
    {
        $p = $this->belongsTo(CustomAssetPrice::class, 'id', 'custom_asset_id')
            ->orderBy('created_at', 'desc')
            ->first('price');
        if ($p) {
            return (float)$p->price;
        }
        return 0;
    }

    public function getPriceAttribute(): float|int
    {
        return $this->lastPrice();
    }


    public function getCurrencySignAttribute(): ?string
    {
        return Currency::signOf($this->currency_code);
    }
}

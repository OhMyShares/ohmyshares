<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomAssetPrice extends Model
{
    protected $table = 'custom_assets_prices';

    protected $fillable = [
        'custom_asset_id',
        'created_at',
        'price',
    ];

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $hidden = [
        'asset',
    ];

    protected $appends = [
        'currency_sign',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat);
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($model): void {
            if (empty($model->created_at)) {
                $model->setCreatedAt(time());
            }
        });
    }

    public function asset()
    {
        return $this->belongsTo(CustomAsset::class, 'custom_asset_id', 'id');
    }

    public function getCurrencySignAttribute()
    {
        if ($this->asset) {
            return $this->asset->currency_sign;
        }
        return '';
    }
}

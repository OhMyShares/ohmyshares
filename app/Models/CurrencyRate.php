<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Price of 1 unit of $currency expressed in $base on $date
 *
 * @property int $id
 * @property string $base
 * @property string $currency
 * @property string $date
 * @property float $rate
 * @property string|null $source
 */
class CurrencyRate extends Model
{
    protected $table = 'currency_rates';

    protected $fillable = [
        'base',
        'currency',
        'date',
        'rate',
        'source',
    ];

    protected $casts = [
        'rate' => 'float',
    ];
}

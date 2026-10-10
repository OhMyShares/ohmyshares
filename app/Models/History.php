<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $ticker
 * @property string $exchange
 * @property integer $asset
 * @property string $date
 * @property float $open
 * @property float $high
 * @property float $low
 * @property float $close
 * @property float $volume
 * @property float $dividends
 * @property float $stock_splits
 */
class History extends Model
{
    protected $table = 'history';

    public $timestamps = false;

    protected $hidden = [
        'id',
    ];

    protected $fillable = [
        'ticker',
        'exchange',
        'date',
    ];

    protected $casts = [
        'open' => 'float',
        'high' => 'float',
        'low' => 'float',
        'close' => 'float',
        'volume' => 'integer',
        'dividends' => 'float',
        'stock_splits' => 'float',
    ];
}

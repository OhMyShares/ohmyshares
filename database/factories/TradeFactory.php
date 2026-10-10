<?php
namespace Database\Factories;

use App\Models\Trade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trade>
 */
class TradeFactory extends Factory
{
    protected $model = Trade::class;

    public function definition(): array
    {
        return [
            'currency_code' => 'USD',
            'fee_currency_code' => 'RUB',
            'shares' => 1,
            'cost' => 100,
        ];
    }
}

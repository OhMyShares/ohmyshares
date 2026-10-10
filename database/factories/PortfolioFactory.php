<?php
namespace Database\Factories;

use App\Enums\Currency;
use App\Models\Portfolio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Portfolio>
 */
class PortfolioFactory extends Factory
{
    protected $model = Portfolio::class;

    public function definition(): array
    {
        return [
            'opened_at' => $this->faker->unixTime(),
            'name' => $this->faker->words(3, true),
            'currency_code' => $this->faker->randomElement(Currency::codes()),
            'account_type' => Portfolio::TYPE_BROKER,
        ];
    }
}

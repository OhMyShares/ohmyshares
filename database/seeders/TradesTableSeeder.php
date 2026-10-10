<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Trade;

class TradesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        Trade::truncate();
        foreach (range(1, 100) as $i) {
        	Trade::create([
        		'user_id' => 1,
        		'portfolio_id' => 1,
        		'ticker_id' => 1,
        		'cost' => $faker->numberBetween($min = 0, $max = 1000),
        		'shares' => $faker->randomDigitNotNull(),
        	]);
        }
    }
}

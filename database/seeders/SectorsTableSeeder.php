<?php
namespace Database\Seeders;

use App\Enums\Sector as SectorEnum;
use App\Models\Sector;
use Illuminate\Database\Seeder;

class SectorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (SectorEnum::titles() as $item) {
            Sector::firstOrCreate(['title' => $item]);
        }
    }
}

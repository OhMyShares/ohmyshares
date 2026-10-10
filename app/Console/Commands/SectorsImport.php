<?php

namespace App\Console\Commands;

use App\Enums\Sector as SectorEnum;
use App\Models\Portfolio;
use App\Models\Sector;
use App\Models\Ticker;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use League\Csv\CharsetConverter;
use League\Csv\Reader;

class SectorsImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sectors:import';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Import sectors from predefined values';

    public array $sectors = [];

    public function __construct()
    {
        parent::__construct();
        $this->sectors = SectorEnum::titles();
    }

    /**
     * Execute the console command.
     */
    public function handle(): bool
    {
        if ($this->confirm('Sectors table will be truncated. Are you sure?')) {
            $this->line("Truncating sectors table...");
            Schema::disableForeignKeyConstraints();
            Sector::truncate();
            Schema::enableForeignKeyConstraints();
            $this->insert();
            $this->line("Done.");
            return true;
        }
        $this->line("Canceled.");
        return false;
    }

    public function insert(): void
    {
        $this->line("Inserting...");
        foreach ($this->sectors as $item) {
            Sector::insert(['title' => $item]);
        }
    }
}

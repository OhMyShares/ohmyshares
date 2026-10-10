<?php

namespace App\Console\Commands;

use App\Components\FileUtils;
use App\Models\Ticker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TickersTruncate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickers:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate tickers table';

    /**
     * Execute the console command.
     */
    public function handle(): bool
    {
        if ($this->confirm('Are u sure?')) {
            $this->line("Truncating tickers table...");
            DB::table('tickers')->truncate();
            $this->line("Done.");
            return true;
        }
        $this->line("Canceled.");
        return false;
    }
}

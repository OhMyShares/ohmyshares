<?php

namespace App\Console\Commands;

use App\Services\CurrencyService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CurrencyRatesUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:update {--date= : Date of rates, YYYY-MM-DD (default: today)}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Requests currency rates from enabled plugins and stores them';

    public function handle(CurrencyService $service): int
    {
        $date = $this->option('date');
        $count = $service->update($date ? Carbon::createFromFormat('Y-m-d', $date) : null);
        $this->info('Stored rates: '.$count);
        return self::SUCCESS;
    }
}

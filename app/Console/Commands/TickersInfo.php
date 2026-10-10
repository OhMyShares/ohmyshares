<?php

namespace App\Console\Commands;

use App\Models\Ticker;
use Illuminate\Console\Command;

class TickersInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickers:get-history {ticker} {exchange?}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Updates ticker history';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $ticker = $this->argument('ticker');
        $exchange = $this->argument('exchange');
        $t = Ticker::findModel($ticker, $exchange);
        if (!$t) {
            $this->warn("Ticker $ticker not found");
            return;
        }
        $t->updateHistory();
    }
}

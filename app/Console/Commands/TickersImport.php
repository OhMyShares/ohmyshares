<?php

namespace App\Console\Commands;

use App\Components\FileUtils;
use App\Models\Ticker;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TickersImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickers:import {csvFile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import tickers from CSV file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $csvFile = $this->argument('csvFile');
        $typeLabels = [];
        foreach (Ticker::typeLabels() as $key => $label) {
            $typeLabels[] = "$key - $label";
        }
        $type = $this->ask(Arr::join($typeLabels, ', ').' Type?');

        $filepath = Storage::path($csvFile);
        //$this->line("Clear tickers table...");
        //DB::table('tickers')->truncate();
        $lines = FileUtils::countLines($filepath);
        $bar = $this->output->createProgressBar($lines);
        $bar->start();

        $cnt = 0;
        ini_set('auto_detect_line_endings', true);

        if (($handle = fopen($filepath, "r")) !== false) {
            $items = [];
            while (($data = fgetcsv($handle, 200, ";")) !== false) {
                $ticker = trim($data[0]);
                if (!empty($ticker)) {
                    $item = [
                        'ticker' => $ticker,
                        'name' => Arr::get($data, 1),
                        'exchange' => Arr::get($data, 2) ?? '',
                        'category' => Arr::get($data, 3),
                        'country' => Arr::get($data, 4),
                        'type' => $type,
                    ];
                    $items[] = $item;
                }
                if (count($items) % 100 == 0) {
                    if (Ticker::upsert($items, ['ticker', 'exchange'], ['name', 'category', 'country', 'type'])) {
                        $cnt += 100;
                        $items = [];
                    }
                    if (($cnt % 100)==0) {
                        $bar->advance(100);
                    }
                }
            }
            fclose($handle);
            if ($items) {
                Ticker::upsert($items, ['ticker', 'exchange'], ['name', 'category', 'country', 'type']);
                $cnt += count($items);
            }
        }
        ini_set('auto_detect_line_endings', false);
        $bar->finish();
        $this->line("\n$cnt lines imported!");
    }
}

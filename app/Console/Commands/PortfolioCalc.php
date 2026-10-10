<?php

namespace App\Console\Commands;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Console\Command;

class PortfolioCalc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portfolio:calc {portfolioId=all} {--user= : Recalculate all portfolios of given user id} {--force : Skip confirmation}';

    /**
     * The console command description.
     * @see https://spbexchange.ru/ru/listing/securities/list/
     * @var string
     */
    protected $description = 'Recalculate portfolio items';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->option('user');
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error('User not found.');
                return self::FAILURE;
            }
            if ($this->option('force') || $this->confirm("Recalculate all portfolios of user #{$user->id} ({$user->email})?")) {
                $this->recalc($user->portfolios()->get());
                return self::SUCCESS;
            }
            $this->line('Canceled.');
            return self::SUCCESS;
        }

        $portfolioId = $this->argument('portfolioId');
        if ($portfolioId === 'all') {
            if ($this->option('force') || $this->confirm('Are you sure you want to recalculate all user`s portfolios?')) {
                $this->recalc(Portfolio::all());
                return self::SUCCESS;
            }
            $this->line('Canceled.');
            return self::SUCCESS;
        }

        $p = Portfolio::find($portfolioId);
        if ($p) {
            $p->calcItems();
            $this->line('Done.');
            dump($p->items->toArray());
            return self::SUCCESS;
        }
        $this->error('Portfolio not found.');
        return self::FAILURE;
    }

    protected function recalc($portfolios): void
    {
        $bar = $this->output->createProgressBar(count($portfolios));
        $bar->start();
        foreach ($portfolios as $p) {
            $p->calcItems();
            $bar->advance(1);
        }
        $bar->finish();
        $this->line(PHP_EOL.'Done.');
    }
}

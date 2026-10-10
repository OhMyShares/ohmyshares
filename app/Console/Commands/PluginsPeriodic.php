<?php

namespace App\Console\Commands;

use App\Services\PluginService;
use Illuminate\Console\Command;

class PluginsPeriodic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugins:periodic';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Runs onPeriodic() hook of every enabled plugin';

    public function handle(PluginService $service): int
    {
        $service->onPeriodic();
        return self::SUCCESS;
    }
}

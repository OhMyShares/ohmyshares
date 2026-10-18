<?php

namespace App\Jobs;

use App\Models\Plugin;
use App\Services\PluginService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class InstallPlugin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public bool $failOnTimeout = true;

    public int $timeout;

    public function __construct(
        public string $installationId,
        public string $remote,
        public ?int $pluginId = null,
    ) {
        $this->timeout = (int)config('plugins.install_timeout');
    }

    public function handle(PluginService $service): void
    {
        $service->markInstallationRunning($this->installationId);
        $lock = Cache::lock('plugins.install', (int)config('plugins.install_timeout'));
        try {
            $lock->block((int)config('plugins.install_wait'), function () use ($service): void {
                $plugin = $this->pluginId
                    ? $service->upgrade(Plugin::findOrFail($this->pluginId))
                    : $service->install($this->remote);
                $service->completeInstallation($this->installationId, $plugin);
            });
        } catch (Throwable $e) {
            $service->failInstallation($this->installationId, $e->getMessage());
            Log::error('Plugin '.$this->remote.' installation failed: '.$e->getMessage());
        }
    }

    public function failed(?Throwable $e): void
    {
        app(PluginService::class)->failInstallation($this->installationId, $e?->getMessage() ?? 'Installation failed');
    }
}

<?php

namespace App\Jobs;

use App\Models\Ticker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OhMyShares\Plugin\Enums\AssetType;

class GetHistory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public string $ticker,
        public string $exchange,
        public int $asset = AssetType::Crypto->value
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $t = Ticker::findModel($this->ticker, $this->exchange);
        $t?->updateHistory();
    }
}

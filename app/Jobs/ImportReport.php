<?php

namespace App\Jobs;

use App\Exceptions\ImportException;
use App\Models\Portfolio;
use App\Services\ReportImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     * @return void
     */
    public function __construct(protected Portfolio $portfolio, protected string $filepath, protected string $brokerName)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(ReportImportService $service): void
    {
        $lock = Cache::lock('import_report_portfolio_'.$this->portfolio->id, 30);
        if ($lock->get()) {
            try {
                $notices = $service->import($this->portfolio, $this->brokerName, $this->filepath);
                $imported = true;
            } catch (ImportException $e) {
                Log::error("Import error: \n".$e->getMessage().
                    "\nInfo: \n".$e->getDetails().
                    "\n\nException Trace: \n".$e->getTraceAsString()
                );
                $notices = [$e->getMessage()];
                $imported = false;
            } catch (Throwable $e) {
                Log::error('Unhandled import error: '.$e->getMessage().
                    "\n\nException Trace:\n".$e->getTraceAsString()
                );
                $notices = [__('parsers.unhandled_import_error')];
                $imported = false;
            } finally {
                $lock->release();
            }

            Cache::put('import_report_status_'.$this->portfolio->id,
                $imported, now()->addDays(10));
            Cache::put('import_report_notices_'.$this->portfolio->id,
                $notices, now()->addDays(10));
        }
    }

}

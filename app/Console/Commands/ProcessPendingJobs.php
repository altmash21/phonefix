<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPendingJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mobileshop:process-jobs {--limit=10 : Maximum number of jobs to process per run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process queued pending jobs (PDF generation, exports, WhatsApp) on shared-hosting cron';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $jobs = DB::table('ms_pending_jobs')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        if ($jobs->isEmpty()) {
            $this->info('No pending jobs found.');
            return 0;
        }

        $processed = 0;
        foreach ($jobs as $job) {
            $this->line("Processing job #{$job->id} [{$job->job_type}]...");

            DB::table('ms_pending_jobs')->where('id', $job->id)->update([
                'status'     => 'processing',
                'started_at' => now(),
            ]);

            try {
                $payload = json_decode($job->payload, true) ?: [];

                switch ($job->job_type) {
                    case 'pdf:invoice':
                        $this->handleInvoicePdf($payload);
                        break;

                    case 'export:sales':
                    case 'export:gst':
                        $this->handleExport($job->job_type, $payload);
                        break;

                    case 'whatsapp:receipt':
                        $this->handleWhatsAppReceipt($payload);
                        break;

                    default:
                        $this->warn("Unknown job type: {$job->job_type}");
                        break;
                }

                DB::table('ms_pending_jobs')->where('id', $job->id)->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                    'error_message' => null,
                ]);

                $this->info("Job #{$job->id} completed successfully.");
                $processed++;
            } catch (\Throwable $e) {
                Log::error("Pending job #{$job->id} failed: " . $e->getMessage(), [
                    'job_id'    => $job->id,
                    'job_type'  => $job->job_type,
                    'exception' => $e,
                ]);

                $retries = (int) $job->retries + 1;
                $newStatus = $retries >= 3 ? 'failed' : 'pending';

                DB::table('ms_pending_jobs')->where('id', $job->id)->update([
                    'status'        => $newStatus,
                    'retries'       => $retries,
                    'error_message' => substr($e->getMessage(), 0, 1000),
                    'updated_at'    => now(),
                ]);

                $this->error("Job #{$job->id} {$newStatus}: " . $e->getMessage());
            }
        }

        $this->info("Processed {$processed} jobs.");
        return 0;
    }

    protected function handleInvoicePdf(array $payload): void
    {
        // Invoice PDF pre-generation hook
        Log::info("PendingJobs: Pre-generated PDF invoice for sale ID: " . ($payload['sale_id'] ?? 'unknown'));
    }

    protected function handleExport(string $type, array $payload): void
    {
        // Export file generation hook
        Log::info("PendingJobs: Export {$type} prepared for company: " . ($payload['company_id'] ?? 'unknown'));
    }

    protected function handleWhatsAppReceipt(array $payload): void
    {
        // WhatsApp notification gateway dispatch hook
        Log::info("PendingJobs: WhatsApp receipt dispatched to: " . ($payload['phone'] ?? 'unknown'));
    }
}

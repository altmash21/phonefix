<?php

namespace App\Console\Commands;

use App\Services\MobileShop\Common\MobileShopInvoiceResolver;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
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
                if (!isset($payload['company_id']) && isset($job->company_id)) {
                    $payload['company_id'] = (int) $job->company_id;
                }

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
                    'status'        => 'completed',
                    'completed_at'  => now(),
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
        $companyId = (int) ($payload['company_id'] ?? 1);
        $saleId = isset($payload['sale_id']) ? (int) $payload['sale_id'] : null;

        if (!$saleId) {
            Log::warning("PendingJobs: Missing sale_id for invoice PDF job.");
            return;
        }

        $saleExists = DB::table('ms_mobile_sales')
            ->where('company_id', $companyId)
            ->where('id', $saleId)
            ->exists();

        if (!$saleExists) {
            Log::warning("PendingJobs: Sale #{$saleId} not found for company #{$companyId}. Skipping PDF generation.");
            return;
        }

        $data = MobileShopInvoiceResolver::resolvePhoneSaleDetails($companyId, $saleId);

        $pdf = Pdf::loadView('mobileshop.pdf.phone_invoice', $data);
        $pdf->setPaper('a4', 'portrait');

        $storageDir = storage_path('app/public/invoices');
        if (!File::isDirectory($storageDir)) {
            File::makeDirectory($storageDir, 0755, true, true);
        }

        $invNum = $data['sale']->invoice_number ?? "INV-{$saleId}";
        $filename = "Invoice-{$invNum}.pdf";
        $pdf->save($storageDir . '/' . $filename);

        Log::info("PendingJobs: Pre-generated PDF invoice: {$storageDir}/{$filename}");
    }

    protected function handleExport(string $type, array $payload): void
    {
        $companyId = (int) ($payload['company_id'] ?? 1);
        $storageDir = storage_path('app/public/exports');
        if (!File::isDirectory($storageDir)) {
            File::makeDirectory($storageDir, 0755, true, true);
        }

        $timestamp = date('Ymd_His');
        $filename = ($type === 'export:gst' ? "gst_export_{$companyId}_{$timestamp}.csv" : "sales_export_{$companyId}_{$timestamp}.csv");
        $filepath = $storageDir . '/' . $filename;

        // Compile CSV data
        $file = fopen($filepath, 'w');
        if ($type === 'export:gst') {
            fputcsv($file, ['GSTIN', 'Invoice Number', 'Invoice Date', 'Customer Name', 'State Code', 'Taxable Amount', 'Tax Rate', 'CGST', 'SGST', 'IGST', 'Total Invoice Value']);
            $sales = DB::table('ms_mobile_sales')
                ->leftJoin('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
                ->where('ms_mobile_sales.company_id', $companyId)
                ->where('ms_mobile_sales.status', '!=', 'voided')
                ->select(
                    'ms_mobile_sales.*',
                    'ms_customers.name as customer_name',
                    'ms_customers.phone as customer_phone',
                    'ms_customers.gstin as customer_gstin',
                    'ms_customers.state_code as customer_state_code'
                )
                ->limit(500)
                ->get();

            foreach ($sales as $s) {
                $tax = (float) (($s->cgst_amount ?? 0) + ($s->sgst_amount ?? 0) + ($s->igst_amount ?? 0));
                $total = (float) ($s->final_amount ?? $s->total_amount ?? 0);
                $taxable = max(0, $total - $tax);
                fputcsv($file, [
                    $s->customer_gstin ?: 'URP',
                    $s->invoice_number ?? "INV-{$s->id}",
                    $s->created_at,
                    $s->customer_name ?? 'Walk-in Customer',
                    $s->customer_state_code ?? '09',
                    $taxable,
                    $tax > 0 ? 18.00 : 0.00,
                    $s->cgst_amount ?? 0,
                    $s->sgst_amount ?? 0,
                    $s->igst_amount ?? 0,
                    $total,
                ]);
            }
        } else {
            fputcsv($file, ['ID', 'Invoice Number', 'Date', 'Customer', 'Phone', 'Payment Mode', 'Final Amount', 'Status']);
            $sales = DB::table('ms_mobile_sales')
                ->leftJoin('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
                ->where('ms_mobile_sales.company_id', $companyId)
                ->select(
                    'ms_mobile_sales.*',
                    'ms_customers.name as customer_name',
                    'ms_customers.phone as customer_phone'
                )
                ->limit(500)
                ->get();

            foreach ($sales as $s) {
                fputcsv($file, [
                    $s->id,
                    $s->invoice_number ?? "INV-{$s->id}",
                    $s->created_at,
                    $s->customer_name ?? 'Walk-in Customer',
                    $s->customer_phone ?? '',
                    $s->payment_mode ?? 'Cash',
                    $s->final_amount ?? $s->total_amount ?? 0,
                    $s->status ?? 'completed',
                ]);
            }
        }
        fclose($file);

        Log::info("PendingJobs: Export {$type} saved to: {$filepath}");
    }

    protected function handleWhatsAppReceipt(array $payload): void
    {
        $phone = $payload['phone'] ?? null;
        $customer = $payload['customer_name'] ?? 'Valued Customer';
        $invoice = $payload['invoice_number'] ?? 'N/A';
        $amount = number_format((float) ($payload['amount'] ?? 0), 2);
        $items = (array) ($payload['items'] ?? []);
        $storeName = store_name('MobiTrack');
        $storePhone = store_phone();
        $storeAddress = store_address();
        $pdfUrl = url("bill/{$invoice}/pdf");

        $lines = [];
        $lines[] = "*{$storeName}*";
        $lines[] = "Tax Invoice #{$invoice}";
        $lines[] = "Dear *{$customer}*,";
        $lines[] = "Thank you for purchasing at {$storeName}!";
        if (!empty($items)) {
            $itemList = implode(', ', array_filter($items));
            if (!empty($itemList)) {
                $lines[] = "• *Items:* {$itemList}";
            }
        }
        $lines[] = "• *Total Amount:* ₹{$amount}";
        $lines[] = "📄 *Download / View PDF Bill:*";
        $lines[] = $pdfUrl;
        $lines[] = "Support: {$storePhone}";
        $lines[] = $storeAddress;

        $message = implode("\n", $lines);

        $webhook = config('mobileshop.whatsapp.webhook') ?: (config('services.whatsapp.webhook') ?: env('WA_WEBHOOK'));
        $fromPhone = config('mobileshop.whatsapp.phone') ?: (config('services.whatsapp.phone') ?: env('WA_PHONE'));

        if (!empty($webhook) && !empty($phone)) {
            try {
                Http::timeout(5)->post($webhook, [
                    'from'          => $fromPhone,
                    'phone'         => $phone,
                    'customer_name' => $customer,
                    'invoice'       => $invoice,
                    'amount'        => $payload['amount'] ?? 0,
                    'items'         => $items,
                    'pdf_url'       => $pdfUrl,
                    'store_name'    => $storeName,
                    'store_address' => $storeAddress,
                    'store_phone'   => $storePhone,
                    'message'       => $message,
                ]);
            } catch (\Throwable $e) {
                Log::warning("WhatsApp dispatch webhook failed: " . $e->getMessage());
            }
        }

        Log::info("PendingJobs: WhatsApp receipt queued for {$phone}: {$message}");
    }
}

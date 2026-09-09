<?php

namespace App\Services\MobileShop\Common;

use Illuminate\Support\Facades\DB;

class PendingJobService
{
    /**
     * Dispatch an asynchronous task to ms_pending_jobs queue
     */
    public static function dispatch(int $companyId, string $jobType, array $payload): int
    {
        return (int) DB::table('ms_pending_jobs')->insertGetId([
            'company_id'    => $companyId,
            'job_type'      => $jobType,
            'payload'       => json_encode($payload),
            'status'        => 'pending',
            'retries'       => 0,
            'error_message' => null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    /**
     * Retrieve status of a queued job
     */
    public static function getJobStatus(int $jobId, int $companyId): ?object
    {
        return DB::table('ms_pending_jobs')
            ->where('id', $jobId)
            ->where('company_id', $companyId)
            ->first();
    }

    /**
     * Queue background invoice PDF pre-generation
     */
    public static function queueInvoicePdf(int $companyId, int $saleId, string $invoiceNumber, string $type = 'phone_sale'): int
    {
        return self::dispatch($companyId, 'pdf:invoice', [
            'company_id'     => $companyId,
            'sale_id'        => $saleId,
            'invoice_number' => $invoiceNumber,
            'type'           => $type,
        ]);
    }

    /**
     * Queue background CSV export
     */
    public static function queueExport(int $companyId, string $exportType, array $filters = []): int
    {
        return self::dispatch($companyId, "export:{$exportType}", [
            'company_id'  => $companyId,
            'export_type' => $exportType,
            'filters'     => $filters,
        ]);
    }

    /**
     * Queue WhatsApp receipt notification
     */
    public static function queueWhatsAppReceipt(
        int $companyId,
        string $phone,
        string $customerName,
        string $invoiceNumber,
        float $amount,
        array $items = []
    ): int {
        return self::dispatch($companyId, 'whatsapp:receipt', [
            'company_id'     => $companyId,
            'phone'          => $phone,
            'customer_name'  => $customerName,
            'invoice_number' => $invoiceNumber,
            'amount'         => $amount,
            'items'          => $items,
        ]);
    }
}

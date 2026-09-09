<?php

namespace App\Services\MobileShop\Sales;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WhatsAppReceiptService
{
    /**
     * Build WhatsApp wa.me direct redirect URL for a mobile sale receipt
     */
    public function buildUrl(int $id, int $companyId): ?string
    {
        $sale = DB::table('ms_mobile_sales')
            ->join('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
            ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
            ->select('ms_mobile_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone',
                     'ms_mobile_devices.brand', 'ms_mobile_devices.model', 'ms_mobile_devices.imei_1', 'ms_mobile_devices.storage')
            ->where('ms_mobile_sales.id', $id)
            ->where('ms_mobile_sales.company_id', $companyId)
            ->first();

        if (!$sale) {
            return null;
        }

        $cPhone = preg_replace('/[^0-9]/', '', $sale->customer_phone ?? '');
        if (strlen($cPhone) === 10) {
            $cPhone = '91' . $cPhone;
        }

        $sName = setting('company.name', 'Maurya Mobile');
        $mobMsg = "🧾 *TAX INVOICE & RECEIPT*\n";
        $mobMsg .= "🏪 *{$sName}*\n";
        $mobMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $mobMsg .= "Dear *{$sale->customer_name}*,\n";
        $mobMsg .= "Thank you for purchasing at *{$sName}*!\n\n";
        $mobMsg .= "📋 *INVOICE DETAILS*\n";
        $mobMsg .= "• *Invoice #:* {$sale->invoice_number}\n";
        $mobMsg .= "• *Date:* " . Carbon::parse($sale->created_at)->format('d M Y, h:i A') . "\n";
        $mobMsg .= "• *Device:* {$sale->brand} {$sale->model}" . (!empty($sale->storage) ? " ({$sale->storage})" : "") . "\n";
        $mobMsg .= "• *IMEI 1:* `{$sale->imei_1}`\n\n";
        $mobMsg .= "💰 *Total Amount:* ₹" . number_format($sale->total_amount, 2) . " (" . strtoupper(str_replace('_', ' ', $sale->payment_mode)) . ")\n";
        if ($sale->udhari_amount > 0) {
            $mobMsg .= "⚠️ *Balance Due:* *₹" . number_format($sale->udhari_amount, 2) . "*\n";
        }
        $mobMsg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $mobMsg .= "🛡️ Official Warranty & Genuine GST Bill\n";
        $mobMsg .= "📍 Linking Road, Bandra West, Mumbai\n";
        $mobMsg .= "_Please retain this digital receipt for your records._";

        return 'https://wa.me/' . $cPhone . '?text=' . rawurlencode($mobMsg);
    }
}

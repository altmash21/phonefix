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

        $sName = store_name();
        $sPhone = store_phone();
        $sAddress = store_address();
        $pdfUrl = url("bill/{$sale->invoice_number}/pdf");

        $mobMsg = "*{$sName}*\n";
        $mobMsg .= "Invoice #{$sale->invoice_number}\n\n";
        $mobMsg .= "Dear *" . ($sale->customer_name ?: 'Customer') . "*,\n";
        $mobMsg .= "Thank you for your purchase!\n\n";
        $mobMsg .= "• *Device:* {$sale->brand} {$sale->model}" . (!empty($sale->storage) ? " ({$sale->storage})" : "") . "\n";
        $mobMsg .= "• *IMEI:* `{$sale->imei_1}`\n";
        $mobMsg .= "• *Date:* " . Carbon::parse($sale->created_at)->format('d M Y') . "\n";
        if (isset($sale->discount_amount) && (float)$sale->discount_amount > 0) {
            $orig = (float)($sale->original_price ?? ($sale->total_amount + $sale->discount_amount));
            $mobMsg .= "• *Original Price:* ₹" . number_format(round($orig)) . "\n";
            $mobMsg .= "• *Discount:* -₹" . number_format(round($sale->discount_amount)) . "\n";
        }
        $mobMsg .= "• *Total Amount:* ₹" . number_format(round($sale->total_amount)) . " (" . strtoupper(str_replace('_', ' ', $sale->payment_mode)) . ")\n";
        if ($sale->udhari_amount > 0) {
            $mobMsg .= "• *Balance Due:* ₹" . number_format(round($sale->udhari_amount)) . "\n";
            $upiId = store_upi_id();
            if (!empty($upiId)) {
                $mobMsg .= "• *Pay via UPI:* `{$upiId}`\n";
            }
        }
        $mobMsg .= "\n📄 *Download / View PDF Bill:*\n";
        $mobMsg .= "{$pdfUrl}\n\n";
        $mobMsg .= "Support: {$sPhone}\n";
        $landline = store_landline();
        if (!empty($landline)) {
            $mobMsg .= "Landline: {$landline}\n";
        }
        $mobMsg .= "{$sAddress}";

        return 'https://wa.me/' . $cPhone . '?text=' . rawurlencode($mobMsg);
    }
}

<?php

namespace App\Services\MobileShop\Reports;

use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesExportService
{
    /**
     * Export Filtered Sales Register to CSV / Excel
     */
    public function exportSalesCsv(array $data): StreamedResponse
    {
        $mobileSales = $data['mobileSales'];
        $accSales    = $data['accSales'];

        $filename = 'mobitrack_sales_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($mobileSales, $accSales) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                'Invoice No',
                'Invoice Date',
                'Category',
                'Customer Name',
                'Customer Phone',
                'Customer GSTIN',
                'Item Description / IMEI',
                'Bill Type',
                'Payment Mode',
                'Taxable Value (INR)',
                'Tax Rate (%)',
                'CGST (INR)',
                'SGST (INR)',
                'IGST (INR)',
                'Total Amount (INR)',
                'Status',
            ]);

            foreach ($mobileSales as $s) {
                $tax = (float) ($s->cgst_amount + $s->sgst_amount + $s->igst_amount);
                $taxable = max(0, (float) $s->total_amount - $tax);
                $billType = ((float) $s->total_amount == $taxable || $tax == 0) ? 'Non-GST' : 'GST';

                fputcsv($handle, [
                    $s->invoice_number,
                    $s->created_at,
                    'Mobile Device',
                    $s->customer_name ?? 'Walk-in Customer',
                    $s->customer_phone ?? '',
                    $s->customer_gstin ?? '',
                    trim(($s->brand ?? '') . ' ' . ($s->model ?? '') . ' ' . ($s->imei_1 ? 'IMEI: ' . $s->imei_1 : '')),
                    $billType,
                    strtoupper($s->payment_mode ?? 'CASH'),
                    number_format($taxable, 2, '.', ''),
                    number_format((float) ($s->tax_rate ?? 18), 2, '.', ''),
                    number_format((float) $s->cgst_amount, 2, '.', ''),
                    number_format((float) $s->sgst_amount, 2, '.', ''),
                    number_format((float) $s->igst_amount, 2, '.', ''),
                    number_format((float) $s->total_amount, 2, '.', ''),
                    ucfirst($s->status ?? 'completed'),
                ]);
            }

            foreach ($accSales as $s) {
                $taxable = (float) ($s->subtotal ?? ($s->total_amount - ($s->cgst_amount + $s->sgst_amount + $s->igst_amount)));
                $tax = (float) ($s->cgst_amount + $s->sgst_amount + $s->igst_amount);
                $billType = ($tax == 0) ? 'Non-GST' : 'GST';

                fputcsv($handle, [
                    $s->invoice_number,
                    $s->created_at,
                    'Accessories / Parts',
                    $s->customer_name ?? 'Walk-in Customer',
                    $s->customer_phone ?? '',
                    $s->customer_gstin ?? '',
                    'Accessories & Services',
                    $billType,
                    strtoupper($s->payment_mode ?? 'CASH'),
                    number_format($taxable, 2, '.', ''),
                    number_format((float) ($s->tax_rate ?? 18), 2, '.', ''),
                    number_format((float) $s->cgst_amount, 2, '.', ''),
                    number_format((float) $s->sgst_amount, 2, '.', ''),
                    number_format((float) $s->igst_amount, 2, '.', ''),
                    number_format((float) $s->total_amount, 2, '.', ''),
                    ucfirst($s->status ?? 'completed'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

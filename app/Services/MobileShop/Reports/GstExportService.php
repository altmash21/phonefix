<?php

namespace App\Services\MobileShop\Reports;

use Symfony\Component\HttpFoundation\StreamedResponse;

class GstExportService
{
    /**
     * Export GSTR-1 & GSTR-3B Compliant Tax Statement to CSV / Excel
     */
    public function exportGstCsv(array $data): StreamedResponse
    {
        $mobileSales = $data['mobileSales'];
        $accSales    = $data['accSales'];
        $purchases   = $data['purchases'];

        $filename = 'mobitrack_gstr1_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($mobileSales, $accSales, $purchases) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            // Section 1: GSTR-1 B2B Invoices (Taxable Sales to Registered Persons)
            fputcsv($handle, ['=== GSTR-1 TABLE 4: B2B INVOICES (Sales to GST Registered Customers) ===']);
            fputcsv($handle, [
                'GSTIN/UIN of Recipient',
                'Receiver Name',
                'Invoice Number',
                'Invoice Date',
                'Invoice Value (INR)',
                'Place of Supply',
                'Reverse Charge',
                'Applicable % of Tax Rate',
                'Taxable Value (INR)',
                'Central Tax Amount (CGST)',
                'State Tax Amount (SGST)',
                'Integrated Tax Amount (IGST)',
            ]);

            $b2bCount = 0;
            foreach ($mobileSales as $s) {
                if (empty(trim($s->customer_gstin ?? ''))) {
                    continue;
                }
                $b2bCount++;
                $taxable = max(0, (float) $s->total_amount - ((float) $s->cgst_amount + (float) $s->sgst_amount + (float) $s->igst_amount));
                fputcsv($handle, [
                    strtoupper(trim($s->customer_gstin)),
                    $s->customer_name ?? '',
                    $s->invoice_number,
                    date('d-M-Y', strtotime($s->created_at)),
                    number_format((float) $s->total_amount, 2, '.', ''),
                    ($s->tax_type === 'inter_state') ? 'Other State' : '09-Uttar Pradesh',
                    'N',
                    (float) ($s->tax_rate ?? 18),
                    number_format($taxable, 2, '.', ''),
                    number_format((float) $s->cgst_amount, 2, '.', ''),
                    number_format((float) $s->sgst_amount, 2, '.', ''),
                    number_format((float) $s->igst_amount, 2, '.', ''),
                ]);
            }
            foreach ($accSales as $s) {
                if (empty(trim($s->customer_gstin ?? ''))) {
                    continue;
                }
                $b2bCount++;
                $taxable = (float) ($s->subtotal ?? ($s->total_amount - ((float) $s->cgst_amount + (float) $s->sgst_amount + (float) $s->igst_amount)));
                fputcsv($handle, [
                    strtoupper(trim($s->customer_gstin)),
                    $s->customer_name ?? '',
                    $s->invoice_number,
                    date('d-M-Y', strtotime($s->created_at)),
                    number_format((float) $s->total_amount, 2, '.', ''),
                    ((float) $s->igst_amount > 0) ? 'Other State' : '09-Uttar Pradesh',
                    'N',
                    (float) ($s->tax_rate ?? 18),
                    number_format($taxable, 2, '.', ''),
                    number_format((float) $s->cgst_amount, 2, '.', ''),
                    number_format((float) $s->sgst_amount, 2, '.', ''),
                    number_format((float) $s->igst_amount, 2, '.', ''),
                ]);
            }
            if ($b2bCount === 0) {
                fputcsv($handle, ['No B2B registered sales recorded in this period']);
            }

            fputcsv($handle, []);
            fputcsv($handle, []);

            // Section 2: GSTR-1 B2C Summary (Consumers & Unregistered Persons)
            fputcsv($handle, ['=== GSTR-1 TABLE 7: B2C (OTHERS) SUMMARY (Consumer / Retail Sales) ===']);
            fputcsv($handle, [
                'Type',
                'Place of Supply',
                'Applicable Rate (%)',
                'Total Taxable Value (INR)',
                'Central Tax (CGST)',
                'State Tax (SGST)',
                'Integrated Tax (IGST)',
                'Total Invoiced Value (INR)',
            ]);

            $intraB2cTaxable = 0; $intraB2cCgst = 0; $intraB2cSgst = 0; $intraB2cTotal = 0;
            $interB2cTaxable = 0; $interB2cIgst = 0; $interB2cTotal = 0;

            foreach ($mobileSales as $s) {
                if (!empty(trim($s->customer_gstin ?? ''))) {
                    continue;
                }
                $tax = (float) ($s->cgst_amount + $s->sgst_amount + $s->igst_amount);
                $taxable = max(0, (float) $s->total_amount - $tax);
                if ($s->tax_type === 'inter_state') {
                    $interB2cTaxable += $taxable;
                    $interB2cIgst += (float) $s->igst_amount;
                    $interB2cTotal += (float) $s->total_amount;
                } else {
                    $intraB2cTaxable += $taxable;
                    $intraB2cCgst += (float) $s->cgst_amount;
                    $intraB2cSgst += (float) $s->sgst_amount;
                    $intraB2cTotal += (float) $s->total_amount;
                }
            }
            foreach ($accSales as $s) {
                if (!empty(trim($s->customer_gstin ?? ''))) {
                    continue;
                }
                $taxable = (float) ($s->subtotal ?? ($s->total_amount - ((float) $s->cgst_amount + (float) $s->sgst_amount + (float) $s->igst_amount)));
                if ((float) $s->igst_amount > 0) {
                    $interB2cTaxable += $taxable;
                    $interB2cIgst += (float) $s->igst_amount;
                    $interB2cTotal += (float) $s->total_amount;
                } else {
                    $intraB2cTaxable += $taxable;
                    $intraB2cCgst += (float) $s->cgst_amount;
                    $intraB2cSgst += (float) $s->sgst_amount;
                    $intraB2cTotal += (float) $s->total_amount;
                }
            }

            fputcsv($handle, [
                'Intra-State Retail (B2C)',
                '09-Uttar Pradesh',
                '18.00',
                number_format($intraB2cTaxable, 2, '.', ''),
                number_format($intraB2cCgst, 2, '.', ''),
                number_format($intraB2cSgst, 2, '.', ''),
                '0.00',
                number_format($intraB2cTotal, 2, '.', ''),
            ]);

            fputcsv($handle, [
                'Inter-State Retail (B2C)',
                'Other State',
                '18.00',
                number_format($interB2cTaxable, 2, '.', ''),
                '0.00',
                '0.00',
                number_format($interB2cIgst, 2, '.', ''),
                number_format($interB2cTotal, 2, '.', ''),
            ]);

            fputcsv($handle, []);
            fputcsv($handle, []);

            // Section 3: GSTR-1 HSN Summary
            fputcsv($handle, ['=== GSTR-1 TABLE 12: HSN-WISE SUMMARY OF OUTWARD SUPPLIES ===']);
            fputcsv($handle, [
                'HSN Code',
                'Description',
                'UQC',
                'Total Quantity',
                'Total Value (INR)',
                'Taxable Value (INR)',
                'Integrated Tax (IGST)',
                'Central Tax (CGST)',
                'State/UT Tax (SGST)',
            ]);

            $mobTaxable = (float) $mobileSales->sum(fn($m) => max(0, $m->total_amount - ($m->cgst_amount + $m->sgst_amount + $m->igst_amount)));
            fputcsv($handle, [
                '8517',
                'Telephones for cellular networks / Smart Handsets',
                'NOS',
                $mobileSales->count(),
                number_format((float) $mobileSales->sum('total_amount'), 2, '.', ''),
                number_format($mobTaxable, 2, '.', ''),
                number_format((float) $mobileSales->sum('igst_amount'), 2, '.', ''),
                number_format((float) $mobileSales->sum('cgst_amount'), 2, '.', ''),
                number_format((float) $mobileSales->sum('sgst_amount'), 2, '.', ''),
            ]);

            $accTaxable = (float) $accSales->sum(fn($a) => (float) ($a->subtotal ?? ($a->total_amount - ($a->cgst_amount + $a->sgst_amount + $a->igst_amount))));
            fputcsv($handle, [
                '85177090',
                'Parts & Accessories of Cellular Phones',
                'PCS',
                $accSales->count(),
                number_format((float) $accSales->sum('total_amount'), 2, '.', ''),
                number_format($accTaxable, 2, '.', ''),
                number_format((float) $accSales->sum('igst_amount'), 2, '.', ''),
                number_format((float) $accSales->sum('cgst_amount'), 2, '.', ''),
                number_format((float) $accSales->sum('sgst_amount'), 2, '.', ''),
            ]);

            fputcsv($handle, []);
            fputcsv($handle, []);

            // Section 4: GSTR-3B Tax Summary
            fputcsv($handle, ['=== GSTR-3B SUMMARY (OUTWARD TAX LIABILITY & INPUT TAX CREDIT ITC) ===']);
            fputcsv($handle, [
                'Nature of Supplies',
                'Total Taxable Value (INR)',
                'Integrated Tax (IGST)',
                'Central Tax (CGST)',
                'State/UT Tax (SGST)',
                'Total Tax (INR)',
            ]);

            $outTaxable = $mobTaxable + $accTaxable;
            $outCgst = (float) ($mobileSales->sum('cgst_amount') + $accSales->sum('cgst_amount'));
            $outSgst = (float) ($mobileSales->sum('sgst_amount') + $accSales->sum('sgst_amount'));
            $outIgst = (float) ($mobileSales->sum('igst_amount') + $accSales->sum('igst_amount'));
            $outTotal = $outCgst + $outSgst + $outIgst;

            fputcsv($handle, [
                '3.1 Outward Taxable Supplies (Sales)',
                number_format($outTaxable, 2, '.', ''),
                number_format($outIgst, 2, '.', ''),
                number_format($outCgst, 2, '.', ''),
                number_format($outSgst, 2, '.', ''),
                number_format($outTotal, 2, '.', ''),
            ]);

            $itcTaxable = (float) $purchases->sum('subtotal');
            $itcCgst = (float) $purchases->sum('cgst_amount');
            $itcSgst = (float) $purchases->sum('sgst_amount');
            $itcIgst = (float) $purchases->sum('igst_amount');
            $itcTotal = $itcCgst + $itcSgst + $itcIgst;

            fputcsv($handle, [
                '4.0 Eligible Input Tax Credit (ITC from Purchases)',
                number_format($itcTaxable, 2, '.', ''),
                number_format($itcIgst, 2, '.', ''),
                number_format($itcCgst, 2, '.', ''),
                number_format($itcSgst, 2, '.', ''),
                number_format($itcTotal, 2, '.', ''),
            ]);

            fputcsv($handle, [
                'NET GST PAYABLE (Output Tax - ITC)',
                '-',
                number_format(max(0, $outIgst - $itcIgst), 2, '.', ''),
                number_format(max(0, $outCgst - $itcCgst), 2, '.', ''),
                number_format(max(0, $outSgst - $itcSgst), 2, '.', ''),
                number_format(max(0, $outTotal - $itcTotal), 2, '.', ''),
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

<?php

namespace App\Services\MobileShop\Purchase;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierPaymentService
{
    /**
     * Record Supplier Payment / Advance / Settlement
     */
    public function recordSupplierPayment(int $companyId, Request $request): array
    {
        $request->validate([
            'supplier_id'  => 'required|exists:ms_suppliers,id',
            'amount'       => 'required|numeric|min:1',
            'payment_mode' => 'required|in:cash,bank_transfer,cheque,upi',
        ]);

        return DB::transaction(function () use ($request, $companyId) {
            $supplierId = $request->supplier_id;
            $paymentAmount = (float) $request->amount;
            $poId = $request->purchase_order_id;

            $wallet = DB::table('ms_supplier_credit_wallets')
                ->where('company_id', $companyId)
                ->where('supplier_id', $supplierId)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                $walletId = DB::table('ms_supplier_credit_wallets')->insertGetId([
                    'company_id'     => $companyId,
                    'supplier_id'    => $supplierId,
                    'credit_balance' => 0.00,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
                $wallet = DB::table('ms_supplier_credit_wallets')->where('id', $walletId)->first();
            }

            if ($poId) {
                $po = DB::table('ms_purchase_orders')
                    ->where('company_id', $companyId)
                    ->where('id', $poId)
                    ->lockForUpdate()
                    ->first();

                if (!$po) {
                    return [
                        'success' => false,
                        'message' => 'Purchase order not found.',
                    ];
                }

                $applyToPO = min($paymentAmount, (float) $po->balance_due);
                $newPaid = (float) $po->amount_paid + $applyToPO;
                $newDue = (float) $po->balance_due - $applyToPO;
                $newStatus = ($newDue <= 0) ? 'paid' : 'partially_paid';

                DB::table('ms_purchase_orders')->where('id', $poId)->update([
                    'amount_paid' => $newPaid,
                    'balance_due' => $newDue,
                    'status'      => $newStatus,
                    'updated_at'  => now(),
                ]);

                DB::table('ms_supplier_payments')->insert([
                    'company_id'        => $companyId,
                    'supplier_id'       => $supplierId,
                    'purchase_order_id' => $poId,
                    'amount'            => $applyToPO,
                    'payment_date'      => now()->toDateString(),
                    'mode'              => $request->payment_mode,
                    'reference_no'      => $request->reference_no,
                    'remarks'           => $request->remarks,
                    'recorded_by'       => auth()->id(),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                $excess = $paymentAmount - $applyToPO;
                if ($excess > 0) {
                    $newCredit = (float) $wallet->credit_balance + $excess;
                    DB::table('ms_supplier_credit_wallets')->where('supplier_id', $supplierId)->update([
                        'credit_balance' => $newCredit,
                        'updated_at'     => now(),
                    ]);

                    DB::table('ms_supplier_credit_transactions')->insert([
                        'supplier_id'   => $supplierId,
                        'txn_type'      => 'credit_added',
                        'amount'        => $excess,
                        'related_po_id' => $poId,
                        'balance_after' => $newCredit,
                        'remarks'       => "Excess overpayment on PO #{$po->po_number} credited to wallet",
                        'txn_date'      => now(),
                    ]);
                }
            } else {
                $newCredit = (float) $wallet->credit_balance + $paymentAmount;
                DB::table('ms_supplier_credit_wallets')->where('supplier_id', $supplierId)->update([
                    'credit_balance' => $newCredit,
                    'updated_at'     => now(),
                ]);

                DB::table('ms_supplier_credit_transactions')->insert([
                    'supplier_id'   => $supplierId,
                    'txn_type'      => 'credit_added',
                    'amount'        => $paymentAmount,
                    'related_po_id' => null,
                    'balance_after' => $newCredit,
                    'remarks'       => "Direct advance payment to supplier wallet",
                    'txn_date'      => now(),
                ]);
            }

            return [
                'success' => true,
                'message' => 'Supplier payment successfully logged and ledger updated!',
            ];
        });
    }

    /**
     * Update Supplier details and/or adjust prepaid wallet balance
     */
    public function updateSupplier(int $companyId, Request $request): array
    {
        $request->validate([
            'supplier_id'      => 'required|exists:ms_suppliers,id',
            'name'             => 'required|string|max:191',
            'contact_person'   => 'nullable|string|max:150',
            'phone'            => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:191',
            'gstin'            => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:255',
            'credit_balance'   => 'nullable|numeric|min:0',
            'adjustment_notes' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($request, $companyId) {
            $supplier = DB::table('ms_suppliers')
                ->where('company_id', $companyId)
                ->where('id', $request->supplier_id)
                ->first();

            if (!$supplier) {
                return [
                    'success' => false,
                    'message' => 'Supplier not found.',
                ];
            }

            DB::table('ms_suppliers')->where('id', $supplier->id)->update([
                'name'           => trim($request->name),
                'contact_person' => $request->contact_person,
                'phone'          => $request->phone,
                'email'          => $request->email,
                'gstin'          => $request->gstin,
                'address'        => $request->address,
                'updated_at'     => now(),
            ]);

            $deltaMsg = "";
            if ($request->has('credit_balance') && $request->credit_balance !== null) {
                $wallet = DB::table('ms_supplier_credit_wallets')
                    ->where('company_id', $companyId)
                    ->where('supplier_id', $supplier->id)
                    ->lockForUpdate()
                    ->first();

                $newBal = round((float) $request->credit_balance, 2);
                $oldBal = $wallet ? (float) $wallet->credit_balance : 0.00;
                $delta = round($newBal - $oldBal, 2);

                if (!$wallet) {
                    DB::table('ms_supplier_credit_wallets')->insert([
                        'company_id'     => $companyId,
                        'supplier_id'    => $supplier->id,
                        'credit_balance' => $newBal,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                } else {
                    DB::table('ms_supplier_credit_wallets')->where('id', $wallet->id)->update([
                        'credit_balance' => $newBal,
                        'updated_at'     => now(),
                    ]);
                }

                if (abs($delta) > 0.001) {
                    $txnType = $delta > 0 ? 'credit_added' : 'credit_applied';
                    DB::table('ms_supplier_credit_transactions')->insert([
                        'supplier_id'   => $supplier->id,
                        'txn_type'      => $txnType,
                        'amount'        => abs($delta),
                        'balance_after' => $newBal,
                        'txn_date'      => now(),
                        'remarks'       => $request->adjustment_notes ?: ('Manual wallet adjustment by ' . auth()->user()->name),
                    ]);
                    $deltaMsg = " Prepaid wallet updated to ₹" . number_format($newBal, 2) . ".";
                }
            }

            return [
                'success' => true,
                'message' => "Supplier '{$request->name}' ledger details updated.{$deltaMsg}",
            ];
        });
    }
}

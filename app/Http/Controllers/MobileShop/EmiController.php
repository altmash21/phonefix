<?php

namespace App\Http\Controllers\MobileShop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmiController extends BaseMobileShopController
{
    /**
     * EMI Companies Ledger — list providers, deposits, usage & balances
     */
    public function emiLedger()
    {
        abort_unless(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized access to EMI ledger.');

        $companyId = $this->getCompanyId();
        $providers = DB::table('ms_emi_providers')->where('company_id', $companyId)->orderBy('name')->get();

        $transactions = DB::table('ms_emi_provider_transactions')
            ->join('ms_emi_providers', 'ms_emi_provider_transactions.emi_provider_id', '=', 'ms_emi_providers.id')
            ->where('ms_emi_providers.company_id', $companyId)
            ->select('ms_emi_provider_transactions.*', 'ms_emi_providers.name as provider_name')
            ->orderBy('ms_emi_provider_transactions.id', 'desc')
            ->limit(100)
            ->get();

        return view('mobileshop.emi_ledger', compact('providers', 'transactions'));
    }

    /**
     * Add new EMI company (finance partner)
     */
    public function storeEmiProvider(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized action.');

        $request->validate([
            'name'                  => 'required|string|max:150',
            'code'                  => 'nullable|string|max:50',
            'contact_person'        => 'nullable|string|max:150',
            'phone'                 => 'nullable|string|max:20',
            'opening_balance'       => 'nullable|numeric|min:0',
            'processing_fee_flat'   => 'nullable|numeric|min:0',
            'processing_fee_pct'    => 'nullable|numeric|min:0|max:100',
            'default_tenure_months' => 'nullable|integer|min:1|max:60',
            'interest_rate_pct'     => 'nullable|numeric|min:0|max:100',
            'notes'                 => 'nullable|string|max:500',
        ]);

        $companyId = $this->getCompanyId();
        $opening = round((float) ($request->opening_balance ?? 0), 2);

        $providerId = DB::table('ms_emi_providers')->insertGetId([
            'company_id'            => $companyId,
            'name'                  => trim($request->name),
            'code'                  => $request->code,
            'contact_person'        => $request->contact_person,
            'phone'                 => $request->phone,
            'advance_balance'       => $opening,
            'processing_fee_flat'   => (float) ($request->processing_fee_flat ?? 0.00),
            'processing_fee_pct'    => (float) ($request->processing_fee_pct ?? 0.00),
            'default_tenure_months' => (int) ($request->default_tenure_months ?? 12),
            'interest_rate_pct'     => (float) ($request->interest_rate_pct ?? 0.00),
            'notes'                 => $request->notes,
            'enabled'               => 1,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        if ($opening > 0) {
            DB::table('ms_emi_provider_transactions')->insert([
                'emi_provider_id' => $providerId, 'type' => 'advance_deposit',
                'amount' => $opening, 'balance_after' => $opening,
                'notes' => 'Opening advance deposit', 'created_at' => now(),
            ]);
        }

        return redirect()->route('mobileshop.emi.ledger', ['company_id' => $companyId])->with('success', "EMI company '{$request->name}' added to ledger.");
    }

    /**
     * Deposit money into an EMI company's advance pool
     */
    public function recordEmiDeposit(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized action.');

        $request->validate([
            'emi_provider_id' => 'required|exists:ms_emi_providers,id',
            'amount' => 'required|numeric|min:1',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($request, $companyId) {
            $provider = DB::table('ms_emi_providers')->where('company_id', $companyId)->where('id', $request->emi_provider_id)->lockForUpdate()->first();
            if (!$provider) {
                return redirect()->back()->with('error', 'EMI provider not found.');
            }
            $amount = round((float) $request->amount, 2);
            $newBalance = (float) $provider->advance_balance + $amount;

            DB::table('ms_emi_providers')->where('id', $provider->id)->update([
                'advance_balance' => $newBalance, 'updated_at' => now(),
            ]);
            DB::table('ms_emi_provider_transactions')->insert([
                'emi_provider_id' => $provider->id, 'type' => 'advance_deposit',
                'amount' => $amount, 'balance_after' => $newBalance,
                'reference_no' => $request->reference_no,
                'notes' => $request->notes ?: 'Advance deposit to finance pool',
                'created_at' => now(),
            ]);

            return redirect()->route('mobileshop.emi.ledger', ['company_id' => $companyId])->with('success', "₹" . number_format($amount, 2) . " deposited to {$provider->name} advance pool.");
        });
    }

    /**
     * Update EMI Provider details and/or adjust advance balance
     */
    public function updateEmiProvider(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized action.');

        $request->validate([
            'emi_provider_id'       => 'required|exists:ms_emi_providers,id',
            'name'                  => 'required|string|max:150',
            'code'                  => 'nullable|string|max:50',
            'contact_person'        => 'nullable|string|max:150',
            'phone'                 => 'nullable|string|max:20',
            'advance_balance'       => 'nullable|numeric|min:0',
            'processing_fee_flat'   => 'nullable|numeric|min:0',
            'processing_fee_pct'    => 'nullable|numeric|min:0|max:100',
            'default_tenure_months' => 'nullable|integer|min:1|max:60',
            'interest_rate_pct'     => 'nullable|numeric|min:0|max:100',
            'notes'                 => 'nullable|string|max:500',
            'adjustment_notes'      => 'nullable|string|max:255',
            'enabled'               => 'nullable',
        ]);

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($request, $companyId) {
            $provider = DB::table('ms_emi_providers')->where('company_id', $companyId)->where('id', $request->emi_provider_id)->lockForUpdate()->first();
            if (!$provider) {
                return redirect()->back()->with('error', 'EMI provider not found.');
            }

            $currentBalance = (float) $provider->advance_balance;
            $newBalance = $request->has('advance_balance') && $request->advance_balance !== null
                ? round((float) $request->advance_balance, 2)
                : $currentBalance;
            $delta = round($newBalance - $currentBalance, 2);

            $updateData = [
                'name'                  => trim($request->name),
                'code'                  => $request->code,
                'contact_person'        => $request->contact_person,
                'phone'                 => $request->phone,
                'advance_balance'       => $newBalance,
                'enabled'               => $request->has('enabled') ? (int) $request->enabled : $provider->enabled,
                'updated_at'            => now(),
            ];

            if ($request->has('processing_fee_flat')) {
                $updateData['processing_fee_flat'] = (float) $request->processing_fee_flat;
            }
            if ($request->has('processing_fee_pct')) {
                $updateData['processing_fee_pct'] = (float) $request->processing_fee_pct;
            }
            if ($request->has('default_tenure_months')) {
                $updateData['default_tenure_months'] = (int) $request->default_tenure_months;
            }
            if ($request->has('interest_rate_pct')) {
                $updateData['interest_rate_pct'] = (float) $request->interest_rate_pct;
            }
            if ($request->has('notes')) {
                $updateData['notes'] = $request->notes;
            }

            DB::table('ms_emi_providers')->where('id', $provider->id)->update($updateData);

            if (abs($delta) > 0.001) {
                $type = $delta > 0 ? 'advance_deposit' : 'settlement';
                DB::table('ms_emi_provider_transactions')->insert([
                    'emi_provider_id' => $provider->id,
                    'type' => $type,
                    'amount' => abs($delta),
                    'balance_after' => $newBalance,
                    'reference_no' => 'MANUAL-ADJ',
                    'notes' => $request->adjustment_notes ?: ('Manual balance adjustment by ' . auth()->user()->name),
                    'created_at' => now(),
                ]);
            }

            return redirect()->back()->with('success', "EMI Partner '{$request->name}' updated successfully." . (abs($delta) > 0 ? " Balance adjusted to ₹" . number_format($newBalance, 2) . "." : ""));
        });
    }
}

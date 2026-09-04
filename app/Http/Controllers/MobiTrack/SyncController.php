<?php

namespace App\Http\Controllers\MobiTrack;

use App\Abstracts\Http\Controller;
use App\Models\MSSyncQueue;
use App\Models\MSSyncOffset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    public function pending(Request $request)
    {
        $limit = (int) $request->query('limit', 50);
        $items = MSSyncQueue::where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
        return response()->json(['success' => true, 'data' => $items]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'operations' => 'required|array|max:100',
            'operations.*.action' => 'required|in:create,update,delete',
            'operations.*.model' => 'required|string|max=100',
            'operations.*.record_id' => 'nullable|integer',
            'operations.*.payload' => 'required|string',
            'operations.*.sync_key' => 'nullable|string|max=100',
        ]);

        $results = [];
        DB::beginTransaction();
        try {
            foreach ($request->operations as $op) {
                try {
                    $results[] = $this->applyOperation($op);
                } catch (\Throwable $e) {
                    if (!empty($op['sync_key'])) {
                        MSSyncQueue::where('sync_key', $op['sync_key'])
                            ->update([
                                'status' => 'failed',
                                'error_message' => $e->getMessage(),
                                'last_attempt_at' => now(),
                                'attempts' => DB::raw('attempts + 1'),
                            ]);
                    }
                    $results[] = ['action' => $op['action'], 'status' => 'error', 'message' => $e->getMessage()];
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
        return response()->json(['success' => true, 'data' => $results]);
    }

    public function offset(Request $request)
    {
        $request->validate([
            'offsets' => 'required|array',
            'offsets.*.model' => 'required|string',
            'offsets.*.last_synced_id' => 'required|integer',
        ]);
        foreach ($request->offsets as $item) {
            MSSyncOffset::updateOrInsert(
                ['model' => $item['model']],
                ['last_synced_id' => $item['last_synced_id'], 'last_synced_at' => now()]
            );
        }
        return response()->json(['success' => true]);
    }

    public function status()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'pending_count' => MSSyncQueue::where('status', 'pending')->count(),
                'failed_count' => MSSyncQueue::where('status', 'failed')->count(),
                'last_sync_at' => MSSyncOffset::where('model', 'ms_mobile_sales')->value('last_synced_at'),
            ],
        ]);
    }

    protected function applyOperation(array $op)
    {
        $payload = json_decode($op['payload'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid payload JSON');
        }
        $payload = $this->sanitize($op['model'], $payload);
        $serverId = null;

        switch ($op['action']) {
            case 'create':
                $cols = [];
                $vals = [];
                foreach ($payload as $k => $v) {
                    $cols[] = '`' . str_replace('`', '``', $k) . '`';
                    $vals[] = DB::connection()->getPdo()->quote($v ?? '');
                }
                $sql = "INSERT INTO `{$op['model']}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ")";
                DB::statement($sql);
                $serverId = (int) DB::connection()->getPdo()->lastInsertId();
                break;
            case 'update':
                if (empty($op['record_id'])) {
                    throw new \InvalidArgumentException('record_id required for update');
                }
                $set = [];
                foreach ($payload as $key => $value) {
                    $set[] = "`{$key}` = " . DB::connection()->getPdo()->quote($value ?? '');
                }
                $sql = "UPDATE `{$op['model']}` SET " . implode(', ', $set) . " WHERE `id` = {$op['record_id']}";
                DB::statement($sql);
                $serverId = $op['record_id'];
                break;
            case 'delete':
                if (empty($op['record_id'])) {
                    throw new \InvalidArgumentException('record_id required for delete');
                }
                DB::statement("DELETE FROM `{$op['model']}` WHERE `id` = {$op['record_id']}");
                $serverId = $op['record_id'];
                break;
        }

        MSSyncQueue::where('sync_key', $op['sync_key'] ?? '')
            ->update(['status' => 'uploaded', 'record_id' => $serverId, 'synced_at' => now()]);

        MSSyncOffset::updateOrInsert(
            ['model' => $op['model']],
            ['last_synced_id' => max($serverId ?? 0, MSSyncOffset::where('model', $op['model'])->value('last_synced_id') ?? 0), 'last_synced_at' => now()]
        );

        return ['action' => $op['action'], 'model' => $op['model'], 'status' => 'ok', 'server_id' => $serverId, 'sync_key' => $op['sync_key'] ?? ''];
    }

    protected function sanitize(string $model, array $payload): array
    {
        $whitelist = [
            'ms_mobile_sales' => ['company_id','customer_id','invoice_number','device_id','sale_price','tax_rate','tax_type','cgst_amount','sgst_amount','igst_amount','total_amount','amount_paid','udhari_amount','payment_mode','emi_provider_id','emi_loan_no','emi_downpayment','emi_financed_amount','emi_monthly_amount','emi_tenure_months','sold_by','status'],
            'ms_accessory_sales' => ['company_id','customer_id','invoice_number','item_id','sale_price','tax_rate','cgst_amount','sgst_amount','igst_amount','total_amount','amount_paid','payment_mode','sold_by','status'],
            'ms_mobile_devices' => ['company_id','type','brand','model','color','ram','storage','imei_1','imei_2','serial_no','hsn_code','purchase_cost','selling_price','min_stock_alert','box_photo_path','status','condition_grade','battery_health','customer_buyback_name','customer_buyback_phone','customer_buyback_id_proof','checklist_notes'],
            'ms_accessory_sale_items' => ['company_id','sale_id','part_id','qty','unit_cost','total_cost','tax_rate','cgst_amount','sgst_amount','igst_amount','total_amount'],
            'ms_purchase_orders' => ['company_id','supplier_id','po_number','order_date','tax_type','subtotal','cgst_amount','sgst_amount','igst_amount','total_amount','amount_paid','balance_due','status','created_by'],
            'ms_supplier_payments' => ['company_id','supplier_id','purchase_order_id','amount','payment_date','mode','reference_no','recorded_by'],
            'ms_repair_tickets' => ['company_id','customer_id','brand','model','imei_serial','reported_faults','physical_condition','technician_id','status','estimated_cost','labor_charge','parts_cost','total_amount','advance_paid','balance_due'],
            'ms_customer_khata_transactions' => ['company_id','customer_id','type','sale_id','amount','balance_after','payment_mode','reference_no','remarks','recorded_by'],
        ];
        $allowed = $whitelist[$model] ?? [];
        $out = [];
        foreach ($payload as $k => $v) {
            if (in_array($k, $allowed, true)) {
                $out[$k] = $v;
            }
        }
        return $out;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function addIndexSafely(string $table, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            $pfx = DB::getTablePrefix();
            $colList = '`' . implode('`, `', $columns) . '`';
            DB::statement("CREATE INDEX `{$indexName}` ON `{$pfx}{$table}` ({$colList})");
        } catch (\Throwable $e) {
            // Index already exists or driver unsupported — continue safely
        }
    }

    public function up(): void
    {
        // 1. Devices IMEI & Brand lookups
        $this->addIndexSafely('ms_mobile_devices', ['company_id', 'imei_1'], 'idx_ms_devices_comp_imei1');
        $this->addIndexSafely('ms_mobile_devices', ['company_id', 'imei_2'], 'idx_ms_devices_comp_imei2');
        $this->addIndexSafely('ms_mobile_devices', ['company_id', 'brand', 'status'], 'idx_ms_devices_comp_brand_status');

        // 2. Accessories categories & inventory
        $this->addIndexSafely('ms_accessories', ['company_id', 'category_id'], 'idx_ms_acc_comp_cat');
        $this->addIndexSafely('ms_accessories', ['company_id', 'stock_qty'], 'idx_ms_acc_comp_stock');

        // 3. Purchase Orders
        $this->addIndexSafely('ms_purchase_orders', ['company_id', 'po_number'], 'idx_ms_po_comp_ponum');
        $this->addIndexSafely('ms_purchase_orders', ['company_id', 'status', 'order_date'], 'idx_ms_po_comp_status_date');

        // 4. Khata Accounts
        $this->addIndexSafely('ms_khata_accounts', ['company_id', 'customer_id'], 'idx_ms_khata_comp_cust');
    }

    public function down(): void
    {
        $indexes = [
            'ms_mobile_devices'  => ['idx_ms_devices_comp_imei1', 'idx_ms_devices_comp_imei2', 'idx_ms_devices_comp_brand_status'],
            'ms_accessories'     => ['idx_ms_acc_comp_cat', 'idx_ms_acc_comp_stock'],
            'ms_purchase_orders' => ['idx_ms_po_comp_ponum', 'idx_ms_po_comp_status_date'],
            'ms_khata_accounts'  => ['idx_ms_khata_comp_cust'],
        ];

        $pfx = DB::getTablePrefix();
        foreach ($indexes as $table => $tableIndexes) {
            if (Schema::hasTable($table)) {
                foreach ($tableIndexes as $idx) {
                    try {
                        DB::statement("DROP INDEX `{$idx}` ON `{$pfx}{$table}`");
                    } catch (\Throwable $e) {}
                }
            }
        }
    }
};

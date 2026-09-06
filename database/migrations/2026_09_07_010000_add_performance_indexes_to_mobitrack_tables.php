<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Helper to safely add an index without failing if it already exists
     */
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

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Mobile Devices (Intake, Stock Register, Dashboard stats)
        $this->addIndexSafely('ms_mobile_devices', ['company_id', 'type', 'status'], 'idx_ms_devices_comp_type_status');
        $this->addIndexSafely('ms_mobile_devices', ['company_id', 'created_at'], 'idx_ms_devices_comp_created');

        // 2. Mobile Sales (POS, Invoices, Monthly Aggregates)
        $this->addIndexSafely('ms_mobile_sales', ['company_id', 'status', 'created_at'], 'idx_ms_sales_comp_status_created');
        $this->addIndexSafely('ms_mobile_sales', ['company_id', 'udhari_amount'], 'idx_ms_sales_comp_udhari');

        // 3. Accessory Sales
        $this->addIndexSafely('ms_accessory_sales', ['company_id', 'status', 'created_at'], 'idx_ms_acc_sales_comp_status_created');

        // 4. Parts Inventory (Catalog filtering, Low stock alerts)
        $this->addIndexSafely('ms_parts_inventory', ['company_id', 'category'], 'idx_ms_parts_comp_category');
        $this->addIndexSafely('ms_parts_inventory', ['company_id', 'stock_qty'], 'idx_ms_parts_comp_stock');

        // 5. Parts Inventory History (Restock & sales ledger tracking)
        $this->addIndexSafely('ms_parts_inventory_history', ['part_id', 'type', 'created_at'], 'idx_ms_parts_hist_part_type_created');

        // 6. Customers (Khata / Udhari Balance fast lookup)
        $this->addIndexSafely('ms_customers', ['company_id', 'udhari_balance'], 'idx_ms_cust_comp_udhari');

        // 7. Repair Tickets (Open jobs & delivered jobs)
        $this->addIndexSafely('ms_repair_tickets', ['company_id', 'status', 'created_at'], 'idx_ms_repairs_comp_status_created');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = [
            'ms_mobile_devices'         => ['idx_ms_devices_comp_type_status', 'idx_ms_devices_comp_created'],
            'ms_mobile_sales'           => ['idx_ms_sales_comp_status_created', 'idx_ms_sales_comp_udhari'],
            'ms_accessory_sales'        => ['idx_ms_acc_sales_comp_status_created'],
            'ms_parts_inventory'        => ['idx_ms_parts_comp_category', 'idx_ms_parts_comp_stock'],
            'ms_parts_inventory_history'=> ['idx_ms_parts_hist_part_type_created'],
            'ms_customers'              => ['idx_ms_cust_comp_udhari'],
            'ms_repair_tickets'         => ['idx_ms_repairs_comp_status_created'],
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

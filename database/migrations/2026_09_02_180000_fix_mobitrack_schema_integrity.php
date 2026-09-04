<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pfx = DB::getTablePrefix();

        // 1. Modify ms_mobile_sales.status to ensure 'voided' is supported
        if (Schema::hasTable('ms_mobile_sales')) {
            try {
                DB::statement("ALTER TABLE `{$pfx}ms_mobile_sales` MODIFY COLUMN status ENUM('completed', 'partially_returned', 'returned', 'cancelled', 'voided') DEFAULT 'completed'");
            } catch (\Throwable $e) {
                // Ignore if already modified or SQLite
            }
        }

        // 2. Modify ms_parts_inventory.category from strict 8-value enum to VARCHAR(50) so all 12 part category slugs work
        if (Schema::hasTable('ms_parts_inventory')) {
            try {
                DB::statement("ALTER TABLE `{$pfx}ms_parts_inventory` MODIFY COLUMN category VARCHAR(50) NOT NULL DEFAULT 'general_accessory'");
            } catch (\Throwable $e) {
                // Ignore if already VARCHAR
            }
        }

        // 3. Drop global unique constraints if they exist and replace with composite (company_id, number)
        // ms_mobile_sales: invoice_number
        if (Schema::hasTable('ms_mobile_sales')) {
            try {
                Schema::table('ms_mobile_sales', function (Blueprint $table) {
                    $table->dropUnique(['invoice_number']);
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('ms_mobile_sales', function (Blueprint $table) {
                    $table->unique(['company_id', 'invoice_number'], 'uq_ms_sales_comp_inv');
                });
            } catch (\Throwable $e) {}
        }

        // ms_repair_tickets: ticket_number
        if (Schema::hasTable('ms_repair_tickets')) {
            try {
                Schema::table('ms_repair_tickets', function (Blueprint $table) {
                    $table->dropUnique(['ticket_number']);
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('ms_repair_tickets', function (Blueprint $table) {
                    $table->unique(['company_id', 'ticket_number'], 'uq_ms_tickets_comp_no');
                });
            } catch (\Throwable $e) {}
        }

        // ms_purchase_orders: po_number
        if (Schema::hasTable('ms_purchase_orders')) {
            try {
                Schema::table('ms_purchase_orders', function (Blueprint $table) {
                    $table->dropUnique(['po_number']);
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('ms_purchase_orders', function (Blueprint $table) {
                    $table->unique(['company_id', 'po_number'], 'uq_ms_pos_comp_no');
                });
            } catch (\Throwable $e) {}
        }

        // ms_goods_receipts: grn_number
        if (Schema::hasTable('ms_goods_receipts')) {
            try {
                Schema::table('ms_goods_receipts', function (Blueprint $table) {
                    $table->dropUnique(['grn_number']);
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('ms_goods_receipts', function (Blueprint $table) {
                    $table->unique(['company_id', 'grn_number'], 'uq_ms_grns_comp_no');
                });
            } catch (\Throwable $e) {}
        }

        // ms_sales_returns: return_number
        if (Schema::hasTable('ms_sales_returns')) {
            try {
                Schema::table('ms_sales_returns', function (Blueprint $table) {
                    $table->dropUnique(['return_number']);
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('ms_sales_returns', function (Blueprint $table) {
                    $table->unique(['company_id', 'return_number'], 'uq_ms_returns_comp_no');
                });
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};

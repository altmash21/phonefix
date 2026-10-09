<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('ms_accessory_sales')) {
            Schema::table('ms_accessory_sales', function (Blueprint $table) {
                if (!Schema::hasColumn('ms_accessory_sales', 'refund_amount')) {
                    $table->decimal('refund_amount', 15, 2)->default(0.00)->after('total_amount');
                }
                if (!Schema::hasColumn('ms_accessory_sales', 'net_amount')) {
                    $table->decimal('net_amount', 15, 2)->default(0.00)->after('refund_amount');
                }
                if (!Schema::hasColumn('ms_accessory_sales', 'return_details')) {
                    $table->json('return_details')->nullable()->after('void_reason');
                }
            });
        }

        if (Schema::hasTable('ms_accessory_sale_items')) {
            Schema::table('ms_accessory_sale_items', function (Blueprint $table) {
                if (!Schema::hasColumn('ms_accessory_sale_items', 'returned_qty')) {
                    $table->integer('returned_qty')->default(0)->after('quantity');
                }
                if (!Schema::hasColumn('ms_accessory_sale_items', 'returned_amount')) {
                    $table->decimal('returned_amount', 15, 2)->default(0.00)->after('returned_qty');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ms_accessory_sales')) {
            Schema::table('ms_accessory_sales', function (Blueprint $table) {
                if (Schema::hasColumn('ms_accessory_sales', 'return_details')) {
                    $table->dropColumn('return_details');
                }
                if (Schema::hasColumn('ms_accessory_sales', 'net_amount')) {
                    $table->dropColumn('net_amount');
                }
                if (Schema::hasColumn('ms_accessory_sales', 'refund_amount')) {
                    $table->dropColumn('refund_amount');
                }
            });
        }

        if (Schema::hasTable('ms_accessory_sale_items')) {
            Schema::table('ms_accessory_sale_items', function (Blueprint $table) {
                if (Schema::hasColumn('ms_accessory_sale_items', 'returned_amount')) {
                    $table->dropColumn('returned_amount');
                }
                if (Schema::hasColumn('ms_accessory_sale_items', 'returned_qty')) {
                    $table->dropColumn('returned_qty');
                }
            });
        }
    }
};

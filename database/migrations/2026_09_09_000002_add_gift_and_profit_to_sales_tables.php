<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ms_mobile_sales')) {
            Schema::table('ms_mobile_sales', function (Blueprint $table) {
                if (!Schema::hasColumn('ms_mobile_sales', 'gift_cost')) {
                    $table->decimal('gift_cost', 15, 2)->default(0)->after('total_amount');
                }
                if (!Schema::hasColumn('ms_mobile_sales', 'final_profit')) {
                    $table->decimal('final_profit', 15, 2)->default(0)->after('gift_cost');
                }
            });
        }

        if (Schema::hasTable('ms_sale_gifts')) {
            Schema::table('ms_sale_gifts', function (Blueprint $table) {
                $table->unsignedBigInteger('gift_id')->nullable()->change();
                if (!Schema::hasColumn('ms_sale_gifts', 'gift_name')) {
                    $table->string('gift_name')->nullable()->after('gift_id');
                }
                if (!Schema::hasColumn('ms_sale_gifts', 'purchase_cost')) {
                    $table->decimal('purchase_cost', 15, 2)->default(0)->after('gift_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ms_mobile_sales')) {
            Schema::table('ms_mobile_sales', function (Blueprint $table) {
                if (Schema::hasColumn('ms_mobile_sales', 'gift_cost')) {
                    $table->dropColumn('gift_cost');
                }
                if (Schema::hasColumn('ms_mobile_sales', 'final_profit')) {
                    $table->dropColumn('final_profit');
                }
            });
        }

        if (Schema::hasTable('ms_sale_gifts')) {
            Schema::table('ms_sale_gifts', function (Blueprint $table) {
                if (Schema::hasColumn('ms_sale_gifts', 'gift_name')) {
                    $table->dropColumn('gift_name');
                }
                if (Schema::hasColumn('ms_sale_gifts', 'purchase_cost')) {
                    $table->dropColumn('purchase_cost');
                }
            });
        }
    }
};

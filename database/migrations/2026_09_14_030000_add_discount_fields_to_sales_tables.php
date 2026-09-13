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
                if (!Schema::hasColumn('ms_mobile_sales', 'original_price')) {
                    $table->decimal('original_price', 15, 2)->default(0)->after('device_id');
                }
                if (!Schema::hasColumn('ms_mobile_sales', 'discount_amount')) {
                    $table->decimal('discount_amount', 15, 2)->default(0)->after('original_price');
                }
            });
        }

        if (Schema::hasTable('ms_accessory_sales')) {
            Schema::table('ms_accessory_sales', function (Blueprint $table) {
                if (!Schema::hasColumn('ms_accessory_sales', 'gross_total')) {
                    $table->decimal('gross_total', 15, 2)->default(0)->after('customer_id');
                }
                if (!Schema::hasColumn('ms_accessory_sales', 'discount_amount')) {
                    $table->decimal('discount_amount', 15, 2)->default(0)->after('gross_total');
                }
            });
        }

        if (Schema::hasTable('ms_accessory_sale_items')) {
            Schema::table('ms_accessory_sale_items', function (Blueprint $table) {
                if (!Schema::hasColumn('ms_accessory_sale_items', 'original_price')) {
                    $table->decimal('original_price', 15, 2)->default(0)->after('unit_price');
                }
                if (!Schema::hasColumn('ms_accessory_sale_items', 'discount_amount')) {
                    $table->decimal('discount_amount', 15, 2)->default(0)->after('original_price');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ms_mobile_sales')) {
            Schema::table('ms_mobile_sales', function (Blueprint $table) {
                if (Schema::hasColumn('ms_mobile_sales', 'original_price')) {
                    $table->dropColumn('original_price');
                }
                if (Schema::hasColumn('ms_mobile_sales', 'discount_amount')) {
                    $table->dropColumn('discount_amount');
                }
            });
        }

        if (Schema::hasTable('ms_accessory_sales')) {
            Schema::table('ms_accessory_sales', function (Blueprint $table) {
                if (Schema::hasColumn('ms_accessory_sales', 'gross_total')) {
                    $table->dropColumn('gross_total');
                }
                if (Schema::hasColumn('ms_accessory_sales', 'discount_amount')) {
                    $table->dropColumn('discount_amount');
                }
            });
        }

        if (Schema::hasTable('ms_accessory_sale_items')) {
            Schema::table('ms_accessory_sale_items', function (Blueprint $table) {
                if (Schema::hasColumn('ms_accessory_sale_items', 'original_price')) {
                    $table->dropColumn('original_price');
                }
                if (Schema::hasColumn('ms_accessory_sale_items', 'discount_amount')) {
                    $table->dropColumn('discount_amount');
                }
            });
        }
    }
};

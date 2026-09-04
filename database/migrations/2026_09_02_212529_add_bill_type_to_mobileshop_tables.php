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
        Schema::table('ms_mobile_sales', function (Blueprint $table) {
            $table->enum('bill_type', ['gst', 'non_gst'])->default('gst')->after('invoice_number');
        });

        Schema::table('ms_accessory_sales', function (Blueprint $table) {
            $table->enum('bill_type', ['gst', 'non_gst'])->default('gst')->after('invoice_number');
        });

        Schema::table('ms_purchase_orders', function (Blueprint $table) {
            $table->enum('bill_type', ['gst', 'non_gst'])->default('gst')->after('po_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ms_mobile_sales', function (Blueprint $table) {
            $table->dropColumn('bill_type');
        });

        Schema::table('ms_accessory_sales', function (Blueprint $table) {
            $table->dropColumn('bill_type');
        });

        Schema::table('ms_purchase_orders', function (Blueprint $table) {
            $table->dropColumn('bill_type');
        });
    }
};

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

        if (Schema::hasTable('ms_accessory_sales')) {
            try {
                DB::statement("ALTER TABLE `{$pfx}ms_accessory_sales` MODIFY COLUMN status ENUM('completed', 'partially_returned', 'returned', 'cancelled', 'voided') DEFAULT 'completed'");
            } catch (\Throwable $e) {
                // In SQLite or environments where ALTER COLUMN ENUM is not supported, ignore gracefully
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $pfx = DB::getTablePrefix();

        if (Schema::hasTable('ms_accessory_sales')) {
            try {
                DB::statement("ALTER TABLE `{$pfx}ms_accessory_sales` MODIFY COLUMN status ENUM('completed', 'voided') DEFAULT 'completed'");
            } catch (\Throwable $e) {}
        }
    }
};

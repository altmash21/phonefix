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
        $prefix = DB::getTablePrefix();
        $tableName = $prefix . 'ms_customer_khata_transactions';

        try {
            DB::statement("ALTER TABLE `{$tableName}` MODIFY `type` VARCHAR(50) NOT NULL DEFAULT 'udhari_sale'");
        } catch (\Throwable $e) {
            // Fallback for drivers that don't support raw statement or if already altered
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep as VARCHAR(50) for backwards safety
    }
};

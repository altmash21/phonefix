<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix GST state defaults + existing demo data: Maharashtra(27) -> Uttar Pradesh(09).
     */
    public function up(): void
    {
        $pfx = DB::getTablePrefix();

        // 1. Column defaults
        if (Schema::hasTable('ms_customers')) {
            try {
                DB::statement("ALTER TABLE `{$pfx}ms_customers` MODIFY COLUMN state_code VARCHAR(2) NOT NULL DEFAULT '09'");
            } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('ms_suppliers')) {
            try {
                DB::statement("ALTER TABLE `{$pfx}ms_suppliers` MODIFY COLUMN state VARCHAR(100) NOT NULL DEFAULT 'Uttar Pradesh'");
            } catch (\Throwable $e) {}
            try {
                DB::statement("ALTER TABLE `{$pfx}ms_suppliers` MODIFY COLUMN state_code VARCHAR(2) NOT NULL DEFAULT '09'");
            } catch (\Throwable $e) {}
        }

        // 2. Existing rows: correct the wrong Maharashtra/27 demo data.
        //    Only rewrite rows that are still carrying the old default, so real
        //    inter-state data (a genuinely out-of-state customer/supplier) is untouched.
        try {
            DB::table('ms_customers')
                ->where('state_code', '27')
                ->update(['state_code' => '09']);
        } catch (\Throwable $e) {}

        try {
            DB::table('ms_suppliers')
                ->where('state', 'Maharashtra')
                ->update(['state' => 'Uttar Pradesh', 'state_code' => '09']);

            // GSTIN's first two digits encode the state; re-key any legacy 27- prefixed GSTIN.
            DB::table('ms_suppliers')
                ->where('gstin', 'like', '27%')
                ->update(['gstin' => DB::raw("CONCAT('09', SUBSTRING(gstin, 3))")]);
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        // No-op: geographic correction is not safely reversible.
    }
};

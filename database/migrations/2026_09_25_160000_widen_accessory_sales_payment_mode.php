<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $p = DB::getTablePrefix();
        if (Schema::hasTable('ms_accessory_sales')) {
            try {
                DB::statement("ALTER TABLE `{$p}ms_accessory_sales` MODIFY COLUMN `payment_mode` VARCHAR(50) NOT NULL DEFAULT 'cash'");
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
    }
};

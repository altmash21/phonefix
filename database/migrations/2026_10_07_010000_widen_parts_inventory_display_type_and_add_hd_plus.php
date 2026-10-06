<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $p = DB::getTablePrefix();
        if (Schema::hasTable('ms_parts_inventory')) {
            try {
                DB::statement("ALTER TABLE `{$p}ms_parts_inventory` MODIFY COLUMN `display_type` VARCHAR(50) NOT NULL DEFAULT 'na'");
            } catch (\Throwable $e) {
                // Ignore if already VARCHAR or SQLite
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};

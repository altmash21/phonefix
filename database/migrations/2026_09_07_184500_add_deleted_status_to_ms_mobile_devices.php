<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'deleted' to status enum in ms_mobile_devices
        DB::statement("ALTER TABLE `9nj_ms_mobile_devices` MODIFY COLUMN `status` ENUM('in_stock', 'sold', 'in_repair', 'returned', 'scrapped', 'rtv_returned', 'deleted') NOT NULL DEFAULT 'in_stock'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `9nj_ms_mobile_devices` MODIFY COLUMN `status` ENUM('in_stock', 'sold', 'in_repair', 'returned', 'scrapped', 'rtv_returned') NOT NULL DEFAULT 'in_stock'");
    }
};

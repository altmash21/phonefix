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
        if (Schema::hasTable('ms_mobile_devices')) {
            Schema::table('ms_mobile_devices', function (Blueprint $table) {
                if (!Schema::hasColumn('ms_mobile_devices', 'photo_path')) {
                    $table->string('photo_path')->nullable()->after('box_photo_path');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ms_mobile_devices')) {
            Schema::table('ms_mobile_devices', function (Blueprint $table) {
                if (Schema::hasColumn('ms_mobile_devices', 'photo_path')) {
                    $table->dropColumn('photo_path');
                }
            });
        }
    }
};

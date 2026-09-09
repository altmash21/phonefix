<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ms_sync_queue') && !Schema::hasColumn('ms_sync_queue', 'company_id')) {
            Schema::table('ms_sync_queue', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->default(1)->index()->after('id');
            });
        }

        if (Schema::hasTable('ms_sync_offsets') && !Schema::hasColumn('ms_sync_offsets', 'company_id')) {
            Schema::table('ms_sync_offsets', function (Blueprint $table) {
                $table->dropPrimary();
                $table->unsignedBigInteger('company_id')->default(1)->after('model');
                $table->primary(['company_id', 'model']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ms_sync_queue') && Schema::hasColumn('ms_sync_queue', 'company_id')) {
            Schema::table('ms_sync_queue', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }

        if (Schema::hasTable('ms_sync_offsets') && Schema::hasColumn('ms_sync_offsets', 'company_id')) {
            Schema::table('ms_sync_offsets', function (Blueprint $table) {
                $table->dropPrimary();
                $table->dropColumn('company_id');
                $table->primary('model');
            });
        }
    }
};

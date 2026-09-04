<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ms_sync_queue', function (Blueprint $table) {
            $table->id();
            $table->string('sync_key')->nullable()->index();
            $table->string('action')->index(); // create/update/delete
            $table->string('model'); // ms_mobile_sales, ms_accessory_sales, ms_mobile_devices...
            $table->unsignedBigInteger('record_id')->nullable(); // local id, null for create
            $table->text('payload')->nullable(); // JSON of the actual data
            $table->enum('status', ['pending','uploaded','failed','synced'])->default('pending');
            $table->unsignedBigInteger('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ms_sync_offsets', function (Blueprint $table) {
            $table->string('model')->primary();
            $table->unsignedBigInteger('last_synced_id')->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ms_sync_queue');
        Schema::dropIfExists('ms_sync_offsets');
    }
};

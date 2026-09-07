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
        if (!Schema::hasTable('ms_stock_audit_log')) {
            Schema::create('ms_stock_audit_log', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->string('item_type', 50)->index(); // 'new_phone', 'second_hand', 'part'
                $table->unsignedBigInteger('item_id')->index();
                $table->string('action', 50)->index(); // 'addition', 'sale', 'deletion', 'adjustment'
                $table->integer('quantity')->default(1);
                $table->integer('balance_after')->nullable();
                $table->string('reason', 255)->nullable();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->timestamps();

                $table->index(['company_id', 'item_type', 'item_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_stock_audit_log');
    }
};

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
        Schema::create('ms_parts_inventory_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('part_id')->index();
            $table->enum('type', ['addition', 'deduction']);
            $table->integer('quantity');
            $table->integer('balance_after');
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_parts_inventory_history');
    }
};

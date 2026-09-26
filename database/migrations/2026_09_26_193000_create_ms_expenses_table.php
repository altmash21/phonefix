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
        if (!Schema::hasTable('ms_expenses')) {
            Schema::create('ms_expenses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->default(1)->index();
                $table->string('expense_number', 50)->nullable()->index();
                $table->string('category', 60)->default('other')->index();
                $table->string('title');
                $table->decimal('amount', 15, 2);
                $table->date('expense_date')->index();
                $table->string('payment_mode', 40)->default('cash')->index();
                $table->string('paid_to')->nullable();
                $table->string('reference_no', 100)->nullable();
                $table->text('notes')->nullable();
                $table->string('receipt_path')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_expenses');
    }
};

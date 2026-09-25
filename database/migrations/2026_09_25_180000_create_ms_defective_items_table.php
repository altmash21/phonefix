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
        if (!Schema::hasTable('ms_defective_items')) {
            Schema::create('ms_defective_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->default(1)->index();
                $table->unsignedBigInteger('part_id')->nullable()->index();
                $table->string('item_name');
                $table->string('source_type', 30)->default('purchase_return')->index(); // 'purchase_return', 'sale_return', 'direct'
                $table->unsignedBigInteger('source_id')->nullable()->index(); // purchase_order_id or accessory_sale_id
                $table->string('source_ref', 100)->nullable(); // PO # or Invoice #
                $table->unsignedBigInteger('supplier_id')->nullable()->index();
                $table->string('supplier_name')->nullable();
                $table->string('customer_name')->nullable();
                $table->integer('qty')->default(1);
                $table->decimal('unit_cost', 15, 2)->default(0.00);
                $table->decimal('total_cost', 15, 2)->default(0.00);
                $table->string('defect_reason')->nullable();
                $table->string('status', 40)->default('pending_supplier_return')->index(); // 'pending_supplier_return', 'returned_to_supplier', 'replaced_by_supplier', 'scrap_written_off'
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_defective_items');
    }
};

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
        // 1. Atomic Invoice Sequences
        if (!Schema::hasTable('ms_invoice_sequences')) {
            Schema::create('ms_invoice_sequences', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->string('prefix', 10); // 'NEW', 'SH', 'ACC'
                $table->unsignedBigInteger('current_sequence')->default(0);
                $table->timestamps();
                $table->unique(['company_id', 'prefix']);
            });
        }

        // 2. Accessory Sales (Header)
        if (!Schema::hasTable('ms_accessory_sales')) {
            Schema::create('ms_accessory_sales', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->string('idempotency_key', 64)->nullable()->index();
                $table->string('invoice_number', 50)->index();
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->decimal('subtotal', 15, 2)->default(0.00);
                $table->decimal('tax_rate', 5, 2)->default(18.00);
                $table->decimal('tax_amount', 15, 2)->default(0.00);
                $table->decimal('cgst_amount', 15, 2)->default(0.00);
                $table->decimal('sgst_amount', 15, 2)->default(0.00);
                $table->decimal('igst_amount', 15, 2)->default(0.00);
                $table->decimal('total_amount', 15, 2)->default(0.00);
                $table->decimal('amount_paid', 15, 2)->default(0.00);
                $table->decimal('udhari_amount', 15, 2)->default(0.00);
                $table->enum('payment_mode', ['cash', 'upi', 'card', 'credit_udhari', 'split'])->default('cash');
                $table->unsignedBigInteger('sold_by')->nullable()->index();
                $table->enum('status', ['completed', 'voided'])->default('completed');
                $table->unsignedBigInteger('voided_by')->nullable()->index();
                $table->timestamp('voided_at')->nullable();
                $table->string('void_reason')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'idempotency_key']);
                $table->unique(['company_id', 'invoice_number']);
            });
        }

        // 3. Accessory Sale Line Items
        if (!Schema::hasTable('ms_accessory_sale_items')) {
            Schema::create('ms_accessory_sale_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('accessory_sale_id')->index();
                $table->unsignedBigInteger('part_id')->index();
                $table->string('part_name');
                $table->string('hsn_code', 20)->default('85177090');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_cost', 15, 2)->default(0.00);
                $table->decimal('unit_price', 15, 2)->default(0.00);
                $table->decimal('tax_rate', 5, 2)->default(18.00);
                $table->decimal('tax_amount', 15, 2)->default(0.00);
                $table->decimal('line_total', 15, 2)->default(0.00);
                $table->timestamps();
            });
        }

        // Also add idempotency_key and void fields to ms_mobile_sales if not present
        if (Schema::hasTable('ms_mobile_sales')) {
            Schema::table('ms_mobile_sales', function (Blueprint $table) {
                if (!Schema::hasColumn('ms_mobile_sales', 'idempotency_key')) {
                    $table->string('idempotency_key', 64)->nullable()->after('company_id')->index();
                }
                if (!Schema::hasColumn('ms_mobile_sales', 'voided_by')) {
                    $table->unsignedBigInteger('voided_by')->nullable()->after('status');
                    $table->timestamp('voided_at')->nullable()->after('voided_by');
                    $table->string('void_reason')->nullable()->after('voided_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_accessory_sale_items');
        Schema::dropIfExists('ms_accessory_sales');
        Schema::dropIfExists('ms_invoice_sequences');

        if (Schema::hasTable('ms_mobile_sales')) {
            Schema::table('ms_mobile_sales', function (Blueprint $table) {
                if (Schema::hasColumn('ms_mobile_sales', 'idempotency_key')) {
                    $table->dropColumn('idempotency_key');
                }
                if (Schema::hasColumn('ms_mobile_sales', 'voided_by')) {
                    $table->dropColumn(['voided_by', 'voided_at', 'void_reason']);
                }
            });
        }
    }
};

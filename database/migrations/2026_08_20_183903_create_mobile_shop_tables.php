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
        // 1. CUSTOMERS & KHATA (UDHARI)
        Schema::create('ms_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->string('name');
            $table->string('phone', 50)->index();
            $table->string('email')->nullable();
            $table->string('gstin', 15)->nullable();
            $table->string('state_code', 2)->default('09');
            $table->text('address')->nullable();
            $table->decimal('udhari_balance', 15, 2)->default(0.00);
            $table->decimal('credit_limit', 15, 2)->default(10000.00);
            $table->timestamps();
        });

        Schema::create('ms_customer_khata_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->unsignedBigInteger('customer_id')->index();
            $table->enum('type', ['udhari_sale', 'payment_received', 'refund_credit', 'adjustment']);
            $table->unsignedBigInteger('sale_id')->nullable()->index();
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->enum('payment_mode', ['cash', 'upi', 'bank_transfer', 'cheque'])->nullable();
            $table->string('reference_no')->nullable();
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 2. SUPPLIERS & PROCUREMENT
        Schema::create('ms_suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone', 50);
            $table->string('email')->nullable();
            $table->string('gstin', 15)->nullable();
            $table->string('state')->default('Uttar Pradesh');
            $table->string('state_code', 2)->default('09');
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('ms_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->unsignedBigInteger('supplier_id')->index();
            $table->string('po_number')->unique();
            $table->date('order_date');
            $table->enum('tax_type', ['intra_state', 'inter_state'])->default('intra_state');
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('cgst_amount', 15, 2)->default(0.00);
            $table->decimal('sgst_amount', 15, 2)->default(0.00);
            $table->decimal('igst_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('amount_paid', 15, 2)->default(0.00);
            $table->decimal('balance_due', 15, 2)->default(0.00);
            $table->enum('status', ['draft', 'partially_paid', 'paid', 'partially_received', 'received', 'closed', 'cancelled'])->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('ms_purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->index();
            $table->string('brand');
            $table->string('model');
            $table->string('variant')->nullable();
            $table->string('hsn_code', 20)->default('85171300');
            $table->integer('qty');
            $table->integer('qty_received')->default(0);
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(18.00);
            $table->decimal('line_total', 15, 2);
        });

        Schema::create('ms_goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->unsignedBigInteger('purchase_order_id')->index();
            $table->string('grn_number')->unique();
            $table->date('received_date');
            $table->unsignedBigInteger('received_by')->nullable();
            $table->string('vendor_invoice_no')->nullable();
            $table->string('delivery_challan_no')->nullable();
            $table->string('box_image_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('ms_goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('goods_receipt_id')->index();
            $table->unsignedBigInteger('purchase_order_item_id')->index();
            $table->integer('qty_received');
            $table->timestamps();
        });

        Schema::create('ms_supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->unsignedBigInteger('supplier_id')->index();
            $table->unsignedBigInteger('purchase_order_id')->nullable()->index();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->enum('mode', ['cash', 'bank_transfer', 'cheque', 'upi']);
            $table->string('reference_no')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();
        });

        Schema::create('ms_supplier_credit_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->unsignedBigInteger('supplier_id')->unique();
            $table->decimal('credit_balance', 15, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('ms_supplier_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id')->index();
            $table->enum('txn_type', ['credit_added', 'credit_applied', 'refunded', 'rtv_credit']);
            $table->decimal('amount', 15, 2);
            $table->unsignedBigInteger('related_po_id')->nullable()->index();
            $table->decimal('balance_after', 15, 2);
            $table->timestamp('txn_date')->useCurrent();
            $table->string('remarks')->nullable();
        });

        // 3. MOBILE INVENTORY & PHYSICAL STOCK
        Schema::create('ms_mobile_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->unsignedBigInteger('purchase_order_id')->nullable()->index();
            $table->unsignedBigInteger('goods_receipt_id')->nullable()->index();
            $table->enum('type', ['new', 'second_hand'])->default('new');
            $table->string('brand');
            $table->string('model');
            $table->string('color')->nullable();
            $table->string('ram', 20)->nullable();
            $table->string('storage', 20)->nullable();
            $table->string('imei_1', 20)->unique();
            $table->string('imei_2', 20)->nullable()->unique();
            $table->string('serial_no')->nullable();
            $table->string('hsn_code', 20)->default('85171300');
            $table->decimal('purchase_cost', 15, 2)->default(0.00);
            $table->decimal('selling_price', 15, 2)->default(0.00);
            $table->integer('min_stock_alert')->default(1);
            $table->string('box_photo_path')->nullable();
            $table->enum('status', ['in_stock', 'sold', 'in_repair', 'returned', 'scrapped', 'rtv_returned'])->default('in_stock');
            $table->enum('condition_grade', ['brand_new', 'like_new_A_plus', 'good_A', 'fair_B'])->default('brand_new');
            $table->integer('battery_health')->nullable();
            $table->string('customer_buyback_name')->nullable();
            $table->string('customer_buyback_phone', 50)->nullable();
            $table->string('customer_buyback_id_proof')->nullable();
            $table->text('checklist_notes')->nullable();
            $table->timestamps();
        });

        // 4. GIFTS, SALES & SALES RETURNS
        Schema::create('ms_gifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->integer('stock_qty')->default(0);
            $table->integer('min_stock_alert')->default(5);
            $table->timestamps();
        });

        Schema::create('ms_mobile_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('invoice_number')->unique();
            $table->unsignedBigInteger('device_id')->index();
            $table->decimal('sale_price', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(18.00);
            $table->enum('tax_type', ['intra_state', 'inter_state'])->default('intra_state');
            $table->decimal('cgst_amount', 15, 2)->default(0.00);
            $table->decimal('sgst_amount', 15, 2)->default(0.00);
            $table->decimal('igst_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0.00);
            $table->decimal('udhari_amount', 15, 2)->default(0.00);
            $table->enum('payment_mode', ['cash', 'upi', 'card', 'bank_transfer', 'emi', 'credit_udhari', 'split']);
            $table->unsignedBigInteger('emi_provider_id')->nullable()->index();
            $table->string('emi_loan_no')->nullable();
            $table->decimal('emi_downpayment', 15, 2)->default(0.00);
            $table->decimal('emi_financed_amount', 15, 2)->default(0.00);
            $table->decimal('emi_monthly_amount', 15, 2)->default(0.00);
            $table->integer('emi_tenure_months')->default(0);
            $table->unsignedBigInteger('sold_by')->nullable();
            $table->enum('status', ['completed', 'partially_returned', 'returned', 'cancelled'])->default('completed');
            $table->timestamps();
        });

        Schema::create('ms_sale_gifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id')->index();
            $table->unsignedBigInteger('gift_id')->index();
            $table->integer('qty')->default(1);
            $table->integer('returned_qty')->default(0);
            $table->timestamps();
        });

        Schema::create('ms_sales_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->string('return_number')->unique();
            $table->unsignedBigInteger('sale_id')->index();
            $table->unsignedBigInteger('device_id')->index();
            $table->date('return_date');
            $table->text('reason');
            $table->enum('item_condition', ['resellable', 'defective_for_repair', 'damaged_scrapped'])->default('resellable');
            $table->enum('refund_mode', ['cash', 'upi', 'bank_transfer', 'store_credit', 'udhari_adjustment', 'replacement']);
            $table->decimal('refund_amount', 15, 2);
            $table->enum('restock_status', ['restocked_to_inventory', 'sent_to_repair', 'scrapped']);
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. EMI PROVIDERS & ADVANCE WALLETS
        Schema::create('ms_emi_providers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->decimal('advance_balance', 15, 2)->default(0.00);
            $table->string('contact_person')->nullable();
            $table->string('phone', 50)->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('ms_emi_provider_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emi_provider_id')->index();
            $table->enum('type', ['advance_deposit', 'sale_deduction', 'settlement', 'reversal', 'return_refund']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->unsignedBigInteger('sale_id')->nullable()->index();
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. ACCESSORIES & REPAIR SPARE PARTS
        Schema::create('ms_parts_inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->enum('category', ['folder_display', 'back_panel', 'tempered_glass', 'battery', 'camera_module', 'charging_port', 'general_accessory', 'ic_chip']);
            $table->string('brand');
            $table->string('compatible_model');
            $table->enum('display_type', ['original_oem', 'oled', 'in_cell', 'tft', 'na'])->default('na');
            $table->string('hsn_code', 20)->default('85177090');
            $table->string('name');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->integer('stock_qty')->default(0);
            $table->integer('min_stock_alert')->default(3);
            $table->timestamps();
        });

        // 7. REPAIR TICKETS & ENCRYPTED PASSCODES
        Schema::create('ms_repair_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->string('ticket_number')->unique();
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('brand');
            $table->string('model');
            $table->string('imei_serial', 50)->nullable();
            $table->text('passcode_encrypted')->nullable();
            $table->text('pattern_code_encrypted')->nullable();
            $table->text('reported_faults');
            $table->text('physical_condition')->nullable();
            $table->unsignedBigInteger('technician_id')->nullable()->index();
            $table->enum('status', ['received', 'in_diagnosis', 'waiting_for_parts', 'waiting_approval', 'in_repair', 'ready', 'delivered', 'cancelled'])->default('received');
            $table->decimal('estimated_cost', 15, 2)->default(0.00);
            $table->decimal('labor_charge', 15, 2)->default(0.00);
            $table->decimal('parts_cost', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('advance_paid', 15, 2)->default(0.00);
            $table->decimal('balance_due', 15, 2)->default(0.00);
            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ms_repair_ticket_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('repair_ticket_id')->index();
            $table->unsignedBigInteger('part_id')->index();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('unit_price', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_repair_ticket_parts');
        Schema::dropIfExists('ms_repair_tickets');
        Schema::dropIfExists('ms_parts_inventory');
        Schema::dropIfExists('ms_emi_provider_transactions');
        Schema::dropIfExists('ms_emi_providers');
        Schema::dropIfExists('ms_sales_returns');
        Schema::dropIfExists('ms_sale_gifts');
        Schema::dropIfExists('ms_mobile_sales');
        Schema::dropIfExists('ms_gifts');
        Schema::dropIfExists('ms_mobile_devices');
        Schema::dropIfExists('ms_supplier_credit_transactions');
        Schema::dropIfExists('ms_supplier_credit_wallets');
        Schema::dropIfExists('ms_supplier_payments');
        Schema::dropIfExists('ms_goods_receipt_items');
        Schema::dropIfExists('ms_goods_receipts');
        Schema::dropIfExists('ms_purchase_order_items');
        Schema::dropIfExists('ms_purchase_orders');
        Schema::dropIfExists('ms_suppliers');
        Schema::dropIfExists('ms_customer_khata_transactions');
        Schema::dropIfExists('ms_customers');
    }
};

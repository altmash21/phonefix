<?php

namespace Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class MobileShopDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing demo data
        DB::table('ms_repair_ticket_parts')->truncate();
        DB::table('ms_repair_tickets')->truncate();
        DB::table('ms_parts_inventory_history')->truncate();
        DB::table('ms_parts_inventory')->truncate();
        DB::table('ms_emi_provider_transactions')->truncate();
        DB::table('ms_emi_providers')->truncate();
        DB::table('ms_sales_returns')->truncate();
        DB::table('ms_sale_gifts')->truncate();
        DB::table('ms_accessory_sale_items')->truncate();
        DB::table('ms_accessory_sales')->truncate();
        DB::table('ms_mobile_sales')->truncate();
        DB::table('ms_gifts')->truncate();
        DB::table('ms_mobile_devices')->truncate();
        DB::table('ms_supplier_credit_transactions')->truncate();
        DB::table('ms_supplier_credit_wallets')->truncate();
        DB::table('ms_supplier_payments')->truncate();
        DB::table('ms_goods_receipt_items')->truncate();
        DB::table('ms_goods_receipts')->truncate();
        DB::table('ms_purchase_order_items')->truncate();
        DB::table('ms_purchase_orders')->truncate();
        DB::table('ms_suppliers')->truncate();
        DB::table('ms_customer_khata_transactions')->truncate();
        DB::table('ms_customers')->truncate();
        DB::table('ms_invoice_sequences')->truncate();

        // 1. Customers
        $c1 = DB::table('ms_customers')->insertGetId([
            'company_id' => 1,
            'name' => 'Rahul Sharma',
            'phone' => '9876543210',
            'email' => 'rahul.sharma@example.com',
            'state_code' => '09',
            'address' => 'Shop 4, Hazratganj Market, Lucknow, Uttar Pradesh',
            'udhari_balance' => 4500.00,
            'credit_limit' => 25000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $c2 = DB::table('ms_customers')->insertGetId([
            'company_id' => 1,
            'name' => 'Amit Patel',
            'phone' => '9822334455',
            'email' => 'amit.patel@example.com',
            'state_code' => '09',
            'address' => 'B-202, Gokul Heights, Gomti Nagar, Lucknow',
            'udhari_balance' => 0.00,
            'credit_limit' => 15000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ms_customer_khata_transactions')->insert([
            'company_id' => 1,
            'customer_id' => $c1,
            'type' => 'udhari_sale',
            'amount' => 4500.00,
            'balance_after' => 4500.00,
            'remarks' => 'Pending balance on Display repair & Screen Guard',
            'created_at' => now()->subDays(3),
        ]);

        // 2. Suppliers & Credit Wallet
        $s1 = DB::table('ms_suppliers')->insertGetId([
            'company_id' => 1,
            'name' => 'Apex Telecom Distributors Pvt Ltd',
            'contact_person' => 'Vikram Singhania',
            'phone' => '9811223344',
            'email' => 'orders@apextelecom.com',
            'gstin' => '09AAACA1234F1Z5',
            'state' => 'Uttar Pradesh',
            'state_code' => '09',
            'address' => 'Gala 12, Naka Hindola, Lucknow',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $s2 = DB::table('ms_suppliers')->insertGetId([
            'company_id' => 1,
            'name' => 'National Screen & Spare Parts Hub',
            'contact_person' => 'Rajesh Jain',
            'phone' => '9820011223',
            'email' => 'sales@nationalscreen.in',
            'gstin' => '09AAECN9988D1Z2',
            'state' => 'Uttar Pradesh',
            'state_code' => '09',
            'address' => 'Shop 104, Naya Ganj, Kanpur',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Supplier 1 Credit Wallet (Prepaid excess balance)
        DB::table('ms_supplier_credit_wallets')->insert([
            'company_id' => 1,
            'supplier_id' => $s1,
            'credit_balance' => 100000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ms_supplier_credit_transactions')->insert([
            'supplier_id' => $s1,
            'txn_type' => 'credit_added',
            'amount' => 100000.00,
            'balance_after' => 100000.00,
            'remarks' => 'Overpayment on PO #PO-2026-0801 credited to wallet',
            'txn_date' => now()->subDays(5),
        ]);

        // 3. Purchase Orders & GRN
        $po1 = DB::table('ms_purchase_orders')->insertGetId([
            'company_id' => 1,
            'supplier_id' => $s1,
            'po_number' => 'PO-2026-0801',
            'order_date' => now()->subDays(10)->toDateString(),
            'tax_type' => 'intra_state',
            'subtotal' => 309322.03,
            'cgst_amount' => 27838.98,
            'sgst_amount' => 27838.99,
            'igst_amount' => 0.00,
            'total_amount' => 365000.00,
            'amount_paid' => 365000.00,
            'balance_due' => 0.00,
            'status' => 'received',
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(5),
        ]);

        $poi1 = DB::table('ms_purchase_order_items')->insertGetId([
            'purchase_order_id' => $po1,
            'brand' => 'Samsung',
            'model' => 'Galaxy S24 5G',
            'variant' => '8GB/128GB Onyx Black',
            'hsn_code' => '85171300',
            'qty' => 5,
            'qty_received' => 5,
            'unit_cost' => 60000.00,
            'tax_rate' => 18.00,
            'line_total' => 300000.00,
        ]);

        $poi2 = DB::table('ms_purchase_order_items')->insertGetId([
            'purchase_order_id' => $po1,
            'brand' => 'Apple',
            'model' => 'iPhone 15',
            'variant' => '128GB Blue',
            'hsn_code' => '85171300',
            'qty' => 1,
            'qty_received' => 1,
            'unit_cost' => 65000.00,
            'tax_rate' => 18.00,
            'line_total' => 65000.00,
        ]);

        $grn1 = DB::table('ms_goods_receipts')->insertGetId([
            'company_id' => 1,
            'purchase_order_id' => $po1,
            'grn_number' => 'GRN-2026-001',
            'received_date' => now()->subDays(8)->toDateString(),
            'vendor_invoice_no' => 'INV-APEX-984',
            'notes' => '5 Samsung + 1 iPhone received in pristine condition with warranty seals intact.',
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);

        // 4. Mobile Devices Stock
        $mobiles = [
            [
                'company_id' => 1,
                'purchase_order_id' => $po1,
                'goods_receipt_id' => $grn1,
                'type' => 'new',
                'brand' => 'Samsung',
                'model' => 'Galaxy S24 5G',
                'color' => 'Onyx Black',
                'ram' => '8GB',
                'storage' => '128GB',
                'imei_1' => '354892019482019',
                'imei_2' => '354892019482027',
                'purchase_cost' => 60000.00,
                'selling_price' => 74999.00,
                'min_stock_alert' => 2,
                'status' => 'in_stock',
                'condition_grade' => 'brand_new',
                'created_at' => now()->subDays(8),
                'updated_at' => now()->subDays(8),
            ],
            [
                'company_id' => 1,
                'purchase_order_id' => $po1,
                'goods_receipt_id' => $grn1,
                'type' => 'new',
                'brand' => 'Samsung',
                'model' => 'Galaxy S24 5G',
                'color' => 'Marble Gray',
                'ram' => '8GB',
                'storage' => '128GB',
                'imei_1' => '354892019482035',
                'imei_2' => '354892019482043',
                'purchase_cost' => 60000.00,
                'selling_price' => 74999.00,
                'min_stock_alert' => 2,
                'status' => 'in_stock',
                'condition_grade' => 'brand_new',
                'created_at' => now()->subDays(8),
                'updated_at' => now()->subDays(8),
            ],
            [
                'company_id' => 1,
                'purchase_order_id' => $po1,
                'goods_receipt_id' => $grn1,
                'type' => 'new',
                'brand' => 'Apple',
                'model' => 'iPhone 15',
                'color' => 'Blue',
                'ram' => '6GB',
                'storage' => '128GB',
                'imei_1' => '864019284019284',
                'imei_2' => null,
                'purchase_cost' => 65000.00,
                'selling_price' => 79900.00,
                'min_stock_alert' => 1,
                'status' => 'in_stock',
                'condition_grade' => 'brand_new',
                'created_at' => now()->subDays(8),
                'updated_at' => now()->subDays(8),
            ],
            [
                'company_id' => 1,
                'purchase_order_id' => null,
                'goods_receipt_id' => null,
                'type' => 'second_hand',
                'brand' => 'Apple',
                'model' => 'iPhone 13 Pro',
                'color' => 'Sierra Blue',
                'ram' => '6GB',
                'storage' => '128GB',
                'imei_1' => '352948102948192',
                'imei_2' => null,
                'purchase_cost' => 42000.00,
                'selling_price' => 54999.00,
                'min_stock_alert' => 1,
                'status' => 'in_stock',
                'condition_grade' => 'like_new_A_plus',
                'battery_health' => 88,
                'customer_buyback_name' => 'Karan Mehra',
                'customer_buyback_phone' => '9988776655',
                'customer_buyback_id_proof' => 'Aadhaar Verified (XX-4912)',
                'checklist_notes' => 'Original display, FaceID working, 100% functional, tiny scratch near speaker grill.',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
        ];

        foreach ($mobiles as $m) {
            DB::table('ms_mobile_devices')->insert($m);
        }

        // 5. Free Promotional Gifts
        $gifts = [
            ['name' => '9D Tempered Glass Guard', 'description' => 'Edge-to-edge curved screen protector', 'unit_cost' => 45.00, 'stock_qty' => 120, 'min_stock_alert' => 10],
            ['name' => 'Shockproof Transparent Silicone Case', 'description' => 'Camera bumper protection cover', 'unit_cost' => 60.00, 'stock_qty' => 85, 'min_stock_alert' => 10],
            ['name' => 'Fast Charging Type-C Braided Cable', 'description' => '65W Fast charging 1.5m cable', 'unit_cost' => 90.00, 'stock_qty' => 40, 'min_stock_alert' => 5],
            ['name' => 'Wireless Bluetooth Earphones', 'description' => 'Bass boost neckband with mic', 'unit_cost' => 280.00, 'stock_qty' => 15, 'min_stock_alert' => 5],
        ];

        foreach ($gifts as $g) {
            DB::table('ms_gifts')->insert(array_merge($g, ['company_id' => 1, 'created_at' => now(), 'updated_at' => now()]));
        }

        // 6. EMI Providers
        $emiProviders = [
            ['name' => 'Bajaj Finserv Consumer Finance', 'code' => 'BAJAJ_FIN', 'advance_balance' => 450000.00, 'contact_person' => 'Sanjay Verma', 'phone' => '9890123456'],
            ['name' => 'Home Credit India', 'code' => 'HOME_CREDIT', 'advance_balance' => 200000.00, 'contact_person' => 'Deepak Gupta', 'phone' => '9870123456'],
            ['name' => 'TVS Credit Services', 'code' => 'TVS_CREDIT', 'advance_balance' => 150000.00, 'contact_person' => 'Manoj Joshi', 'phone' => '9860123456'],
            ['name' => 'In-House Store Easy Installments', 'code' => 'IN_HOUSE', 'advance_balance' => 0.00, 'contact_person' => 'Store Owner', 'phone' => '9822334455'],
        ];

        foreach ($emiProviders as $ep) {
            DB::table('ms_emi_providers')->insert(array_merge($ep, ['company_id' => 1, 'created_at' => now(), 'updated_at' => now()]));
        }

        // 7. Accessories, Folder Displays & Repair Parts
        $parts = [
            ['category' => 'folder_display', 'brand' => 'Apple', 'compatible_model' => 'iPhone 13 / 13 Pro', 'display_type' => 'oled', 'name' => 'iPhone 13 OLED Display Screen Assembly (Super Retina XDR)', 'unit_cost' => 7500.00, 'selling_price' => 11500.00, 'stock_qty' => 6, 'min_stock_alert' => 2],
            ['category' => 'folder_display', 'brand' => 'Samsung', 'compatible_model' => 'Galaxy S23 / S24', 'display_type' => 'original_oem', 'name' => 'Samsung S24 Dynamic AMOLED 2X Display Combo', 'unit_cost' => 8500.00, 'selling_price' => 13000.00, 'stock_qty' => 4, 'min_stock_alert' => 2],
            ['category' => 'folder_display', 'brand' => 'Xiaomi', 'compatible_model' => 'Redmi Note 13 Pro', 'display_type' => 'in_cell', 'name' => 'Redmi Note 13 Pro In-Cell FHD+ Screen Combo', 'unit_cost' => 1800.00, 'selling_price' => 3200.00, 'stock_qty' => 12, 'min_stock_alert' => 3],
            ['category' => 'back_panel', 'brand' => 'Apple', 'compatible_model' => 'iPhone 14 Pro', 'display_type' => 'na', 'name' => 'iPhone 14 Pro Matte Glass Back Housing (Deep Purple)', 'unit_cost' => 1400.00, 'selling_price' => 2800.00, 'stock_qty' => 5, 'min_stock_alert' => 2],
            ['category' => 'battery', 'brand' => 'Apple', 'compatible_model' => 'iPhone 12 / 12 Pro', 'display_type' => 'na', 'name' => 'iPhone 12 High Capacity OEM Battery 2815mAh (Zero Cycle)', 'unit_cost' => 1200.00, 'selling_price' => 2400.00, 'stock_qty' => 8, 'min_stock_alert' => 3],
            ['category' => 'tempered_glass', 'brand' => 'Universal', 'compatible_model' => 'iPhone 15 / 15 Pro', 'display_type' => 'na', 'name' => '11D Matte Gaming Tempered Glass', 'unit_cost' => 35.00, 'selling_price' => 250.00, 'stock_qty' => 45, 'min_stock_alert' => 10],
            ['category' => 'charging_port', 'brand' => 'Samsung', 'compatible_model' => 'Galaxy M33 / M53', 'display_type' => 'na', 'name' => 'Type-C Charging Sub-Board with Mic IC', 'unit_cost' => 250.00, 'selling_price' => 750.00, 'stock_qty' => 9, 'min_stock_alert' => 2],
        ];

        foreach ($parts as $p) {
            DB::table('ms_parts_inventory')->insert(array_merge($p, ['company_id' => 1, 'hsn_code' => '85177090', 'created_at' => now(), 'updated_at' => now()]));
        }

        // 8. Repair Tickets with Encrypted PIN & Pattern
        DB::table('ms_repair_tickets')->insert([
            'company_id' => 1,
            'ticket_number' => 'REP-2026-0042',
            'customer_id' => $c1,
            'brand' => 'Apple',
            'model' => 'iPhone 13',
            'imei_serial' => '359482019482011',
            'passcode_encrypted' => Crypt::encryptString('258014'),
            'pattern_code_encrypted' => Crypt::encryptString('1-4-7-8-9'),
            'reported_faults' => 'Screen cracked after drop. Touch working intermittently. Green vertical line on right edge.',
            'physical_condition' => 'Minor dent on top-right aluminum corner. Camera glass intact.',
            'status' => 'in_repair',
            'estimated_cost' => 12500.00,
            'labor_charge' => 1000.00,
            'parts_cost' => 11500.00,
            'total_amount' => 12500.00,
            'advance_paid' => 2000.00,
            'balance_due' => 10500.00,
            'received_at' => now()->subHours(6),
            'created_at' => now()->subHours(6),
            'updated_at' => now()->subHours(1),
        ]);

        // 9. Initial Demo Sales (for live charts and reports)
        $soldPhone = DB::table('ms_mobile_devices')->where('brand', 'Samsung')->first();
        if ($soldPhone) {
            DB::table('ms_mobile_sales')->insert([
                'company_id' => 1,
                'idempotency_key' => \Illuminate\Support\Str::uuid()->toString(),
                'customer_id' => $c1,
                'invoice_number' => 'INV-2026-0001',
                'device_id' => $soldPhone->id,
                'sale_price' => 74999.00,
                'tax_rate' => 18.00,
                'tax_type' => 'intra_state',
                'cgst_amount' => 5720.26,
                'sgst_amount' => 5720.27,
                'igst_amount' => 0.00,
                'total_amount' => 74999.00,
                'payment_mode' => 'cash',
                'amount_paid' => 74999.00,
                'udhari_amount' => 0.00,
                'status' => 'completed',
                'sold_by' => 1,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]);
        }

        $samplePart = DB::table('ms_parts_inventory')->where('category', 'tempered_glass')->first();
        if ($samplePart) {
            $accSaleId = DB::table('ms_accessory_sales')->insertGetId([
                'company_id' => 1,
                'idempotency_key' => \Illuminate\Support\Str::uuid()->toString(),
                'invoice_number' => 'ACC-2026-0001',
                'customer_id' => $c2,
                'subtotal' => 250.00,
                'tax_rate' => 18.00,
                'tax_amount' => 38.14,
                'cgst_amount' => 19.07,
                'sgst_amount' => 19.07,
                'igst_amount' => 0.00,
                'total_amount' => 250.00,
                'payment_mode' => 'upi',
                'amount_paid' => 250.00,
                'udhari_amount' => 0.00,
                'status' => 'completed',
                'sold_by' => 1,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]);

            DB::table('ms_accessory_sale_items')->insert([
                'accessory_sale_id' => $accSaleId,
                'company_id' => 1,
                'part_id' => $samplePart->id,
                'part_name' => $samplePart->name,
                'hsn_code' => '85177090',
                'quantity' => 1,
                'unit_cost' => $samplePart->unit_cost,
                'unit_price' => 250.00,
                'tax_rate' => 18.00,
                'tax_amount' => 38.14,
                'line_total' => 250.00,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]);
        }
    }
}

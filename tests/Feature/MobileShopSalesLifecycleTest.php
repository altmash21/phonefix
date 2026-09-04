<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobileShopSalesLifecycleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        session(['company_id' => 1]);
    }

    public function test_accessory_multi_item_cart_sale_and_void_lifecycle(): void
    {
        $admin = User::where('email', 'admin@mobitrack.local')->firstOrFail();
        $accessoriesStaff = User::where('email', 'accessories@mobitrack.local')->firstOrFail();

        // 1. Create 2 test accessories with known stock
        $part1Id = DB::table('ms_parts_inventory')->insertGetId([
            'company_id' => 1,
            'category' => 'tempered_glass',
            'brand' => 'Universal',
            'compatible_model' => 'Universal',
            'display_type' => 'na',
            'hsn_code' => '85177090',
            'name' => 'Test 9D Tempered Glass',
            'unit_cost' => 50.00,
            'selling_price' => 199.00,
            'stock_qty' => 10,
            'min_stock_alert' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $part2Id = DB::table('ms_parts_inventory')->insertGetId([
            'company_id' => 1,
            'category' => 'general_accessory',
            'brand' => 'Universal',
            'compatible_model' => 'Universal',
            'display_type' => 'na',
            'hsn_code' => '85177090',
            'name' => 'Test Type-C Fast Cable',
            'unit_cost' => 80.00,
            'selling_price' => 299.00,
            'stock_qty' => 5,
            'min_stock_alert' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Perform Multi-item Cart Checkout
        $idempotencyKey = Str::uuid()->toString();
        $response = $this->actingAs($accessoriesStaff)
            ->post('/1/mobileshop/accessories/sale', [
                'idempotency_key' => $idempotencyKey,
                'customer_phone' => '9988776655',
                'customer_name' => 'Amit Verma',
                'items' => [
                    [
                        'part_id' => $part1Id,
                        'quantity' => 2,
                        'unit_price' => 199.00,
                    ],
                    [
                        'part_id' => $part2Id,
                        'quantity' => 1,
                        'unit_price' => 299.00,
                    ],
                ],
                'amount_paid' => 697.00,
                'payment_mode' => 'cash',
            ]);

        $response->assertStatus(302);

        // Verify Stock Decrements: Part 1 (10 -> 8), Part 2 (5 -> 4)
        $part1 = DB::table('ms_parts_inventory')->where('id', $part1Id)->first();
        $part2 = DB::table('ms_parts_inventory')->where('id', $part2Id)->first();
        $this->assertEquals(8, $part1->stock_qty);
        $this->assertEquals(4, $part2->stock_qty);

        // Verify Header & Line Items in Database
        $sale = DB::table('ms_accessory_sales')->where('idempotency_key', $idempotencyKey)->first();
        $this->assertNotNull($sale);
        $this->assertEquals(697.00, (float) $sale->total_amount);
        $this->assertStringStartsWith('ACC-', $sale->invoice_number);

        $lineItems = DB::table('ms_accessory_sale_items')->where('accessory_sale_id', $sale->id)->get();
        $this->assertCount(2, $lineItems);

        // Verify History Ledger Entries
        $history = DB::table('ms_parts_inventory_history')->where('part_id', $part1Id)->where('type', 'deduction')->first();
        $this->assertNotNull($history);
        $this->assertEquals(2, $history->quantity);
        $this->assertEquals(8, $history->balance_after);

        // 3. Idempotency Test: Repeat same submission
        $repeatResponse = $this->actingAs($accessoriesStaff)
            ->post('/1/mobileshop/accessories/sale', [
                'idempotency_key' => $idempotencyKey,
                'customer_phone' => '9988776655',
                'customer_name' => 'Amit Verma',
                'items' => [
                    [
                        'part_id' => $part1Id,
                        'quantity' => 2,
                        'unit_price' => 199.00,
                    ],
                ],
                'amount_paid' => 398.00,
                'payment_mode' => 'cash',
            ]);

        $repeatResponse->assertStatus(302);
        // Stock should still remain 8 (NOT deducted again!)
        $part1AfterRepeat = DB::table('ms_parts_inventory')->where('id', $part1Id)->first();
        $this->assertEquals(8, $part1AfterRepeat->stock_qty);

        // 4. Void Authorization Gate: Staff cannot void (403)
        $this->actingAs($accessoriesStaff)
            ->post("/1/mobileshop/accessories/{$sale->id}/void", ['void_reason' => 'Mistake'])
            ->assertStatus(403);

        // 5. Admin Void: Restores stock and records audit trail
        $this->actingAs($admin)
            ->post("/1/mobileshop/accessories/{$sale->id}/void", ['void_reason' => 'Customer cancelled checkout at counter'])
            ->assertStatus(302);

        $part1Voided = DB::table('ms_parts_inventory')->where('id', $part1Id)->first();
        $part2Voided = DB::table('ms_parts_inventory')->where('id', $part2Id)->first();
        $this->assertEquals(10, $part1Voided->stock_qty);
        $this->assertEquals(5, $part2Voided->stock_qty);

        $voidedSale = DB::table('ms_accessory_sales')->where('id', $sale->id)->first();
        $this->assertEquals('voided', $voidedSale->status);
        $this->assertEquals($admin->id, $voidedSale->voided_by);
        $this->assertEquals('Customer cancelled checkout at counter', $voidedSale->void_reason);
    }
}

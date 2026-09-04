<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Auth\User;

class MobileShopRbacTest extends TestCase
{
    protected function getCompanyUser(string $email): User
    {
        $user = User::where('email', $email)->firstOrFail();
        session(['company_id' => 1]);
        return $user;
    }

    public function test_sales_staff_can_access_new_pos_but_not_procurement(): void
    {
        $user = $this->getCompanyUser('sales@mobitrack.local');

        $this->actingAs($user)
            ->get('/1/mobileshop/pos')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/1/mobileshop/new-mobiles')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/1/mobileshop/purchase-orders')
            ->assertStatus(403);
    }

    public function test_secondhand_staff_can_access_second_hand_hub_only(): void
    {
        $user = $this->getCompanyUser('buyback@mobitrack.local');

        $this->actingAs($user)
            ->get('/1/mobileshop/second-hand')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/1/mobileshop/pos')
            ->assertStatus(403);

        $this->actingAs($user)
            ->get('/1/mobileshop/purchase-orders')
            ->assertStatus(403);
    }

    public function test_accessories_staff_can_access_accessories_counter_only(): void
    {
        $user = $this->getCompanyUser('accessories@mobitrack.local');

        $this->actingAs($user)
            ->get('/1/mobileshop/accessories')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/1/mobileshop/pos')
            ->assertStatus(403);

        $this->actingAs($user)
            ->get('/1/mobileshop/purchase-orders')
            ->assertStatus(403);
    }

    public function test_technician_can_access_repairs_desk_only(): void
    {
        $user = $this->getCompanyUser('tech@mobitrack.local');

        $this->actingAs($user)
            ->get('/1/mobileshop/repairs')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/1/mobileshop/pos')
            ->assertStatus(403);
    }

    public function test_admin_has_master_access_to_all_panels(): void
    {
        $user = $this->getCompanyUser('admin@mobitrack.local');

        $this->actingAs($user)
            ->get('/1/mobileshop')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/1/mobileshop/pos')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/1/mobileshop/second-hand')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/1/mobileshop/accessories')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/1/mobileshop/repairs')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/1/mobileshop/purchase-orders')
            ->assertStatus(200);
    }
}

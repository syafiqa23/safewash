<?php

namespace Tests\Feature;

use App\Models\Laundry;
use App\Models\LaundryOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_admin_dashboard_and_merchant_management_pages(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $merchant = User::factory()->create([
            'role' => 'merchant',
        ]);

        $laundry = Laundry::create([
            'user_id' => $merchant->id,
            'name' => 'Laundry Test',
            'slug' => 'laundry-test',
            'subscription_plan' => 'free-signup',
            'commission_rate' => 15,
            'service_fee_rate' => 3,
            'premium_protection_enabled' => true,
        ]);

        LaundryOrder::create([
            'laundry_id' => $laundry->id,
            'customer_name' => 'Customer Test',
            'customer_phone' => '08123',
            'service_type' => 'cuci setrika',
            'weight_kg' => 3,
            'total_price' => 30000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'tracking_code' => 'SW-TEST01',
            'qr_token' => (string) Str::uuid(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.merchants.index'))
            ->assertOk();
    }
}

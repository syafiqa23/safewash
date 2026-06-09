<?php

namespace Tests\Feature;

use App\Models\Laundry;
use App\Models\LaundryOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminMerchantManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_merchants_and_export_reports(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ownerA = User::factory()->create(['role' => 'merchant', 'name' => 'Alpha Owner']);
        $ownerB = User::factory()->create(['role' => 'merchant', 'name' => 'Beta Owner']);

        Laundry::create([
            'user_id' => $ownerA->id,
            'name' => 'Alpha Laundry',
            'slug' => 'alpha-laundry',
            'subscription_plan' => 'free-signup',
            'commission_rate' => 15,
            'service_fee_rate' => 3,
            'is_active' => true,
            'premium_protection_enabled' => true,
        ]);

        Laundry::create([
            'user_id' => $ownerB->id,
            'name' => 'Beta Laundry',
            'slug' => 'beta-laundry',
            'subscription_plan' => 'free-signup',
            'commission_rate' => 15,
            'service_fee_rate' => 3,
            'is_active' => false,
            'premium_protection_enabled' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.merchants.index', ['q' => 'Alpha', 'status' => 'active']))
            ->assertOk()
            ->assertSee('Alpha Laundry')
            ->assertDontSee('Beta Laundry');

        $this->actingAs($admin)
            ->get(route('admin.reports.excel'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.reports.pdf'))
            ->assertOk();
    }

    public function test_admin_can_toggle_merchant_status_without_reload(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'merchant']);

        $laundry = Laundry::create([
            'user_id' => $owner->id,
            'name' => 'Toggle Laundry',
            'slug' => 'toggle-laundry',
            'subscription_plan' => 'free-signup',
            'commission_rate' => 15,
            'service_fee_rate' => 3,
            'is_active' => true,
            'premium_protection_enabled' => true,
        ]);

        $this->actingAs($admin)
            ->patchJson(route('admin.merchants.toggle-status', $laundry), [
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'is_active' => false,
                'label' => 'inactive',
            ]);

        $this->assertFalse($laundry->fresh()->is_active);
    }

    public function test_admin_can_export_reports_with_date_range_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'merchant']);

        $laundry = Laundry::create([
            'user_id' => $owner->id,
            'name' => 'Range Laundry',
            'slug' => 'range-laundry',
            'subscription_plan' => 'free-signup',
            'commission_rate' => 15,
            'service_fee_rate' => 3,
            'is_active' => true,
            'premium_protection_enabled' => true,
        ]);

        LaundryOrder::create([
            'laundry_id' => $laundry->id,
            'customer_name' => 'Customer Range',
            'customer_phone' => '08122',
            'service_type' => 'cuci',
            'weight_kg' => 2,
            'total_price' => 25000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'tracking_code' => 'SW-RANGE01',
            'qr_token' => (string) Str::uuid(),
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.excel', [
                'date_from' => now()->subWeek()->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.reports.pdf', [
                'date_from' => now()->subWeek()->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertOk();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Laundry;
use App\Models\LaundryOrder;
use App\Models\MerchantSettlement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SettlementCalculationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $merchant;
    private Laundry $laundry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin    = User::factory()->create(['role' => 'admin']);
        $this->merchant = User::factory()->create(['role' => 'merchant', 'phone' => '083300000001']);

        $this->laundry = Laundry::create([
            'user_id'            => $this->merchant->id,
            'name'               => 'Settlement Test Laundry',
            'slug'               => 'settlement-test-laundry',
            'subscription_plan'  => 'free-signup',
            'commission_rate'    => 15,
            'service_fee_rate'   => 3,
            'is_active'          => true,
            'premium_protection_enabled' => true,
        ]);
    }

    private function makeOrder(int $price, string $code, string $paidAt = '2026-06-01 10:00:00'): LaundryOrder
    {
        return LaundryOrder::create([
            'laundry_id'     => $this->laundry->id,
            'customer_name'  => 'Customer',
            'customer_phone' => '0800',
            'service_type'   => 'cuci',
            'weight_kg'      => 3,
            'total_price'    => $price,
            'status'         => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'payment_paid_at'=> $paidAt,
            'tracking_code'  => $code,
            'qr_token'       => (string) Str::uuid(),
        ]);
    }

    public function test_admin_can_create_settlement(): void
    {
        $this->makeOrder(100000, 'SW-SET001');

        $this->actingAs($this->admin)
            ->post(route('admin.settlement.store'), [
                'laundry_id'   => $this->laundry->id,
                'period_start' => '2026-06-01',
                'period_end'   => '2026-06-30',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('merchant_settlements', [
            'laundry_id' => $this->laundry->id,
            'status'     => 'pending',
        ]);
    }

    public function test_settlement_calculates_gross_correctly(): void
    {
        $this->makeOrder(100000, 'SW-SET002');
        $this->makeOrder(50000,  'SW-SET003');

        $this->actingAs($this->admin)
            ->post(route('admin.settlement.store'), [
                'laundry_id'   => $this->laundry->id,
                'period_start' => '2026-06-01',
                'period_end'   => '2026-06-30',
            ]);

        $settlement = MerchantSettlement::first();
        $this->assertEquals(150000, $settlement->gross_amount);
    }

    public function test_settlement_calculates_commission_and_fee(): void
    {
        // gross = 200000, commission 15% = 30000, fee 3% = 6000, net = 164000
        $this->makeOrder(200000, 'SW-SET004');

        $this->actingAs($this->admin)
            ->post(route('admin.settlement.store'), [
                'laundry_id'   => $this->laundry->id,
                'period_start' => '2026-06-01',
                'period_end'   => '2026-06-30',
            ]);

        $s = MerchantSettlement::first();
        $this->assertEquals(200000, $s->gross_amount);
        $this->assertEquals(30000,  $s->commission_amount);
        $this->assertEquals(6000,   $s->service_fee);
        $this->assertEquals(164000, $s->net_amount);
    }

    public function test_settlement_excludes_orders_outside_period(): void
    {
        $this->makeOrder(100000, 'SW-SET005', '2026-05-15 10:00:00'); // outside
        $this->makeOrder(50000,  'SW-SET006', '2026-06-10 10:00:00'); // inside

        $this->actingAs($this->admin)
            ->post(route('admin.settlement.store'), [
                'laundry_id'   => $this->laundry->id,
                'period_start' => '2026-06-01',
                'period_end'   => '2026-06-30',
            ]);

        $s = MerchantSettlement::first();
        $this->assertEquals(50000, $s->gross_amount);
        $this->assertEquals(1,     $s->order_count);
    }

    public function test_settlement_excludes_unpaid_orders(): void
    {
        // paid order
        $this->makeOrder(100000, 'SW-SET007');

        // unpaid order in same period
        LaundryOrder::create([
            'laundry_id'     => $this->laundry->id,
            'customer_name'  => 'Unpaid Customer',
            'customer_phone' => '0800',
            'service_type'   => 'cuci',
            'weight_kg'      => 2,
            'total_price'    => 50000,
            'status'         => 'completed',
            'payment_status' => 'pending',
            'payment_method' => 'cash',
            'tracking_code'  => 'SW-SET008',
            'qr_token'       => (string) Str::uuid(),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.settlement.store'), [
                'laundry_id'   => $this->laundry->id,
                'period_start' => '2026-06-01',
                'period_end'   => '2026-06-30',
            ]);

        $s = MerchantSettlement::first();
        $this->assertEquals(100000, $s->gross_amount);
        $this->assertEquals(1,      $s->order_count);
    }

    public function test_admin_can_mark_settlement_as_paid(): void
    {
        $this->makeOrder(100000, 'SW-SET009');

        $this->actingAs($this->admin)
            ->post(route('admin.settlement.store'), [
                'laundry_id'   => $this->laundry->id,
                'period_start' => '2026-06-01',
                'period_end'   => '2026-06-30',
            ]);

        $settlement = MerchantSettlement::first();

        $this->actingAs($this->admin)
            ->patch(route('admin.settlement.mark-paid', $settlement))
            ->assertRedirect();

        $this->assertSame('paid', $settlement->fresh()->status);
        $this->assertNotNull($settlement->fresh()->settled_at);
    }

    public function test_settlement_page_accessible_by_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settlement.index'))
            ->assertOk();
    }

    public function test_non_admin_cannot_create_settlement(): void
    {
        $this->actingAs($this->merchant)
            ->post(route('admin.settlement.store'), [
                'laundry_id'   => $this->laundry->id,
                'period_start' => '2026-06-01',
                'period_end'   => '2026-06-30',
            ])
            ->assertForbidden();
    }
}

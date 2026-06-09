<?php

namespace Tests\Feature;

use App\Models\Laundry;
use App\Models\LaundryOrder;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTransaction;
use App\Models\User;
use App\Services\LoyaltyProgramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoyaltyProgramTest extends TestCase
{
    use RefreshDatabase;

    private LoyaltyProgramService $service;
    private User $customer;
    private Laundry $laundry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service  = app(LoyaltyProgramService::class);
        $this->customer = User::factory()->create(['role' => 'customer', 'phone' => '082200000001']);

        $merchant = User::factory()->create(['role' => 'merchant', 'phone' => '082200000002']);
        $this->laundry = Laundry::create([
            'user_id'            => $merchant->id,
            'name'               => 'Loyalty Test Laundry',
            'slug'               => 'loyalty-test-laundry',
            'subscription_plan'  => 'free-signup',
            'commission_rate'    => 15,
            'service_fee_rate'   => 3,
            'is_active'          => true,
            'premium_protection_enabled' => true,
        ]);
    }

    private function makeOrder(int $totalPrice, string $code, string $status = 'completed', string $paymentStatus = 'paid'): LaundryOrder
    {
        return LaundryOrder::create([
            'laundry_id'      => $this->laundry->id,
            'customer_id'     => $this->customer->id,
            'customer_name'   => $this->customer->name,
            'customer_email'  => $this->customer->email,
            'customer_phone'  => $this->customer->phone,
            'service_type'    => 'cuci',
            'weight_kg'       => 3,
            'total_price'     => $totalPrice,
            'status'          => $status,
            'payment_status'  => $paymentStatus,
            'payment_method'  => 'cash',
            'payment_paid_at' => $paymentStatus === 'paid' ? now() : null,
            'tracking_code'   => $code,
            'qr_token'        => (string) Str::uuid(),
        ]);
    }

    public function test_points_formula_minimum_ten_points(): void
    {
        // total_price = 10000 → floor(10000/5000) = 2, but minimum is 10
        $order = $this->makeOrder(10000, 'SW-LY001');
        $tx = $this->service->awardCompletedOrder($order);

        $this->assertNotNull($tx);
        $this->assertSame(10, $tx->points);
    }

    public function test_points_formula_scales_with_price(): void
    {
        // total_price = 75000 → floor(75000/5000) = 15, above minimum
        $order = $this->makeOrder(75000, 'SW-LY002');
        $tx = $this->service->awardCompletedOrder($order);

        $this->assertNotNull($tx);
        $this->assertSame(15, $tx->points);
    }

    public function test_loyalty_account_is_created_on_first_award(): void
    {
        $this->assertDatabaseMissing('loyalty_accounts', ['customer_id' => $this->customer->id]);

        $order = $this->makeOrder(50000, 'SW-LY003');
        $this->service->awardCompletedOrder($order);

        $this->assertDatabaseHas('loyalty_accounts', ['customer_id' => $this->customer->id]);
    }

    public function test_duplicate_earn_not_created_for_same_order(): void
    {
        $order = $this->makeOrder(50000, 'SW-LY004');

        $this->service->awardCompletedOrder($order);
        $this->service->awardCompletedOrder($order); // second call

        $this->assertSame(1, LoyaltyTransaction::where('laundry_order_id', $order->id)->where('type', 'earn')->count());
    }

    public function test_returns_null_if_order_not_paid(): void
    {
        $order = $this->makeOrder(50000, 'SW-LY005', 'completed', 'pending');
        $result = $this->service->awardCompletedOrder($order);

        $this->assertNull($result);
        $this->assertDatabaseMissing('loyalty_accounts', ['customer_id' => $this->customer->id]);
    }

    public function test_returns_null_if_order_not_completed(): void
    {
        $order = $this->makeOrder(50000, 'SW-LY006', 'washing', 'paid');
        $result = $this->service->awardCompletedOrder($order);

        $this->assertNull($result);
    }

    public function test_returns_null_if_order_has_no_customer(): void
    {
        $order = LaundryOrder::create([
            'laundry_id'     => $this->laundry->id,
            'customer_id'    => null,
            'customer_name'  => 'Walk-in',
            'customer_phone' => '0800',
            'service_type'   => 'cuci',
            'weight_kg'      => 2,
            'total_price'    => 20000,
            'status'         => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'tracking_code'  => 'SW-LY007',
            'qr_token'       => (string) Str::uuid(),
        ]);

        $this->assertNull($this->service->awardCompletedOrder($order));
    }

    public function test_tier_upgrades_ocean_to_sky(): void
    {
        // Sky threshold = 200 pts. Need 14 orders × 15pts = 210 pts
        for ($i = 1; $i <= 14; $i++) {
            $order = $this->makeOrder(75000, 'SW-TIER-'.str_pad($i, 3, '0', STR_PAD_LEFT));
            $this->service->awardCompletedOrder($order);
        }

        $account = LoyaltyAccount::where('customer_id', $this->customer->id)->first();
        $this->assertSame('Sky', $account->tier);
    }

    public function test_tier_upgrades_sky_to_cloud(): void
    {
        // Cloud threshold = 500 pts. Need 34 orders × 15pts = 510 pts
        for ($i = 1; $i <= 34; $i++) {
            $order = $this->makeOrder(75000, 'SW-CLOUD-'.str_pad($i, 3, '0', STR_PAD_LEFT));
            $this->service->awardCompletedOrder($order);
        }

        $account = LoyaltyAccount::where('customer_id', $this->customer->id)->first();
        $this->assertSame('Cloud', $account->tier);
    }

    public function test_order_loyalty_points_earned_field_is_set(): void
    {
        $order = $this->makeOrder(75000, 'SW-LY008');
        $this->service->awardCompletedOrder($order);

        $this->assertGreaterThan(0, $order->fresh()->loyalty_points_earned);
    }

    public function test_loyalty_page_accessible_by_customer(): void
    {
        $this->actingAs($this->customer)
            ->get(route('loyalty.index'))
            ->assertOk();
    }
}

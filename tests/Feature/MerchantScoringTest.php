<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Laundry;
use App\Models\LaundryOrder;
use App\Models\User;
use App\Services\MerchantScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MerchantScoringTest extends TestCase
{
    use RefreshDatabase;

    private MerchantScoringService $service;
    private Laundry $laundry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(MerchantScoringService::class);

        $merchant = User::factory()->create(['role' => 'merchant', 'phone' => '084400000001']);
        $this->laundry = Laundry::create([
            'user_id'               => $merchant->id,
            'name'                  => 'Score Test Laundry',
            'slug'                  => 'score-test-laundry',
            'subscription_plan'     => 'free-signup',
            'commission_rate'       => 15,
            'service_fee_rate'      => 3,
            'is_active'             => true,
            'supports_white_label'  => false,
            'premium_protection_enabled' => true,
        ]);
    }

    private function makeOrder(array $overrides = []): LaundryOrder
    {
        static $counter = 0;
        $counter++;

        return LaundryOrder::create(array_merge([
            'laundry_id'          => $this->laundry->id,
            'customer_name'       => 'Customer',
            'customer_phone'      => '0800',
            'service_type'        => 'cuci',
            'weight_kg'           => 3,
            'total_price'         => 30000,
            'status'              => 'completed',
            'payment_status'      => 'paid',
            'payment_method'      => 'cash',
            'pickup_delivery_opt_in' => false,
            'tracking_code'       => 'SW-SC-'.str_pad($counter, 4, '0', STR_PAD_LEFT),
            'qr_token'            => (string) Str::uuid(),
        ], $overrides));
    }

    public function test_score_is_zero_for_merchant_with_no_orders(): void
    {
        $score = $this->service->recalculate($this->laundry);

        $this->assertSame(0.0, $score);
        $this->assertSame(0.0, (float) $this->laundry->fresh()->merchant_score);
    }

    public function test_perfect_score_all_completed_paid_digital_delivery(): void
    {
        // 4 orders: all completed, paid via digital, with delivery opt-in
        for ($i = 0; $i < 4; $i++) {
            $this->makeOrder([
                'status'              => 'completed',
                'payment_status'      => 'paid',
                'payment_method'      => 'qris',
                'pickup_delivery_opt_in' => true,
            ]);
        }

        // completedRate=1×40 + paidRate=1×25 + digitalRate=1×15 + deliveryRate=1×10 = 90
        $score = $this->service->recalculate($this->laundry);
        $this->assertEquals(90.0, $score);
    }

    public function test_white_label_bonus_adds_ten_points(): void
    {
        $this->laundry->update(['supports_white_label' => true]);

        $this->makeOrder(['payment_method' => 'qris', 'pickup_delivery_opt_in' => true]);

        // completedRate=1×40 + paidRate=1×25 + digital=1×15 + delivery=1×10 + whitelabel=10 = 100
        $score = $this->service->recalculate($this->laundry);
        $this->assertEquals(100.0, $score);
    }

    public function test_claim_penalty_reduces_score(): void
    {
        $order = $this->makeOrder();

        Claim::create([
            'laundry_order_id'  => $order->id,
            'claimant_name'     => 'Penalty Customer',
            'claimant_contact'  => '0800',
            'claim_type'        => 'rusak',
            'item_name'         => 'Baju',
            'description'       => 'Rusak.',
            'status'            => 'submitted', // not resolved → triggers penalty
            'submitted_at'      => now(),
        ]);

        // 1 order all completed+paid+cash+no-delivery: 40+25+0+0 = 65
        // claim penalty: 1 unresolved / 1 order = 1 → -20
        // = 45
        $score = $this->service->recalculate($this->laundry);
        $this->assertEquals(45.0, $score);
    }

    public function test_resolved_claim_does_not_trigger_penalty(): void
    {
        $order = $this->makeOrder();

        Claim::create([
            'laundry_order_id'  => $order->id,
            'claimant_name'     => 'Resolved Customer',
            'claimant_contact'  => '0800',
            'claim_type'        => 'rusak',
            'item_name'         => 'Baju',
            'description'       => 'Rusak.',
            'status'            => 'resolved', // resolved → no penalty
            'submitted_at'      => now(),
        ]);

        // No claim penalty: 40+25 = 65
        $score = $this->service->recalculate($this->laundry);
        $this->assertEquals(65.0, $score);
    }

    public function test_score_clamped_to_zero_minimum(): void
    {
        // 5 orders, 5 unresolved claims → penalty can exceed raw score
        for ($i = 0; $i < 5; $i++) {
            $order = $this->makeOrder([
                'status'         => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'cash',
            ]);

            Claim::create([
                'laundry_order_id' => $order->id,
                'claimant_name'    => 'C',
                'claimant_contact' => '0',
                'claim_type'       => 'hilang',
                'item_name'        => 'X',
                'description'      => 'X',
                'status'           => 'submitted',
                'submitted_at'     => now(),
            ]);
        }

        $score = $this->service->recalculate($this->laundry);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function test_score_is_persisted_to_laundry_table(): void
    {
        $this->makeOrder(['payment_method' => 'qris']);

        $this->service->recalculate($this->laundry);

        $this->assertNotNull($this->laundry->fresh()->merchant_score);
        $this->assertNotNull($this->laundry->fresh()->score_last_calculated_at);
    }
}

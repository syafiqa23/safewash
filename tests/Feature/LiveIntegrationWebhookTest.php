<?php

namespace Tests\Feature;

use App\Models\Laundry;
use App\Models\LaundryOrder;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LiveIntegrationWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_midtrans_webhook_can_mark_order_paid(): void
    {
        config()->set('services.midtrans.server_key', 'server-key-test');

        $merchant = User::factory()->create(['role' => 'merchant', 'phone' => '081111111111']);
        $laundry = Laundry::create([
            'user_id' => $merchant->id,
            'name' => 'Webhook Laundry',
            'slug' => 'webhook-laundry',
            'subscription_plan' => 'free-signup',
            'commission_rate' => 15,
            'service_fee_rate' => 3,
            'is_active' => true,
            'premium_protection_enabled' => true,
        ]);

        $order = LaundryOrder::create([
            'laundry_id' => $laundry->id,
            'customer_name' => 'Webhook Customer',
            'customer_phone' => '081222222222',
            'service_type' => 'cuci',
            'weight_kg' => 3,
            'total_price' => 30000,
            'status' => 'received',
            'payment_status' => 'pending',
            'payment_method' => 'qris',
            'tracking_code' => 'SW-WEBHOOK',
            'qr_token' => (string) Str::uuid(),
        ]);

        PaymentTransaction::create([
            'laundry_order_id' => $order->id,
            'provider' => 'midtrans',
            'gateway_name' => 'Midtrans Snap',
            'payment_method' => 'qris',
            'reference' => 'MID-WEBHOOK-001',
            'gross_amount' => 30000,
            'gateway_fee' => 0,
            'net_amount' => 30000,
            'status' => 'pending',
        ]);

        $payload = [
            'order_id' => 'MID-WEBHOOK-001',
            'status_code' => '200',
            'gross_amount' => '30000.00',
            'transaction_status' => 'settlement',
        ];
        $payload['signature_key'] = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'server-key-test');

        $this->postJson(route('webhooks.payments.midtrans'), $payload)
            ->assertOk();

        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_whatsapp_webhook_verification_returns_challenge(): void
    {
        config()->set('services.whatsapp.verify_token', 'safe-token');

        $this->get(route('webhooks.whatsapp.verify', [
            'hub_mode' => 'subscribe',
            'hub_verify_token' => 'safe-token',
            'hub_challenge' => '123456',
        ]))
            ->assertOk()
            ->assertSeeText('123456');
    }
}

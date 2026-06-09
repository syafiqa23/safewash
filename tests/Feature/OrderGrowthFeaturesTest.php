<?php

namespace Tests\Feature;

use App\Models\DeliveryRequest;
use App\Models\Laundry;
use App\Models\LaundryOrder;
use App\Models\LoyaltyAccount;
use App\Models\NotificationLog;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderGrowthFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_merchant_can_create_order_with_digital_payment_and_pickup_delivery(): void
    {
        $merchant = User::factory()->create(['role' => 'merchant', 'phone' => '081111111111']);
        $customer = User::factory()->create(['role' => 'customer', 'phone' => '082222222222']);

        $laundry = Laundry::create([
            'user_id' => $merchant->id,
            'name' => 'Growth Laundry',
            'slug' => 'growth-laundry',
            'subscription_plan' => 'free-signup',
            'commission_rate' => 15,
            'service_fee_rate' => 3,
            'is_active' => true,
            'premium_protection_enabled' => true,
        ]);

        $this->actingAs($merchant)
            ->post(route('orders.store'), [
                'laundry_id' => $laundry->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'service_type' => 'cuci express',
                'weight_kg' => 3,
                'total_price' => 45000,
                'payment_status' => 'paid',
                'payment_method' => 'qris',
                'pickup_delivery_opt_in' => 1,
                'pickup_delivery_type' => 'round_trip',
                'pickup_delivery_fee' => 12000,
                'pickup_address' => 'Alamat pickup',
                'delivery_address' => 'Alamat delivery',
                'items' => 'Kemeja | 2',
            ])
            ->assertRedirect();

        $order = LaundryOrder::first();

        $this->assertNotNull($order);
        $this->assertSame('qris', $order->payment_method);
        $this->assertTrue($order->pickup_delivery_opt_in);
        $this->assertDatabaseHas('payment_transactions', [
            'laundry_order_id' => $order->id,
            'payment_method' => 'qris',
            'status' => 'settled',
        ]);
        $this->assertDatabaseHas('delivery_requests', [
            'laundry_order_id' => $order->id,
            'service_type' => 'round_trip',
            'status' => 'scheduled',
        ]);
        $this->assertDatabaseHas('notification_logs', [
            'laundry_order_id' => $order->id,
            'channel' => 'whatsapp-gateway',
        ]);
    }

    public function test_completed_paid_order_awards_loyalty_points_to_customer(): void
    {
        $merchant = User::factory()->create(['role' => 'merchant', 'phone' => '083333333333']);
        $customer = User::factory()->create(['role' => 'customer', 'phone' => '084444444444']);

        $laundry = Laundry::create([
            'user_id' => $merchant->id,
            'name' => 'Loyal Laundry',
            'slug' => 'loyal-laundry',
            'subscription_plan' => 'free-signup',
            'commission_rate' => 15,
            'service_fee_rate' => 3,
            'is_active' => true,
            'premium_protection_enabled' => true,
        ]);

        $order = LaundryOrder::create([
            'laundry_id' => $laundry->id,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'service_type' => 'cuci lipat',
            'weight_kg' => 4,
            'total_price' => 50000,
            'status' => 'washing',
            'payment_status' => 'paid',
            'payment_method' => 'ewallet',
            'payment_paid_at' => now(),
            'tracking_code' => 'SW-LOYAL01',
            'qr_token' => (string) Str::uuid(),
        ]);

        $this->actingAs($merchant)
            ->post(route('orders.status', $order), [
                'status' => 'completed',
                'title' => 'Selesai diproses',
                'description' => 'Order telah selesai dan siap diterima customer.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('loyalty_accounts', [
            'customer_id' => $customer->id,
        ]);
        $this->assertGreaterThan(0, $order->fresh()->loyalty_points_earned);
    }
}

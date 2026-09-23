<?php

namespace Tests\Feature;

use App\Models\Laundry;
use App\Models\LaundryOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $merchant;
    private User $customer;
    private Laundry $laundry;
    private LaundryOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin    = User::factory()->create(['role' => 'admin']);
        $this->merchant = User::factory()->create(['role' => 'merchant', 'phone' => '085500000001']);
        $this->customer = User::factory()->create(['role' => 'customer', 'phone' => '085500000002']);

        $this->laundry = Laundry::create([
            'user_id'            => $this->merchant->id,
            'name'               => 'Auth Test Laundry',
            'slug'               => 'auth-test-laundry',
            'subscription_plan'  => 'free-signup',
            'commission_rate'    => 15,
            'service_fee_rate'   => 3,
            'is_active'          => true,
            'premium_protection_enabled' => true,
        ]);

        $this->order = LaundryOrder::create([
            'laundry_id'     => $this->laundry->id,
            'customer_id'    => $this->customer->id,
            'customer_name'  => $this->customer->name,
            'customer_email' => $this->customer->email,
            'customer_phone' => $this->customer->phone,
            'service_type'   => 'cuci',
            'weight_kg'      => 3,
            'total_price'    => 30000,
            'status'         => 'received',
            'payment_status' => 'pending',
            'payment_method' => 'cash',
            'tracking_code'  => 'SW-AUTH01',
            'qr_token'       => (string) Str::uuid(),
        ]);
    }

    // ── Unauthenticated ────────────────────────────────────────────────────────

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('orders.index'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    // ── Admin-only routes ──────────────────────────────────────────────────────

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $this->actingAs($this->customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_merchant_cannot_access_admin_dashboard(): void
    {
        $this->actingAs($this->merchant)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_customer_cannot_access_admin_settlement(): void
    {
        $this->actingAs($this->customer)
            ->get(route('admin.settlement.index'))
            ->assertForbidden();
    }

    public function test_merchant_cannot_access_admin_reports(): void
    {
        $this->actingAs($this->merchant)
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }

    // ── Create/update order (admin + merchant only) ────────────────────────────

    public function test_customer_cannot_access_order_create_page(): void
    {
        $this->actingAs($this->customer)
            ->get(route('orders.create'))
            ->assertForbidden();
    }

    public function test_customer_cannot_post_create_order(): void
    {
        $this->actingAs($this->customer)
            ->post(route('orders.store'), [
                'laundry_id'     => $this->laundry->id,
                'customer_name'  => 'Hacker',
                'customer_phone' => '0800',
                'service_type'   => 'cuci',
                'weight_kg'      => 1,
                'total_price'    => 10000,
                'payment_status' => 'pending',
                'payment_method' => 'cash',
            ])
            ->assertForbidden();
    }

    public function test_merchant_can_create_order(): void
    {
        $this->actingAs($this->merchant)
            ->post(route('orders.store'), [
                'laundry_id'     => $this->laundry->id,
                'customer_name'  => 'New Customer',
                'customer_phone' => '082200000099',
                'service_type'   => 'cuci lipat',
                'weight_kg'      => 2,
                'total_price'    => 20000,
                'payment_status' => 'pending',
                'payment_method' => 'cash',
            ])
            ->assertRedirect();
    }

    // ── Merchant-specific routes ───────────────────────────────────────────────

    public function test_customer_cannot_access_merchant_revenue(): void
    {
        $this->actingAs($this->customer)
            ->get(route('merchant.revenue.index'))
            ->assertForbidden();
    }

    public function test_customer_cannot_access_merchant_customers(): void
    {
        $this->actingAs($this->customer)
            ->get(route('merchant.customers.index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_access_merchant_only_routes(): void
    {
        $this->actingAs($this->admin)
            ->get(route('merchant.revenue.index'))
            ->assertForbidden();
    }

    // ── Order show: only owner or admin ───────────────────────────────────────

    public function test_customer_can_view_their_own_order(): void
    {
        $this->actingAs($this->customer)
            ->get(route('orders.show', $this->order))
            ->assertOk();
    }

    public function test_other_customer_cannot_view_someone_elses_order(): void
    {
        $otherCustomer = User::factory()->create(['role' => 'customer', 'phone' => '085500000099']);

        $this->actingAs($otherCustomer)
            ->get(route('orders.show', $this->order))
            ->assertForbidden();
    }

    public function test_merchant_owner_can_view_their_laundry_order(): void
    {
        $this->actingAs($this->merchant)
            ->get(route('orders.show', $this->order))
            ->assertOk();
    }

    public function test_admin_can_view_any_order(): void
    {
        $this->actingAs($this->admin)
            ->get(route('orders.show', $this->order))
            ->assertOk();
    }

    public function test_customer_api_only_lists_their_own_orders(): void
    {
        $otherCustomer = User::factory()->create(['role' => 'customer', 'phone' => '085500000099']);
        LaundryOrder::create([
            'laundry_id'     => $this->laundry->id,
            'customer_id'    => $otherCustomer->id,
            'customer_name'  => $otherCustomer->name,
            'customer_email' => $otherCustomer->email,
            'customer_phone' => $otherCustomer->phone,
            'service_type'   => 'cuci',
            'weight_kg'      => 2,
            'total_price'    => 20000,
            'status'         => 'received',
            'payment_status' => 'pending',
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs($this->customer)->getJson('/api/orders');

        $response->assertOk()->assertJsonCount(1);
        $this->assertSame($this->order->id, $response->json('0.id'));
    }

    public function test_customer_api_cannot_claim_another_customers_order(): void
    {
        $otherCustomer = User::factory()->create(['role' => 'customer', 'phone' => '085500000099']);
        $otherOrder = LaundryOrder::create([
            'laundry_id'     => $this->laundry->id,
            'customer_id'    => $otherCustomer->id,
            'customer_name'  => $otherCustomer->name,
            'customer_email' => $otherCustomer->email,
            'customer_phone' => $otherCustomer->phone,
            'service_type'   => 'cuci',
            'weight_kg'      => 2,
            'total_price'    => 20000,
            'status'         => 'received',
            'payment_status' => 'pending',
            'payment_method' => 'cash',
        ]);

        $this->actingAs($this->customer)
            ->postJson('/api/claims', [
                'laundry_order_id' => $otherOrder->id,
                'claimant_name'    => $this->customer->name,
                'claimant_contact' => $this->customer->phone,
                'item_name'        => 'Baju',
                'description'      => 'Unauthorized claim',
            ])
            ->assertForbidden();
    }

    // ── Notification pages ────────────────────────────────────────────────────

    public function test_notification_index_accessible_by_all_authenticated_roles(): void
    {
        foreach ([$this->admin, $this->merchant, $this->customer] as $user) {
            $this->actingAs($user)
                ->get(route('notifications.index'))
                ->assertOk();
        }
    }

    public function test_notification_bell_returns_json_for_all_roles(): void
    {
        foreach ([$this->admin, $this->merchant, $this->customer] as $user) {
            $response = $this->actingAs($user)
                ->getJson(route('notifications.bell'))
                ->assertOk()
                ->assertJsonStructure(['notifications', 'unread_count']);

            $this->assertIsInt($response->json('unread_count'));
        }
    }

    // ── Public tracking (no auth) ─────────────────────────────────────────────

    public function test_public_qr_tracking_accessible_without_auth(): void
    {
        $this->get(route('tracking.show', $this->order->tracking_code))
            ->assertOk();
    }
}

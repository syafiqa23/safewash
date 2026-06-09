<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Laundry;
use App\Models\LaundryOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClaimWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $merchant;
    private User $customer;
    private Laundry $laundry;
    private LaundryOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->merchant = User::factory()->create(['role' => 'merchant', 'phone' => '081100000001']);
        $this->customer = User::factory()->create(['role' => 'customer', 'phone' => '081100000002']);

        $this->laundry = Laundry::create([
            'user_id'                  => $this->merchant->id,
            'name'                     => 'Claim Test Laundry',
            'slug'                     => 'claim-test-laundry',
            'subscription_plan'        => 'free-signup',
            'commission_rate'          => 15,
            'service_fee_rate'         => 3,
            'is_active'                => true,
            'premium_protection_enabled' => true,
        ]);

        $this->order = LaundryOrder::create([
            'laundry_id'      => $this->laundry->id,
            'customer_id'     => $this->customer->id,
            'customer_name'   => $this->customer->name,
            'customer_email'  => $this->customer->email,
            'customer_phone'  => $this->customer->phone,
            'service_type'    => 'cuci setrika',
            'weight_kg'       => 3,
            'total_price'     => 45000,
            'status'          => 'completed',
            'payment_status'  => 'paid',
            'payment_method'  => 'cash',
            'tracking_code'   => 'SW-CLAIM01',
            'qr_token'        => (string) Str::uuid(),
        ]);
    }

    public function test_customer_can_submit_claim_on_their_order(): void
    {
        $this->actingAs($this->customer)
            ->post(route('claims.store', $this->order), [
                'claimant_name'    => $this->customer->name,
                'claimant_contact' => $this->customer->phone,
                'claim_type'       => 'rusak',
                'item_name'        => 'Kemeja putih',
                'description'      => 'Kemeja sobek setelah dicuci, ada robekan di bagian lengan.',
                'loss_amount'      => 150000,
            ])
            ->assertRedirect(route('claims.index'));

        $this->assertDatabaseHas('claims', [
            'laundry_order_id' => $this->order->id,
            'customer_id'      => $this->customer->id,
            'claim_type'       => 'rusak',
            'item_name'        => 'Kemeja putih',
            'status'           => 'submitted',
        ]);

        $this->assertSame('submitted', $this->order->fresh()->claim_status);
    }

    public function test_customer_cannot_submit_claim_on_another_customers_order(): void
    {
        $otherCustomer = User::factory()->create(['role' => 'customer', 'phone' => '081100000099']);

        $this->actingAs($otherCustomer)
            ->post(route('claims.store', $this->order), [
                'claimant_name'    => 'Penipu',
                'claimant_contact' => '0811',
                'claim_type'       => 'hilang',
                'item_name'        => 'Barang orang lain',
                'description'      => 'Mencoba klaim order milik customer lain.',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('claims', ['laundry_order_id' => $this->order->id]);
    }

    public function test_admin_can_view_all_claims(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Claim::create([
            'laundry_order_id'  => $this->order->id,
            'customer_id'       => $this->customer->id,
            'claimant_name'     => $this->customer->name,
            'claimant_contact'  => $this->customer->phone,
            'claim_type'        => 'hilang',
            'item_name'         => 'Celana jeans',
            'description'       => 'Tidak dikembalikan.',
            'status'            => 'submitted',
            'submitted_at'      => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('claims.index'))
            ->assertOk()
            ->assertSee('Celana jeans');
    }

    public function test_merchant_sees_only_their_laundry_claims(): void
    {
        // Claim on this merchant's order
        Claim::create([
            'laundry_order_id'  => $this->order->id,
            'customer_id'       => $this->customer->id,
            'claimant_name'     => $this->customer->name,
            'claimant_contact'  => $this->customer->phone,
            'claim_type'        => 'rusak',
            'item_name'         => 'Baju merchant ini',
            'description'       => 'Rusak.',
            'status'            => 'submitted',
            'submitted_at'      => now(),
        ]);

        // Claim on a different merchant's order
        $otherMerchant = User::factory()->create(['role' => 'merchant', 'phone' => '081100000088']);
        $otherLaundry = Laundry::create([
            'user_id' => $otherMerchant->id, 'name' => 'Other Laundry', 'slug' => 'other-laundry',
            'subscription_plan' => 'free-signup', 'commission_rate' => 15, 'service_fee_rate' => 3,
        ]);
        $otherOrder = LaundryOrder::create([
            'laundry_id' => $otherLaundry->id, 'customer_name' => 'X', 'customer_phone' => '0800',
            'service_type' => 'cuci', 'weight_kg' => 1, 'total_price' => 10000,
            'status' => 'completed', 'payment_status' => 'paid', 'payment_method' => 'cash',
            'tracking_code' => 'SW-OTHER01', 'qr_token' => (string) Str::uuid(),
        ]);
        Claim::create([
            'laundry_order_id'  => $otherOrder->id,
            'claimant_name'     => 'Other Customer',
            'claimant_contact'  => '0800',
            'claim_type'        => 'tertukar',
            'item_name'         => 'Baju merchant lain',
            'description'       => 'Tertukar.',
            'status'            => 'submitted',
            'submitted_at'      => now(),
        ]);

        $response = $this->actingAs($this->merchant)
            ->get(route('claims.index'))
            ->assertOk();

        $response->assertSee('Baju merchant ini');
        $response->assertDontSee('Baju merchant lain');
    }

    public function test_customer_sees_only_their_own_claims(): void
    {
        $otherCustomer = User::factory()->create(['role' => 'customer', 'phone' => '081100000077']);

        Claim::create([
            'laundry_order_id'  => $this->order->id,
            'customer_id'       => $this->customer->id,
            'claimant_name'     => 'Klaim Milikku',
            'claimant_contact'  => $this->customer->phone,
            'claim_type'        => 'hilang',
            'item_name'         => 'Baju saya',
            'description'       => 'Hilang.',
            'status'            => 'submitted',
            'submitted_at'      => now(),
        ]);

        Claim::create([
            'laundry_order_id'  => $this->order->id,
            'customer_id'       => $otherCustomer->id,
            'claimant_name'     => 'Klaim Orang Lain',
            'claimant_contact'  => $otherCustomer->phone,
            'claim_type'        => 'rusak',
            'item_name'         => 'Baju orang lain',
            'description'       => 'Rusak.',
            'status'            => 'submitted',
            'submitted_at'      => now(),
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('claims.index'))
            ->assertOk();

        $response->assertSee('Baju saya');
        $response->assertDontSee('Baju orang lain');
    }

    public function test_admin_can_resolve_a_claim(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $claim = Claim::create([
            'laundry_order_id'  => $this->order->id,
            'customer_id'       => $this->customer->id,
            'claimant_name'     => $this->customer->name,
            'claimant_contact'  => $this->customer->phone,
            'claim_type'        => 'rusak',
            'item_name'         => 'Baju resolve test',
            'description'       => 'Rusak parah.',
            'status'            => 'submitted',
            'submitted_at'      => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.claims.update', $claim), [
                'status'               => 'compensated',
                'compensation_amount'  => 200000,
                'resolution_notes'     => 'Dikompensasi penuh sesuai nilai barang.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('claims', [
            'id'                  => $claim->id,
            'status'              => 'compensated',
            'compensation_amount' => 200000,
        ]);
    }
}

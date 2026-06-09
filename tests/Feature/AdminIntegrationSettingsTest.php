<?php

namespace Tests\Feature;

use App\Models\IntegrationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminIntegrationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_integrations_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.integrations.index'))
            ->assertOk()
            ->assertSee('Pengaturan Integrasi');
    }

    public function test_admin_can_store_integration_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.integrations.update'), [
                'public_app_url' => 'https://safewash.example.com',
                'payment_provider' => 'midtrans',
                'payment_mode' => 'production',
                'midtrans_server_key' => 'server-key-live',
                'midtrans_client_key' => 'client-key-live',
                'midtrans_is_production' => 1,
                'xendit_api_version' => '2024-11-11',
                'whatsapp_gateway_name' => 'WhatsApp Cloud API',
                'whatsapp_enabled' => 1,
                'whatsapp_phone_number_id' => '123456',
                'whatsapp_access_token' => 'secret-token',
                'whatsapp_verify_token' => 'verify-me',
                'whatsapp_api_version' => 'v23.0',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('integration_settings', [
            'key' => 'payment_provider',
        ]);
        $this->assertDatabaseHas('integration_settings', [
            'key' => 'public_app_url',
            'value' => 'https://safewash.example.com',
        ]);
        $this->assertTrue(IntegrationSetting::where('key', 'midtrans_server_key')->first()?->is_secret);
    }
}

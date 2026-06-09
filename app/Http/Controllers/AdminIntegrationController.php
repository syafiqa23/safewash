<?php

namespace App\Http\Controllers;

use App\Services\IntegrationSettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminIntegrationController extends Controller
{
    public function index(IntegrationSettingsService $settings): View
    {
        $appUrl = rtrim($settings->appUrl(), '/');

        $form = [
            'public_app_url' => $settings->get('public_app_url', config('app.url')),
            'payment_provider' => $settings->get('payment_provider', 'midtrans'),
            'payment_mode' => $settings->get('payment_mode', config('safewash.payment.mode', 'sandbox')),
            'midtrans_server_key' => $settings->masked('midtrans_server_key'),
            'midtrans_client_key' => $settings->masked('midtrans_client_key'),
            'midtrans_is_production' => filter_var($settings->get('midtrans_is_production', false), FILTER_VALIDATE_BOOL),
            'xendit_secret_key' => $settings->masked('xendit_secret_key'),
            'xendit_webhook_token' => $settings->masked('xendit_webhook_token'),
            'xendit_api_version' => $settings->get('xendit_api_version', '2024-11-11'),
            'whatsapp_enabled' => filter_var($settings->get('whatsapp_enabled', false), FILTER_VALIDATE_BOOL),
            'whatsapp_gateway_name' => $settings->get('whatsapp_gateway_name', 'WhatsApp Cloud API'),
            'whatsapp_phone_number_id' => $settings->masked('whatsapp_phone_number_id'),
            'whatsapp_access_token' => $settings->masked('whatsapp_access_token'),
            'whatsapp_verify_token' => $settings->masked('whatsapp_verify_token'),
            'whatsapp_api_version' => $settings->get('whatsapp_api_version', 'v23.0'),
        ];

        $webhooks = [
            'midtrans' => $appUrl.'/webhooks/payments/midtrans',
            'xendit' => $appUrl.'/webhooks/payments/xendit',
            'whatsapp_verify' => $appUrl.'/webhooks/whatsapp',
            'whatsapp_receive' => $appUrl.'/webhooks/whatsapp',
        ];

        return view('admin.integrations', compact('form', 'webhooks'));
    }

    public function update(Request $request, IntegrationSettingsService $settings): RedirectResponse
    {
        $data = $request->validate([
            'public_app_url' => ['required', 'url'],
            'payment_provider' => ['required', 'in:midtrans,xendit,simulator'],
            'payment_mode' => ['required', 'in:sandbox,production'],
            'midtrans_server_key' => ['nullable', 'string'],
            'midtrans_client_key' => ['nullable', 'string'],
            'midtrans_is_production' => ['nullable', 'boolean'],
            'xendit_secret_key' => ['nullable', 'string'],
            'xendit_webhook_token' => ['nullable', 'string'],
            'xendit_api_version' => ['nullable', 'string', 'max:50'],
            'whatsapp_enabled' => ['nullable', 'boolean'],
            'whatsapp_gateway_name' => ['nullable', 'string', 'max:255'],
            'whatsapp_phone_number_id' => ['nullable', 'string'],
            'whatsapp_access_token' => ['nullable', 'string'],
            'whatsapp_verify_token' => ['nullable', 'string'],
            'whatsapp_api_version' => ['nullable', 'string', 'max:20'],
        ]);

        $settings->putMany([
            'public_app_url' => $data['public_app_url'],
            'payment_provider' => $data['payment_provider'],
            'payment_mode' => $data['payment_mode'],
            'midtrans_server_key' => $data['midtrans_server_key'] ?? null,
            'midtrans_client_key' => $data['midtrans_client_key'] ?? null,
            'midtrans_is_production' => $request->boolean('midtrans_is_production') ? '1' : '0',
            'xendit_secret_key' => $data['xendit_secret_key'] ?? null,
            'xendit_webhook_token' => $data['xendit_webhook_token'] ?? null,
            'xendit_api_version' => $data['xendit_api_version'] ?? '2024-11-11',
            'whatsapp_enabled' => $request->boolean('whatsapp_enabled') ? '1' : '0',
            'whatsapp_gateway_name' => $data['whatsapp_gateway_name'] ?? 'WhatsApp Cloud API',
            'whatsapp_phone_number_id' => $data['whatsapp_phone_number_id'] ?? null,
            'whatsapp_access_token' => $data['whatsapp_access_token'] ?? null,
            'whatsapp_verify_token' => $data['whatsapp_verify_token'] ?? null,
            'whatsapp_api_version' => $data['whatsapp_api_version'] ?? 'v23.0',
        ], $request->user()->id, [
            'midtrans_server_key',
            'midtrans_client_key',
            'xendit_secret_key',
            'xendit_webhook_token',
            'whatsapp_phone_number_id',
            'whatsapp_access_token',
            'whatsapp_verify_token',
        ]);

        return back()->with('success', 'Pengaturan integrasi SafeWash berhasil disimpan.');
    }
}

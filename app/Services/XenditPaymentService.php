<?php

namespace App\Services;

use App\Models\LaundryOrder;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class XenditPaymentService
{
    public function __construct(private readonly IntegrationSettingsService $settings)
    {
    }

    public function createCheckout(LaundryOrder $order): PaymentTransaction
    {
        $secretKey = (string) $this->settings->get('xendit_secret_key', config('services.xendit.secret_key'));
        $apiVersion = (string) $this->settings->get('xendit_api_version', config('services.xendit.api_version', '2024-11-11'));

        if ($secretKey === '') {
            return $this->storeFallback($order, 'Xendit key belum diisi');
        }

        $reference = $order->payment_reference ?: 'XND-'.Str::upper(Str::random(10));
        $payload = [
            'reference_id' => $reference,
            'type' => 'PAY',
            'country' => 'ID',
            'currency' => 'IDR',
            'request_amount' => (int) round((float) $order->total_price),
            'capture_method' => 'AUTOMATIC',
            'customer' => [
                'reference_id' => 'CUS-'.($order->customer_id ?: $order->tracking_code),
                'given_names' => $order->customer_name,
                'mobile_number' => $order->customer_phone,
                'email' => $order->customer_email,
            ],
            'metadata' => [
                'tracking_code' => $order->tracking_code,
                'laundry_order_id' => $order->id,
            ],
        ];

        $response = Http::withBasicAuth($secretKey, '')
            ->withHeaders(['api-version' => $apiVersion])
            ->acceptJson()
            ->post('https://api.xendit.co/v3/payment_requests', $payload);

        if (! $response->successful()) {
            return $this->storeFallback($order, $response->body());
        }

        $json = $response->json();
        $actions = collect($json['actions'] ?? []);
        $checkoutUrl = $actions->firstWhere('method', 'GET')['url']
            ?? $json['checkout_url']
            ?? data_get($json, 'payment_link.checkout_url');

        $transaction = PaymentTransaction::updateOrCreate(
            ['laundry_order_id' => $order->id],
            [
                'provider' => 'xendit',
                'gateway_name' => 'Xendit Payment Request',
                'payment_method' => $order->payment_method,
                'reference' => $reference,
                'external_id' => $json['id'] ?? $reference,
                'checkout_url' => $checkoutUrl,
                'checkout_token' => $json['id'] ?? null,
                'gross_amount' => $order->total_price,
                'gateway_fee' => 0,
                'net_amount' => $order->total_price,
                'status' => 'pending',
                'webhook_status' => 'awaiting_payment',
                'paid_at' => null,
                'expired_at' => null,
                'gateway_payload' => $json,
            ]
        );

        $order->update([
            'payment_reference' => $reference,
            'payment_status' => 'pending',
        ]);

        return $transaction;
    }

    public function verifyWebhookToken(?string $token): bool
    {
        $expected = (string) ($this->settings->get('xendit_webhook_token', config('services.xendit.webhook_token'))
            ?: $this->settings->get('xendit_callback_token', config('services.xendit.callback_token')));

        return $expected !== '' && $token !== null && hash_equals($expected, $token);
    }

    public function handleNotification(array $payload): ?PaymentTransaction
    {
        $reference = $payload['reference_id'] ?? $payload['data']['reference_id'] ?? null;
        $status = strtolower((string) ($payload['status'] ?? $payload['data']['status'] ?? 'pending'));

        if (! $reference) {
            return null;
        }

        $transaction = PaymentTransaction::where('reference', $reference)->first();

        if (! $transaction) {
            return null;
        }

        $mappedStatus = in_array($status, ['succeeded', 'paid', 'completed', 'settled'], true)
            ? 'settled'
            : (in_array($status, ['failed', 'expired', 'cancelled'], true) ? 'failed' : 'pending');

        $transaction->update([
            'status' => $mappedStatus,
            'webhook_status' => $status,
            'paid_at' => $mappedStatus === 'settled' ? now() : $transaction->paid_at,
            'gateway_payload' => $payload,
        ]);

        $order = $transaction->order;
        $order->update([
            'payment_status' => $mappedStatus === 'settled' ? 'paid' : ($status === 'expired' ? 'expired' : 'pending'),
            'payment_paid_at' => $mappedStatus === 'settled' ? now() : $order->payment_paid_at,
        ]);

        return $transaction->fresh();
    }

    private function storeFallback(LaundryOrder $order, string $reason): PaymentTransaction
    {
        return PaymentTransaction::updateOrCreate(
            ['laundry_order_id' => $order->id],
            [
                'provider' => 'xendit',
                'gateway_name' => 'Xendit Payment Request',
                'payment_method' => $order->payment_method,
                'reference' => $order->payment_reference ?: 'XND-FALLBACK-'.Str::upper(Str::random(8)),
                'external_id' => null,
                'checkout_url' => null,
                'checkout_token' => null,
                'gross_amount' => $order->total_price,
                'gateway_fee' => 0,
                'net_amount' => $order->total_price,
                'status' => 'pending',
                'webhook_status' => 'integration_error',
                'gateway_payload' => ['error' => $reason],
            ]
        );
    }
}

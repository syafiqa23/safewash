<?php

namespace App\Services;

use App\Models\LaundryOrder;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransPaymentService
{
    public function __construct(private readonly IntegrationSettingsService $settings)
    {
    }

    public function createCheckout(LaundryOrder $order): PaymentTransaction
    {
        $serverKey = (string) $this->settings->get('midtrans_server_key', config('services.midtrans.server_key'));
        $isProduction = filter_var($this->settings->get('midtrans_is_production', config('services.midtrans.is_production', false)), FILTER_VALIDATE_BOOL);
        $baseUrl = $isProduction
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        if ($serverKey === '') {
            Log::warning('Midtrans createCheckout: server key is empty', ['order_id' => $order->id]);
            return $this->storeFallback($order, 'Midtrans key belum diisi');
        }

        $reference = $order->payment_reference ?: 'MID-'.Str::upper(Str::random(10));
        $grossAmount = (int) round((float) $order->total_price);

        $payload = [
            'transaction_details' => [
                'order_id' => $reference,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
            ],
            'item_details' => [
                [
                    'id' => $order->tracking_code,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => 'SafeWash '.$order->service_type,
                ],
            ],
            'callbacks' => [
                'finish' => route('orders.show', $order),
            ],
        ];

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->timeout(10)
                ->post($baseUrl.'/snap/v1/transactions', $payload);
        } catch (\Throwable $e) {
            Log::warning('Midtrans createCheckout: connection error', [
                'order_id' => $order->id, 'error' => $e->getMessage(),
            ]);
            return $this->storeFallback($order, 'connection_error: '.$e->getMessage());
        }

        if (! $response->successful()) {
            Log::warning('Midtrans createCheckout failed', [
                'order_id'      => $order->id,
                'status'        => $response->status(),
                'body'          => $response->body(),
                'is_production' => $isProduction,
            ]);
            return $this->storeFallback($order, $response->body());
        }

        $json = $response->json();

        $transaction = PaymentTransaction::updateOrCreate(
            ['laundry_order_id' => $order->id],
            [
                'provider' => 'midtrans',
                'gateway_name' => 'Midtrans Snap',
                'payment_method' => $order->payment_method,
                'reference' => $reference,
                'external_id' => $reference,
                'checkout_url' => $json['redirect_url'] ?? null,
                'checkout_token' => $json['token'] ?? null,
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

    public function verifySignature(array $payload): bool
    {
        $serverKey = (string) $this->settings->get('midtrans_server_key', config('services.midtrans.server_key'));
        $signatureKey = $payload['signature_key'] ?? null;

        if ($serverKey === '' || ! $signatureKey) {
            return false;
        }

        $expected = hash('sha512',
            ($payload['order_id'] ?? '').
            ($payload['status_code'] ?? '').
            ($payload['gross_amount'] ?? '').
            $serverKey
        );

        return hash_equals($expected, $signatureKey);
    }

    public function handleNotification(array $payload): ?PaymentTransaction
    {
        $reference = $payload['order_id'] ?? null;

        if (! $reference) {
            return null;
        }

        $transaction = PaymentTransaction::where('reference', $reference)->first();

        if (! $transaction) {
            return null;
        }

        $status = (string) ($payload['transaction_status'] ?? 'pending');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');
        $paidStatuses = ['capture', 'settlement'];
        $failedStatuses = ['cancel', 'deny', 'expire'];

        $transaction->update([
            'status' => in_array($status, $paidStatuses, true) && $fraudStatus !== 'deny' ? 'settled' : (in_array($status, $failedStatuses, true) ? 'failed' : 'pending'),
            'webhook_status' => $status,
            'paid_at' => in_array($status, $paidStatuses, true) ? now() : $transaction->paid_at,
            'gateway_payload' => $payload,
        ]);

        $order = $transaction->order;
        $order->update([
            'payment_status' => $transaction->status === 'settled' ? 'paid' : ($status === 'expire' ? 'expired' : 'pending'),
            'payment_paid_at' => $transaction->status === 'settled' ? now() : $order->payment_paid_at,
        ]);

        return $transaction->fresh();
    }

    private function storeFallback(LaundryOrder $order, string $reason): PaymentTransaction
    {
        return PaymentTransaction::updateOrCreate(
            ['laundry_order_id' => $order->id],
            [
                'provider' => 'midtrans',
                'gateway_name' => 'Midtrans Snap',
                'payment_method' => $order->payment_method,
                'reference' => $order->payment_reference ?: 'MID-FALLBACK-'.Str::upper(Str::random(8)),
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

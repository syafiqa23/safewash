<?php

namespace App\Services;

use App\Models\LaundryOrder;
use App\Models\PaymentTransaction;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentGatewayService
{
    public function __construct(
        private readonly MidtransPaymentService $midtrans,
        private readonly XenditPaymentService $xendit,
        private readonly IntegrationSettingsService $settings,
    ) {
    }

    public function createOrRefreshTransaction(LaundryOrder $order): PaymentTransaction
    {
        if ($order->payment_status === 'paid') {
            return $this->storeSimulatorTransaction($order, $this->provider());
        }

        if ($order->payment_method === 'cash') {
            return $this->storeSimulatorTransaction($order, 'cash');
        }

        return match ($this->provider()) {
            'midtrans' => $this->midtrans->createCheckout($order),
            'xendit' => $this->xendit->createCheckout($order),
            default => $this->storeSimulatorTransaction($order, 'simulator'),
        };
    }

    public function settle(LaundryOrder $order): PaymentTransaction
    {
        $order->update([
            'payment_status' => 'paid',
            'payment_paid_at' => $order->payment_paid_at ?? now(),
        ]);

        $transaction = $this->createOrRefreshTransaction($order);

        $transaction->update([
            'status' => 'settled',
            'webhook_status' => 'manual_settlement',
            'paid_at' => $order->payment_paid_at,
        ]);

        return $transaction->fresh();
    }

    public function handleMidtransNotification(array $payload): ?PaymentTransaction
    {
        return $this->midtrans->handleNotification($payload);
    }

    public function handleXenditNotification(array $payload): ?PaymentTransaction
    {
        return $this->xendit->handleNotification($payload);
    }

    public function verifyMidtransSignature(array $payload): bool
    {
        return $this->midtrans->verifySignature($payload);
    }

    public function verifyXenditWebhook(?string $token): bool
    {
        return $this->xendit->verifyWebhookToken($token);
    }

    private function provider(): string
    {
        return $this->settings->paymentProvider();
    }

    private function storeSimulatorTransaction(LaundryOrder $order, string $provider): PaymentTransaction
    {
        $gatewayFeeRate = (float) config('safewash.payment.gateway_fee_rate', 1.5);
        $gatewayFee = round((float) $order->total_price * ($gatewayFeeRate / 100), 2);
        $paidAt = $order->payment_status === 'paid'
            ? ($order->payment_paid_at ?? now())
            : null;

        $transaction = PaymentTransaction::updateOrCreate(
            ['laundry_order_id' => $order->id],
            [
                'provider' => $provider,
                'gateway_name' => (string) config('safewash.payment.gateway_name'),
                'payment_method' => $order->payment_method,
                'reference' => $order->payment_reference ?: 'PAY-'.Str::upper(Str::random(10)),
                'external_id' => 'SAFEWASH-'.$order->tracking_code,
                'checkout_url' => null,
                'checkout_token' => null,
                'gross_amount' => $order->total_price,
                'gateway_fee' => $gatewayFee,
                'net_amount' => (float) $order->total_price - $gatewayFee,
                'status' => $order->payment_status === 'paid' ? 'settled' : 'pending',
                'webhook_status' => $order->payment_status === 'paid' ? 'settlement' : 'awaiting_payment',
                'paid_at' => $paidAt,
                'expired_at' => null,
                'gateway_payload' => [
                    'payment_method' => $order->payment_method,
                    'simulated' => true,
                ],
            ]
        );

        $order->forceFill([
            'payment_reference' => $transaction->reference,
            'payment_paid_at' => $paidAt,
        ])->save();

        return $transaction;
    }
}

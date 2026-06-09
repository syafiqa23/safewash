<?php

namespace App\Http\Controllers;

use App\Mail\OrderCreatedMail;
use App\Mail\PaymentSuccessMail;
use App\Models\Laundry;
use App\Models\LaundryOrder;
use App\Models\TrackingUpdate;
use App\Services\IntegrationSettingsService;
use App\Services\LoyaltyProgramService;
use App\Services\MerchantScoringService;
use App\Services\PaymentGatewayService;
use App\Services\WhatsAppGatewayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    // Standard per-kg rates (Rp) used when customer self-orders
    const SERVICE_RATES = [
        'Cuci Kering'   => 7000,
        'Cuci Lipat'    => 8000,
        'Cuci Setrika'  => 10000,
        'Express 24 Jam'=> 15000,
        'Premium'       => 20000,
    ];

    const PICKUP_FEE     = 8000;
    const DELIVERY_FEE   = 8000;
    const PROTECTION_FEE = 5000;

    public function create(Laundry $laundry): View
    {
        abort_unless($laundry->is_active, 404);
        $user = Auth::user();
        // Only customer role can self-order; merchant/admin use their own form
        abort_if($user->isMerchant(), 403);

        $laundry->load('owner');
        $serviceRates    = self::SERVICE_RATES;
        $pickupFee       = self::PICKUP_FEE;
        $deliveryFee     = self::DELIVERY_FEE;
        $protectionFee   = self::PROTECTION_FEE;

        return view('customer.order-form', compact(
            'laundry', 'serviceRates', 'pickupFee', 'deliveryFee', 'protectionFee'
        ));
    }

    public function store(
        Request $request,
        Laundry $laundry,
        PaymentGatewayService $paymentGateway,
        WhatsAppGatewayService $whatsApp,
        MerchantScoringService $merchantScoring,
    ): RedirectResponse {
        abort_unless($laundry->is_active, 404);
        $user = Auth::user();
        abort_if($user->isMerchant(), 403);

        $data = $request->validate([
            'customer_name'         => ['required', 'string', 'max:255'],
            'customer_phone'        => ['required', 'string', 'max:30'],
            'service_type'          => ['required', 'string', 'in:'.implode(',', array_keys(self::SERVICE_RATES))],
            'weight_kg'             => ['required', 'numeric', 'min:0.5', 'max:100'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
            'total_price'           => ['required', 'numeric', 'min:1000'],
            'payment_method'        => ['required', 'in:qris,ewallet,virtual_account'],
            'pickup_delivery_type'  => ['required', 'in:none,pickup,delivery,round_trip'],
            'pickup_address'        => ['nullable', 'string', 'max:500'],
            'delivery_address'      => ['nullable', 'string', 'max:500'],
            'pickup_delivery_fee'   => ['required', 'numeric', 'min:0'],
            'is_premium_protected'  => ['nullable', 'boolean'],
        ]);

        $hasDelivery = $data['pickup_delivery_type'] !== 'none';

        $order = LaundryOrder::create([
            'laundry_id'             => $laundry->id,
            'customer_id'            => $user->id,
            'customer_name'          => $data['customer_name'],
            'customer_email'         => $user->email,
            'customer_phone'         => $data['customer_phone'],
            'service_type'           => $data['service_type'],
            'weight_kg'              => $data['weight_kg'],
            'total_price'            => $data['total_price'],
            'status'                 => 'received',
            'payment_status'         => 'pending',
            'payment_method'         => $data['payment_method'],
            'tracking_code'          => 'SW-'.Str::upper(Str::random(8)),
            'qr_token'               => (string) Str::uuid(),
            'is_premium_protected'   => $request->boolean('is_premium_protected'),
            'pickup_delivery_opt_in' => $hasDelivery,
            'pickup_delivery_fee'    => $data['pickup_delivery_fee'],
            'pickup_address'         => $data['pickup_address'] ?? null,
            'delivery_address'       => $data['delivery_address'] ?? null,
            'notes'                  => $data['notes'] ?? null,
        ]);

        if ($hasDelivery) {
            $order->deliveryRequest()->create([
                'service_type'          => $data['pickup_delivery_type'],
                'partner_name'          => (string) config('safewash.delivery.default_partner'),
                'status'                => 'scheduled',
                'fee'                   => $data['pickup_delivery_fee'],
                'pickup_address'        => $data['pickup_address'] ?? null,
                'delivery_address'      => $data['delivery_address'] ?? null,
                'courier_notes'         => 'Dibuat oleh customer melalui marketplace SafeWash.',
            ]);
        }

        TrackingUpdate::create([
            'laundry_order_id'     => $order->id,
            'status'               => 'received',
            'title'                => 'Order baru diterima',
            'description'          => 'Order masuk ke sistem SafeWash dan menunggu konfirmasi pembayaran.',
            'is_visible_to_customer' => true,
            'sent_to_customer'     => true,
            'created_by'           => $user->id,
        ]);

        // Create payment transaction (Midtrans / Xendit / Simulator)
        try {
            $paymentGateway->createOrRefreshTransaction($order);
        } catch (\Throwable $e) {
            Log::warning('createOrRefreshTransaction failed in store()', [
                'order_id' => $order->id, 'error' => $e->getMessage(),
            ]);
            // Order is already saved — checkout page will show "no token" fallback with simulator
        }

        // WhatsApp notification
        $whatsApp->sendOrderUpdate(
            $order,
            'Order '.$order->tracking_code.' berhasil dibuat. Selesaikan pembayaran untuk memulai proses laundry.'
        );

        // Email notification
        if ($user->email) {
            try {
                Mail::to($user->email)->send(new OrderCreatedMail($order));
            } catch (\Throwable) {
                // Mail failure must not break the order creation flow
            }
        }

        $merchantScoring->recalculate($laundry);

        return redirect()->route('customer.order.checkout', $order)
            ->with('success', 'Order berhasil dibuat! Lanjutkan pembayaran di bawah.');
    }

    public function checkout(LaundryOrder $order, IntegrationSettingsService $integrationSettings): View|RedirectResponse
    {
        abort_unless($order->customer_id === Auth::id(), 403);

        if ($order->payment_status !== 'pending') {
            return redirect()->route('orders.show', $order)
                ->with('info', 'Pembayaran order ini sudah selesai.');
        }

        Log::info('CustomerOrderController::checkout', ['order_id' => $order->id, 'customer_id' => Auth::id()]);

        $order->load(['laundry', 'paymentTransaction', 'deliveryRequest']);

        // Use IntegrationSettingsService (DB-first) so it matches what the gateway actually uses
        $provider        = $integrationSettings->paymentProvider();
        $isSimulator     = $provider === 'simulator';
        $snapToken       = $order->paymentTransaction?->checkout_token;
        $tokenFailed     = !$isSimulator && !$snapToken
                           && $order->paymentTransaction !== null
                           && in_array($order->paymentTransaction->webhook_status, ['integration_error', 'awaiting_payment'], true);
        $isProduction    = filter_var($integrationSettings->get('midtrans_is_production', config('services.midtrans.is_production', false)), FILTER_VALIDATE_BOOL);
        $snapScriptUrl   = $isProduction
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
        $clientKey       = (string) $integrationSettings->get('midtrans_client_key', config('services.midtrans.client_key', ''));

        return view('customer.checkout', compact(
            'order', 'snapToken', 'isSimulator', 'tokenFailed', 'snapScriptUrl', 'clientKey', 'provider'
        ));
    }

    public function generatePaymentLink(
        LaundryOrder $order,
        PaymentGatewayService $paymentGateway,
    ): RedirectResponse {
        abort_unless($order->customer_id === Auth::id(), 403);

        if ($order->payment_status !== 'pending') {
            return redirect()->route('orders.show', $order)
                ->with('info', 'Order ini sudah dibayar.');
        }

        try {
            $paymentGateway->createOrRefreshTransaction($order);
        } catch (\Throwable $e) {
            Log::error('CustomerOrderController::generatePaymentLink failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('customer.order.checkout', $order)
                ->with('error', 'Gagal menghubungi Midtrans. Gunakan Simulasi Bayar untuk melanjutkan.');
        }

        $fresh = $order->fresh(['paymentTransaction']);
        if ($fresh->paymentTransaction?->checkout_token) {
            return redirect()->route('customer.order.checkout', $order)
                ->with('success', 'Link pembayaran Midtrans berhasil dibuat!');
        }

        return redirect()->route('customer.order.checkout', $order)
            ->with('warning', 'Midtrans belum merespons. Gunakan Simulasi Bayar untuk melanjutkan demo.');
    }

    public function simulatePayment(
        LaundryOrder $order,
        PaymentGatewayService $paymentGateway,
        LoyaltyProgramService $loyaltyProgram,
        MerchantScoringService $merchantScoring,
        WhatsAppGatewayService $whatsApp,
    ): RedirectResponse {
        abort_unless($order->customer_id === Auth::id(), 403);

        if ($order->payment_status !== 'pending') {
            return redirect()->route('orders.show', $order)
                ->with('info', 'Order ini sudah dibayar sebelumnya.');
        }

        Log::info('CustomerOrderController::simulatePayment', ['order_id' => $order->id, 'customer_id' => Auth::id()]);

        $paymentGateway->settle($order);

        $fresh = $order->fresh();

        if ($fresh->payment_status === 'paid') {
            $email = $fresh->customer_email ?? Auth::user()->email;
            if ($email) {
                try { Mail::to($email)->send(new PaymentSuccessMail($fresh)); } catch (\Throwable) {}
            }
        }

        if ($fresh->status === 'completed' && $fresh->payment_status === 'paid') {
            $loyaltyProgram->awardCompletedOrder($fresh);
        }

        $merchantScoring->recalculate($fresh->laundry);
        $whatsApp->sendOrderUpdate($fresh, 'Pembayaran order '.$fresh->tracking_code.' berhasil dikonfirmasi (simulator).');

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pembayaran berhasil dikonfirmasi! Order sedang diproses.');
    }
}

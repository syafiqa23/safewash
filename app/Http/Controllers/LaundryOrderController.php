<?php

namespace App\Http\Controllers;

use App\Models\Laundry;
use App\Models\LaundryItem;
use App\Models\LaundryOrder;
use App\Models\NotificationLog;
use App\Models\TrackingUpdate;
use App\Models\User;
use App\Services\LoyaltyProgramService;
use App\Services\MerchantScoringService;
use App\Services\PaymentGatewayService;
use App\Services\WhatsAppGatewayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LaundryOrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $orders = LaundryOrder::with(['laundry', 'items', 'claims'])
            ->when($user->isMerchant(), fn ($query) => $query->whereIn('laundry_id', $user->laundries()->pluck('id')))
            ->when($user->isCustomer(), function ($query) use ($user): void {
                $query->where(function ($inner) use ($user): void {
                    $inner->where('customer_id', $user->id)
                        ->orWhere('customer_email', $user->email);
                });
            })
            ->when($request->boolean('active'), fn ($q) => $q->whereNotIn('status', ['completed', 'claimed']))
            ->when($request->boolean('delivery'), fn ($q) => $q->where('pickup_delivery_opt_in', true))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest()
            ->paginate(10);

        $activeFilter = $request->boolean('active');
        $deliveryFilter = $request->boolean('delivery');

        return view('orders.index', compact('orders', 'user', 'activeFilter', 'deliveryFilter'));
    }

    public function create(): View
    {
        $user = Auth::user();
        abort_unless($user->isMerchant() || $user->isAdmin(), 403);

        $laundries = $user->isAdmin()
            ? Laundry::where('is_active', true)->orderBy('name')->get()
            : $user->laundries()->where('is_active', true)->get();

        return view('orders.create', compact('laundries'));
    }

    public function show(LaundryOrder $order): View
    {
        $this->authorizeOrder($order);

        $order->load(['laundry', 'items', 'trackingUpdates.creator', 'notifications', 'claims', 'paymentTransaction', 'deliveryRequest', 'customer.loyaltyAccount']);

        return view('orders.show', compact('order'));
    }

    public function store(Request $request, PaymentGatewayService $paymentGateway, WhatsAppGatewayService $whatsAppGateway, MerchantScoringService $merchantScoring): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isMerchant(), 403);

        $data = $request->validate([
            'laundry_id' => ['required', 'exists:laundries,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'service_type' => ['required', 'string', 'max:255'],
            'weight_kg' => ['required', 'numeric', 'min:0.1'],
            'total_price' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:pending,paid'],
            'payment_method' => ['required', 'in:cash,qris,ewallet,virtual_account'],
            'promised_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['nullable', 'string', 'max:5000'],
            'is_premium_protected' => ['nullable', 'boolean'],
            'pickup_delivery_opt_in' => ['nullable', 'boolean'],
            'pickup_delivery_type' => ['nullable', 'in:none,pickup,delivery,round_trip'],
            'pickup_delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'pickup_address' => ['nullable', 'string'],
            'delivery_address' => ['nullable', 'string'],
            'pickup_scheduled_at' => ['nullable', 'date'],
            'delivery_scheduled_at' => ['nullable', 'date'],
        ]);

        abort_unless($user->laundries()->whereKey($data['laundry_id'])->exists(), 403);
        abort_unless(Laundry::whereKey($data['laundry_id'])->where('is_active', true)->exists(), 422, 'Merchant laundry ini sedang nonaktif.');

        $customer = null;
        if (! empty($data['customer_email'])) {
            $customer = User::where('email', $data['customer_email'])->first();
        }

        $order = LaundryOrder::create([
            'laundry_id' => $data['laundry_id'],
            'customer_id' => $customer?->id,
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'] ?? null,
            'customer_phone' => $data['customer_phone'],
            'service_type' => $data['service_type'],
            'weight_kg' => $data['weight_kg'],
            'total_price' => $data['total_price'],
            'status' => 'received',
            'payment_status' => $data['payment_status'],
            'payment_method' => $data['payment_method'],
            'tracking_code' => 'SW-'.Str::upper(Str::random(8)),
            'qr_token' => (string) Str::uuid(),
            'payment_paid_at' => $data['payment_status'] === 'paid' ? now() : null,
            'promised_at' => $data['promised_at'] ?? null,
            'is_premium_protected' => $request->boolean('is_premium_protected'),
            'pickup_delivery_opt_in' => $request->boolean('pickup_delivery_opt_in'),
            'pickup_delivery_fee' => $data['pickup_delivery_fee'] ?? 0,
            'pickup_address' => $data['pickup_address'] ?? null,
            'delivery_address' => $data['delivery_address'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        foreach (preg_split('/\r\n|\r|\n/', trim((string) ($data['items'] ?? ''))) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            [$name, $qty] = array_pad(explode('|', $line), 2, 1);

            LaundryItem::create([
                'laundry_order_id' => $order->id,
                'item_name' => trim($name),
                'quantity' => (int) trim((string) $qty) ?: 1,
                'condition_notes' => 'Dicatat saat penerimaan order.',
                'is_priority' => false,
            ]);
        }

        if ($order->pickup_delivery_opt_in) {
            $order->deliveryRequest()->create([
                'service_type' => $data['pickup_delivery_type'] ?? 'round_trip',
                'partner_name' => (string) config('safewash.delivery.default_partner'),
                'status' => 'scheduled',
                'fee' => $data['pickup_delivery_fee'] ?? 0,
                'pickup_scheduled_at' => $data['pickup_scheduled_at'] ?? null,
                'delivery_scheduled_at' => $data['delivery_scheduled_at'] ?? null,
                'pickup_address' => $data['pickup_address'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? null,
                'courier_notes' => 'Dibuat otomatis saat order masuk ke SafeWash.',
            ]);
        }

        TrackingUpdate::create([
            'laundry_order_id' => $order->id,
            'status' => 'received',
            'title' => 'Order baru diterima',
            'description' => 'Order sudah masuk ke sistem SafeWash dan siap diproses.',
            'is_visible_to_customer' => true,
            'sent_to_customer' => true,
            'created_by' => $user->id,
        ]);

        $paymentGateway->createOrRefreshTransaction($order);
        $whatsAppGateway->sendOrderUpdate(
            $order,
            'Order '.$order->tracking_code.' berhasil dibuat. Status awal: received. Tracking QR dan notifikasi real-time sudah aktif.'
        );
        $merchantScoring->recalculate($order->laundry);

        return redirect()->route('orders.show', $order)->with('success', 'Order laundry berhasil dibuat.');
    }

    public function createPaymentLink(LaundryOrder $order, PaymentGatewayService $paymentGateway, WhatsAppGatewayService $whatsAppGateway): RedirectResponse
    {
        $this->authorizeOwnerOrder($order);

        $transaction = $paymentGateway->createOrRefreshTransaction($order);

        if ($transaction->checkout_url) {
            $whatsAppGateway->sendOrderUpdate(
                $order->fresh(),
                'Link pembayaran untuk order '.$order->tracking_code.' berhasil dibuat. Silakan lanjutkan pembayaran melalui tautan resmi.'
            );
        }

        return back()->with('success', 'Link pembayaran berhasil diperbarui.');
    }

    public function updateStatus(Request $request, LaundryOrder $order, WhatsAppGatewayService $whatsAppGateway, LoyaltyProgramService $loyaltyProgram, MerchantScoringService $merchantScoring): RedirectResponse
    {
        $this->authorizeOwnerOrder($order);

        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', LaundryOrder::STATUSES)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        $order->update([
            'status' => $data['status'],
            'picked_up_at' => $data['status'] === 'completed' ? now() : $order->picked_up_at,
            'delivered_at' => $data['status'] === 'completed' && $order->pickup_delivery_opt_in ? now() : $order->delivered_at,
        ]);

        TrackingUpdate::create([
            'laundry_order_id' => $order->id,
            'status' => $data['status'],
            'title' => $data['title'],
            'description' => $data['description'],
            'is_visible_to_customer' => true,
            'sent_to_customer' => true,
            'created_by' => Auth::id(),
        ]);

        if ($order->deliveryRequest) {
            $order->deliveryRequest->update([
                'status' => $data['status'] === 'completed' ? 'delivered' : $order->deliveryRequest->status,
                'delivered_at' => $data['status'] === 'completed' ? now() : $order->deliveryRequest->delivered_at,
            ]);
        }

        $whatsAppGateway->sendOrderUpdate(
            $order,
            'Status order '.$order->tracking_code.' diperbarui menjadi '.$data['status'].'. '.$data['title']
        );

        if ($order->status === 'completed') {
            $loyaltyProgram->awardCompletedOrder($order->fresh());
        }

        $merchantScoring->recalculate($order->laundry);

        return back()->with('success', 'Status order berhasil diperbarui.');
    }

    public function settlePayment(LaundryOrder $order, PaymentGatewayService $paymentGateway, LoyaltyProgramService $loyaltyProgram, MerchantScoringService $merchantScoring, WhatsAppGatewayService $whatsAppGateway): RedirectResponse
    {
        $this->authorizeOwnerOrder($order);

        $paymentGateway->settle($order);

        if ($order->fresh()->status === 'completed') {
            $loyaltyProgram->awardCompletedOrder($order->fresh());
        }

        $merchantScoring->recalculate($order->laundry);
        $whatsAppGateway->sendOrderUpdate(
            $order->fresh(),
            'Pembayaran digital untuk order '.$order->tracking_code.' telah dikonfirmasi dengan metode '.$order->payment_method.'.'
        );

        return back()->with('success', 'Pembayaran order berhasil dikonfirmasi.');
    }

    public function updateDelivery(Request $request, LaundryOrder $order, MerchantScoringService $merchantScoring): RedirectResponse
    {
        $this->authorizeOwnerOrder($order);
        abort_unless($order->deliveryRequest, 404);

        $data = $request->validate([
            'status' => ['required', 'in:scheduled,picked_up,in_transit,out_for_delivery,delivered,cancelled'],
            'partner_name' => ['required', 'string', 'max:255'],
            'courier_notes' => ['nullable', 'string'],
        ]);

        $order->deliveryRequest->update([
            'status' => $data['status'],
            'partner_name' => $data['partner_name'],
            'courier_notes' => $data['courier_notes'] ?? null,
            'picked_up_at' => $data['status'] === 'picked_up' ? now() : $order->deliveryRequest->picked_up_at,
            'delivered_at' => $data['status'] === 'delivered' ? now() : $order->deliveryRequest->delivered_at,
        ]);

        $merchantScoring->recalculate($order->laundry);

        return back()->with('success', 'Status pickup-delivery berhasil diperbarui.');
    }

    private function authorizeOrder(LaundryOrder $order): void
    {
        $user = Auth::user();

        if (($user->isMerchant() || $user->isAdmin()) && ($user->isAdmin() || $user->laundries()->whereKey($order->laundry_id)->exists())) {
            return;
        }

        if ($user->isCustomer() && ($order->customer_id === $user->id || $order->customer_email === $user->email)) {
            return;
        }

        abort(403);
    }

    private function authorizeOwnerOrder(LaundryOrder $order): void
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || ($user->isMerchant() && $user->laundries()->whereKey($order->laundry_id)->exists()), 403);
    }
}

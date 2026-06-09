<?php

use App\Models\Claim;
use App\Models\DeliveryRequest;
use App\Models\LaundryOrder;
use App\Models\NotificationLog;
use App\Models\PaymentTransaction;
use App\Models\TrackingUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Public — no auth required (mirrors /track/{code} web route)
Route::get('/track/{code}', function (string $code) {
    $order = LaundryOrder::with(['laundry', 'items', 'trackingUpdates', 'claims', 'paymentTransaction', 'deliveryRequest'])
        ->where('tracking_code', $code)
        ->orWhere('qr_token', $code)
        ->firstOrFail();

    return response()->json($order);
});

// Protected — session auth required (StartSession appended to api group via bootstrap/app.php)
Route::middleware('auth')->group(function (): void {

    Route::get('/orders', function () {
        return LaundryOrder::with(['laundry', 'items', 'trackingUpdates', 'claims', 'paymentTransaction', 'deliveryRequest'])->latest()->get();
    });

    Route::post('/orders', function (Request $request) {
        $data = $request->validate([
            'laundry_id' => ['required', 'exists:laundries,id'],
            'customer_name' => ['required', 'string'],
            'customer_email' => ['nullable', 'email'],
            'customer_phone' => ['required', 'string'],
            'service_type' => ['required', 'string'],
            'weight_kg' => ['required', 'numeric'],
            'total_price' => ['required', 'numeric'],
            'payment_method' => ['nullable', 'in:cash,qris,ewallet,virtual_account'],
        ]);

        $order = LaundryOrder::create($data + [
            'status' => 'received',
            'payment_status' => 'pending',
            'payment_method' => $data['payment_method'] ?? 'cash',
            'tracking_code' => 'SW-'.Str::upper(Str::random(8)),
            'qr_token' => (string) Str::uuid(),
        ]);

        PaymentTransaction::create([
            'laundry_order_id' => $order->id,
            'gateway_name' => (string) config('safewash.payment.gateway_name'),
            'payment_method' => $order->payment_method,
            'reference' => 'API-PAY-'.Str::upper(Str::random(8)),
            'gross_amount' => $order->total_price,
            'gateway_fee' => 0,
            'net_amount' => $order->total_price,
            'status' => 'pending',
            'gateway_payload' => ['source' => 'api'],
        ]);

        TrackingUpdate::create([
            'laundry_order_id' => $order->id,
            'status' => 'received',
            'title' => 'Order API diterima',
            'description' => 'Order dibuat melalui endpoint backend SafeWash.',
            'is_visible_to_customer' => true,
            'sent_to_customer' => false,
        ]);

        return response()->json($order->load('trackingUpdates'), 201);
    });

    Route::patch('/orders/{order}', function (Request $request, LaundryOrder $order) {
        $data = $request->validate([
            'status' => ['required', 'string'],
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
        ]);

        $order->update(['status' => $data['status']]);

        $update = TrackingUpdate::create([
            'laundry_order_id' => $order->id,
            'status' => $data['status'],
            'title' => $data['title'],
            'description' => $data['description'],
            'is_visible_to_customer' => true,
            'sent_to_customer' => true,
        ]);

        NotificationLog::create([
            'laundry_order_id' => $order->id,
            'channel' => 'webhook',
            'recipient' => $order->customer_email ?: $order->customer_phone,
            'message' => 'Backend update '.$order->tracking_code.' ke status '.$data['status'],
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return response()->json([
            'order' => $order->fresh(),
            'tracking_update' => $update,
        ]);
    });

    Route::post('/orders/{order}/delivery', function (Request $request, LaundryOrder $order) {
        $data = $request->validate([
            'service_type' => ['required', 'in:none,pickup,delivery,round_trip'],
            'partner_name' => ['required', 'string'],
            'status' => ['required', 'string'],
            'fee' => ['nullable', 'numeric'],
            'pickup_address' => ['nullable', 'string'],
            'delivery_address' => ['nullable', 'string'],
        ]);

        $delivery = DeliveryRequest::updateOrCreate(
            ['laundry_order_id' => $order->id],
            $data
        );

        return response()->json($delivery);
    });

    Route::post('/claims', function (Request $request) {
        $data = $request->validate([
            'laundry_order_id' => ['required', 'exists:laundry_orders,id'],
            'claimant_name' => ['required', 'string'],
            'claimant_contact' => ['required', 'string'],
            'item_name' => ['required', 'string'],
            'description' => ['required', 'string'],
        ]);

        $claim = Claim::create($data + [
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json($claim, 201);
    });

});

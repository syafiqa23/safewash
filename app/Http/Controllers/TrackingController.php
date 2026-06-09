<?php

namespace App\Http\Controllers;

use App\Models\LaundryOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class TrackingController extends Controller
{
    public function show(string $code): View|JsonResponse
    {
        $order = LaundryOrder::with(['laundry', 'items', 'trackingUpdates', 'claims', 'notifications'])
            ->where('tracking_code', $code)
            ->orWhere('qr_token', $code)
            ->firstOrFail();

        if (request()->expectsJson()) {
            return response()->json([
                'order' => $order,
                'tracking_updates' => $order->trackingUpdates,
                'claims' => $order->claims,
            ]);
        }

        return view('tracking.show', compact('order'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\LaundryOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CustomerTrackingController extends Controller
{
    public function index(Request $request): View
    {
        $order = null;

        if ($request->filled('code')) {
            $code  = strtoupper(trim($request->input('code')));
            $order = LaundryOrder::with(['laundry', 'trackingUpdates', 'items', 'claims', 'deliveryRequest'])
                ->where('tracking_code', $code)
                ->first();
        }

        return view('customer.tracking', compact('order'));
    }
}

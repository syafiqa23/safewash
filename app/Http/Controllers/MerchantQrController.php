<?php

namespace App\Http\Controllers;

use App\Models\LaundryOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MerchantQrController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $laundryIds = $user->laundries()->pluck('id');

        $orders = LaundryOrder::with(['laundry', 'trackingUpdates'])
            ->whereIn('laundry_id', $laundryIds)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($inner) use ($request): void {
                $inner->where('tracking_code', 'like', '%'.$request->input('q').'%')
                      ->orWhere('customer_name', 'like', '%'.$request->input('q').'%');
            }))
            ->latest()
            ->paginate(20);

        return view('merchant.qr-tracking', compact('orders'));
    }
}

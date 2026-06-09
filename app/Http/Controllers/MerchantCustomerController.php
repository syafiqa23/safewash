<?php

namespace App\Http\Controllers;

use App\Models\LaundryOrder;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MerchantCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $laundryIds = $user->laundries()->pluck('id');

        // Aggregate unique customers with stats
        $customers = User::whereHas('customerOrders', fn ($q) => $q->whereIn('laundry_id', $laundryIds))
            ->withCount(['customerOrders as order_count' => fn ($q) => $q->whereIn('laundry_id', $laundryIds)])
            ->withSum(['customerOrders as total_spent' => fn ($q) => $q->whereIn('laundry_id', $laundryIds)->where('payment_status', 'paid')], 'total_price')
            ->when($request->filled('q'), fn ($q) => $q->where(function ($inner) use ($request): void {
                $inner->where('name', 'like', '%'.$request->input('q').'%')
                      ->orWhere('email', 'like', '%'.$request->input('q').'%')
                      ->orWhere('phone', 'like', '%'.$request->input('q').'%');
            }))
            ->orderByDesc('order_count')
            ->paginate(20);

        $totalCustomers = User::whereHas('customerOrders', fn ($q) => $q->whereIn('laundry_id', $laundryIds))->count();

        return view('merchant.customers', compact('customers', 'totalCustomers'));
    }
}

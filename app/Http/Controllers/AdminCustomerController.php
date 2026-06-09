<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AdminCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = User::where('role', 'customer')
            ->withCount('customerOrders as order_count')
            ->withSum(['customerOrders as total_spent' => fn ($q) => $q->where('payment_status', 'paid')], 'total_price')
            ->when($request->filled('q'), fn ($q) => $q->where(function ($inner) use ($request): void {
                $inner->where('name', 'like', '%'.$request->input('q').'%')
                      ->orWhere('email', 'like', '%'.$request->input('q').'%')
                      ->orWhere('phone', 'like', '%'.$request->input('q').'%')
                      ->orWhere('city', 'like', '%'.$request->input('q').'%');
            }))
            ->when($request->filled('city'), fn ($q) => $q->where('city', $request->input('city')))
            ->orderByDesc('order_count')
            ->paginate(20);

        $summary = [
            'total'        => User::where('role', 'customer')->count(),
            'with_orders'  => User::where('role', 'customer')->whereHas('customerOrders')->count(),
            'new_this_month' => User::where('role', 'customer')->whereMonth('created_at', now()->month)->count(),
        ];

        $cities = User::where('role', 'customer')->whereNotNull('city')->distinct()->orderBy('city')->pluck('city');

        return view('admin.customers', compact('customers', 'summary', 'cities'));
    }
}

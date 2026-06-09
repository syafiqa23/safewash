<?php

namespace App\Http\Controllers;

use App\Models\Laundry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Laundry::where('is_active', true)
            ->withCount('orders')
            ->when($request->filled('city'), fn ($q) => $q->where('city', $request->input('city')))
            ->when($request->filled('pickup'), fn ($q) => $q->where('pickup_available', true))
            ->when($request->filled('delivery'), fn ($q) => $q->where('delivery_available', true))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->input('q').'%'));

        $sort = $request->input('sort', 'rating');
        $query = match ($sort) {
            'orders'  => $query->orderByDesc('orders_count'),
            'name'    => $query->orderBy('name'),
            default   => $query->orderByDesc('rating')->orderByDesc('review_count'),
        };

        $laundries = $query->paginate(12)->withQueryString();

        $cities = Laundry::where('is_active', true)
            ->whereNotNull('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('marketplace.index', compact('laundries', 'cities'));
    }

    public function show(Laundry $laundry): View
    {
        abort_unless($laundry->is_active, 404);

        $laundry->load('owner');

        $recentOrders = $laundry->orders()
            ->where('payment_status', 'paid')
            ->whereNotNull('customer_id')
            ->latest()
            ->limit(5)
            ->get();

        $completedCount = $laundry->orders()->where('status', 'completed')->count();
        $totalOrders    = $laundry->orders()->count();

        return view('marketplace.show', compact('laundry', 'recentOrders', 'completedCount', 'totalOrders'));
    }
}

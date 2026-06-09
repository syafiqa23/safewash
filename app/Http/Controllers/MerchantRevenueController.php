<?php

namespace App\Http\Controllers;

use App\Models\LaundryOrder;
use App\Models\MerchantSettlement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class MerchantRevenueController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $laundryIds = $user->laundries()->pluck('id');

        $from = $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay() : now()->startOfMonth();
        $to   = $request->filled('to')   ? Carbon::parse($request->input('to'))->endOfDay()     : now()->endOfDay();

        $paidOrders = LaundryOrder::whereIn('laundry_id', $laundryIds)
            ->where('payment_status', 'paid')
            ->whereBetween('payment_paid_at', [$from, $to]);

        $grossRevenue     = (float) (clone $paidOrders)->sum('total_price');
        $commissionRate   = 0.15;
        $serviceFeeRate   = 0.03;
        $platformCut      = $grossRevenue * ($commissionRate + $serviceFeeRate);
        $netRevenue       = $grossRevenue - $platformCut;
        $orderCount       = (clone $paidOrders)->count();

        $byDay = (clone $paidOrders)
            ->selectRaw('DATE(payment_paid_at) as day, SUM(total_price) as total')
            ->groupByRaw('DATE(payment_paid_at)')
            ->orderBy('day')
            ->get();

        $settlements = MerchantSettlement::whereIn('laundry_id', $laundryIds)
            ->with('laundry')
            ->latest()
            ->paginate(10);

        $summary = [
            'gross'       => $grossRevenue,
            'commission'  => $grossRevenue * $commissionRate,
            'service_fee' => $grossRevenue * $serviceFeeRate,
            'net'         => $netRevenue,
            'orders'      => $orderCount,
        ];

        return view('merchant.revenue', compact('summary', 'byDay', 'settlements', 'from', 'to'));
    }
}

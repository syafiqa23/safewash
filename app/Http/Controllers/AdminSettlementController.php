<?php

namespace App\Http\Controllers;

use App\Models\Laundry;
use App\Models\LaundryOrder;
use App\Models\MerchantSettlement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminSettlementController extends Controller
{
    public function index(Request $request): View
    {
        $settlements = MerchantSettlement::with('laundry')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('laundry'), fn ($q) => $q->where('laundry_id', $request->input('laundry')))
            ->latest()
            ->paginate(20);

        $summary = [
            'total'      => MerchantSettlement::count(),
            'pending'    => MerchantSettlement::where('status', 'pending')->count(),
            'processing' => MerchantSettlement::where('status', 'processing')->count(),
            'paid'       => MerchantSettlement::where('status', 'paid')->count(),
            'total_net'  => MerchantSettlement::where('status', 'paid')->sum('net_amount'),
        ];

        $laundries = Laundry::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.settlement', compact('settlements', 'summary', 'laundries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'laundry_id'   => ['required', 'exists:laundries,id'],
            'period_start' => ['required', 'date'],
            'period_end'   => ['required', 'date', 'after_or_equal:period_start'],
            'notes'        => ['nullable', 'string'],
        ]);

        $laundry    = Laundry::findOrFail($data['laundry_id']);
        $from       = Carbon::parse($data['period_start'])->startOfDay();
        $to         = Carbon::parse($data['period_end'])->endOfDay();

        $orders = LaundryOrder::where('laundry_id', $laundry->id)
            ->where('payment_status', 'paid')
            ->whereBetween('payment_paid_at', [$from, $to])
            ->get();

        $gross      = $orders->sum('total_price');
        $commission = $gross * ($laundry->commission_rate / 100);
        $fee        = $gross * (($laundry->service_fee_rate ?? 3) / 100);
        $net        = $gross - $commission - $fee;

        MerchantSettlement::create([
            'laundry_id'        => $laundry->id,
            'period_start'      => $data['period_start'],
            'period_end'        => $data['period_end'],
            'order_count'       => $orders->count(),
            'gross_amount'      => $gross,
            'commission_amount' => $commission,
            'service_fee'       => $fee,
            'net_amount'        => $net,
            'status'            => 'pending',
            'notes'             => $data['notes'] ?? null,
            'created_by'        => Auth::id(),
        ]);

        return back()->with('success', "Settlement untuk {$laundry->name} berhasil dibuat.");
    }

    public function markPaid(Request $request, MerchantSettlement $settlement): RedirectResponse
    {
        $settlement->update([
            'status'     => 'paid',
            'settled_at' => now(),
        ]);

        return back()->with('success', "Settlement #{$settlement->id} ditandai sudah dibayar.");
    }
}

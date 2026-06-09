<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\Laundry;
use App\Models\LaundryOrder;
use App\Models\LoyaltyTransaction;
use App\Models\NotificationLog;
use App\Models\PaymentTransaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private const COMMISSION_RATE  = 15;
    private const SERVICE_FEE_RATE = 3;

    public function landing(): View
    {
        $paidRevenue = LaundryOrder::where('payment_status', 'paid')->sum('total_price');

        $metrics = [
            'orders'              => LaundryOrder::count(),
            'laundries'           => Laundry::where('is_active', true)->count(),
            'claims'              => Claim::count(),
            'resolvedClaims'      => Claim::whereIn('status', ['approved', 'compensated', 'closed'])->count(),
            'platformRevenue'     => $this->platformRevenue($paidRevenue),
            'digitalPayments'     => LaundryOrder::whereIn('payment_method', ['qris', 'ewallet', 'virtual_account'])->count(),
            'deliveryOrders'      => LaundryOrder::where('pickup_delivery_opt_in', true)->count(),
            'whiteLabelMerchants' => Laundry::where('supports_white_label', true)->count(),
        ];

        $featuredMerchants = Laundry::where('is_active', true)
            ->orderByDesc('rating')
            ->limit(6)
            ->get();

        $latestOrders = LaundryOrder::with(['laundry', 'paymentTransaction', 'deliveryRequest'])
            ->latest()
            ->take(3)
            ->get();

        return view('home', compact('metrics', 'latestOrders', 'featuredMerchants'));
    }

    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isMerchant()) {
            return $this->merchantDashboard($user);
        }

        return $this->customerDashboard($user);
    }

    private function merchantDashboard($user): View
    {
        $laundryIds = $user->laundries()->pluck('id');
        $today      = Carbon::today();

        // KPI Hari Ini
        $todayOrders   = LaundryOrder::whereIn('laundry_id', $laundryIds)->whereDate('created_at', $today)->count();
        $todayRevenue  = LaundryOrder::whereIn('laundry_id', $laundryIds)->where('payment_status', 'paid')->whereDate('payment_paid_at', $today)->sum('total_price');
        $todayPickup   = LaundryOrder::whereIn('laundry_id', $laundryIds)->where('pickup_delivery_opt_in', true)->whereDate('created_at', $today)->count();
        $activeOrders  = LaundryOrder::whereIn('laundry_id', $laundryIds)->whereNotIn('status', ['completed', 'claimed'])->count();

        // Order Aktif (terbaru, max 8)
        $activeOrderList = LaundryOrder::with(['laundry', 'deliveryRequest'])
            ->whereIn('laundry_id', $laundryIds)
            ->whereNotIn('status', ['completed', 'claimed'])
            ->latest()
            ->take(8)
            ->get();

        // Pickup Delivery Aktif
        $activeDeliveries = LaundryOrder::with(['laundry', 'deliveryRequest'])
            ->whereIn('laundry_id', $laundryIds)
            ->where('pickup_delivery_opt_in', true)
            ->whereHas('deliveryRequest', fn ($q) => $q->whereNotIn('status', ['delivered', 'cancelled']))
            ->latest()
            ->take(5)
            ->get();

        // Klaim Aktif
        $activeClaims = Claim::with('order')
            ->whereHas('order', fn ($q) => $q->whereIn('laundry_id', $laundryIds))
            ->whereIn('status', ['submitted', 'investigating'])
            ->latest()
            ->take(5)
            ->get();

        // Aktivitas Terakhir (semua order)
        $recentOrders = LaundryOrder::with(['laundry'])
            ->whereIn('laundry_id', $laundryIds)
            ->latest()
            ->take(10)
            ->get();

        // Pendapatan bulan ini
        $monthRevenue = LaundryOrder::whereIn('laundry_id', $laundryIds)
            ->where('payment_status', 'paid')
            ->whereMonth('payment_paid_at', now()->month)
            ->sum('total_price');

        $stats = [
            'today_orders'   => $todayOrders,
            'today_revenue'  => $todayRevenue,
            'today_pickup'   => $todayPickup,
            'active_orders'  => $activeOrders,
            'month_revenue'  => $monthRevenue,
            'active_claims'  => $activeClaims->count(),
            'merchant_score' => round((float) $user->laundries()->avg('merchant_score'), 2),
        ];

        $laundries = $user->laundries()->get();

        return view('dashboard.merchant', compact(
            'user', 'stats', 'activeOrderList', 'activeDeliveries',
            'activeClaims', 'recentOrders', 'laundries'
        ));
    }

    private function customerDashboard($user): View
    {
        // Laundry sedang diproses (order aktif paling recent)
        $activeOrders = LaundryOrder::with(['laundry', 'trackingUpdates'])
            ->where(function ($q) use ($user): void {
                $q->where('customer_id', $user->id)->orWhere('customer_email', $user->email);
            })
            ->whereNotIn('status', ['completed', 'claimed'])
            ->latest()
            ->take(5)
            ->get();

        // Pesanan terakhir
        $recentOrders = LaundryOrder::with(['laundry', 'paymentTransaction'])
            ->where(function ($q) use ($user): void {
                $q->where('customer_id', $user->id)->orWhere('customer_email', $user->email);
            })
            ->latest()
            ->take(8)
            ->get();

        // Klaim
        $claims = Claim::with('order.laundry')
            ->where('customer_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // Merchant favorit (dari laundry yang pernah diorder)
        $favoriteMerchants = Laundry::whereHas('orders', fn ($q) => $q->where(function ($inner) use ($user): void {
            $inner->where('customer_id', $user->id)->orWhere('customer_email', $user->email);
        }))->limit(4)->get();

        // Loyalty
        $loyaltyAccount = $user->loyaltyAccount()->first();
        $loyaltyHistory = LoyaltyTransaction::whereHas('account', fn ($q) => $q->where('customer_id', $user->id))
            ->latest()->take(5)->get();

        $stats = [
            'total_orders'   => $recentOrders->count(),
            'active_orders'  => $activeOrders->count(),
            'total_spent'    => $recentOrders->where('payment_status', 'paid')->sum('total_price'),
            'open_claims'    => $claims->whereIn('status', ['submitted', 'investigating'])->count(),
        ];

        return view('dashboard.customer', compact(
            'user', 'stats', 'activeOrders', 'recentOrders',
            'claims', 'favoriteMerchants', 'loyaltyAccount', 'loyaltyHistory'
        ));
    }

    private function platformRevenue(float|int $grossRevenue): float
    {
        return $grossRevenue * ((self::COMMISSION_RATE + self::SERVICE_FEE_RATE) / 100);
    }
}

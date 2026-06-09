<?php

namespace App\Http\Controllers;

use App\Exports\AdminReportExport;
use App\Models\Claim;
use App\Models\PaymentTransaction;
use App\Models\Laundry;
use App\Models\LaundryOrder;
use App\Models\LoyaltyTransaction;
use App\Models\NotificationLog;
use App\Services\MerchantScoringService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminController extends Controller
{
    private const COMMISSION_RATE = 15;
    private const SERVICE_FEE_RATE = 3;

    public function dashboard(Request $request): View
    {
        $filters = $this->validateDateFilters($request);
        [$startDate, $endDate] = $this->resolveDateRange($filters);

        $paidOrders = LaundryOrder::with(['laundry', 'paymentTransaction', 'deliveryRequest'])
            ->where('payment_status', 'paid')
            ->when($startDate, fn ($query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('created_at', '<=', $endDate))
            ->get();

        $grossRevenue = (float) $paidOrders->sum('total_price');
        $platformRevenue = $this->platformRevenue($grossRevenue);
        $merchantRevenue = $grossRevenue - $platformRevenue;

        $monthlySeries = $this->buildMonthlySeries($paidOrders, $startDate, $endDate);
        $topMerchants = $this->merchantRows([
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
        ])->take(5)->values();

        $recentClaims = Claim::with(['order.laundry', 'customer'])
            ->latest()
            ->take(6)
            ->get();

        $latestOrders = LaundryOrder::with(['laundry', 'paymentTransaction', 'deliveryRequest'])
            ->latest()
            ->take(8)
            ->get();

        $ordersInRange = LaundryOrder::query()
            ->when($startDate, fn ($query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('created_at', '<=', $endDate))
            ->get();

        $paymentMethodBreakdown = collect(config('safewash.payment.supported_methods'))
            ->mapWithKeys(fn (string $label, string $method) => [$label => $ordersInRange->where('payment_method', $method)->count()]);

        $stats = [
            'gross_revenue' => $grossRevenue,
            'admin_revenue' => $platformRevenue,
            'merchant_revenue' => $merchantRevenue,
            'commission_revenue' => $grossRevenue * (self::COMMISSION_RATE / 100),
            'service_fee_revenue' => $grossRevenue * (self::SERVICE_FEE_RATE / 100),
            'merchant_count' => Laundry::count(),
            'merchant_active_count' => Laundry::where('is_active', true)->count(),
            'merchant_inactive_count' => Laundry::where('is_active', false)->count(),
            'white_label_count' => Laundry::where('supports_white_label', true)->count(),
            'average_merchant_score' => round((float) Laundry::avg('merchant_score'), 2),
            'active_orders' => LaundryOrder::whereNotIn('status', ['completed'])->count(),
            'claims_open' => Claim::where('status', '!=', 'resolved')->count(),
            'digital_payment_orders' => $ordersInRange->whereIn('payment_method', ['qris', 'ewallet', 'virtual_account'])->count(),
            'delivery_orders' => $ordersInRange->where('pickup_delivery_opt_in', true)->count(),
            'loyalty_points_issued' => $ordersInRange->sum('loyalty_points_earned'),
            'whatsapp_notifications' => NotificationLog::query()
                ->when($startDate, fn ($query) => $query->whereDate('created_at', '>=', $startDate))
                ->when($endDate, fn ($query) => $query->whereDate('created_at', '<=', $endDate))
                ->where('channel', 'whatsapp-gateway')
                ->count(),
            'avg_order_value' => LaundryOrder::where('payment_status', 'paid')
                ->when($startDate, fn ($query) => $query->whereDate('created_at', '>=', $startDate))
                ->when($endDate, fn ($query) => $query->whereDate('created_at', '<=', $endDate))
                ->avg('total_price') ?? 0,
        ];

        $chartData = [
            'labels' => $monthlySeries->pluck('label')->values(),
            'adminRevenue' => $monthlySeries->pluck('admin_revenue')->values(),
            'merchantRevenue' => $monthlySeries->pluck('merchant_revenue')->values(),
        ];

        $orderSeries = [
            'labels' => $monthlySeries->pluck('label')->values(),
            'totalOrders' => $monthlySeries->pluck('orders_count')->values(),
            'paidOrders' => $monthlySeries->pluck('paid_orders_count')->values(),
            'completedOrders' => $monthlySeries->pluck('completed_orders_count')->values(),
        ];

        $paymentMix = [
            'labels' => $paymentMethodBreakdown->keys()->values(),
            'values' => $paymentMethodBreakdown->values(),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'topMerchants',
            'recentClaims',
            'latestOrders',
            'filters',
            'chartData',
            'orderSeries',
            'paymentMix',
        ));
    }

    public function merchants(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:all,active,inactive'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $rows = $this->merchantRows($filters);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 5;
        $paginatedRows = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $summary = $this->merchantSummary($rows);

        return view('admin.merchants', [
            'merchants' => $paginatedRows,
            'filters' => $filters,
            'summary' => $summary,
        ]);
    }

    public function reports(Request $request): View
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
            'status'    => ['nullable', 'in:all,active,inactive'],
            'q'         => ['nullable', 'string', 'max:255'],
        ]);

        [$startDate, $endDate] = $this->resolveDateRange($this->validateDateFilters($request));

        $ordersInRange = LaundryOrder::query()
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate,   fn ($q) => $q->whereDate('created_at', '<=', $endDate))
            ->get();

        $paidInRange = $ordersInRange->where('payment_status', 'paid');
        $gross       = (float) $paidInRange->sum('total_price');

        $summary = [
            'total_orders'    => $ordersInRange->count(),
            'paid_orders'     => $paidInRange->count(),
            'completed_orders'=> $ordersInRange->where('status', 'completed')->count(),
            'gross_revenue'   => $gross,
            'platform_cut'    => $this->platformRevenue($gross),
            'merchant_cut'    => $gross - $this->platformRevenue($gross),
            'claim_count'     => Claim::count(),
            'delivery_orders' => $ordersInRange->where('pickup_delivery_opt_in', true)->count(),
        ];

        $rows = $this->merchantRows($filters);

        return view('admin.reports', compact('summary', 'rows', 'filters'));
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:all,active,inactive'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        return Excel::download(
            new AdminReportExport($this->merchantRows($filters)),
            'safewash-admin-report.xlsx'
        );
    }

    public function claims(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:all,submitted,investigating,approved,rejected,compensated,closed'],
            'q'      => ['nullable', 'string', 'max:255'],
        ]);

        $claims = Claim::with(['order.laundry', 'customer'])
            ->when(! empty($filters['status']) && $filters['status'] !== 'all', fn ($q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['q']), function ($q) use ($filters): void {
                $q->where(function ($inner) use ($filters): void {
                    $inner->where('item_name', 'like', '%'.$filters['q'].'%')
                        ->orWhere('claimant_name', 'like', '%'.$filters['q'].'%')
                        ->orWhereHas('order', fn ($o) => $o->where('tracking_code', 'like', '%'.$filters['q'].'%'));
                });
            })
            ->latest()
            ->paginate(20);

        $summary = [
            'total'         => Claim::count(),
            'submitted'     => Claim::where('status', 'submitted')->count(),
            'investigating' => Claim::where('status', 'investigating')->count(),
            'approved'      => Claim::where('status', 'approved')->count(),
            'rejected'      => Claim::where('status', 'rejected')->count(),
            'compensated'   => Claim::where('status', 'compensated')->count(),
            'closed'        => Claim::where('status', 'closed')->count(),
        ];

        return view('admin.claims', compact('claims', 'summary', 'filters'));
    }

    public function updateClaim(Request $request, Claim $claim): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'status'              => ['required', 'in:submitted,investigating,approved,rejected,compensated,closed'],
            'resolution_notes'    => ['nullable', 'string', 'max:2000'],
            'compensation_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $claim->update([
            'status'              => $data['status'],
            'resolution_notes'    => $data['resolution_notes'] ?? $claim->resolution_notes,
            'compensation_amount' => $data['compensation_amount'] ?? $claim->compensation_amount,
            'resolved_at'         => in_array($data['status'], ['compensated', 'closed', 'rejected']) ? now() : $claim->resolved_at,
        ]);

        if ($claim->order) {
            $claim->order->update(['claim_status' => $data['status']]);
        }

        return back()->with('success', 'Status klaim berhasil diperbarui.');
    }

    public function payments(Request $request): View
    {
        $filters = $request->validate([
            'status'    => ['nullable', 'in:all,pending,paid,failed,expired'],
            'method'    => ['nullable', 'string', 'max:50'],
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $startDate = ! empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : null;
        $endDate   = ! empty($filters['date_to'])   ? Carbon::parse($filters['date_to'])->endOfDay()   : null;

        $transactions = PaymentTransaction::with(['order.laundry'])
            ->when(! empty($filters['status']) && $filters['status'] !== 'all', fn ($q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['method']), fn ($q) => $q->where('payment_method', $filters['method']))
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate,   fn ($q) => $q->whereDate('created_at', '<=', $endDate))
            ->latest()
            ->paginate(25);

        $summary = [
            'total_transactions' => PaymentTransaction::count(),
            'paid_count'         => PaymentTransaction::where('status', 'paid')->count(),
            'pending_count'      => PaymentTransaction::where('status', 'pending')->count(),
            'gross_revenue'      => PaymentTransaction::where('status', 'paid')->sum('gross_amount'),
            'total_fees'         => PaymentTransaction::where('status', 'paid')->sum('gateway_fee'),
            'net_revenue'        => PaymentTransaction::where('status', 'paid')->sum('net_amount'),
        ];

        return view('admin.payments', compact('transactions', 'summary', 'filters'));
    }

    public function exportPdf(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:all,active,inactive'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $rows = $this->merchantRows($filters);
        $summary = $this->merchantSummary($rows) + [
            'generated_at' => now(),
        ];

        return Pdf::loadView('admin.exports.report-pdf', [
            'rows' => $rows,
            'summary' => $summary,
            'filters' => $filters,
        ])->download('safewash-admin-report.pdf');
    }

    public function toggleMerchantStatus(Request $request, Laundry $laundry): JsonResponse
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $laundry->update([
            'is_active' => $data['is_active'],
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $laundry->is_active,
            'label' => $laundry->is_active ? 'active' : 'inactive',
        ]);
    }

    public function updateMerchant(Request $request, Laundry $laundry, MerchantScoringService $merchantScoring): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'subscription_plan' => ['required', 'string', 'max:100'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'service_fee_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'premium_protection_enabled' => ['nullable', 'boolean'],
            'supports_white_label' => ['nullable', 'boolean'],
            'network_name' => ['nullable', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'brand_primary_color' => ['nullable', 'string', 'max:20'],
            'brand_secondary_color' => ['nullable', 'string', 'max:20'],
            'custom_domain' => ['nullable', 'string', 'max:255'],
        ]);

        $laundry->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'description' => $data['description'] ?? null,
            'subscription_plan' => $data['subscription_plan'],
            'commission_rate' => $data['commission_rate'],
            'service_fee_rate' => $data['service_fee_rate'],
            'is_active' => $request->boolean('is_active'),
            'premium_protection_enabled' => $request->boolean('premium_protection_enabled'),
            'supports_white_label' => $request->boolean('supports_white_label'),
            'network_name' => $data['network_name'] ?? null,
            'brand_name' => $data['brand_name'] ?? null,
            'brand_primary_color' => $data['brand_primary_color'] ?? '#79bff3',
            'brand_secondary_color' => $data['brand_secondary_color'] ?? '#16324a',
            'custom_domain' => $data['custom_domain'] ?? null,
        ]);

        $merchantScoring->recalculate($laundry->fresh());

        return back()->with('success', 'Data merchant berhasil diperbarui.');
    }

    private function merchantRows(array $filters = []): Collection
    {
        [$startDate, $endDate] = $this->resolveDateRange($filters);

        return $this->merchantQuery($filters)
            ->get()
            ->map(fn (Laundry $laundry) => $this->mapMerchantRow($laundry, $startDate, $endDate))
            ->sortByDesc('paid_revenue')
            ->values();
    }

    private function merchantQuery(array $filters = []): Builder
    {
        $query = Laundry::with(['owner', 'orders.claims']);

        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('name', 'like', '%'.$keyword.'%')
                    ->orWhere('phone', 'like', '%'.$keyword.'%')
                    ->orWhere('address', 'like', '%'.$keyword.'%')
                    ->orWhereHas('owner', function ($ownerQuery) use ($keyword): void {
                        $ownerQuery->where('name', 'like', '%'.$keyword.'%')
                            ->orWhere('email', 'like', '%'.$keyword.'%');
                    });
            });
        }

        $status = $filters['status'] ?? 'all';
        if ($status === 'active') {
            $query->where('is_active', true);
        }

        if ($status === 'inactive') {
            $query->where('is_active', false);
        }

        return $query;
    }

    private function mapMerchantRow(Laundry $laundry, ?Carbon $startDate, ?Carbon $endDate): array
    {
        $filteredOrders = $laundry->orders
            ->filter(function (LaundryOrder $order) use ($startDate, $endDate): bool {
                if ($startDate && $order->created_at->lt($startDate->copy()->startOfDay())) {
                    return false;
                }

                if ($endDate && $order->created_at->gt($endDate->copy()->endOfDay())) {
                    return false;
                }

                return true;
            });

        $paidRevenue = (float) $filteredOrders
            ->where('payment_status', 'paid')
            ->sum('total_price');

        $openClaims = $filteredOrders
            ->flatMap->claims
            ->where('status', '!=', 'resolved')
            ->count();

        $platformCut = $this->platformRevenue($paidRevenue);

        return [
            // raw models (for export / other callers)
            'laundry'               => $laundry,
            'owner'                 => $laundry->owner,
            // view-facing flat keys
            'name'                  => $laundry->name,
            'plan'                  => $laundry->subscription_plan ?? '-',
            'city'                  => $laundry->city ?? '-',
            'total_orders'          => $filteredOrders->count(),
            'completed_orders'      => $filteredOrders->where('status', 'completed')->count(),
            'gross_revenue'         => $paidRevenue,
            'platform_revenue'      => $platformCut,
            'merchant_revenue'      => $paidRevenue - $platformCut,
            'merchant_score'        => (float) ($laundry->merchant_score ?? 0),
            // legacy / extra keys kept for backwards compat
            'orders_count'          => $filteredOrders->count(),
            'paid_revenue'          => $paidRevenue,
            'admin_revenue'         => $platformCut,
            'open_claims'           => $openClaims,
            'digital_payment_orders'=> $filteredOrders->whereIn('payment_method', ['qris', 'ewallet', 'virtual_account'])->count(),
            'delivery_orders'       => $filteredOrders->where('pickup_delivery_opt_in', true)->count(),
            'loyalty_points_issued' => $filteredOrders->sum('loyalty_points_earned'),
            'white_label_ready'     => (bool) $laundry->supports_white_label,
        ];
    }

    private function merchantSummary(Collection $merchants): array
    {
        return [
            'total' => $merchants->count(),
            'active' => $merchants->where(fn (array $row) => $row['laundry']->is_active)->count(),
            'inactive' => $merchants->where(fn (array $row) => ! $row['laundry']->is_active)->count(),
        ];
    }

    private function validateDateFilters(Request $request): array
    {
        return $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
    }

    private function resolveDateRange(array $filters): array
    {
        $startDate = ! empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : null;
        $endDate = ! empty($filters['date_to']) ? Carbon::parse($filters['date_to'])->endOfDay() : null;

        return [$startDate, $endDate];
    }

    private function buildMonthlySeries(Collection $paidOrders, ?Carbon $startDate, ?Carbon $endDate): Collection
    {
        // Load all orders for the chart range in a single query to avoid N+1 per month
        $seriesStart = $startDate ? $startDate->copy()->startOfMonth() : now()->subMonths(5)->startOfMonth();
        $seriesEnd   = $endDate   ? $endDate->copy()->endOfMonth()     : now()->endOfMonth();

        $allOrdersInSeries = LaundryOrder::query()
            ->whereBetween('created_at', [$seriesStart, $seriesEnd])
            ->get(['id', 'created_at', 'status', 'payment_status', 'total_price']);

        if ($startDate && $endDate) {
            $cursor = $startDate->copy()->startOfMonth();
            $months = collect();

            while ($cursor->lte($endDate)) {
                $months->push($cursor->copy());
                $cursor->addMonth();
            }

            return $months->map(fn (Carbon $month) => $this->buildMonthPoint($paidOrders, $allOrdersInSeries, $month));
        }

        return collect(range(5, 0, -1))
            ->map(fn (int $monthsAgo) => $this->buildMonthPoint($paidOrders, $allOrdersInSeries, now()->subMonths($monthsAgo)->startOfMonth()))
            ->push($this->buildMonthPoint($paidOrders, $allOrdersInSeries, now()->startOfMonth()));
    }

    private function buildMonthPoint(Collection $paidOrders, Collection $allOrders, Carbon $month): array
    {
        $end = $month->copy()->endOfMonth();

        $monthOrders = $allOrders->filter(
            fn (LaundryOrder $order) => $order->created_at->between($month, $end)
        );

        $monthRevenue = (float) $paidOrders
            ->filter(fn (LaundryOrder $order) => $order->created_at->between($month, $end))
            ->sum('total_price');

        return [
            'label' => $month->translatedFormat('M Y'),
            'admin_revenue' => $this->platformRevenue($monthRevenue),
            'merchant_revenue' => $monthRevenue - $this->platformRevenue($monthRevenue),
            'orders_count' => $monthOrders->count(),
            'paid_orders_count' => $monthOrders->where('payment_status', 'paid')->count(),
            'completed_orders_count' => $monthOrders->where('status', 'completed')->count(),
        ];
    }

    private function platformRevenue(float|int $grossRevenue): float
    {
        return $grossRevenue * ((self::COMMISSION_RATE + self::SERVICE_FEE_RATE) / 100);
    }
}

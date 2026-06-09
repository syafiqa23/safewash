<x-layouts.sidebar :title="'Admin Analytics'" :heading="'Analytics Platform'">

{{-- ─── Date filter ─────────────────────────────────────────────────────────── --}}
<div class="sw-card" style="margin-bottom:20px; padding:16px 20px;">
    <form method="GET" action="{{ route('admin.dashboard') }}" style="display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap;">
        <div class="sw-form-group" style="flex:1; min-width:140px;">
            <label class="sw-label">
                <i data-lucide="calendar" style="width:12px;height:12px;"></i>
                Dari Tanggal
            </label>
            <input type="date" name="date_from" class="sw-input" value="{{ $filters['date_from'] ?? '' }}">
        </div>
        <div class="sw-form-group" style="flex:1; min-width:140px;">
            <label class="sw-label">
                <i data-lucide="calendar" style="width:12px;height:12px;"></i>
                Sampai Tanggal
            </label>
            <input type="date" name="date_to" class="sw-input" value="{{ $filters['date_to'] ?? '' }}">
        </div>
        <div style="display:flex; gap:8px; padding-bottom:1px;">
            <button type="submit" class="sw-btn sw-btn-primary">
                <i data-lucide="filter" style="width:14px;height:14px;"></i>
                Terapkan
            </button>
            <a href="{{ route('admin.dashboard') }}" class="sw-btn sw-btn-ghost">
                <i data-lucide="rotate-ccw" style="width:14px;height:14px;"></i>
                Reset
            </a>
        </div>
        <div style="margin-left:auto; display:flex; gap:8px; padding-bottom:1px; flex-wrap:wrap;">
            <a href="{{ route('admin.reports.excel', request()->query()) }}" class="sw-btn sw-btn-ghost" style="color:var(--success);">
                <i data-lucide="table-2" style="width:14px;height:14px;"></i>
                Export Excel
            </a>
            <a href="{{ route('admin.reports.pdf', request()->query()) }}" class="sw-btn sw-btn-ghost" style="color:var(--danger);">
                <i data-lucide="file-text" style="width:14px;height:14px;"></i>
                Export PDF
            </a>
        </div>
    </form>
</div>

{{-- ─── KPI Row 1: Revenue ─────────────────────────────────────────────────── --}}
<div class="sw-kpi-grid">
    <x-ui.kpi-card icon="banknote"      label="Omzet Platform"       value="Rp{{ number_format($stats['gross_revenue'],0,',','.') }}"        color="accent" />
    <x-ui.kpi-card icon="trending-up"   label="Pemasukan Admin"       value="Rp{{ number_format($stats['admin_revenue'],0,',','.') }}"         color="green" />
    <x-ui.kpi-card icon="wallet"        label="Pendapatan Merchant"   value="Rp{{ number_format($stats['merchant_revenue'],0,',','.') }}"       color="purple" />
    <x-ui.kpi-card icon="receipt"       label="Rata-rata Nilai Order"  value="Rp{{ number_format($stats['avg_order_value'],0,',','.') }}"       color="amber" />
</div>

{{-- ─── KPI Row 2: Commission + Merchants ─────────────────────────────────── --}}
<div class="sw-kpi-grid" style="margin-top:12px;">
    <x-ui.kpi-card icon="percent"       label="Komisi Admin 15%"      value="Rp{{ number_format($stats['commission_revenue'],0,',','.') }}"    color="accent" />
    <x-ui.kpi-card icon="tag"           label="Service Fee 3%"         value="Rp{{ number_format($stats['service_fee_revenue'],0,',','.') }}"   color="green" />
    <x-ui.kpi-card icon="store"         label="Merchant Aktif"         value="{{ number_format($stats['merchant_active_count']) }}"             color="purple" />
    <x-ui.kpi-card icon="award"         label="White-Label Ready"      value="{{ number_format($stats['white_label_count']) }}"                 color="amber" />
</div>

{{-- ─── KPI Row 3: Ops ─────────────────────────────────────────────────────── --}}
<div class="sw-kpi-grid" style="margin-top:12px;">
    <x-ui.kpi-card icon="activity"      label="Skor Merchant Rata-rata" value="{{ number_format($stats['average_merchant_score'],1) }}/100"   color="accent" />
    <x-ui.kpi-card icon="truck"         label="Order Pickup-Delivery"  value="{{ number_format($stats['delivery_orders']) }}"                   color="green" />
    <x-ui.kpi-card icon="smartphone"    label="Pembayaran Digital"     value="{{ number_format($stats['digital_payment_orders']) }}"            color="purple" />
    <x-ui.kpi-card icon="star"          label="Loyalty Points Issued"  value="{{ number_format($stats['loyalty_points_issued']) }} pts"         color="amber" />
</div>

{{-- ─── Charts ──────────────────────────────────────────────────────────────── --}}
<div class="sw-body-grid" style="margin-top:24px;">
    <div class="sw-card">
        <x-ui.section-header title="Pendapatan vs Merchant" icon="bar-chart-2" badge="Bar Chart" />
        <div class="sw-chart-wrap">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    <div class="sw-card">
        <x-ui.section-header title="Distribusi Metode Pembayaran" icon="pie-chart" badge="Doughnut" />
        <div class="sw-chart-wrap">
            <canvas id="paymentMixChart"></canvas>
        </div>
    </div>
</div>

<div class="sw-card" style="margin-top:20px;">
    <x-ui.section-header title="Tren Order Bulanan" icon="line-chart" badge="Multi-series" />
    <div class="sw-chart-wrap">
        <canvas id="ordersChart"></canvas>
    </div>
</div>

{{-- ─── Top Merchants + Health ──────────────────────────────────────────────── --}}
<div class="sw-body-grid" style="margin-top:20px;">
    <div class="sw-card">
        <x-ui.section-header title="Top Merchant" icon="trophy">
            <a href="{{ route('admin.merchants.index', request()->query()) }}" class="sw-btn sw-btn-ghost sw-btn-sm">
                <i data-lucide="arrow-right" style="width:13px;height:13px;"></i>
                Semua Merchant
            </a>
        </x-ui.section-header>

        @forelse ($topMerchants as $row)
            <div class="sw-list-row">
                <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                    <div class="sw-timeline-dot" style="width:36px; height:36px; flex-shrink:0;">
                        <i data-lucide="store" style="width:15px; height:15px;"></i>
                    </div>
                    <div style="min-width:0;">
                        <div style="font-weight:600; font-size:13px; color:var(--ink); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $row['laundry']->name }}</div>
                        <div class="sw-muted" style="font-size:11.5px;">{{ $row['orders_count'] }} order · skor {{ number_format($row['merchant_score'],1) }}</div>
                    </div>
                </div>
                <div style="text-align:right; flex-shrink:0;">
                    <div style="font-weight:700; font-size:13px; color:var(--ink);">Rp{{ number_format($row['paid_revenue'],0,',','.') }}</div>
                    <div class="sw-muted" style="font-size:11px;">Admin Rp{{ number_format($row['admin_revenue'],0,',','.') }}</div>
                    @if ($row['white_label_ready'])
                        <span class="sw-badge sw-badge-purple" style="font-size:10px; margin-top:2px;">White-label</span>
                    @endif
                </div>
            </div>
        @empty
            <x-ui.empty-state icon="store" title="Belum ada merchant" />
        @endforelse
    </div>

    <div class="sw-card">
        <x-ui.section-header title="Health Platform" icon="heart-pulse" badge="{{ $stats['claims_open'] }} klaim" />

        <div style="display:flex; flex-direction:column; gap:0;">
            @foreach ([
                ['label' => 'Order Aktif',         'value' => $stats['active_orders'],              'icon' => 'activity',       'color' => 'var(--accent)'],
                ['label' => 'Merchant Aktif',       'value' => $stats['merchant_active_count'],      'icon' => 'check-circle',   'color' => 'var(--success)'],
                ['label' => 'Merchant Nonaktif',    'value' => $stats['merchant_inactive_count'],    'icon' => 'minus-circle',   'color' => 'var(--subtle)'],
                ['label' => 'Formula Admin',        'value' => '15% + 3%',                           'icon' => 'percent',        'color' => 'var(--warning)'],
                ['label' => 'Biaya Daftar',         'value' => 'Gratis',                             'icon' => 'gift',           'color' => 'var(--accent)'],
                ['label' => 'Pembayaran Digital',   'value' => $stats['digital_payment_orders'].' order', 'icon' => 'smartphone','color' => 'var(--accent)'],
                ['label' => 'WA Notifications',     'value' => $stats['whatsapp_notifications'],     'icon' => 'message-circle', 'color' => 'var(--success)'],
            ] as $item)
                <div class="sw-list-row">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <i data-lucide="{{ $item['icon'] }}" style="width:15px;height:15px;color:{{ $item['color'] }};flex-shrink:0;"></i>
                        <span style="font-size:13px; color:var(--ink-2);">{{ $item['label'] }}</span>
                    </div>
                    <span style="font-size:13px; font-weight:700; color:var(--ink);">{{ $item['value'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ─── Recent Claims + Orders ─────────────────────────────────────────────── --}}
<div class="sw-body-grid" style="margin-top:20px;">
    <div class="sw-card">
        <x-ui.section-header title="Klaim Terbaru" icon="alert-circle" badge="{{ $recentClaims->count() }} item" />
        @forelse ($recentClaims as $claim)
            <div class="sw-list-row">
                <div>
                    <div style="font-weight:600; font-size:13px; color:var(--ink);">{{ $claim->item_name }}</div>
                    <div class="sw-muted" style="font-size:11.5px;">{{ $claim->order?->laundry?->name }} · {{ Str::limit($claim->description, 35) }}</div>
                </div>
                <x-ui.status-badge :status="$claim->status" size="sm" />
            </div>
        @empty
            <x-ui.empty-state icon="check-circle" title="Tidak ada klaim aktif" />
        @endforelse
    </div>

    <div class="sw-card">
        <x-ui.section-header title="Order Terbaru Platform" icon="list-ordered">
            <a href="{{ route('orders.index') }}" class="sw-btn sw-btn-ghost sw-btn-sm">
                <i data-lucide="arrow-right" style="width:13px;height:13px;"></i>
                Lihat Semua
            </a>
        </x-ui.section-header>

        @foreach ($latestOrders as $order)
            <div class="sw-list-row" onclick="window.location='{{ route('orders.show', $order) }}'" style="cursor:pointer;">
                <div style="min-width:0;">
                    <div style="font-weight:700; font-size:13px; font-family:monospace; letter-spacing:.5px; color:var(--ink);">{{ $order->tracking_code }}</div>
                    <div class="sw-muted" style="font-size:11.5px;">{{ $order->laundry->name }} · {{ $order->customer_name }}</div>
                </div>
                <div style="text-align:right; flex-shrink:0;">
                    <x-ui.status-badge :status="$order->status" size="sm" />
                    <div class="sw-muted" style="font-size:11px; margin-top:3px;">Rp{{ number_format($order->total_price,0,',','.') }}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- ─── Chart.js scripts ────────────────────────────────────────────────────── --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartData   = @json($chartData);
    const orderSeries = @json($orderSeries);
    const paymentMix  = @json($paymentMix);

    const style   = getComputedStyle(document.documentElement);
    const accent  = style.getPropertyValue('--accent').trim()  || '#2563EB';
    const green   = style.getPropertyValue('--success').trim() || '#22C55E';
    const amber   = style.getPropertyValue('--warning').trim() || '#F59E0B';
    const purple  = style.getPropertyValue('--info').trim()    || '#0EA5E9';

    // Revenue chart
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [
                { label: 'Pemasukan Admin',      data: chartData.adminRevenue,    backgroundColor: accent + 'cc',  borderRadius: 8 },
                { label: 'Pendapatan Merchant',  data: chartData.merchantRevenue, backgroundColor: green  + 'cc',  borderRadius: 8 },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, font: { size: 12 } } },
                tooltip: {
                    callbacks: {
                        label(ctx) { return `${ctx.dataset.label}: Rp${new Intl.NumberFormat('id-ID').format(ctx.raw)}`; }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: style.getPropertyValue('--line').trim() || '#e2e8f0' },
                    ticks: { callback(v) { return 'Rp' + new Intl.NumberFormat('id-ID').format(v); }, font: { size: 11 } }
                },
                x: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });

    // Orders line chart
    new Chart(document.getElementById('ordersChart'), {
        type: 'line',
        data: {
            labels: orderSeries.labels,
            datasets: [
                { label: 'Total Order',    data: orderSeries.totalOrders,     borderColor: accent, backgroundColor: accent + '22', tension: 0.35, fill: true },
                { label: 'Order Paid',     data: orderSeries.paidOrders,      borderColor: green,  backgroundColor: green  + '22', tension: 0.35, fill: true },
                { label: 'Order Completed',data: orderSeries.completedOrders, borderColor: amber,  backgroundColor: amber  + '22', tension: 0.35, fill: true },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', labels: { usePointStyle: true, font: { size: 12 } } } },
            scales: {
                y: { beginAtZero: true, grid: { color: style.getPropertyValue('--line').trim() || '#e2e8f0' }, ticks: { precision: 0, font: { size: 11 } } },
                x: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });

    // Payment mix doughnut
    new Chart(document.getElementById('paymentMixChart'), {
        type: 'doughnut',
        data: {
            labels: paymentMix.labels,
            datasets: [{
                data: paymentMix.values,
                backgroundColor: [accent + 'cc', green + 'cc', amber + 'cc', purple + 'cc'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 12 } } } }
        }
    });
});
</script>

</x-layouts.sidebar>

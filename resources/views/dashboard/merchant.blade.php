<x-layouts.sidebar title="Dashboard Merchant" heading="Selamat Datang, {{ auth()->user()->name }}">

{{-- ─── KPI HARI INI ────────────────────────────────────────────────────────── --}}
<div class="sw-kpi-grid sw-mb-20">
    <x-ui.kpi-card icon="package"       label="Order Hari Ini"       value="{{ number_format($stats['today_orders']) }}"  color="accent" />
    <x-ui.kpi-card icon="activity"      label="Order Aktif"          value="{{ number_format($stats['active_orders']) }}" color="blue" />
    <x-ui.kpi-card icon="truck"         label="Pickup Hari Ini"      value="{{ number_format($stats['today_pickup']) }}"  color="purple" />
    <x-ui.kpi-card icon="banknote"      label="Pendapatan Hari Ini"  value="Rp{{ number_format($stats['today_revenue'],0,',','.') }}" color="green" />
</div>

{{-- ─── QUICK ACTION ─────────────────────────────────────────────────────────── --}}
<div class="sw-card sw-mb-20">
    <div style="font-size:13px;font-weight:600;color:var(--ink-2);text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px;">Quick Action</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;">
        <a href="{{ route('orders.create') }}" class="sw-btn sw-btn-primary" style="justify-content:center;flex-direction:column;gap:6px;padding:14px 10px;height:auto;">
            <i data-lucide="package-plus" style="width:22px;height:22px;"></i>
            <span>Buat Order</span>
        </a>
        <a href="{{ route('merchant.qr.index') }}" class="sw-btn sw-btn-ghost" style="justify-content:center;flex-direction:column;gap:6px;padding:14px 10px;height:auto;border:1.5px solid var(--surface-3);">
            <i data-lucide="qr-code" style="width:22px;height:22px;color:var(--accent);"></i>
            <span>QR Tracking</span>
        </a>
        <a href="{{ route('orders.index', ['delivery' => 1]) }}" class="sw-btn sw-btn-ghost" style="justify-content:center;flex-direction:column;gap:6px;padding:14px 10px;height:auto;border:1.5px solid var(--surface-3);">
            <i data-lucide="truck" style="width:22px;height:22px;color:#7c3aed;"></i>
            <span>Pickup-Delivery</span>
        </a>
        <a href="{{ route('merchant.customers.index') }}" class="sw-btn sw-btn-ghost" style="justify-content:center;flex-direction:column;gap:6px;padding:14px 10px;height:auto;border:1.5px solid var(--surface-3);">
            <i data-lucide="users" style="width:22px;height:22px;color:#059669;"></i>
            <span>Pelanggan</span>
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

    <div>
        {{-- ─── ORDER AKTIF ─────────────────────────────────────────────────────── --}}
        <div class="sw-card sw-mb-20">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <x-ui.section-header title="Order Aktif" icon="activity" badge="{{ $stats['active_orders'] }}" />
                <a href="{{ route('orders.index', ['active' => 1]) }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Lihat Semua →</a>
            </div>
            @if ($activeOrderList->isEmpty())
                <x-ui.empty-state icon="activity" title="Tidak ada order aktif" description="Semua order sudah selesai." />
            @else
                <div class="sw-table-wrap">
                    <table class="sw-table">
                        <thead>
                            <tr>
                                <th>Tracking</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Delivery</th>
                                <th class="sw-text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activeOrderList as $order)
                                <tr onclick="window.location='{{ route('orders.show',$order) }}'" style="cursor:pointer;">
                                    <td class="sw-font-mono sw-fw-700" style="color:var(--accent);">{{ $order->tracking_code }}</td>
                                    <td>
                                        <div class="sw-fw-600 sw-text-sm">{{ $order->customer_name }}</div>
                                        <div class="sw-text-xs sw-muted">{{ $order->customer_phone }}</div>
                                    </td>
                                    <td><x-ui.status-badge :status="$order->status" /></td>
                                    <td>
                                        @if ($order->pickup_delivery_opt_in)
                                            <span class="sw-badge sw-badge-blue sw-text-xs"><i data-lucide="truck" style="width:10px;height:10px;"></i> Aktif</span>
                                        @else
                                            <span class="sw-muted sw-text-xs">Outlet</span>
                                        @endif
                                    </td>
                                    <td class="sw-text-right sw-fw-700 sw-text-sm">Rp{{ number_format($order->total_price,0,',','.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ─── PICKUP DELIVERY AKTIF ───────────────────────────────────────────── --}}
        <div class="sw-card sw-mb-20">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <x-ui.section-header title="Pickup Delivery Aktif" icon="truck" />
                <a href="{{ route('orders.index', ['delivery' => 1]) }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Lihat Semua →</a>
            </div>
            @if ($activeDeliveries->isEmpty())
                <x-ui.empty-state icon="truck" title="Tidak ada pickup aktif" description="Pickup-delivery selesai semua." />
            @else
                @foreach ($activeDeliveries as $order)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--surface-3);">
                        <div>
                            <div class="sw-font-mono sw-fw-600 sw-text-sm" style="color:var(--accent);">{{ $order->tracking_code }}</div>
                            <div class="sw-text-xs sw-muted">{{ $order->customer_name }}</div>
                        </div>
                        <div style="text-align:right;">
                            @if ($order->deliveryRequest)
                                <x-ui.status-badge :status="$order->deliveryRequest->status" size="sm" />
                                @if ($order->deliveryRequest->pickup_scheduled_at)
                                    <div class="sw-text-xs sw-muted sw-mt-4">{{ $order->deliveryRequest->pickup_scheduled_at->format('d M H:i') }}</div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- ─── KLAIM AKTIF ─────────────────────────────────────────────────────── --}}
        @if ($activeClaims->isNotEmpty())
            <div class="sw-card sw-mb-20" style="border-left:3px solid #dc2626;">
                <x-ui.section-header title="Klaim Perlu Ditindaklanjuti" icon="shield-alert" badge="{{ $activeClaims->count() }}" />
                @foreach ($activeClaims as $claim)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--surface-3);">
                        <div>
                            <div class="sw-fw-600 sw-text-sm">{{ $claim->item_name }}</div>
                            <div class="sw-text-xs sw-muted">Order: {{ $claim->order->tracking_code }} · {{ $claim->claimant_name }}</div>
                        </div>
                        <div style="text-align:right;">
                            <x-ui.status-badge :status="$claim->status" size="sm" />
                            <div class="sw-text-xs sw-muted sw-mt-4">{{ $claim->submitted_at?->format('d M') }}</div>
                        </div>
                    </div>
                @endforeach
                <a href="{{ route('claims.index') }}" style="font-size:12px;color:var(--accent);text-decoration:none;display:block;margin-top:8px;">Lihat Semua Klaim →</a>
            </div>
        @endif
    </div>

    {{-- Right: Pendapatan + Aktivitas --}}
    <div>
        {{-- Pendapatan Bulan Ini --}}
        <div class="sw-card sw-mb-16">
            <x-ui.section-header title="Pendapatan Bulan Ini" icon="wallet" />
            <div style="font-size:28px;font-weight:800;color:var(--success);margin:8px 0 4px;">
                Rp{{ number_format($stats['month_revenue'],0,',','.') }}
            </div>
            <div style="font-size:12px;color:var(--ink-3);margin-bottom:12px;">Gross dari order berbayar</div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div class="sw-mini-kpi">
                    <div class="sw-mini-kpi-lbl">Komisi (15%)</div>
                    <div class="sw-mini-kpi-val" style="color:#d97706;">Rp{{ number_format($stats['month_revenue']*0.15,0,',','.') }}</div>
                </div>
                <div class="sw-mini-kpi">
                    <div class="sw-mini-kpi-lbl">Net Anda</div>
                    <div class="sw-mini-kpi-val" style="color:var(--success);">Rp{{ number_format($stats['month_revenue']*0.82,0,',','.') }}</div>
                </div>
            </div>

            <a href="{{ route('merchant.revenue.index') }}" class="sw-btn sw-btn-ghost sw-w-full sw-mt-4" style="justify-content:center;font-size:13px;">
                Laporan Lengkap →
            </a>
        </div>

        {{-- Merchant Score --}}
        <div class="sw-card sw-mb-16">
            <x-ui.section-header title="Merchant Score" icon="bar-chart-3" />
            <div style="font-size:36px;font-weight:800;color:var(--accent);margin:6px 0 2px;">{{ number_format($stats['merchant_score'],1) }}</div>
            <div style="font-size:12px;color:var(--ink-3);margin-bottom:10px;">dari 100 poin</div>
            <div style="height:8px;background:var(--surface-3);border-radius:4px;overflow:hidden;">
                <div style="width:{{ min(100,$stats['merchant_score']) }}%;height:100%;background:var(--accent);border-radius:4px;"></div>
            </div>
        </div>

        {{-- Aktivitas Terakhir --}}
        <div class="sw-card">
            <x-ui.section-header title="Aktivitas Terakhir" icon="clock" />
            @foreach ($recentOrders->take(6) as $order)
                <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--surface-3);">
                    <div style="width:6px;height:6px;border-radius:50%;background:{{ in_array($order->status,['completed']) ? 'var(--success)' : 'var(--accent)' }};flex-shrink:0;"></div>
                    <div style="flex:1;min-width:0;">
                        <div class="sw-font-mono sw-text-xs sw-fw-600" style="color:var(--accent);">{{ $order->tracking_code }}</div>
                        <div class="sw-text-xs sw-muted">{{ $order->customer_name }}</div>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <x-ui.status-badge :status="$order->status" size="sm" />
                        <div class="sw-text-xs sw-muted">{{ $order->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>

</x-layouts.sidebar>

<x-layouts.sidebar title="Laporan Platform" heading="Laporan Platform">

{{-- Filter --}}
<div class="sw-card sw-mb-20">
    <form method="GET" action="{{ route('admin.reports.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div class="sw-form-group" style="flex:1;min-width:140px;">
            <label class="sw-label">Dari Tanggal</label>
            <input type="date" name="date_from" class="sw-input" value="{{ $filters['date_from'] ?? '' }}">
        </div>
        <div class="sw-form-group" style="flex:1;min-width:140px;">
            <label class="sw-label">Sampai Tanggal</label>
            <input type="date" name="date_to" class="sw-input" value="{{ $filters['date_to'] ?? '' }}">
        </div>
        <div style="display:flex;gap:6px;padding-bottom:1px;">
            <button type="submit" class="sw-btn sw-btn-primary"><i data-lucide="filter" style="width:14px;height:14px;"></i> Filter</button>
            <a href="{{ route('admin.reports.index') }}" class="sw-btn sw-btn-ghost">Reset</a>
        </div>
        <div style="margin-left:auto;display:flex;gap:6px;padding-bottom:1px;">
            <a href="{{ route('admin.reports.excel', $filters) }}" class="sw-btn sw-btn-ghost">
                <i data-lucide="file-spreadsheet" style="width:14px;height:14px;"></i> Export Excel
            </a>
            <a href="{{ route('admin.reports.pdf', $filters) }}" class="sw-btn sw-btn-ghost">
                <i data-lucide="file-text" style="width:14px;height:14px;"></i> Export PDF
            </a>
        </div>
    </form>
</div>

{{-- Summary KPIs --}}
<div class="sw-kpi-grid sw-mb-20">
    <x-ui.kpi-card icon="package"      label="Total Order"       value="{{ number_format($summary['total_orders']) }}"    color="accent" />
    <x-ui.kpi-card icon="check-circle" label="Order Lunas"       value="{{ number_format($summary['paid_orders']) }}"     color="green" />
    <x-ui.kpi-card icon="flag"         label="Order Selesai"     value="{{ number_format($summary['completed_orders']) }}" color="purple" />
    <x-ui.kpi-card icon="shield-alert" label="Klaim"             value="{{ number_format($summary['claim_count']) }}"     color="amber" />
</div>

<div class="sw-kpi-grid sw-mb-20" style="grid-template-columns:repeat(3,1fr);">
    <x-ui.kpi-card icon="banknote"    label="Gross Revenue"     value="Rp{{ number_format($summary['gross_revenue'],0,',','.') }}"    color="accent" />
    <x-ui.kpi-card icon="percent"     label="Platform (18%)"    value="Rp{{ number_format($summary['platform_cut'],0,',','.') }}"     color="purple" />
    <x-ui.kpi-card icon="wallet"      label="Net ke Merchant"   value="Rp{{ number_format($summary['merchant_cut'],0,',','.') }}"     color="green" />
</div>

{{-- Merchant Table --}}
<div class="sw-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <x-ui.section-header title="Performa per Merchant" icon="store" />
        <div style="display:flex;gap:6px;">
            <a href="{{ route('admin.reports.excel', $filters) }}" class="sw-btn sw-btn-ghost sw-btn-sm">
                <i data-lucide="download" style="width:13px;height:13px;"></i> Excel
            </a>
        </div>
    </div>

    @if ($rows->isEmpty())
        <x-ui.empty-state icon="store" title="Tidak ada data merchant" description="Coba sesuaikan filter tanggal." />
    @else
        <div class="sw-table-wrap">
            <table class="sw-table">
                <thead>
                    <tr>
                        <th>Merchant</th>
                        <th>Kota</th>
                        <th class="sw-text-right">Total Order</th>
                        <th class="sw-text-right">Order Selesai</th>
                        <th class="sw-text-right">Gross Revenue</th>
                        <th class="sw-text-right">Platform Cut</th>
                        <th class="sw-text-right">Net Merchant</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $m)
                        @php
                            $mName     = $m['name'] ?? ($m['laundry']->name ?? '-');
                            $mPlan     = $m['plan'] ?? ($m['laundry']->subscription_plan ?? '-');
                            $mCity     = $m['city'] ?? ($m['laundry']->city ?? '—');
                            $mTotal    = $m['total_orders'] ?? ($m['orders_count'] ?? 0);
                            $mDone     = $m['completed_orders'] ?? 0;
                            $mGross    = $m['gross_revenue'] ?? ($m['paid_revenue'] ?? 0);
                            $mPlatform = $m['platform_revenue'] ?? ($m['admin_revenue'] ?? 0);
                            $mNet      = $m['merchant_revenue'] ?? 0;
                            $mScore    = $m['merchant_score'] ?? 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="sw-fw-600 sw-text-sm">{{ $mName }}</div>
                                <div class="sw-text-xs sw-muted">{{ $mPlan }}</div>
                            </td>
                            <td class="sw-text-sm sw-muted">{{ $mCity }}</td>
                            <td class="sw-text-right sw-fw-600">{{ number_format($mTotal) }}</td>
                            <td class="sw-text-right sw-text-sm">{{ number_format($mDone) }}</td>
                            <td class="sw-text-right sw-text-sm">Rp{{ number_format($mGross,0,',','.') }}</td>
                            <td class="sw-text-right sw-text-xs sw-muted">Rp{{ number_format($mPlatform,0,',','.') }}</td>
                            <td class="sw-text-right sw-fw-700 sw-text-sm" style="color:var(--success);">Rp{{ number_format($mNet,0,',','.') }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <div style="flex:1;height:6px;background:var(--surface-3);border-radius:3px;overflow:hidden;">
                                        <div style="width:{{ min(100,$mScore) }}%;height:100%;background:var(--accent);border-radius:3px;"></div>
                                    </div>
                                    <span class="sw-text-xs sw-fw-700">{{ number_format($mScore,1) }}</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

</x-layouts.sidebar>

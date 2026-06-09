<x-layouts.sidebar title="Pendapatan Merchant" heading="Pendapatan Merchant">

{{-- Filter Tanggal --}}
<div class="sw-card sw-mb-20">
    <form method="GET" action="{{ route('merchant.revenue.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div class="sw-form-group">
            <label class="sw-label">Dari Tanggal</label>
            <input type="date" name="from" class="sw-input" value="{{ $from->format('Y-m-d') }}">
        </div>
        <div class="sw-form-group">
            <label class="sw-label">Sampai Tanggal</label>
            <input type="date" name="to" class="sw-input" value="{{ $to->format('Y-m-d') }}">
        </div>
        <div style="display:flex;gap:6px;padding-bottom:1px;">
            <button type="submit" class="sw-btn sw-btn-primary"><i data-lucide="filter" style="width:14px;height:14px;"></i> Tampilkan</button>
            <a href="{{ route('merchant.revenue.index') }}" class="sw-btn sw-btn-ghost">Bulan Ini</a>
        </div>
    </form>
</div>

{{-- KPI --}}
<div class="sw-kpi-grid sw-mb-20">
    <x-ui.kpi-card icon="banknote"    label="Gross Revenue"    value="Rp{{ number_format($summary['gross'],0,',','.') }}"       color="accent" />
    <x-ui.kpi-card icon="wallet"      label="Pendapatan Bersih" value="Rp{{ number_format($summary['net'],0,',','.') }}"          color="green" />
    <x-ui.kpi-card icon="percent"     label="Komisi Platform (15%)" value="Rp{{ number_format($summary['commission'],0,',','.') }}" color="amber" />
    <x-ui.kpi-card icon="credit-card" label="Service Fee (3%)" value="Rp{{ number_format($summary['service_fee'],0,',','.') }}"  color="purple" />
</div>

{{-- Breakdown per hari --}}
<div class="sw-card sw-mb-20">
    <x-ui.section-header title="Pendapatan per Hari" icon="bar-chart-3" badge="{{ $summary['orders'] }} order berbayar" />
    @if ($byDay->isEmpty())
        <x-ui.empty-state icon="bar-chart-3" title="Belum ada data" description="Tidak ada order berbayar di rentang ini." />
    @else
        <div class="sw-table-wrap">
            <table class="sw-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th class="sw-text-right">Gross</th>
                        <th class="sw-text-right">Komisi (15%)</th>
                        <th class="sw-text-right">Fee (3%)</th>
                        <th class="sw-text-right">Net Merchant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($byDay as $row)
                        @php
                            $g = (float) $row->total;
                            $c = $g * 0.15;
                            $f = $g * 0.03;
                            $n = $g - $c - $f;
                        @endphp
                        <tr>
                            <td class="sw-fw-600">{{ \Carbon\Carbon::parse($row->day)->format('d M Y') }}</td>
                            <td class="sw-text-right sw-text-sm">Rp{{ number_format($g,0,',','.') }}</td>
                            <td class="sw-text-right sw-text-xs sw-muted">Rp{{ number_format($c,0,',','.') }}</td>
                            <td class="sw-text-right sw-text-xs sw-muted">Rp{{ number_format($f,0,',','.') }}</td>
                            <td class="sw-text-right sw-fw-700 sw-text-sm" style="color:var(--success);">Rp{{ number_format($n,0,',','.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Settlement History --}}
<div class="sw-card">
    <x-ui.section-header title="Riwayat Settlement" icon="banknote" />
    @if ($settlements->isEmpty())
        <x-ui.empty-state icon="banknote" title="Belum ada settlement" description="Admin akan membuat settlement secara berkala." />
    @else
        <div class="sw-table-wrap">
            <table class="sw-table">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th class="sw-text-right">Order</th>
                        <th class="sw-text-right">Gross</th>
                        <th class="sw-text-right">Komisi + Fee</th>
                        <th class="sw-text-right">Net Merchant</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($settlements as $s)
                        <tr>
                            <td class="sw-text-sm">
                                {{ $s->period_start->format('d M') }} – {{ $s->period_end->format('d M Y') }}
                            </td>
                            <td class="sw-text-right sw-fw-600">{{ number_format($s->order_count) }}</td>
                            <td class="sw-text-right sw-text-sm">Rp{{ number_format($s->gross_amount,0,',','.') }}</td>
                            <td class="sw-text-right sw-text-xs sw-muted">Rp{{ number_format($s->commission_amount + $s->service_fee,0,',','.') }}</td>
                            <td class="sw-text-right sw-fw-700 sw-text-sm" style="color:var(--success);">Rp{{ number_format($s->net_amount,0,',','.') }}</td>
                            <td>
                                <x-ui.status-badge :status="$s->status" />
                            </td>
                            <td class="sw-text-xs sw-muted">{{ $s->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($settlements->hasPages())
            <div class="sw-flex sw-justify-center" style="padding:16px 0 4px;">
                {{ $settlements->links() }}
            </div>
        @endif
    @endif
</div>

</x-layouts.sidebar>

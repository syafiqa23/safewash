<x-layouts.sidebar :title="'Payment Monitoring'" :heading="'Payment Monitoring'">

{{-- ─── Summary KPIs ─────────────────────────────────────────────────────── --}}
<div class="sw-kpi-grid sw-mb-20">
    <x-ui.kpi-card icon="credit-card"    label="Total Transaksi"  value="{{ number_format($summary['total_transactions']) }}"  color="accent" />
    <x-ui.kpi-card icon="check-circle"   label="Transaksi Lunas"  value="{{ number_format($summary['paid_count']) }}"          color="green" />
    <x-ui.kpi-card icon="clock"          label="Pending"          value="{{ number_format($summary['pending_count']) }}"       color="amber" />
    <x-ui.kpi-card icon="banknote"       label="Gross Revenue"    value="Rp{{ number_format($summary['gross_revenue'], 0, ',', '.') }}" color="purple" />
</div>

<div class="sw-kpi-grid sw-mb-20" style="grid-template-columns:repeat(3,minmax(0,1fr));">
    <x-ui.kpi-card icon="percent"   label="Gateway Fee Total"  value="Rp{{ number_format($summary['total_fees'], 0, ',', '.') }}"   color="amber" />
    <x-ui.kpi-card icon="wallet"    label="Net Revenue"        value="Rp{{ number_format($summary['net_revenue'], 0, ',', '.') }}"  color="green" />
    <x-ui.kpi-card icon="trending-up" label="Platform 18% dari Gross" value="Rp{{ number_format($summary['gross_revenue'] * 0.18, 0, ',', '.') }}" color="accent" />
</div>

{{-- ─── Filter ────────────────────────────────────────────────────────────── --}}
<div class="sw-card sw-mb-20">
    <form method="GET" action="{{ route('admin.payments.index') }}" style="display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap;">
        <div class="sw-form-group" style="flex:1; min-width:130px;">
            <label class="sw-label">Status</label>
            <select name="status" class="sw-input">
                @foreach (['all' => 'Semua', 'pending' => 'Pending', 'paid' => 'Lunas', 'failed' => 'Gagal', 'expired' => 'Expired'] as $val => $lbl)
                    <option value="{{ $val }}" {{ ($filters['status'] ?? 'all') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="sw-form-group" style="flex:1; min-width:130px;">
            <label class="sw-label">Metode</label>
            <select name="method" class="sw-input">
                <option value="">Semua Metode</option>
                @foreach (['cash' => 'Cash', 'qris' => 'QRIS', 'ewallet' => 'E-Wallet', 'virtual_account' => 'Virtual Account'] as $val => $lbl)
                    <option value="{{ $val }}" {{ ($filters['method'] ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="sw-form-group" style="flex:1; min-width:130px;">
            <label class="sw-label">Dari Tanggal</label>
            <input type="date" name="date_from" class="sw-input" value="{{ $filters['date_from'] ?? '' }}">
        </div>
        <div class="sw-form-group" style="flex:1; min-width:130px;">
            <label class="sw-label">Sampai Tanggal</label>
            <input type="date" name="date_to" class="sw-input" value="{{ $filters['date_to'] ?? '' }}">
        </div>
        <div style="display:flex; gap:8px; padding-bottom:1px;">
            <button type="submit" class="sw-btn sw-btn-primary">
                <i data-lucide="filter" style="width:14px;height:14px;"></i>
                Filter
            </button>
            <a href="{{ route('admin.payments.index') }}" class="sw-btn sw-btn-ghost">
                <i data-lucide="rotate-ccw" style="width:14px;height:14px;"></i>
                Reset
            </a>
        </div>
    </form>
</div>

{{-- ─── Transactions Table ───────────────────────────────────────────────── --}}
<div class="sw-card">
    <x-ui.section-header title="Daftar Transaksi" icon="credit-card" badge="{{ $transactions->total() }} transaksi" />

    @if ($transactions->isEmpty())
        <x-ui.empty-state
            icon="credit-card"
            title="Tidak ada transaksi"
            description="Tidak ada transaksi yang cocok dengan filter."
            actionLabel="Reset Filter"
            actionHref="{{ route('admin.payments.index') }}"
        />
    @else
        <div class="sw-table-wrap sw-mt-4">
            <table class="sw-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Order</th>
                        <th>Merchant</th>
                        <th>Gateway</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th class="sw-text-right">Gross</th>
                        <th class="sw-text-right">Fee</th>
                        <th class="sw-text-right">Net</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $tx)
                        <tr>
                            <td class="sw-font-mono sw-text-xs" style="color:var(--ink-2);">{{ $tx->reference }}</td>
                            <td>
                                @if ($tx->order)
                                    <a href="{{ route('orders.show', $tx->order) }}" class="sw-font-mono sw-fw-700" style="color:var(--accent);">
                                        {{ $tx->order->tracking_code }}
                                    </a>
                                @else
                                    <span class="sw-muted">—</span>
                                @endif
                            </td>
                            <td class="sw-text-sm">{{ $tx->order?->laundry?->name ?? '—' }}</td>
                            <td class="sw-text-xs sw-fw-600" style="text-transform:uppercase;">{{ $tx->gateway_name }}</td>
                            <td class="sw-text-xs sw-fw-600" style="text-transform:uppercase;">{{ $tx->payment_method }}</td>
                            <td><x-ui.status-badge :status="$tx->status" size="sm" /></td>
                            <td class="sw-text-right sw-fw-600 sw-text-sm">Rp{{ number_format($tx->gross_amount, 0, ',', '.') }}</td>
                            <td class="sw-text-right sw-text-xs sw-muted">Rp{{ number_format($tx->gateway_fee, 0, ',', '.') }}</td>
                            <td class="sw-text-right sw-fw-700 sw-text-sm" style="color:var(--success);">Rp{{ number_format($tx->net_amount, 0, ',', '.') }}</td>
                            <td class="sw-muted sw-text-xs">{{ $tx->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($transactions->hasPages())
            <div class="sw-flex sw-justify-center" style="padding:16px 0 4px;">
                {{ $transactions->links() }}
            </div>
        @endif
    @endif
</div>

</x-layouts.sidebar>

<x-layouts.sidebar title="Pelanggan Merchant" heading="Pelanggan Merchant">

<div class="sw-kpi-grid sw-mb-20" style="grid-template-columns:repeat(2,1fr);">
    <x-ui.kpi-card icon="users"       label="Total Pelanggan"    value="{{ number_format($totalCustomers) }}"   color="accent" />
    <x-ui.kpi-card icon="user-check"  label="Pernah Order"       value="{{ number_format($customers->total()) }}" color="green" />
</div>

<div class="sw-card sw-mb-20">
    <form method="GET" action="{{ route('merchant.customers.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div class="sw-form-group" style="flex:1;min-width:200px;">
            <label class="sw-label">Cari Pelanggan</label>
            <div style="position:relative;">
                <i data-lucide="search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--ink-3);"></i>
                <input type="text" name="q" class="sw-input" style="padding-left:32px;" placeholder="Nama, email, atau HP..." value="{{ request('q') }}">
            </div>
        </div>
        <div style="display:flex;gap:6px;padding-bottom:1px;">
            <button type="submit" class="sw-btn sw-btn-primary"><i data-lucide="filter" style="width:14px;height:14px;"></i> Filter</button>
            <a href="{{ route('merchant.customers.index') }}" class="sw-btn sw-btn-ghost">Reset</a>
        </div>
    </form>
</div>

<div class="sw-card">
    <x-ui.section-header title="Daftar Pelanggan" icon="users" badge="{{ $customers->total() }} pelanggan" />

    @if ($customers->isEmpty())
        <x-ui.empty-state icon="users" title="Belum ada pelanggan" description="Pelanggan yang pernah order akan muncul di sini." />
    @else
        <div class="sw-table-wrap">
            <table class="sw-table">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Kontak</th>
                        <th class="sw-text-right">Total Order</th>
                        <th class="sw-text-right">Total Belanja</th>
                        <th>Bergabung</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $customer)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#bfdbfe);display:flex;align-items:center;justify-content:center;font-weight:700;color:#1d4ed8;font-size:14px;flex-shrink:0;">
                                        {{ strtoupper(mb_substr($customer->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="sw-fw-600 sw-text-sm">{{ $customer->name }}</div>
                                        @if ($customer->city)
                                            <div class="sw-text-xs sw-muted">{{ $customer->city }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="sw-text-sm">{{ $customer->email }}</div>
                                @if ($customer->phone)
                                    <div class="sw-text-xs sw-muted">{{ $customer->phone }}</div>
                                @endif
                            </td>
                            <td class="sw-text-right sw-fw-700">{{ number_format($customer->order_count) }}</td>
                            <td class="sw-text-right sw-fw-600 sw-text-sm" style="color:var(--success);">
                                Rp{{ number_format((float) $customer->total_spent, 0, ',', '.') }}
                            </td>
                            <td class="sw-text-xs sw-muted">{{ $customer->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($customers->hasPages())
            <div class="sw-flex sw-justify-center" style="padding:16px 0 4px;">
                {{ $customers->links() }}
            </div>
        @endif
    @endif
</div>

</x-layouts.sidebar>

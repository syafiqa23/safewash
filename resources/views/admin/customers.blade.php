<x-layouts.sidebar title="Customer Management" heading="Customer Management">

<div class="sw-kpi-grid sw-mb-20" style="grid-template-columns:repeat(3,1fr);">
    <x-ui.kpi-card icon="users"       label="Total Customer"       value="{{ number_format($summary['total']) }}"           color="accent" />
    <x-ui.kpi-card icon="shopping-bag" label="Customer Aktif"       value="{{ number_format($summary['with_orders']) }}"     color="green" />
    <x-ui.kpi-card icon="user-plus"   label="Baru Bulan Ini"       value="{{ number_format($summary['new_this_month']) }}"  color="amber" />
</div>

<div class="sw-card sw-mb-20">
    <form method="GET" action="{{ route('admin.customers.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div class="sw-form-group" style="flex:2;min-width:180px;">
            <label class="sw-label">Cari Customer</label>
            <div style="position:relative;">
                <i data-lucide="search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--ink-3);"></i>
                <input type="text" name="q" class="sw-input" style="padding-left:32px;" placeholder="Nama, email, HP, kota..." value="{{ request('q') }}">
            </div>
        </div>
        <div class="sw-form-group" style="flex:1;min-width:140px;">
            <label class="sw-label">Kota</label>
            <select name="city" class="sw-input">
                <option value="">Semua Kota</option>
                @foreach ($cities as $city)
                    <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex;gap:6px;padding-bottom:1px;">
            <button type="submit" class="sw-btn sw-btn-primary"><i data-lucide="filter" style="width:14px;height:14px;"></i> Filter</button>
            <a href="{{ route('admin.customers.index') }}" class="sw-btn sw-btn-ghost">Reset</a>
        </div>
    </form>
</div>

<div class="sw-card">
    <x-ui.section-header title="Daftar Customer" icon="users" badge="{{ $customers->total() }} customer" />

    @if ($customers->isEmpty())
        <x-ui.empty-state icon="users" title="Belum ada customer" description="Customer yang telah mendaftar akan muncul di sini." />
    @else
        <div class="sw-table-wrap">
            <table class="sw-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Kontak</th>
                        <th>Kota</th>
                        <th class="sw-text-right">Order</th>
                        <th class="sw-text-right">Total Belanja</th>
                        <th>Bergabung</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $c)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#bfdbfe);display:flex;align-items:center;justify-content:center;font-weight:700;color:#1d4ed8;font-size:14px;flex-shrink:0;">
                                        {{ strtoupper(mb_substr($c->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="sw-fw-600 sw-text-sm">{{ $c->name }}</div>
                                        <div class="sw-text-xs sw-muted">{{ $c->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="sw-text-sm">{{ $c->phone ?: '—' }}</td>
                            <td class="sw-text-sm">{{ $c->city ?: '—' }}</td>
                            <td class="sw-text-right sw-fw-700">{{ number_format($c->order_count) }}</td>
                            <td class="sw-text-right sw-fw-600 sw-text-sm" style="color:var(--success);">
                                Rp{{ number_format((float) $c->total_spent, 0, ',', '.') }}
                            </td>
                            <td class="sw-text-xs sw-muted">{{ $c->created_at->format('d M Y') }}</td>
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

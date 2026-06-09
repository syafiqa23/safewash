<x-layouts.sidebar :title="'Manajemen Merchant'" :heading="'Manajemen Merchant'">

{{-- ─── Summary KPIs + Filter ──────────────────────────────────────────────── --}}
<div class="sw-kpi-grid sw-mb-20" style="grid-template-columns:repeat(3,minmax(0,1fr));">
    <x-ui.kpi-card icon="store"         label="Total Merchant (Filter)"  value="{{ $summary['total'] }}"    color="accent" />
    <x-ui.kpi-card icon="check-circle"  label="Merchant Aktif"           value="{{ $summary['active'] }}"   color="green" />
    <x-ui.kpi-card icon="minus-circle"  label="Merchant Nonaktif"        value="{{ $summary['inactive'] }}" color="amber" />
</div>

<div class="sw-card sw-mb-20">
    <form method="GET" action="{{ route('admin.merchants.index') }}" style="display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap;">
        <div class="sw-form-group" style="flex:2; min-width:200px;">
            <label class="sw-label">
                <i data-lucide="search" style="width:12px;height:12px;"></i>
                Cari Merchant
            </label>
            <input type="text" name="q" class="sw-input" value="{{ $filters['q'] ?? '' }}" placeholder="Nama outlet, owner, email, alamat">
        </div>
        <div class="sw-form-group" style="flex:1; min-width:130px;">
            <label class="sw-label">Status</label>
            <select name="status" class="sw-input">
                <option value="all"      {{ ($filters['status'] ?? 'all') === 'all'      ? 'selected' : '' }}>Semua Status</option>
                <option value="active"   {{ ($filters['status'] ?? '') === 'active'      ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive'    ? 'selected' : '' }}>Nonaktif</option>
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
        <div style="display:flex; gap:8px; padding-bottom:1px; flex-wrap:wrap;">
            <button type="submit" class="sw-btn sw-btn-primary">
                <i data-lucide="filter" style="width:14px;height:14px;"></i>
                Filter
            </button>
            <a href="{{ route('admin.merchants.index') }}" class="sw-btn sw-btn-ghost">
                <i data-lucide="rotate-ccw" style="width:14px;height:14px;"></i>
                Reset
            </a>
            <a href="{{ route('admin.reports.excel', request()->query()) }}" class="sw-btn sw-btn-ghost" style="color:var(--success);">
                <i data-lucide="table-2" style="width:14px;height:14px;"></i>
                Excel
            </a>
            <a href="{{ route('admin.reports.pdf', request()->query()) }}" class="sw-btn sw-btn-ghost" style="color:var(--danger);">
                <i data-lucide="file-text" style="width:14px;height:14px;"></i>
                PDF
            </a>
        </div>
    </form>
</div>

{{-- ─── Merchant list ───────────────────────────────────────────────────────── --}}
@forelse ($merchants as $row)
    <div class="sw-card sw-mb-16">

        {{-- Header --}}
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:16px; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:14px;">
                <div class="sw-icon-box">
                    <i data-lucide="store" style="width:22px;height:22px;color:var(--accent);"></i>
                </div>
                <div>
                    <div style="font-size:16px; font-weight:800; color:var(--ink);">{{ $row['laundry']->name }}</div>
                    <div class="sw-muted" style="font-size:12px;">{{ $row['owner']?->name }} · {{ $row['owner']?->email }} · {{ $row['laundry']->phone ?: 'No phone' }}</div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <span class="sw-badge sw-badge-gray" style="font-size:11px;">{{ $row['laundry']->subscription_plan }}</span>
                <x-ui.status-badge
                    :status="$row['laundry']->is_active ? 'active' : 'inactive'"
                    data-status-label
                />
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; padding:6px 10px; background:var(--surface-2); border:1px solid var(--line); border-radius:var(--r-md);">
                    <input
                        type="checkbox"
                        data-toggle-merchant
                        data-url="{{ route('admin.merchants.toggle-status', $row['laundry']) }}"
                        {{ $row['laundry']->is_active ? 'checked' : '' }}
                        style="accent-color:var(--accent); width:14px; height:14px;"
                    >
                    <span style="font-size:12px; font-weight:600; color:var(--ink-2);">Aktif</span>
                </label>
            </div>
        </div>

        {{-- KPI metrics --}}
        <div class="sw-grid sw-grid-4 sw-gap-10 sw-mb-16">
            @foreach ([
                ['label' => 'Order',          'value' => $row['orders_count'],                                    'icon' => 'shopping-bag',  'color' => 'var(--accent)'],
                ['label' => 'Omzet Paid',     'value' => 'Rp'.number_format($row['paid_revenue'],0,',','.'),      'icon' => 'banknote',      'color' => 'var(--success)'],
                ['label' => 'Bagian Admin',   'value' => 'Rp'.number_format($row['admin_revenue'],0,',','.'),     'icon' => 'percent',       'color' => 'var(--warning)'],
                ['label' => 'Klaim Terbuka',  'value' => $row['open_claims'],                                     'icon' => 'alert-triangle','color' => 'var(--danger)'],
                ['label' => 'Skor Merchant',  'value' => number_format($row['merchant_score'],1).'/100',          'icon' => 'gauge',         'color' => 'var(--accent)'],
                ['label' => 'Pembayaran Digital','value'=> $row['digital_payment_orders'],                        'icon' => 'smartphone',    'color' => 'var(--accent)'],
                ['label' => 'Pickup-Delivery','value' => $row['delivery_orders'],                                 'icon' => 'truck',         'color' => 'var(--accent)'],
                ['label' => 'Loyalty Points', 'value' => number_format($row['loyalty_points_issued']).' pts',     'icon' => 'star',          'color' => 'var(--warning)'],
            ] as $m)
                <div class="sw-mini-kpi">
                    <div class="sw-mini-kpi-lbl">
                        <i data-lucide="{{ $m['icon'] }}" style="width:11px;height:11px;color:{{ $m['color'] }};"></i>
                        <span>{{ $m['label'] }}</span>
                    </div>
                    <div class="sw-mini-kpi-val">{{ $m['value'] }}</div>
                </div>
            @endforeach
        </div>

        {{-- Edit form (collapsible) --}}
        <details>
            <summary style="cursor:pointer; font-size:13px; font-weight:600; color:var(--accent); padding:8px 0; list-style:none; display:flex; align-items:center; gap:6px;">
                <i data-lucide="edit-3" style="width:14px;height:14px;"></i>
                Edit Data Merchant
                <i data-lucide="chevron-down" style="width:13px;height:13px; margin-left:auto;"></i>
            </summary>

            <form method="POST" action="{{ route('admin.merchants.update', $row['laundry']) }}" class="sw-form" style="padding-top:16px; border-top:1px solid var(--line); margin-top:8px;">
                @csrf

                <div class="sw-form-row">
                    <div class="sw-form-group">
                        <label class="sw-label">Nama Merchant</label>
                        <input type="text" name="name" class="sw-input" value="{{ $row['laundry']->name }}" required>
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">No. Telepon</label>
                        <input type="text" name="phone" class="sw-input" value="{{ $row['laundry']->phone }}">
                    </div>
                </div>
                <div class="sw-form-row">
                    <div class="sw-form-group">
                        <label class="sw-label">Alamat</label>
                        <textarea name="address" class="sw-input" rows="2">{{ $row['laundry']->address }}</textarea>
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Deskripsi</label>
                        <textarea name="description" class="sw-input" rows="2">{{ $row['laundry']->description }}</textarea>
                    </div>
                </div>
                <div class="sw-grid sw-grid-3 sw-gap-14">
                    <div class="sw-form-group">
                        <label class="sw-label">Plan Langganan</label>
                        <input type="text" name="subscription_plan" class="sw-input" value="{{ $row['laundry']->subscription_plan }}" required>
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Komisi Admin (%)</label>
                        <input type="number" step="0.1" name="commission_rate" class="sw-input" value="{{ $row['laundry']->commission_rate }}" required>
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Service Fee (%)</label>
                        <input type="number" step="0.1" name="service_fee_rate" class="sw-input" value="{{ $row['laundry']->service_fee_rate }}" required>
                    </div>
                </div>
                <div class="sw-form-row">
                    <div class="sw-form-group">
                        <label class="sw-label">Nama Network</label>
                        <input type="text" name="network_name" class="sw-input" value="{{ $row['laundry']->network_name }}">
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Brand White-Label</label>
                        <input type="text" name="brand_name" class="sw-input" value="{{ $row['laundry']->brand_name }}">
                    </div>
                </div>
                <div class="sw-grid sw-grid-3 sw-gap-14">
                    <div class="sw-form-group">
                        <label class="sw-label">Primary Color</label>
                        <input type="text" name="brand_primary_color" class="sw-input" value="{{ $row['laundry']->brand_primary_color }}">
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Secondary Color</label>
                        <input type="text" name="brand_secondary_color" class="sw-input" value="{{ $row['laundry']->brand_secondary_color }}">
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Custom Domain</label>
                        <input type="text" name="custom_domain" class="sw-input" value="{{ $row['laundry']->custom_domain }}">
                    </div>
                </div>
                <div class="sw-flex sw-flex-col sw-gap-8">
                    <label class="sw-check-label">
                        <input type="checkbox" name="is_active" value="1" {{ $row['laundry']->is_active ? 'checked' : '' }}>
                        <span>Merchant sedang aktif di platform</span>
                    </label>
                    <label class="sw-check-label">
                        <input type="checkbox" name="premium_protection_enabled" value="1" {{ $row['laundry']->premium_protection_enabled ? 'checked' : '' }}>
                        <span>Aktifkan proteksi premium untuk merchant ini</span>
                    </label>
                    <label class="sw-check-label">
                        <input type="checkbox" name="supports_white_label" value="1" {{ $row['laundry']->supports_white_label ? 'checked' : '' }}>
                        <span>Merchant ini menggunakan solusi white-label SafeWash</span>
                    </label>
                </div>
                <button type="submit" class="sw-btn sw-btn-primary sw-w-full">
                    <i data-lucide="save" style="width:14px;height:14px;"></i>
                    Simpan Perubahan Merchant
                </button>
            </form>
        </details>
    </div>
@empty
    <x-ui.empty-state
        icon="store"
        title="Tidak ada merchant ditemukan"
        description="Coba ubah kata kunci pencarian, status, atau rentang tanggal filter."
        actionLabel="Reset Filter"
        actionHref="{{ route('admin.merchants.index') }}"
    />
@endforelse

@if ($merchants->hasPages())
    <div class="sw-flex sw-justify-center" style="padding:8px 0;">
        {{ $merchants->links() }}
    </div>
@endif

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.querySelectorAll('[data-toggle-merchant]').forEach((toggle) => {
        toggle.addEventListener('change', async (event) => {
            const input = event.currentTarget;
            const card = input.closest('.sw-card');
            const statusBadge = card.querySelector('[data-status-label]');
            const previousState = !input.checked;

            try {
                const response = await fetch(input.dataset.url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ is_active: input.checked }),
                });

                if (!response.ok) throw new Error('Toggle status gagal');

                const result = await response.json();

                if (statusBadge) {
                    const isActive = result.is_active;
                    statusBadge.style.background = isActive ? 'var(--success-bg, #d1fae5)' : 'var(--surface-2)';
                    statusBadge.style.color       = isActive ? 'var(--success-fg, #065f46)' : 'var(--muted)';
                    const txt = statusBadge.childNodes[statusBadge.childNodes.length - 1];
                    if (txt) txt.textContent = ' ' + (isActive ? 'Active' : 'Inactive');
                }

                window.showToast({
                    title:   'Status merchant diperbarui',
                    message: `Merchant sekarang ${result.is_active ? 'aktif' : 'nonaktif'}.`,
                    type:    'success',
                });
            } catch (error) {
                input.checked = previousState;
                window.showToast({
                    title:   'Gagal memperbarui status',
                    message: 'Coba lagi beberapa saat lagi.',
                    type:    'error',
                });
            }
        });
    });
</script>

</x-layouts.sidebar>

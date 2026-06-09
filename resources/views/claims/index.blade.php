<x-layouts.sidebar
    :title="$user->isAdmin() ? 'Semua Klaim' : ($user->isMerchant() ? 'Klaim Merchant' : 'Klaim Saya')"
    :heading="$user->isAdmin() ? 'Klaim Platform' : ($user->isMerchant() ? 'Manajemen Klaim' : 'Klaim Saya')"
>

<div class="sw-card">
    <x-ui.section-header
        title="{{ $user->isAdmin() ? 'Semua Klaim Platform' : ($user->isMerchant() ? 'Klaim pada Order Merchant' : 'Riwayat Klaim Saya') }}"
        icon="alert-triangle"
        badge="{{ $claims->total() }} klaim"
    >
        @if ($user->isAdmin())
            <a href="{{ route('admin.claims.index') }}" class="sw-btn sw-btn-primary sw-btn-sm">
                <i data-lucide="shield-check" style="width:13px;height:13px;"></i>
                Kelola di Admin
            </a>
        @endif
    </x-ui.section-header>

    @if ($claims->isEmpty())
        <x-ui.empty-state
            icon="check-circle"
            title="Tidak ada klaim"
            description="{{ $user->isCustomer() ? 'Belum ada klaim yang pernah diajukan.' : 'Tidak ada klaim aktif pada order Anda.' }}"
        />
    @else
        <div class="sw-table-wrap sw-mt-4">
            <table class="sw-table">
                <thead>
                    <tr>
                        <th>No. Order</th>
                        @if (! $user->isCustomer())
                            <th>Customer</th>
                        @endif
                        <th>Item</th>
                        <th>Merchant</th>
                        <th>Status</th>
                        <th>Kompensasi</th>
                        <th>Tanggal</th>
                        @if ($user->isCustomer())
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($claims as $claim)
                        <tr>
                            <td>
                                @if ($claim->order)
                                    <a href="{{ route('orders.show', $claim->order) }}" class="sw-font-mono sw-fw-700" style="color:var(--accent); letter-spacing:.5px;">
                                        {{ $claim->order->tracking_code }}
                                    </a>
                                    <div class="sw-muted sw-text-xs">{{ $claim->order->service_type }}</div>
                                @else
                                    <span class="sw-muted">—</span>
                                @endif
                            </td>
                            @if (! $user->isCustomer())
                                <td>
                                    <div class="sw-fw-600 sw-text-sm">{{ $claim->claimant_name }}</div>
                                    <div class="sw-muted sw-text-xs">{{ $claim->claimant_contact }}</div>
                                </td>
                            @endif
                            <td>
                                <div class="sw-fw-600 sw-text-sm">{{ $claim->item_name }}</div>
                                <div class="sw-muted sw-text-xs">{{ Str::limit($claim->description, 40) }}</div>
                            </td>
                            <td class="sw-text-sm">{{ $claim->order?->laundry?->name ?? '—' }}</td>
                            <td><x-ui.status-badge :status="$claim->status" /></td>
                            <td class="sw-fw-600 sw-text-sm">
                                @if ($claim->compensation_amount > 0)
                                    Rp{{ number_format($claim->compensation_amount, 0, ',', '.') }}
                                @else
                                    <span class="sw-muted">—</span>
                                @endif
                            </td>
                            <td class="sw-muted sw-text-xs">{{ $claim->submitted_at?->format('d M Y') ?? $claim->created_at->format('d M Y') }}</td>
                            @if ($user->isCustomer())
                                <td>
                                    @if ($claim->order)
                                        <a href="{{ route('orders.show', $claim->order) }}" class="sw-btn sw-btn-ghost sw-btn-sm">
                                            <i data-lucide="eye" style="width:12px;height:12px;"></i>
                                            Detail
                                        </a>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($claims->hasPages())
            <div class="sw-flex sw-justify-center" style="padding:16px 0 4px;">
                {{ $claims->links() }}
            </div>
        @endif
    @endif
</div>

</x-layouts.sidebar>

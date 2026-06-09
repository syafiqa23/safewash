@php
    $pageTitle = $user->isAdmin() ? 'Semua Order' : ($user->isMerchant() ? 'Manajemen Order' : 'Order Saya');
    if ($activeFilter)   $pageTitle = 'Order Aktif';
    if ($deliveryFilter) $pageTitle = 'Pickup-Delivery';
@endphp
<x-layouts.sidebar :title="$pageTitle" :heading="$pageTitle">

<div class="sw-card">
    <x-ui.section-header
        title="{{ $user->isAdmin() ? 'Semua Order Platform' : ($user->isMerchant() ? 'Manajemen Order Merchant' : 'Order Laundry Saya') }}"
        icon="list-ordered"
        badge="{{ $orders->total() }} order"
    >
        @if ($user->isMerchant())
            <a href="{{ route('dashboard') }}" class="sw-btn sw-btn-primary sw-btn-sm">
                <i data-lucide="plus" style="width:13px;height:13px;"></i>
                Buat Order
            </a>
        @endif
    </x-ui.section-header>

    @if ($orders->isEmpty())
        <x-ui.empty-state
            icon="inbox"
            title="Belum ada order"
            description="{{ $user->isMerchant() ? 'Buat order pertama dari dashboard.' : 'Order akan muncul setelah dibuat.' }}"
        />
    @else
        <div class="sw-table-wrap sw-mt-4">
            <table class="sw-table">
                <thead>
                    <tr>
                        <th>Tracking</th>
                        <th>Customer</th>
                        <th>Merchant</th>
                        <th>Status</th>
                        <th>Pembayaran</th>
                        <th>Delivery</th>
                        <th>Proteksi</th>
                        <th class="sw-text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr onclick="window.location='{{ route('orders.show', $order) }}'" class="sw-cursor-pointer">
                            <td>
                                <a href="{{ route('orders.show', $order) }}" onclick="event.stopPropagation()" class="sw-font-mono sw-fw-700" style="letter-spacing:.5px; color:var(--accent);">
                                    {{ $order->tracking_code }}
                                </a>
                                @if ($user->role === 'customer' && ($order->payment_status ?? 'pending') === 'pending')
                                    <div style="margin-top:4px;">
                                        <a href="{{ route('customer.order.checkout', $order) }}" onclick="event.stopPropagation()" class="sw-btn sw-btn-primary" style="padding:4px 10px;font-size:11px;font-weight:700;border-radius:6px;display:inline-flex;align-items:center;gap:4px;">
                                            <i data-lucide="credit-card" style="width:10px;height:10px;"></i>
                                            Bayar Sekarang
                                        </a>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="sw-fw-600 sw-text-sm">{{ $order->customer_name }}</div>
                                @if ($order->customer_phone)
                                    <div class="sw-muted sw-text-xs">{{ $order->customer_phone }}</div>
                                @endif
                            </td>
                            <td class="sw-text-sm">{{ $order->laundry->name }}</td>
                            <td><x-ui.status-badge :status="$order->status" /></td>
                            <td>
                                <div class="sw-fw-600 sw-text-xs" style="text-transform:uppercase; letter-spacing:.5px;">{{ $order->payment_method }}</div>
                                <x-ui.status-badge :status="$order->payment_status ?? 'pending'" size="sm" />
                            </td>
                            <td>
                                @if ($order->pickup_delivery_opt_in)
                                    <span class="sw-badge sw-badge-blue sw-text-xs">
                                        <i data-lucide="truck" style="width:10px;height:10px;"></i>
                                        Aktif
                                    </span>
                                @else
                                    <span class="sw-muted sw-text-sm">Outlet only</span>
                                @endif
                            </td>
                            <td>
                                @if ($order->is_premium_protected)
                                    <span class="sw-badge sw-badge-purple sw-text-xs">
                                        <i data-lucide="shield-check" style="width:10px;height:10px;"></i>
                                        Premium
                                    </span>
                                @else
                                    <span class="sw-muted sw-text-sm">Standar</span>
                                @endif
                            </td>
                            <td class="sw-text-right sw-fw-700 sw-text-sm">
                                Rp{{ number_format($order->total_price, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="sw-flex sw-justify-center" style="padding:16px 0 4px;">
                {{ $orders->links() }}
            </div>
        @endif
    @endif
</div>

</x-layouts.sidebar>

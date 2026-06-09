<x-layouts.sidebar title="QR Tracking Management" heading="QR Tracking Management">

<div class="sw-card sw-mb-20" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border:1px solid #bfdbfe;">
    <div style="display:flex;align-items:center;gap:12px;">
        <div style="width:44px;height:44px;background:#2563eb;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="qr-code" style="width:22px;height:22px;color:white;"></i>
        </div>
        <div>
            <div style="font-size:15px;font-weight:700;color:#1e3a5f;">Setiap order memiliki QR unik</div>
            <div style="font-size:13px;color:#3b82f6;">Customer dapat melacak status laundry real-time via QR scan</div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="sw-card sw-mb-20">
    <form method="GET" action="{{ route('merchant.qr.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div class="sw-form-group" style="flex:2;min-width:180px;">
            <label class="sw-label">Cari Order / Customer</label>
            <div style="position:relative;">
                <i data-lucide="search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--ink-3);"></i>
                <input type="text" name="q" class="sw-input" style="padding-left:32px;" placeholder="Tracking code atau nama..." value="{{ request('q') }}">
            </div>
        </div>
        <div class="sw-form-group" style="flex:1;min-width:140px;">
            <label class="sw-label">Status</label>
            <select name="status" class="sw-input">
                <option value="">Semua Status</option>
                @foreach (\App\Models\LaundryOrder::STATUS_LABELS as $val => $lbl)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex;gap:6px;padding-bottom:1px;">
            <button type="submit" class="sw-btn sw-btn-primary"><i data-lucide="filter" style="width:14px;height:14px;"></i> Filter</button>
            <a href="{{ route('merchant.qr.index') }}" class="sw-btn sw-btn-ghost">Reset</a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="sw-card">
    <x-ui.section-header title="Daftar QR Order" icon="qr-code" badge="{{ $orders->total() }} order" />

    @if ($orders->isEmpty())
        <x-ui.empty-state icon="qr-code" title="Belum ada order" description="Order akan muncul di sini setelah dibuat." />
    @else
        <div class="sw-table-wrap">
            <table class="sw-table">
                <thead>
                    <tr>
                        <th>QR / Tracking</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Update Terakhir</th>
                        <th>Link Tracking</th>
                        <th class="sw-text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>
                                <div class="sw-font-mono sw-fw-700" style="color:var(--accent);letter-spacing:.5px;">{{ $order->tracking_code }}</div>
                                <div class="sw-text-xs sw-muted">{{ $order->laundry->name }}</div>
                            </td>
                            <td>
                                <div class="sw-fw-600 sw-text-sm">{{ $order->customer_name }}</div>
                                @if ($order->customer_phone)
                                    <div class="sw-text-xs sw-muted">{{ $order->customer_phone }}</div>
                                @endif
                            </td>
                            <td><x-ui.status-badge :status="$order->status" /></td>
                            <td class="sw-text-xs sw-muted">
                                @if ($order->trackingUpdates->first())
                                    {{ $order->trackingUpdates->first()->created_at->format('d M H:i') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <div class="sw-code" style="font-size:11px;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ url('/track/'.$order->tracking_code) }}
                                </div>
                            </td>
                            <td class="sw-text-right">
                                <div style="display:flex;gap:6px;justify-content:flex-end;">
                                    <a href="{{ route('tracking.show', $order->tracking_code) }}" target="_blank" class="sw-btn sw-btn-ghost sw-btn-sm" title="Lihat Tracking">
                                        <i data-lucide="external-link" style="width:13px;height:13px;"></i>
                                    </a>
                                    <a href="{{ route('orders.show', $order) }}" class="sw-btn sw-btn-primary sw-btn-sm">
                                        Detail
                                    </a>
                                </div>
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

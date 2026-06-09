<x-layouts.sidebar :title="'Order '.$order->tracking_code" :heading="'Detail Order'">

{{-- ─── Row 1: Order info + items ──────────────────────────────────────────── --}}
<div class="sw-body-grid">

    {{-- Order Info ────────────────────────────────────────────────────────────── --}}
    <div class="sw-card">
        <div class="sw-flex sw-justify-between sw-gap-12 sw-mb-16" style="align-items:flex-start;">
            <div>
                <div class="sw-font-mono sw-fw-700" style="font-size:18px; letter-spacing:1px; color:var(--ink);">
                    {{ $order->tracking_code }}
                </div>
                <div class="sw-muted sw-text-sm sw-mt-4">
                    {{ $order->laundry->name }} · {{ $order->service_type }}
                </div>
            </div>
            <x-ui.status-badge :status="$order->status" />
        </div>

        <div class="sw-grid sw-grid-2 sw-gap-12">
            <div class="sw-detail-box">
                <div class="sw-detail-lbl">Customer</div>
                <div class="sw-detail-val">{{ $order->customer_name }}</div>
                <div class="sw-text-sm sw-mt-4" style="color:var(--ink-2);">{{ $order->customer_phone }}</div>
                @if ($order->customer_email)
                    <div class="sw-muted sw-text-xs sw-mt-4">{{ $order->customer_email }}</div>
                @endif
            </div>
            <div class="sw-detail-box">
                <div class="sw-detail-lbl">Total & Estimasi</div>
                <div class="sw-fw-700" style="font-size:18px; color:var(--ink);">Rp{{ number_format($order->total_price, 0, ',', '.') }}</div>
                <div class="sw-muted sw-text-xs sw-mt-4">
                    {{ optional($order->promised_at)->format('d M Y H:i') ?: 'Estimasi belum diset' }}
                </div>
            </div>
        </div>

        <div class="sw-grid sw-grid-2 sw-gap-12 sw-mt-12">
            <div class="sw-detail-box">
                <div class="sw-detail-lbl">Pembayaran</div>
                <div class="sw-fw-700 sw-text-sm" style="text-transform:uppercase; letter-spacing:.5px;">{{ $order->payment_method }}</div>
                <div class="sw-mt-4"><x-ui.status-badge :status="$order->payment_status ?? 'pending'" size="sm" /></div>
                <div class="sw-muted sw-text-xs sw-mt-4">{{ $order->payment_reference ?: 'Reference otomatis' }}</div>
            </div>
            <div class="sw-detail-box">
                <div class="sw-detail-lbl">Loyalty Customer</div>
                <div class="sw-fw-700" style="font-size:16px; color:var(--ink);">{{ $order->loyalty_points_earned }} pts</div>
                <div class="sw-muted sw-text-xs sw-mt-4">{{ $order->customer?->loyaltyAccount?->tier ?? 'Belum ada tier' }}</div>
            </div>
        </div>

        @if ($order->notes)
            <div class="sw-note sw-mt-12">
                <div class="sw-detail-lbl">Catatan</div>
                <div class="sw-text-sm" style="color:var(--ink-2);">{{ $order->notes }}</div>
            </div>
        @endif

        <div class="sw-mt-16">
            <div class="sw-muted sw-text-xs sw-fw-600 sw-mb-12">QR Tracking</div>
            <div class="sw-flex sw-items-center sw-gap-16 sw-flex-wrap">
                <div style="border:1px solid var(--line); border-radius:var(--r-md); padding:10px; background:var(--surface); display:inline-block;">
                    {!! QrCode::size(140)->generate(route('tracking.show', $order->qr_token)) !!}
                </div>
                <div>
                    <a href="{{ route('tracking.show', $order->tracking_code) }}" target="_blank" class="sw-btn sw-btn-secondary sw-text-sm">
                        <i data-lucide="external-link" style="width:13px;height:13px;"></i>
                        Halaman Publik
                    </a>
                    <div class="sw-muted sw-text-xs sw-mt-8" style="max-width:160px; line-height:1.5;">
                        Scan QR untuk tracking real-time tanpa login
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Items + Delivery ──────────────────────────────────────────────────────── --}}
    <div class="sw-flex sw-flex-col sw-gap-16">

        <div class="sw-card">
            <x-ui.section-header
                title="Daftar Item"
                icon="package"
                badge="{{ $order->items->sum('quantity') }} item"
            />
            @forelse ($order->items as $item)
                <div class="sw-list-row">
                    <div class="sw-flex sw-items-center sw-gap-10">
                        <div class="sw-timeline-dot"><i data-lucide="shirt"></i></div>
                        <div>
                            <div class="sw-fw-600 sw-text-sm">{{ $item->item_name }} <span class="sw-muted">×{{ $item->quantity }}</span></div>
                            @if ($item->condition_notes)
                                <div class="sw-muted sw-text-xs">{{ $item->condition_notes }}</div>
                            @endif
                        </div>
                    </div>
                    @if ($item->is_priority)
                        <span class="sw-badge sw-badge-amber sw-text-xs">
                            <i data-lucide="zap" style="width:9px;height:9px;"></i>
                            Prioritas
                        </span>
                    @endif
                </div>
            @empty
                <x-ui.empty-state icon="package" title="Belum ada item" />
            @endforelse
        </div>

        @if ($order->deliveryRequest)
            <div class="sw-card">
                <x-ui.section-header title="Pickup-Delivery" icon="truck">
                    <x-ui.status-badge :status="$order->deliveryRequest->status" size="sm" />
                </x-ui.section-header>

                <div class="sw-flex sw-flex-col">
                    @foreach ([
                        ['label' => 'Partner',   'value' => $order->deliveryRequest->partner_name,    'icon' => 'building-2'],
                        ['label' => 'Layanan',   'value' => $order->deliveryRequest->service_type,    'icon' => 'truck'],
                        ['label' => 'Biaya',     'value' => 'Rp'.number_format($order->deliveryRequest->fee,0,',','.'), 'icon' => 'banknote'],
                        ['label' => 'Pickup',    'value' => $order->deliveryRequest->pickup_address ?: '-',    'icon' => 'map-pin'],
                        ['label' => 'Delivery',  'value' => $order->deliveryRequest->delivery_address ?: '-',  'icon' => 'navigation'],
                    ] as $item)
                        <div class="sw-list-row">
                            <div class="sw-flex sw-items-center sw-gap-8">
                                <i data-lucide="{{ $item['icon'] }}" style="width:13px;height:13px;color:var(--muted);"></i>
                                <span class="sw-muted sw-text-sm">{{ $item['label'] }}</span>
                            </div>
                            <span class="sw-fw-600 sw-text-sm">{{ $item['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>

{{-- ─── Row 2: Timeline + Actions ─────────────────────────────────────────── --}}
<div class="sw-body-grid sw-mt-20">

    {{-- Timeline ──────────────────────────────────────────────────────────────── --}}
    <div class="sw-card">
        <x-ui.section-header title="Timeline Tracking" icon="map-pin" />

        @if ($order->trackingUpdates->isEmpty())
            <x-ui.empty-state icon="map-pin" title="Belum ada update tracking" />
        @else
            <div class="sw-timeline sw-mt-4">
                @foreach ($order->trackingUpdates as $update)
                    <div class="sw-timeline-item">
                        <div class="sw-timeline-dot"><i data-lucide="map-pin"></i></div>
                        <div class="sw-timeline-body">
                            <div class="sw-timeline-title">{{ $update->title }}</div>
                            <div class="sw-timeline-desc">{{ $update->description }}</div>
                            <div class="sw-timeline-time">
                                <i data-lucide="clock" style="width:11px;height:11px;"></i>
                                {{ $update->created_at->format('d M Y H:i') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Actions (Merchant/Admin) or Claim form (Customer) ──────────────────── --}}
    <div class="sw-flex sw-flex-col sw-gap-16">

        @if (auth()->user()->isMerchant() || auth()->user()->isAdmin())
            {{-- Update Status --}}
            <div class="sw-card">
                <x-ui.section-header title="Update Status Order" icon="edit-3" />
                <form method="POST" action="{{ route('orders.status', $order) }}" class="sw-form sw-mt-4">
                    @csrf
                    <div class="sw-form-group">
                        <label class="sw-label">Status Baru</label>
                        <select name="status" class="sw-input">
                            @foreach (\App\Models\LaundryOrder::STATUSES as $status)
                                <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Judul Update</label>
                        <input type="text" name="title" class="sw-input" placeholder="Mis. Sedang proses setrika" required>
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Deskripsi</label>
                        <textarea name="description" class="sw-input" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="sw-btn sw-btn-primary">
                        <i data-lucide="save" style="width:14px;height:14px;"></i>
                        Simpan Update
                    </button>
                </form>
            </div>

            {{-- Payment --}}
            <div class="sw-card">
                <x-ui.section-header title="Transaksi Pembayaran" icon="credit-card">
                    <x-ui.status-badge :status="$order->payment_status ?? 'pending'" size="sm" />
                </x-ui.section-header>

                <form method="POST" action="{{ route('orders.settle-payment', $order) }}">
                    @csrf
                    <div class="sw-flex sw-flex-col">
                        @foreach ([
                            ['label' => 'Gateway',        'value' => $order->paymentTransaction?->gateway_name ?? 'SafeWash Pay Simulator'],
                            ['label' => 'Provider',       'value' => strtoupper($order->paymentTransaction?->provider ?? config('safewash.payment.provider'))],
                            ['label' => 'Metode',         'value' => strtoupper($order->payment_method)],
                            ['label' => 'Reference',      'value' => $order->paymentTransaction?->reference ?? $order->payment_reference ?? '-'],
                            ['label' => 'Gateway Status', 'value' => $order->paymentTransaction?->webhook_status ?? 'belum ada'],
                        ] as $item)
                            <div class="sw-list-row">
                                <span class="sw-muted sw-text-sm">{{ $item['label'] }}</span>
                                <span class="sw-fw-600 sw-text-sm sw-text-right">{{ $item['value'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="sw-flex sw-gap-8 sw-mt-16 sw-flex-wrap">
                        @if ($order->paymentTransaction?->checkout_url)
                            <a href="{{ $order->paymentTransaction->checkout_url }}" target="_blank" rel="noopener" class="sw-btn sw-btn-secondary sw-flex-1">
                                <i data-lucide="external-link" style="width:13px;height:13px;"></i>
                                Buka Checkout
                            </a>
                        @endif
                        @if ($order->payment_status !== 'paid')
                            <button type="submit" class="sw-btn sw-btn-primary sw-flex-1">
                                <i data-lucide="check-circle" style="width:13px;height:13px;"></i>
                                Konfirmasi Manual
                            </button>
                        @endif
                    </div>
                </form>

                <form method="POST" action="{{ route('orders.payment-link', $order) }}" class="sw-mt-12" style="padding-top:12px; border-top:1px solid var(--line);">
                    @csrf
                    <div class="sw-flex sw-items-center sw-justify-between sw-gap-10 sw-flex-wrap">
                        <span class="sw-muted sw-text-xs sw-flex-1">Generate ulang link checkout dari provider aktif</span>
                        <button type="submit" class="sw-btn sw-btn-ghost sw-btn-sm">
                            <i data-lucide="link" style="width:13px;height:13px;"></i>
                            Generate Link
                        </button>
                    </div>
                </form>
            </div>

            @if ($order->deliveryRequest)
                <div class="sw-card">
                    <x-ui.section-header title="Update Pickup-Delivery" icon="truck" badge="{{ $order->deliveryRequest->partner_name }}" />
                    <form method="POST" action="{{ route('orders.delivery', $order) }}" class="sw-form sw-mt-4">
                        @csrf
                        <div class="sw-form-group">
                            <label class="sw-label">Status Delivery</label>
                            <select name="status" class="sw-input">
                                @foreach (['scheduled', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'cancelled'] as $s)
                                    <option value="{{ $s }}" {{ $order->deliveryRequest->status === $s ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $s)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sw-form-group">
                            <label class="sw-label">Partner</label>
                            <input type="text" name="partner_name" class="sw-input" value="{{ $order->deliveryRequest->partner_name }}" required>
                        </div>
                        <div class="sw-form-group">
                            <label class="sw-label">Catatan Kurir</label>
                            <textarea name="courier_notes" class="sw-input" rows="2">{{ $order->deliveryRequest->courier_notes }}</textarea>
                        </div>
                        <button type="submit" class="sw-btn sw-btn-primary">
                            <i data-lucide="truck" style="width:14px;height:14px;"></i>
                            Simpan Delivery
                        </button>
                    </form>
                </div>
            @endif

        @else
            {{-- Customer: Claim form --}}
            <div class="sw-card">
                <x-ui.section-header title="Ajukan Klaim Kehilangan" icon="alert-triangle">
                    <x-ui.status-badge :status="$order->claim_status ?? 'open'" size="sm" />
                </x-ui.section-header>
                <form method="POST" action="{{ route('claims.store', $order) }}" class="sw-form sw-mt-4">
                    @csrf
                    <div class="sw-form-group">
                        <label class="sw-label">Nama Pelapor</label>
                        <input type="text" name="claimant_name" class="sw-input" value="{{ auth()->user()->name }}" required>
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Kontak</label>
                        <input type="text" name="claimant_contact" class="sw-input" value="{{ auth()->user()->phone }}" required>
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Nama Item</label>
                        <input type="text" name="item_name" class="sw-input" required>
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Deskripsi Masalah</label>
                        <textarea name="description" class="sw-input" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="sw-btn sw-btn-danger">
                        <i data-lucide="alert-triangle" style="width:14px;height:14px;"></i>
                        Kirim Klaim
                    </button>
                </form>
            </div>
        @endif

    </div>
</div>

{{-- ─── Row 3: Notifications + Claims ─────────────────────────────────────── --}}
<div class="sw-body-grid sw-mt-20">
    <div class="sw-card">
        <x-ui.section-header title="Log Notifikasi" icon="bell" badge="{{ $order->notifications->count() }} log" />
        @forelse ($order->notifications as $notification)
            <div class="sw-list-row">
                <div>
                    <div class="sw-fw-600 sw-text-xs" style="text-transform:uppercase; letter-spacing:.5px; color:var(--ink);">
                        {{ $notification->channel }}
                    </div>
                    <div class="sw-muted sw-text-xs">{{ Str::limit($notification->message, 60) }}</div>
                </div>
                <x-ui.status-badge :status="$notification->status" size="sm" />
            </div>
        @empty
            <x-ui.empty-state icon="bell" title="Belum ada notifikasi" />
        @endforelse
    </div>

    <div class="sw-card">
        <x-ui.section-header title="Riwayat Klaim" icon="alert-circle" badge="{{ $order->claims->count() }} klaim" />
        @forelse ($order->claims as $claim)
            <div class="sw-list-row">
                <div>
                    <div class="sw-fw-600 sw-text-sm" style="color:var(--ink);">{{ $claim->item_name }}</div>
                    <div class="sw-muted sw-text-xs">{{ Str::limit($claim->description, 50) }}</div>
                </div>
                <x-ui.status-badge :status="$claim->status" size="sm" />
            </div>
        @empty
            <x-ui.empty-state icon="check-circle" title="Tidak ada klaim" />
        @endforelse
    </div>
</div>

</x-layouts.sidebar>

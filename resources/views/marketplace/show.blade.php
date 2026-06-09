<x-layouts.sidebar :title="$laundry->name" :heading="$laundry->name">

{{-- Breadcrumb --}}
<div style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--ink-3);margin-bottom:16px;">
    <a href="{{ route('marketplace.index') }}" style="color:var(--accent);text-decoration:none;">Marketplace</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px;"></i>
    <span>{{ $laundry->name }}</span>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

    {{-- Left: Detail --}}
    <div>
        {{-- Hero Card --}}
        <div class="sw-card sw-mb-16">
            <div style="height:160px;border-radius:8px;overflow:hidden;background:linear-gradient(135deg,#dbeafe,#eff6ff);display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                @if ($laundry->photo_url)
                    <img src="{{ $laundry->photo_url }}" alt="{{ $laundry->name }}" style="width:100%;height:100%;object-fit:cover;">
                @else
                    <i data-lucide="store" style="width:48px;height:48px;color:#93c5fd;"></i>
                @endif
            </div>

            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
                <div>
                    <div style="font-size:22px;font-weight:800;color:var(--ink-1);margin-bottom:4px;">{{ $laundry->name }}</div>
                    @if ($laundry->city)
                        <div style="display:flex;align-items:center;gap:5px;color:var(--ink-2);font-size:13px;">
                            <i data-lucide="map-pin" style="width:13px;height:13px;"></i>
                            {{ $laundry->city }}{{ $laundry->address ? ' · '.$laundry->address : '' }}
                        </div>
                    @endif
                </div>
                <div style="text-align:right;">
                    <div style="display:flex;align-items:center;gap:4px;justify-content:flex-end;margin-bottom:2px;">
                        @for ($i = 1; $i <= 5; $i++)
                            <i data-lucide="star" style="width:14px;height:14px;color:{{ $i <= round((float) $laundry->rating) ? '#f59e0b' : '#e5e7eb' }};fill:{{ $i <= round((float) $laundry->rating) ? '#f59e0b' : '#e5e7eb' }};"></i>
                        @endfor
                        <span style="font-weight:700;font-size:14px;color:var(--ink-1);margin-left:4px;">{{ number_format((float) $laundry->rating, 1) }}</span>
                    </div>
                    <div style="font-size:12px;color:var(--ink-3);">{{ number_format($laundry->review_count) }} ulasan</div>
                </div>
            </div>

            {{-- Badges --}}
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;">
                @if ($laundry->pickup_available)
                    <span style="display:flex;align-items:center;gap:5px;background:#dbeafe;color:#1d4ed8;font-size:12px;font-weight:600;padding:4px 10px;border-radius:20px;">
                        <i data-lucide="package" style="width:12px;height:12px;"></i> Pickup Tersedia
                    </span>
                @endif
                @if ($laundry->delivery_available)
                    <span style="display:flex;align-items:center;gap:5px;background:#d1fae5;color:#065f46;font-size:12px;font-weight:600;padding:4px 10px;border-radius:20px;">
                        <i data-lucide="truck" style="width:12px;height:12px;"></i> Delivery Tersedia
                    </span>
                @endif
                @if ($laundry->premium_protection_enabled)
                    <span style="display:flex;align-items:center;gap:5px;background:#ede9fe;color:#5b21b6;font-size:12px;font-weight:600;padding:4px 10px;border-radius:20px;">
                        <i data-lucide="shield-check" style="width:12px;height:12px;"></i> Proteksi Premium
                    </span>
                @endif
                @if ($laundry->is_active)
                    <span style="display:flex;align-items:center;gap:5px;background:#d1fae5;color:#065f46;font-size:12px;font-weight:600;padding:4px 10px;border-radius:20px;">
                        <i data-lucide="check-circle" style="width:12px;height:12px;"></i> Aktif
                    </span>
                @endif
            </div>
        </div>

        {{-- Info --}}
        <div class="sw-card sw-mb-16">
            <x-ui.section-header title="Tentang Merchant" icon="info" />
            @if ($laundry->description)
                <p style="color:var(--ink-2);font-size:14px;line-height:1.6;margin-bottom:12px;">{{ $laundry->description }}</p>
            @endif
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                @if ($laundry->phone)
                    <div style="font-size:13px;color:var(--ink-2);">
                        <div style="font-weight:600;color:var(--ink-1);margin-bottom:2px;">Telepon</div>
                        {{ $laundry->phone }}
                    </div>
                @endif
                @if ($laundry->operating_hours)
                    <div style="font-size:13px;color:var(--ink-2);">
                        <div style="font-weight:600;color:var(--ink-1);margin-bottom:2px;">Jam Operasional</div>
                        {{ $laundry->operating_hours }}
                    </div>
                @endif
                <div style="font-size:13px;color:var(--ink-2);">
                    <div style="font-weight:600;color:var(--ink-1);margin-bottom:2px;">Total Order Selesai</div>
                    {{ number_format($completedCount) }} order
                </div>
                <div style="font-size:13px;color:var(--ink-2);">
                    <div style="font-weight:600;color:var(--ink-1);margin-bottom:2px;">Merchant Score</div>
                    {{ number_format((float) $laundry->merchant_score, 1) }} / 100
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="sw-kpi-grid sw-mb-16" style="grid-template-columns:repeat(3,1fr);">
            <x-ui.kpi-card icon="package" label="Total Order" value="{{ number_format($totalOrders) }}" color="accent" />
            <x-ui.kpi-card icon="check-circle" label="Selesai" value="{{ number_format($completedCount) }}" color="green" />
            <x-ui.kpi-card icon="star" label="Rating" value="{{ number_format((float) $laundry->rating, 1) }}" color="amber" />
        </div>
    </div>

    {{-- Right: CTA --}}
    <div style="position:sticky;top:80px;">
        <div class="sw-card" style="border:2px solid var(--accent);">
            <div style="font-size:16px;font-weight:700;color:var(--ink-1);margin-bottom:4px;">Buat Order di Sini</div>
            <div style="font-size:13px;color:var(--ink-3);margin-bottom:16px;">QR Tracking aktif otomatis setelah order dibuat</div>

            <div style="border:1px solid var(--surface-3);border-radius:8px;padding:12px;margin-bottom:16px;background:var(--surface-2);">
                <div style="font-size:12px;font-weight:600;color:var(--ink-2);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Layanan</div>
                <div style="font-size:13px;color:var(--ink-2);display:flex;flex-direction:column;gap:4px;">
                    <div style="display:flex;align-items:center;gap:6px;"><i data-lucide="check" style="width:13px;height:13px;color:var(--success);"></i> Tracking QR Real-time</div>
                    @if ($laundry->pickup_available)
                        <div style="display:flex;align-items:center;gap:6px;"><i data-lucide="check" style="width:13px;height:13px;color:var(--success);"></i> Layanan Pickup</div>
                    @endif
                    @if ($laundry->delivery_available)
                        <div style="display:flex;align-items:center;gap:6px;"><i data-lucide="check" style="width:13px;height:13px;color:var(--success);"></i> Layanan Delivery</div>
                    @endif
                    @if ($laundry->premium_protection_enabled)
                        <div style="display:flex;align-items:center;gap:6px;"><i data-lucide="check" style="width:13px;height:13px;color:var(--success);"></i> Proteksi Barang Premium</div>
                    @endif
                    <div style="display:flex;align-items:center;gap:6px;"><i data-lucide="check" style="width:13px;height:13px;color:var(--success);"></i> Notifikasi WhatsApp</div>
                </div>
            </div>

            {{-- CTA varies by role --}}
            @if (auth()->user()->isMerchant() && auth()->user()->laundries()->whereKey($laundry->id)->exists())
                <a href="{{ route('orders.create') }}" class="sw-btn sw-btn-primary sw-w-full" style="justify-content:center;">
                    <i data-lucide="package-plus" style="width:15px;height:15px;"></i>
                    Buat Order Baru
                </a>
            @elseif (auth()->user()->role === 'customer')
                <a href="{{ route('customer.order.create', $laundry->slug) }}" class="sw-btn sw-btn-primary sw-w-full" style="justify-content:center;background:linear-gradient(135deg,#2563eb,#1d4ed8);padding:12px 16px;font-size:14px;font-weight:700;">
                    <i data-lucide="shopping-bag" style="width:15px;height:15px;"></i>
                    Buat Order Sekarang
                </a>
                @if ($laundry->phone)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$laundry->phone) }}" target="_blank" class="sw-btn sw-w-full sw-mt-4" style="justify-content:center;border:1.5px solid #25d366;color:#25d366;background:transparent;">
                        <i data-lucide="message-circle" style="width:14px;height:14px;"></i>
                        Tanya via WhatsApp
                    </a>
                @endif
            @else
                <div style="font-size:12px;color:var(--ink-3);text-align:center;padding:8px;border-radius:6px;background:var(--surface-2);">
                    Hubungi merchant ini langsung atau kunjungi outletnya untuk membuat order.
                </div>
                @if ($laundry->phone)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$laundry->phone) }}" target="_blank" class="sw-btn sw-btn-primary sw-w-full sw-mt-4" style="justify-content:center;background:#25d366;">
                        <i data-lucide="message-circle" style="width:15px;height:15px;"></i>
                        Hubungi via WhatsApp
                    </a>
                @endif
            @endif
        </div>

        {{-- Track --}}
        <div class="sw-card sw-mt-4">
            <div style="font-size:13px;font-weight:600;color:var(--ink-1);margin-bottom:8px;">Sudah punya kode tracking?</div>
            <form method="GET" action="{{ route('tracking.show', 'CODE') }}" onsubmit="this.action=this.action.replace('CODE',this.querySelector('input').value)">
                <div style="display:flex;gap:6px;">
                    <input type="text" name="code" placeholder="SW-XXXXXXXX" class="sw-input" style="flex:1;font-family:monospace;" required>
                    <button type="submit" class="sw-btn sw-btn-primary">Track</button>
                </div>
            </form>
        </div>
    </div>

</div>

</x-layouts.sidebar>

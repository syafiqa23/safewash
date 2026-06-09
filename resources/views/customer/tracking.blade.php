<x-layouts.sidebar title="Tracking Order" heading="Tracking Order">

<div style="max-width:720px;margin:0 auto;">

    {{-- ── Header Info ──────────────────────────────────────────────────────── --}}
    <div style="background:linear-gradient(135deg,#eff6ff 0%,#dbeafe 100%);border:1px solid #bfdbfe;border-radius:16px;padding:24px;margin-bottom:20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <div style="width:48px;height:48px;background:#2563eb;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="qr-code" style="width:24px;height:24px;color:#fff;"></i>
        </div>
        <div>
            <div style="font-size:18px;font-weight:800;color:#1e3a5f;line-height:1.2;">Lacak Status Laundry</div>
            <div style="font-size:13px;color:#3b82f6;margin-top:3px;">Pantau perjalanan laundry Anda secara real-time</div>
        </div>
    </div>

    {{-- ── Form Tracking ─────────────────────────────────────────────────────── --}}
    <div class="sw-card sw-mb-20">
        <form method="GET" action="{{ route('customer.tracking') }}" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
            <div class="sw-form-group" style="flex:1;min-width:220px;margin-bottom:0;">
                <label class="sw-label" style="font-weight:600;font-size:13px;">Kode Tracking</label>
                <div style="position:relative;">
                    <i data-lucide="search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:var(--ink-3);pointer-events:none;"></i>
                    <input
                        type="text"
                        name="code"
                        id="tracking-input"
                        class="sw-input"
                        style="padding-left:36px;font-family:monospace;font-size:14px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;"
                        placeholder="Contoh: SW-DEMO001"
                        value="{{ request('code') }}"
                        autocomplete="off"
                        autofocus
                    >
                </div>
                <div style="font-size:11px;color:var(--ink-3);margin-top:4px;">Kode tracking terdiri dari format SW-XXXXXXXX</div>
            </div>
            <div style="padding-bottom:24px;">
                <button type="submit" class="sw-btn sw-btn-primary" style="height:42px;padding:0 22px;font-size:14px;">
                    <i data-lucide="search" style="width:15px;height:15px;"></i>
                    Lacak Order
                </button>
            </div>
        </form>
    </div>

    {{-- ── Hasil Tracking ────────────────────────────────────────────────────── --}}
    @if (isset($order))

        {{-- Order summary card --}}
        <div class="sw-card sw-mb-16" style="border-left:4px solid #2563eb;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                <div>
                    <div style="font-size:20px;font-weight:800;color:var(--accent);font-family:monospace;letter-spacing:.04em;">{{ $order->tracking_code }}</div>
                    <div style="font-size:13px;color:var(--ink-3);margin-top:2px;">{{ $order->laundry->name }} · {{ $order->service_type }}</div>
                </div>
                <x-ui.status-badge :status="$order->status" />
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;">
                <div style="background:var(--surface-2);border-radius:8px;padding:10px;">
                    <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--ink-3);font-weight:600;">Customer</div>
                    <div style="font-size:13px;font-weight:600;color:var(--ink-1);margin-top:3px;">{{ $order->customer_name }}</div>
                </div>
                <div style="background:var(--surface-2);border-radius:8px;padding:10px;">
                    <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--ink-3);font-weight:600;">Berat</div>
                    <div style="font-size:13px;font-weight:600;color:var(--ink-1);margin-top:3px;">{{ $order->weight_kg }} kg</div>
                </div>
                <div style="background:var(--surface-2);border-radius:8px;padding:10px;">
                    <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--ink-3);font-weight:600;">Pembayaran</div>
                    <div style="font-size:13px;font-weight:600;margin-top:3px;color:{{ $order->payment_status === 'paid' ? '#059669' : '#d97706' }};">{{ $order->payment_status === 'paid' ? 'Lunas' : 'Belum Lunas' }}</div>
                </div>
                <div style="background:var(--surface-2);border-radius:8px;padding:10px;">
                    <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--ink-3);font-weight:600;">Total</div>
                    <div style="font-size:13px;font-weight:700;color:var(--ink-1);margin-top:3px;">Rp{{ number_format($order->total_price,0,',','.') }}</div>
                </div>
                @if($order->promised_at)
                <div style="background:var(--surface-2);border-radius:8px;padding:10px;">
                    <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--ink-3);font-weight:600;">Estimasi</div>
                    <div style="font-size:13px;font-weight:600;color:var(--ink-1);margin-top:3px;">{{ $order->promised_at->format('d M H:i') }}</div>
                </div>
                @endif
                <div style="background:var(--surface-2);border-radius:8px;padding:10px;">
                    <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--ink-3);font-weight:600;">Proteksi</div>
                    <div style="font-size:13px;font-weight:600;margin-top:3px;color:{{ $order->is_premium_protected ? '#7c3aed' : 'var(--ink-3)' }};">{{ $order->is_premium_protected ? '🛡️ Premium' : 'Standar' }}</div>
                </div>
            </div>
        </div>

        {{-- ── Progress Timeline ─────────────────────────────────────────────── --}}
        @php
            $allSteps = [
                'received'       => ['label' => 'Order Dibuat',   'icon' => 'clipboard-check', 'desc' => 'Order diterima & diverifikasi'],
                'pickup'         => ['label' => 'Pickup',          'icon' => 'package',          'desc' => 'Pakaian dijemput kurir'],
                'washing'        => ['label' => 'Dicuci',          'icon' => 'droplets',         'desc' => 'Proses pencucian'],
                'drying'         => ['label' => 'Dikeringkan',     'icon' => 'wind',             'desc' => 'Proses pengeringan'],
                'ironing'        => ['label' => 'Disetrika',       'icon' => 'zap',              'desc' => 'Proses penyetrikaan'],
                'quality_check'  => ['label' => 'Quality Check',  'icon' => 'shield-check',     'desc' => 'Pemeriksaan kualitas'],
                'ready_delivery' => ['label' => 'Siap Diambil',   'icon' => 'truck',            'desc' => 'Siap diantar/diambil'],
                'completed'      => ['label' => 'Selesai',         'icon' => 'check-circle',     'desc' => 'Order selesai'],
            ];
            $statusKeys    = array_keys($allSteps);
            $currentStatus = $order->status === 'claimed' ? 'completed' : $order->status;
            $currentIdx    = array_search($currentStatus, $statusKeys);
            $trackingMap   = $order->trackingUpdates->keyBy('status');
        @endphp

        <div class="sw-card sw-mb-16">
            <div style="font-size:13px;font-weight:700;color:var(--ink-1);margin-bottom:20px;display:flex;align-items:center;gap:6px;">
                <i data-lucide="map" style="width:14px;height:14px;color:var(--accent);"></i>
                Perjalanan Laundry
            </div>

            {{-- Horizontal pipeline (scrollable) --}}
            <div style="overflow-x:auto;padding-bottom:4px;">
                <div style="display:flex;align-items:flex-start;min-width:560px;">
                    @foreach($allSteps as $key => $step)
                        @php
                            $idx       = array_search($key, $statusKeys);
                            $isDone    = $currentIdx !== false && $idx <= $currentIdx;
                            $isCurrent = $key === $currentStatus;
                            $hasUpdate = isset($trackingMap[$key]);
                            $ts        = $hasUpdate ? $trackingMap[$key]->created_at->format('d M H:i') : null;
                        @endphp
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;position:relative;">
                            {{-- Connector --}}
                            @if(!$loop->last)
                            <div style="position:absolute;top:20px;left:50%;right:-50%;height:2px;background:{{ ($isDone && $currentIdx > $idx) ? '#2563eb' : '#e5e7eb' }};z-index:0;"></div>
                            @endif
                            {{-- Circle --}}
                            <div style="width:40px;height:40px;border-radius:50%;z-index:1;display:flex;align-items:center;justify-content:center;transition:all .3s;
                                background:{{ $isCurrent ? '#2563eb' : ($isDone ? '#dbeafe' : '#f3f4f6') }};
                                border:2px solid {{ $isDone ? '#2563eb' : '#e5e7eb' }};
                                box-shadow:{{ $isCurrent ? '0 0 0 5px rgba(37,99,235,.18)' : 'none' }};">
                                <i data-lucide="{{ $step['icon'] }}" style="width:15px;height:15px;color:{{ $isCurrent ? '#fff' : ($isDone ? '#2563eb' : '#9ca3af') }};"></i>
                            </div>
                            {{-- Label --}}
                            <div style="text-align:center;margin-top:8px;padding:0 2px;">
                                <div style="font-size:10.5px;font-weight:{{ $isCurrent ? '700' : '500' }};color:{{ $isDone ? '#1e3a5f' : '#9ca3af' }};line-height:1.3;">{{ $step['label'] }}</div>
                                @if($ts)
                                    <div style="font-size:9.5px;color:#6b7280;margin-top:2px;">{{ $ts }}</div>
                                @elseif($isCurrent)
                                    <div style="font-size:9.5px;color:#2563eb;font-weight:600;margin-top:2px;">Sekarang</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Tracking Updates ──────────────────────────────────────────────── --}}
        @if($order->trackingUpdates->isNotEmpty())
        <div class="sw-card sw-mb-16">
            <div style="font-size:13px;font-weight:700;color:var(--ink-1);margin-bottom:14px;display:flex;align-items:center;gap:6px;">
                <i data-lucide="clock" style="width:14px;height:14px;color:var(--accent);"></i>
                Riwayat Update
                <span style="background:var(--surface-3);color:var(--ink-3);font-size:11px;font-weight:600;padding:1px 8px;border-radius:12px;">{{ $order->trackingUpdates->count() }}</span>
            </div>
            <div>
                @foreach($order->trackingUpdates as $update)
                <div style="display:flex;gap:12px;padding:10px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--surface-3);' : '' }}">
                    <div style="width:8px;height:8px;border-radius:50%;background:#2563eb;flex-shrink:0;margin-top:5px;"></div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:600;color:var(--ink-1);">{{ $update->title }}</div>
                        <div style="font-size:12px;color:var(--ink-3);margin-top:2px;">{{ $update->description }}</div>
                        <div style="font-size:11px;color:var(--muted);margin-top:4px;display:flex;align-items:center;gap:4px;">
                            <i data-lucide="clock" style="width:10px;height:10px;"></i>
                            {{ $update->created_at->format('d M Y · H:i') }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Detail link --}}
        <div style="text-align:center;padding:8px 0 4px;">
            <a href="{{ route('tracking.show', $order->tracking_code) }}" class="sw-btn sw-btn-ghost" style="font-size:13px;">
                <i data-lucide="external-link" style="width:13px;height:13px;"></i>
                Lihat Halaman Tracking Lengkap
            </a>
        </div>

    @elseif(request('code'))
        {{-- Not found --}}
        <div class="sw-card" style="text-align:center;padding:40px 20px;">
            <div style="width:56px;height:56px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i data-lucide="search-x" style="width:24px;height:24px;color:#dc2626;"></i>
            </div>
            <div style="font-size:16px;font-weight:700;color:var(--ink-1);margin-bottom:6px;">Order Tidak Ditemukan</div>
            <div style="font-size:13px;color:var(--ink-3);">Kode "<strong>{{ request('code') }}</strong>" tidak ditemukan.<br>Pastikan kode tracking benar (format: SW-XXXXXXXX).</div>
        </div>

    @else
        {{-- Default state —— tips ──────────────────────────────────────────── --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;">
            @foreach([
                ['icon'=>'qr-code',       'color'=>'#2563eb','bg'=>'#eff6ff','title'=>'Scan QR',            'desc'=>'Setiap order memiliki QR code yang bisa di-scan langsung dari packaging laundry.'],
                ['icon'=>'package',       'color'=>'#7c3aed','bg'=>'#ede9fe','title'=>'8 Status Tracking',   'desc'=>'Dari order diterima hingga selesai diantar — semua terpantau secara real-time.'],
                ['icon'=>'bell',          'color'=>'#059669','bg'=>'#d1fae5','title'=>'Notifikasi WA',       'desc'=>'Dapatkan update status via WhatsApp otomatis tanpa perlu membuka aplikasi.'],
                ['icon'=>'shield-check',  'color'=>'#d97706','bg'=>'#fef3c7','title'=>'Proteksi Premium',    'desc'=>'Aktifkan proteksi untuk perlindungan barang senilai hingga Rp2 juta per item.'],
            ] as $tip)
            <div style="background:var(--surface);border:1px solid var(--surface-3);border-radius:12px;padding:16px;">
                <div style="width:38px;height:38px;background:{{ $tip['bg'] }};border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
                    <i data-lucide="{{ $tip['icon'] }}" style="width:18px;height:18px;color:{{ $tip['color'] }};"></i>
                </div>
                <div style="font-size:13px;font-weight:700;color:var(--ink-1);margin-bottom:4px;">{{ $tip['title'] }}</div>
                <div style="font-size:12px;color:var(--ink-3);line-height:1.5;">{{ $tip['desc'] }}</div>
            </div>
            @endforeach
        </div>

        {{-- Quick access: my orders --}}
        @if(auth()->user()->customerOrders()->whereNotIn('status',['completed'])->exists())
        <div class="sw-card" style="margin-top:20px;">
            <div style="font-size:13px;font-weight:700;color:var(--ink-1);margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                <i data-lucide="package" style="width:14px;height:14px;color:var(--accent);"></i>
                Order Terakhir Anda
            </div>
            @foreach(auth()->user()->customerOrders()->with('laundry')->whereNotIn('status',['completed'])->latest()->take(3)->get() as $myOrder)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--surface-3);' : '' }}">
                <div>
                    <div style="font-family:monospace;font-size:13px;font-weight:700;color:var(--accent);">{{ $myOrder->tracking_code }}</div>
                    <div style="font-size:12px;color:var(--ink-3);">{{ $myOrder->laundry->name }}</div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <x-ui.status-badge :status="$myOrder->status" size="sm" />
                    <a href="{{ route('customer.tracking', ['code' => $myOrder->tracking_code]) }}" class="sw-btn sw-btn-ghost" style="padding:4px 10px;font-size:12px;">Lacak</a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    @endif

</div>

<script>
    // Auto-uppercase the tracking input
    document.getElementById('tracking-input')?.addEventListener('input', function() {
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });
</script>

</x-layouts.sidebar>

<x-layouts.app :title="'Tracking '.$order->tracking_code.' — SafeWash'">

<div class="trk-container">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="trk-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="background:#2563eb;border-radius:10px;width:42px;height:42px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="qr-code" style="width:20px;height:20px;color:white;"></i>
                </div>
                <div>
                    <div class="trk-code">{{ $order->tracking_code }}</div>
                    <div class="trk-sub">{{ $order->laundry->name }} · {{ $order->service_type }}</div>
                </div>
            </div>
            <x-ui.status-badge :status="$order->status" />
        </div>

        <div class="trk-meta-grid">
            <div class="trk-meta-box">
                <div class="trk-meta-lbl">Customer</div>
                <div class="trk-meta-val">{{ $order->customer_name }}</div>
            </div>
            <div class="trk-meta-box">
                <div class="trk-meta-lbl">Berat</div>
                <div class="trk-meta-val">{{ $order->weight_kg }} kg</div>
            </div>
            <div class="trk-meta-box">
                <div class="trk-meta-lbl">Proteksi</div>
                <div class="trk-meta-val">{{ $order->is_premium_protected ? '🛡️ Premium' : 'Standar' }}</div>
            </div>
            <div class="trk-meta-box">
                <div class="trk-meta-lbl">Pembayaran</div>
                <div class="trk-meta-val">{{ $order->payment_status === 'paid' ? '✅ Lunas' : '⏳ Belum Lunas' }}</div>
            </div>
            @if ($order->promised_at)
                <div class="trk-meta-box">
                    <div class="trk-meta-lbl">Estimasi Selesai</div>
                    <div class="trk-meta-val">{{ $order->promised_at->format('d M Y H:i') }}</div>
                </div>
            @endif
            @if ($order->notes)
                <div class="trk-meta-box">
                    <div class="trk-meta-lbl">Catatan</div>
                    <div class="trk-meta-val" style="font-size:12px;">{{ $order->notes }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Shopee-style Status Pipeline ────────────────────────────────────── --}}
    @php
        $allSteps = [
            'received'       => ['label' => 'Diterima',        'icon' => 'clipboard-check'],
            'pickup'         => ['label' => 'Pickup',           'icon' => 'package'],
            'washing'        => ['label' => 'Dicuci',           'icon' => 'droplets'],
            'drying'         => ['label' => 'Dikeringkan',      'icon' => 'wind'],
            'ironing'        => ['label' => 'Disetrika',        'icon' => 'zap'],
            'quality_check'  => ['label' => 'Quality Check',   'icon' => 'shield-check'],
            'ready_delivery' => ['label' => 'Siap Diantar',    'icon' => 'truck'],
            'completed'      => ['label' => 'Selesai',          'icon' => 'check-circle'],
        ];
        $statusKeys   = array_keys($allSteps);
        $currentStatus = $order->status === 'claimed' ? 'completed' : $order->status;
        $currentIdx   = array_search($currentStatus, $statusKeys);
        $trackingMap  = $order->trackingUpdates->keyBy('status');
    @endphp

    <div class="trk-card" style="overflow-x:auto;">
        <div style="font-size:13px;font-weight:700;color:var(--ink-1);margin-bottom:16px;display:flex;align-items:center;gap:6px;">
            <i data-lucide="map" style="width:14px;height:14px;color:var(--accent);"></i>
            Status Perjalanan Laundry
        </div>

        {{-- Desktop pipeline --}}
        <div style="display:flex;align-items:flex-start;gap:0;min-width:600px;">
            @foreach ($allSteps as $key => $step)
                @php
                    $idx      = array_search($key, $statusKeys);
                    $isDone   = $currentIdx !== false && $idx <= $currentIdx;
                    $isCurrent= $key === $currentStatus;
                    $hasUpdate= isset($trackingMap[$key]);
                    $ts       = $hasUpdate ? $trackingMap[$key]->created_at->format('d M H:i') : null;
                @endphp
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;position:relative;">
                    {{-- Connector line --}}
                    @if (!$loop->last)
                        <div style="position:absolute;top:20px;left:50%;right:-50%;height:2px;background:{{ $isDone && $currentIdx > $idx ? '#2563eb' : '#e5e7eb' }};z-index:0;"></div>
                    @endif

                    {{-- Icon circle --}}
                    <div style="
                        width:40px;height:40px;border-radius:50%;z-index:1;
                        background:{{ $isCurrent ? '#2563eb' : ($isDone ? '#dbeafe' : '#f3f4f6') }};
                        border:2px solid {{ $isDone ? '#2563eb' : '#e5e7eb' }};
                        display:flex;align-items:center;justify-content:center;
                        box-shadow:{{ $isCurrent ? '0 0 0 4px rgba(37,99,235,.2)' : 'none' }};
                        transition:all .3s;
                    ">
                        <i data-lucide="{{ $step['icon'] }}" style="width:16px;height:16px;color:{{ $isCurrent ? 'white' : ($isDone ? '#2563eb' : '#9ca3af') }};"></i>
                    </div>

                    {{-- Label --}}
                    <div style="text-align:center;margin-top:8px;padding:0 2px;">
                        <div style="font-size:11px;font-weight:{{ $isCurrent ? '700' : '500' }};color:{{ $isDone ? '#1e3a5f' : '#9ca3af' }};line-height:1.3;">{{ $step['label'] }}</div>
                        @if ($ts)
                            <div style="font-size:10px;color:#6b7280;margin-top:2px;">{{ $ts }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Grid: Items + Timeline ──────────────────────────────────────────── --}}
    <div class="trk-grid-2">

        {{-- Items --}}
        <div class="trk-card" style="margin-bottom:0;">
            <div class="trk-card-title">
                Ringkasan Item
                <span class="trk-count">{{ $order->items->sum('quantity') }} item</span>
            </div>
            @forelse ($order->items as $item)
                <div class="trk-item">
                    <div>
                        <div class="trk-item-name">
                            {{ $item->item_name }}
                            <span style="color:var(--muted);font-weight:500;">×{{ $item->quantity }}</span>
                        </div>
                        @if ($item->condition_notes)
                            <div class="trk-item-note">{{ $item->condition_notes }}</div>
                        @endif
                    </div>
                    @if ($item->is_priority)
                        <span class="sw-badge sw-badge-amber" style="font-size:10.5px;">
                            <i data-lucide="zap" style="width:9px;height:9px;"></i> Prioritas
                        </span>
                    @endif
                </div>
            @empty
                <div class="trk-empty">
                    <i data-lucide="package"></i> Belum ada item tercatat.
                </div>
            @endforelse
        </div>

        {{-- Detail Timeline --}}
        <div class="trk-card" style="margin-bottom:0;">
            <div class="trk-card-title">
                Riwayat Update
                <span class="trk-count">{{ $order->trackingUpdates->count() }} update</span>
            </div>
            @if ($order->trackingUpdates->isEmpty())
                <div class="trk-empty">
                    <i data-lucide="map-pin"></i> Belum ada update tracking.
                </div>
            @else
                <div class="trk-tl">
                    @foreach ($order->trackingUpdates as $update)
                        <div class="trk-tl-item">
                            <div class="trk-tl-dot">
                                <i data-lucide="check" style="width:10px;height:10px;"></i>
                            </div>
                            <div class="trk-tl-body">
                                <div class="trk-tl-title">{{ $update->title }}</div>
                                <div class="trk-tl-desc">{{ $update->description }}</div>
                                @if ($update->creator)
                                    <div style="font-size:11px;color:var(--muted);margin-top:2px;">
                                        oleh {{ $update->creator->name }}
                                    </div>
                                @endif
                                <div class="trk-tl-time">
                                    <i data-lucide="clock" style="width:10px;height:10px;"></i>
                                    {{ $update->created_at->format('d M Y · H:i') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div style="margin-top:16px;"></div>

    {{-- ── Notifikasi + Klaim ──────────────────────────────────────────────── --}}
    <div class="trk-grid-2">

        <div class="trk-card" style="margin-bottom:0;">
            <div class="trk-card-title">
                Log Notifikasi
                <span class="trk-count">{{ $order->notifications->count() }}</span>
            </div>
            @forelse ($order->notifications as $notif)
                <div class="trk-log">
                    <div>
                        <div class="trk-log-ch">{{ $notif->channel }}</div>
                        <div class="trk-log-msg">{{ Str::limit($notif->message, 60) }}</div>
                    </div>
                    <x-ui.status-badge :status="$notif->status" size="sm" />
                </div>
            @empty
                <div class="trk-empty" style="padding:20px;">
                    <i data-lucide="bell-off"></i> Belum ada log notifikasi.
                </div>
            @endforelse
        </div>

        <div class="trk-card" style="margin-bottom:0;">
            <div class="trk-card-title">
                Status Klaim
                <span class="trk-count">{{ $order->claims->count() }}</span>
            </div>
            @forelse ($order->claims as $claim)
                <div class="trk-log">
                    <div>
                        <div class="trk-log-ch">
                            {{ \App\Models\Claim::TYPE_LABELS[$claim->claim_type] ?? $claim->claim_type }} — {{ $claim->item_name }}
                        </div>
                        <div class="trk-log-msg">{{ Str::limit($claim->description, 55) }}</div>
                        @if ($claim->compensation_amount > 0)
                            <div style="font-size:11px;color:#059669;font-weight:600;margin-top:2px;">
                                Kompensasi: Rp{{ number_format($claim->compensation_amount,0,',','.') }}
                            </div>
                        @endif
                    </div>
                    <x-ui.status-badge :status="$claim->status" size="sm" />
                </div>
            @empty
                <div class="trk-empty" style="padding:20px;">
                    <i data-lucide="shield-check"></i> Belum ada klaim untuk order ini.
                </div>
            @endforelse
        </div>

    </div>

    {{-- ── Pickup Delivery ─────────────────────────────────────────────────── --}}
    @if ($order->deliveryRequest)
        <div class="trk-card" style="margin-top:16px;">
            <div class="trk-card-title">
                <i data-lucide="truck" style="width:14px;height:14px;color:var(--accent);vertical-align:middle;margin-right:4px;"></i>
                Pickup & Delivery
            </div>
            <div class="trk-meta-grid">
                <div class="trk-meta-box">
                    <div class="trk-meta-lbl">Partner</div>
                    <div class="trk-meta-val">{{ $order->deliveryRequest->partner_name }}</div>
                </div>
                <div class="trk-meta-box">
                    <div class="trk-meta-lbl">Status Kurir</div>
                    <div class="trk-meta-val"><x-ui.status-badge :status="$order->deliveryRequest->status" size="sm" /></div>
                </div>
                @if ($order->deliveryRequest->pickup_scheduled_at)
                    <div class="trk-meta-box">
                        <div class="trk-meta-lbl">Jadwal Pickup</div>
                        <div class="trk-meta-val">{{ $order->deliveryRequest->pickup_scheduled_at->format('d M Y H:i') }}</div>
                    </div>
                @endif
                @if ($order->deliveryRequest->delivery_scheduled_at)
                    <div class="trk-meta-box">
                        <div class="trk-meta-lbl">Jadwal Antar</div>
                        <div class="trk-meta-val">{{ $order->deliveryRequest->delivery_scheduled_at->format('d M Y H:i') }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ── CTA Footer ──────────────────────────────────────────────────────── --}}
    <div class="trk-cta" style="margin-top:24px;">
        <p>Kelola laundry Anda lebih profesional dengan SafeWash</p>
        <a href="{{ route('register') }}" class="hp-btn hp-btn-primary" style="display:inline-flex;align-items:center;gap:7px;padding:11px 22px;border-radius:10px;background:var(--accent);color:#fff;font-weight:600;font-size:14px;text-decoration:none;">
            <i data-lucide="store" style="width:15px;height:15px;"></i>
            Daftar Merchant Gratis
        </a>
    </div>

</div>

</x-layouts.app>

<x-layouts.sidebar title="Dashboard" heading="Halo, {{ auth()->user()->name }} 👋">

{{-- ─── KPI SUMMARY ─────────────────────────────────────────────────────────── --}}
<div class="sw-kpi-grid sw-mb-20">
    <x-ui.kpi-card icon="package"      label="Total Pesanan"    value="{{ number_format($stats['total_orders']) }}"  color="accent" />
    <x-ui.kpi-card icon="loader"       label="Sedang Diproses"  value="{{ number_format($stats['active_orders']) }}" color="blue" />
    <x-ui.kpi-card icon="banknote"     label="Total Belanja"    value="Rp{{ number_format($stats['total_spent'],0,',','.') }}" color="green" />
    <x-ui.kpi-card icon="shield-alert" label="Klaim Terbuka"    value="{{ number_format($stats['open_claims']) }}"   color="amber" />
</div>

{{-- ─── LAUNDRY SEDANG DIPROSES (FOKUS UTAMA) ──────────────────────────────── --}}
<div class="sw-card sw-mb-20" style="{{ $activeOrders->isNotEmpty() ? 'border-left:3px solid var(--accent);' : '' }}">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:40px;height:40px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="loader" style="width:20px;height:20px;color:white;"></i>
            </div>
            <div>
                <div style="font-size:16px;font-weight:700;color:var(--ink-1);">Laundry Sedang Diproses</div>
                <div style="font-size:12px;color:var(--ink-3);">Status terbaru pakaian Anda</div>
            </div>
        </div>
        <a href="{{ route('orders.index') }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Semua Pesanan →</a>
    </div>

    @if ($activeOrders->isEmpty())
        <div style="text-align:center;padding:24px 0;color:var(--ink-3);">
            <i data-lucide="check-circle" style="width:36px;height:36px;color:var(--success);margin-bottom:8px;"></i>
            <div style="font-size:14px;font-weight:600;color:var(--success);">Semua laundry sudah selesai!</div>
            <div style="font-size:13px;margin-top:4px;">
                <a href="{{ route('marketplace.index') }}" style="color:var(--accent);text-decoration:none;">Cari merchant baru →</a>
            </div>
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:12px;">
            @foreach ($activeOrders as $order)
                @php
                    $allStatuses = \App\Models\LaundryOrder::STATUSES;
                    $currentIdx  = array_search($order->status, $allStatuses);
                    $totalSteps  = count($allStatuses) - 1; // exclude 'claimed'
                    $progressPct = $currentIdx !== false ? round(($currentIdx / max(1,$totalSteps-1)) * 100) : 0;
                @endphp
                <div style="border:1px solid var(--surface-3);border-radius:10px;padding:14px;background:var(--surface-1);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
                        <div>
                            <a href="{{ route('orders.show', $order) }}" class="sw-font-mono sw-fw-700" style="color:var(--accent);font-size:15px;text-decoration:none;">{{ $order->tracking_code }}</a>
                            <div style="font-size:13px;color:var(--ink-2);margin-top:2px;">{{ $order->laundry->name }} · {{ $order->service_type }}</div>
                        </div>
                        <x-ui.status-badge :status="$order->status" />
                    </div>

                    {{-- Progress bar --}}
                    <div style="margin-bottom:8px;">
                        <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--ink-3);margin-bottom:4px;">
                            <span>Progres</span>
                            <span>{{ $progressPct }}%</span>
                        </div>
                        <div style="height:6px;background:var(--surface-3);border-radius:3px;overflow:hidden;">
                            <div style="width:{{ $progressPct }}%;height:100%;background:var(--accent);border-radius:3px;transition:width .3s;"></div>
                        </div>
                    </div>

                    {{-- Latest update --}}
                    @if ($order->trackingUpdates->first())
                        <div style="font-size:12px;color:var(--ink-2);background:var(--surface-2);border-radius:6px;padding:6px 10px;">
                            <i data-lucide="info" style="width:11px;height:11px;color:var(--accent);vertical-align:middle;margin-right:4px;"></i>
                            {{ $order->trackingUpdates->first()->description }}
                        </div>
                    @endif

                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;">
                        <div style="font-size:12px;color:var(--ink-3);">
                            @if ($order->promised_at)
                                Estimasi selesai: <strong>{{ $order->promised_at->format('d M H:i') }}</strong>
                            @else
                                Dibuat {{ $order->created_at->diffForHumans() }}
                            @endif
                        </div>
                        <a href="{{ route('tracking.show', $order->tracking_code) }}" target="_blank" style="font-size:12px;color:var(--accent);text-decoration:none;display:flex;align-items:center;gap:3px;">
                            <i data-lucide="qr-code" style="width:12px;height:12px;"></i> Tracking QR
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

    <div>
        {{-- ─── PESANAN TERAKHIR ────────────────────────────────────────────────── --}}
        <div class="sw-card sw-mb-20">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <x-ui.section-header title="Pesanan Terakhir" icon="clock" />
                <a href="{{ route('orders.index') }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Lihat Semua →</a>
            </div>
            @if ($recentOrders->isEmpty())
                <x-ui.empty-state icon="package" title="Belum ada pesanan" description="Cari merchant laundry di marketplace kami." actionLabel="Buka Marketplace" actionHref="{{ route('marketplace.index') }}" />
            @else
                <div class="sw-table-wrap">
                    <table class="sw-table">
                        <thead>
                            <tr>
                                <th>Tracking</th>
                                <th>Merchant</th>
                                <th>Layanan</th>
                                <th>Status</th>
                                <th class="sw-text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentOrders->take(6) as $order)
                                <tr onclick="window.location='{{ route('orders.show',$order) }}'" style="cursor:pointer;">
                                    <td class="sw-font-mono sw-fw-700" style="color:var(--accent);">{{ $order->tracking_code }}</td>
                                    <td class="sw-text-sm">{{ $order->laundry->name }}</td>
                                    <td class="sw-text-sm sw-muted">{{ $order->service_type }}</td>
                                    <td><x-ui.status-badge :status="$order->status" size="sm" /></td>
                                    <td class="sw-text-right sw-fw-600 sw-text-sm">Rp{{ number_format($order->total_price,0,',','.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ─── MERCHANT FAVORIT ────────────────────────────────────────────────── --}}
        @if ($favoriteMerchants->isNotEmpty())
            <div class="sw-card sw-mb-20">
                <x-ui.section-header title="Merchant Favorit" icon="heart" />
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-top:12px;">
                    @foreach ($favoriteMerchants as $m)
                        <a href="{{ route('marketplace.show', $m) }}" style="text-decoration:none;">
                            <div style="border:1px solid var(--surface-3);border-radius:8px;padding:12px;transition:box-shadow .15s;" onmouseover="this.style.boxShadow='0 2px 12px rgba(37,99,235,.12)'" onmouseout="this.style.boxShadow=''">
                                <div style="font-size:14px;font-weight:700;color:var(--ink-1);margin-bottom:2px;">{{ $m->name }}</div>
                                @if ($m->city)<div style="font-size:12px;color:var(--ink-3);">{{ $m->city }}</div>@endif
                                <div style="display:flex;align-items:center;gap:3px;margin-top:6px;">
                                    <i data-lucide="star" style="width:11px;height:11px;color:#d97706;fill:#d97706;"></i>
                                    <span style="font-size:12px;font-weight:600;color:#92400e;">{{ number_format((float)$m->rating,1) }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ─── KLAIM AKTIF ─────────────────────────────────────────────────────── --}}
        @if ($claims->whereIn('status', ['submitted','investigating'])->isNotEmpty())
            <div class="sw-card sw-mb-20" style="border-left:3px solid #dc2626;">
                <x-ui.section-header title="Klaim Aktif" icon="shield-alert" />
                @foreach ($claims->whereIn('status', ['submitted','investigating']) as $claim)
                    <div style="padding:10px 0;border-bottom:1px solid var(--surface-3);display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div class="sw-fw-600 sw-text-sm">{{ $claim->item_name }}</div>
                            <div class="sw-text-xs sw-muted">{{ $claim->order->tracking_code }} · {{ \App\Models\Claim::TYPE_LABELS[$claim->claim_type] ?? $claim->claim_type }}</div>
                        </div>
                        <x-ui.status-badge :status="$claim->status" size="sm" />
                    </div>
                @endforeach
                <a href="{{ route('claims.index') }}" style="font-size:12px;color:var(--accent);text-decoration:none;display:block;margin-top:8px;">Lihat Semua Klaim →</a>
            </div>
        @endif
    </div>

    {{-- Right column: Loyalty + Quick Links --}}
    <div>
        {{-- Loyalty Card --}}
        @if ($loyaltyAccount)
            <div class="sw-card sw-mb-16" style="background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 100%);color:white;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                    <div>
                        <div style="font-size:12px;opacity:.7;text-transform:uppercase;letter-spacing:.08em;">Loyalty Tier</div>
                        <div style="font-size:22px;font-weight:800;">{{ $loyaltyAccount->tier }}</div>
                    </div>
                    <i data-lucide="gift" style="width:28px;height:28px;opacity:.8;"></i>
                </div>
                <div style="font-size:32px;font-weight:800;">{{ number_format($loyaltyAccount->points_balance) }}</div>
                <div style="font-size:12px;opacity:.7;margin-bottom:12px;">poin tersedia</div>

                @php
                    $tiers = [['name'=>'Ocean','min'=>0,'max'=>499],['name'=>'Sky','min'=>500,'max'=>1499],['name'=>'Cloud','min'=>1500,'max'=>3999],['name'=>'Aurora','min'=>4000,'max'=>null]];
                    $currentTier = collect($tiers)->firstWhere('name', $loyaltyAccount->tier);
                    $nextTier = collect($tiers)->firstWhere(fn($t) => $t['min'] > ($currentTier['min']??0));
                    $ptsNeeded = $nextTier ? max(0, $nextTier['min'] - $loyaltyAccount->lifetime_points) : 0;
                    $pct = $nextTier ? min(100, round(($loyaltyAccount->lifetime_points - ($currentTier['min']??0)) / max(1,($nextTier['min']-($currentTier['min']??0))) * 100)) : 100;
                @endphp

                @if ($nextTier)
                    <div style="font-size:11px;opacity:.7;margin-bottom:4px;">{{ $ptsNeeded }} poin lagi ke {{ $nextTier['name'] }}</div>
                    <div style="height:5px;background:rgba(255,255,255,.25);border-radius:3px;overflow:hidden;">
                        <div style="width:{{ $pct }}%;height:100%;background:white;border-radius:3px;"></div>
                    </div>
                @else
                    <div style="font-size:11px;opacity:.7;">Tier tertinggi — Aurora ⭐</div>
                @endif

                <a href="{{ route('loyalty.index') }}" style="display:block;margin-top:12px;font-size:12px;color:white;text-align:center;border:1px solid rgba(255,255,255,.4);border-radius:6px;padding:6px;text-decoration:none;">
                    Lihat Program Loyalty →
                </a>
            </div>
        @else
            <div class="sw-card sw-mb-16" style="text-align:center;">
                <i data-lucide="gift" style="width:32px;height:32px;color:var(--accent);margin-bottom:8px;"></i>
                <div style="font-size:14px;font-weight:600;color:var(--ink-1);margin-bottom:4px;">Program Loyalty</div>
                <div style="font-size:12px;color:var(--ink-3);margin-bottom:12px;">Kumpulkan poin dari setiap order selesai</div>
                <a href="{{ route('loyalty.index') }}" class="sw-btn sw-btn-primary sw-w-full" style="justify-content:center;">Aktifkan</a>
            </div>
        @endif

        {{-- Quick Links --}}
        <div class="sw-card">
            <div style="font-size:12px;font-weight:600;color:var(--ink-2);text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px;">Aksi Cepat</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <a href="{{ route('marketplace.index') }}" style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid var(--surface-3);border-radius:8px;text-decoration:none;color:var(--ink-1);font-size:13px;font-weight:600;">
                    <i data-lucide="store" style="width:16px;height:16px;color:var(--accent);"></i>
                    Cari Merchant Laundry
                </a>
                <a href="{{ route('orders.index') }}" style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid var(--surface-3);border-radius:8px;text-decoration:none;color:var(--ink-1);font-size:13px;font-weight:600;">
                    <i data-lucide="package" style="width:16px;height:16px;color:var(--accent);"></i>
                    Semua Pesanan Saya
                </a>
                <a href="{{ route('claims.index') }}" style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid var(--surface-3);border-radius:8px;text-decoration:none;color:var(--ink-1);font-size:13px;font-weight:600;">
                    <i data-lucide="shield-alert" style="width:16px;height:16px;color:#dc2626;"></i>
                    Ajukan / Lihat Klaim
                </a>
                <a href="{{ route('profile.index') }}" style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid var(--surface-3);border-radius:8px;text-decoration:none;color:var(--ink-1);font-size:13px;font-weight:600;">
                    <i data-lucide="user" style="width:16px;height:16px;color:var(--accent);"></i>
                    Edit Profil
                </a>
            </div>
        </div>
    </div>

</div>

</x-layouts.sidebar>

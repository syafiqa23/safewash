<x-layouts.sidebar :title="'Program Loyalty'" :heading="'Program Loyalty'">

{{-- ─── Tier Banner ────────────────────────────────────────────────────────── --}}
<div class="sw-card sw-mb-20" style="background:linear-gradient(135deg,var(--accent) 0%,#1d4ed8 100%); border:none;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
        <div>
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:rgba(255,255,255,.7); margin-bottom:6px;">
                Tier Saat Ini
            </div>
            <div style="font-size:28px; font-weight:900; color:#fff; letter-spacing:-.02em;">
                {{ $loyaltyAccount?->tier ?? 'Belum Aktif' }}
            </div>
            <div style="font-size:13px; color:rgba(255,255,255,.8); margin-top:6px;">
                {{ number_format($loyaltyAccount?->points_balance ?? 0) }} poin tersedia
                · {{ number_format($loyaltyAccount?->lifetime_points ?? 0) }} poin seumur hidup
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:11px; font-weight:700; color:rgba(255,255,255,.7); text-transform:uppercase; letter-spacing:.1em; margin-bottom:6px;">
                Milestone Tier
            </div>
            @php
                $currentTier = $loyaltyAccount?->tier ?? 'Ocean';
                $nextTier = match($currentTier) { 'Ocean' => ['name'=>'Sky','min'=>500], 'Sky' => ['name'=>'Cloud','min'=>1500], 'Cloud' => ['name'=>'Aurora','min'=>4000], default => null };
                $balance = $loyaltyAccount?->lifetime_points ?? 0;
            @endphp
            @if ($nextTier)
                <div style="font-size:13px; color:rgba(255,255,255,.9);">
                    {{ number_format(max(0, $nextTier['min'] - $balance)) }} pts lagi ke {{ $nextTier['name'] }}
                </div>
                <div style="margin-top:8px; background:rgba(255,255,255,.2); border-radius:999px; height:6px; width:160px; overflow:hidden;">
                    <div style="height:100%; background:#fff; border-radius:999px; width:{{ min(100, ($balance / $nextTier['min']) * 100) }}%;"></div>
                </div>
            @else
                <div style="font-size:13px; color:rgba(255,255,255,.9);">Tier tertinggi — Aurora</div>
            @endif
        </div>
    </div>
</div>

{{-- ─── Tier Roadmap ───────────────────────────────────────────────────────── --}}
<div class="sw-kpi-grid sw-mb-20">
    @foreach ($tiers as $tier)
        @php $active = ($loyaltyAccount?->tier ?? 'Ocean') === $tier['name']; @endphp
        <x-ui.kpi-card
            :icon="$tier['icon']"
            :label="$tier['name']"
            :value="$tier['min'] === 0 ? 'Awal' : number_format($tier['min']).' pts'"
            :sub="$tier['max'] ? 'hingga '.number_format($tier['max']).' pts' : 'Tier tertinggi'"
            :color="$active ? $tier['color'] : 'accent'"
        />
    @endforeach
</div>

{{-- ─── KPI Row ─────────────────────────────────────────────────────────────── --}}
<div class="sw-body-grid sw-mb-20">
    <div class="sw-card">
        <x-ui.section-header title="Saldo &amp; Statistik" icon="star" />
        <div class="sw-mt-12">
            @foreach ([
                ['lbl' => 'Poin Aktif (Saldo)',    'val' => number_format($loyaltyAccount?->points_balance ?? 0).' pts',   'icon' => 'star',     'color' => 'var(--warning)'],
                ['lbl' => 'Total Poin Seumur Hidup','val' => number_format($loyaltyAccount?->lifetime_points ?? 0).' pts',  'icon' => 'trophy',   'color' => 'var(--accent)'],
                ['lbl' => 'Tier',                   'val' => $loyaltyAccount?->tier ?? 'Belum Aktif',                       'icon' => 'award',    'color' => 'var(--success)'],
                ['lbl' => 'Total Transaksi',        'val' => number_format($history->total()).' transaksi',                  'icon' => 'list',     'color' => 'var(--muted)'],
            ] as $stat)
                <div class="sw-list-row">
                    <div class="sw-flex sw-items-center sw-gap-10">
                        <i data-lucide="{{ $stat['icon'] }}" style="width:14px;height:14px;color:{{ $stat['color'] }};"></i>
                        <span class="sw-muted sw-text-sm">{{ $stat['lbl'] }}</span>
                    </div>
                    <span class="sw-fw-700 sw-text-sm">{{ $stat['val'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="sw-card">
        <x-ui.section-header title="Cara Mendapatkan Poin" icon="gift" />
        <div class="sw-mt-12">
            @foreach ([
                ['icon' => 'check-circle',  'title' => 'Order Selesai & Lunas',       'desc' => 'Dapatkan poin otomatis setiap order laundry Anda selesai'],
                ['icon' => 'calculator',    'title' => 'Rumus: max(10, harga÷5.000)', 'desc' => 'Semakin besar nilai order, semakin banyak poin didapat'],
                ['icon' => 'trending-up',   'title' => 'Naik Tier Otomatis',          'desc' => 'Ocean → Sky → Cloud → Aurora berdasarkan lifetime points'],
                ['icon' => 'shield-check',  'title' => 'Proteksi Premium',            'desc' => 'Tier tinggi memberi akses ke layanan klaim prioritas'],
            ] as $item)
                <div class="sw-list-row">
                    <div class="sw-flex sw-items-center sw-gap-10">
                        <div class="sw-timeline-dot"><i data-lucide="{{ $item['icon'] }}"></i></div>
                        <div>
                            <div class="sw-fw-600 sw-text-sm">{{ $item['title'] }}</div>
                            <div class="sw-muted sw-text-xs">{{ $item['desc'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ─── Transaction History ─────────────────────────────────────────────────── --}}
<div class="sw-card">
    <x-ui.section-header title="Riwayat Poin" icon="list" badge="{{ $history->total() }} transaksi" />

    @if ($history->isEmpty())
        <x-ui.empty-state
            icon="gift"
            title="Belum ada riwayat poin"
            description="Poin akan muncul setelah order pertama Anda selesai dan lunas."
        />
    @else
        <div class="sw-table-wrap sw-mt-4">
            <table class="sw-table">
                <thead>
                    <tr>
                        <th>Tipe</th>
                        <th>Deskripsi</th>
                        <th>Order</th>
                        <th class="sw-text-right">Poin</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($history as $tx)
                        <tr>
                            <td>
                                <span class="sw-badge {{ $tx->type === 'earn' ? 'sw-badge-green' : 'sw-badge-gray' }} sw-text-xs">
                                    <i data-lucide="{{ $tx->type === 'earn' ? 'plus-circle' : 'minus-circle' }}" style="width:10px;height:10px;"></i>
                                    {{ $tx->type === 'earn' ? 'Earn' : 'Redeem' }}
                                </span>
                            </td>
                            <td class="sw-text-sm">{{ $tx->description }}</td>
                            <td>
                                @if ($tx->order)
                                    <a href="{{ route('orders.show', $tx->order) }}" class="sw-font-mono sw-fw-700" style="color:var(--accent);">
                                        {{ $tx->order->tracking_code }}
                                    </a>
                                @else
                                    <span class="sw-muted">—</span>
                                @endif
                            </td>
                            <td class="sw-text-right sw-fw-700" style="color:{{ $tx->type === 'earn' ? 'var(--success)' : 'var(--danger)' }};">
                                {{ $tx->type === 'earn' ? '+' : '-' }}{{ number_format(abs($tx->points)) }} pts
                            </td>
                            <td class="sw-muted sw-text-xs">{{ $tx->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($history->hasPages())
            <div class="sw-flex sw-justify-center" style="padding:16px 0 4px;">
                {{ $history->links() }}
            </div>
        @endif
    @endif
</div>

</x-layouts.sidebar>

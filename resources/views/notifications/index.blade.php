<x-layouts.sidebar title="Notifikasi" heading="Notifikasi">

@php
$notifJson = $notifications->map(fn($n) => [
    'id'    => $n['id'],
    'icon'  => $n['icon'],
    'color' => $n['color'],
    'bg'    => $n['bg'],
    'title' => $n['title'],
    'body'  => $n['body'],
    'time'  => $n['time'] ? \Carbon\Carbon::parse($n['time'])->toIso8601String() : null,
    'read'  => $n['read'],
    'url'   => $n['url'],
])->values()->toJson();
@endphp

{{-- ── Page Header ─────────────────────────────────────────────────────────── --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <div style="width:44px;height:44px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="bell" style="width:20px;height:20px;color:#fff;"></i>
        </div>
        <div>
            <div style="font-size:18px;font-weight:800;color:var(--ink-1);">Notifikasi</div>
            @if($unreadCount > 0)
            <div style="font-size:12px;color:#dc2626;font-weight:600;">{{ $unreadCount }} belum dibaca</div>
            @else
            <div style="font-size:12px;color:var(--ink-3);">Semua sudah dibaca</div>
            @endif
        </div>
    </div>
    <div style="display:flex;gap:6px;">
        <a href="{{ route('notifications.index') }}"
           class="sw-btn sw-btn-sm {{ $filter === 'all' ? 'sw-btn-primary' : 'sw-btn-ghost' }}"
           style="font-size:12px;">
            <i data-lucide="list" style="width:13px;height:13px;"></i>
            Semua
        </a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
           class="sw-btn sw-btn-sm {{ $filter === 'unread' ? 'sw-btn-primary' : 'sw-btn-ghost' }}"
           style="font-size:12px;">
            <i data-lucide="bell-dot" style="width:13px;height:13px;"></i>
            Belum Dibaca
            @if($unreadCount > 0)
                <span style="background:#dc2626;color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;margin-left:2px;">{{ $unreadCount }}</span>
            @endif
        </a>
    </div>
</div>

{{-- ── Two-Column Grid ─────────────────────────────────────────────────────── --}}
<div id="sw-notif-grid" style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px;align-items:start;">

    {{-- ── Left: Notification List ────────────────────────────────────────── --}}
    <div>
        @if($notifications->isEmpty())
            <div class="sw-card" style="text-align:center;padding:56px 24px;">
                <div style="width:60px;height:60px;background:var(--surface-2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i data-lucide="bell-off" style="width:26px;height:26px;color:var(--ink-3);"></i>
                </div>
                <div style="font-size:15px;font-weight:700;color:var(--ink-1);margin-bottom:6px;">
                    {{ $filter === 'unread' ? 'Tidak ada notifikasi yang belum dibaca' : 'Belum ada notifikasi' }}
                </div>
                <div style="font-size:13px;color:var(--ink-3);">
                    {{ $filter === 'unread' ? 'Semua notifikasi sudah dibaca.' : 'Notifikasi akan muncul saat ada aktivitas pada akun Anda.' }}
                </div>
            </div>
        @else
            <div class="sw-card" style="padding:0;overflow:hidden;">
                @foreach($notifications as $i => $notif)
                @php $isRead = (bool)($notif['read'] ?? true); @endphp
                <button
                    type="button"
                    onclick="swSelectNotif({{ $i }})"
                    id="sw-notif-item-{{ $i }}"
                    style="display:flex;align-items:flex-start;gap:13px;padding:15px 18px;width:100%;text-align:left;background:{{ !$isRead ? 'rgba(37,99,235,.05)' : 'transparent' }};transition:background .15s;border:none;border-bottom:1px solid var(--surface-3);cursor:pointer;">

                    {{-- Icon --}}
                    <div style="width:40px;height:40px;border-radius:10px;background:{{ $notif['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                        <i data-lucide="{{ $notif['icon'] }}" style="width:17px;height:17px;color:{{ $notif['color'] }};"></i>
                    </div>

                    {{-- Content --}}
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:6px;">
                            <div style="font-size:13px;font-weight:{{ !$isRead ? '700' : '600' }};color:var(--ink-1);line-height:1.35;overflow:hidden;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;">{{ $notif['title'] }}</div>
                            @if(!$isRead)
                            <div style="width:8px;height:8px;border-radius:50%;background:#2563eb;flex-shrink:0;margin-top:5px;"></div>
                            @endif
                        </div>
                        <div style="font-size:12px;color:var(--ink-3);margin-top:2px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;">{{ $notif['body'] }}</div>
                        <div style="font-size:11px;color:var(--muted);margin-top:5px;display:flex;align-items:center;gap:4px;">
                            <i data-lucide="clock" style="width:10px;height:10px;"></i>
                            {{ $notif['time'] ? \Carbon\Carbon::parse($notif['time'])->diffForHumans() : '' }}
                        </div>
                    </div>
                </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Right: Detail Panel ─────────────────────────────────────────────── --}}
    <div id="sw-notif-detail-wrap" style="position:sticky;top:24px;">

        {{-- Empty state --}}
        <div id="sw-detail-empty" class="sw-card" style="padding:56px 24px;text-align:center;">
            <div style="width:64px;height:64px;background:linear-gradient(135deg,#eff6ff,#dbeafe);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i data-lucide="mouse-pointer-click" style="width:28px;height:28px;color:#2563eb;"></i>
            </div>
            <div style="font-size:15px;font-weight:700;color:var(--ink-1);margin-bottom:6px;">Pilih notifikasi</div>
            <div style="font-size:13px;color:var(--ink-3);line-height:1.6;">Klik salah satu notifikasi di sebelah kiri untuk melihat detail lengkapnya di sini.</div>
        </div>

        {{-- Detail card (hidden until click) --}}
        <div id="sw-detail-card" class="sw-card" style="display:none;padding:0;overflow:hidden;">

            {{-- Detail header --}}
            <div id="sw-detail-header" style="padding:20px;border-bottom:1px solid var(--surface-3);">
                <div style="display:flex;align-items:center;gap:14px;">
                    <div id="sw-detail-icon-wrap" style="width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg id="sw-detail-icon-svg" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></svg>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div id="sw-detail-title" style="font-size:15px;font-weight:800;color:var(--ink-1);line-height:1.35;"></div>
                        <div id="sw-detail-status-row" style="margin-top:5px;"></div>
                    </div>
                </div>
            </div>

            {{-- Detail body --}}
            <div style="padding:20px;">
                <div style="display:flex;flex-direction:column;gap:14px;">

                    <div style="display:flex;align-items:flex-start;gap:12px;padding:14px;background:var(--surface-2);border-radius:10px;">
                        <i data-lucide="info" style="width:16px;height:16px;color:var(--ink-3);flex-shrink:0;margin-top:1px;"></i>
                        <div>
                            <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Keterangan</div>
                            <div id="sw-detail-body" style="font-size:13px;color:var(--ink-1);line-height:1.6;"></div>
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:12px;padding:14px;background:var(--surface-2);border-radius:10px;">
                        <i data-lucide="clock" style="width:16px;height:16px;color:var(--ink-3);flex-shrink:0;"></i>
                        <div>
                            <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Waktu</div>
                            <div id="sw-detail-time" style="font-size:13px;color:var(--ink-1);font-weight:600;"></div>
                            <div id="sw-detail-time-rel" style="font-size:11.5px;color:var(--ink-3);margin-top:2px;"></div>
                        </div>
                    </div>

                </div>

                {{-- CTA --}}
                <a id="sw-detail-cta" href="#" style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:20px;padding:12px 20px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;text-decoration:none;border-radius:10px;font-size:13px;font-weight:700;transition:opacity .15s;" onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                    <i data-lucide="external-link" style="width:15px;height:15px;"></i>
                    Lihat Detail
                </a>
            </div>

        </div>
    </div>

</div>

{{-- ── Inline SVG paths (same set as topbar) ───────────────────────────────── --}}
<script>
(function () {
    var notifs = @json($notifications->values());

    var iconPaths = {
        'bell':           '<path d="M13.73 21a2 2 0 0 1-3.46 0"/><path d="M20.14 14a9 9 0 0 0 1.86-5.28A10 10 0 0 0 2 9a9 9 0 0 0 1.86 5.28L2 17h20z"/>',
        'shield-alert':   '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
        'package':        '<path d="M16.5 9.4 7.55 4.24"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.29 7 12 12 20.71 7"/><line x1="12" y1="22" x2="12" y2="12"/>',
        'activity':       '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
        'check-circle':   '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'truck':          '<rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 4v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
        'clipboard-check':'<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
        'droplets':       '<path d="M7 16.3c2.2 0 4-1.83 4-4.05 0-1.16-.57-2.26-1.71-3.19S7.29 6.75 7 5.3c-.29 1.45-1.14 2.84-2.29 3.76S3 11.1 3 12.25c0 2.22 1.8 4.05 4 4.05z"/><path d="M12.56 6.6A10.97 10.97 0 0 0 14 3.02c.5 2.5 2 4.9 4 6.5s3 3.5 3 5.5a6.98 6.98 0 0 1-11.91 4.97"/>',
        'wind':           '<path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"/>',
        'zap':            '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
        'shield-check':   '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
        'mouse-pointer-click': '<path d="m9 9 5 12 1.8-5.2L21 14Z"/><path d="M7.2 2.2 8 5.1"/><path d="m5.1 8-2.9-.8"/><path d="M14 4.1 12 6"/><path d="m6 12-1.9 2"/>',
        'external-link':  '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>',
        'info':           '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        'clock':          '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
    };

    var selected = null;

    window.swSelectNotif = function (idx) {
        var n = notifs[idx];
        if (!n) return;

        // Highlight selected item
        if (selected !== null) {
            var prev = document.getElementById('sw-notif-item-' + selected);
            if (prev) prev.style.outline = 'none';
        }
        selected = idx;
        var cur = document.getElementById('sw-notif-item-' + idx);
        if (cur) cur.style.outline = '2px solid #2563eb';

        // Show detail card
        document.getElementById('sw-detail-empty').style.display = 'none';
        var card = document.getElementById('sw-detail-card');
        card.style.display = 'block';

        // Icon
        var iconWrap = document.getElementById('sw-detail-icon-wrap');
        iconWrap.style.background = n.bg;
        var svg = document.getElementById('sw-detail-icon-svg');
        svg.setAttribute('stroke', n.color);
        svg.innerHTML = iconPaths[n.icon] || '';

        // Title
        document.getElementById('sw-detail-title').textContent = n.title;

        // Status badge
        var statusRow = document.getElementById('sw-detail-status-row');
        if (n.read) {
            statusRow.innerHTML = '<span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#059669;background:#d1fae5;padding:3px 8px;border-radius:20px;"><svg xmlns=\'http://www.w3.org/2000/svg\' width=\'10\' height=\'10\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'#059669\' stroke-width=\'2.5\'><polyline points=\'20 6 9 17 4 12\'/></svg>Sudah dibaca</span>';
        } else {
            statusRow.innerHTML = '<span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#2563eb;background:#dbeafe;padding:3px 8px;border-radius:20px;"><span style=\'width:6px;height:6px;border-radius:50%;background:#2563eb;display:inline-block;\'></span>Belum dibaca</span>';
        }

        // Body
        document.getElementById('sw-detail-body').textContent = n.body || '-';

        // Time
        if (n.time) {
            var d = new Date(n.time);
            document.getElementById('sw-detail-time').textContent = d.toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' }) + ', ' + d.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
            document.getElementById('sw-detail-time-rel').textContent = relativeTime(n.time);
        } else {
            document.getElementById('sw-detail-time').textContent = '-';
            document.getElementById('sw-detail-time-rel').textContent = '';
        }

        // CTA
        document.getElementById('sw-detail-cta').href = n.url;

        // Re-init Lucide for the detail card icons
        if (window.lucide) lucide.createIcons();
    };

    function relativeTime(dateStr) {
        var diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
        if (diff < 60)     return 'baru saja';
        if (diff < 3600)   return Math.floor(diff / 60) + ' menit lalu';
        if (diff < 86400)  return Math.floor(diff / 3600) + ' jam lalu';
        if (diff < 604800) return Math.floor(diff / 86400) + ' hari lalu';
        return new Date(dateStr).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' });
    }

    // Responsive: collapse to single column on narrow screens
    function applyLayout() {
        var grid = document.getElementById('sw-notif-grid');
        if (!grid) return;
        if (window.innerWidth < 768) {
            grid.style.gridTemplateColumns = '1fr';
            // On mobile hide detail panel initially to keep UX clean
            document.getElementById('sw-notif-detail-wrap').style.position = 'static';
        } else {
            grid.style.gridTemplateColumns = 'minmax(0,1fr) minmax(0,1fr)';
            document.getElementById('sw-notif-detail-wrap').style.position = 'sticky';
        }
    }
    applyLayout();
    window.addEventListener('resize', applyLayout);
})();
</script>

</x-layouts.sidebar>

@props(['heading' => 'SafeWash'])

@php
    $user    = auth()->user();
    $initial = strtoupper(mb_substr($user->name, 0, 1));
@endphp

<header class="sw-topbar" aria-label="Top navigation">

    {{-- Mobile: hamburger ────────────────────────────────────────────────── --}}
    <button
        class="sw-topbar-menu"
        onclick="swOpenSidebar()"
        aria-label="Buka menu"
        aria-expanded="false"
        aria-controls="sw-sidebar"
    >
        <i data-lucide="menu" style="width:20px;height:20px;"></i>
    </button>

    {{-- Mobile: compact logo ─────────────────────────────────────────────── --}}
    <a class="sw-topbar-logo" href="{{ route('home') }}" aria-label="SafeWash beranda">
        <x-brand-logo size="xs" />
    </a>

    {{-- Page title ───────────────────────────────────────────────────────── --}}
    <h1 class="sw-topbar-title">{{ $heading }}</h1>

    {{-- Search ───────────────────────────────────────────────────────────── --}}
    <div class="sw-search" role="search">
        <i data-lucide="search"></i>
        <input type="text" placeholder="Cari order, merchant, customer…" aria-label="Pencarian">
    </div>

    {{-- Right actions ────────────────────────────────────────────────────── --}}
    <div class="sw-topbar-right">

        {{-- Notification bell with dropdown --}}
        <div style="position:relative;" id="sw-notif-wrapper">
            <button
                class="sw-icon-btn"
                aria-label="Notifikasi"
                id="sw-notif-btn"
                onclick="swToggleNotif()"
                style="position:relative;"
            >
                <i data-lucide="bell"></i>
                <span id="sw-notif-dot" aria-hidden="true" style="display:none;position:absolute;top:-5px;right:-5px;min-width:18px;height:18px;border-radius:9px;background:#dc2626;color:#fff;font-size:10px;font-weight:700;align-items:center;justify-content:center;padding:0 4px;line-height:1;pointer-events:none;box-sizing:border-box;"></span>
            </button>

            {{-- Dropdown ─────────────────────────────────────────────────── --}}
            <div id="sw-notif-dropdown"
                style="display:none;position:absolute;top:calc(100% + 8px);right:0;width:380px;background:var(--surface);border:1px solid var(--surface-3);border-radius:14px;box-shadow:0 8px 32px rgba(15,31,63,.15);z-index:200;overflow:hidden;"
                role="dialog" aria-label="Panel notifikasi">

                {{-- Header --}}
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid var(--surface-3);">
                    <div style="font-size:13px;font-weight:700;color:var(--ink-1);">Notifikasi</div>
                    <a href="{{ route('notifications.index') }}" style="font-size:11.5px;color:var(--accent);font-weight:600;text-decoration:none;">Lihat Semua</a>
                </div>

                {{-- List (populated by JS) --}}
                <div id="sw-notif-list" style="max-height:480px;overflow-y:auto;">
                    <div style="text-align:center;padding:24px 16px;color:var(--ink-3);font-size:13px;">
                        <i data-lucide="loader" style="width:18px;height:18px;color:var(--ink-3);animation:spin 1s linear infinite;"></i>
                        <div style="margin-top:8px;">Memuat...</div>
                    </div>
                </div>

                {{-- Footer --}}
                <div style="padding:10px 16px;border-top:1px solid var(--surface-3);text-align:center;">
                    <a href="{{ route('notifications.index') }}" style="font-size:12px;color:var(--accent);font-weight:600;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:5px;">
                        <i data-lucide="list" style="width:12px;height:12px;"></i>
                        Semua Notifikasi
                    </a>
                </div>
            </div>
        </div>

        {{-- User identity ─────────────────────────────────────────────────── --}}
        <div class="sw-topbar-user" title="{{ $user->name }}">
            <div class="sw-topbar-avatar" aria-hidden="true">{{ $initial }}</div>
            <div style="display:flex; flex-direction:column; gap:2px;">
                <span class="sw-topbar-uname">{{ Str::limit($user->name, 18) }}</span>
                <span class="sw-topbar-urole">
                    <i data-lucide="{{ $user->isAdmin() ? 'shield' : ($user->isMerchant() ? 'store' : 'user') }}" style="width:10px;height:10px;"></i>
                    {{ $user->roleLabel() }}
                </span>
            </div>
        </div>

    </div>
</header>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
(function () {
    let loaded = false;

    window.swToggleNotif = function () {
        const dropdown = document.getElementById('sw-notif-dropdown');
        const isOpen   = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
        if (!isOpen && !loaded) { swLoadNotif(); }
    };

    function swLoadNotif() {
        loaded = true;
        fetch('{{ route('notifications.bell') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' } })
            .then(r => r.json())
            .then(data => {
                const dot  = document.getElementById('sw-notif-dot');
                const list = document.getElementById('sw-notif-list');

                if (data.unread_count > 0) {
                    dot.style.display = 'inline-flex';
                    dot.textContent   = data.unread_count > 99 ? '99+' : data.unread_count;
                }

                if (!data.notifications || data.notifications.length === 0) {
                    list.innerHTML = '<div style="text-align:center;padding:28px 16px;"><div style="width:40px;height:40px;background:var(--surface-2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;"><svg xmlns=\'http://www.w3.org/2000/svg\' width=\'18\' height=\'18\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'#9ca3af\' stroke-width=\'2\'><path d=\'M13.73 21a2 2 0 0 1-3.46 0\'/><path d=\'M20.14 14a9 9 0 0 0 1.86-5.28A10 10 0 0 0 2 9a9 9 0 0 0 1.86 5.28L2 17h20z\'/></svg></div><div style="font-size:13px;font-weight:600;color:var(--ink-2);">Belum ada notifikasi</div></div>';
                    return;
                }

                list.innerHTML = data.notifications.map(n => {
                    const timeAgo = n.time ? relativeTime(n.time) : '';
                    const unreadStyle = n.read ? '' : 'background:rgba(37,99,235,.04);';
                    const dot = n.read ? '' : '<div style="width:7px;height:7px;border-radius:50%;background:#2563eb;flex-shrink:0;margin-top:5px;"></div>';
                    return `<a href="${n.url}" style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;text-decoration:none;${unreadStyle}border-bottom:1px solid var(--surface-3);" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='${n.read ? 'transparent' : 'rgba(37,99,235,.04)'}'">
                        <div style="width:36px;height:36px;border-radius:10px;background:${n.bg};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='${n.color}' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>${iconPaths[n.icon] || ''}</svg>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:6px;">
                                <div style="font-size:12.5px;font-weight:${n.read ? 600 : 700};color:var(--ink-1);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:240px;">${n.title}</div>
                                ${dot}
                            </div>
                            <div style="font-size:11.5px;color:var(--ink-3);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;">${n.body}</div>
                            <div style="font-size:10.5px;color:var(--muted);margin-top:3px;">${timeAgo}</div>
                        </div>
                    </a>`;
                }).join('');

                if (window.lucide) { lucide.createIcons(); }
            })
            .catch(() => {
                document.getElementById('sw-notif-list').innerHTML = '<div style="text-align:center;padding:20px;font-size:13px;color:var(--ink-3);">Gagal memuat notifikasi.</div>';
            });
    }

    function relativeTime(dateStr) {
        const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
        if (diff < 60)    return 'baru saja';
        if (diff < 3600)  return Math.floor(diff / 60) + ' menit lalu';
        if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
        if (diff < 604800)return Math.floor(diff / 86400) + ' hari lalu';
        return new Date(dateStr).toLocaleDateString('id-ID', { day:'numeric', month:'short' });
    }

    // Minimal inline SVG paths for common lucide icons used in notifications
    const iconPaths = {
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
        'loader':         '<line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"/><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"/>',
    };

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        const wrapper = document.getElementById('sw-notif-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            const d = document.getElementById('sw-notif-dropdown');
            if (d) d.style.display = 'none';
        }
    });

    // Auto-load unread count on page load
    document.addEventListener('DOMContentLoaded', function () {
        fetch('{{ route('notifications.bell') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (data.unread_count > 0) {
                    const dot = document.getElementById('sw-notif-dot');
                    if (dot) { dot.style.display = 'inline-flex'; dot.textContent = data.unread_count > 99 ? '99+' : data.unread_count; }
                }
            })
            .catch(() => {});
    });
})();
</script>

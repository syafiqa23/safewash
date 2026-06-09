<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SafeWash' }}</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 37'%3E%3Cdefs%3E%3ClinearGradient id='d' x1='6' y1='2' x2='26' y2='35' gradientUnits='userSpaceOnUse'%3E%3Cstop offset='0%25' stop-color='%232563EB'/%3E%3Cstop offset='100%25' stop-color='%231D4ED8'/%3E%3C/linearGradient%3E%3C/defs%3E%3Cpath d='M16 1.5C13.5 5 6 11.5 6 21c0 7.6 4.5 14 10 14s10-6.4 10-14c0-9.5-7.5-16-10-19.5Z' fill='url(%23d)'/%3E%3Cpath d='M16 10.5 11.5 13v7.5c0 4.5 2 8 4.5 9.5 2.5-1.5 4.5-5 4.5-9.5V13Z' fill='none' stroke='rgba(255,255,255,0.52)' stroke-width='1' stroke-linejoin='round'/%3E%3Cpath d='M13.5 21 15.5 23.5 20 17.5' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</head>
<body>
<div class="pub-wrap">

    {{-- Navigation ─────────────────────────────────────────────────────── --}}
    <header class="pub-nav">
        <div class="pub-container">
            <div class="pub-nav-inner">
                <a href="{{ route('home') }}" class="pub-nav-brand" aria-label="SafeWash beranda">
                    <x-brand-logo size="sm" />
                </a>

                <nav class="pub-nav-links" aria-label="Navigasi publik">
                    <a href="{{ route('home') }}" class="pub-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
                    <a href="{{ auth()->check() ? route('orders.index') : route('home').'#tracking' }}"
                       class="pub-nav-link">Tracking</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="pub-nav-link">Dashboard</a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="pub-nav-link">Analytics</a>
                            <a href="{{ route('admin.merchants.index') }}" class="pub-nav-link">Merchant</a>
                        @endif
                    @endauth
                </nav>

                <div class="pub-nav-actions">
                    @auth
                        <span style="font-size:13px; font-weight:600; color:var(--muted); padding:0 4px;" class="pub-nav-label">
                            {{ auth()->user()->name }}
                        </span>
                        <a href="{{ route('dashboard') }}" class="pub-btn pub-btn-ghost pub-nav-label">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                            @csrf
                            <button type="submit" class="pub-btn pub-btn-ghost">Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="pub-btn pub-btn-ghost">Masuk</a>
                        <a href="{{ route('register') }}" class="pub-btn pub-btn-primary">Daftar Gratis</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    {{-- Flash messages ──────────────────────────────────────────────────── --}}
    @if (session('success') || $errors->any())
        <div class="pub-container" style="padding-top:16px;">
            @if (session('success'))
                <div class="pub-flash success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="pub-flash error">
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Page content ────────────────────────────────────────────────────── --}}
    <main class="pub-main">
        {{ $slot }}
    </main>

    {{-- Footer ──────────────────────────────────────────────────────────── --}}
    <footer class="pub-footer">
        <div class="pub-container">
            <div class="pub-footer-inner">
                <span class="pub-footer-copy">© {{ date('Y') }} SafeWash. Platform laundry digital untuk merchant dan customer.</span>
                <div class="pub-footer-links">
                    <a href="{{ route('home') }}">Beranda</a>
                    <a href="{{ route('login') }}">Masuk</a>
                    <a href="{{ route('register') }}">Daftar</a>
                </div>
            </div>
        </div>
    </footer>

</div>

{{-- Toast stack ─────────────────────────────────────────────────────────── --}}
<div class="toast-stack" id="toastStack"></div>

<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>
    if (window.lucide) { lucide.createIcons(); }
</script>

<script>
    window.showToast = function ({ title = 'Notifikasi', message = '', type = 'success' }) {
        const stack = document.getElementById('toastStack');
        if (!stack) return;
        const el = document.createElement('div');
        el.className = 'toast-item ' + type;
        el.innerHTML = '<strong>' + title + '</strong><div>' + message + '</div>';
        stack.appendChild(el);
        requestAnimationFrame(() => el.classList.add('show'));
        setTimeout(() => {
            el.classList.remove('show');
            setTimeout(() => el.remove(), 220);
        }, 3000);
    };
</script>
</body>
</html>

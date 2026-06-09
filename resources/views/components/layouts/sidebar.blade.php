@props([
    'title'   => 'SafeWash',
    'heading' => null,
])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — SafeWash</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 37'%3E%3Cdefs%3E%3ClinearGradient id='d' x1='6' y1='2' x2='26' y2='35' gradientUnits='userSpaceOnUse'%3E%3Cstop offset='0%25' stop-color='%235BBEF5'/%3E%3Cstop offset='100%25' stop-color='%231A6EC5'/%3E%3C/linearGradient%3E%3C/defs%3E%3Cpath d='M16 1.5C13.5 5 6 11.5 6 21c0 7.6 4.5 14 10 14s10-6.4 10-14c0-9.5-7.5-16-10-19.5Z' fill='url(%23d)'/%3E%3Cpath d='M16 10.5 11.5 13v7.5c0 4.5 2 8 4.5 9.5 2.5-1.5 4.5-5 4.5-9.5V13Z' fill='none' stroke='rgba(255,255,255,0.52)' stroke-width='1' stroke-linejoin='round'/%3E%3Cpath d='M13.5 21 15.5 23.5 20 17.5' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</head>
<body>

    {{-- Mobile backdrop ─────────────────────────────────────────────────────── --}}
    <div id="sw-backdrop"
        style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.5); z-index:40; backdrop-filter:blur(2px);"
        aria-hidden="true">
    </div>

    <div class="sw-shell">

        <x-nav.sidebar />

        <div class="sw-main">

            <x-nav.topbar :heading="$heading ?? $title" />

            @if (session('success'))
                <div class="sw-flash success">
                    <i data-lucide="check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="sw-flash error" style="flex-direction:column; align-items:flex-start; gap:4px;">
                    @foreach ($errors->all() as $error)
                        <div style="display:flex; align-items:center; gap:8px;">
                            <i data-lucide="alert-circle" style="width:14px;height:14px;flex-shrink:0;"></i>
                            {{ $error }}
                        </div>
                    @endforeach
                </div>
            @endif

            <main class="sw-content" id="sw-content">
                {{ $slot }}
            </main>

        </div>
    </div>

    <div class="sw-toast-stack" id="swToastStack"></div>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script>
        if (window.lucide) { lucide.createIcons(); }
    </script>

    <script>
        window.showToast = function ({ title = 'Notifikasi', message = '', type = 'success' }) {
            const stack = document.getElementById('swToastStack');
            if (!stack) return;
            const toast = document.createElement('div');
            toast.className = 'sw-toast ' + type;
            toast.innerHTML = '<strong>' + title + '</strong><div>' + message + '</div>';
            stack.appendChild(toast);
            requestAnimationFrame(() => toast.classList.add('show'));
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 220);
            }, 3000);
        };
        window.swOpenSidebar = function () {
            document.getElementById('sw-sidebar')?.classList.add('is-open');
            const bd = document.getElementById('sw-backdrop');
            if (bd) bd.style.display = 'block';
            document.body.style.overflow = 'hidden';
        };
        window.swCloseSidebar = function () {
            document.getElementById('sw-sidebar')?.classList.remove('is-open');
            const bd = document.getElementById('sw-backdrop');
            if (bd) bd.style.display = 'none';
            document.body.style.overflow = '';
        };
        document.getElementById('sw-backdrop')?.addEventListener('click', window.swCloseSidebar);
    </script>
</body>
</html>

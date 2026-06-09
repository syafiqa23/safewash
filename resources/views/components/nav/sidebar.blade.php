@php
    $user    = auth()->user();
    $initial = strtoupper(mb_substr($user->name, 0, 1));
    $isActive = fn (string|array $routes): string =>
        request()->routeIs($routes) ? 'active' : '';
@endphp

<aside id="sw-sidebar" class="sw-sidebar" aria-label="Navigasi utama">

    {{-- Logo + mobile close --}}
    <div class="sw-sidebar-logo">
        <a href="{{ route('home') }}" aria-label="SafeWash beranda">
            <x-brand-logo size="sm" variant="dark" />
        </a>
        <button id="sw-sidebar-close" onclick="swCloseSidebar()" class="sw-sidebar-close-btn" aria-label="Tutup sidebar">
            <i data-lucide="x" style="width:18px;height:18px;"></i>
        </button>
    </div>

    <nav class="sw-sidebar-nav" aria-label="Menu utama">

        {{-- ══════════════════════ ADMIN ══════════════════════ --}}
        @if ($user->isAdmin())

            <a href="{{ route('admin.dashboard') }}" class="sw-nav-link {{ $isActive('admin.dashboard') }}">
                <i data-lucide="home" class="sw-nav-icon"></i><span>Dashboard Platform</span>
            </a>

            <p class="sw-sidebar-section">Manajemen</p>
            <a href="{{ route('admin.merchants.index') }}" class="sw-nav-link {{ $isActive('admin.merchants.*') }}">
                <i data-lucide="store" class="sw-nav-icon"></i><span>Merchant Management</span>
            </a>
            <a href="{{ route('admin.customers.index') }}" class="sw-nav-link {{ $isActive('admin.customers.*') }}">
                <i data-lucide="users" class="sw-nav-icon"></i><span>Customer Management</span>
            </a>

            <p class="sw-sidebar-section">Operasional</p>
            <a href="{{ route('orders.index') }}" class="sw-nav-link {{ $isActive('orders.*') }}">
                <i data-lucide="package" class="sw-nav-icon"></i><span>Order Monitoring</span>
            </a>
            <a href="{{ route('admin.claims.index') }}" class="sw-nav-link {{ $isActive('admin.claims.*') }}">
                <i data-lucide="shield-alert" class="sw-nav-icon"></i><span>Claim Center</span>
            </a>
            <a href="{{ route('admin.payments.index') }}" class="sw-nav-link {{ $isActive('admin.payments.*') }}">
                <i data-lucide="credit-card" class="sw-nav-icon"></i><span>Payment Monitoring</span>
            </a>
            <a href="{{ route('admin.settlement.index') }}" class="sw-nav-link {{ $isActive('admin.settlement.*') }}">
                <i data-lucide="banknote" class="sw-nav-icon"></i><span>Settlement Merchant</span>
            </a>

            <p class="sw-sidebar-section">Sistem</p>
            <a href="{{ route('admin.reports.index') }}" class="sw-nav-link {{ $isActive('admin.reports.*') }}">
                <i data-lucide="file-bar-chart" class="sw-nav-icon"></i><span>Laporan</span>
            </a>
            <a href="{{ route('admin.integrations.index') }}" class="sw-nav-link {{ $isActive('admin.integrations.*') }}">
                <i data-lucide="link-2" class="sw-nav-icon"></i><span>Integrasi</span>
            </a>
        {{-- ══════════════════════ MERCHANT ══════════════════════ --}}
        @elseif ($user->isMerchant())

            <a href="{{ route('dashboard') }}" class="sw-nav-link {{ $isActive('dashboard') }}">
                <i data-lucide="home" class="sw-nav-icon"></i><span>Dashboard</span>
            </a>

            <p class="sw-sidebar-section">Order</p>
            <a href="{{ route('orders.create') }}" class="sw-nav-link {{ $isActive('orders.create') }}">
                <i data-lucide="package-plus" class="sw-nav-icon"></i><span>Order Baru</span>
            </a>
            <a href="{{ route('orders.index') }}" class="sw-nav-link {{ $isActive('orders.index') && !request()->boolean('active') && !request()->boolean('delivery') ? 'active' : '' }}">
                <i data-lucide="package-check" class="sw-nav-icon"></i><span>Order Diproses</span>
            </a>
            <a href="{{ route('orders.index', ['active' => 1]) }}" class="sw-nav-link {{ request()->boolean('active') ? 'active' : '' }}">
                <i data-lucide="activity" class="sw-nav-icon"></i><span>Order Aktif</span>
            </a>

            <p class="sw-sidebar-section">Operasional</p>
            <a href="{{ route('orders.index', ['delivery' => 1]) }}" class="sw-nav-link {{ request()->boolean('delivery') ? 'active' : '' }}">
                <i data-lucide="truck" class="sw-nav-icon"></i><span>Pickup Delivery</span>
            </a>
            <a href="{{ route('merchant.qr.index') }}" class="sw-nav-link {{ $isActive('merchant.qr.*') }}">
                <i data-lucide="qr-code" class="sw-nav-icon"></i><span>QR Tracking</span>
            </a>
            <a href="{{ route('merchant.customers.index') }}" class="sw-nav-link {{ $isActive('merchant.customers.*') }}">
                <i data-lucide="users" class="sw-nav-icon"></i><span>Pelanggan</span>
            </a>
            <a href="{{ route('claims.index') }}" class="sw-nav-link {{ $isActive('claims.*') }}">
                <i data-lucide="shield-alert" class="sw-nav-icon"></i><span>Klaim</span>
            </a>

            <p class="sw-sidebar-section">Keuangan</p>
            <a href="{{ route('merchant.revenue.index') }}" class="sw-nav-link {{ $isActive('merchant.revenue.*') }}">
                <i data-lucide="wallet" class="sw-nav-icon"></i><span>Pendapatan</span>
            </a>

            <p class="sw-sidebar-section">Akun</p>
            <a href="{{ route('profile.index') }}" class="sw-nav-link {{ $isActive('profile.*') }}">
                <i data-lucide="user" class="sw-nav-icon"></i><span>Profil</span>
            </a>

        {{-- ══════════════════════ CUSTOMER ══════════════════════ --}}
        @else

            <a href="{{ route('dashboard') }}" class="sw-nav-link {{ $isActive('dashboard') }}">
                <i data-lucide="home" class="sw-nav-icon"></i><span>Dashboard</span>
            </a>

            <p class="sw-sidebar-section">Temukan Laundry</p>
            <a href="{{ route('marketplace.index') }}" class="sw-nav-link {{ $isActive('marketplace.*') }}">
                <i data-lucide="store" class="sw-nav-icon"></i><span>Marketplace Merchant</span>
            </a>

            <p class="sw-sidebar-section">Pesanan</p>
            <a href="{{ route('orders.index') }}" class="sw-nav-link {{ $isActive('orders.index') }}">
                <i data-lucide="package" class="sw-nav-icon"></i><span>Pesanan Saya</span>
            </a>
            <a href="{{ route('customer.tracking') }}" class="sw-nav-link {{ $isActive('customer.tracking') }}">
                <i data-lucide="qr-code" class="sw-nav-icon"></i><span>Tracking QR</span>
            </a>

            <p class="sw-sidebar-section">Program</p>
            <a href="{{ route('loyalty.index') }}" class="sw-nav-link {{ $isActive('loyalty.*') }}">
                <i data-lucide="gift" class="sw-nav-icon"></i><span>Loyalty</span>
            </a>
            <a href="{{ route('claims.index') }}" class="sw-nav-link {{ $isActive('claims.index') }}">
                <i data-lucide="shield-alert" class="sw-nav-icon"></i><span>Klaim</span>
            </a>

            <p class="sw-sidebar-section">Akun</p>
            <a href="{{ route('profile.index') }}" class="sw-nav-link {{ $isActive('profile.*') }}">
                <i data-lucide="user" class="sw-nav-icon"></i><span>Profil</span>
            </a>

        @endif

    </nav>

    {{-- User footer --}}
    <div class="sw-sidebar-footer">
        <div class="sw-user-row">
            <div class="sw-avatar" aria-hidden="true">{{ $initial }}</div>
            <div class="sw-min-w-0 sw-flex-1">
                <div class="sw-user-name">{{ $user->name }}</div>
                <div class="sw-user-role">{{ $user->roleLabel() }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sw-logout-btn">
                <i data-lucide="log-out"></i><span>Keluar</span>
            </button>
        </form>
    </div>

</aside>

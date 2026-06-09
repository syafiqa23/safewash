<x-layouts.app :title="'Masuk — SafeWash'">

<div class="auth-page">
    <div class="auth-box">

        {{-- Brand --}}
        <div class="auth-brand">
            <a href="{{ route('home') }}"><x-brand-logo size="md" :stacked="true" /></a>
            <span class="auth-brand-tagline">Platform manajemen laundry digital</span>
        </div>

        {{-- Login card --}}
        <div class="auth-card">
            <h1 class="auth-card-title">Masuk ke SafeWash</h1>
            <p class="auth-card-sub">Masukkan email dan password akun Anda.</p>

            <form method="POST" action="{{ route('login.perform') }}" class="auth-form">
                @csrf

                <label class="auth-label">
                    Email
                    <input type="email" name="email" class="auth-input"
                        value="{{ old('email', app()->environment('local') ? 'admin@safewash.test' : '') }}"
                        required autocomplete="email" autofocus>
                </label>

                <label class="auth-label">
                    Password
                    <input type="password" name="password" class="auth-input"
                        required autocomplete="current-password">
                </label>

                <label class="auth-check">
                    <input type="checkbox" name="remember">
                    Ingat saya di perangkat ini
                </label>

                <button type="submit" class="auth-submit">Masuk</button>
            </form>

            @if (app()->environment('local'))
                <div class="auth-divider">Demo credentials</div>
                <div class="auth-demo">
                    <strong>Admin:</strong> admin@safewash.test / password<br>
                    <strong>Merchant:</strong> merchant@safewash.test / password<br>
                    <strong>Customer:</strong> customer@safewash.test / password
                </div>
            @endif
        </div>

        <p class="auth-footer">
            Belum punya akun?
            <a href="{{ route('register') }}">Daftar sekarang — gratis</a>
        </p>

    </div>
</div>

</x-layouts.app>

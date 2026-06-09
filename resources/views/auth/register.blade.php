<x-layouts.app :title="'Daftar — SafeWash'">

<div class="auth-page">
    <div class="auth-box">

        {{-- Brand --}}
        <div class="auth-brand">
            <a href="{{ route('home') }}"><x-brand-logo size="md" :stacked="true" /></a>
            <span class="auth-brand-tagline">Platform manajemen laundry digital</span>
        </div>

        {{-- Register card --}}
        <div class="auth-card">
            <h1 class="auth-card-title">Buat akun SafeWash</h1>
            <p class="auth-card-sub">Merchant daftar gratis. Komisi 15% + service fee 3% per order berhasil.</p>

            <div class="auth-info">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" style="flex-shrink:0; margin-top:1px;"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                <span><strong>Gratis selamanya untuk merchant.</strong> QR tracking, notifikasi WhatsApp, loyalty pelanggan, dan pickup-delivery dalam satu platform.</span>
            </div>

            <form method="POST" action="{{ route('register.perform') }}" class="auth-form">
                @csrf

                <div class="auth-row">
                    <label class="auth-label">
                        Nama Lengkap
                        <input type="text" name="name" class="auth-input"
                            value="{{ old('name') }}" required autocomplete="name">
                    </label>
                    <label class="auth-label">
                        No. HP
                        <input type="text" name="phone" class="auth-input"
                            value="{{ old('phone') }}" placeholder="+628...">
                    </label>
                </div>

                <label class="auth-label">
                    Email
                    <input type="email" name="email" class="auth-input"
                        value="{{ old('email') }}" required autocomplete="email">
                </label>

                <div>
                    <div style="font-size:13px; font-weight:600; color:var(--ink-2); margin-bottom:8px;">Daftar sebagai</div>
                    <div class="auth-role-grid">
                        <label class="auth-role-opt">
                            <input type="radio" name="role" value="merchant"
                                {{ old('role', 'merchant') === 'merchant' ? 'checked' : '' }}>
                            <div>
                                <div class="auth-role-opt-label">Merchant Laundry</div>
                                <div class="auth-role-opt-sub">Kelola outlet & order</div>
                            </div>
                        </label>
                        <label class="auth-role-opt">
                            <input type="radio" name="role" value="customer"
                                {{ old('role') === 'customer' ? 'checked' : '' }}>
                            <div>
                                <div class="auth-role-opt-label">Customer</div>
                                <div class="auth-role-opt-sub">Pantau order laundry</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="auth-row">
                    <label class="auth-label">
                        Password
                        <input type="password" name="password" class="auth-input"
                            required autocomplete="new-password">
                    </label>
                    <label class="auth-label">
                        Konfirmasi Password
                        <input type="password" name="password_confirmation" class="auth-input"
                            required autocomplete="new-password">
                    </label>
                </div>

                <button type="submit" class="auth-submit">Buat Akun Gratis</button>
            </form>
        </div>

        <p class="auth-footer">
            Sudah punya akun?
            <a href="{{ route('login') }}">Masuk di sini</a>
        </p>

    </div>
</div>

</x-layouts.app>

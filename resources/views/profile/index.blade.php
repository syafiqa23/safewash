<x-layouts.sidebar title="Profil Saya" heading="Profil Saya">

@if (session('success'))
    <div class="sw-alert-success sw-mb-16">{{ session('success') }}</div>
@endif

<div style="display:grid;grid-template-columns:260px 1fr;gap:20px;align-items:start;">

    {{-- Avatar card --}}
    <div class="sw-card" style="text-align:center;">
        <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#1d4ed8);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:32px;font-weight:800;color:white;">
            {{ strtoupper(mb_substr($user->name, 0, 1)) }}
        </div>
        <div style="font-size:17px;font-weight:700;color:var(--ink-1);">{{ $user->name }}</div>
        <div style="font-size:12px;color:var(--ink-3);margin-top:2px;">{{ $user->email }}</div>
        <div style="margin-top:8px;">
            <span style="background:{{ $user->isAdmin() ? '#fef3c7' : ($user->isMerchant() ? '#dbeafe' : '#d1fae5') }};color:{{ $user->isAdmin() ? '#92400e' : ($user->isMerchant() ? '#1d4ed8' : '#065f46') }};font-size:12px;font-weight:600;padding:3px 10px;border-radius:20px;">
                {{ $user->roleLabel() }}
            </span>
        </div>

        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--surface-3);text-align:left;">
            <div style="font-size:12px;color:var(--ink-3);margin-bottom:6px;">Info Akun</div>
            @if ($user->phone)
                <div style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--ink-2);margin-bottom:4px;">
                    <i data-lucide="phone" style="width:13px;height:13px;"></i>
                    {{ $user->phone }}
                </div>
            @endif
            @if ($user->city)
                <div style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--ink-2);margin-bottom:4px;">
                    <i data-lucide="map-pin" style="width:13px;height:13px;"></i>
                    {{ $user->city }}
                </div>
            @endif
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--ink-3);margin-top:8px;">
                <i data-lucide="calendar" style="width:12px;height:12px;"></i>
                Bergabung {{ $user->created_at->format('d M Y') }}
            </div>
        </div>
    </div>

    {{-- Form --}}
    <div class="sw-card">
        <x-ui.section-header title="Edit Profil" icon="user" />

        <form method="POST" action="{{ route('profile.update') }}" class="sw-form">
            @csrf

            <div class="sw-grid sw-grid-2 sw-gap-14">
                <div class="sw-form-group">
                    <label class="sw-label">Nama Lengkap *</label>
                    <input type="text" name="name" class="sw-input @error('name') sw-input-error @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="sw-error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="sw-form-group">
                    <label class="sw-label">Email</label>
                    <input type="email" class="sw-input" value="{{ $user->email }}" disabled style="opacity:.6;cursor:not-allowed;">
                    <div style="font-size:11px;color:var(--ink-3);margin-top:2px;">Email tidak dapat diubah.</div>
                </div>
                <div class="sw-form-group">
                    <label class="sw-label">Nomor HP</label>
                    <input type="text" name="phone" class="sw-input" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx">
                </div>
                <div class="sw-form-group">
                    <label class="sw-label">Kota</label>
                    <input type="text" name="city" class="sw-input" value="{{ old('city', $user->city) }}" placeholder="Jakarta, Surabaya, ...">
                </div>
            </div>

            <div class="sw-form-group">
                <label class="sw-label">Alamat</label>
                <textarea name="address" class="sw-input" rows="2" placeholder="Alamat lengkap...">{{ old('address', $user->address) }}</textarea>
            </div>

            <div style="border-top:1px solid var(--surface-3);margin:20px 0 16px;"></div>
            <div style="font-size:13px;font-weight:600;color:var(--ink-1);margin-bottom:12px;">Ganti Password (opsional)</div>

            <div class="sw-grid sw-grid-2 sw-gap-14">
                <div class="sw-form-group">
                    <label class="sw-label">Password Baru</label>
                    <input type="password" name="password" class="sw-input @error('password') sw-input-error @enderror" placeholder="Kosongkan jika tidak ingin ganti">
                    @error('password')<div class="sw-error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="sw-form-group">
                    <label class="sw-label">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="sw-input" placeholder="Ulangi password baru">
                </div>
            </div>

            <div class="sw-save-bar">
                <button type="submit" class="sw-btn sw-btn-primary">
                    <i data-lucide="save" style="width:15px;height:15px;"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

</x-layouts.sidebar>

<x-layouts.sidebar title="Ajukan Klaim" heading="Ajukan Klaim">

<div class="sw-card sw-mb-20" style="background:#fef2f2;border:1px solid #fecaca;">
    <div style="display:flex;align-items:center;gap:12px;">
        <i data-lucide="shield-alert" style="width:28px;height:28px;color:#dc2626;flex-shrink:0;"></i>
        <div>
            <div style="font-size:15px;font-weight:700;color:#7f1d1d;">Klaim untuk Order {{ $order->tracking_code }}</div>
            <div style="font-size:13px;color:#991b1b;">{{ $order->laundry->name }} · {{ $order->customer_name }}</div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('claims.store', $order) }}" enctype="multipart/form-data">
    @csrf

    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

        <div>
            <div class="sw-card sw-mb-16">
                <x-ui.section-header title="Data Pelapor" icon="user" />
                <div class="sw-grid sw-grid-2 sw-gap-14">
                    <div class="sw-form-group">
                        <label class="sw-label">Nama Pelapor *</label>
                        <input type="text" name="claimant_name" class="sw-input @error('claimant_name') sw-input-error @enderror"
                            value="{{ old('claimant_name', auth()->user()->name) }}" required>
                        @error('claimant_name')<div class="sw-error-msg">{{ $message }}</div>@enderror
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Kontak (HP / Email) *</label>
                        <input type="text" name="claimant_contact" class="sw-input @error('claimant_contact') sw-input-error @enderror"
                            value="{{ old('claimant_contact', auth()->user()->phone ?? auth()->user()->email) }}" required>
                        @error('claimant_contact')<div class="sw-error-msg">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="sw-card sw-mb-16">
                <x-ui.section-header title="Detail Klaim" icon="shield-alert" />

                <div class="sw-form-group">
                    <label class="sw-label">Jenis Klaim *</label>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
                        @foreach (\App\Models\Claim::TYPE_LABELS as $val => $lbl)
                            <label style="display:flex;align-items:center;gap:8px;padding:12px;border:2px solid {{ old('claim_type') === $val ? 'var(--accent)' : 'var(--surface-3)' }};border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">
                                <input type="radio" name="claim_type" value="{{ $val }}" {{ old('claim_type','hilang') === $val ? 'checked' : '' }} style="accent-color:var(--accent);" required>
                                {{ $lbl }}
                            </label>
                        @endforeach
                    </div>
                    @error('claim_type')<div class="sw-error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="sw-form-group">
                    <label class="sw-label">Nama Barang yang Diklaim *</label>
                    <input type="text" name="item_name" class="sw-input @error('item_name') sw-input-error @enderror"
                        value="{{ old('item_name') }}" required placeholder="Kemeja putih, jaket kulit, ...">
                    @error('item_name')<div class="sw-error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="sw-form-group">
                    <label class="sw-label">Deskripsi Kejadian *</label>
                    <textarea name="description" class="sw-input @error('description') sw-input-error @enderror" rows="4" required
                        placeholder="Jelaskan kronologi, ciri-ciri barang, dan bukti yang dimiliki...">{{ old('description') }}</textarea>
                    @error('description')<div class="sw-error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="sw-form-group">
                    <label class="sw-label">Estimasi Nominal Kerugian (Rp)</label>
                    <input type="number" name="loss_amount" class="sw-input" value="{{ old('loss_amount','0') }}" min="0"
                        placeholder="Nominal kerugian yang diperkirakan">
                </div>

                <div class="sw-form-group">
                    <label class="sw-label">Foto Bukti <span class="sw-muted">(JPG/PNG, maks 4MB)</span></label>
                    <input type="file" name="photo" class="sw-input" accept="image/*">
                    @error('photo')<div class="sw-error-msg">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Right: ringkasan + submit --}}
        <div style="position:sticky;top:80px;">
            <div class="sw-card sw-mb-16" style="background:var(--surface-2);">
                <div style="font-size:13px;font-weight:600;color:var(--ink-1);margin-bottom:10px;">Ringkasan Order</div>
                <div style="font-size:13px;color:var(--ink-2);display:flex;flex-direction:column;gap:6px;">
                    <div><span class="sw-muted">Tracking:</span> <span class="sw-font-mono sw-fw-600">{{ $order->tracking_code }}</span></div>
                    <div><span class="sw-muted">Merchant:</span> {{ $order->laundry->name }}</div>
                    <div><span class="sw-muted">Layanan:</span> {{ $order->service_type }}</div>
                    <div><span class="sw-muted">Total:</span> Rp{{ number_format($order->total_price,0,',','.') }}</div>
                    <div><span class="sw-muted">Status:</span> <x-ui.status-badge :status="$order->status" size="sm" /></div>
                </div>
            </div>

            <div class="sw-card" style="background:#fef2f2;border:1px solid #fecaca;">
                <div style="font-size:12px;color:#7f1d1d;margin-bottom:12px;">
                    Klaim akan ditinjau dalam <strong>1×24 jam</strong>. Pastikan informasi yang diisi akurat dan lengkap.
                </div>
                <button type="submit" class="sw-btn sw-w-full" style="background:#dc2626;color:white;justify-content:center;padding:10px;">
                    <i data-lucide="send" style="width:15px;height:15px;"></i>
                    Kirim Klaim
                </button>
                <a href="{{ route('orders.show', $order) }}" class="sw-btn sw-btn-ghost sw-w-full sw-mt-4" style="justify-content:center;">Batal</a>
            </div>
        </div>

    </div>
</form>

</x-layouts.sidebar>

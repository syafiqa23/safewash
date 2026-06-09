<x-layouts.sidebar title="Order Baru" heading="Order Baru">

@if (session('error'))
    <div class="sw-alert-error sw-mb-16">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('orders.store') }}" class="sw-form">
    @csrf

    <div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;">

        {{-- Left: Data Order --}}
        <div>
            {{-- Pilih Merchant --}}
            <div class="sw-card sw-mb-16">
                <x-ui.section-header title="Informasi Laundry" icon="store" />
                <div class="sw-form-group">
                    <label class="sw-label">Pilih Outlet Laundry *</label>
                    <select name="laundry_id" class="sw-input @error('laundry_id') sw-input-error @enderror" required>
                        <option value="">— Pilih Outlet —</option>
                        @foreach ($laundries as $laundry)
                            <option value="{{ $laundry->id }}" {{ old('laundry_id') == $laundry->id ? 'selected' : '' }}>
                                {{ $laundry->name }}{{ $laundry->city ? ' ('.$laundry->city.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('laundry_id')<div class="sw-error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Data Customer --}}
            <div class="sw-card sw-mb-16">
                <x-ui.section-header title="Data Customer" icon="user" />
                <div class="sw-grid sw-grid-2 sw-gap-14">
                    <div class="sw-form-group">
                        <label class="sw-label">Nama Pelanggan *</label>
                        <input type="text" name="customer_name" class="sw-input @error('customer_name') sw-input-error @enderror" value="{{ old('customer_name') }}" required placeholder="Nama lengkap">
                        @error('customer_name')<div class="sw-error-msg">{{ $message }}</div>@enderror
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">No. HP *</label>
                        <input type="text" name="customer_phone" class="sw-input @error('customer_phone') sw-input-error @enderror" value="{{ old('customer_phone') }}" required placeholder="08xxxxxxxxxx">
                        @error('customer_phone')<div class="sw-error-msg">{{ $message }}</div>@enderror
                    </div>
                    <div class="sw-form-group sw-col-span-2">
                        <label class="sw-label">Email Pelanggan <span class="sw-muted">(opsional — untuk notifikasi & tracking)</span></label>
                        <input type="email" name="customer_email" class="sw-input" value="{{ old('customer_email') }}" placeholder="customer@email.com">
                    </div>
                </div>
            </div>

            {{-- Detail Laundry --}}
            <div class="sw-card sw-mb-16">
                <x-ui.section-header title="Detail Order" icon="package" />
                <div class="sw-grid sw-grid-2 sw-gap-14">
                    <div class="sw-form-group">
                        <label class="sw-label">Jenis Layanan *</label>
                        <select name="service_type" class="sw-input" required>
                            <option value="">— Pilih Layanan —</option>
                            @foreach (['Cuci Reguler','Cuci + Setrika','Dry Cleaning','Setrika Saja','Express','Karpet','Bed Cover','Sepatu'] as $svc)
                                <option value="{{ $svc }}" {{ old('service_type') === $svc ? 'selected' : '' }}>{{ $svc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Berat (kg) *</label>
                        <input type="number" name="weight_kg" class="sw-input" value="{{ old('weight_kg','1') }}" min="0.1" step="0.1" required>
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Total Harga (Rp) *</label>
                        <input type="number" name="total_price" class="sw-input" value="{{ old('total_price') }}" min="0" required placeholder="25000">
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Estimasi Selesai</label>
                        <input type="datetime-local" name="promised_at" class="sw-input" value="{{ old('promised_at') }}">
                    </div>
                </div>

                <div class="sw-form-group">
                    <label class="sw-label">Daftar Item <span class="sw-muted">(satu per baris, format: Nama Barang | Jumlah)</span></label>
                    <textarea name="items" class="sw-input" rows="3" placeholder="Kemeja|3&#10;Celana|2&#10;Jaket|1">{{ old('items') }}</textarea>
                </div>

                <div class="sw-form-group">
                    <label class="sw-label">Catatan Tambahan</label>
                    <textarea name="notes" class="sw-input" rows="2" placeholder="Instruksi khusus...">{{ old('notes') }}</textarea>
                </div>
            </div>

            {{-- Pickup Delivery --}}
            <div class="sw-card sw-mb-16">
                <x-ui.section-header title="Pickup & Delivery" icon="truck" />
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:12px;">
                    <input type="checkbox" name="pickup_delivery_opt_in" value="1" id="pdo" {{ old('pickup_delivery_opt_in') ? 'checked' : '' }} onchange="document.getElementById('pdo-fields').style.display=this.checked?'block':'none'" style="accent-color:var(--accent);width:16px;height:16px;">
                    <span class="sw-fw-600 sw-text-sm">Aktifkan Pickup / Delivery</span>
                </label>

                <div id="pdo-fields" style="{{ old('pickup_delivery_opt_in') ? '' : 'display:none;' }}">
                    <div class="sw-grid sw-grid-2 sw-gap-14">
                        <div class="sw-form-group">
                            <label class="sw-label">Tipe Layanan</label>
                            <select name="pickup_delivery_type" class="sw-input">
                                <option value="pickup">Pickup Only</option>
                                <option value="delivery">Delivery Only</option>
                                <option value="round_trip" selected>Round Trip</option>
                            </select>
                        </div>
                        <div class="sw-form-group">
                            <label class="sw-label">Biaya Delivery (Rp)</label>
                            <input type="number" name="pickup_delivery_fee" class="sw-input" value="{{ old('pickup_delivery_fee','0') }}" min="0">
                        </div>
                        <div class="sw-form-group">
                            <label class="sw-label">Alamat Pickup</label>
                            <input type="text" name="pickup_address" class="sw-input" value="{{ old('pickup_address') }}" placeholder="Alamat pickup customer">
                        </div>
                        <div class="sw-form-group">
                            <label class="sw-label">Alamat Delivery</label>
                            <input type="text" name="delivery_address" class="sw-input" value="{{ old('delivery_address') }}" placeholder="Alamat pengiriman">
                        </div>
                        <div class="sw-form-group">
                            <label class="sw-label">Jadwal Pickup</label>
                            <input type="datetime-local" name="pickup_scheduled_at" class="sw-input" value="{{ old('pickup_scheduled_at') }}">
                        </div>
                        <div class="sw-form-group">
                            <label class="sw-label">Jadwal Delivery</label>
                            <input type="datetime-local" name="delivery_scheduled_at" class="sw-input" value="{{ old('delivery_scheduled_at') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Pembayaran + Submit --}}
        <div style="position:sticky;top:80px;">
            <div class="sw-card sw-mb-16">
                <x-ui.section-header title="Pembayaran" icon="credit-card" />
                <div class="sw-grid sw-grid-2 sw-gap-10 sw-mb-16">
                    @foreach (['cash' => 'Cash', 'qris' => 'QRIS', 'ewallet' => 'E-Wallet', 'virtual_account' => 'Virtual Account'] as $val => $lbl)
                        <label style="display:flex;align-items:center;gap:8px;padding:10px;border:1.5px solid {{ old('payment_method',$val==='cash'?'cash':'') === $val ? 'var(--accent)' : 'var(--surface-3)' }};border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">
                            <input type="radio" name="payment_method" value="{{ $val }}" {{ old('payment_method','cash') === $val ? 'checked' : '' }} style="accent-color:var(--accent);">
                            {{ $lbl }}
                        </label>
                    @endforeach
                </div>

                <div class="sw-form-group">
                    <label class="sw-label">Status Pembayaran *</label>
                    <select name="payment_status" class="sw-input" required>
                        <option value="pending" {{ old('payment_status','pending') === 'pending' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="paid"    {{ old('payment_status') === 'paid'    ? 'selected' : '' }}>Sudah Bayar</option>
                    </select>
                </div>
            </div>

            <div class="sw-card sw-mb-16">
                <x-ui.section-header title="Opsi Tambahan" icon="settings" />
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                        <input type="checkbox" name="is_premium_protected" value="1" {{ old('is_premium_protected') ? 'checked' : '' }} style="accent-color:var(--accent);width:15px;height:15px;">
                        <span>
                            <span class="sw-fw-600">Proteksi Premium</span>
                            <div class="sw-text-xs sw-muted">Klaim barang hilang / rusak terlindungi</div>
                        </span>
                    </label>
                </div>
            </div>

            <div class="sw-card">
                <button type="submit" class="sw-btn sw-btn-primary sw-w-full" style="font-size:15px;padding:12px;justify-content:center;">
                    <i data-lucide="package-plus" style="width:16px;height:16px;"></i>
                    Buat Order Sekarang
                </button>
                <a href="{{ route('dashboard') }}" class="sw-btn sw-btn-ghost sw-w-full sw-mt-4" style="justify-content:center;">Batal</a>
            </div>
        </div>

    </div>
</form>

</x-layouts.sidebar>

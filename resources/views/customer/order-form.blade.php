<x-layouts.sidebar :title="'Buat Order — '.$laundry->name" :heading="'Buat Order Laundry'">

@php
$serviceRatesJson  = json_encode($serviceRates);
$pickupFeeJs       = $pickupFee;
$deliveryFeeJs     = $deliveryFee;
$protectionFeeJs   = $protectionFee;
$user              = auth()->user();
@endphp

{{-- Breadcrumb --}}
<div style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--ink-3);margin-bottom:20px;flex-wrap:wrap;">
    <a href="{{ route('marketplace.index') }}" style="color:var(--accent);text-decoration:none;">Marketplace</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px;"></i>
    <a href="{{ route('marketplace.show', $laundry->slug) }}" style="color:var(--accent);text-decoration:none;">{{ $laundry->name }}</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px;"></i>
    <span>Buat Order</span>
</div>

<form id="sw-order-form" method="POST" action="{{ route('customer.order.store', $laundry->slug) }}">
@csrf

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;">

    {{-- ── Left: Form ────────────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Merchant info bar --}}
        <div class="sw-card" style="padding:14px 18px;display:flex;align-items:center;gap:14px;background:linear-gradient(135deg,#eff6ff,#f8fafc);">
            <div style="width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,#2563eb,#1d4ed8);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                @if($laundry->photo_url)
                    <img src="{{ $laundry->photo_url }}" style="width:44px;height:44px;border-radius:10px;object-fit:cover;" alt="{{ $laundry->name }}">
                @else
                    <i data-lucide="store" style="width:20px;height:20px;color:#fff;"></i>
                @endif
            </div>
            <div>
                <div style="font-size:14px;font-weight:700;color:var(--ink-1);">{{ $laundry->name }}</div>
                <div style="font-size:12px;color:var(--ink-3);">{{ $laundry->city ?? 'Merchant SafeWash' }}{{ $laundry->rating ? ' · ⭐ '.number_format((float)$laundry->rating,1) : '' }}</div>
            </div>
        </div>

        {{-- 1. Data Customer --}}
        <div class="sw-card">
            <div style="font-size:14px;font-weight:700;color:var(--ink-1);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                <div style="width:24px;height:24px;border-radius:6px;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;">1</div>
                Data Customer
            </div>

            <div class="sw-form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="sw-form-group">
                    <label class="sw-label">Nama Lengkap <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="customer_name" class="sw-input" value="{{ old('customer_name', $user->name) }}" required placeholder="Nama Anda">
                </div>
                <div class="sw-form-group">
                    <label class="sw-label">Nomor HP <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="customer_phone" class="sw-input" value="{{ old('customer_phone', $user->phone ?? '') }}" required placeholder="08xxxxxxxxxx">
                </div>
            </div>

            <div class="sw-form-group sw-mt-4">
                <label class="sw-label">Email</label>
                <input type="email" class="sw-input" value="{{ $user->email }}" disabled style="opacity:.6;">
                <div style="font-size:11px;color:var(--ink-3);margin-top:3px;">Email terhubung ke akun SafeWash Anda. Bukti order dikirim ke sini.</div>
            </div>
        </div>

        {{-- 2. Detail Laundry --}}
        <div class="sw-card">
            <div style="font-size:14px;font-weight:700;color:var(--ink-1);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                <div style="width:24px;height:24px;border-radius:6px;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;">2</div>
                Detail Laundry
            </div>

            <div class="sw-form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="sw-form-group">
                    <label class="sw-label">Jenis Layanan <span style="color:#dc2626;">*</span></label>
                    <select name="service_type" id="sw-service-type" class="sw-input" required onchange="swCalc()">
                        <option value="">-- Pilih Layanan --</option>
                        @foreach($serviceRates as $name => $rate)
                        <option value="{{ $name }}" {{ old('service_type') === $name ? 'selected' : '' }}>
                            {{ $name }} — Rp{{ number_format($rate,0,',','.') }}/kg
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="sw-form-group">
                    <label class="sw-label">Perkiraan Berat (kg) <span style="color:#dc2626;">*</span></label>
                    <input type="number" name="weight_kg" id="sw-weight" class="sw-input" value="{{ old('weight_kg', 2) }}" min="0.5" max="100" step="0.5" required oninput="swCalc()">
                    <div style="font-size:11px;color:var(--ink-3);margin-top:3px;">Minimum 0.5 kg. Merchant akan timbang ulang saat pickup.</div>
                </div>
            </div>

            <div class="sw-form-group sw-mt-4">
                <label class="sw-label">Catatan untuk Merchant</label>
                <textarea name="notes" class="sw-input" rows="2" placeholder="Instruksi khusus, jenis pakaian, dll." style="resize:vertical;">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- 3. Layanan Tambahan --}}
        <div class="sw-card">
            <div style="font-size:14px;font-weight:700;color:var(--ink-1);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                <div style="width:24px;height:24px;border-radius:6px;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;">3</div>
                Layanan Tambahan
            </div>

            {{-- Pickup / Delivery --}}
            <div style="font-size:13px;font-weight:600;color:var(--ink-2);margin-bottom:8px;">Jasa Antar-Jemput</div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
                @foreach(['none' => 'Outlet Only (Gratis)', 'pickup' => 'Pickup (+Rp '.number_format($pickupFee,0,',','.').')', 'delivery' => 'Delivery (+Rp '.number_format($deliveryFee,0,',','.').')', 'round_trip' => 'Pickup + Delivery (+Rp '.number_format($pickupFee+$deliveryFee,0,',','.'). ')'] as $val => $label)
                @php $disabled = match($val) {
                    'pickup'     => !$laundry->pickup_available,
                    'delivery'   => !$laundry->delivery_available,
                    'round_trip' => !$laundry->pickup_available || !$laundry->delivery_available,
                    default      => false,
                }; @endphp
                <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;border:2px solid var(--surface-3);border-radius:9px;cursor:{{ $disabled ? 'not-allowed' : 'pointer' }};opacity:{{ $disabled ? '.4' : '1' }};transition:border-color .15s;" onclick="if(!this.querySelector('input').disabled){swSelectDelivery('{{ $val }}')}">
                    <input type="radio" name="pickup_delivery_type" value="{{ $val }}" {{ $disabled ? 'disabled' : '' }} {{ old('pickup_delivery_type','none') === $val ? 'checked' : '' }} style="accent-color:#2563eb;" onchange="swCalc()">
                    <span style="font-size:13px;font-weight:600;color:var(--ink-1);">{{ $label }}</span>
                </label>
                @endforeach
            </div>

            {{-- Pickup/Delivery addresses --}}
            <div id="sw-addr-pickup" style="display:none;" class="sw-form-group sw-mb-3">
                <label class="sw-label">Alamat Pickup <span style="color:#dc2626;">*</span></label>
                <input type="text" name="pickup_address" id="sw-pickup-addr" class="sw-input" placeholder="Alamat lengkap penjemputan" value="{{ old('pickup_address') }}">
            </div>
            <div id="sw-addr-delivery" style="display:none;" class="sw-form-group sw-mb-3">
                <label class="sw-label">Alamat Delivery <span style="color:#dc2626;">*</span></label>
                <input type="text" name="delivery_address" id="sw-delivery-addr" class="sw-input" placeholder="Alamat lengkap pengantaran" value="{{ old('delivery_address') }}">
            </div>

            {{-- Proteksi --}}
            @if($laundry->premium_protection_enabled)
            <div style="border-top:1px solid var(--surface-3);padding-top:14px;margin-top:4px;">
                <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="is_premium_protected" value="1" id="sw-protection" {{ old('is_premium_protected') ? 'checked' : '' }} onchange="swCalc()" style="margin-top:2px;accent-color:#2563eb;width:16px;height:16px;flex-shrink:0;">
                    <div>
                        <div style="font-size:13px;font-weight:700;color:var(--ink-1);">Proteksi Premium +Rp{{ number_format($protectionFee,0,',','.') }}</div>
                        <div style="font-size:12px;color:var(--ink-3);margin-top:2px;">Ganti rugi hingga Rp2 juta jika ada barang hilang atau rusak.</div>
                    </div>
                </label>
            </div>
            @endif
        </div>

        {{-- 4. Metode Pembayaran --}}
        <div class="sw-card">
            <div style="font-size:14px;font-weight:700;color:var(--ink-1);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                <div style="width:24px;height:24px;border-radius:6px;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;">4</div>
                Metode Pembayaran Digital
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                @foreach(['qris' => ['label'=>'QRIS','icon'=>'qr-code','desc'=>'Scan QR dari dompet digital manapun'], 'ewallet' => ['label'=>'E-Wallet','icon'=>'smartphone','desc'=>'GoPay, ShopeePay, OVO, Dana'], 'virtual_account' => ['label'=>'Virtual Account','icon'=>'building-2','desc'=>'BCA, BNI, BRI, Mandiri, Permata']] as $val => $info)
                <label style="flex:1;min-width:140px;display:flex;flex-direction:column;gap:6px;padding:12px 14px;border:2px solid var(--surface-3);border-radius:10px;cursor:pointer;transition:border-color .15s;" id="sw-pm-{{ $val }}">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <input type="radio" name="payment_method" value="{{ $val }}" {{ old('payment_method','qris') === $val ? 'checked' : '' }} style="accent-color:#2563eb;" onchange="swHighlightPM()">
                        <i data-lucide="{{ $info['icon'] }}" style="width:16px;height:16px;color:#2563eb;"></i>
                        <span style="font-size:13px;font-weight:700;color:var(--ink-1);">{{ $info['label'] }}</span>
                    </div>
                    <div style="font-size:11.5px;color:var(--ink-3);padding-left:24px;">{{ $info['desc'] }}</div>
                </label>
                @endforeach
            </div>

            <div style="margin-top:12px;padding:10px 14px;background:#eff6ff;border-radius:8px;display:flex;align-items:center;gap:8px;">
                <i data-lucide="shield-check" style="width:14px;height:14px;color:#2563eb;flex-shrink:0;"></i>
                <div style="font-size:12px;color:#1e40af;">Pembayaran diproses aman melalui <strong>Midtrans</strong> — gateway pembayaran terpercaya Indonesia.</div>
            </div>
        </div>

    </div>

    {{-- ── Right: Price Summary ────────────────────────────────────────────── --}}
    <div style="position:sticky;top:24px;display:flex;flex-direction:column;gap:12px;">

        <div class="sw-card" style="border:2px solid #2563eb;">
            <div style="font-size:15px;font-weight:800;color:var(--ink-1);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                <i data-lucide="receipt" style="width:18px;height:18px;color:#2563eb;"></i>
                Ringkasan Biaya
            </div>

            <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:var(--ink-2);">Jenis Layanan</span>
                    <span id="sw-sum-service" style="font-weight:600;color:var(--ink-1);">—</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:var(--ink-2);">Berat Estimasi</span>
                    <span id="sw-sum-weight" style="font-weight:600;color:var(--ink-1);">—</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:var(--ink-2);">Harga Laundry</span>
                    <span id="sw-sum-base" style="font-weight:600;color:var(--ink-1);">Rp 0</span>
                </div>
                <div id="sw-sum-delivery-row" style="display:none;justify-content:space-between;align-items:center;">
                    <span style="color:var(--ink-2);" id="sw-sum-delivery-label">Antar-Jemput</span>
                    <span id="sw-sum-delivery" style="font-weight:600;color:var(--ink-1);">Rp 0</span>
                </div>
                <div id="sw-sum-protection-row" style="display:none;justify-content:space-between;align-items:center;">
                    <span style="color:var(--ink-2);">Proteksi Premium</span>
                    <span style="font-weight:600;color:var(--ink-1);">Rp{{ number_format($protectionFee,0,',','.') }}</span>
                </div>
                <div style="border-top:2px solid var(--surface-3);padding-top:10px;margin-top:4px;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:14px;font-weight:700;color:var(--ink-1);">Total</span>
                    <span id="sw-sum-total" style="font-size:18px;font-weight:800;color:#2563eb;">Rp 0</span>
                </div>
            </div>

            <div style="margin-top:4px;font-size:11px;color:var(--ink-3);">*Harga final dikonfirmasi merchant saat pickup. Selisih dikembalikan/ditagih.</div>

            {{-- Hidden inputs --}}
            <input type="hidden" name="total_price" id="sw-total-input" value="0">
            <input type="hidden" name="pickup_delivery_fee" id="sw-delivery-fee-input" value="0">

            <button type="submit" id="sw-submit-btn" class="sw-btn sw-btn-primary sw-w-full" style="justify-content:center;margin-top:16px;padding:13px;font-size:14px;font-weight:700;" disabled>
                <i data-lucide="shopping-bag" style="width:16px;height:16px;"></i>
                Lanjut ke Pembayaran
            </button>
            <div id="sw-submit-hint" style="text-align:center;font-size:12px;color:var(--ink-3);margin-top:8px;">Pilih layanan dan berat untuk melanjutkan</div>
        </div>

        <div class="sw-card" style="padding:12px 16px;background:var(--surface-2);border:none;">
            <div style="font-size:12px;font-weight:600;color:var(--ink-2);margin-bottom:8px;display:flex;align-items:center;gap:6px;">
                <i data-lucide="info" style="width:12px;height:12px;"></i> Yang Anda Dapatkan
            </div>
            <div style="font-size:12px;color:var(--ink-2);display:flex;flex-direction:column;gap:5px;">
                <div style="display:flex;align-items:center;gap:6px;"><i data-lucide="check-circle" style="width:12px;height:12px;color:#059669;"></i> Tracking QR real-time otomatis aktif</div>
                <div style="display:flex;align-items:center;gap:6px;"><i data-lucide="check-circle" style="width:12px;height:12px;color:#059669;"></i> Notifikasi WhatsApp setiap update status</div>
                <div style="display:flex;align-items:center;gap:6px;"><i data-lucide="check-circle" style="width:12px;height:12px;color:#059669;"></i> Bukti order via email</div>
                <div style="display:flex;align-items:center;gap:6px;"><i data-lucide="check-circle" style="width:12px;height:12px;color:#059669;"></i> Poin loyalitas setelah selesai</div>
            </div>
        </div>

    </div>
</div>

</form>

<script>
(function () {
    var rates     = @json($serviceRates);
    var pickupFee = {{ $pickupFee }};
    var delFee    = {{ $deliveryFee }};
    var protFee   = {{ $protectionFee }};

    function fmt(n) {
        return 'Rp' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    window.swCalc = function () {
        var svc     = document.getElementById('sw-service-type').value;
        var weight  = parseFloat(document.getElementById('sw-weight').value) || 0;
        var rate    = rates[svc] || 0;
        var base    = Math.round(rate * weight);

        var delType = document.querySelector('input[name="pickup_delivery_type"]:checked')?.value || 'none';
        var delCost = 0;
        var delLabel = '';
        if (delType === 'pickup')     { delCost = pickupFee; delLabel = 'Jasa Pickup'; }
        if (delType === 'delivery')   { delCost = delFee;    delLabel = 'Jasa Delivery'; }
        if (delType === 'round_trip') { delCost = pickupFee + delFee; delLabel = 'Pickup + Delivery'; }

        var hasProt = document.getElementById('sw-protection')?.checked || false;
        var protCost = hasProt ? protFee : 0;

        var total = base + delCost + protCost;

        // Update summary
        document.getElementById('sw-sum-service').textContent  = svc || '—';
        document.getElementById('sw-sum-weight').textContent   = weight ? weight + ' kg' : '—';
        document.getElementById('sw-sum-base').textContent     = fmt(base);
        document.getElementById('sw-sum-total').textContent    = fmt(total);

        var delRow = document.getElementById('sw-sum-delivery-row');
        if (delCost > 0) {
            delRow.style.display = 'flex';
            document.getElementById('sw-sum-delivery-label').textContent = delLabel;
            document.getElementById('sw-sum-delivery').textContent = fmt(delCost);
        } else {
            delRow.style.display = 'none';
        }

        var protRow = document.getElementById('sw-sum-protection-row');
        protRow.style.display = hasProt ? 'flex' : 'none';

        // Update hidden inputs
        document.getElementById('sw-total-input').value      = total;
        document.getElementById('sw-delivery-fee-input').value = delCost;

        // Address fields visibility
        document.getElementById('sw-addr-pickup').style.display   = (delType === 'pickup' || delType === 'round_trip')    ? 'block' : 'none';
        document.getElementById('sw-addr-delivery').style.display = (delType === 'delivery' || delType === 'round_trip')  ? 'block' : 'none';

        // Enable submit
        var btn  = document.getElementById('sw-submit-btn');
        var hint = document.getElementById('sw-submit-hint');
        if (svc && weight > 0 && total > 0) {
            btn.disabled = false;
            hint.style.display = 'none';
        } else {
            btn.disabled = true;
            hint.style.display = 'block';
        }
    };

    window.swSelectDelivery = function (val) {
        var radios = document.querySelectorAll('input[name="pickup_delivery_type"]');
        radios.forEach(r => { if (r.value === val && !r.disabled) r.checked = true; });
        swCalc();
    };

    window.swHighlightPM = function () {
        ['qris','ewallet','virtual_account'].forEach(function (v) {
            var lbl = document.getElementById('sw-pm-' + v);
            var inp = lbl?.querySelector('input');
            if (lbl) {
                lbl.style.borderColor = inp?.checked ? '#2563eb' : 'var(--surface-3)';
                lbl.style.background  = inp?.checked ? '#eff6ff' : 'transparent';
            }
        });
    };

    // Init
    swCalc();
    swHighlightPM();

    // Prevent submit with 0 total
    document.getElementById('sw-order-form').addEventListener('submit', function (e) {
        var total = parseInt(document.getElementById('sw-total-input').value) || 0;
        if (total < 1000) {
            e.preventDefault();
            alert('Pilih jenis layanan dan berat terlebih dahulu.');
        }
    });
})();
</script>

</x-layouts.sidebar>

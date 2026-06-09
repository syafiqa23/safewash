<x-layouts.sidebar :title="'Checkout — '.$order->tracking_code" :heading="'Checkout & Pembayaran'">

<div style="max-width:680px;margin:0 auto;">

    {{-- Breadcrumb --}}
    <div style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--ink-3);margin-bottom:20px;flex-wrap:wrap;">
        <a href="{{ route('marketplace.index') }}" style="color:var(--accent);text-decoration:none;">Marketplace</a>
        <i data-lucide="chevron-right" style="width:12px;height:12px;"></i>
        <a href="{{ route('marketplace.show', $order->laundry->slug) }}" style="color:var(--accent);text-decoration:none;">{{ $order->laundry->name }}</a>
        <i data-lucide="chevron-right" style="width:12px;height:12px;"></i>
        <span>Checkout</span>
    </div>

    {{-- Flash messages --}}
    @foreach(['success' => ['#d1fae5','#6ee7b7','#059669','#065f46','check-circle'], 'error' => ['#fee2e2','#fca5a5','#dc2626','#991b1b','x-circle'], 'warning' => ['#fefce8','#fde68a','#d97706','#92400e','alert-triangle'], 'info' => ['#eff6ff','#93c5fd','#2563eb','#1e40af','info']] as $type => $s)
        @if(session($type))
        <div style="background:{{ $s[0] }};border:1px solid {{ $s[1] }};border-radius:10px;padding:12px 16px;margin-bottom:14px;display:flex;align-items:center;gap:10px;">
            <i data-lucide="{{ $s[4] }}" style="width:18px;height:18px;color:{{ $s[2] }};flex-shrink:0;"></i>
            <div style="font-size:13px;color:{{ $s[3] }};font-weight:600;">{{ session($type) }}</div>
        </div>
        @endif
    @endforeach

    {{-- Order Summary Card --}}
    <div class="sw-card" style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--ink-3);text-transform:uppercase;letter-spacing:.5px;">Tracking Code</div>
                <div style="font-family:monospace;font-size:22px;font-weight:800;color:#2563eb;letter-spacing:1px;">{{ $order->tracking_code }}</div>
            </div>
            <x-ui.status-badge :status="$order->payment_status ?? 'pending'" />
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;padding:14px;background:var(--surface-2);border-radius:10px;">
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;margin-bottom:3px;">Merchant</div>
                <div style="font-size:13px;font-weight:600;color:var(--ink-1);">{{ $order->laundry->name }}</div>
            </div>
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;margin-bottom:3px;">Layanan</div>
                <div style="font-size:13px;font-weight:600;color:var(--ink-1);">{{ $order->service_type }}</div>
            </div>
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;margin-bottom:3px;">Berat</div>
                <div style="font-size:13px;font-weight:600;color:var(--ink-1);">{{ $order->weight_kg }} kg</div>
            </div>
        </div>

        @if($order->pickup_delivery_opt_in && $order->deliveryRequest)
        <div style="margin-top:12px;padding:10px 14px;background:#eff6ff;border-radius:8px;display:flex;align-items:center;gap:8px;">
            <i data-lucide="truck" style="width:14px;height:14px;color:#2563eb;flex-shrink:0;"></i>
            <div style="font-size:12px;color:#1e40af;">
                Antar-jemput: <strong>{{ ucfirst(str_replace('_',' ', $order->deliveryRequest->service_type)) }}</strong> — termasuk dalam total biaya
            </div>
        </div>
        @endif
    </div>

    {{-- Total + Payment Section --}}
    <div class="sw-card" style="border:2px solid #2563eb;margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:8px;">
            <div style="font-size:15px;font-weight:800;color:var(--ink-1);">Total Pembayaran</div>
            <div style="font-size:26px;font-weight:800;color:#2563eb;">Rp{{ number_format($order->total_price, 0, ',', '.') }}</div>
        </div>

        @if($isSimulator)
        {{-- ══════════════════════════════════════════════
             STATE 1: SIMULATOR MODE
             ══════════════════════════════════════════════ --}}
        <div style="padding:14px;background:#fefce8;border:1px solid #fde68a;border-radius:10px;margin-bottom:16px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                <i data-lucide="zap" style="width:15px;height:15px;color:#d97706;"></i>
                <div style="font-size:13px;font-weight:700;color:#92400e;">Mode Simulator Aktif</div>
            </div>
            <div style="font-size:12px;color:#78350f;line-height:1.5;">
                Payment gateway dalam mode simulator. Klik tombol di bawah untuk mensimulasikan pembayaran berhasil.
            </div>
        </div>

        <form method="POST" action="{{ route('customer.order.simulate-payment', $order) }}">
            @csrf
            <button type="submit" class="sw-btn sw-btn-primary sw-w-full" style="justify-content:center;padding:14px;font-size:15px;font-weight:700;">
                <i data-lucide="zap" style="width:16px;height:16px;"></i>
                Simulasi Bayar — Rp{{ number_format($order->total_price, 0, ',', '.') }}
            </button>
        </form>

        @elseif($snapToken)
        {{-- ══════════════════════════════════════════════
             STATE 2: MIDTRANS SNAP TOKEN READY
             ══════════════════════════════════════════════ --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding:10px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;">
            <div style="font-size:12px;color:#065f46;display:flex;align-items:center;gap:6px;">
                <i data-lucide="check-circle" style="width:13px;height:13px;color:#059669;"></i>
                Token pembayaran aktif
            </div>
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:#2563eb;">
                <i data-lucide="shield-check" style="width:13px;height:13px;"></i>
                Powered by Midtrans
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:16px;">
            @foreach(['QRIS' => 'qr-code', 'GoPay' => 'smartphone', 'BCA VA' => 'building-2', 'BNI VA' => 'building-2', 'BRI VA' => 'building-2', 'Kartu Kredit' => 'credit-card'] as $pm => $icon)
            <div style="display:flex;align-items:center;gap:6px;padding:8px 10px;background:var(--surface-2);border-radius:8px;">
                <i data-lucide="{{ $icon }}" style="width:13px;height:13px;color:var(--accent);"></i>
                <span style="font-size:12px;font-weight:600;color:var(--ink-2);">{{ $pm }}</span>
            </div>
            @endforeach
        </div>

        <button id="sw-snap-btn" onclick="swOpenSnap()" class="sw-btn sw-btn-primary sw-w-full" style="justify-content:center;padding:14px;font-size:15px;font-weight:700;background:linear-gradient(135deg,#2563eb,#1d4ed8);">
            <i data-lucide="credit-card" style="width:16px;height:16px;"></i>
            Bayar Sekarang - Rp{{ number_format($order->total_price, 0, ',', '.') }}
        </button>
        <div style="text-align:center;font-size:11.5px;color:var(--ink-3);margin-top:8px;">
            Anda akan diarahkan ke halaman pembayaran aman Midtrans
        </div>

        {{-- Fallback simulator — hidden; shown by JS when Snap fails/times out --}}
        <div id="sw-snap-fallback" style="display:none;margin-top:14px;">
            <div style="padding:10px 14px;background:#fef9c3;border:1px solid #fde68a;border-radius:8px;font-size:12px;color:#78350f;margin-bottom:10px;">
                Midtrans tidak merespons atau popup ditutup. Gunakan Simulasi Bayar untuk melanjutkan demo.
            </div>
            <form method="POST" action="{{ route('customer.order.simulate-payment', $order) }}">
                @csrf
                <button type="submit" class="sw-btn sw-w-full" style="justify-content:center;padding:11px;font-size:13px;font-weight:700;background:#d97706;color:#fff;border:none;border-radius:8px;display:flex;align-items:center;gap:6px;">
                    <i data-lucide="zap" style="width:14px;height:14px;"></i>
                    Simulasi Bayar - Rp{{ number_format($order->total_price, 0, ',', '.') }}
                </button>
            </form>
        </div>

        @else
        {{-- ══════════════════════════════════════════════
             STATE 3: NO TOKEN — Generate or fallback
             ══════════════════════════════════════════════ --}}
        <div style="padding:14px;background:#eff6ff;border:1px solid #93c5fd;border-radius:10px;margin-bottom:16px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                <i data-lucide="info" style="width:15px;height:15px;color:#2563eb;"></i>
                <div style="font-size:13px;font-weight:700;color:#1e40af;">
                    @if($order->paymentTransaction?->webhook_status === 'integration_error')
                        Midtrans belum merespons
                    @else
                        Token pembayaran belum tersedia
                    @endif
                </div>
            </div>
            <div style="font-size:12px;color:#1e40af;line-height:1.6;">
                Klik <strong>Generate Payment Link</strong> untuk meminta token Midtrans. Jika masih gagal, gunakan <strong>Simulasi Bayar</strong> untuk melanjutkan demo skripsi.
            </div>
        </div>

        {{-- Primary: Generate Midtrans Token --}}
        <form id="sw-gen-form" method="POST" action="{{ route('customer.order.generate-link', $order) }}" style="margin-bottom:10px;">
            @csrf
            <button type="submit" id="sw-gen-btn" class="sw-btn sw-btn-primary sw-w-full" style="justify-content:center;padding:13px;font-size:14px;font-weight:700;" onclick="swSetLoading()">
                <i data-lucide="refresh-cw" style="width:15px;height:15px;"></i>
                Generate Payment Link (Midtrans)
            </button>
        </form>

        {{-- Divider --}}
        <div style="display:flex;align-items:center;gap:10px;margin:12px 0;">
            <div style="flex:1;height:1px;background:var(--surface-3);"></div>
            <div style="font-size:11px;font-weight:600;color:var(--ink-3);text-transform:uppercase;letter-spacing:.5px;">atau</div>
            <div style="flex:1;height:1px;background:var(--surface-3);"></div>
        </div>

        {{-- Fallback: Simulator --}}
        <div style="padding:12px;background:#fefce8;border:1px solid #fde68a;border-radius:8px;margin-bottom:12px;">
            <div style="font-size:12px;color:#78350f;margin-bottom:8px;">
                <strong>Simulasi Bayar</strong> — untuk demo & skripsi. Tidak memerlukan koneksi ke Midtrans.
            </div>
            <form method="POST" action="{{ route('customer.order.simulate-payment', $order) }}">
                @csrf
                <button type="submit" class="sw-btn sw-w-full" style="justify-content:center;padding:11px;font-size:13px;font-weight:700;background:#d97706;color:#fff;border:none;border-radius:8px;display:flex;align-items:center;gap:6px;">
                    <i data-lucide="zap" style="width:14px;height:14px;"></i>
                    Simulasi Bayar — Rp{{ number_format($order->total_price, 0, ',', '.') }}
                </button>
            </form>
        </div>

        @endif
    </div>

    {{-- Info Cards --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="sw-card" style="padding:14px;background:var(--surface-2);border:none;">
            <div style="font-size:12px;font-weight:700;color:var(--ink-1);margin-bottom:8px;display:flex;align-items:center;gap:6px;">
                <i data-lucide="check-circle" style="width:13px;height:13px;color:#059669;"></i> Setelah Bayar
            </div>
            <div style="font-size:12px;color:var(--ink-2);display:flex;flex-direction:column;gap:4px;">
                <div>• Status berubah ke <strong>PAID</strong> otomatis</div>
                <div>• Merchant dikonfirmasi segera</div>
                <div>• Tracking QR aktif real-time</div>
                <div>• Email konfirmasi dikirim</div>
            </div>
        </div>
        <div class="sw-card" style="padding:14px;background:var(--surface-2);border:none;">
            <div style="font-size:12px;font-weight:700;color:var(--ink-1);margin-bottom:8px;display:flex;align-items:center;gap:6px;">
                <i data-lucide="help-circle" style="width:13px;height:13px;color:#2563eb;"></i> Perlu Bantuan?
            </div>
            <div style="font-size:12px;color:var(--ink-2);display:flex;flex-direction:column;gap:4px;">
                <div>• <a href="{{ route('orders.show', $order) }}" style="color:#2563eb;">Detail order ini</a></div>
                <div>• <a href="{{ route('orders.index') }}" style="color:#2563eb;">Semua pesanan saya</a></div>
                @if($order->laundry->phone)
                <div>• <a href="https://wa.me/{{ preg_replace('/[^0-9]/','', $order->laundry->phone) }}" target="_blank" style="color:#25d366;">Hubungi merchant</a></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Debug info (hanya saat development) --}}
    @if(config('app.debug') && $order->paymentTransaction)
    <div style="margin-top:16px;padding:12px;background:#1e293b;border-radius:8px;font-family:monospace;font-size:11px;color:#94a3b8;">
        <div style="color:#f1f5f9;font-weight:700;margin-bottom:6px;">DEBUG — Payment Transaction</div>
        <div>Provider: <span style="color:#86efac;">{{ $provider }}</span></div>
        <div>Status: <span style="color:#fbbf24;">{{ $order->paymentTransaction->status }}</span></div>
        <div>Webhook Status: <span style="color:#fbbf24;">{{ $order->paymentTransaction->webhook_status }}</span></div>
        <div>Checkout Token: <span style="color:{{ $snapToken ? '#86efac' : '#f87171' }};">{{ $snapToken ? substr($snapToken,0,20).'...' : 'NULL' }}</span></div>
        <div>Reference: {{ $order->paymentTransaction->reference }}</div>
    </div>
    @endif

</div>

{{-- Pass PHP values to JS — use @php block so @json() receives a single variable (multiline array literal breaks Blade regex) --}}
@if(!$isSimulator)
@php
$_swCheckout = [
    'snapToken' => $snapToken ?? '',
    'urlOk'     => route('orders.show', $order) . '?paid=1',
    'urlPend'   => route('orders.show', $order) . '?pending=1',
    'btnLabel'  => 'Bayar Sekarang - Rp' . number_format($order->total_price, 0, ',', '.'),
    'hasToken'  => (bool) $snapToken,
];
@endphp
<script>
var SW_CHECKOUT = @json($_swCheckout);
</script>
@endif

{{-- Midtrans Snap Integration --}}
@if(!$isSimulator && $snapToken)
<script src="{{ $snapScriptUrl }}" data-client-key="{{ $clientKey }}"></script>
<script>
(function() {
    function swShowFallback(btn) {
        if (btn) { btn.disabled = false; btn.textContent = SW_CHECKOUT.btnLabel; }
        var fb = document.getElementById('sw-snap-fallback');
        if (fb) { fb.style.display = ''; }
    }

    window.swOpenSnap = function() {
        var btn = document.getElementById('sw-snap-btn');
        btn.disabled = true;
        btn.textContent = 'Memuat halaman Midtrans…';

        if (typeof snap === 'undefined') {
            swShowFallback(btn);
            return;
        }

        var timer = setTimeout(function() { swShowFallback(btn); }, 10000);

        snap.pay(SW_CHECKOUT.snapToken, {
            onSuccess: function() { clearTimeout(timer); window.location = SW_CHECKOUT.urlOk; },
            onPending: function() { clearTimeout(timer); window.location = SW_CHECKOUT.urlPend; },
            onError:   function() { clearTimeout(timer); swShowFallback(btn); },
            onClose:   function() {
                clearTimeout(timer);
                btn.disabled = false;
                btn.textContent = SW_CHECKOUT.btnLabel;
            }
        });
    };
})();
</script>
@endif

@if(!$isSimulator && !$snapToken)
<script>
function swSetLoading() {
    var btn = document.getElementById('sw-gen-btn');
    if (!btn) return;
    btn.disabled = true;
    btn.textContent = 'Menghubungi Midtrans…';
    setTimeout(function() {
        if (btn && btn.disabled) {
            btn.disabled = false;
            btn.textContent = 'Generate Payment Link (Midtrans)';
        }
    }, 15000);
}
</script>
@endif

</x-layouts.sidebar>

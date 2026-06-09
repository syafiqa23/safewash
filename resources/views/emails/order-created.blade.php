<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Order Berhasil Dibuat — SafeWash</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:40px 20px;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);">

        {{-- Header --}}
        <tr>
          <td style="background:linear-gradient(135deg,#2563eb,#1d4ed8);padding:32px 40px;text-align:center;">
            <div style="font-size:32px;margin-bottom:8px;">🧺</div>
            <div style="font-size:22px;font-weight:800;color:#ffffff;letter-spacing:.5px;">SafeWash</div>
            <div style="font-size:13px;color:#bfdbfe;margin-top:4px;">Platform Laundry Digital</div>
          </td>
        </tr>

        {{-- Body --}}
        <tr>
          <td style="padding:36px 40px;">
            <div style="font-size:15px;color:#374151;margin-bottom:24px;line-height:1.6;">
              Halo <strong>{{ $order->customer_name }}</strong>,<br>
              Order laundry Anda telah berhasil dibuat. Berikut detail order Anda:
            </div>

            {{-- Tracking Code Box --}}
            <div style="background:#eff6ff;border:2px solid #2563eb;border-radius:12px;padding:20px;text-align:center;margin-bottom:24px;">
              <div style="font-size:11px;font-weight:700;color:#1e40af;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Kode Tracking Order</div>
              <div style="font-size:28px;font-weight:900;color:#2563eb;font-family:monospace;letter-spacing:2px;">{{ $order->tracking_code }}</div>
            </div>

            {{-- Order Details --}}
            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-radius:10px;overflow:hidden;margin-bottom:24px;">
              <tr style="background:#f9fafb;">
                <td style="padding:12px 16px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;border-bottom:1px solid #f3f4f6;">Detail Layanan</td>
                <td style="padding:12px 16px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;text-align:right;border-bottom:1px solid #f3f4f6;"></td>
              </tr>
              <tr>
                <td style="padding:12px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;">Merchant</td>
                <td style="padding:12px 16px;font-size:13px;font-weight:600;color:#111827;text-align:right;border-bottom:1px solid #f3f4f6;">{{ $order->laundry->name ?? '-' }}</td>
              </tr>
              <tr>
                <td style="padding:12px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;">Jenis Layanan</td>
                <td style="padding:12px 16px;font-size:13px;font-weight:600;color:#111827;text-align:right;border-bottom:1px solid #f3f4f6;">{{ $order->service_type }}</td>
              </tr>
              <tr>
                <td style="padding:12px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;">Berat</td>
                <td style="padding:12px 16px;font-size:13px;font-weight:600;color:#111827;text-align:right;border-bottom:1px solid #f3f4f6;">{{ $order->weight_kg }} kg</td>
              </tr>
              @if($order->pickup_delivery_opt_in)
              <tr>
                <td style="padding:12px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;">Antar-Jemput</td>
                <td style="padding:12px 16px;font-size:13px;font-weight:600;color:#2563eb;text-align:right;border-bottom:1px solid #f3f4f6;">Ya</td>
              </tr>
              @endif
              <tr style="background:#f0fdf4;">
                <td style="padding:14px 16px;font-size:14px;font-weight:700;color:#059669;">Total Pembayaran</td>
                <td style="padding:14px 16px;font-size:18px;font-weight:900;color:#059669;text-align:right;">Rp{{ number_format($order->total_price, 0, ',', '.') }}</td>
              </tr>
            </table>

            {{-- CTA --}}
            <div style="text-align:center;margin-bottom:28px;">
              <a href="{{ url('/orders/'.$order->id.'/checkout') }}" style="display:inline-block;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:10px;font-size:15px;font-weight:700;letter-spacing:.3px;">
                💳 Bayar Sekarang
              </a>
            </div>

            {{-- Info Box --}}
            <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:16px;margin-bottom:24px;">
              <div style="font-size:13px;font-weight:700;color:#92400e;margin-bottom:8px;">⚠️ Penting</div>
              <div style="font-size:12px;color:#78350f;line-height:1.7;">
                Selesaikan pembayaran sesegera mungkin agar order Anda segera diproses oleh merchant. Order yang belum dibayar dapat dibatalkan otomatis.
              </div>
            </div>

            {{-- Tracking Link --}}
            <div style="background:#f8fafc;border-radius:10px;padding:14px;text-align:center;">
              <div style="font-size:12px;color:#6b7280;margin-bottom:6px;">Pantau status laundry Anda real-time:</div>
              <a href="{{ url('/track/'.$order->tracking_code) }}" style="font-size:13px;font-weight:600;color:#2563eb;text-decoration:none;">🔍 {{ url('/track/'.$order->tracking_code) }}</a>
            </div>
          </td>
        </tr>

        {{-- Footer --}}
        <tr>
          <td style="background:#f9fafb;padding:24px 40px;text-align:center;border-top:1px solid #f3f4f6;">
            <div style="font-size:12px;color:#9ca3af;line-height:1.6;">
              Email ini dikirim otomatis oleh sistem SafeWash.<br>
              Jika Anda tidak merasa membuat order ini, abaikan email ini.<br><br>
              <strong style="color:#6b7280;">SafeWash</strong> — Platform Laundry Digital Terpercaya
            </div>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pembayaran Berhasil — SafeWash</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:40px 20px;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);">

        {{-- Header --}}
        <tr>
          <td style="background:linear-gradient(135deg,#059669,#047857);padding:32px 40px;text-align:center;">
            <div style="font-size:40px;margin-bottom:8px;">✅</div>
            <div style="font-size:22px;font-weight:800;color:#ffffff;letter-spacing:.5px;">Pembayaran Berhasil!</div>
            <div style="font-size:13px;color:#a7f3d0;margin-top:4px;">SafeWash — Platform Laundry Digital</div>
          </td>
        </tr>

        {{-- Body --}}
        <tr>
          <td style="padding:36px 40px;">
            <div style="font-size:15px;color:#374151;margin-bottom:24px;line-height:1.6;">
              Halo <strong>{{ $order->customer_name }}</strong>,<br>
              Pembayaran Anda telah <strong>berhasil dikonfirmasi</strong>! Merchant laundry segera memproses pakaian Anda.
            </div>

            {{-- Paid Badge --}}
            <div style="background:#f0fdf4;border:2px solid #6ee7b7;border-radius:12px;padding:20px;text-align:center;margin-bottom:24px;">
              <div style="font-size:11px;font-weight:700;color:#065f46;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Status Pembayaran</div>
              <div style="display:inline-block;background:#dcfce7;border:1.5px solid #86efac;border-radius:20px;padding:6px 20px;">
                <span style="font-size:15px;font-weight:800;color:#166534;">💚 LUNAS / PAID</span>
              </div>
              <div style="font-size:12px;color:#6b7280;margin-top:10px;">Kode Tracking: <strong style="font-family:monospace;color:#059669;font-size:16px;">{{ $order->tracking_code }}</strong></div>
            </div>

            {{-- Order Details --}}
            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-radius:10px;overflow:hidden;margin-bottom:24px;">
              <tr style="background:#f9fafb;">
                <td colspan="2" style="padding:12px 16px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;border-bottom:1px solid #f3f4f6;">Ringkasan Pembayaran</td>
              </tr>
              <tr>
                <td style="padding:12px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;">Merchant</td>
                <td style="padding:12px 16px;font-size:13px;font-weight:600;color:#111827;text-align:right;border-bottom:1px solid #f3f4f6;">{{ $order->laundry->name ?? '-' }}</td>
              </tr>
              <tr>
                <td style="padding:12px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;">Layanan</td>
                <td style="padding:12px 16px;font-size:13px;font-weight:600;color:#111827;text-align:right;border-bottom:1px solid #f3f4f6;">{{ $order->service_type }}</td>
              </tr>
              <tr>
                <td style="padding:12px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;">Berat</td>
                <td style="padding:12px 16px;font-size:13px;font-weight:600;color:#111827;text-align:right;border-bottom:1px solid #f3f4f6;">{{ $order->weight_kg }} kg</td>
              </tr>
              <tr>
                <td style="padding:12px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;">Metode Bayar</td>
                <td style="padding:12px 16px;font-size:13px;font-weight:600;color:#111827;text-align:right;border-bottom:1px solid #f3f4f6;">{{ strtoupper(str_replace('_',' ',$order->payment_method ?? '-')) }}</td>
              </tr>
              <tr style="background:#f0fdf4;">
                <td style="padding:14px 16px;font-size:14px;font-weight:700;color:#059669;">Total Dibayar</td>
                <td style="padding:14px 16px;font-size:18px;font-weight:900;color:#059669;text-align:right;">Rp{{ number_format($order->total_price, 0, ',', '.') }}</td>
              </tr>
            </table>

            {{-- CTA --}}
            <div style="text-align:center;margin-bottom:28px;">
              <a href="{{ url('/track/'.$order->tracking_code) }}" style="display:inline-block;background:linear-gradient(135deg,#059669,#047857);color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:10px;font-size:15px;font-weight:700;letter-spacing:.3px;margin-right:10px;">
                📍 Lacak Order
              </a>
              <a href="{{ url('/orders/'.$order->id) }}" style="display:inline-block;background:#f9fafb;border:2px solid #e5e7eb;color:#374151;text-decoration:none;padding:13px 24px;border-radius:10px;font-size:14px;font-weight:600;">
                Detail Order
              </a>
            </div>

            {{-- Next Steps --}}
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px;margin-bottom:24px;">
              <div style="font-size:13px;font-weight:700;color:#065f46;margin-bottom:10px;">🚀 Langkah Selanjutnya</div>
              <div style="font-size:12px;color:#047857;line-height:1.9;">
                1. Merchant menerima konfirmasi pembayaran otomatis<br>
                2. Laundry Anda segera diproses oleh merchant<br>
                3. Pantau status melalui halaman tracking QR<br>
                4. Notifikasi dikirim saat setiap tahap selesai<br>
                @if($order->pickup_delivery_opt_in)
                5. Tim antar-jemput akan menghubungi Anda
                @endif
              </div>
            </div>

            {{-- Tracking QR mention --}}
            <div style="background:#f8fafc;border-radius:10px;padding:14px;text-align:center;">
              <div style="font-size:12px;color:#6b7280;margin-bottom:6px;">Tracking real-time laundry Anda:</div>
              <a href="{{ url('/track/'.$order->tracking_code) }}" style="font-size:13px;font-weight:600;color:#2563eb;text-decoration:none;">🔍 {{ url('/track/'.$order->tracking_code) }}</a>
            </div>
          </td>
        </tr>

        {{-- Footer --}}
        <tr>
          <td style="background:#f9fafb;padding:24px 40px;text-align:center;border-top:1px solid #f3f4f6;">
            <div style="font-size:12px;color:#9ca3af;line-height:1.6;">
              Email ini dikirim otomatis oleh sistem SafeWash setelah pembayaran dikonfirmasi.<br><br>
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

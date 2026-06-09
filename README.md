# SafeWash

Prototype startup laundry digital berbasis Laravel untuk merchant laundry, customer, dan admin platform. Proyek ini sekarang sudah `final/live-ready` dengan mode lokal `simulator` dan mode `live` untuk integrasi payment gateway serta WhatsApp resmi.

## Fitur utama

- QR tracking untuk setiap order laundry
- Dashboard admin platform
- Dashboard merchant laundry untuk membuat order dan update status
- Dashboard customer untuk memantau order
- Timeline tracking dan log notifikasi
- Sistem klaim barang hilang
- Integrasi pembayaran digital simulatif: QRIS, e-wallet, virtual account, cash
- Notifikasi WhatsApp gateway simulatif untuk status order dan pembayaran
- Integrasi pickup-delivery dengan partner kurir, biaya, alamat, dan status fulfillment
- Merchant performance scoring berbasis completion rate, payment rate, delivery readiness, digital adoption, dan klaim
- Loyalty program customer dengan tier, poin, dan histori transaksi
- White-label solution untuk jaringan laundry: brand name, warna, custom domain, dan network branding
- Integrasi live-ready ke `Midtrans Snap`
- Integrasi live-ready ke `Xendit Payment Request`
- Integrasi resmi ke `Meta WhatsApp Cloud API`
- Webhook payment dan webhook WhatsApp untuk sinkronisasi status otomatis
- Halaman admin `Integrations` untuk mengatur key dan token langsung dari dashboard
- Endpoint backend JSON di `/api/...`
- Merchant daftar gratis, admin mengambil 15% komisi + 3% service fee dari order berhasil

## Akun demo

- Admin: `admin@safewash.test` / `password`
- Merchant: `merchant@safewash.test` / `password`
- Merchant 2 / white-label: `merchant2@safewash.test` / `password`
- Customer: `customer@safewash.test` / `password`

## Menjalankan project

```bash
cd safewash
php artisan migrate:fresh --seed
php artisan serve
```

Lalu buka `http://127.0.0.1:8000`.

## Menyalakan mode live

Secara default project berjalan di mode aman:

```env
SAFEWASH_PAYMENT_PROVIDER=simulator
SAFEWASH_WHATSAPP_ENABLED=false
```

Jika ingin mengaktifkan payment live, pilih salah satu:

### Opsi 1. Midtrans

```env
SAFEWASH_PAYMENT_PROVIDER=midtrans
SAFEWASH_PAYMENT_GATEWAY="Midtrans Snap"
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_IS_PRODUCTION=true
```

Webhook Midtrans arahkan ke:

```text
https://domain-kamu.com/webhooks/payments/midtrans
```

### Opsi 2. Xendit

```env
SAFEWASH_PAYMENT_PROVIDER=xendit
SAFEWASH_PAYMENT_GATEWAY="Xendit Payment Request"
XENDIT_SECRET_KEY=your_secret_key
XENDIT_WEBHOOK_TOKEN=your_callback_token
XENDIT_API_VERSION=2024-11-11
```

Webhook Xendit arahkan ke:

```text
https://domain-kamu.com/webhooks/payments/xendit
```

### WhatsApp Cloud API resmi

```env
SAFEWASH_WHATSAPP_ENABLED=true
SAFEWASH_WHATSAPP_GATEWAY="WhatsApp Cloud API"
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_ACCESS_TOKEN=your_long_lived_token
WHATSAPP_VERIFY_TOKEN=your_custom_verify_token
WHATSAPP_API_VERSION=v23.0
```

Webhook WhatsApp:

```text
GET  https://domain-kamu.com/webhooks/whatsapp
POST https://domain-kamu.com/webhooks/whatsapp
```

Setelah `.env` diubah:

```bash
php artisan config:clear
php artisan cache:clear
```

## Endpoint backend

- `GET /api/orders`
- `POST /api/orders`
- `PATCH /api/orders/{order}`
- `POST /api/orders/{order}/delivery`
- `GET /api/track/{code}`
- `POST /api/claims`

## Endpoint webhooks

- `POST /webhooks/payments/midtrans`
- `POST /webhooks/payments/xendit`
- `GET /webhooks/whatsapp`
- `POST /webhooks/whatsapp`

## Dokumen pendukung

- `DOKUMEN_SAFEWASH_FRS_E2E_EXEC_SUMMARY.md`
- `MODEL_BISNIS_SAFEWASH.md`
- `DEPLOYMENT_SAFEWASH.md`
- `.env.midtrans.live.example`

# SafeWash

SafeWash adalah sistem manajemen laundry berbasis Laravel untuk merchant, customer, dan admin platform. Aplikasi mendukung mode simulator untuk demo serta integrasi live payment gateway dan WhatsApp resmi.

## Tech stack

- Laravel 12 dan PHP 8.2+
- Blade, Vite, Tailwind CSS, dan JavaScript frontend
- MySQL/MariaDB atau PostgreSQL untuk production
- Nginx dengan PHP-FPM untuk deployment VPS
- Supervisor untuk queue worker

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

## Requirements

- PHP 8.2+ dengan ekstensi Laravel, Composer 2, Node.js 20+, dan npm
- MySQL/MariaDB atau PostgreSQL untuk deployment production
- Nginx dan PHP-FPM jika dijalankan di VPS

## Local development

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm install
npm run dev
```

Pada terminal lain, jalankan `php artisan serve` dan buka `http://127.0.0.1:8000`. Untuk local development, ubah `APP_ENV=local`, `APP_DEBUG=true`, dan `APP_URL=http://127.0.0.1:8000` di `.env`.

## Environment variables

`.env.example` adalah template yang aman untuk disalin ke `.env`. `.env` tidak
boleh di-commit. Minimal production configuration:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=safewash
DB_USERNAME=safewash
DB_PASSWORD=isi_password_database
SAFEWASH_PAYMENT_PROVIDER=simulator
SAFEWASH_WHATSAPP_ENABLED=false
```

Payment live dan WhatsApp hanya boleh diaktifkan dengan credential resmi yang
disimpan di environment server, bukan di source code atau Git.

## Production build

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Document root web server harus menunjuk ke `/var/www/safewash/public`, bukan ke
root repository. PHP-FPM menjalankan `public/index.php` dan Supervisor menjaga
queue worker tetap aktif.

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

- `DEPLOYMENT.md`
- `DOKUMEN_SAFEWASH_FRS_E2E_EXEC_SUMMARY.md`
- `MODEL_BISNIS_SAFEWASH.md`
- `DEPLOYMENT_SAFEWASH.md`
- `.env.midtrans.live.example`

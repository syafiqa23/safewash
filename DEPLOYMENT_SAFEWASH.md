# Deployment SafeWash

## 1. Target Deployment

Dokumen ini menyiapkan SafeWash agar bisa dijalankan pada:

- `Laragon` untuk localhost / demo kampus
- `VPS` dengan Nginx + PHP-FPM untuk domain publik dan webhook live

Provider live yang direkomendasikan untuk SafeWash:

- `Midtrans` sebagai payment gateway utama
- `Meta WhatsApp Cloud API` sebagai notifikasi resmi

## 2. Persiapan Umum

Jalankan dari folder project:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

Jika ingin langsung mode live Midtrans, gunakan acuan dari file:

- `.env.midtrans.live.example`

## 3. Deployment Laragon

### Lokasi project

Simpan project SafeWash di folder yang dibaca Laragon, misalnya:

```text
C:\laragon\www\safewash
```

### Langkah setup

1. Buka Laragon.
2. Aktifkan `Apache` atau `Nginx`, serta `MySQL`.
3. Buat database `safewash`.
4. Sesuaikan `.env`:

```env
APP_URL=http://safewash.test
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=safewash
DB_USERNAME=root
DB_PASSWORD=
```

5. Jalankan:

```bash
php artisan migrate:fresh --seed
php artisan config:clear
php artisan cache:clear
```

### Catatan webhook di Laragon

Webhook live dari Midtrans/Meta membutuhkan URL publik HTTPS. Karena `Laragon` lokal tidak publik, gunakan salah satu:

- `ngrok`
- `Cloudflare Tunnel`

Contoh ngrok:

```bash
ngrok http http://safewash.test
```

Setelah itu pasang URL HTTPS dari ngrok ke:

- Midtrans notification URL
- WhatsApp webhook URL

## 4. Deployment VPS

### Stack yang direkomendasikan

- Ubuntu 22.04 / 24.04
- Nginx
- PHP 8.2+
- MySQL / MariaDB
- Supervisor
- Certbot SSL

### Struktur deployment

Contoh lokasi:

```text
/var/www/safewash
```

### Langkah server

1. Upload project ke VPS.
2. Install dependency:

```bash
composer install --optimize-autoloader --no-dev
```

3. Copy env:

```bash
cp .env.midtrans.live.example .env
php artisan key:generate
```

4. Sesuaikan isi `.env` dengan:

- domain final
- database production
- Midtrans live key
- WhatsApp Cloud API token

5. Jalankan:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Queue worker

Jika nanti notifikasi atau job diperluas, aktifkan supervisor:

```bash
php artisan queue:work --tries=3 --timeout=120
```

Contoh file supervisor ada di:

- `deploy/supervisor/safewash-worker.conf.example`

### Nginx config

Contoh config tersedia di:

- `deploy/nginx/safewash.conf.example`

## 5. Webhook yang Harus Diaktifkan

### Midtrans

URL:

```text
https://domain-kamu.com/webhooks/payments/midtrans
```

### WhatsApp Cloud API

Verification URL:

```text
https://domain-kamu.com/webhooks/whatsapp
```

Callback URL:

```text
https://domain-kamu.com/webhooks/whatsapp
```

Verify token:

```text
sesuaikan dengan nilai whatsapp_verify_token di admin integrations atau .env
```

## 6. Production Checklist

- `APP_DEBUG=false`
- SSL aktif
- `APP_URL` sesuai domain final
- cron/queue worker aktif jika diperlukan
- database backup aktif
- Midtrans live key valid
- WhatsApp access token valid
- webhook bisa diakses dari internet
- tombol generate payment link berhasil membuat checkout

## 7. Catatan Akhir

Untuk penggunaan kampus atau demo dosen:

- Laragon + ngrok sudah cukup
- gunakan Midtrans sandbox
- gunakan WhatsApp mode simulated jika token live belum siap

Untuk produksi sungguhan:

- gunakan VPS atau hosting yang mendukung Laravel secara penuh
- aktifkan HTTPS
- gunakan domain publik permanen

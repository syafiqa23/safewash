# Deployment SafeWash

Panduan deployment SafeWash ke VPS Ubuntu dengan Nginx, PHP-FPM, MySQL, Supervisor, dan Certbot. Ganti `domain-anda.com` dengan domain publik sebenarnya dan simpan credential hanya di `.env` server.

## Persiapan server

Pasang PHP 8.2+, ekstensi PHP Laravel, Composer, Node.js, MySQL/MariaDB, Nginx, Supervisor, dan Certbot.

```bash
sudo apt update
sudo apt install nginx mysql-server supervisor certbot python3-certbot-nginx
```

Upload source ke `/var/www/safewash`, lalu jalankan:

```bash
cd /var/www/safewash
composer install --optimize-autoloader --no-dev
npm ci
npm run build
cp .env.example .env
php artisan key:generate --force
```

## Konfigurasi production

Isi `.env` server dengan nilai nyata. Jangan commit file `.env` atau credential ke Git.

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
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
SAFEWASH_PAYMENT_PROVIDER=simulator
SAFEWASH_WHATSAPP_ENABLED=false
```

Mode simulator cocok untuk demo. Untuk transaksi nyata, pilih `midtrans` atau `xendit` dan isi credential resmi provider. Aktifkan WhatsApp hanya setelah token Meta WhatsApp Cloud API valid tersedia.

## Database dan permission

Buat database serta user MySQL, kemudian jalankan migrasi production:

```bash
php artisan migrate --force
php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 775 {} \;
sudo find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

## Nginx dan SSL

Salin `deploy/nginx/safewash.conf.example` ke konfigurasi Nginx, ganti domain, lalu jalankan:

```bash
sudo nginx -t
sudo systemctl reload nginx
sudo certbot --nginx -d domain-anda.com -d www.domain-anda.com
```

Certbot mengaktifkan HTTPS. Webhook payment dan WhatsApp harus memakai URL HTTPS publik.

## Queue worker Supervisor

Salin konfigurasi dari `deploy/supervisor/safewash-worker.conf.example`, lalu jalankan:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart safewash-worker:*
sudo supervisorctl status
```

## Optimasi dan verifikasi

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan about
```

Periksa halaman utama, login, pembuatan order, queue worker, storage link, payment simulator, dan endpoint webhook setelah deployment.

## Checklist production

- [ ] Domain DNS mengarah ke IP VPS.
- [ ] `APP_ENV=production` dan `APP_DEBUG=false`.
- [ ] `APP_URL` memakai domain HTTPS yang benar.
- [ ] Database memakai MySQL/PostgreSQL, bukan SQLite.
- [ ] `php artisan migrate --force` selesai tanpa error.
- [ ] `storage` dan `bootstrap/cache` writable oleh web server.
- [ ] SSL aktif dan HTTP redirect ke HTTPS.
- [ ] Supervisor queue worker berstatus `RUNNING`.
- [ ] Backup database terjadwal.
- [ ] Credential payment/WhatsApp valid dan tidak tersimpan di Git.

# SafeWash VPS Deployment

Panduan deployment production ke Ubuntu menggunakan Nginx, PHP-FPM, MySQL,
Composer, Node.js, Certbot, dan Supervisor. Panduan ini tidak menggunakan
Docker.

## 1. Prasyarat server

Gunakan Ubuntu 22.04 atau 24.04 dengan domain DNS yang sudah mengarah ke IP
VPS. Pasang PHP 8.2+, ekstensi PHP yang diperlukan Laravel, Composer 2,
Node.js 20+, MySQL/MariaDB, Nginx, Supervisor, Git, dan Certbot.

```bash
sudo apt update
sudo apt install nginx mysql-server supervisor certbot python3-certbot-nginx git unzip
```

Pasang PHP-FPM dan ekstensi yang sesuai dengan versi PHP yang tersedia, misalnya:

```bash
sudo apt install php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath
```

## 2. Database MySQL

Buat database dan user khusus aplikasi. Jangan menggunakan user `root` untuk
SafeWash.

```sql
CREATE DATABASE safewash CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'safewash'@'localhost' IDENTIFIED BY 'GANTI_DENGAN_PASSWORD_KUAT';
GRANT ALL PRIVILEGES ON safewash.* TO 'safewash'@'localhost';
FLUSH PRIVILEGES;
```

Password database hanya disimpan pada `.env` di server dan tidak boleh masuk
Git, screenshot, log, atau dokumentasi yang di-commit.

## 3. Deploy source

```bash
sudo mkdir -p /var/www
sudo git clone REPOSITORY_URL /var/www/safewash
sudo chown -R $USER:$USER /var/www/safewash
cd /var/www/safewash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
cp .env.example .env
php artisan key:generate --force
```

Edit `.env` dan isi domain serta credential database:

```env
APP_NAME=SafeWash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=safewash
DB_USERNAME=safewash
DB_PASSWORD=PASSWORD_DATABASE

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
SAFEWASH_PAYMENT_PROVIDER=simulator
SAFEWASH_WHATSAPP_ENABLED=false
```

Mode simulator dan WhatsApp nonaktif aman untuk demo. Untuk transaksi nyata,
isi credential resmi Midtrans atau Xendit dan aktifkan WhatsApp hanya setelah
konfigurasi Meta WhatsApp Cloud API tervalidasi.

## 4. Migration dan storage

Jalankan migration tanpa destructive reset:

```bash
php artisan migrate --force
php artisan storage:link
```

Jangan gunakan `migrate:fresh` pada production karena menghapus data.

Berikan akses tulis hanya pada direktori runtime Laravel:

```bash
sudo chown -R www-data:www-data /var/www/safewash/storage /var/www/safewash/bootstrap/cache
sudo find /var/www/safewash/storage /var/www/safewash/bootstrap/cache -type d -exec chmod 775 {} \;
sudo find /var/www/safewash/storage /var/www/safewash/bootstrap/cache -type f -exec chmod 664 {} \;
```

## 5. Nginx dan PHP-FPM

Salin `deploy/nginx/safewash.conf.example` ke `/etc/nginx/sites-available/safewash`,
ganti `domain-anda.com` dengan domain sebenarnya, lalu aktifkan site:

```bash
sudo ln -s /etc/nginx/sites-available/safewash /etc/nginx/sites-enabled/safewash
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

Document root harus `/var/www/safewash/public`. Jangan arahkan Nginx ke root
repository karena dapat mengekspos `.env` dan source code.

## 6. HTTPS

Pastikan DNS sudah aktif, lalu terbitkan sertifikat:

```bash
sudo certbot --nginx -d domain-anda.com -d www.domain-anda.com
sudo certbot renew --dry-run
```

Nginx harus mengarahkan HTTP ke HTTPS. Semua webhook payment dan WhatsApp wajib
menggunakan URL HTTPS publik.

## 7. Queue worker

Salin `deploy/supervisor/safewash-worker.conf.example` ke
`/etc/supervisor/conf.d/safewash-worker.conf`. Pastikan path dan user sesuai
server, lalu jalankan:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart safewash-worker:*
sudo supervisorctl status
```

Status worker harus `RUNNING`. Periksa log jika job menumpuk:

```bash
sudo tail -f /var/www/safewash/storage/logs/worker.log
```

## 8. Laravel optimization

Setelah `.env` final dan migration selesai:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
php artisan about
```

Jika `.env` berubah, jalankan kembali `php artisan config:cache`. Jangan
menjalankan `config:cache` sebelum `.env` production lengkap.

## 9. Verifikasi deployment

```bash
curl -I https://domain-anda.com
php artisan route:list
php artisan migrate:status
sudo systemctl status nginx php8.2-fpm mysql supervisor
sudo supervisorctl status
```

Uji login, dashboard sesuai role, pembuatan order, tracking publik, payment
simulator, upload/storage, dan queue worker. Untuk mode live, uji webhook hanya
setelah URL HTTPS dan credential provider resmi siap.

## 10. Checklist keamanan

- [ ] `.env` tidak terlacak Git dan tidak ada secret di source.
- [ ] `APP_ENV=production` dan `APP_DEBUG=false`.
- [ ] Database memakai user khusus, bukan root.
- [ ] Document root Nginx adalah `/public`.
- [ ] HTTPS aktif dan renewal berhasil.
- [ ] `storage` serta `bootstrap/cache` writable oleh `www-data`.
- [ ] Queue worker Supervisor berstatus `RUNNING`.
- [ ] Backup database terjadwal dan dapat dipulihkan.
- [ ] Payment/WhatsApp live hanya memakai credential resmi di environment server.
- [ ] Password akun demo tidak digunakan di production.

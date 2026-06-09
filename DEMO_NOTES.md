# SafeWash — Catatan Demo Aplikasi

> Disiapkan untuk demo besok, 10 Juni 2026.
> Status aplikasi: **63/63 tests passing ✅**

---

## Daftar Isi

1. [Informasi Teknis](#1-informasi-teknis)
2. [Persiapan Sebelum Demo](#2-persiapan-sebelum-demo)
3. [Akun Demo](#3-akun-demo)
4. [Data Demo yang Sudah Ada](#4-data-demo-yang-sudah-ada)
5. [Skenario Demo — Customer](#5-skenario-demo--customer)
6. [Skenario Demo — Merchant](#6-skenario-demo--merchant)
7. [Skenario Demo — Admin](#7-skenario-demo--admin)
8. [Konfigurasi Payment](#8-konfigurasi-payment)
9. [Flow Teknis Lengkap](#9-flow-teknis-lengkap)
10. [Referensi URL Lengkap](#10-referensi-url-lengkap)
11. [Tips Demo](#11-tips-demo)
12. [Troubleshooting Cepat](#12-troubleshooting-cepat)
13. [Status Fitur](#13-status-fitur)

---

## 1. Informasi Teknis

| Item | Value |
|------|-------|
| Framework | Laravel 12 |
| PHP | 8.2+ |
| Database | MySQL (production) |
| Payment Provider | Midtrans Sandbox (`SAFEWASH_PAYMENT_PROVIDER=midtrans`) |
| WhatsApp | Enabled di .env (`SAFEWASH_WHATSAPP_ENABLED=true`) tapi pakai access token dummy → masuk log |
| Email | Gmail SMTP (lihat `.env` → `MAIL_USERNAME`) |
| Debug Mode | OFF (`APP_DEBUG=false`) |
| Environment | production (di `.env`) |

**Catatan penting:**
- `SAFEWASH_PAYMENT_PROVIDER=midtrans` → sistem mencoba buat Snap token ke Midtrans Sandbox
- Jika Midtrans gagal/timeout, otomatis fallback ke status "simulator" → tombol Simulasi Bayar tetap muncul
- WhatsApp send akan gagal (token dummy) tapi tidak crash — semua masuk ke `notification_logs` table dengan status `failed`

---

## 2. Persiapan Sebelum Demo

### Reset Data (Opsional — Jika Ingin Data Bersih)

```bash
php artisan migrate:fresh --seed
```

> Perintah ini menghapus semua data lama dan mengisi ulang data demo dari seeder. Jalankan ini jika data sudah berantakan dari sesi coba-coba sebelumnya.

### Jalankan Aplikasi

```bash
composer dev
```

Perintah ini menjalankan 4 proses sekaligus:
- `php artisan serve` — web server di `http://127.0.0.1:8000`
- `php artisan queue:work` — proses queue (email, dll)
- `php artisan pail` — real-time log viewer
- `npm run dev` — Vite (asset hot reload)

### Alternatif manual (jika `composer dev` bermasalah)

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev

# Terminal 3 (opsional, untuk lihat log)
php artisan pail
```

### Clear Cache (Jika Ada Masalah Aneh)

```bash
php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

---

## 3. Akun Demo

Semua akun menggunakan password: **`password`**

### Admin

| Field | Value |
|-------|-------|
| Email | `admin@safewash.test` |
| Password | `password` |
| Nama | SafeWash Admin |
| Kota | Jakarta |

### Merchant (4 Merchant Tersedia)

| Nama | Email | Laundry | Kota | Plan |
|------|-------|---------|------|------|
| Mitra Laundry Cempaka | `merchant@safewash.test` | SafeWash Express Cempaka | Jakarta | Professional |
| BlueBubble Laundry | `merchant2@safewash.test` | BlueBubble Express | Bandung | Growth Network |
| FreshClean Surabaya | `merchant3@safewash.test` | FreshClean Surabaya | Surabaya | Basic |
| BersihKilat Jogja | `merchant4@safewash.test` | BersihKilat Jogja | Yogyakarta | Basic |

### Customer

| Field | Value |
|-------|-------|
| Email | `customer@safewash.test` |
| Password | `password` |
| Nama | Nadia Customer |
| Kota | Jakarta |
| Tier Loyalty | **Sky** (84 poin) |

> Untuk demo, customer `Nadia Customer` sudah memiliki loyalty account dengan tier **Sky** dan riwayat order.

---

## 4. Data Demo yang Sudah Ada

Setelah `migrate:fresh --seed`, data berikut sudah tersedia:

### Order Demo

| Kode | Laundry | Customer | Status Order | Status Payment | Total |
|------|---------|---------|-------------|---------------|-------|
| `SW-DEMO001` | SafeWash Express Cempaka | Nadia Customer | `ready_delivery` | `paid` | Rp 42.000 |
| `SW-DEMO002` | SafeWash Express Cempaka | Rifki Santoso | `washing` | `pending` | Rp 56.000 |
| `SW-DEMO003` | BlueBubble Express | Nadia Customer | `completed` | `paid` | Rp 98.000 |
| `SW-HIST01` – `SW-HIST05` | SafeWash Express Cempaka | (historis) | `completed` | `paid` | Rp 77k–Rp 125k |

### Detail Order SW-DEMO001 (Order Paling Lengkap untuk Demo)

- **Status**: `ready_delivery` → siap diantar
- **Layanan**: Cuci + Setrika Express, 4.5 kg
- **Opsi**: Pickup & Delivery ✓, Premium Protection ✓
- **Tracking**: 6 update tracking (received → washing → drying → ironing → quality_check → ready_delivery)
- **Klaim aktif**: Kemeja kerja putih hilang (status: investigating)
- **Delivery**: SafeWash Courier, status `out_for_delivery`
- **Item**: Kemeja kerja (5 pcs) + Celana bahan (2 pcs)

### Loyalty Account (Nadia Customer)

- Tier: **Sky**
- Saldo poin: **84 poin**
- Lifetime poin: **284 poin**
- Threshold tier berikutnya (Cloud): **500 poin**

---

## 5. Skenario Demo — Customer

### 5.1. Register Akun Baru

1. Buka `http://127.0.0.1:8000/register`
2. Isi form:
   - Nama, Email, No HP (opsional)
   - Role: pilih **Customer** atau **Merchant**
   - Password (min 8 karakter)
3. Klik **Daftar** → langsung login dan redirect ke `/dashboard`

> Jika register sebagai Merchant → otomatis dibuat Laundry baru dengan nama "Laundry [Nama]"

### 5.2. Login Sebagai Customer yang Ada

1. Buka `http://127.0.0.1:8000/login`
2. Email: `customer@safewash.test`, Password: `password`
3. Redirect ke `/dashboard` — tampilan customer dashboard

### 5.3. Marketplace — Temukan Laundry

1. Klik **Marketplace** di sidebar → `/marketplace`
2. Tampil daftar 4 laundry aktif dengan:
   - Foto, rating, review count
   - Kota, jam operasional
   - Badge: Pickup/Delivery tersedia, Premium Protection
   - Subscription plan (Professional, Growth Network, Basic)
3. Klik nama laundry → halaman detail merchant (`/marketplace/{slug}`)
4. Tampil info lengkap laundry + tombol **"Buat Order Sekarang"**

**Slug laundry yang bisa dipakai:**
- `safewash-express-cempaka` → `http://127.0.0.1:8000/marketplace/safewash-express-cempaka`
- `bluebubble-express`
- `freshclean-surabaya`
- `bersihkilat-jogja`

### 5.4. Buat Order

1. Di halaman detail merchant → klik **"Buat Order Sekarang"**
   → redirect ke `/marketplace/{slug}/order`
2. Isi form order:
   - **Nama & No HP**: (auto-isi dari profil customer)
   - **Jenis Layanan**: pilih salah satu (Cuci Kering, Cuci Lipat, Cuci Setrika, Express, Premium)
   - **Berat (kg)**: minimal 1 kg
   - **Metode Pembayaran**: QRIS, E-Wallet, Virtual Account
   - **Antar-Jemput** (opsional): aktifkan toggle → isi alamat pickup & delivery → tambah biaya
   - **Premium Protection** (opsional): ganti rugi jika barang rusak/hilang
   - **Catatan**: instruksi khusus ke merchant
3. **Harga kalkulasi real-time** berdasarkan berat × tarif layanan + biaya delivery
4. Klik **"Buat Order"**
5. Sistem:
   - Buat `LaundryOrder` dengan `tracking_code` format `SW-XXXXXXXX`
   - Coba buat Snap token Midtrans (jika provider = midtrans)
   - Redirect ke halaman checkout `/orders/{id}/checkout`

### 5.5. Checkout & Pembayaran

Halaman checkout memiliki **3 state** tergantung kondisi:

#### State 1: Mode Simulator

Muncul jika `SAFEWASH_PAYMENT_PROVIDER=simulator` atau tidak ada provider di DB.

```
[Bayar Sekarang - Rp XX.XXX]  ← tombol kuning "Simulasi Bayar"
```

- Klik tombol → POST ke `/orders/{order}/simulate-payment`
- Order langsung jadi **PAID**
- Email konfirmasi terkirim
- Redirect ke detail order dengan flash "Pembayaran berhasil!"

#### State 2: Midtrans Snap Token Tersedia

Muncul jika provider = midtrans dan Snap token berhasil dibuat.

```
[Bayar Sekarang - Rp XX.XXX]  ← tombol biru Midtrans Snap
──── ATAU ────
[Simulasi Bayar]               ← tombol fallback (tersembunyi, muncul jika Snap gagal)
```

**Cara pakai Midtrans Sandbox:**
- Klik **"Bayar Sekarang"** → Midtrans Snap popup muncul
- Pilih **Kartu Kredit** → isi data:
  - Nomor kartu: `4811 1111 1111 1114`
  - CVV: `123`
  - Expired: `01/25`
- Atau pilih metode lain (Virtual Account, dll.)
- Setelah bayar → redirect (order masih "pending" karena webhook tidak aktif di lokal)
- **Untuk demo**: klik **"Simulasi Bayar"** sebagai fallback — lebih cepat dan langsung paid

**Catatan Snap:**
- Jika Snap tidak merespons dalam 10 detik → tombol fallback otomatis muncul
- Jika library Snap.js gagal load → tombol fallback langsung muncul
- Tombol Snap tidak bisa diklik dua kali (disable saat loading)

#### State 3: Token Midtrans Gagal Dibuat

Muncul jika Midtrans tidak bisa dihubungi saat `store()` dijalankan.

```
[Generate Payment Link (Midtrans)]  ← coba ulang buat token
──── ATAU ────
[Simulasi Bayar]                    ← langsung pakai simulator
```

### 5.6. Setelah Pembayaran

Setelah simulasi bayar berhasil:

1. `payment_status` order → `paid`
2. `PaymentTransaction.status` → `settled`
3. **Email konfirmasi** terkirim ke customer (cek inbox `customer@safewash.test` atau log Laravel)
4. **Loyalty poin** diberikan otomatis jika order sudah `completed`
5. **Merchant score** dihitung ulang
6. Redirect ke `/orders/{id}` dengan flash sukses

### 5.7. Tracking Order

**Cara 1: Via halaman order**
- Buka `/orders` → klik order → lihat timeline tracking

**Cara 2: Via QR Code publik**
- Di detail order → ada QR Code
- Scan dengan HP → buka `/track/{qr_token}` (tidak perlu login)
- Tampil timeline lengkap, status terakhir, info laundry

**Cara 3: Via My Tracking**
- Buka `/my-tracking` → daftar semua order customer dengan status terakhir

### 5.8. Loyalty Program

1. Buka `/loyalty`
2. Tampil:
   - Tier saat ini (Ocean / Sky / Cloud / Aurora)
   - Progress bar ke tier berikutnya
   - Saldo poin saat ini
   - Riwayat transaksi poin

**Rumus poin:** `max(10, floor(total_price / 5000))` per order completed + paid

**Threshold Tier:**

| Tier | Minimum Lifetime Poin |
|------|----------------------|
| Ocean | 0 |
| Sky | 100 |
| Cloud | 500 |
| Aurora | 2.000 |

> Customer demo (Nadia) sudah di tier **Sky** dengan 84 poin saldo, 284 poin lifetime.

### 5.9. Ajukan Klaim

1. Buka detail order yang sudah **completed**
2. Klik **"Ajukan Klaim"** → `/claims/create/{order}`
3. Isi form:
   - Jenis klaim: hilang / rusak / tidak puas / lainnya
   - Nama item, deskripsi kejadian
   - Estimasi kerugian (Rp)
4. Submit → klaim dengan status `open`
5. Merchant/admin bisa update: `open` → `investigating` → `resolved`/`rejected`

> Order SW-DEMO001 sudah punya klaim aktif (status: investigating) untuk kemeja putih hilang — tinggal tunjukkan.

### 5.10. Notifikasi

- Ikon lonceng di topbar → badge count dari `/notifications/bell` (JSON polling)
- Klik lonceng → `/notifications` → daftar notifikasi
- Berisi: update status order, klaim masuk, log WhatsApp

---

## 6. Skenario Demo — Merchant

Login: `merchant@safewash.test` / `password`

### 6.1. Dashboard Merchant

URL: `/dashboard`

Tampil KPI:
- Total order hari ini / bulan ini
- Revenue bulan ini
- Order aktif (belum completed)
- Total customer unik
- Grafik order/revenue
- Daftar order terbaru

### 6.2. Buat Order dari Sisi Merchant

1. Di dashboard → klik **"Buat Order Baru"** atau `/orders/create`
2. Isi form order (sama seperti customer tapi dari POV merchant)
   - Input nama, email, HP customer secara manual
   - Pilih layanan, berat, metode bayar, dsb.
3. Submit → order dibuat, tracking code ter-generate

### 6.3. Manajemen Order

URL: `/orders`

Fitur:
- Daftar semua order di laundry merchant ini
- Filter: **Aktif** (belum completed), **Pickup-Delivery** (ada request delivery)
- Search by kode tracking / nama customer
- Klik order → halaman detail

### 6.4. Update Status Order

Di halaman detail order:

1. Klik tombol status sesuai alur proses
2. Alur status lengkap:

```
received → washing → drying → ironing → ready_for_pickup → completed
```

3. Setiap update status:
   - Tracking update tercatat
   - WhatsApp notifikasi dikirim ke customer (masuk log karena token dummy)
   - Merchant score dihitung ulang

> Order SW-DEMO001 sudah di status `ready_delivery` — tinggal klik **Completed** untuk selesaikan.

### 6.5. Kelola Delivery

Di detail order yang punya pickup/delivery:

1. Lihat status delivery saat ini
2. Update status delivery:

```
scheduled → picked_up → in_transit → out_for_delivery → delivered
```

3. Setiap update → dicatat di `DeliveryRequest`

> Order SW-DEMO001 delivery-nya sedang `out_for_delivery` oleh SafeWash Courier.

### 6.6. Settle Payment (Manual)

Merchant bisa settle order yang sudah selesai tapi payment masih pending:

1. Di detail order → klik **"Settle Payment"** (tombol muncul untuk order pending payment)
2. Konfirmasi → payment status jadi `paid`

### 6.7. QR Code Management

URL: `/qr-manage`

Fitur:
- Daftar semua QR code untuk setiap order
- Download / print QR code individual
- QR code scan → buka `/track/{qr_token}` (halaman publik)

### 6.8. Revenue & Analytics

URL: `/revenue`

Tampil:
- Grafik revenue bulanan (dari historis order completed + paid)
- Breakdown metode pembayaran (QRIS, E-Wallet, VA, Cash)
- Total revenue, komisi admin, net merchant

### 6.9. Daftar Customer

URL: `/customers`

Tampil daftar semua customer yang pernah order di laundry ini, dengan:
- Total order per customer
- Total spend
- Tanggal order terakhir

---

## 7. Skenario Demo — Admin

Login: `admin@safewash.test` / `password`

### 7.1. Dashboard Admin

URL: `/admin/dashboard`

KPI Platform:
- Total revenue platform (semua merchant)
- Komisi admin (15% dari setiap order paid)
- Jumlah merchant aktif
- Total order bulan ini
- Customer baru bulan ini
- Grafik revenue bulanan platform
- Top merchants by revenue
- Klaim terbaru

### 7.2. Manajemen Merchant

URL: `/admin/merchants`

Fitur unggulan:
1. **Daftar semua merchant** dengan KPI: total order, revenue, completion rate, merchant score
2. **Search & Filter** by kota, status, subscription plan
3. **Toggle Active/Inactive** tanpa reload halaman (AJAX PATCH → `/admin/merchants/{laundry}/toggle-status`)
4. **Edit merchant**: nama, alamat, deskripsi, jam operasional, komisi
5. **Merchant Score** per merchant (0–100)

**Rumus Merchant Score:**
```
(completion_rate × 40) + (paid_rate × 25) + (digital_payment_rate × 15) +
(delivery_readiness × 10) + (supports_white_label ? 10 : 0) − (claim_penalty × 20)

Hasil di-clamp ke 0–100
```

Komponen:
- `completion_rate`: % order yang selesai (completed) dari total
- `paid_rate`: % order yang terbayar (paid) dari total
- `digital_payment_rate`: % yang pakai QRIS/E-Wallet/VA (bukan cash)
- `delivery_readiness`: 1 jika pickup_available + delivery_available, 0.5 jika salah satu
- `supports_white_label`: bonus 10 poin jika aktif
- `claim_penalty`: banyaknya klaim open/investigating × 20 poin penalti

### 7.3. Monitoring Order Platform

URL: `/orders` (sebagai admin)

Admin melihat **SEMUA order dari semua merchant**. Filter:
- By status (all/active/pickup-delivery)
- Search by tracking code / nama customer

### 7.4. Monitoring Payment

URL: `/admin/payments`

Tampil semua `PaymentTransaction`:
- Reference, gateway, metode, gross amount, gateway fee, net amount
- Status: pending / paid / failed / expired
- Tanggal bayar

> Data demo: 3 payment transaction (2 paid, 1 pending)

### 7.5. Klaim Center

URL: `/admin/claims`

Admin bisa:
1. Lihat semua klaim dari semua customer
2. Update status klaim:
   - `open` → `investigating`
   - `investigating` → `resolved` (isi compensation amount)
   - `investigating` → `rejected` (isi catatan penolakan)
3. Tambah resolution notes

> Data demo: 1 klaim aktif dari Nadia Customer (SW-DEMO001, kemeja hilang, investigating)

### 7.6. Settlement Merchant

URL: `/admin/settlement`

**Cara kerja settlement:**
1. Admin pilih merchant dan periode
2. Sistem hitung: gross revenue − komisi admin (15%) − service fee (3%)
3. Buat settlement record
4. Transfer ke rekening merchant (manual di luar sistem)
5. Admin klik **"Mark as Paid"** → settlement lunas

**Contoh kalkulasi:**
```
Order Rp 100.000 (paid)
→ Admin commission: 15% = Rp 15.000
→ Service fee: 3% = Rp 3.000
→ Total admin ambil: Rp 18.000
→ Merchant dapat: Rp 82.000
```

### 7.7. Laporan & Export

URL: `/admin/reports`

Filter: tanggal mulai–akhir, pilih merchant (all / spesifik)

Export:
- **Excel** → `GET /admin/reports/excel`
- **PDF** → `GET /admin/reports/pdf`

Isi laporan: daftar order, total revenue, komisi, breakdown per merchant

### 7.8. Manajemen Integrasi (API Keys)

URL: `/admin/integrations`

Fitur kunci aplikasi ini:
- Admin bisa update API keys **tanpa restart server**
- Perubahan langsung efektif karena `IntegrationSettingsService` selalu cek DB dulu sebelum fallback ke `.env`

**Midtrans:**
- Server Key (untuk transaksi backend)
- Client Key (untuk Snap.js frontend)
- Toggle: Sandbox / Production

**Xendit:**
- API Key
- Webhook Token

**WhatsApp (Meta Cloud API):**
- Phone Number ID
- Access Token
- Webhook Verify Token

**Demo yang bisa ditunjukkan:**
1. Ubah salah satu setting → Save
2. Buat order baru → sistem langsung pakai setting yang baru
3. Tidak perlu `php artisan config:clear`

### 7.9. Manajemen Customer

URL: `/admin/customers`

Daftar semua customer yang pernah order di platform:
- Total order, total spend, tier loyalty
- Kota, tanggal bergabung

---

## 8. Konfigurasi Payment

### Mode Saat Ini: Midtrans Sandbox

```env
SAFEWASH_PAYMENT_PROVIDER=xxxxxxxxx
MIDTRANS_SERVER_KEY=xxxxxxxxxxx
MIDTRANS_CLIENT_KEY=xxxxxxxxx
MIDTRANS_IS_PRODUCTION=xxxxxx
```

**Perilaku:**
1. Saat customer submit order → sistem POST ke Midtrans Sandbox untuk buat Snap token
2. Jika berhasil → Snap button muncul di checkout (State 2)
3. Jika gagal/timeout (10 detik) → checkout masuk State 3 (generate link button + simulasi)
4. Snap popup muncul → customer bisa bayar dengan kartu test

**Kartu test Midtrans Sandbox:**
| Tipe | Nomor | CVV | Expired | Hasil |
|------|-------|-----|---------|-------|
| Kartu sukses | `4811 1111 1111 1114` | `123` | `01/25` | Sukses |
| Kartu denied | `4911 1111 1111 1113` | `123` | `01/25` | Ditolak bank |
| Kartu 3DS | `4811 1111 1111 1114` + OTP: `112233` | `123` | `01/25` | Sukses dengan 3DS |

**Penting:** Setelah bayar via Snap di lokal, order masih status `pending` karena webhook Midtrans tidak bisa reach localhost. Untuk demo, gunakan **Simulasi Bayar** sebagai pengganti.

### Ganti ke Mode Simulator (Untuk Demo Tanpa Internet)

Edit `.env`:
```env
SAFEWASH_PAYMENT_PROVIDER=simulator
```

Atau ubah via admin UI di `/admin/integrations` (efek langsung tanpa restart).

**Perilaku simulator:**
- Tidak ada request ke Midtrans
- Tombol Simulasi Bayar langsung muncul
- Klik → order PAID seketika
- Email terkirim, loyalty poin diberikan

### Kenapa Ada Dua Opsi?

```
Midtrans Sandbox  →  Realistis, ada Snap UI, cocok untuk demo fitur payment gateway
Simulator         →  Cepat, offline-friendly, tidak tergantung internet, cocok untuk demo flow
```

---

## 9. Flow Teknis Lengkap

```
[Customer]
    │
    ▼
Register / Login (/register, /login)
    │
    ▼
Marketplace (/marketplace)
    │  Lihat daftar laundry aktif
    ▼
Detail Merchant (/marketplace/{slug})
    │  Klik "Buat Order Sekarang"
    ▼
Form Order (/marketplace/{slug}/order)
    │  Isi form, harga kalkulasi live
    │  Submit POST → CustomerOrderController::store()
    │
    ├── Buat LaundryOrder (tracking_code: SW-XXXXXXXX, qr_token: UUID)
    ├── Buat DeliveryRequest (jika opt-in delivery)
    │
    ├─ [Midtrans] → Http::post() ke Sandbox API (timeout 10s)
    │       ├── Sukses  → simpan snap_token di PaymentTransaction
    │       └── Gagal   → simpan status 'integration_error' (fallback)
    │
    └── Redirect ke /orders/{id}/checkout
            │
            ├── STATE 1: Provider=simulator
            │   └── Tampil form Simulasi Bayar
            │
            ├── STATE 2: snap_token tersedia
            │   ├── Tampil tombol Snap (Bayar Sekarang)
            │   └── Hidden fallback #sw-snap-fallback
            │       └── Muncul jika: snap undefined / timeout 10s / onError
            │
            └── STATE 3: token tidak ada / integration_error
                ├── Form Generate Link (coba ulang ke Midtrans)
                └── Tombol Simulasi Bayar

[Bayar via Simulate]
    │
    │  POST /orders/{order}/simulate-payment
    │  → PaymentGatewayService::settle()
    │  → order.payment_status = 'paid'
    │  → PaymentTransaction.status = 'settled'
    │  → Mail::to(customer)->send(PaymentSuccessMail)
    │  → LoyaltyProgramService::award() (jika order=completed)
    │  → MerchantScoringService::recalculate()
    │  → WhatsApp notification (log/failed karena dummy token)
    │
    └── Redirect /orders/{id} dengan flash "Pembayaran berhasil!"

[Merchant]
    │
    ├── Update status order:
    │   POST /orders/{order}/status
    │   received → washing → drying → ironing → ready_for_pickup → completed
    │   └── Tiap update: TrackingUpdate + WhatsApp + Recalculate Score
    │
    └── Update delivery:
        POST /orders/{order}/delivery
        scheduled → picked_up → in_transit → out_for_delivery → delivered

[Admin]
    │
    ├── Monitor semua order & payment
    ├── Kelola klaim customer
    ├── Buat settlement merchant
    ├── Export laporan (Excel/PDF)
    └── Update API keys (Midtrans/Xendit/WhatsApp) — efek langsung
```

---

## 10. Referensi URL Lengkap

### Publik (Tanpa Login)

| URL | Keterangan |
|-----|-----------|
| `/` | Landing page SafeWash |
| `/login` | Halaman login |
| `/register` | Halaman register (pilih role) |
| `/track/{code}` | Tracking order publik (bisa pakai: `SW-DEMO001`) |

### Authenticated — Semua Role

| URL | Keterangan |
|-----|-----------|
| `/dashboard` | Dashboard role-based |
| `/marketplace` | Daftar laundry aktif |
| `/marketplace/{slug}` | Detail merchant |
| `/orders` | Daftar order (filtered by role) |
| `/orders/{id}` | Detail order |
| `/claims` | Daftar klaim |
| `/notifications` | Semua notifikasi |
| `/profile` | Profil user |

### Authenticated — Customer Only

| URL | Keterangan |
|-----|-----------|
| `/marketplace/{slug}/order` | Form buat order |
| `/orders/{id}/checkout` | Halaman checkout & payment |
| `/loyalty` | Loyalty program |
| `/my-tracking` | Tracking semua order customer |

### Authenticated — Merchant Only

| URL | Keterangan |
|-----|-----------|
| `/orders/create` | Form buat order (dari merchant) |
| `/qr-manage` | QR code management |
| `/customers` | Daftar customer laundry |
| `/revenue` | Revenue & analytics |

### Authenticated — Admin Only

| URL | Keterangan |
|-----|-----------|
| `/admin/dashboard` | Dashboard admin platform |
| `/admin/merchants` | Manajemen semua merchant |
| `/admin/payments` | Monitoring semua payment |
| `/admin/claims` | Klaim center |
| `/admin/settlement` | Settlement merchant |
| `/admin/reports` | Laporan (+ export Excel/PDF) |
| `/admin/integrations` | API key management |
| `/admin/customers` | Manajemen semua customer |

### Webhook (Unauthenticated)

| URL | Keterangan |
|-----|-----------|
| `POST /webhooks/payments/midtrans` | Notifikasi pembayaran Midtrans |
| `POST /webhooks/payments/xendit` | Notifikasi pembayaran Xendit |
| `GET /webhooks/whatsapp` | Verifikasi webhook WhatsApp |
| `POST /webhooks/whatsapp` | Terima pesan inbound WhatsApp |

---

## 11. Tips Demo

### Urutan Demo yang Disarankan

**Blok 1: Customer Journey (±10 menit)**
1. Login sebagai customer (`customer@safewash.test`)
2. Buka Marketplace → pilih SafeWash Express Cempaka
3. Buat order baru (isi form, tunjukkan kalkulasi harga real-time)
4. Tunjukkan halaman checkout (tergantung kondisi: State 2 dengan Snap, atau State 3 dengan simulator)
5. Klik Simulasi Bayar → tunjukkan redirect sukses
6. Buka `/my-tracking` dan `/loyalty` untuk tunjukkan poin

**Blok 2: Merchant Flow (±7 menit)**
1. Buka tab/window baru → login sebagai merchant (`merchant@safewash.test`)
2. Lihat dashboard — tunjukkan order yang baru masuk
3. Buka SW-DEMO001 → tunjukkan detail lengkap (item, tracking timeline, delivery, klaim)
4. Update status order ke completed
5. Tunjukkan QR code → scan atau paste URL ke browser

**Blok 3: Admin Platform (±8 menit)**
1. Buka tab baru → login sebagai admin (`admin@safewash.test`)
2. Dashboard admin — tunjukkan KPI platform
3. Manajemen merchant → toggle status, lihat merchant score
4. Klaim center → lihat klaim Nadia, update jadi resolved
5. Integrasi settings → tunjukkan update API key tanpa restart

### Tips Praktis

1. **Buka 3 browser berbeda** (atau incognito) bersamaan untuk customer + merchant + admin
2. **Gunakan Simulator** untuk demo payment yang smooth — tidak tergantung internet atau Midtrans
3. **Reset data** dengan `php artisan migrate:fresh --seed` jika data sudah kacau dari percobaan sebelumnya
4. **Scan QR code** SW-DEMO001 dengan HP untuk demo tracking publik yang menarik
5. **Tunjukkan toggle merchant** di admin — efek AJAX langsung tanpa reload halaman
6. **Export Excel/PDF** di halaman laporan — pastikan Excel ter-install (atau pakai PDF saja)
7. **WhatsApp "sent"**: semua notifikasi masuk log dengan status `failed` — bisa tunjukkan di `/notifications` bahwa sistemnya ada, hanya token dummy

### Yang Perlu Dihindari

- Jangan klik bayar via Snap Midtrans dan harap webhook diterima — tidak bisa di lokal
- Jangan ubah `APP_URL` di `.env` saat demo karena akan mempengaruhi Midtrans notification URL
- Jangan jalankan `php artisan migrate:fresh --seed` di tengah demo (data hilang semua)

---

## 12. Troubleshooting Cepat

### Halaman blank / error 500

```bash
php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

Jika masih error, lihat log:
```bash
php artisan pail
# atau
type storage\logs\laravel.log
```

### Login tidak bisa

Pastikan database MySQL berjalan dan tabel ada:
```bash
php artisan migrate:status
```

Jika belum ada tabel:
```bash
php artisan migrate --seed
```

### Email tidak terkirim

Normal — Gmail SMTP terkadang lambat. Bisa cek di:
- Inbox `zahroosyafiqa@gmail.com`
- Atau lihat log Laravel untuk memastikan `Mail::send()` dipanggil

Jika ingin email pasti masuk log saja (untuk demo):
Edit `.env` sementara: `MAIL_MAILER=log`

### Snap Midtrans tidak muncul / loading terus

Ini normal — Snap popup muncul jika token valid dan koneksi ke Midtrans CDN OK.
Jika tidak muncul dalam 10 detik → tombol Simulasi Bayar otomatis muncul.
Gunakan Simulasi Bayar untuk demo.

### Pagination tidak tampil

Sudah diperbaiki dengan custom view. Jika masih aneh:
```bash
php artisan view:clear
```

### Queue tidak jalan (email tidak terkirim)

```bash
php artisan queue:work
```

Atau jalankan ulang `composer dev`.

### Database connection refused

Pastikan MySQL berjalan. Di Windows, cek XAMPP/Laragon/MySQL service.

---

## 13. Status Fitur

| Fitur | Status |
|-------|--------|
| Multi-role auth (admin/merchant/customer) | ✅ |
| Register & Login | ✅ |
| Marketplace & detail merchant | ✅ |
| Form order customer (kalkulasi harga real-time) | ✅ |
| Form order merchant | ✅ |
| Checkout 3-state (simulator/snap/fallback) | ✅ |
| Simulasi pembayaran | ✅ |
| Midtrans Snap integration | ✅ |
| Fallback simulator (timeout/error) | ✅ |
| Email konfirmasi order & payment | ✅ |
| Status update order (6 tahap) | ✅ |
| Delivery request management | ✅ |
| QR tracking publik | ✅ |
| Loyalty program (poin & tier) | ✅ |
| Klaim customer | ✅ |
| Notifikasi (WhatsApp log) | ✅ |
| Dashboard merchant (KPI + grafik) | ✅ |
| Revenue analytics merchant | ✅ |
| QR code management | ✅ |
| Dashboard admin (KPI platform) | ✅ |
| Manajemen merchant (toggle, edit) | ✅ |
| Merchant score (0–100) | ✅ |
| Monitoring payment admin | ✅ |
| Klaim center admin | ✅ |
| Settlement merchant | ✅ |
| Laporan + export Excel/PDF | ✅ |
| Integrasi settings (API keys live) | ✅ |
| Pagination custom (sw-* CSS) | ✅ |
| 63/63 Tests passing | ✅ |

---

*Dibuat: 9 Juni 2026 — SafeWash v1.0 Demo Ready*

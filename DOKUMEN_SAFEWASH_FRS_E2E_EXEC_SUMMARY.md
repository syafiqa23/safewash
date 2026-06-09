# DOKUMEN SAFEWASH
## Executive Summary, Functional Requirement Specification (FRS), dan End-to-End (E2E)

**Nama Startup:** SafeWash  
**Bidang:** Startup Digital / Laundry Management Platform  
**Program Studi:** Teknik Informatika  
**Mata Kuliah:** Startup Digital  
**Disusun untuk:** Kebutuhan tugas akademik dan presentasi dosen  

---

# DAFTAR ISI

1. Executive Summary  
2. Functional Requirement Specification (FRS)  
3. End-to-End (E2E) Process  
4. Referensi  

---

# 1. EXECUTIVE SUMMARY

## 1.1 Latar Belakang

Usaha laundry skala UMKM atau merchant laundry masih banyak dikelola secara manual. Pada praktik operasional sehari-hari, banyak merchant laundry mengalami kendala seperti:

- pencatatan order yang tidak rapi,
- risiko pakaian hilang atau tertukar,
- tidak adanya transparansi status cucian kepada pelanggan,
- sulitnya melakukan dokumentasi komplain atau klaim barang hilang,
- terbatasnya kemampuan merchant dalam membangun citra usaha yang profesional.

Di sisi pelanggan, layanan laundry sering dianggap kurang transparan karena pelanggan tidak dapat mengetahui posisi pakaian mereka secara real-time. Ketika terjadi masalah seperti kehilangan pakaian, proses penyelesaian sering kali tidak terdokumentasi dengan baik sehingga memicu konflik antara pelanggan dan merchant.

SafeWash hadir sebagai solusi digital untuk menjawab permasalahan tersebut melalui platform yang menghubungkan **admin platform**, **merchant laundry**, dan **customer** dalam satu sistem terintegrasi.

## 1.2 Ringkasan Solusi

SafeWash adalah platform laundry digital yang membantu merchant laundry mengelola order secara lebih aman, transparan, dan profesional. Sistem ini menyediakan:

- QR tracking untuk setiap order laundry,
- dashboard merchant untuk operasional order,
- dashboard customer untuk pelacakan order,
- dashboard admin untuk monitoring merchant dan performa platform,
- log notifikasi,
- sistem klaim barang hilang,
- laporan bisnis dan analitik berbasis data.

## 1.3 Permasalahan Utama yang Diselesaikan

SafeWash berfokus menyelesaikan beberapa masalah inti:

1. **Kurangnya transparansi proses laundry**  
   Customer tidak dapat mengetahui status order secara jelas.

2. **Tingginya risiko kehilangan atau tertukar pakaian**  
   Merchant laundry sering belum memiliki pencatatan item dan tracking yang konsisten.

3. **Operasional merchant masih manual**  
   Banyak merchant laundry masih memakai catatan tulis tangan, chat, atau spreadsheet sederhana.

4. **Tidak adanya sistem klaim yang terdokumentasi**  
   Ketika komplain terjadi, tidak ada histori digital yang cukup kuat untuk penelusuran.

5. **Merchant kesulitan terlihat profesional dan scalable**  
   Merchant laundry membutuhkan sistem untuk menaikkan kualitas layanan dan kepercayaan pelanggan.

## 1.4 Value Proposition

Nilai utama SafeWash adalah:

- **Untuk customer:** laundry lebih aman, transparan, dan mudah dilacak.
- **Untuk merchant laundry:** operasional lebih rapi, efisien, dan profesional.
- **Untuk admin platform:** dapat mengelola merchant, memantau performa, dan menghasilkan pendapatan berbasis komisi digital.

## 1.5 Model Bisnis

Model bisnis SafeWash menggunakan pendekatan platform:

- Merchant laundry dapat **mendaftar gratis** ke platform.
- SafeWash memperoleh pendapatan dari:
  - **komisi 15%** dari order berhasil,
  - **service fee 3%** dari order berhasil,
  - peluang pengembangan pendapatan masa depan dari fitur premium, promosi merchant, dan integrasi pembayaran.

## 1.6 Target Pasar

Sesuai arahan tugas, analisis target pasar SafeWash dijabarkan ke dalam tiga layer utama:

### 1.6.1 Segmen Pasar

Segmen pasar adalah pengelompokan target pengguna utama yang menjadi sasaran startup.

#### A. Merchant Laundry

Target utama SafeWash pada sisi penyedia layanan adalah:

- laundry kiloan skala UMKM,
- laundry satuan,
- laundry express,
- laundry rumahan,
- laundry yang ingin naik kelas secara digital.

#### B. Customer Laundry

Target utama pada sisi pengguna jasa adalah:

- mahasiswa,
- pekerja kantoran,
- keluarga muda di perkotaan,
- penghuni kos/apartemen,
- masyarakat urban yang membutuhkan jasa laundry rutin.

#### C. Admin Platform

Dalam konteks model bisnis, admin bukan target pasar eksternal, tetapi merupakan aktor bisnis internal yang mengelola platform, merchant, analitik, dan laporan.

### 1.6.2 Karakteristik Pasar

Karakteristik pasar menjelaskan perilaku, kebutuhan, dan kondisi spesifik dari segmen yang ditargetkan.

#### A. Karakteristik Merchant Laundry

- Sebagian besar masih mengelola order secara manual atau semi-manual.
- Sangat bergantung pada kepercayaan pelanggan.
- Memiliki kebutuhan pencatatan order dan item yang lebih rapi.
- Membutuhkan solusi murah, mudah dipakai, dan tidak rumit diimplementasikan.
- Cenderung tertarik pada solusi yang langsung berdampak pada efisiensi operasional.
- Memerlukan fitur tracking, histori status, dan dokumentasi klaim.
- Cocok dengan model onboarding gratis karena sensitif terhadap biaya awal.

#### B. Karakteristik Customer Laundry

- Mengutamakan kemudahan dan kecepatan layanan.
- Memiliki tingkat penggunaan smartphone yang tinggi.
- Cenderung menggunakan internet dan aplikasi digital dalam aktivitas harian.
- Menyukai transparansi layanan, termasuk notifikasi status order.
- Akan lebih percaya pada merchant yang memiliki sistem pelacakan dan bukti digital.
- Lebih loyal pada layanan yang aman dan profesional.

#### C. Karakteristik Lingkungan Pasar

- Pasar jasa laundry tumbuh seiring urbanisasi, mobilitas tinggi, dan kebutuhan outsourcing pekerjaan rumah tangga.
- Perubahan perilaku masyarakat mendorong adopsi layanan digital berbasis mobile.
- Merchant tradisional masih memiliki ruang besar untuk digitalisasi.
- Trust, transparency, dan service quality menjadi faktor pembeda yang kuat.

### 1.6.3 Ukuran Pasar

Ukuran pasar menjelaskan besarnya peluang yang bisa dimasuki oleh SafeWash. Dalam dokumen ini digunakan tiga level: **TAM**, **BAM/SAM**, dan **SOM**.

#### Dasar Data

Beberapa dasar data yang digunakan:

- APJII melaporkan jumlah pengguna internet Indonesia tahun 2024 mencapai **221.563.479 jiwa** dengan tingkat penetrasi **79,5%**.  
- BPS dalam publikasi **Profil Industri Mikro dan Kecil 2023** menegaskan bahwa usaha mikro dan kecil memiliki sebaran luas di Indonesia dan memainkan peran penting dalam ekonomi lokal.  
- Karena data resmi nasional yang sangat spesifik untuk jumlah merchant laundry UMKM digital-ready tidak tersedia secara langsung pada sumber contoh yang digunakan, maka ukuran pasar SafeWash pada dokumen ini memakai **kombinasi data resmi dan asumsi bisnis konservatif**.

#### A. Total Addressable Market (TAM)

TAM adalah seluruh pasar potensial yang secara teoritis dapat dilayani oleh SafeWash.

**Perspektif sisi customer**

- SafeWash berpotensi menjangkau pengguna internet Indonesia yang memakai layanan digital.
- Basis paling luas untuk demand side adalah **221,56 juta pengguna internet Indonesia**.

**Perspektif sisi merchant**

- SafeWash berpotensi menyasar merchant laundry UMKM di berbagai kota di Indonesia.
- Secara konseptual, TAM merchant mencakup seluruh laundry UMKM yang ingin meningkatkan operasional dan kepercayaan layanan melalui sistem digital.

**Makna TAM bagi SafeWash**

- Menunjukkan bahwa SafeWash bermain di ruang pasar yang besar karena berada di persimpangan antara **digital adoption**, **layanan rumah tangga**, dan **UMKM services digitization**.

#### B. Business Available Market / Serviceable Available Market (BAM/SAM)

BAM adalah bagian pasar yang relevan dan realistis untuk dilayani oleh solusi SafeWash.

Pada tahap awal, SafeWash tidak menargetkan seluruh Indonesia sekaligus, tetapi fokus pada:

- merchant laundry UMKM di wilayah perkotaan,
- customer urban dengan penggunaan smartphone tinggi,
- wilayah dengan perilaku digital yang kuat dan kebutuhan laundry rutin.

**Asumsi bisnis konservatif untuk BAM**

- Jika SafeWash menargetkan sekitar **20% dari basis pengguna internet** yang paling relevan secara perilaku untuk layanan laundry digital di area urban dan semi-urban, maka pasar demand-side yang relevan berada pada kisaran **44,3 juta orang**.
- Dari sisi merchant, jika SafeWash menargetkan merchant laundry UMKM di kota-kota besar dan kawasan padat hunian, maka BAM merchant dapat diposisikan sebagai merchant laundry di kota prioritas seperti Jakarta, Bandung, Surabaya, Semarang, Yogyakarta, Malang, Denpasar, dan kota besar lain yang memiliki kebutuhan laundry tinggi.

**Makna BAM**

- Menunjukkan bahwa pasar yang benar-benar cocok dengan SafeWash cukup besar dan layak untuk dimasuki.

#### C. Serviceable Obtainable Market (SOM)

SOM adalah bagian pasar yang realistis untuk dicapai dalam tahap awal implementasi.

Untuk fase awal, SafeWash dapat menargetkan:

- peluncuran di 1 sampai 3 kota,
- akuisisi merchant laundry secara bertahap melalui pendekatan komunitas, digital marketing, dan direct onboarding,
- customer yang berasal dari merchant yang telah bergabung.

**Estimasi awal SOM SafeWash**

- **Merchant:** target realistis fase awal adalah **100 sampai 300 merchant laundry aktif** dalam 12 sampai 24 bulan.
- **Customer aktif:** bila satu merchant rata-rata melayani 80 sampai 150 customer aktif, maka potensi customer aktif awal berada pada kisaran **8.000 sampai 45.000 customer**.

**Makna SOM**

- Menunjukkan target pasar yang bisa dieksekusi secara operasional, terukur, dan sesuai kapasitas startup tahap awal.

### 1.6.4 Kesimpulan Analisis Target Pasar

- **Segmen pasar** SafeWash terdiri dari merchant laundry UMKM dan customer pengguna jasa laundry.
- **Karakteristik pasar** menunjukkan bahwa merchant membutuhkan digitalisasi operasional dan customer membutuhkan transparansi layanan.
- **Ukuran pasar** menunjukkan bahwa SafeWash memiliki potensi pertumbuhan yang besar karena bergerak pada pasar digital yang luas, namun tetap memiliki target realistis untuk akuisisi awal.

Dengan demikian, SafeWash tidak hanya memiliki ide yang valid secara masalah, tetapi juga menunjukkan potensi pertumbuhan yang jelas dan dapat dieksekusi.

## 1.7 Keunggulan Kompetitif

Keunggulan kompetitif SafeWash antara lain:

- fokus pada masalah nyata merchant laundry UMKM,
- memiliki fitur tracking QR yang langsung meningkatkan trust,
- menyediakan sistem klaim yang terdokumentasi,
- mendukung admin analytics dan merchant management,
- model bisnis ringan bagi merchant karena onboarding gratis.

## 1.8 Proyeksi Pengembangan

Pengembangan SafeWash dapat diarahkan ke:

- integrasi pembayaran digital,
- notifikasi WhatsApp gateway,
- integrasi pickup-delivery,
- scoring performa merchant,
- loyalty program customer,
- white-label solution untuk jaringan laundry.

## 1.9 Kesimpulan Executive Summary

SafeWash adalah startup digital yang relevan dengan kebutuhan pasar laundry UMKM di Indonesia. Startup ini menawarkan solusi konkret terhadap masalah kehilangan pakaian, kurangnya transparansi, dan lemahnya dokumentasi komplain. Dengan model bisnis berbasis komisi dan service fee, serta target pasar yang jelas dan terstruktur, SafeWash memiliki potensi tumbuh sebagai platform operasional laundry digital yang scalable.

---

# 2. FUNCTIONAL REQUIREMENT SPECIFICATION (FRS)

## 2.1 Tujuan Dokumen

Dokumen FRS ini disusun untuk mendefinisikan kebutuhan fungsional dan nonfungsional sistem SafeWash secara jelas, terstruktur, dan dapat digunakan sebagai acuan pengembangan produk.

## 2.2 Ruang Lingkup Sistem

Sistem SafeWash mencakup:

- manajemen akun berdasarkan role,
- manajemen merchant laundry,
- manajemen outlet laundry,
- pembuatan dan pengelolaan order laundry,
- tracking status laundry,
- notifikasi kepada customer,
- sistem klaim barang hilang,
- dashboard admin, merchant, dan customer,
- laporan dan analitik admin.

## 2.3 Aktor Sistem

### 2.3.1 Admin

Admin adalah pemilik platform yang memiliki kewenangan:

- melihat dashboard admin,
- memantau performa platform,
- mengelola merchant,
- mengaktifkan/nonaktifkan merchant,
- melihat laporan dan analitik,
- mengekspor laporan.

### 2.3.2 Merchant Laundry

Merchant adalah mitra laundry yang menggunakan platform untuk:

- registrasi merchant,
- mengelola profil laundry,
- membuat order laundry,
- memperbarui status order,
- mengelola klaim,
- memantau pendapatan merchant.

### 2.3.3 Customer

Customer adalah pengguna jasa laundry yang:

- melakukan login/registrasi,
- melihat order,
- melacak status laundry,
- mengajukan klaim jika terjadi masalah.

## 2.4 Kebutuhan Fungsional

### 2.4.1 Modul Autentikasi dan Otorisasi

#### F-01 Registrasi Customer
- Sistem harus memungkinkan customer membuat akun.

#### F-02 Registrasi Merchant
- Sistem harus memungkinkan merchant laundry mendaftar ke platform secara gratis.

#### F-03 Login
- Sistem harus memungkinkan admin, merchant, dan customer login sesuai kredensial.

#### F-04 Logout
- Sistem harus memungkinkan pengguna keluar dari sistem dengan aman.

#### F-05 Otorisasi Berbasis Role
- Sistem harus membedakan akses berdasarkan role `admin`, `merchant`, dan `customer`.

### 2.4.2 Modul Merchant Management

#### F-06 Admin Melihat Daftar Merchant
- Sistem harus menampilkan seluruh merchant kepada admin.

#### F-07 Search Merchant
- Admin dapat mencari merchant berdasarkan nama outlet, nama owner, email, telepon, atau alamat.

#### F-08 Filter Merchant
- Admin dapat memfilter merchant berdasarkan status aktif/nonaktif.

#### F-09 Toggle Status Merchant
- Admin dapat mengubah status merchant aktif/nonaktif tanpa reload halaman.

#### F-10 Edit Merchant
- Admin dapat memperbarui data merchant seperti nama, alamat, plan, komisi, service fee, dan proteksi premium.

### 2.4.3 Modul Order Laundry

#### F-11 Merchant Membuat Order
- Merchant harus dapat membuat order laundry baru.

#### F-12 Pencatatan Customer pada Order
- Sistem harus menyimpan data customer yang terkait dengan order.

#### F-13 Pencatatan Item Laundry
- Sistem harus memungkinkan pencatatan item-item pada setiap order.

#### F-14 Generate Tracking Code
- Sistem harus membuat kode tracking unik untuk setiap order.

#### F-15 Generate QR Token
- Sistem harus membuat token QR unik untuk setiap order.

#### F-16 Update Status Order
- Merchant harus dapat memperbarui status order.

#### F-17 Riwayat Status
- Sistem harus menyimpan histori tracking update setiap perubahan status.

### 2.4.4 Modul Tracking dan Customer Experience

#### F-18 Lihat Tracking via Kode
- Customer dapat melihat status laundry melalui tracking code.

#### F-19 Lihat Tracking via QR
- Customer dapat melihat status laundry melalui QR token.

#### F-20 Lihat Riwayat Order
- Customer dapat melihat daftar order yang terkait dengan akunnya.

### 2.4.5 Modul Notifikasi

#### F-21 Catat Log Notifikasi
- Sistem harus menyimpan log notifikasi untuk setiap order.

#### F-22 Notifikasi Perubahan Status
- Sistem harus mencatat notifikasi saat status order diperbarui.

### 2.4.6 Modul Klaim

#### F-23 Ajukan Klaim
- Customer dapat mengajukan klaim kehilangan barang.

#### F-24 Lihat Klaim
- Merchant dan admin dapat melihat klaim yang masuk.

#### F-25 Update Status Klaim
- Sistem harus menyimpan status klaim seperti submitted, investigating, dan resolved.

### 2.4.7 Modul Dashboard Admin

#### F-26 Dashboard Analitik Admin
- Admin dapat melihat gross revenue, admin revenue, merchant revenue, rata-rata order, jumlah merchant aktif/nonaktif, dan order aktif.

#### F-27 Chart Revenue
- Sistem harus menampilkan grafik pemasukan admin vs pendapatan merchant.

#### F-28 Chart Multi-Series Order
- Sistem harus menampilkan grafik multi-series jumlah order per bulan.

#### F-29 Date Range Filter
- Admin dapat memfilter dashboard berdasarkan rentang tanggal.

#### F-30 Export Excel
- Admin dapat mengekspor laporan ke format Excel.

#### F-31 Export PDF
- Admin dapat mengekspor laporan ke format PDF.

### 2.4.8 Modul Dashboard Merchant

#### F-32 Dashboard Merchant
- Merchant dapat melihat order aktif, omzet, komisi admin, service fee, dan estimasi pendapatan bersih.

#### F-33 Kelola Profil Laundry
- Merchant dapat memperbarui profil outlet laundry.

### 2.4.9 Modul Dashboard Customer

#### F-34 Dashboard Customer
- Customer dapat melihat order aktif, riwayat order, dan klaim.

## 2.5 Kebutuhan Nonfungsional

### NFR-01 Usability
- Antarmuka harus mudah digunakan oleh merchant UMKM yang tidak semuanya memiliki latar belakang teknis.

### NFR-02 Performance
- Halaman dashboard harus merespons dengan cepat untuk skala awal.

### NFR-03 Security
- Sistem harus menerapkan autentikasi, otorisasi role, proteksi CSRF, dan penyimpanan password terenkripsi.

### NFR-04 Availability
- Sistem harus dapat diakses secara online melalui browser.

### NFR-05 Scalability
- Sistem harus dapat dikembangkan untuk mendukung lebih banyak merchant dan transaksi.

### NFR-06 Maintainability
- Sistem harus memiliki struktur modul yang jelas agar mudah dipelihara dan dikembangkan.

## 2.6 Data Utama yang Dikelola

Data utama SafeWash meliputi:

- data pengguna,
- data merchant laundry,
- data order laundry,
- data item laundry,
- data tracking update,
- data klaim,
- data notifikasi.

## 2.7 Asumsi Sistem

- Merchant mendaftar ke platform secara gratis.
- Pendapatan platform diperoleh dari komisi 15% dan service fee 3%.
- Akses customer berbasis akun dan/atau tracking code.
- Sistem dikembangkan sebagai web application berbasis Laravel.

## 2.8 Batasan Sistem

- Integrasi payment gateway belum menjadi fitur inti tahap awal.
- Integrasi WhatsApp gateway pada tahap awal masih dapat berupa log notifikasi.
- Sistem pengiriman/pickup belum menjadi modul utama pada versi awal.

---

# 3. END-TO-END (E2E) PROCESS

## 3.1 Tujuan Dokumen E2E

Dokumen E2E menjelaskan alur lengkap penggunaan sistem SafeWash dari awal hingga akhir, mulai dari merchant mendaftar, customer menerima layanan, hingga admin memantau performa platform.

## 3.2 E2E Flow 1: Onboarding Merchant

### Langkah Alur

1. Merchant membuka halaman registrasi.
2. Merchant memilih role `merchant`.
3. Merchant mengisi data akun.
4. Sistem membuat akun merchant.
5. Sistem otomatis membuat data outlet laundry awal.
6. Merchant masuk ke dashboard merchant.
7. Merchant melengkapi profil outlet.

### Output

- akun merchant aktif,
- outlet laundry terdaftar,
- merchant siap membuat order.

## 3.3 E2E Flow 2: Pembuatan Order Laundry

### Langkah Alur

1. Merchant login ke dashboard.
2. Merchant memilih menu pembuatan order.
3. Merchant mengisi data customer.
4. Merchant mengisi detail layanan dan item.
5. Sistem menyimpan order.
6. Sistem membuat tracking code unik.
7. Sistem membuat QR token.
8. Sistem mencatat tracking update pertama.
9. Sistem mencatat log notifikasi awal.

### Output

- order berhasil dibuat,
- customer dapat melacak order.

## 3.4 E2E Flow 3: Tracking Laundry oleh Customer

### Langkah Alur

1. Customer menerima tracking code atau QR.
2. Customer membuka halaman tracking.
3. Customer memasukkan tracking code atau scan QR.
4. Sistem menampilkan status order.
5. Customer melihat histori update.

### Output

- customer mengetahui progres laundry secara transparan.

## 3.5 E2E Flow 4: Update Status oleh Merchant

### Langkah Alur

1. Merchant membuka detail order.
2. Merchant memilih status baru.
3. Merchant mengisi judul dan deskripsi update.
4. Sistem memperbarui status order.
5. Sistem menambahkan tracking update.
6. Sistem mencatat log notifikasi.
7. Customer dapat melihat update terbaru.

### Output

- histori order terdokumentasi,
- customer mendapatkan transparansi layanan.

## 3.6 E2E Flow 5: Klaim Barang Hilang

### Langkah Alur

1. Customer membuka detail order.
2. Customer memilih menu klaim.
3. Customer mengisi data klaim.
4. Sistem menyimpan klaim dengan status `submitted`.
5. Merchant dan admin dapat melihat klaim.
6. Merchant/admin melakukan investigasi.
7. Status klaim diperbarui menjadi `investigating` atau `resolved`.

### Output

- proses klaim terdokumentasi dengan baik,
- risiko konflik merchant-customer dapat ditekan.

## 3.7 E2E Flow 6: Pengelolaan Merchant oleh Admin

### Langkah Alur

1. Admin login.
2. Admin membuka halaman manajemen merchant.
3. Admin melakukan pencarian/filter merchant.
4. Admin melihat data performa merchant.
5. Admin mengubah status merchant aktif/nonaktif.
6. Admin memperbarui biaya komisi, fee, dan plan jika diperlukan.

### Output

- merchant platform terkelola dengan lebih baik,
- admin dapat menjaga kualitas merchant yang aktif di platform.

## 3.8 E2E Flow 7: Monitoring dan Pelaporan Admin

### Langkah Alur

1. Admin membuka dashboard analytics.
2. Admin memilih rentang tanggal.
3. Sistem menampilkan KPI dan grafik.
4. Admin melihat chart revenue dan chart order bulanan.
5. Admin mengekspor laporan PDF atau Excel.

### Output

- admin memperoleh insight bisnis,
- laporan siap digunakan untuk evaluasi dan pengambilan keputusan.

## 3.9 Ringkasan E2E Berdasarkan Aktor

### Admin
- monitoring platform,
- kelola merchant,
- analitik,
- export laporan.

### Merchant
- onboarding gratis,
- input order,
- update status,
- kelola klaim.

### Customer
- tracking order,
- lihat histori,
- klaim barang hilang.

## 3.10 Indikator Keberhasilan E2E

SafeWash dianggap berjalan efektif bila:

- merchant dapat membuat order tanpa hambatan,
- customer dapat melacak order dengan mudah,
- status order terdokumentasi secara real-time,
- klaim dapat ditelusuri secara digital,
- admin dapat mengelola merchant dan membaca performa platform dari dashboard.

---

# 4. REFERENSI

1. Contoh struktur target pasar dari dokumen dosen `target pasar nyalain.id.pdf` yang diberikan pengguna.  
2. APJII, “Jumlah Pengguna Internet Indonesia Tembus 221 Juta Orang,” 7 Februari 2024. Link: https://apjii.or.id/berita/d/apjii-jumlah-pengguna-internet-indonesia-tembus-221-juta-orang  
3. BPS, “Profil Industri Mikro dan Kecil 2023,” rilis 18 September 2024. Link: https://www.bps.go.id/id/publication/2024/09/18/52d85cbe9de005b6f5d69f95/profil-industri-mikro-dan-kecil-2023.html  
4. BPS, “Statistik Karakteristik Usaha 2022/2023,” rilis 22 Desember 2023. Link: https://www.bps.go.id/id/publication/2023/12/22/140fcce371d95181d426827c/business-characteristics-statistics-2022-2023.html  

## Catatan Akademik

Untuk bagian **ukuran pasar**, angka TAM, BAM/SAM, dan SOM SafeWash disusun menggunakan kombinasi:

- data resmi yang tersedia,
- karakteristik produk SafeWash,
- serta **asumsi bisnis konservatif** untuk kebutuhan akademik dan validasi awal model startup.

Angka-angka tersebut dapat dikembangkan lebih lanjut melalui survei lapangan, wawancara merchant laundry, dan riset pasar primer.


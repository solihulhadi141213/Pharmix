# Pharmix V.1.0.0

Pharmix adalah aplikasi manajemen apotek dan fasilitas kesehatan berbasis web. Aplikasi ini membantu pengelolaan pengguna, master data, resep, stok, transaksi operasional, transaksi jual-beli, pembayaran, akuntansi, laporan, dan integrasi SATUSEHAT.

Pharmix dibangun menggunakan PHP native, MySQL/MariaDB, Bootstrap 5, dan jQuery. Sebagian proses pada halaman menggunakan AJAX sehingga data dapat dimuat tanpa memuat ulang seluruh halaman.

> [!NOTE]
> Project/repository Pharmix bersifat **gratis dan open source**. Siapa saja dapat menggunakan, memodifikasi, dan mengubah aplikasi ini sesuai keinginan dan kebutuhan dengan mengikuti ketentuan [Lisensi Apache 2.0](./LICENSE).
>
> Untuk menghubungi pembuat, silakan melalui WhatsApp [089601154726](https://wa.me/6289601154726) atau email [dhiforester@gmail.com](mailto:dhiforester@gmail.com).

## Fitur

Daftar berikut mengikuti susunan menu aplikasi pada [`_Partial/Menu.php`](./_Partial/Menu.php). Akses ke masing-masing modul mengikuti izin pengguna.

### Dashboard

- **Dashboard** — Menampilkan ringkasan data, aktivitas operasional, informasi barang dan transaksi, serta grafik sesuai periode yang dipilih.

### Master

- **Index Obat/Alkes** — Mengelola katalog obat dan alat kesehatan, termasuk sediaan, komposisi, kode KFA, dan ID Medication SATUSEHAT. Data dapat ditambahkan secara manual atau dari KFA, serta diimpor dan diekspor.
- **Pasien** — Mengelola identitas pasien, nomor rekam medis, dan ID IHS untuk mendukung pelayanan serta integrasi SATUSEHAT.
- **Kunjungan** — Mencatat kunjungan pasien, tanggal, kategori, prioritas, poliklinik, tenaga kesehatan, dan status pelayanan, serta mengirim data Encounter ke SATUSEHAT.
- **Resep** — Mengelola resep dan rincian obat, dokter, apoteker, aturan penggunaan, serta pencetakan resep. Mendukung pencarian resep berdasarkan Nomor Resep Nasional dan proses SATUSEHAT melalui Medication, MedicationRequest, MedicationDispense, serta DocumentReference.
- **Supplier** — Menyimpan dan memperbarui data pemasok untuk kebutuhan transaksi pembelian barang.

### Inventaris

- **Master Barang** — Mengelola data barang, stok, beberapa satuan, harga dan kategori harga, serta riwayat transaksi barang. Tersedia fasilitas import, export, dan backup data pada modul barang.
- **Batch & Expired** — Mengelola batch barang dan tanggal kedaluwarsa untuk membantu pemantauan persediaan.
- **Stock Opname** — Mencatat pemeriksaan stok fisik dan membandingkannya dengan stok pada aplikasi untuk mengetahui selisih persediaan.

### Transaksi

- **Kategori Operasional** — Mengatur jenis transaksi operasional agar pencatatan dan pelaporan dapat dikelompokkan sesuai kebutuhan.
- **Transaksi Operasional** — Mencatat transaksi operasional beserta rincian dan informasi pembayarannya.
- **Transaksi Penjualan** — Mengelola penjualan barang, rincian item, diskon, PPN, pembayaran, kembalian, dan pencetakan invoice.
- **Transaksi Pembelian** — Mengelola pembelian dari supplier beserta rincian barang, diskon, PPN, pembayaran, dan dokumen transaksi.

### Keuangan

- **Akun Perkiraan** — Mengelola daftar akun akuntansi yang digunakan dalam jurnal dan laporan keuangan.
- **Utang/Piutang** — Memantau kewajiban dan tagihan dari transaksi yang belum lunas beserta rincian pembayarannya.
- **Pembayaran** — Mencatat dan menelusuri pembayaran yang terkait dengan transaksi aplikasi.

### Laporan

- **Jurnal** — Menampilkan pencatatan debit dan kredit transaksi sebagai dasar pembukuan.
- **Buku Besar** — Menyajikan mutasi dan saldo transaksi berdasarkan akun serta periode yang dipilih.
- **Neraca Saldo** — Menampilkan ringkasan saldo debit dan kredit setiap akun untuk pemeriksaan pembukuan.
- **Laba Rugi** — Menyajikan laporan pendapatan dan beban untuk mengetahui hasil usaha pada suatu periode.
- **Operasional** — Merekap transaksi operasional berdasarkan periode dan filter laporan.
- **Jual/Beli** — Merekap transaksi penjualan dan pembelian untuk memantau aktivitas perdagangan.

### Pengaturan

- **Pengaturan Umum** — Mengatur identitas aplikasi dan informasi umum fasilitas atau usaha.
- **Auto Jurnal** — Mengatur pemetaan akun untuk mendukung pencatatan jurnal otomatis dari transaksi.
- **Email Gateway** — Mengatur layanan pengiriman email aplikasi dan menguji konfigurasi pengirimannya.
- **SATUSEHAT** — Mengatur koneksi, kredensial, dan token akses serta menguji koneksi integrasi SATUSEHAT.

### Aksesibilitas

- **Fitur Aplikasi** — Mengelola daftar fitur yang menjadi dasar pemberian izin akses.
- **Entitas Akses** — Mengelola kelompok atau entitas akses beserta pengaturan izin fiturnya.
- **Akses Pengguna** — Mengelola akun pengguna dan izin fitur yang dapat diakses oleh masing-masing pengguna.

### Referensi

- **Route** — Mengelola referensi rute pemberian obat untuk melengkapi informasi resep.
- **Sediaan** — Mengelola referensi bentuk sediaan obat untuk data Medication.
- **Satuan Dosis** — Mengelola referensi satuan yang digunakan dalam penulisan dosis obat.
- **Denominator** — Mengelola referensi satuan penyebut pada informasi komposisi atau kekuatan obat dalam Medication.
- **Numerator** — Mengelola referensi satuan pembilang pada informasi komposisi atau kekuatan obat dalam Medication.
- **Poliklinik** — Mengelola data dan status poliklinik, termasuk pencarian serta pemilihan ID Location SATUSEHAT.
- **Nakes** — Mengelola data tenaga kesehatan untuk mendukung pencatatan kunjungan dan resep.

### Sistem dan Fitur Lainnya

- **Log Aktivitas** — Menelusuri catatan aktivitas umum, email, dan API untuk memantau penggunaan aplikasi.
- **Dokumentasi** — Mengelola dokumentasi aplikasi sebagai sumber informasi penggunaan dan pengembangan.
- **Bantuan** — Menampilkan panduan penggunaan yang dapat dicari berdasarkan judul atau deskripsi dan disaring menurut topik/tag.
- **Keluar** — Mengakhiri sesi pengguna melalui konfirmasi logout.
- **Login dan Profil Pengguna** — Mendukung login dengan validasi sesi berbasis token serta pengelolaan identitas, foto profil, dan password melalui menu profil.

## Teknologi dan Dependency

### Backend

- PHP 8.1 atau versi yang kompatibel dengan dependency project.
- MySQL atau MariaDB.
- Apache atau Nginx dengan PHP-FPM.
- Composer.
- Ekstensi PHP yang umum diperlukan: `mysqli`, `curl`, `json`, `mbstring`, `fileinfo`, `openssl`, `zip`, dan `gd`.

### Frontend dan library

- Bootstrap `5.3.x`.
- Bootstrap Icons.
- jQuery `3.7.x`.
- SweetAlert2.
- ApexCharts.
- Quill.
- Select2.
- html2canvas dan jsPDF.
- jsqr, signature_pad, dan library frontend lain pada `package.json`.

### Dependency PHP

- PhpSpreadsheet untuk kebutuhan spreadsheet/import/export.
- Daftar lengkap dependency tersedia pada [`composer.json`](./composer.json).

## Struktur Direktori

```text
Pharmix/
├── _Config/       Konfigurasi database, session, helper, dan pengaturan aplikasi
├── _Page/         Halaman dan proses setiap modul aplikasi
├── _Partial/      Layout, menu, modal, routing, dan komponen bersama
├── assets/        CSS, JavaScript, font, dan gambar
├── db/            File SQL database
├── vendor/        Dependency PHP dari Composer
├── node_modules/  Dependency frontend dari npm
├── index.php      Entry point dan routing halaman
├── Login.php      Halaman login
├── composer.json  Konfigurasi dependency PHP
└── package.json   Konfigurasi dependency frontend
```

## Modul yang Terdaftar

Modul utama yang tersedia pada routing aplikasi meliputi:

`Dashboard`, `Akses`, `AksesFitur`, `AksesEntitas`, `MyProfile`, `Medication`, `Pasien`, `Kunjungan`, `Resep`, `Supplier`, `Barang`, `BarangExpired`, `StockOpename`, `JenisTransaksi`, `Transaksi`, `Penjualan`, `Pembelian`, `Pembayaran`, `UtangPiutang`, `RekapTransaksi`, `RekapJualBeli`, `RekapitulasiTransaksi`, `AkunPerkiraan`, `Jurnal`, `BukuBesar`, `NeracaSaldo`, `LabaRugi`, `AutoJurnal`, `Dokumentasi`, `Bantuan`, `Aktivitas`, `SettingGeneral`, `SettingEmailGateway`, `SettingSatuSehat`, `Route`, `Sediaan`, `SatuanDosis`, `Denominator`, `Numerator`, `Poliklinik`, dan `Nakes`.

Folder lain seperti `Anggota`, `ApiDoc`, `CetakInvoice`, `RiwayatAnggota`, `ResetPassword`, serta `TransaksiJualBeli` berisi halaman/proses pendukung atau bagian dari alur modul utama.

## Instalasi Umum Aplikasi PHP

Langkah berikut berlaku secara umum untuk aplikasi PHP native yang dijalankan pada web server lokal maupun server produksi.

### 1. Siapkan server

Pasang komponen berikut pada server:

- Web server Apache atau Nginx.
- PHP dan ekstensi yang dibutuhkan aplikasi.
- MySQL atau MariaDB.
- Composer.
- Node.js dan npm jika dependency frontend perlu dipasang ulang.

Pastikan versi PHP yang aktif di command line sama dengan versi PHP yang digunakan web server:

```bash
php -v
composer --version
node -v
npm -v
```

### 2. Tempatkan source code

Clone repository atau salin source code ke document root web server. Contoh lokasi umum:

- Apache Linux: `/var/www/html/Pharmix`
- XAMPP: `htdocs/Pharmix`
- WAMP: `www/Pharmix`
- Nginx: direktori `root` pada konfigurasi virtual host

Document root sebaiknya mengarah ke folder project yang berisi `index.php`.

### 3. Pasang dependency

Jalankan perintah dari folder project:

```bash
composer install
npm install
```

`composer install` memasang dependency PHP ke folder `vendor`, sedangkan `npm install` memasang dependency frontend ke folder `node_modules`.

### 4. Buat dan isi database

1. Buat database dengan nama `pharmix`, atau gunakan nama lain sesuai konfigurasi.
2. Import file [`db/pharmix.sql`](./db/pharmix.sql) melalui phpMyAdmin, MySQL client, atau tool database lain.

Contoh melalui MySQL client:

```bash
mysql -u root -p pharmix < db/pharmix.sql
```

### 5. Atur koneksi database

Edit [`_Config/Connection.php`](./_Config/Connection.php) dan sesuaikan host, username, password, serta nama database:

```php
$servername = "localhost";
$username   = "root";
$password   = "password_database";
$db         = "pharmix";
```

Jangan menggunakan password database contoh pada server produksi. Simpan kredensial menggunakan konfigurasi server atau environment variable bila memungkinkan.

### 6. Atur document root dan permission

Pastikan web server memiliki akses baca ke seluruh source code dan akses tulis hanya pada direktori yang memang digunakan untuk upload/cache. Hindari memberikan permission tulis penuh pada seluruh folder project.

Untuk Apache, aktifkan modul PHP dan rewrite yang dibutuhkan oleh konfigurasi server. Untuk Nginx, arahkan request `.php` ke PHP-FPM dan pastikan `index.php` menjadi file index.

### 7. Jalankan aplikasi

Buka URL sesuai document root, misalnya:

```text
http://localhost/Pharmix/
```

Entry point aplikasi adalah [`index.php`](./index.php). Halaman login tersedia pada [`Login.php`](./Login.php).

Untuk pengujian lokal sederhana, PHP built-in server juga dapat digunakan jika konfigurasi database dapat diakses:

```bash
php -S localhost:8000
```

Kemudian buka `http://localhost:8000/` pada browser.

### 8. Konfigurasi opsional

Setelah berhasil login, lakukan konfigurasi sesuai kebutuhan:

- Pengaturan umum aplikasi.
- Data akses dan izin fitur.
- Email gateway.
- Koneksi SATUSEHAT dan token akses.
- Data master apotek.
- Akun perkiraan dan auto jurnal.

Integrasi SATUSEHAT dan email memerlukan kredensial serta konfigurasi layanan masing-masing. Fitur tersebut tidak dapat digunakan hanya dengan mengimpor database tanpa konfigurasi tambahan.

## Login dan Hak Akses

Login menggunakan email, password, dan validasi keamanan yang tersedia pada halaman login. Setelah login, akses ke halaman dan proses aplikasi diperiksa berdasarkan session serta izin fitur pengguna.

Jika sesi berakhir, lakukan login ulang. Untuk pengguna baru, administrator perlu menambahkan akses, fitur, dan izin yang sesuai sebelum seluruh menu dapat digunakan.

## Catatan Pengembangan

- Routing halaman utama menggunakan parameter `Page` pada [`index.php`](./index.php).
- Routing JavaScript dan modal dikelola melalui file pada `_Partial`.
- Banyak proses list, pencarian, filter, pagination, dan form menggunakan AJAX.
- Output proses AJAX umumnya menggunakan response JSON dengan properti `status`, `message`, dan/atau `html`.
- Validasi sesi dilakukan pada proses server, bukan hanya pada antarmuka browser.
- Gunakan prepared statement untuk query baru dan lakukan escaping output HTML.
- File SQL perlu diperbarui bersama perubahan schema database.

## Troubleshooting Singkat

### Database gagal terhubung

Periksa service MySQL/MariaDB, nama database, username, password, dan konfigurasi pada `_Config/Connection.php`.

### Halaman menampilkan error dependency

Jalankan kembali `composer install` dan `npm install`, lalu pastikan folder `vendor` dan `node_modules` tersedia.

### Session atau login tidak berjalan

Periksa konfigurasi session PHP, permission direktori penyimpanan session, waktu server, dan validitas tabel `akses_login`.

### Fitur SATUSEHAT gagal

Periksa konfigurasi koneksi, URL service, client key, secret key, sertifikat SSL, serta status koneksi pada menu SATUSEHAT.

## Lisensi

Lisensi project tercantum pada file [`LICENSE`](./LICENSE).

## Informasi Versi

- Nama aplikasi: Pharmix
- Versi: `V.1.0.0`
- Database utama: `pharmix`
- File schema: [`db/pharmix.sql`](./db/pharmix.sql)


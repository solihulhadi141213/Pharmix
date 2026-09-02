# Pharmix V.1.0.0

Pharmix adalah aplikasi manajemen apotek dan fasilitas kesehatan berbasis web. Aplikasi ini membantu pengelolaan pengguna, master data, resep, stok, transaksi operasional, transaksi jual-beli, pembayaran, akuntansi, laporan, dan integrasi SATUSEHAT.

Pharmix dibangun menggunakan PHP native, MySQL/MariaDB, Bootstrap 5, dan jQuery. Sebagian proses pada halaman menggunakan AJAX sehingga data dapat dimuat tanpa memuat ulang seluruh halaman.

## Fitur

### Dashboard

- Ringkasan data dan aktivitas operasional.
- Informasi transaksi, barang, penjualan, dan pembelian.
- Grafik ringkasan sesuai periode.

### Akses dan Profil

- Login dan validasi sesi berbasis token.
- Pengelolaan pengguna akses.
- Pengelolaan fitur aplikasi dan entitas akses.
- Pengaturan izin fitur per pengguna.
- Profil pengguna.
- Perubahan identitas, foto profil, dan password.
- Pencatatan aktivitas pengguna.

### Master Data

- Pasien dan anggota.
- Supplier.
- Barang, satuan, harga, dan kategori harga.
- Batch barang dan tanggal kedaluwarsa.
- Resep.
- Jenis transaksi operasional.
- Akun perkiraan.

### Referensi Kesehatan

- Route.
- Sediaan.
- Satuan dosis.
- Denominator.
- Numerator.
- Poliklinik.
- Tenaga kesehatan (Nakes).

Modul Poliklinik mendukung pencarian data Location SATUSEHAT, pemilihan ID Location, pencarian/filter data, pagination, dan pengelolaan status poliklinik.

### Transaksi

- Transaksi operasional.
- Transaksi penjualan.
- Transaksi pembelian.
- Rincian barang dan layanan transaksi.
- Diskon, PPN, pembayaran, dan kembalian.
- Pembatalan, perubahan, dan penghapusan transaksi sesuai izin akses.
- Cetak invoice dan dokumen transaksi.
- Rekapitulasi transaksi operasional dan jual-beli.

### Stok dan Inventaris

- Pengelolaan stok barang.
- Banyak satuan dan harga barang.
- Batch dan barang kedaluwarsa.
- Stock opname.
- Riwayat transaksi barang.
- Import, export, dan backup data barang pada modul yang tersedia.

### Keuangan dan Laporan

- Pembayaran.
- Utang dan piutang.
- Jurnal transaksi.
- Buku besar.
- Neraca saldo.
- Laba rugi.
- Auto jurnal.
- Rekapitulasi transaksi.

### Pengaturan dan Integrasi

- Pengaturan umum aplikasi.
- Email gateway.
- Konfigurasi dan pengujian koneksi SATUSEHAT.
- Dokumentasi aplikasi dan API.
- Aktivitas umum, email, dan API.

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

`Dashboard`, `Akses`, `AksesFitur`, `AksesEntitas`, `MyProfile`, `Pasien`, `Resep`, `Supplier`, `Barang`, `BarangExpired`, `StockOpename`, `JenisTransaksi`, `Transaksi`, `Penjualan`, `Pembelian`, `Pembayaran`, `UtangPiutang`, `RekapTransaksi`, `RekapJualBeli`, `RekapitulasiTransaksi`, `AkunPerkiraan`, `Jurnal`, `BukuBesar`, `NeracaSaldo`, `LabaRugi`, `AutoJurnal`, `Dokumentasi`, `Aktivitas`, `SettingGeneral`, `SettingEmailGateway`, `SettingSatuSehat`, `Route`, `Sediaan`, `SatuanDosis`, `Denominator`, `Numerator`, `Poliklinik`, dan `Nakes`.

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


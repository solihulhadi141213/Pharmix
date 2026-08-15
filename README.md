# Pharmix V.1.0.0

Pharmix adalah aplikasi manajemen apotek berbasis web yang dapat digunakan untuk apotek biasa maupun fasilitas kesehatan. Aplikasi ini dirancang untuk membantu pengelolaan data barang, transaksi jual-beli, stok, utang/piutang, jurnal keuangan, laporan, hingga pengaturan akses pengguna dalam satu sistem terpusat.

## Gambaran Singkat

Pharmix dibangun dengan arsitektur PHP native dan MySQL, dengan antarmuka berbasis Bootstrap serta komponen pendukung seperti jQuery, SweetAlert2, ApexCharts, dan berbagai library ekspor/cetak laporan. Aplikasi ini mendukung proses operasional harian seperti:

- Login dengan captcha
- Manajemen akses pengguna dan hak fitur
- Pengelolaan master data apotek
- Transaksi penjualan dan pembelian
- Pencatatan transaksi operasional
- Jurnal dan buku besar
- Neraca saldo dan laba rugi
- Rekap transaksi dan laporan riwayat
- Monitoring aktivitas sistem
- Pengaturan layanan email, pembayaran, dan notifikasi

## Fitur Utama

### 1. Dashboard

Dashboard menampilkan ringkasan data dan informasi operasional penting, termasuk jumlah data transaksi, barang, pembelian, penjualan, serta grafik ringkasan sesuai periode yang dipilih.

### 2. Manajemen Akses

Modul akses digunakan untuk mengatur keamanan dan pembagian hak pengguna, meliputi:

- Fitur aplikasi
- Entitas akses
- Data akses pengguna
- Pengaturan izin fitur per pengguna/role
- Profil pengguna
- Ubah password dan foto profil

### 3. Master Data

Pharmix menyediakan pengelolaan data inti yang umum dipakai di apotek dan fasilitas kesehatan:

- Data pasien
- Data supplier
- Data barang
- Kategori harga
- Satuan barang
- Data batch dan expired date
- Data akun perkiraan
- Jenis transaksi

### 4. Transaksi Operasional

Modul transaksi operasional mendukung pencatatan aktivitas non-penjualan/non-pembelian yang tetap perlu dibukukan, lengkap dengan:

- Kategori operasional
- Input transaksi operasional
- Jurnal transaksi
- Rekap transaksi operasional

### 5. Transaksi Jual Beli

Modul ini menjadi inti proses bisnis aplikasi, dengan dukungan untuk:

- Transaksi penjualan
- Transaksi pembelian
- Detail item transaksi
- Pembayaran transaksi
- Diskon, PPN, dan perhitungan kembalian
- Riwayat transaksi
- Pembatalan, edit, dan hapus transaksi
- Cetak invoice dan preview cetak
- Rekap jual/beli
- Laporan utang/piutang

### 6. Stok dan Barang

Manajemen barang dibuat cukup lengkap untuk kebutuhan apotek:

- Tambah, edit, hapus, dan detail barang
- Import dan export data barang
- Riwayat transaksi barang
- Pengelolaan banyak satuan
- Pengelolaan banyak harga
- Stok opname
- Pemantauan batch dan barang expired
- Backup data barang

### 7. Keuangan dan Akuntansi

Pharmix juga mendukung pencatatan akuntansi sederhana hingga laporan keuangan:

- Akun perkiraan
- Jurnal keuangan
- Buku besar
- Neraca saldo
- Laba rugi
- Auto jurnal
- Rekapitulasi transaksi

### 8. Utang dan Piutang

Modul utang/piutang digunakan untuk memantau transaksi yang belum lunas, histori pembayaran, dan pengelolaan pembayaran lanjutan.

### 9. Modul Anggota

Selain data apotek umum, sistem juga menyediakan modul anggota untuk kebutuhan operasional yang melibatkan data member, simpanan, pinjaman, dan transaksi terkait.

### 10. Layanan dan Integrasi

Aplikasi menyediakan pengaturan layanan yang dapat disesuaikan, seperti:

- Email gateway
- Whatsapp service
- Payment service
- Pengujian pengiriman email
- Pengujian token pembayaran
- Tombol/generate button layanan tertentu

### 11. Dokumentasi dan Aktivitas

Pharmix memiliki fitur pendukung untuk administrasi sistem:

- Log aktivitas umum
- Aktivitas email
- Aktivitas API
- Dokumentasi aplikasi
- Halaman bantuan

## Teknologi yang Digunakan

- PHP
- MySQL / MariaDB
- Bootstrap 5
- jQuery
- SweetAlert2
- ApexCharts
- Quill
- html2canvas
- jsPDF
- PhpSpreadsheet
- QR Code library
- Barcode library

## Kebutuhan Sistem

Sebelum menjalankan aplikasi, pastikan environment memiliki:

- PHP yang kompatibel dengan project
- MySQL / MariaDB
- Web server seperti Apache
- Composer
- Node.js dan npm

## Instalasi

1. Clone atau salin project ke folder web server, misalnya `htdocs` atau `www`.
2. Buat database MySQL, lalu impor struktur dan data dari folder `db` jika tersedia.
3. Atur koneksi database pada file [`_Config/Connection.php`](./_Config/Connection.php).
4. Jalankan `composer install` untuk dependency PHP.
5. Jalankan `npm install` untuk dependency frontend.
6. Pastikan folder `node_modules` dan `vendor` terpasang dengan benar.
7. Buka aplikasi melalui browser, lalu login melalui halaman [`Login.php`](./Login.php).

## Cara Login

Halaman login menggunakan:

- Email
- Password
- Captcha untuk validasi keamanan

Setelah login berhasil, pengguna diarahkan ke dashboard utama melalui [`index.php`](./index.php).

## Struktur Aplikasi

Beberapa folder utama di proyek ini adalah:

- `_Config` untuk konfigurasi koneksi, session, setting umum, dan helper global
- `_Partial` untuk komponen layout seperti menu, header, modal, routing, dan notifikasi
- `_Page` untuk seluruh modul aplikasi
- `assets` untuk CSS, JS, font, dan image
- `db` untuk data atau backup basis data

## Modul yang Tersedia

Berikut ringkasan modul utama yang ditemukan pada aplikasi:

- Dashboard
- Akses
- Akses Fitur
- Akses Entitas
- Pasien
- Supplier
- Barang
- Barang Expired
- Stock Opname
- Jenis Transaksi
- Transaksi Operasional
- Rekap Transaksi
- Transaksi Penjualan
- Transaksi Pembelian
- Rekap Jual/Beli
- Utang/Piutang
- Akun Perkiraan
- Jurnal
- Buku Besar
- Neraca Saldo
- Laba Rugi
- Auto Jurnal
- My Profile
- Setting General
- Setting Email / Service
- Aktivitas Sistem
- Dokumentasi
- Help

## Catatan Pengembangan

- Aplikasi menggunakan pola routing berbasis parameter `Page` dan `Sub` pada [`index.php`](./index.php).
- Hak akses diperiksa sebelum halaman ditampilkan.
- Beberapa modul mendukung export data, cetak PDF, cetak invoice, dan ekspor Excel.
- Sebagian fitur mengandalkan library pihak ketiga yang sudah disertakan di project.

## Lisensi

Project ini menggunakan lisensi yang tercantum pada file [`LICENSE`](./LICENSE).

## Informasi Versi

- Nama aplikasi: Pharmix
- Versi: `V.1.0.0`

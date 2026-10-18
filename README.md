# Pharmix V.1.0.0

Pharmix adalah aplikasi manajemen apotek dan fasilitas kesehatan berbasis web. Aplikasi ini membantu pengelolaan pengguna, master data, resep, stok, transaksi operasional, transaksi jual-beli, pembayaran, akuntansi, laporan, dan integrasi SATUSEHAT.

Pharmix dibangun menggunakan PHP native, MySQL/MariaDB, Bootstrap 5, dan jQuery. Sebagian proses pada halaman menggunakan AJAX sehingga data dapat dimuat tanpa memuat ulang seluruh halaman.

> [!NOTE]
> Project/repository Pharmix bersifat **gratis dan open source**. Siapa saja dapat menggunakan, memodifikasi, dan mengubah aplikasi ini sesuai keinginan dan kebutuhan dengan mengikuti ketentuan [Lisensi Apache 2.0](./LICENSE).
>
> Untuk menghubungi pembuat, silakan melalui WhatsApp [089601154726](https://wa.me/6289601154726) atau email [dhiforester@gmail.com](mailto:dhiforester@gmail.com).

## Fitur

Daftar berikut merangkum kemampuan yang terlihat pada halaman, form, tabel, dan proses di `_Page`. Susunan bagian utama mengikuti menu aplikasi pada [`_Partial/Menu.php`](./_Partial/Menu.php); ketersediaan halaman dan tindakan tetap mengikuti izin akses pengguna.

### Dashboard

- **Dashboard** — Menampilkan ringkasan jumlah pasien, obat, resep, kunjungan, stok barang, serta transaksi penjualan, pembelian, dan operasional. Pengguna dapat melihat transaksi terbaru, pemberitahuan sistem, dan grafik transaksi menurut periode.

### Master

- **Index Obat/Alkes** — Melihat, mencari, memfilter, menambah, mengubah, melihat detail, dan menghapus data obat/alat kesehatan. Data dapat dibuat manual atau dicari dari KFA; komposisi/ingredient dapat dirinci, dan data dapat diimpor atau diekspor. Modul menyimpan kode KFA serta ID Medication SATUSEHAT dan mendukung pengiriman Medication ke SATUSEHAT.
- **Pasien** — Menambah, melihat detail, mengubah, menghapus, mencari, memfilter, mengimpor, dan mengekspor data pasien. Detail pasien mencakup identitas/nomor rekam medis serta riwayat kunjungan, resep, dan transaksi; ID IHS dapat dicari atau diperiksa untuk integrasi SATUSEHAT.
- **Kunjungan** — Mencatat, melihat detail, mengubah, menghapus, memfilter, dan mengekspor kunjungan pasien. Data kunjungan dapat memuat tanggal, kategori/kondisi, prioritas, poliklinik, tenaga kesehatan, status, dan lampiran; tersedia proses pengiriman Encounter ke SATUSEHAT.
- **Resep** — Membuat, melihat, mengubah, dan menghapus resep beserta item/rincian obat, ingredient, dosis/aturan pakai, dokter, dan apoteker. Resep dapat dicari menggunakan Nomor Resep Nasional (NRN), dicetak sebagai resep atau etiket, dan dikaitkan dengan data kunjungan/pasien. Proses SATUSEHAT mencakup MedicationRequest, MedicationDispense, dan DocumentReference.
- **Supplier** — Menambah, melihat detail, mengubah, menghapus, mencari, memfilter, mengimpor, dan mengekspor data pemasok. Detail supplier menampilkan riwayat transaksi dan rincian barang; riwayat tersebut juga dapat difilter dan diekspor.

### Inventaris

- **Master Barang** — Menambah, melihat detail, mengubah, menghapus, mencari, memfilter, mengimpor, mengekspor, dan mencadangkan data barang. Data barang mencakup stok, satuan tunggal maupun multi-satuan, harga serta kategori multi-harga, tanggal kedaluwarsa/batch, dan riwayat transaksi. Tersedia pencarian barang melalui pemindaian kode.
- **Batch & Expired** — Menambah, melihat detail, mengubah, menghapus, memfilter, mengimpor, dan mengekspor data batch serta tanggal kedaluwarsa barang untuk pemantauan persediaan.
- **Stock Opname** — Membuat, mencari, melihat, mengubah, dan menghapus sesi stock opname. Pada rincian sesi, pengguna dapat memfilter dan mengekspor daftar barang, mencatat stok fisik, melihat stok awal/akhir serta selisih dan nilainya; pencatatan hasil opname memperbarui stok barang.

### Transaksi

- **Kategori Transaksi** — Menambah, melihat detail, mengubah, menghapus, dan memfilter kategori/jenis transaksi; kategori dapat dikaitkan dengan akun perkiraan untuk pencatatan transaksi.
- **Transaksi Operasional** — Membuat, melihat detail, mengubah, dan menghapus transaksi operasional. Pengguna dapat mengelola rincian dan jurnal transaksi, menambah/mengubah/menghapus rincian maupun jurnal, mengelola pembayaran, memfilter daftar, dan mengekspor transaksi.
- **Kasir / Penjualan** — Membuat transaksi penjualan dengan memilih atau memindai barang, mengatur rincian barang dan rincian lainnya, jumlah, harga/kategori harga, diskon, PPN, serta anggota/pasien bila diperlukan. Rincian dapat ditambah, diubah, dihapus atau diedit secara massal; transaksi dapat disimpan, diubah atau dibatalkan, disertai pengelolaan pembayaran dan jurnal. Tersedia cetak invoice, filter riwayat, ekspor transaksi/rincian, serta ekspor estimasi laba penjualan.
- **Pembelian** — Membuat transaksi pembelian dengan memilih supplier, memindai barang, dan mengatur rincian barang maupun rincian lainnya, harga, diskon, dan PPN. Rincian dapat ditambah, diubah, dihapus atau diedit secara massal; transaksi dapat disimpan, diubah atau dibatalkan, disertai pengelolaan pembayaran dan jurnal. Tersedia cetak dokumen transaksi serta ekspor transaksi/rincian.

### Keuangan

- **Akun Perkiraan** — Mengelola akun perkiraan utama dan akun anak, melihat detail, mengubah, menghapus, serta memilih akun untuk pengelompokan transaksi dan pencatatan jurnal.
- **Utang/Piutang** — Melihat tagihan dan kewajiban yang belum lunas untuk transaksi operasional maupun jual-beli pada tampilan terpisah. Daftar dapat difilter; pengguna dapat melihat rincian transaksi dan riwayat pembayaran, mengatur tempo/jatuh tempo, mencatat, mengubah atau menghapus pembayaran, serta mengekspor riwayat pembayaran.
- **Pembayaran** — Menambah, melihat detail, mengubah, dan menghapus pembayaran serta memfilter dan mengekspor daftar pembayaran. Pembayaran dapat ditelusuri ke transaksi terkait; jurnal pembayaran juga dapat ditambah, diubah, dan dihapus.

### Laporan

- **Jurnal** — Menelusuri jurnal debit/kredit berdasarkan filter, membuka detail jurnal/transaksi, dan mengekspor data jurnal.
- **Buku Besar** — Memilih akun perkiraan dan periode untuk melihat mutasi, rincian transaksi, dan saldo akun; tersedia ekspor/cetak laporan.
- **Neraca Saldo** — Melihat saldo debit dan kredit akun untuk periode yang dipilih serta mengekspor atau mencetak laporan.
- **Laba Rugi** — Melihat laporan pendapatan dan beban menurut periode serta mengekspor laporan.
- **Rekap Operasional** — Melihat grafik dan rekap transaksi operasional berdasarkan jenis/periode, membuka rincian transaksi, dan mengekspor transaksi maupun rinciannya.
- **Rekap Jual/Beli** — Melihat grafik ringkasan penjualan dan pembelian serta mencetak atau mengunduh hasil rekap dalam format PDF/Excel.
- **Rekapitulasi Transaksi** — Menampilkan grafik transaksi serta rekap simpanan dan pinjaman anggota; tersedia laporan cetak/rekap beserta rincian transaksi.

### Pengaturan

- **Pengaturan Umum** — Mengubah informasi/identitas umum aplikasi dan memperbarui logo serta favicon.
- **Auto Jurnal** — Mengatur dan mengubah pemetaan akun jurnal otomatis untuk jenis transaksi jual-beli; pengaturan dapat digunakan atau diatur ulang pada alur transaksi.
- **Email Gateway** — Mengatur konfigurasi layanan email dan mengirim email uji untuk memeriksa pengaturannya.
- **SATUSEHAT** — Menambah, melihat detail, mengubah, dan menghapus konfigurasi koneksi; mengatur base URL, Organization ID, client key, secret key, serta status koneksi dan menguji koneksi.

### Aksesibilitas

- **Fitur Aplikasi** — Menambah, melihat detail, mengubah, menghapus, dan memfilter daftar fitur yang menjadi dasar izin.
- **Entitas Akses** — Membuat, melihat detail, mengubah, dan menghapus kelompok/entitas akses; mengatur fitur yang diwariskan oleh entitas dan mengelola API key entitas.
- **Akses Pengguna** — Menambah, melihat detail, mengubah, menghapus, memfilter, dan mengelola akun pengguna. Admin dapat mengubah level akses, mengatur izin per fitur, mengembalikan izin ke standar entitas, mengganti foto/password, serta melihat log akses.

### Referensi

- **Route, Sediaan, Satuan Dosis, Denominator, dan Numerator** — Mengelola data referensi obat melalui tambah, ubah, hapus, pencarian/filter pada modul yang menyediakannya, serta impor dan ekspor. Referensi ini digunakan untuk melengkapi aturan pakai, bentuk sediaan, dosis, dan komposisi/kekuatan Medication.
- **Poliklinik** — Menambah, melihat detail, mengubah, menghapus, dan memfilter data poliklinik; mencari/menghubungkan ID Location SATUSEHAT.
- **Nakes** — Menambah, melihat detail, mengubah, menghapus, dan memfilter data tenaga kesehatan; mencari/menghubungkan data Practitioner dan mengatur akses nakes.

### Sistem dan Fitur Lainnya

- **Log Aktivitas** — Menelusuri log aktivitas aplikasi, email, dan API dalam bentuk dataset/tabel, rekapitulasi, atau grafik; memilih mode/periode/filter serta mengekspor data aktivitas dan rincian rekap.
- **Dokumentasi** — Menambah dan mengelola dokumentasi beserta kontennya, mengubah atau menghapus dokumentasi/konten, mengatur urutan konten, dan mencari/filter berdasarkan tag.
- **Bantuan** — Menelusuri panduan bantuan, mencari berdasarkan judul/deskripsi, dan menyaring berdasarkan topik/tag.
- **Login, pemulihan akun, dan profil** — Login dan logout melalui sesi aplikasi; meminta pemulihan password dan mengatur ulang password melalui alur reset. Pada profil, pengguna dapat mengubah identitas, foto, dan password.

### Komponen Pendukung di `_Page`

Komponen berikut berada di `_Page`, tetapi bukan semuanya merupakan menu utama:

- **Anggota Koperasi** — Menampilkan dashboard anggota; menambah, melihat detail, mengubah, menghapus, mengimpor, dan mengekspor data anggota. Pengelola dapat menghubungkan akun akses anggota, mengatur status/izin akses dan foto, serta melihat riwayat simpanan, pinjaman/angsuran, pembelian, penarikan, dan bagi hasil. Riwayat dapat dicari atau difilter; rincian, transaksi, simpanan, dan riwayat simpanan dapat diekspor atau direkap.
- **Riwayat Anggota** — Menelusuri riwayat simpanan, penarikan, pinjaman, angsuran, dan pembelian anggota, termasuk pencarian pada riwayat yang tersedia.
- **Dokumentasi API** — Mengelola entri dokumentasi API, membuka editor, dan melihat dokumentasi melalui viewer.
- **Cetak Invoice** — Menyediakan halaman cetak invoice berdasarkan data transaksi.
- **Halaman login/reset password** — Komponen untuk autentikasi, permintaan pemulihan, dan pengaturan ulang password.
- **Komponen internal** — `Beranda`, `Condition`, `Error`, dan komponen lainnya menyediakan halaman beranda, kondisi/validasi alur (termasuk SATUSEHAT), serta tampilan akses ditolak, halaman tidak ditemukan, atau fitur dalam pengembangan; komponen ini bukan fitur transaksi mandiri.

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

### 7. Buat akun pertama

Pada instalasi baru, jalankan [`generate_credential.php`](./generate_credential.php) melalui terminal dari direktori project untuk membuat akun login pertama:

```bash
php generate_credential.php
```

Pastikan database sudah diimpor, konfigurasi `_Config/Connection.php` sudah benar, ekstensi PHP `mysqli` tersedia, dan tabel `akses_entitas` memiliki minimal satu level akses. Tabel `akses` harus masih kosong; generator menolak berjalan jika akun sudah tersedia.

Ikuti petunjuk terminal untuk memilih level akses serta memasukkan nama, nomor kontak, email, dan password. Setelah selesai, gunakan email dan password tersebut untuk login. Script hanya dapat dijalankan melalui PHP CLI, bukan melalui browser.

Panduan lengkap tersedia pada [`generate_credential.md`](./generate_credential.md).

### 8. Jalankan aplikasi

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

### 9. Konfigurasi opsional

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

- Tabel Google Credential membaca semua baris `google_credential` tanpa filter atau pagination dan menampilkan opsi detail, uji koneksi, edit, serta hapus sesuai halaman pengelola.
- Form tambah Google Credential menyimpan nama, Client ID, Client Secret, dan status ke tabel `google_credential` melalui endpoint AJAX; pilihan status divalidasi dan field URL Satu Sehat yang tidak tersedia di skema tidak ditampilkan.
- Tampilan `Login.php` menggunakan layout dua panel responsif dengan gambar `assets/img/Left-Background.png`; pada layar mobile gambar memenuhi lebar di bagian atas dan diputar 90 derajat, dengan logo, nama aplikasi, versi, dan petunjuk login berwarna putih di atasnya. Form memiliki jarak yang lega dan ikon tanpa latar khusus yang tidak memakai style `input-group-text` global; autentikasi email, password, captcha, toggle password, dan endpoint AJAX tetap mengikuti alur yang sudah ada. Tombol Google hanya elemen antarmuka dan belum menjalankan autentikasi.
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

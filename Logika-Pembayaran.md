# Pembayaran Berdasarkan Jenis Transaksi
Karena transaksi pembayaran hanya berlaku untuk transaksi yang memiliki status Utang/Piutang maka 
pencatatan jurnal pembayaran adalah kebalikan dari posisi akun utang/piutang

1. Kategori Transaksi = Pemasukan
   - Mencatat nominal pembayaran pada lajur DEBET dengan id_akun_perkiraan sesuai id_akun_debet
   - Mencatat nominal pembayaran pada lajur KREDIT dengan id_akun_perkiraan sesuai id_utang_piutang

2. Kategori Transaksi = Pengeluaran
   - Mencatat nominal pembayaran pada lajur DEBET dengan id_akun_perkiraan sesuai id_utang_piutang
   - Mencatat nominal pembayaran pada lajur KREDIT dengan id_akun_perkiraan sesuai id_akun_debet

3. Kategori Transaksi = Pembelian
   - Mencatat nominal pembayaran pada lajur DEBET dengan id_akun_perkiraan sesuai utang_piutang pada setting_autojurnal_jual_beli
   - Mencatat nominal pembayaran pada lajur KREDIT dengan id_akun_perkiraan sesuai debet pada tabel setting_autojurnal_jual_beli

   Untuk Retur Pembelian adalah sebaliknya

4. Kategori Transaksi = Penjualan
   - Mencatat nominal pembayaran pada lajur DEBET dengan id_akun_perkiraan sesuai debet pada tabel setting_autojurnal_jual_beli
   - Mencatat nominal pembayaran pada lajur KREDIT dengan id_akun_perkiraan sesuai utang_piutang pada tabel setting_autojurnal_jual_beli

   Untuk Retur Penjualan adalah sebaliknya

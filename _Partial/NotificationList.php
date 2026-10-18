<?php
// Muat dependensi untuk daftar notifikasi yang dipanggil melalui AJAX.
require_once __DIR__ . '/../_Config/Connection.php';
require_once __DIR__ . '/../_Config/GlobalFunction.php';
require_once __DIR__ . '/../_Config/Session.php';

$jumlahNotifikasi = 0;

// Satu notifikasi dibuat jika salah satu konfigurasi auto jurnal belum lengkap.
$sql = "
    SELECT EXISTS (
        SELECT 1
        FROM (
            SELECT 'Penjualan' AS kategori
            UNION ALL SELECT 'Pembelian'
            UNION ALL SELECT 'Retur Penjualan'
            UNION ALL SELECT 'Retur Pembelian'
        ) AS kategori_wajib
        LEFT JOIN setting_autojurnal_jual_beli AS saj
            ON saj.kategori = kategori_wajib.kategori
        LEFT JOIN akun_perkiraan AS akun_debet
            ON akun_debet.id_perkiraan = saj.debet
        LEFT JOIN akun_perkiraan AS akun_kredit
            ON akun_kredit.id_perkiraan = saj.kredit
        LEFT JOIN akun_perkiraan AS akun_utang_piutang
            ON akun_utang_piutang.id_perkiraan = saj.utang_piutang
        WHERE saj.id_autojurnal_jual_beli IS NULL
           OR saj.debet IS NULL
           OR saj.kredit IS NULL
           OR saj.utang_piutang IS NULL
           OR akun_debet.id_perkiraan IS NULL
           OR akun_kredit.id_perkiraan IS NULL
           OR akun_utang_piutang.id_perkiraan IS NULL
    ) AS notifikasi
";

$stmt = $Conn->prepare($sql);
if ($stmt && $stmt->execute()) {
    $data = $stmt->get_result()->fetch_assoc();
    $jumlahNotifikasi = (int) ($data['notifikasi'] ?? 0);
}
if ($stmt) {
    $stmt->close();
}

// Tampilkan satu pemberitahuan sesuai jumlah yang digunakan pada bel notifikasi.
if ($jumlahNotifikasi === 0) {
    echo '<li class="dropdown-header">Tidak Ada Pemberitahuan Baru</li>';
} else {
    echo '<li class="dropdown-header">Ada ' . $jumlahNotifikasi . ' Pemberitahuan</li>';
    echo '<li><hr class="dropdown-divider"></li>';
    // Seluruh item dapat diklik untuk membuka konfigurasi Auto Jurnal.
    echo '<li>';
    echo '  <a href="index.php?Page=AutoJurnal" class="notification-item text-decoration-none">';
    echo '      <i class="bi bi-exclamation-circle text-danger"></i>';
    echo '      <div>';
    echo '          <h4>Konfigurasi Auto Jurnal Belum Lengkap</h4>';
    echo '          <p>Lengkapi akun debet, kredit, dan utang/piutang untuk Penjualan, Pembelian, Retur Penjualan, dan Retur Pembelian.</p>';
    echo '      </div>';
    echo '  </a>';
    echo '</li>';
}
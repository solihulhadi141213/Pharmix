<?php
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";

    // Helper untuk output pesan error JSON/HTML
    function showError($message) {
        echo '<div class="row"><div class="col-md-12 mb-3 text-center"><small class="text-danger">' . $message . '</small></div></div>';
        exit;
    }

    // Validasi Session & Input ID
    if (empty($SessionIdAkses)) {
        showError('Sesi Akses Sudah Berakhir, Silahkan Login Ulang');
    }
    if (empty($_POST['id_transaksi_jenis'])) {
        showError('ID Jenis Transaksi Tidak Boleh Kosong!');
    }

    $id_transaksi_jenis = (int)validateAndSanitizeInput($_POST['id_transaksi_jenis']);
    if ($id_transaksi_jenis <= 0) {
        showError('ID Jenis Transaksi Tidak Valid!');
    }

    // Query Detail (Satu Query)
    $sql = "SELECT tj.id_transaksi_jenis, tj.nama, tj.kategori, tj.deskripsi, tj.id_akun_debet, tj.id_akun_kredit,
                ad.kode AS kode_akun_debet, ad.nama AS nama_akun_debet,
                ak.kode AS kode_akun_kredit, ak.nama AS nama_akun_kredit,
                COUNT(t.id_transaksi) AS jumlah_transaksi, 
                COALESCE(SUM(t.jumlah), 0) AS total_transaksi
            FROM transaksi_jenis AS tj
            LEFT JOIN akun_perkiraan AS ad ON ad.id_perkiraan = tj.id_akun_debet
            LEFT JOIN akun_perkiraan AS ak ON ak.id_perkiraan = tj.id_akun_kredit
            LEFT JOIN transaksi AS t ON t.id_transaksi_jenis = tj.id_transaksi_jenis
            WHERE tj.id_transaksi_jenis = ?
            GROUP BY tj.id_transaksi_jenis, tj.nama, tj.kategori, tj.deskripsi, tj.id_akun_debet, tj.id_akun_kredit, ad.kode, ad.nama, ak.kode, ak.nama";

    $stmt = $Conn->prepare($sql);
    if (!$stmt) {
        showError('<b>Opsss!</b> Terjadi kesalahan pada saat mempersiapkan query.<br>' . htmlspecialchars($Conn->error, ENT_QUOTES, 'UTF-8'));
    }

    $stmt->bind_param("i", $id_transaksi_jenis);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        showError('<b>Opsss!</b> Terjadi kesalahan pada saat mengambil data.<br>' . htmlspecialchars($err, ENT_QUOTES, 'UTF-8'));
    }

    $Data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$Data) {
        showError('Data jenis transaksi tidak ditemukan.');
    }

    // Format Data & Escaping
    $nama = $Data['nama'] ?? '';
    $kategori = $Data['kategori'] ?? '';
    $deskripsi = $Data['deskripsi'] ?? '';

    $debet_kode = $Data['kode_akun_debet'] ?? '';
    $debet_nama = !empty($Data['nama_akun_debet']) ? $Data['nama_akun_debet'] : '-';
    $text_debet = (!empty($debet_kode) ? $debet_kode . ' - ' : '') . $debet_nama;

    $kredit_kode = $Data['kode_akun_kredit'] ?? '';
    $kredit_nama = !empty($Data['nama_akun_kredit']) ? $Data['nama_akun_kredit'] : '-';
    $text_kredit = (!empty($kredit_kode) ? $kredit_kode . ' - ' : '') . $kredit_nama;

    $jml_transaksi = number_format((int)($Data['jumlah_transaksi'] ?? 0), 0, ',', '.');
    $total_transaksi = "Rp " . number_format((float)($Data['total_transaksi'] ?? 0), 0, ',', '.');

    // Routing Kategori Transaksi
    if($kategori=="Pengeluaran"){
        $label_kategori = '<span class="badge badge-danger">'.$kategori.'</span>';
    }else{
        $label_kategori = '<span class="badge badge-success">'.$kategori.'</span>';
    }
?>

<div class="col-md-12 mb-4">
    <div class="row mb-3">
        <div class="col-md-5"><small>Nama Transaksi</small></div>
        <div class="col-md-7"><small class="text text-grayish"><?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?></small></div>
    </div>
    <div class="row mb-3">
        <div class="col-md-5"><small>Kategori Transaksi</small></div>
        <div class="col-md-7">
            <?php
                echo $label_kategori;
            ?>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-5"><small>Deskripsi/Keterangan</small></div>
        <div class="col-md-7"><small class="text-grayish"><?= htmlspecialchars($deskripsi, ENT_QUOTES, 'UTF-8'); ?></small></div>
    </div>
    <div class="row mb-3">
        <div class="col-md-5"><small>Akun Debet</small></div>
        <div class="col-md-7"><small class="text-grayish"><?= htmlspecialchars($text_debet, ENT_QUOTES, 'UTF-8'); ?></small></div>
    </div>
    <div class="row mb-3">
        <div class="col-md-5"><small>Akun Kredit</small></div>
        <div class="col-md-7"><small class="text-grayish"><?= htmlspecialchars($text_kredit, ENT_QUOTES, 'UTF-8'); ?></small></div>
    </div>
    <div class="row mb-3">
        <div class="col-md-5"><small>Jumlah Record</small></div>
        <div class="col-md-7"><small class="text-grayish"><?= $jml_transaksi; ?> Record</small></div>
    </div>
    <div class="row mb-3">
        <div class="col-md-5"><small>Total / Volume (Rp)</small></div>
        <div class="col-md-7"><small class="text-grayish"><?= $total_transaksi; ?></small></div>
    </div>
</div>
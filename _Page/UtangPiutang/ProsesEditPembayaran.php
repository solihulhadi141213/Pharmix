<?php
    // ==========================================
    // KONEKSI, SESSION DAN KONFIGURASI
    // ==========================================
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');
    header('Content-Type: application/json; charset=utf-8');

    // ==========================================
    // DEFAULT RESPONSE
    // ==========================================
    $response = [
        "status"  => "Error",
        "message" => "Terjadi kesalahan yang tidak diketahui."
    ];

    // ==========================================
    // VALIDASI SESI AKSES
    // ==========================================
    if (empty($SessionIdAkses)) {
        $response["message"] = "Sesi akses sudah berakhir! Silahkan Login Ulang!";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ==========================================
    // VALIDASI METHOD REQUEST
    // ==========================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response["message"] = "Metode request tidak valid.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ==========================================
    // VALIDASI MANDATORY INPUT
    // ==========================================
    $id_transaksi_pembayaran = $_POST['id_transaksi_pembayaran'] ?? '';
    $id                      = $_POST['id'] ?? '';
    $kategori                = $_POST['kategori'] ?? '';
    $tanggal_input           = $_POST['tanggal'] ?? '';
    $jam_input               = $_POST['jam'] ?? '';
    $jumlah_input            = $_POST['jumlah'] ?? '';

    $id_transaksi_pembayaran = trim($id_transaksi_pembayaran);
    $id                      = trim($id);
    $kategori                = trim($kategori);
    $tanggal_input           = trim($tanggal_input);
    $jam_input               = trim($jam_input);
    $jumlah_input            = trim($jumlah_input);

    if (empty($id_transaksi_pembayaran) || !ctype_digit($id_transaksi_pembayaran)) {
        $response["message"] = "ID Pembayaran tidak valid.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($id)) {
        $response["message"] = "ID Transaksi tidak boleh kosong.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($kategori)) {
        $response["message"] = "Kategori transaksi tidak boleh kosong.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($tanggal_input) || empty($jam_input)) {
        $response["message"] = "Tanggal dan Jam pembayaran wajib diisi.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($jumlah_input)) {
        $response["message"] = "Jumlah nominal pembayaran tidak boleh kosong.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Bersihkan format rupiah (hilangkan titik)
    $jumlah_bersih = str_replace('.', '', $jumlah_input);
    if (!is_numeric($jumlah_bersih)) {
        $response["message"] = "Format jumlah nominal tidak valid.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $jumlah_nominal = (float) $jumlah_bersih;
    if ($jumlah_nominal <= 0) {
        $response["message"] = "Jumlah nominal pembayaran harus lebih besar dari 0.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Gabungkan tanggal dan jam menjadi format datetime lengkap
    $tanggal_waktu_gabung = $tanggal_input . ' ' . $jam_input . ':00';

    // ==========================================
    // MULAI TRANSAKSI DATABASE (ACID TRANSACTION)
    // ==========================================
    mysqli_begin_transaction($Conn);

    try {
        // A. Ambil data pembayaran lama untuk mengetahui relasi induk transaksi & kategori jenisnya
        $sql_cek_byr = "SELECT id_transaksi, id_transaksi_jual_beli, kategori_transaksi FROM transaksi_pembayaran WHERE id_transaksi_pembayaran = ? LIMIT 1";
        $stmt_cek_byr = mysqli_prepare($Conn, $sql_cek_byr);
        if (!$stmt_cek_byr) {
            throw new Exception("Gagal mempersiapkan query cek pembayaran.");
        }
        mysqli_stmt_bind_param($stmt_cek_byr, "i", $id_transaksi_pembayaran);
        mysqli_stmt_execute($stmt_cek_byr);
        $res_cek_byr = mysqli_stmt_get_result($stmt_cek_byr);
        $data_byr_lama = mysqli_fetch_assoc($res_cek_byr);
        mysqli_stmt_close($stmt_cek_byr);

        if (!$data_byr_lama) {
            throw new Exception("Data riwayat pembayaran tidak ditemukan.");
        }

        // B. Validasi batas maksimal berdasarkan kategori induk
        if ($kategori === "jual_beli") {
            $id_induk = $data_byr_lama['id_transaksi_jual_beli'] ?? $id;
            
            // Ambil data total tagihan dan cash awal dari transaksi_jual_beli
            $sql_induk = "SELECT total AS jumlah_tagihan, cash AS pembayaran_cash, kategori FROM transaksi_jual_beli WHERE id_transaksi_jual_beli = ? LIMIT 1";
            $stmt_induk = mysqli_prepare($Conn, $sql_induk);
            if (!$stmt_induk) {
                throw new Exception("Gagal menyiapkan query induk jual beli.");
            }
            mysqli_stmt_bind_param($stmt_induk, "s", $id_induk);
            mysqli_stmt_execute($stmt_induk);
            $res_induk = mysqli_stmt_get_result($stmt_induk);
            $data_induk = mysqli_fetch_assoc($res_induk);
            mysqli_stmt_close($stmt_induk);

            if (!$data_induk) {
                throw new Exception("Data transaksi jual/beli induk tidak ditemukan.");
            }

            $jml_tagihan     = (float) $data_induk['jumlah_tagihan'];
            $pembayaran_cash = (float) $data_induk['pembayaran_cash'];
            $kat_transaksi   = $data_induk['kategori']; // e.g. Penjualan / Pembelian / Retur

            // Hitung akumulasi pembayaran lain (kecuali pembayaran yang sedang diedit ini)
            $sql_akum = "SELECT SUM(jumlah) AS total_lain FROM transaksi_pembayaran WHERE id_transaksi_jual_beli = ? AND id_transaksi_pembayaran != ?";
            $stmt_akum = mysqli_prepare($Conn, $sql_akum);
            mysqli_stmt_bind_param($stmt_akum, "si", $id_induk, $id_transaksi_pembayaran);
            mysqli_stmt_execute($stmt_akum);
            $res_akum = mysqli_stmt_get_result($stmt_akum);
            $data_akum = mysqli_fetch_assoc($res_akum);
            mysqli_stmt_close($stmt_akum);

            $total_pembayaran_lain = (float) ($data_akum['total_lain'] ?? 0);

            // Batas maksimal yang diperbolehkan = Total Tagihan - (Cash + Pembayaran Lain)
            $sisa_maksimal = $jml_tagihan - $pembayaran_cash - $total_pembayaran_lain;
            if ($jumlah_nominal > $sisa_maksimal) {
                throw new Exception("Nominal pembayaran melebihi sisa tagihan! Maksimal pembayaran yang diizinkan adalah Rp " . number_format($sisa_maksimal, 0, ',', '.'));
            }

            // Hitung total keseluruhan setelah update
            $total_terbayar_baru = $pembayaran_cash + $total_pembayaran_lain + $jumlah_nominal;
            
            // Tentukan status baru
            if ($total_terbayar_baru >= $jml_tagihan) {
                $status_baru = "Lunas";
            } else {
                if ($kat_transaksi === "Penjualan" || $kat_transaksi === "Retur Pembelian") {
                    $status_baru = "Piutang";
                } else {
                    $status_baru = "Utang";
                }
            }

            // 1. UPDATE TABEL transaksi_pembayaran
            $sql_up_byr = "UPDATE transaksi_pembayaran SET tanggal = ?, jumlah = ? WHERE id_transaksi_pembayaran = ?";
            $stmt_up_byr = mysqli_prepare($Conn, $sql_up_byr);
            mysqli_stmt_bind_param($stmt_up_byr, "sdi", $tanggal_waktu_gabung, $jumlah_nominal, $id_transaksi_pembayaran);
            if (!mysqli_stmt_execute($stmt_up_byr)) {
                throw new Exception("Gagal memperbarui data pembayaran jual beli.");
            }
            mysqli_stmt_close($stmt_up_byr);

            // 2. UPDATE STATUS TABEL transaksi_jual_beli
            $sql_up_induk = "UPDATE transaksi_jual_beli SET status = ? WHERE id_transaksi_jual_beli = ?";
            $stmt_up_induk = mysqli_prepare($Conn, $sql_up_induk);
            mysqli_stmt_bind_param($stmt_up_induk, "ss", $status_baru, $id_induk);
            if (!mysqli_stmt_execute($stmt_up_induk)) {
                throw new Exception("Gagal memperbarui status transaksi jual beli.");
            }
            mysqli_stmt_close($stmt_up_induk);

        } else {
            // Kategori Operasional
            $id_induk = $data_byr_lama['id_transaksi'] ?? $id;

            // Ambil data total tagihan dan pembayaran cash dari tabel transaksi
            $sql_induk = "SELECT t.jumlah AS jumlah_tagihan, t.pembayaran AS pembayaran_cash, tj.kategori FROM transaksi t INNER JOIN transaksi_jenis tj ON tj.id_transaksi_jenis = t.id_transaksi_jenis WHERE t.id_transaksi = ? LIMIT 1";
            $stmt_induk = mysqli_prepare($Conn, $sql_induk);
            if (!$stmt_induk) {
                throw new Exception("Gagal menyiapkan query induk operasional.");
            }
            mysqli_stmt_bind_param($stmt_induk, "i", $id_induk);
            mysqli_stmt_execute($stmt_induk);
            $res_induk = mysqli_stmt_get_result($stmt_induk);
            $data_induk = mysqli_fetch_assoc($res_induk);
            mysqli_stmt_close($stmt_induk);

            if (!$data_induk) {
                throw new Exception("Data transaksi operasional induk tidak ditemukan.");
            }

            $jml_tagihan     = (float) $data_induk['jumlah_tagihan'];
            $pembayaran_cash = (float) $data_induk['pembayaran_cash'];
            $kat_transaksi   = $data_induk['kategori']; // Pemasukan / Pengeluaran

            // Hitung akumulasi pembayaran lain
            $sql_akum = "SELECT SUM(jumlah) AS total_lain FROM transaksi_pembayaran WHERE id_transaksi = ? AND id_transaksi_pembayaran != ?";
            $stmt_akum = mysqli_prepare($Conn, $sql_akum);
            mysqli_stmt_bind_param($stmt_akum, "ii", $id_induk, $id_transaksi_pembayaran);
            mysqli_stmt_execute($stmt_akum);
            $res_akum = mysqli_stmt_get_result($stmt_akum);
            $data_akum = mysqli_fetch_assoc($res_akum);
            mysqli_stmt_close($stmt_akum);

            $total_pembayaran_lain = (float) ($data_akum['total_lain'] ?? 0);

            $sisa_maksimal = $jml_tagihan - $pembayaran_cash - $total_pembayaran_lain;
            if ($jumlah_nominal > $sisa_maksimal) {
                throw new Exception("Nominal pembayaran melebihi sisa tagihan! Maksimal pembayaran yang diizinkan adalah Rp " . number_format($sisa_maksimal, 0, ',', '.'));
            }

            $total_terbayar_baru = $pembayaran_cash + $total_pembayaran_lain + $jumlah_nominal;

            if ($total_terbayar_baru >= $jml_tagihan) {
                $status_baru = "Lunas";
            } else {
                if ($kat_transaksi === "Pemasukan") {
                    $status_baru = "Piutang";
                } else {
                    $status_baru = "Utang";
                }
            }

            // 1. UPDATE TABEL transaksi_pembayaran
            $sql_up_byr = "UPDATE transaksi_pembayaran SET tanggal = ?, jumlah = ? WHERE id_transaksi_pembayaran = ?";
            $stmt_up_byr = mysqli_prepare($Conn, $sql_up_byr);
            mysqli_stmt_bind_param($stmt_up_byr, "sdi", $tanggal_waktu_gabung, $jumlah_nominal, $id_transaksi_pembayaran);
            if (!mysqli_stmt_execute($stmt_up_byr)) {
                throw new Exception("Gagal memperbarui data pembayaran operasional.");
            }
            mysqli_stmt_close($stmt_up_byr);

            // 2. UPDATE STATUS TABEL transaksi
            $sql_up_induk = "UPDATE transaksi SET status = ? WHERE id_transaksi = ?";
            $stmt_up_induk = mysqli_prepare($Conn, $sql_up_induk);
            mysqli_stmt_bind_param($stmt_up_induk, "si", $status_baru, $id_induk);
            if (!mysqli_stmt_execute($stmt_up_induk)) {
                throw new Exception("Gagal memperbarui status transaksi operasional.");
            }
            mysqli_stmt_close($stmt_up_induk);
        }

        // C. UPDATE TABEL jurnal (berdasarkan relasi id_transaksi_pembayaran)
        $sql_update_jurnal = "UPDATE jurnal SET nilai = ? WHERE id_transaksi_pembayaran = ?";
        $stmt_jurnal = mysqli_prepare($Conn, $sql_update_jurnal);
        if (!$stmt_jurnal) {
            throw new Exception("Gagal mempersiapkan query update jurnal.");
        }
        mysqli_stmt_bind_param($stmt_jurnal, "di", $jumlah_nominal, $id_transaksi_pembayaran);
        if (!mysqli_stmt_execute($stmt_jurnal)) {
            throw new Exception("Gagal memperbarui data jurnal.");
        }
        mysqli_stmt_close($stmt_jurnal);

        // Commit seluruh perubahan transaksi
        mysqli_commit($Conn);

        $response["status"]   = "Success";
        $response["message"]  = "Data pembayaran berhasil diperbaharui.";
        $response["id"]       = $id;
        $response["kategori"] = $kategori;

    } catch (Exception $e) {
        // Rollback jika terjadi kendala agar data database tetap konsisten dan balance
        mysqli_rollback($Conn);
        $response["message"] = $e->getMessage();
    }

    // ==========================================
    // KEMBALIKAN RESPONSE JSON
    // ==========================================
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
?>
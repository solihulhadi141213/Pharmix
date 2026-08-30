<?php
    // Koneksi, Sesi dan Helper
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";

    // Default Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Default JSON Response
    header('Content-Type: application/json; charset=utf-8');

    // Tanggal sekarang
    $now = date('Y-m-d H:i:s');

    $response = [
        "status"  => "Error",
        "message" => "Terjadi kesalahan yang tidak diketahui."
    ];

    // 1. Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        $response["message"] = "Sesi akses sudah berakhir! Silahkan Login Ulang!";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response["message"] = "Metode request tidak valid.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. Tangkap dan Sanitasi Data Mandatory
    $kategori_transaksi = $_POST['kategori_transaksi'] ?? '';
    $id                 = $_POST['id'] ?? '';
    $tanggal_pembayaran = $_POST['tanggal_pembayaran'] ?? '';
    $jam_pembayaran     = $_POST['jam_pembayaran'] ?? '';
    $jumlah_input       = $_POST['jumlah'] ?? '';

    $kategori_transaksi = trim($kategori_transaksi);
    $id                 = trim($id);
    $tanggal_pembayaran = trim($tanggal_pembayaran);
    $jam_pembayaran     = trim($jam_pembayaran);
    $jumlah_input       = trim($jumlah_input);

    if (empty($kategori_transaksi) || empty($id) || empty($tanggal_pembayaran) || empty($jam_pembayaran) || empty($jumlah_input)) {
        $response["message"] = "Semua kolom data mandatory wajib diisi!";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Bersihkan format uang (ribuan titik)
    $jumlah_bersih = str_replace('.', '', $jumlah_input);
    if (!is_numeric($jumlah_bersih)) {
        $response["message"] = "Format jumlah nominal pembayaran tidak valid.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $jumlah_nominal = (float) $jumlah_bersih;
    if ($jumlah_nominal <= 0) {
        $response["message"] = "Jumlah nominal pembayaran harus lebih besar dari 0.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $tanggal_waktu = $tanggal_pembayaran . ' ' . $jam_pembayaran . ':00';

    // Menentukan 'id_transaksi_pembayaran' berbasis String (UUID/Custom String 36 char)
    // Menggunakan fungsi bawaan/helper Anda untuk menghasilkan string unik
    $randome                   = GenerateKodeBarang(6);
    $id_transaksi_pembayaran   = "PBY-$randome";

    // 3. Mulai Transaksi Database (ACID)
    mysqli_begin_transaction($Conn);

    try {
        $sisa_maksimal    = 0;
        $jml_tagihan      = 0;
        $pembayaran_cash  = 0;
        $total_bayar_lain = 0;
        $status_baru      = "";
        $sub_kategori     = "";

        if ($kategori_transaksi === "jual_beli") {
            // Ambil data tagihan & cash jual/beli
            $sql_induk = "SELECT total AS jumlah_tagihan, cash AS pembayaran_cash, status, kategori FROM transaksi_jual_beli WHERE id_transaksi_jual_beli = ? LIMIT 1";
            $stmt_induk = mysqli_prepare($Conn, $sql_induk);
            mysqli_stmt_bind_param($stmt_induk, "s", $id);
            mysqli_stmt_execute($stmt_induk);
            $data_induk = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_induk));
            mysqli_stmt_close($stmt_induk);

            if (!$data_induk) {
                throw new Exception("Data transaksi Jual/Beli induk tidak ditemukan.");
            }

            $jml_tagihan     = (float) $data_induk['jumlah_tagihan'];
            $pembayaran_cash = (float) $data_induk['pembayaran_cash'];
            $status_sekarang = $data_induk['status'];
            $sub_kategori    = $data_induk['kategori'];

            // Akumulasi riwayat pembayaran sebelumnya
            $sql_akum = "SELECT SUM(jumlah) AS total_lain FROM transaksi_pembayaran WHERE id_transaksi_jual_beli = ?";
            $stmt_akum = mysqli_prepare($Conn, $sql_akum);
            mysqli_stmt_bind_param($stmt_akum, "s", $id);
            mysqli_stmt_execute($stmt_akum);
            $data_akum = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_akum));
            mysqli_stmt_close($stmt_akum);

            $total_bayar_lain = (float) ($data_akum['total_lain'] ?? 0);
            $sisa_maksimal = $jml_tagihan - ($pembayaran_cash + $total_bayar_lain);

            // Validasi agar tidak melebihi sisa tagihan
            if ($jumlah_nominal > $sisa_maksimal) {
                throw new Exception("Nominal pembayaran melebihi sisa tagihan! Maksimal yang dapat dibayar adalah Rp " . number_format($sisa_maksimal, 0, ',', '.'));
            }

            $total_terbayar_baru = $pembayaran_cash + $total_bayar_lain + $jumlah_nominal;
            if ($total_terbayar_baru >= $jml_tagihan) {
                $status_baru = "Lunas";
            } else {
                $status_baru = ($sub_kategori === "Penjualan" || $sub_kategori === "Pembelian" || $sub_kategori === "Retur Penjualan" || $sub_kategori === "Retur Pembelian") ? "Piutang" : "Utang";
            }

            // Insert ke tabel transaksi_pembayaran
            $kategori_pembayaran = "Termin";
            $sql_ins_byr = "INSERT INTO transaksi_pembayaran (
                id_transaksi_pembayaran, 
                id_transaksi_jual_beli, 
                kategori_pembayaran, 
                kategori_transaksi, 
                tanggal, 
                jumlah, 
                creat_at,
                creat_by_id,
                creat_by_name,
                update_at,
                update_by_id,
                update_by_name
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt_ins_byr = mysqli_prepare($Conn, $sql_ins_byr);
            // Perhatikan tipe parameter bind: id_transaksi_pembayaran (s), id_transaksi_jual_beli (s) -> total string "sssssissis"
            mysqli_stmt_bind_param($stmt_ins_byr, "sssssisissis", $id_transaksi_pembayaran, $id, $kategori_pembayaran, $sub_kategori, $tanggal_waktu, $jumlah_nominal, $now, $SessionIdAkses, $SessionNama, $now, $SessionIdAkses, $SessionNama);
            
            if (!mysqli_stmt_execute($stmt_ins_byr)) {
                throw new Exception("Gagal menyimpan data pembayaran jual/beli.");
            }
            mysqli_stmt_close($stmt_ins_byr);

            // Update status tabel induk transaksi_jual_beli
            $sql_up_induk = "UPDATE transaksi_jual_beli SET status = ? WHERE id_transaksi_jual_beli = ?";
            $stmt_up_induk = mysqli_prepare($Conn, $sql_up_induk);
            mysqli_stmt_bind_param($stmt_up_induk, "ss", $status_baru, $id);
            mysqli_stmt_execute($stmt_up_induk);
            mysqli_stmt_close($stmt_up_induk);

            // --- PROSES AUTO JURNAL UNTUK JUAL/BELI ---
            $sql_jurnal_cfg = "SELECT * FROM setting_autojurnal_jual_beli WHERE kategori = ? LIMIT 1";
            $stmt_cfg = mysqli_prepare($Conn, $sql_jurnal_cfg);
            mysqli_stmt_bind_param($stmt_cfg, "s", $sub_kategori);
            mysqli_stmt_execute($stmt_cfg);
            $cfg_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cfg));
            mysqli_stmt_close($stmt_cfg);

            if ($cfg_data) {
                $kode_perkiraan_debit  = $cfg_data['kode_perkiraan_debit_kas'] ?? $cfg_data['kode_perkiraan_debit'] ?? '';
                $kode_perkiraan_kredit = $cfg_data['kode_perkiraan_kredit_piutang'] ?? $cfg_data['kode_perkiraan_kredit'] ?? '';

                if (!empty($kode_perkiraan_debit) && !empty($kode_perkiraan_kredit)) {
                    $sql_jurnal = "INSERT INTO jurnal (id_transaksi_pembayaran, tanggal, kode_perkiraan, d_k, nilai, keterangan) VALUES (?, ?, ?, 'Debit', ?, ?), (?, ?, ?, 'Kredit', ?, ?)";
                    $stmt_jur = mysqli_prepare($Conn, $sql_jurnal);
                    $ket = "Pembayaran Transaksi Jual/Beli ID: " . $id;
                    
                    // Karena id_transaksi_pembayaran bertipe string (char), bind menggunakan parameter string 's'
                    mysqli_stmt_bind_param($stmt_jur, "sssdsissds", 
                        $id_transaksi_pembayaran, $tanggal_waktu, $kode_perkiraan_debit, $jumlah_nominal, $ket,
                        $id_transaksi_pembayaran, $tanggal_waktu, $kode_perkiraan_kredit, $jumlah_nominal, $ket
                    );
                    mysqli_stmt_execute($stmt_jur);
                    mysqli_stmt_close($stmt_jur);
                }
            }

        } elseif ($kategori_transaksi === "Operasional") {
            // Ambil data tagihan & cash operasional
            $sql_induk = "SELECT t.jumlah AS jumlah_tagihan, t.pembayaran AS pembayaran_cash, t.status, tj.kategori AS sub_kategori 
                          FROM transaksi t 
                          INNER JOIN transaksi_jenis tj ON t.id_transaksi_jenis = tj.id_transaksi_jenis 
                          WHERE t.id_transaksi = ? LIMIT 1";
            $stmt_induk = mysqli_prepare($Conn, $sql_induk);
            mysqli_stmt_bind_param($stmt_induk, "i", $id);
            mysqli_stmt_execute($stmt_induk);
            $data_induk = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_induk));
            mysqli_stmt_close($stmt_induk);

            if (!$data_induk) {
                throw new Exception("Data transaksi operasional induk tidak ditemukan.");
            }

            $jml_tagihan     = (float) $data_induk['jumlah_tagihan'];
            $pembayaran_cash = (float) $data_induk['pembayaran_cash'];
            $status_sekarang = $data_induk['status'];
            $sub_kategori    = $data_induk['sub_kategori'];

            $sql_akum = "SELECT SUM(jumlah) AS total_lain FROM transaksi_pembayaran WHERE id_transaksi = ?";
            $stmt_akum = mysqli_prepare($Conn, $sql_akum);
            mysqli_stmt_bind_param($stmt_akum, "i", $id);
            mysqli_stmt_execute($stmt_akum);
            $data_akum = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_akum));
            mysqli_stmt_close($stmt_akum);

            $total_bayar_lain = (float) ($data_akum['total_lain'] ?? 0);
            $sisa_maksimal = $jml_tagihan - ($pembayaran_cash + $total_bayar_lain);

            if ($jumlah_nominal > $sisa_maksimal) {
                throw new Exception("Nominal pembayaran melebihi sisa tagihan! Maksimal yang dapat dibayar adalah Rp " . number_format($sisa_maksimal, 0, ',', '.'));
            }

            $total_terbayar_baru = $pembayaran_cash + $total_bayar_lain + $jumlah_nominal;
            if ($total_terbayar_baru >= $jml_tagihan) {
                $status_baru = "Lunas";
            } else {
                $status_baru = ($sub_kategori === "Pemasukan") ? "Piutang" : "Utang";
            }

            // Insert ke tabel transaksi_pembayaran
            $kategori_pembayaran = "Termin";
            $sql_ins_byr = "INSERT INTO transaksi_pembayaran (
                id_transaksi_pembayaran, 
                id_transaksi, 
                kategori_pembayaran, 
                kategori_transaksi, 
                tanggal, 
                jumlah, 
                creat_at,
                creat_by_id,
                creat_by_name,
                update_at,
                update_by_id,
                update_by_name
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt_ins_byr = mysqli_prepare($Conn, $sql_ins_byr);
            // Perhatikan tipe parameter bind: id_transaksi_pembayaran (s), id_transaksi (i) -> total string "sisssisissis"
            mysqli_stmt_bind_param($stmt_ins_byr, "sisssisissis", $id_transaksi_pembayaran, $id, $kategori_pembayaran, $sub_kategori, $tanggal_waktu, $jumlah_nominal, $now, $SessionIdAkses, $SessionNama, $now, $SessionIdAkses, $SessionNama);
            
            if (!mysqli_stmt_execute($stmt_ins_byr)) {
                throw new Exception("Gagal menyimpan data pembayaran operasional.");
            }
            mysqli_stmt_close($stmt_ins_byr);

            // Update status tabel induk transaksi
            $sql_up_induk = "UPDATE transaksi SET status = ? WHERE id_transaksi = ?";
            $stmt_up_induk = mysqli_prepare($Conn, $sql_up_induk);
            mysqli_stmt_bind_param($stmt_up_induk, "si", $status_baru, $id);
            mysqli_stmt_execute($stmt_up_induk);
            mysqli_stmt_close($stmt_up_induk);

        } else {
            throw new Exception("Kategori transaksi tidak valid.");
        }

        // Commit transaksi database
        mysqli_commit($Conn);

        $response["status"]   = "Success";
        $response["message"]  = "Pembayaran berhasil disimpan dan status transaksi diperbarui.";
        $response["id"]       = $id;
        $response["kategori"] = $kategori_transaksi;

    } catch (Exception $e) {
        mysqli_rollback($Conn);
        $response["message"] = $e->getMessage();
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
?>
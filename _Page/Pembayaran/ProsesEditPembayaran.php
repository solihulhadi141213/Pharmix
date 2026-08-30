<?php
    // Koneksi dan Konfigurasi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Default Response JSON
    header('Content-Type: application/json; charset=utf-8');

    $response = [
        "status"  => "error",
        "message" => "Terjadi kesalahan yang tidak diketahui"
    ];

    // 1. Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        $response = [
            "status"  => "error",
            "message" => "Sesi akses telah berakhir. Silakan login ulang."
        ];
        echo json_encode($response);
        exit;
    }

    // 2. Validasi Data Mandatory (id_transaksi_pembayaran, tanggal, jam, jumlah)
    if (empty($_POST['id_transaksi_pembayaran']) || empty($_POST['tanggal_pembayaran']) || empty($_POST['jam_pembayaran']) || empty($_POST['jumlah'])) {
        $response = [
            "status"  => "error",
            "message" => "ID Pembayaran, Tanggal, Jam, dan Jumlah Nominal tidak boleh kosong!"
        ];
        echo json_encode($response);
        exit;
    }

    // Sanitasi Input
    $id_transaksi_pembayaran = validateAndSanitizeInput($_POST['id_transaksi_pembayaran']);
    $tanggal_pembayaran      = validateAndSanitizeInput($_POST['tanggal_pembayaran']);
    $jam_pembayaran          = validateAndSanitizeInput($_POST['jam_pembayaran']);
    $gabung_tanggal          = $tanggal_pembayaran . ' ' . $jam_pembayaran;

    // Bersihkan format uang menjadi angka murni
    $jumlah_mentah           = validateAndSanitizeInput($_POST['jumlah']);
    $jumlah_baru             = preg_replace('/[^0-9]/', '', $jumlah_mentah);
    $jumlah_baru             = (int)$jumlah_baru;

    if ($jumlah_baru <= 0) {
        $response = [
            "status"  => "error",
            "message" => "Jumlah nominal pembayaran baru tidak valid."
        ];
        echo json_encode($response);
        exit;
    }

    // 3. Buka Data Pembayaran Lama Berdasarkan id_transaksi_pembayaran
    $QryOld = $Conn->prepare("SELECT * FROM transaksi_pembayaran WHERE id_transaksi_pembayaran = ?");
    $QryOld->bind_param("i", $id_transaksi_pembayaran);
    $QryOld->execute();
    $ResultOld = $QryOld->get_result();
    $DataOld = $ResultOld->fetch_assoc();
    $QryOld->close();

    if (!$DataOld) {
        $response = [
            "status"  => "error",
            "message" => "Data pembayaran lama tidak ditemukan di database."
        ];
        echo json_encode($response);
        exit;
    }

    $id_transaksi           = $DataOld['id_transaksi'];
    $id_transaksi_jual_beli = $DataOld['id_transaksi_jual_beli'];
    $kategori_pembayaran    = $DataOld['kategori_pembayaran'];

    // Mulai Database Transaction
    $Conn->begin_transaction();

    try {
        $status_transaksi_baru = null;

        // 4 & 6 & 7. Validasi jika berhubungan dengan tabel 'transaksi' (Operasional)
        if (!empty($id_transaksi)) {
            // Ambil data tagihan transaksi utama
            $QryTrx = $Conn->prepare("SELECT jumlah, pembayaran FROM transaksi WHERE id_transaksi = ?");
            $QryTrx->bind_param("i", $id_transaksi);
            $QryTrx->execute();
            $DataTrx = $QryTrx->get_result()->fetch_assoc();
            $QryTrx->close();

            if ($DataTrx) {
                $tagihan_utama   = (int)$DataTrx['jumlah'];
                $pembayaran_cash = (int)$DataTrx['pembayaran'];

                // Hitung akumulasi pembayaran lain selain ID pembayaran yang sedang diedit
                $QrySum = $Conn->prepare("SELECT SUM(jumlah) as total_bayar_lain FROM transaksi_pembayaran WHERE id_transaksi = ? AND id_transaksi_pembayaran != ?");
                $QrySum->bind_param("ii", $id_transaksi, $id_transaksi_pembayaran);
                $QrySum->execute();
                $SumResult = $QrySum->get_result()->fetch_assoc();
                $QrySum->close();

                $total_bayar_lain = (int)($SumResult['total_bayar_lain'] ?? 0);
                $total_akumulasi  = $pembayaran_cash + $total_bayar_lain + $jumlah_baru;

                if ($total_akumulasi > $tagihan_utama) {
                    throw new Exception("Jumlah pembayaran melampaui total tagihan transaksi operasional! (Maksimal: Rp " . number_format($tagihan_utama - ($pembayaran_cash + $total_bayar_lain), 0, ',', '.') . ")");
                }

                // Tentukan status baru (Lunas jika akumulasi >= tagihan)
                $status_transaksi_baru = ($total_akumulasi >= $tagihan_utama) ? 'Lunas' : 'Utang'; // Sesuaikan enum status di tabel transaksi jika perlu (Lunas/Utang/Piutang)
            }
        }

        // 8 & 9. Validasi jika berhubungan dengan tabel 'transaksi_jual_beli'
        if (!empty($id_transaksi_jual_beli)) {
            // Ambil data total jual beli
            $QryJB = $Conn->prepare("SELECT total, cash FROM transaksi_jual_beli WHERE id_transaksi_jual_beli = ?");
            $QryJB->bind_param("s", $id_transaksi_jual_beli);
            $QryJB->execute();
            $DataJB = $QryJB->get_result()->fetch_assoc();
            $QryJB->close();

            if ($DataJB) {
                $total_jual_beli = (int)$DataJB['total'];
                $cash_jual_beli  = (int)$DataJB['cash'];

                // Hitung akumulasi pembayaran lain selain ID pembayaran yang sedang diedit
                $QrySumJB = $Conn->prepare("SELECT SUM(jumlah) as total_bayar_lain FROM transaksi_pembayaran WHERE id_transaksi_jual_beli = ? AND id_transaksi_pembayaran != ?");
                $QrySumJB->bind_param("si", $id_transaksi_jual_beli, $id_transaksi_pembayaran);
                $QrySumJB->execute();
                $SumJBResult = $QrySumJB->get_result()->fetch_assoc();
                $QrySumJB->close();

                $total_bayar_lain_jb = (int)($SumJBResult['total_bayar_lain'] ?? 0);
                $total_akumulasi_jb  = $cash_jual_beli + $total_bayar_lain_jb + $jumlah_baru;

                if ($total_akumulasi_jb > $total_jual_beli) {
                    throw new Exception("Akumulasi pembayaran melampaui total transaksi jual beli! (Sisa kurang: Rp " . number_format($total_jual_beli - ($cash_jual_beli + $total_bayar_lain_jb), 0, ',', '.') . ")");
                }

                // Tentukan status jual beli baru (Lunas jika akumulasi >= total)
                $status_jual_beli_baru = ($total_akumulasi_jb >= $total_jual_beli) ? 'Lunas' : 'Kredit';
            }
        }

        // 2 (Lanjutan). Simpan Perubahan pada tabel 'transaksi_pembayaran'
        $QryUpdate = $Conn->prepare("UPDATE transaksi_pembayaran SET tanggal = ?, jumlah = ?, update_at = NOW(), update_by_id = ?, update_by_name = ? WHERE id_transaksi_pembayaran = ?");
        $QryUpdate->bind_param("siisi", $gabung_tanggal, $jumlah_baru, $IdAkses, $NamaAkses, $id_transaksi_pembayaran);
        if (!$QryUpdate->execute()) {
            throw new Exception("Gagal memperbarui data transaksi pembayaran: " . $Conn->error);
        }
        $QryUpdate->close();

        // UPDATE STATUS PADA TABEL 'transaksi' (Jika memenuhi tagihan)
        if (!empty($id_transaksi) && isset($status_transaksi_baru)) {
            $QryUpTrx = $Conn->prepare("UPDATE transaksi SET status = ?, update_at = NOW(), update_by_id = ?, update_by_name = ? WHERE id_transaksi = ?");
            $QryUpTrx->bind_param("sisi", $status_transaksi_baru, $IdAkses, $NamaAkses, $id_transaksi);
            if (!$QryUpTrx->execute()) {
                throw new Exception("Gagal memperbarui status pada tabel transaksi: " . $Conn->error);
            }
            $QryUpTrx->close();
        }

        // UPDATE STATUS PADA TABEL 'transaksi_jual_beli' (Jika memenuhi total)
        if (!empty($id_transaksi_jual_beli) && isset($status_jual_beli_baru)) {
            $QryUpJB = $Conn->prepare("UPDATE transaksi_jual_beli SET status = ?, update_at = NOW(), update_by_id = ?, update_by_name = ? WHERE id_transaksi_jual_beli = ?");
            $QryUpJB->bind_param("siss", $status_jual_beli_baru, $IdAkses, $NamaAkses, $id_transaksi_jual_beli);
            if (!$QryUpJB->execute()) {
                throw new Exception("Gagal memperbarui status pada tabel transaksi_jual_beli: " . $Conn->error);
            }
            $QryUpJB->close();
        }

        // 10. Update atribut nilai pada tabel 'jurnal' berdasarkan id_transaksi_pembayaran
        $QryJurnal = $Conn->prepare("UPDATE jurnal SET nilai = ? WHERE id_transaksi_pembayaran = ?");
        $QryJurnal->bind_param("ii", $jumlah_baru, $id_transaksi_pembayaran);
        if (!$QryJurnal->execute()) {
            throw new Exception("Gagal memperbarui nominal pada tabel jurnal: " . $Conn->error);
        }
        $QryJurnal->close();

        // Commit Transaksi Database jika semua Berhasil
        $Conn->commit();

        $response = [
            "status"  => "Success",
            "message" => "Data pembayaran dan status transaksi berhasil diperbarui."
        ];
        echo json_encode($response);
        exit;

    } catch (Exception $e) {
        // Rollback jika terjadi kesalahan
        $Conn->rollback();

        $response = [
            "status"  => "error",
            "message" => $e->getMessage()
        ];
        echo json_encode($response);
        exit;
    }
?>
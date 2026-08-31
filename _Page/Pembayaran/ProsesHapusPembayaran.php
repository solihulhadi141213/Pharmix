<?php
    // Koneksi, Sesi dan Helper
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Default Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Default JSON Response
    header('Content-Type: application/json; charset=utf-8');

    $response = [
        "status"  => "error",
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

    // 2. Validasi id_transaksi_pembayaran tidak boleh kosong
    if (empty($_POST['id_transaksi_pembayaran'])) {
        $response["message"] = "ID Pembayaran tidak boleh kosong.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $id_transaksi_pembayaran = validateAndSanitizeInput($_POST['id_transaksi_pembayaran']);

    // 3. Ambil data transaksi_pembayaran yang akan dihapus
    $QryByr = $Conn->prepare("SELECT * FROM transaksi_pembayaran WHERE id_transaksi_pembayaran = ? LIMIT 1");
    $QryByr->bind_param("s", $id_transaksi_pembayaran);
    $QryByr->execute();
    $ResultByr = $QryByr->get_result();
    $DataByr = $ResultByr->fetch_assoc();
    $QryByr->close();

    if (!$DataByr) {
        $response["message"] = "Data pembayaran tidak ditemukan di database.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $id_transaksi           = $DataByr['id_transaksi'];
    $id_transaksi_jual_beli = $DataByr['id_transaksi_jual_beli'];

    // 4. Mulai Transaksi Database (ACID)
    mysqli_begin_transaction($Conn);

    try {
        // ==========================================
        // KASUS A: id_transaksi TIDAK KOSONG (Operasional)
        // ==========================================
        if (!empty($id_transaksi)) {
            // Hapus data pembayaran (Jurnal otomatis terhapus via FOREIGN KEY ON DELETE CASCADE)
            $DelByr = $Conn->prepare("DELETE FROM transaksi_pembayaran WHERE id_transaksi_pembayaran = ?");
            $DelByr->bind_param("s", $id_transaksi_pembayaran);
            if (!$DelByr->execute()) {
                throw new Exception("Gagal menghapus data pembayaran: " . $DelByr->error);
            }
            $DelByr->close();

            // Ambil data dari tabel transaksi & transaksi_jenis
            $QryTrx = $Conn->prepare("
                SELECT t.jumlah, t.pembayaran, tj.kategori 
                FROM transaksi t 
                LEFT JOIN transaksi_jenis tj ON t.id_transaksi_jenis = tj.id_transaksi_jenis 
                WHERE t.id_transaksi = ? LIMIT 1
            ");
            $QryTrx->bind_param("s", $id_transaksi);
            $QryTrx->execute();
            $ResTrx = $QryTrx->get_result();
            $DataTrx = $ResTrx->fetch_assoc();
            $QryTrx->close();

            if ($DataTrx) {
                $jml_tagihan  = (float) $DataTrx['jumlah'];
                $jml_cash     = (float) $DataTrx['pembayaran'];
                $kategori_jenis = $DataTrx['kategori']; // 'Pengeluaran' atau 'Pemasukan'

                // Akumulasi sisa pembayaran lain dari tabel transaksi_pembayaran setelah penghapusan
                $QryAkum = $Conn->prepare("SELECT SUM(jumlah) AS total_akumulasi FROM transaksi_pembayaran WHERE id_transaksi = ?");
                $QryAkum->bind_param("s", $id_transaksi);
                $QryAkum->execute();
                $ResAkum = $QryAkum->get_result();
                $DataAkum = $ResAkum->fetch_assoc();
                $QryAkum->close();

                $total_akumulasi = (float) ($DataAkum['total_akumulasi'] ?? 0);
                $total_terbayar  = $jml_cash + $total_akumulasi;

                // Tentukan status baru berdasarkan ketentuan
                $status_baru = "Lunas"; // Default jika sudah tercover
                if ($jml_tagihan > $total_terbayar) {
                    if (strtolower($kategori_jenis) === 'pengeluaran') {
                        $status_baru = "Utang";
                    } elseif (strtolower($kategori_jenis) === 'pemasukan') {
                        $status_baru = "Piutang";
                    }
                }

                // Update status pada tabel transaksi
                $UpTrx = $Conn->prepare("UPDATE transaksi SET status = ? WHERE id_transaksi = ?");
                $UpTrx->bind_param("ss", $status_baru, $id_transaksi);
                $UpTrx->execute();
                $UpTrx->close();
            }

        // ==========================================
        // KASUS B: id_transaksi_jual_beli TIDAK KOSONG
        // ==========================================
        } elseif (!empty($id_transaksi_jual_beli)) {
            // Hapus data pembayaran (Jurnal otomatis terhapus via FOREIGN KEY ON DELETE CASCADE)
            $DelByr = $Conn->prepare("DELETE FROM transaksi_pembayaran WHERE id_transaksi_pembayaran = ?");
            $DelByr->bind_param("s", $id_transaksi_pembayaran);
            if (!$DelByr->execute()) {
                throw new Exception("Gagal menghapus data pembayaran jual beli: " . $DelByr->error);
            }
            $DelByr->close();

            // Ambil data dari tabel transaksi_jual_beli
            $QryJB = $Conn->prepare("SELECT total, cash, kategori FROM transaksi_jual_beli WHERE id_transaksi_jual_beli = ? LIMIT 1");
            $QryJB->bind_param("s", $id_transaksi_jual_beli);
            $QryJB->execute();
            $ResJB = $QryJB->get_result();
            $DataJB = $ResJB->fetch_assoc();
            $QryJB->close();

            if ($DataJB) {
                $total_tagihan = (float) $DataJB['total'];
                $cash_awal     = (float) $DataJB['cash'];
                $kategori_jb   = $DataJB['kategori']; // 'Pembelian', 'Retur Penjualan', 'Penjualan', 'Retur Pembelian'

                // Akumulasi sisa pembayaran lain dari tabel transaksi_pembayaran setelah penghapusan
                $QryAkumJB = $Conn->prepare("SELECT SUM(jumlah) AS total_akumulasi FROM transaksi_pembayaran WHERE id_transaksi_jual_beli = ?");
                $QryAkumJB->bind_param("s", $id_transaksi_jual_beli);
                $QryAkumJB->execute();
                $ResAkumJB = $QryAkumJB->get_result();
                $DataAkumJB = $ResAkumJB->fetch_assoc();
                $QryAkumJB->close();

                $total_akumulasi_jb = (float) ($DataAkumJB['total_akumulasi'] ?? 0);
                $total_terbayar_jb  = $cash_awal + $total_akumulasi_jb;

                // Tentukan status baru berdasarkan ketentuan
                $status_baru = "Lunas"; // Default
                if ($total_tagihan > $total_terbayar_jb) {
                    if ($kategori_jb === 'Pembelian' || $kategori_jb === 'Retur Penjualan') {
                        $status_baru = "Utang";
                    } elseif ($kategori_jb === 'Penjualan' || $kategori_jb === 'Retur Pembelian') {
                        $status_baru = "Piutang";
                    } else {
                        $status_baru = "Kredit";
                    }
                }

                // Update status pada tabel transaksi_jual_beli (enum: 'Lunas', 'Kredit')
                // Catatan: Sesuai struktur tabel, status menggunakan 'Kredit' untuk sisa tagihan belum lunas
                $status_db = ($status_baru === "Lunas") ? "Lunas" : "Kredit";

                $UpJB = $Conn->prepare("UPDATE transaksi_jual_beli SET status = ? WHERE id_transaksi_jual_beli = ?");
                $UpJB->bind_param("ss", $status_db, $id_transaksi_jual_beli);
                $UpJB->execute();
                $UpJB->close();
            }
        } else {
            throw new Exception("Referensi ID Transaksi induk tidak valid.");
        }

        // Commit transaksi database secara sukses
        mysqli_commit($Conn);

        $response["status"]  = "success";
        $response["message"] = "Data pembayaran berhasil dihapus.";

    } catch (Exception $e) {
        mysqli_rollback($Conn);
        $response["message"] = $e->getMessage();
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
?>
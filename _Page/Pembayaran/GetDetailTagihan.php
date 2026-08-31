<?php
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    $response = [
        "status" => "Error",
        "message" => "Data tidak ditemukan"
    ];

    if (empty($SessionIdAkses)) {
        $response["message"] = "Sesi akses berakhir.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $id                 = $_POST['id'] ?? '';
    $kategori_transaksi = $_POST['kategori_transaksi'] ?? '';

    $id                 = trim($id);
    $kategori_transaksi = trim($kategori_transaksi);

    if (empty($id) || empty($kategori_transaksi)) {
        $response["message"] = "Parameter tidak lengkap.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($kategori_transaksi === "jual_beli") {
        // Ambil data dari transaksi_jual_beli
        $sql = "SELECT total AS jumlah_tagihan, cash AS pembayaran_cash, tanggal, status, kategori FROM transaksi_jual_beli WHERE id_transaksi_jual_beli = ? LIMIT 1";
        $stmt = mysqli_prepare($Conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if (!$data) {
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $jml_tagihan     = (float) $data['jumlah_tagihan'];
        $pembayaran_cash = (float) $data['pembayaran_cash'];
        $tanggal         = $data['tanggal'];
        $status          = $data['status'];
        $sub_kategori    = $data['kategori'];

        // Akumulasi riwayat pembayaran yang sudah dilakukan
        $sql_byr = "SELECT SUM(jumlah) AS total_bayar FROM transaksi_pembayaran WHERE id_transaksi_jual_beli = ?";
        $stmt_byr = mysqli_prepare($Conn, $sql_byr);
        mysqli_stmt_bind_param($stmt_byr, "s", $id);
        mysqli_stmt_execute($stmt_byr);
        $res_byr = mysqli_stmt_get_result($stmt_byr);
        $data_byr = mysqli_fetch_assoc($res_byr);
        mysqli_stmt_close($stmt_byr);

        $total_terbayar = $pembayaran_cash + (float) ($data_byr['total_bayar'] ?? 0);
        $sisa_tagihan   = $jml_tagihan - $total_terbayar;
        if ($sisa_tagihan < 0) $sisa_tagihan = 0;

        $response = [
            "status"          => "Success",
            "tanggal"         => date('d/m/Y H:i', strtotime($tanggal)),
            "sub_kategori"    => $sub_kategori,
            "jumlah_tagihan"  => 'Rp ' . number_format($jml_tagihan, 0, ',', '.'),
            "total_terbayar"  => 'Rp ' . number_format($total_terbayar, 0, ',', '.'),
            "sisa_tagihan_val"=> $sisa_tagihan,
            "sisa_tagihan"    => 'Rp ' . number_format($sisa_tagihan, 0, ',', '.'),
            "status_transaksi"=> $status
        ];

    } elseif ($kategori_transaksi === "Operasional") {
        // Ambil data dari transaksi & transaksi_jenis
        $sql = "SELECT t.jumlah AS jumlah_tagihan, t.pembayaran AS pembayaran_cash, t.tanggal, t.status, tj.nama AS nama_jenis 
                FROM transaksi t 
                INNER JOIN transaksi_jenis tj ON t.id_transaksi_jenis = tj.id_transaksi_jenis 
                WHERE t.id_transaksi = ? LIMIT 1";
        $stmt = mysqli_prepare($Conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if (!$data) {
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $jml_tagihan     = (float) $data['jumlah_tagihan'];
        $pembayaran_cash = (float) $data['pembayaran_cash'];
        $tanggal         = $data['tanggal'];
        $status          = $data['status'];
        $sub_kategori    = $data['nama_jenis'];

        // Akumulasi riwayat pembayaran
        $sql_byr = "SELECT SUM(jumlah) AS total_bayar FROM transaksi_pembayaran WHERE id_transaksi = ?";
        $stmt_byr = mysqli_prepare($Conn, $sql_byr);
        mysqli_stmt_bind_param($stmt_byr, "s", $id);
        mysqli_stmt_execute($stmt_byr);
        $res_byr = mysqli_stmt_get_result($stmt_byr);
        $data_byr = mysqli_fetch_assoc($res_byr);
        mysqli_stmt_close($stmt_byr);

        $total_terbayar = $pembayaran_cash + (float) ($data_byr['total_bayar'] ?? 0);
        $sisa_tagihan   = $jml_tagihan - $total_terbayar;
        if ($sisa_tagihan < 0) $sisa_tagihan = 0;

        $response = [
            "status"          => "Success",
            "tanggal"         => date('d/m/Y H:i', strtotime($tanggal)),
            "sub_kategori"    => $sub_kategori,
            "jumlah_tagihan"  => 'Rp ' . number_format($jml_tagihan, 0, ',', '.'),
            "total_terbayar"  => 'Rp ' . number_format($total_terbayar, 0, ',', '.'),
            "sisa_tagihan_val"=> $sisa_tagihan,
            "sisa_tagihan"    => 'Rp ' . number_format($sisa_tagihan, 0, ',', '.'),
            "status_transaksi"=> $status
        ];
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
?>
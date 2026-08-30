<?php
    //Koneksi, Helper dan Session
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";

    // Default JSON
    header('Content-Type: application/json; charset=utf-8');

    // Default Response
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.',
        'html'    => ''
    ];

    // Tanggal Sekarang
    $now=date('Y-m-d H:i:s');

    // Validasi Session Akses
    if(empty($SessionIdAkses)){
        $response = [
            'status'  => 'error',
            'message' => 'Sesi Akses Sudah Berakhir! Silahkan Login Ulang!',
            'html'    => '
                <div class="alert alert-danger">
                    <small>
                        <b>Oops!</b> Sesi Akses Sudah Berakhir! Silahkan Login Ulang!
                    </small>
                </div>
            '
        ];
        echo json_encode($response,JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validasi id_transaksi
    if(empty($_POST['id_transaksi'])){
        $response = [
            'status'  => 'error',
            'message' => 'ID Transaksi Tidak Boleh Kosong!',
            'html'    => '
                <div class="alert alert-danger">
                    <small>
                        <b>Oops!</b> Sesi Akses Sudah Berakhir! Silahkan Login Ulang!
                    </small>
                </div>
            '
        ];
        echo json_encode($response,JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Variabel Dan Sanitasi
    $id_transaksi=validateAndSanitizeInput($_POST['id_transaksi']);

    // Hapus data dengan prepared statement
    $StmtDelete = mysqli_prepare($Conn, "DELETE FROM transaksi WHERE id_transaksi=?");
    if(!$StmtDelete){
        echo json_encode([
            "status" => "error",
            "message" => "Gagal mempersiapkan query hapus!",
            "html" => ""
        ]);
        exit;
    }
    mysqli_stmt_bind_param($StmtDelete, "s", $id_transaksi);
    $DeleteStockOpename = mysqli_stmt_execute($StmtDelete);
    mysqli_stmt_close($StmtDelete);

    if($DeleteStockOpename){
        $kategori_log = "Transaksi";
        $deskripsi_log = "Hapus Transaksi Operasional";
        $InputLog = addLog($Conn, $SessionIdAkses, $now, $kategori_log, $deskripsi_log);

        if($InputLog=="Success"){
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil dihapus.",
                "html" => ""
            ]);
        }else{
            echo json_encode([
                "status" => "error",
                "message" => "Terjadi kesalahan pada saat menyimpan Log",
                "html" => ""
            ]);
        }
    }else{
        echo json_encode([
            "status" => "error",
            "message" => "Terjadi kesalahan pada saat menghapus data",
            "html" => ""
        ]);
    }
?>
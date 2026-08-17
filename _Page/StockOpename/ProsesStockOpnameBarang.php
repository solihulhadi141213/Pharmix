<?php

    // Koneksi
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Time Now
    $now = date('Y-m-d H:i:s');

    // Response JSON
    header('Content-Type: application/json; charset=utf-8');


    // =====================================================
    // FUNCTION RESPONSE
    // =====================================================

    function responseJson($status, $message, $html = "")
    {
        echo json_encode([
            "status"  => $status,
            "message" => $message,
            "html"    => $html
        ]);

        exit;
    }


    // =====================================================
    // CEK SESSION
    // =====================================================

    if (empty($SessionIdAkses)) {

        responseJson(
            "error",
            "Sesi Akses Sudah Berakhir. Silahkan Login Ulang!"
        );
    }


    // =====================================================
    // VALIDASI ID BARANG
    // =====================================================

    if (empty($_POST['id_barang'])) {

        responseJson(
            "error",
            "ID Barang Tidak Boleh Kosong!"
        );
    }


    // =====================================================
    // VALIDASI ID STOCK OPNAME
    // =====================================================

    if (empty($_POST['id_stock_opname'])) {

        responseJson(
            "error",
            "ID Sesi Stock Opname Tidak Boleh Kosong!"
        );
    }


    // =====================================================
    // AMBIL DATA POST
    // =====================================================

    $id_barang = validateAndSanitizeInput(
        $_POST['id_barang']
    );

    $id_stock_opname = validateAndSanitizeInput(
        $_POST['id_stock_opname']
    );

    $stok_awal = !empty($_POST['stok_awal'])
        ? validateAndSanitizeInput($_POST['stok_awal'])
        : 0;

    $stok_akhir = !empty($_POST['stok_akhir'])
        ? validateAndSanitizeInput($_POST['stok_akhir'])
        : 0;

    $harga = !empty($_POST['harga'])
        ? validateAndSanitizeInput($_POST['harga'])
        : 0;

    $keterangan = !empty($_POST['keterangan'])
        ? validateAndSanitizeInput($_POST['keterangan'])
        : "";


    // =====================================================
    // BERSIHKAN FORMAT ANGKA / DESIMAL
    // =====================================================

    $stok_awal  = str_replace(",", ".", str_replace(".", "", $stok_awal));
    $stok_akhir = str_replace(",", ".", str_replace(".", "", $stok_akhir));
    $harga      = str_replace(",", ".", str_replace(".", "", $harga));


    // =====================================================
    // VALIDASI ANGKA / DESIMAL
    // =====================================================

    if (!is_numeric($stok_awal)) {

        responseJson(
            "error",
            "Stok Awal Hanya Boleh Berupa Angka!"
        );
    }


    if (!is_numeric($stok_akhir)) {

        responseJson(
            "error",
            "Stok Akhir Hanya Boleh Berupa Angka!"
        );
    }


    if (!is_numeric($harga)) {

        responseJson(
            "error",
            "Harga Hanya Boleh Berupa Angka!"
        );
    }


    // =====================================================
    // KONVERSI KE TIPE DATA YANG SESUAI
    // =====================================================

    $id_barang       = (int) $id_barang;
    $id_stock_opname = (int) $id_stock_opname;
    $stok_awal       = (float) $stok_awal;
    $stok_akhir      = (float) $stok_akhir;
    $harga           = (float) $harga;


    // =====================================================
    // HITUNG SELISIH STOK
    // =====================================================

    $stok_gap = $stok_akhir - $stok_awal;


    // =====================================================
    // HITUNG JUMLAH
    // =====================================================

    $jumlah = $stok_gap * $harga;


    // =====================================================
    // MULAI TRANSACTION
    // =====================================================

    mysqli_begin_transaction($Conn);


    try {

        // =================================================
        // CEK DATA STOCK OPNAME
        // =================================================

        $StmtCek = mysqli_prepare(
            $Conn,
            "
            SELECT id_stock_opname_barang
            FROM stock_opname_barang
            WHERE id_stock_opname = ?
            AND id_barang = ?
            LIMIT 1
            "
        );

        if (!$StmtCek) {
            throw new Exception(
                "Gagal mempersiapkan query pengecekan data stok opname."
            );
        }


        mysqli_stmt_bind_param(
            $StmtCek,
            "ii",
            $id_stock_opname,
            $id_barang
        );


        if (!mysqli_stmt_execute($StmtCek)) {

            throw new Exception(
                "Gagal melakukan pengecekan data stok opname."
            );
        }


        $ResultCek = mysqli_stmt_get_result($StmtCek);

        $DataCek = mysqli_fetch_assoc($ResultCek);

        mysqli_stmt_close($StmtCek);


        // =================================================
        // JIKA DATA SUDAH ADA
        // =================================================

        if (!empty($DataCek)) {

            $id_stock_opname_barang =
                (int) $DataCek['id_stock_opname_barang'];


            $StmtUpdate = mysqli_prepare(
                $Conn,
                "
                UPDATE stock_opname_barang
                SET
                    stok_awal = ?,
                    stok_akhir = ?,
                    stok_gap = ?,
                    harga_beli = ?,
                    jumlah = ?,
                    keterangan = ?,
                    updateAt = ?,
                    updateBy = ?
                WHERE id_stock_opname_barang = ?
                "
            );


            if (!$StmtUpdate) {

                throw new Exception(
                    "Gagal mempersiapkan query update data stok opname barang."
                );
            }


            mysqli_stmt_bind_param(
                $StmtUpdate,
                "dddddssii",
                $stok_awal,
                $stok_akhir,
                $stok_gap,
                $harga,
                $jumlah,
                $keterangan,
                $now,
                $SessionIdAkses,
                $id_stock_opname_barang
            );


            if (!mysqli_stmt_execute($StmtUpdate)) {

                throw new Exception(
                    "Gagal memperbarui data stok opname barang."
                );
            }


            mysqli_stmt_close($StmtUpdate);


        // =================================================
        // JIKA DATA BELUM ADA
        // =================================================

        } else {

            $StmtInsert = mysqli_prepare(
                $Conn,
                "
                INSERT INTO stock_opname_barang
                (
                    id_stock_opname,
                    id_barang,
                    stok_awal,
                    stok_akhir,
                    stok_gap,
                    harga_beli,
                    jumlah,
                    keterangan,
                    creatAt,
                    creatBy,
                    updateAt,
                    updateBy
                )
                VALUES
                (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
                "
            );


            if (!$StmtInsert) {

                throw new Exception(
                    "Gagal mempersiapkan query insert data stok opname barang."
                );
            }


            mysqli_stmt_bind_param(
                $StmtInsert,
                "iiddddsisisi",
                $id_stock_opname,
                $id_barang,
                $stok_awal,
                $stok_akhir,
                $stok_gap,
                $harga,
                $jumlah,
                $keterangan,
                $now,
                $SessionIdAkses,
                $now,
                $SessionIdAkses
            );


            if (!mysqli_stmt_execute($StmtInsert)) {

                throw new Exception(
                    "Gagal menyimpan data stok opname barang."
                );
            }


            mysqli_stmt_close($StmtInsert);
        }


        // =================================================
        // UPDATE MASTER BARANG
        // =================================================

        $StmtBarang = mysqli_prepare(
            $Conn,
            "
            UPDATE barang
            SET
                harga_beli = ?,
                stok_barang = ?
            WHERE id_barang = ?
            "
        );


        if (!$StmtBarang) {

            throw new Exception(
                "Gagal mempersiapkan query update barang."
            );
        }


        mysqli_stmt_bind_param(
            $StmtBarang,
            "dii",
            $harga,
            $stok_akhir,
            $id_barang
        );


        if (!mysqli_stmt_execute($StmtBarang)) {

            throw new Exception(
                "Gagal memperbarui data barang."
            );
        }


        mysqli_stmt_close($StmtBarang);


        // =================================================
        // LOG AKTIVITAS
        // =================================================

        $kategori_log = "Barang";

        $deskripsi_log = "Atur Stock Opname Barang";

        $InputLog = addLog(
            $Conn,
            $SessionIdAkses,
            $now,
            $kategori_log,
            $deskripsi_log
        );


        if ($InputLog != "Success") {

            throw new Exception(
                "Gagal menyimpan log aktivitas."
            );
        }


        // =================================================
        // COMMIT
        // =================================================

        mysqli_commit($Conn);


        responseJson(
            "success",
            "Data berhasil disimpan."
        );


    } catch (Exception $e) {

        // =================================================
        // ROLLBACK
        // =================================================

        mysqli_rollback($Conn);


        responseJson(
            "error",
            $e->getMessage()
        );
    }
?>
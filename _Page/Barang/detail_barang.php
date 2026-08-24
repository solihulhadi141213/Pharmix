<?php

    // =========================================================
    // Koneksi, Function dan Session
    // =========================================================
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // =========================================================
    // Timezone
    // =========================================================
    date_default_timezone_set('Asia/Jakarta');

    // =========================================================
    // Header Response
    // =========================================================
    header('Content-Type: application/json; charset=utf-8');

    // =========================================================
    // Response Default
    // =========================================================
    $response = [
        "status"  => "Error",
        "message" => "Belum ada proses yang dilakukan pada sistem."
    ];

    try {

        // =====================================================
        // Validasi Session
        // =====================================================
        if (empty($SessionIdAkses)) {
            $response = [
                "status"  => "Error",
                "message" => "Sesi Akses Sudah Berakhir, Silahkan Login Ulang."
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        // =====================================================
        // Validasi ID Barang
        // =====================================================
        if (empty($_POST['id_barang'])) {
            $response = [
                "status"  => "Error",
                "message" => "ID Barang Tidak Boleh Kosong!"
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        // =====================================================
        // Sanitasi Input
        // =====================================================
        $id_barang = validateAndSanitizeInput($_POST['id_barang']);

        // =====================================================
        // Ambil Data Barang
        // =====================================================
        $sqlBarang = "
            SELECT
                id_barang,
                kode_barang,
                nama_barang,
                kategori_barang,
                satuan_barang,
                konversi,
                harga_beli,
                stok_barang,
                stok_minimum
            FROM barang
            WHERE id_barang = ?
            LIMIT 1
        ";

        $stmtBarang = $Conn->prepare($sqlBarang);

        if (!$stmtBarang) {
            throw new Exception("Gagal menyiapkan query barang: " . $Conn->error);
        }

        /*
        * Jika id_barang pada database bertipe INTEGER,
        * gunakan "i".
        *
        * Jika VARCHAR/CHAR, ubah menjadi "s".
        */
        $stmtBarang->bind_param("s", $id_barang);

        if (!$stmtBarang->execute()) {
            throw new Exception("Gagal mengambil data barang: " . $stmtBarang->error);
        }

        $resultBarang = $stmtBarang->get_result();
        $Data = $resultBarang->fetch_assoc();

        $stmtBarang->close();

        // =====================================================
        // Validasi Data Barang
        // =====================================================
        if (!$Data) {
            $response = [
                "status"  => "Error",
                "message" => "Data Barang Tidak Ditemukan."
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        // =====================================================
        // Variabel Barang
        // =====================================================
        $kode_barang     = $Data['kode_barang'];
        $nama_barang     = $Data['nama_barang'];
        $kategori_barang = $Data['kategori_barang'];
        $satuan_barang   = $Data['satuan_barang'];
        $konversi        = $Data['konversi'];
        $harga_beli      = (float) $Data['harga_beli'];
        $stok_barang     = $Data['stok_barang'];
        $stok_minimum    = $Data['stok_minimum'];

        // =====================================================
        // Format Harga Beli
        // =====================================================
        $harga_beli_format = "Rp " . number_format(
            $harga_beli,
            0,
            ',',
            '.'
        );

        // =====================================================
        // Hitung Jumlah Multi Harga
        // =====================================================
        $sqlJumlahHarga = "
            SELECT COUNT(id_barang_harga) AS jumlah
            FROM barang_harga
            WHERE id_barang = ?
        ";

        $stmtJumlahHarga = $Conn->prepare($sqlJumlahHarga);

        if (!$stmtJumlahHarga) {
            throw new Exception(
                "Gagal menyiapkan query jumlah harga: " . $Conn->error
            );
        }

        $stmtJumlahHarga->bind_param("s", $id_barang);

        if (!$stmtJumlahHarga->execute()) {
            throw new Exception(
                "Gagal menghitung jumlah harga: " . $stmtJumlahHarga->error
            );
        }

        $resultJumlahHarga = $stmtJumlahHarga->get_result();
        $dataJumlahHarga = $resultJumlahHarga->fetch_assoc();

        $jumlah_multi_harga = (int) ($dataJumlahHarga['jumlah'] ?? 0);

        $stmtJumlahHarga->close();

        // =====================================================
        // Dataset Barang
        // =====================================================
        $dataset = [
            "id_barang"          => $id_barang,
            "kode_barang"        => $kode_barang,
            "nama_barang"        => $nama_barang,
            "kategori_barang"    => $kategori_barang,
            "satuan_barang"      => $satuan_barang,
            "konversi"           => $konversi,
            "harga_beli"         => $harga_beli,
            "harga_beli_format"  => $harga_beli_format,
            "stok_barang"        => $stok_barang,
            "stok_minimum"       => $stok_minimum,
            "jumlah_multi_harga" => $jumlah_multi_harga
        ];

        // =====================================================
        // Ambil Kategori Multi Harga
        // =====================================================
        $multi_harga = [];

        $sqlMultiHarga = "
            SELECT
                bkh.id_barang_kategori_harga,
                bkh.kategori_harga,
                COALESCE(bh.harga, 0) AS harga
            FROM barang_kategori_harga AS bkh

            LEFT JOIN barang_harga AS bh
                ON bkh.id_barang_kategori_harga = bh.id_barang_kategori_harga
                AND bh.id_barang = ?

            ORDER BY bkh.id_barang_kategori_harga ASC
        ";

        $stmtMultiHarga = $Conn->prepare($sqlMultiHarga);

        if (!$stmtMultiHarga) {
            throw new Exception(
                "Gagal menyiapkan query multi harga: " . $Conn->error
            );
        }

        $stmtMultiHarga->bind_param("s", $id_barang);

        if (!$stmtMultiHarga->execute()) {
            throw new Exception(
                "Gagal mengambil multi harga: " . $stmtMultiHarga->error
            );
        }

        $resultMultiHarga = $stmtMultiHarga->get_result();

        // =====================================================
        // Loop Multi Harga
        // =====================================================
        while ($row = $resultMultiHarga->fetch_assoc()) {

            $harga_multi = (float) $row['harga'];

            // ---------------------------------------------
            // Hitung Persentase Laba
            // ---------------------------------------------
            if ($harga_beli > 0) {

                $selisih = $harga_multi - $harga_beli;

                $persen_laba = ($selisih / $harga_beli) * 100;

                $persen_laba = round($persen_laba);

            } else {

                /*
                * Harga beli = 0 tidak boleh digunakan
                * sebagai pembagi.
                *
                * Kita gunakan 0 sebagai default.
                */
                $persen_laba = 0;
            }

            // ---------------------------------------------
            // Format Harga
            // ---------------------------------------------
            $harga_multi_format = "Rp " . number_format(
                $harga_multi,
                0,
                ',',
                '.'
            );

            // ---------------------------------------------
            // Masukkan ke Array
            // ---------------------------------------------
            $multi_harga[] = [
                "id_barang_kategori_harga" => $row['id_barang_kategori_harga'],
                "kategori_harga"           => $row['kategori_harga'],
                "harga"                    => $harga_multi,
                "persen_laba"              => $persen_laba,
                "harga_format"             => $harga_multi_format
            ];
        }

        $stmtMultiHarga->close();

        // =====================================================
        // Response Success
        // =====================================================
        $response = [
            "status"      => "Success",
            "message"     => "Data Ditemukan",
            "dataset"     => $dataset,
            "multi_harga" => $multi_harga
        ];

    } catch (Throwable $e) {

        // =====================================================
        // Error Handling
        // =====================================================
        $response = [
            "status"  => "Error",
            "message" => $e->getMessage()
        ];
    }

    // =========================================================
    // Output JSON
    // =========================================================
    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
?>
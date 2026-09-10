<?php
    // ---------------------------------------
    // KONFIGURASI DAN VALIDASI AKSES
    // ---------------------------------------
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    function kirimJson(array $data, int $kode = 200): void {
        http_response_code($kode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    if (empty($SessionIdAkses)) {
        kirimJson(['message' => 'Sesi akses sudah berakhir. Silakan login ulang.'], 401);
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header('Allow: GET');
        kirimJson(['message' => 'Metode request tidak valid.'], 405);
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // ---------------------------------------
    // VALIDASI BARANG
    // ---------------------------------------
    $id_barang = $_GET['id_barang'] ?? '';
    if (!is_string($id_barang) || !ctype_digit($id_barang) ||
        (float) $id_barang < 1 || (float) $id_barang > 4294967295) {
        kirimJson(['message' => 'ID barang tidak valid.'], 400);
    }

    try {
        // Satu query: barang tetap ditemukan meskipun tidak memiliki satuan multi.
        $stmt = $Conn->prepare(
            "SELECT b.satuan_barang, s.id_barang_satuan, s.satuan_multi
            FROM barang b
            LEFT JOIN barang_satuan s ON s.id_barang = b.id_barang
            WHERE b.id_barang = ?
            ORDER BY s.satuan_multi ASC, s.id_barang_satuan ASC"
        );
        $stmt->bind_param('s', $id_barang);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (!$row) {
            $stmt->close();
            kirimJson(['message' => 'Barang tidak ditemukan.'], 404);
        }

        $satuan_barang = $row['satuan_barang'];
        $results = [];
        do {
            if ($row['id_barang_satuan'] !== null) {
                $results[] = [
                    'id'   => (string) $row['id_barang_satuan'],
                    'text' => $row['satuan_multi']
                ];
            }
        } while ($row = $result->fetch_assoc());
        $stmt->close();

        kirimJson(['satuan_barang' => $satuan_barang, 'results' => $results]);
    } catch (mysqli_sql_exception $e) {
        error_log('ListSatuan: ' . $e->getMessage());
        kirimJson(['message' => 'Gagal memuat satuan barang.'], 500);
    }
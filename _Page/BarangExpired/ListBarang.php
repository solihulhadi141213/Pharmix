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
    // PARAMETER PENCARIAN DAN PAGINATION
    // ---------------------------------------
    $keyword = $_GET['keyword'] ?? '';
    $page    = $_GET['page'] ?? '1';

    if (!is_string($keyword) || !is_string($page) ||
        !ctype_digit($page) || (float) $page < 1 || (float) $page > 214748364) {
        kirimJson(['message' => 'Parameter pencarian tidak valid.'], 400);
    }
    $keyword = trim($keyword);
    $page    = (int) $page;
    $limit   = 10;
    $offset  = ($page - 1) * $limit;
    // Ambil satu baris tambahan untuk mendeteksi halaman berikutnya.
    $fetch   = $limit + 1;

    try {
        $sql = "SELECT id_barang, kode_barang, nama_barang, kategori_barang FROM barang";
        if ($keyword !== '') {
            $sql .= " WHERE kode_barang LIKE ? ESCAPE '!'
                        OR nama_barang LIKE ? ESCAPE '!'
                        OR kategori_barang LIKE ? ESCAPE '!'";
        }
        $sql .= " ORDER BY nama_barang ASC, id_barang ASC LIMIT ? OFFSET ?";
        $stmt = $Conn->prepare($sql);

        if ($keyword !== '') {
            $cari = '%' . strtr($keyword, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $stmt->bind_param('sssii', $cari, $cari, $cari, $fetch, $offset);
        } else {
            $stmt->bind_param('ii', $fetch, $offset);
        }
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $more = count($rows) > $limit;
        $results = [];
        foreach (array_slice($rows, 0, $limit) as $row) {
            $results[] = [
                'id'              => (string) $row['id_barang'],
                'text'            => $row['kode_barang'] . ' - ' . $row['nama_barang'],
                'kode_barang'     => $row['kode_barang'],
                'nama_barang'     => $row['nama_barang'],
                'kategori_barang' => $row['kategori_barang']
            ];
        }
        kirimJson(['results' => $results, 'pagination' => ['more' => $more]]);
    } catch (mysqli_sql_exception $e) {
        error_log('ListBarang: ' . $e->getMessage());
        kirimJson(['message' => 'Gagal memuat data barang.'], 500);
    }
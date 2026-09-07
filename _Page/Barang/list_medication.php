<?php
    header('Content-Type: application/json; charset=utf-8');

    // Koneksi & Session
    require_once __DIR__ . '/../../_Config/Connection.php';
    require_once __DIR__ . '/../../_Config/GlobalFunction.php';
    require_once __DIR__ . '/../../_Config/Session.php';

    date_default_timezone_set('Asia/Jakarta');

    // Helper Response Error
    function responseResepError(
        string $message,
        int $page = 1,
        int $total_page = 1,
        int $total_data = 0
    ): void {
        echo json_encode([
            'status'     => 'error',
            'message'    => $message,
            'html'       => '
                <div class="alert alert-danger text-center mb-0">
                    <h1 class="bi bi-exclamation-triangle"></h1>
                    <small>
                        '.htmlspecialchars($message, ENT_QUOTES, 'UTF-8').'
                    </small>
                </div>
            ',
            'page'       => $page,
            'total_page' => $total_page,
            'total_data' => $total_data
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // Helper Escape
    function e(?string $value): string {
        return htmlspecialchars(trim((string)$value),ENT_QUOTES,'UTF-8');
    }

    // Validasi Session
    if (empty($SessionIdAkses)) {
        responseResepError('Sesi akses sudah berakhir. Silakan login ulang.');
    }

    if(empty($_POST['id_barang'])){
        responseResepError('ID Barang Tidak Boleh Kosong!');
    }
    $id_barang = $_POST['id_barang'];

    // Parameter Request
    $page       = $_POST['page_medication'] ?? $_POST['page'] ?? 1;
    $batas      = $_POST['batas'] ?? 10;
    $OrderBy    = trim($_POST['OrderBy'] ?? 'id_index_medication');
    $ShortBy    = strtoupper(trim($_POST['ShortBy'] ?? 'DESC'));
    $keyword_by = trim($_POST['keyword_by'] ?? '');
    $keyword    = trim($_POST['keyword_medication'] ?? $_POST['keyword'] ?? '');

    // Validasi Page
    $page = filter_var($page, FILTER_VALIDATE_INT);
    if ($page === false || $page < 1) {
        $page = 1;
    }

    // Validasi Limit
    $allowedLimit = [10, 25, 50, 100, 250];
    $batas = filter_var($batas, FILTER_VALIDATE_INT);
    if ($batas === false || !in_array($batas, $allowedLimit, true)) {
        $batas = 10;
    }

    // Mapping Order By
    $allowedOrderBy = [
        'id_index_medication' => 'id_index_medication',
        'id_medication' => 'id_medication',
        'medication_code' => 'medication_code',
        'medication_name' => 'medication_name'
    ];

    if ($OrderBy === '' || !isset($allowedOrderBy[$OrderBy])) {
        $OrderBy = 'id_index_medication';
    }
    $orderColumn = $allowedOrderBy[$OrderBy];

    // Validasi Sort
    if (!in_array($ShortBy, ['ASC', 'DESC'], true)) {
        $ShortBy = 'DESC';
    }

    // Mapping Keyword By
    $allowedKeywordBy = [
        'id_medication' => 'id_medication',
        'medication_code' => 'medication_code',
        'medication_name' => 'medication_name'
    ];

    if ($keyword_by !== '' && !isset($allowedKeywordBy[$keyword_by])) {
        $keyword_by = '';
    }

    // Base Query
    $from = "FROM medication";

    // Build Where
    $where      = '';
    $bindTypes  = '';
    $bindValues = [];

    if ($keyword !== '') {
        $keywordLike = '%'.$keyword.'%';
        $where = "
            WHERE (
                id_medication LIKE ?
                OR medication_code LIKE ?
                OR medication_name LIKE ?
            )
        ";
        $bindTypes = 'sss';
        $bindValues = [
            $keywordLike, 
            $keywordLike, 
            $keywordLike
        ];
        if ($keyword_by !== '') {
            $where = 'WHERE ' . $allowedKeywordBy[$keyword_by] . ' LIKE ?';
            $bindTypes = 's';
            $bindValues = [$keywordLike];
        }
    }

    // Hitung Total Data
    $sqlCount = "SELECT COUNT(*) AS total $from $where";
    $stmtCount = mysqli_prepare($Conn, $sqlCount);

    if (!$stmtCount) {
        responseResepError('Gagal mempersiapkan query jumlah data medication.', $page);
    }

    if (!empty($bindValues)) {
        mysqli_stmt_bind_param($stmtCount, $bindTypes, ...$bindValues);
    }

    if (!mysqli_stmt_execute($stmtCount)) {
        mysqli_stmt_close($stmtCount);
        responseResepError('Gagal menghitung jumlah data medication.', $page);
    }

    $resultCount = mysqli_stmt_get_result($stmtCount);
    if (!$resultCount) {
        mysqli_stmt_close($stmtCount);
        responseResepError('Gagal membaca jumlah data.', $page);
    }

    $rowCount   = mysqli_fetch_assoc($resultCount);
    $total_data = (int)($rowCount['total'] ?? 0);
    mysqli_stmt_close($stmtCount);

    // Pagination
    $total_page = $total_data > 0 ? (int)ceil($total_data / $batas) : 1;
    if ($page > $total_page) {
        $page = $total_page;
    }
    $posisi = ($page - 1) * $batas;

    // Query Data
    $sql = "SELECT * $from $where ORDER BY $orderColumn $ShortBy LIMIT ?, ?";
    $stmt = mysqli_prepare($Conn, $sql);
    if (!$stmt) {
        responseResepError('Gagal mempersiapkan query data.', $page, $total_page, $total_data);
    }

    // Bind Parameter Query Data
    $bindTypesData  = $bindTypes.'ii';
    $bindValuesData = $bindValues;
    $bindValuesData[] = $posisi;
    $bindValuesData[] = $batas;

    mysqli_stmt_bind_param($stmt, $bindTypesData, ...$bindValuesData);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        responseResepError('Terjadi kesalahan saat mengambil data.', $page, $total_page, $total_data);
    }

    $query = mysqli_stmt_get_result($stmt);
    if (!$query) {
        mysqli_stmt_close($stmt);
        responseResepError('Gagal membaca data.', $page, $total_page, $total_data);
    }

    // Generate HTML
    $html = '';
    $no   = $posisi + 1;

    if (mysqli_num_rows($query) < 1) {
        $html = '
            <div class="alert alert-warning text-center mb-0">
                <h1 class="bi bi-exclamation-triangle"></h1>
                <small>
                    <b>Opss!</b><br>
                    Tidak ada data yang ditemukan.
                </small>
            </div>
        ';
    } else {
        while ($data = mysqli_fetch_assoc($query)) {
            $id_index_medication = (int)($data['id_index_medication'] ?? 0);
            $id_medication       = $data['id_medication'] ?? '-';
            $medication_code     = $data['medication_code'] ?? '-';
            $medication_name     = $data['medication_name'] ?? '-';
            $medication_category = $data['medication_category'] ?? '-';
            $sediaan_code        = $data['sediaan_code'] ?? '-';

            // Status barang sama untuk semua pilihan, cukup diperiksa sekali.
            $cek_barang = GetDetailData($Conn, 'barang', 'id_index_medication', $id_index_medication, 'id_index_medication');
            $sudah_terhubung = !empty($cek_barang);
            $class_pilihan = $sudah_terhubung ? 'disabled bg-light text-muted' : 'pilih_data_index';
            $atribut_disabled = $sudah_terhubung ? ' disabled aria-disabled="true"' : '';

            $id_medication_ringkas = mb_strlen($id_medication, 'UTF-8') > 20
                ? mb_substr($id_medication, 0, 16, 'UTF-8') . '...'
                : $id_medication;
            $badge_kategori = [
                'Obat' => 'bg-success',
                'Alkes' => 'bg-info text-dark',
                'Lainnya' => 'bg-secondary'
            ][$medication_category] ?? 'bg-secondary';
            $status_pilihan = $sudah_terhubung
                ? '<span class="small text-muted"><i class="bi bi-lock" aria-hidden="true"></i> Barang sudah memiliki index medication</span>'
                : '<span class="small text-primary">Pilih <i class="bi bi-chevron-right" aria-hidden="true"></i></span>';

            // Render Card Item
            $html .= '
                <button type="button" class="list-group-item list-group-item-action text-start p-3 '.$class_pilihan.'" data-barang="'.e($id_barang).'" data-index="'.$id_index_medication.'"'.$atribut_disabled.'>
                    <span class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <span class="fw-bold small text-break">'.e($medication_name).'</span>
                        <span class="badge '.$badge_kategori.'">'.e($medication_category).'</span>
                    </span>
                    <span class="d-block small text-muted text-break">Kode: '.e($medication_code).'</span>
                    <span class="d-block small text-muted" title="'.e($id_medication).'">ID Medication: '.e($id_medication_ringkas).'</span>
                </button>
            ';

            $no++;
        }
        $html = '<div class="list-group">'.$html.'</div>';
    }

    mysqli_stmt_close($stmt);

    // Response Success
    echo json_encode([
        'status'     => 'success',
        'html'       => $html,
        'page'       => $page,
        'total_page' => $total_page,
        'total_data' => $total_data
    ], JSON_UNESCAPED_UNICODE);
    exit;
?>

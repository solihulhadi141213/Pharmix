<?php
    header('Content-Type: application/json; charset=utf-8');

    // Koneksi & Session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

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
                <div class="col-12">
                    <div class="alert alert-danger text-center mb-0">
                        <h1 class="bi bi-exclamation-triangle"></h1>
                        <small>
                            '.htmlspecialchars($message, ENT_QUOTES, 'UTF-8').'
                        </small>
                    </div>
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
        return htmlspecialchars(
            trim((string)$value),
            ENT_QUOTES,
            'UTF-8'
        );
    }

    // Validasi Session
    if (empty($SessionIdAkses)) {
        responseResepError('Sesi akses sudah berakhir. Silakan login ulang.');
    }

    // Parameter Request
    $page       = $_POST['page'] ?? 1;
    $batas      = $_POST['batas'] ?? 12;
    $OrderBy    = trim($_POST['OrderBy'] ?? '');
    $ShortBy    = strtoupper(trim($_POST['ShortBy'] ?? 'DESC'));
    $keyword_by = trim($_POST['keyword_by'] ?? '');
    $keyword    = trim($_POST['keyword'] ?? '');

    // Validasi Page
    $page = filter_var($page, FILTER_VALIDATE_INT);
    if ($page === false || $page < 1) {
        $page = 1;
    }

    // Validasi Limit
    $allowedLimit = [8, 12, 16, 20, 24];
    $batas = filter_var($batas, FILTER_VALIDATE_INT);
    if ($batas === false || !in_array($batas, $allowedLimit, true)) {
        $batas = 12;
    }

    // Mapping Order By
    $allowedOrderBy = [
        'id_medication_request_group' => 'mrg.id_medication_request_group',
        'id_pasien'                   => 'a.id_pasien',
        'nama_pasien'                 => 'mrg.nama_pasien',
        'no_resep_nasional'           => 'mrg.no_resep_nasional',
        'priority'                    => 'mrg.priority',
        'datetime_creat'              => 'mrg.datetime_creat',
        'dokter_nama'                 => 'mrg.dokter_nama',
        'sumber_resep'                 => 'mrg.sumber_resep',
        'status_resep'                => 'mrg.status_resep'
    ];

    if ($OrderBy === '' || !isset($allowedOrderBy[$OrderBy])) {
        $OrderBy = 'id_medication_request_group';
    }
    $orderColumn = $allowedOrderBy[$OrderBy];

    // Validasi Sort
    if (!in_array($ShortBy, ['ASC', 'DESC'], true)) {
        $ShortBy = 'DESC';
    }

    // Mapping Keyword By
    $allowedKeywordBy = [
        'id_pasien'         => 'a.id_pasien',
        'nama_pasien'       => 'mrg.nama_pasien',
        'no_resep_nasional' => 'mrg.no_resep_nasional',
        'priority'          => 'mrg.priority',
        'datetime_creat'    => 'mrg.datetime_creat',
        'dokter_nama'       => 'mrg.dokter_nama',
        'sumber_resep'       => 'mrg.sumber_resep',
        'status_resep'      => 'mrg.status_resep'
    ];

    if ($keyword_by !== '' && !isset($allowedKeywordBy[$keyword_by])) {
        $keyword_by = '';
    }

    // Base Query
    $from = "
        FROM medication_request_group AS mrg
        LEFT JOIN anggota AS a
            ON a.id_anggota = mrg.id_anggota
    ";

    // Build Where
    $where      = '';
    $bindTypes  = '';
    $bindValues = [];

    if ($keyword !== '') {
        if ($keyword_by !== '') {
            $column = $allowedKeywordBy[$keyword_by];

            if ($keyword_by === 'datetime_creat') {
                $where = "WHERE DATE(mrg.datetime_creat) = ?";
                $bindTypes   = 's';
                $bindValues[] = $keyword;
            } elseif (in_array($keyword_by, ['priority', 'status_resep', 'sumber_resep'], true)) {
                $where = "WHERE $column = ?";
                $bindTypes   = 's';
                $bindValues[] = $keyword;
            } else {
                $where = "WHERE $column LIKE ?";
                $bindTypes   = 's';
                $bindValues[] = '%'.$keyword.'%';
            }
        } else {
            $keywordLike = '%'.$keyword.'%';
            $where = "
                WHERE (
                    a.id_pasien LIKE ?
                    OR mrg.nama_pasien LIKE ?
                    OR mrg.no_resep_nasional LIKE ?
                    OR mrg.priority LIKE ?
                    OR mrg.dokter_nama LIKE ?
                    OR mrg.sumber_resep LIKE ?
                    OR mrg.status_resep LIKE ?
                )
            ";
            $bindTypes = 'sssssss';
            $bindValues = [
                $keywordLike, $keywordLike, $keywordLike,
                $keywordLike, $keywordLike, $keywordLike, $keywordLike
            ];
        }
    }

    // Hitung Total Data
    $sqlCount = "SELECT COUNT(*) AS total $from $where";
    $stmtCount = mysqli_prepare($Conn, $sqlCount);

    if (!$stmtCount) {
        responseResepError('Gagal mempersiapkan query jumlah data resep.', $page);
    }

    if (!empty($bindValues)) {
        mysqli_stmt_bind_param($stmtCount, $bindTypes, ...$bindValues);
    }

    if (!mysqli_stmt_execute($stmtCount)) {
        mysqli_stmt_close($stmtCount);
        responseResepError('Gagal menghitung jumlah data resep.', $page);
    }

    $resultCount = mysqli_stmt_get_result($stmtCount);
    if (!$resultCount) {
        mysqli_stmt_close($stmtCount);
        responseResepError('Gagal membaca jumlah data resep.', $page);
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
    $sql = "
        SELECT
            mrg.id_medication_request_group,
            mrg.id_anggota,
            mrg.id_kunjungan,
            mrg.nama_pasien,
            mrg.priority,
            mrg.datetime_creat,
            mrg.datetime_verified,
            mrg.datetime_completed,
            mrg.dokter_id,
            mrg.dokter_code,
            mrg.dokter_ihs,
            mrg.dokter_nama,
            mrg.apoteker_id,
            mrg.apoteker_nama,
            mrg.sumber_resep,
            mrg.status_resep,
            mrg.no_resep_nasional,
            a.id_pasien
        $from
        $where
        ORDER BY $orderColumn $ShortBy
        LIMIT ?, ?
    ";

    $stmt = mysqli_prepare($Conn, $sql);
    if (!$stmt) {
        responseResepError('Gagal mempersiapkan query data resep.', $page, $total_page, $total_data);
    }

    // Bind Parameter Query Data
    $bindTypesData  = $bindTypes.'ii';
    $bindValuesData = $bindValues;
    $bindValuesData[] = $posisi;
    $bindValuesData[] = $batas;

    mysqli_stmt_bind_param($stmt, $bindTypesData, ...$bindValuesData);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        responseResepError('Terjadi kesalahan saat mengambil data resep.', $page, $total_page, $total_data);
    }

    $query = mysqli_stmt_get_result($stmt);
    if (!$query) {
        mysqli_stmt_close($stmt);
        responseResepError('Gagal membaca data resep.', $page, $total_page, $total_data);
    }

    // Generate HTML
    $html = '';
    $no   = $posisi + 1;

    if (mysqli_num_rows($query) < 1) {
        $html = '
            <div class="col-12">
                <div class="alert alert-warning text-center mb-0">
                    <h1 class="bi bi-exclamation-triangle"></h1>
                    <small>
                        <b>Opss!</b><br>
                        Tidak ada data resep yang ditemukan.
                    </small>
                </div>
            </div>
        ';
    } else {
        while ($data = mysqli_fetch_assoc($query)) {
            $id_medication_request_group = (int)($data['id_medication_request_group'] ?? 0);
            $nama_pasien                 = e($data['nama_pasien'] ?? '-');
            $id_pasien                   = e(!empty($data['id_pasien']) ? $data['id_pasien'] : '-');
            $dokter_nama                 = e(!empty($data['dokter_nama']) ? $data['dokter_nama'] : '-');
            $sumber_resep                 = e(!empty($data['sumber_resep']) ? $data['sumber_resep'] : '-');
            $priorityRaw                 = trim((string)($data['priority'] ?? ''));
            $statusRaw                   = trim((string)($data['status_resep'] ?? ''));
            $nrnRaw                      = trim((string)($data['no_resep_nasional'] ?? ''));

            // Tanggal Resep
            $datetimeRaw = trim((string)($data['datetime_creat'] ?? ''));
            if ($datetimeRaw !== '' && $datetimeRaw !== '0000-00-00 00:00:00') {
                $timestamp = strtotime($datetimeRaw);
                $tanggalResep = $timestamp ? date('d/m/Y H:i', $timestamp) : '-';
            } else {
                $tanggalResep = '-';
            }

            // Priority Badge
            switch ($priorityRaw) {
                case 'routine':
                    $priorityBadge = '<span class="badge bg-primary">Routine</span>';
                    break;
                case 'urgent':
                    $priorityBadge = '<span class="badge bg-warning text-dark">Urgent</span>';
                    break;
                case 'asap':
                    $priorityBadge = '<span class="badge bg-danger">ASAP</span>';
                    break;
                case 'stat':
                    $priorityBadge = '<span class="badge bg-danger">STAT</span>';
                    break;
                default:
                    $priorityBadge = '<span class="badge bg-secondary">-</span>';
                    break;
            }

            // Status Resep Badge
            switch ($statusRaw) {
                case 'Draft':
                    $statusClass = 'bg-secondary';
                    break;
                case 'Verified':
                    $statusClass = 'bg-primary';
                    break;
                case 'Partially':
                    $statusClass = 'bg-warning text-dark';
                    break;
                case 'Completed':
                    $statusClass = 'bg-success';
                    break;
                case 'Cancelled':
                    $statusClass = 'bg-danger';
                    break;
                default:
                    $statusClass = 'bg-secondary';
                    break;
            }

            $statusDisplay = $statusRaw !== '' ? e($statusRaw) : '-';
            $statusBadge = '<span class="badge '.$statusClass.'">'.$statusDisplay.'</span>';

            // NRN Html
            if ($nrnRaw !== '') {
                $nrn = e($nrnRaw);
                $nrnHtml = '<code class="text-primary fw-semibold">'.$nrn.'</code>';
            } else {
                $nrnHtml = '<span class="text-muted">Belum tersedia</span>';
            }

            // Render Card Item
            $html .= '
                <div class="col-12 col-md-6 col-xl-4 col-xxl-3 mb-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-visible">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:32px; height:32px; font-size:.8rem; font-weight:600;">
                                    '.$no.'
                                </span>
                                <div class="flex-grow-1 min-w-0 pe-1">
                                    <a href="javascript:void(0);" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#ModalDetail" data-id="'.$id_medication_request_group.'">
                                        <h6 class="card-title text-dark fw-semibold mb-0 text-break">'.$nama_pasien.'</h6>
                                    </a>
                                </div>
                                <div class="dropdown flex-shrink-0">
                                    <button type="button" class="p-0 border-0 bg-transparent text-muted fs-5" data-bs-toggle="dropdown" aria-expanded="false" title="Opsi">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetail" data-id="'.$id_medication_request_group.'">
                                                <i class="bi bi-eye me-2"></i> Detail
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item edit_resep" href="javascript:void(0);" data-id="'.$id_medication_request_group.'">
                                                <i class="bi bi-pencil me-2"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger hapus_resep" href="javascript:void(0);" data-id="'.$id_medication_request_group.'">
                                                <i class="bi bi-trash me-2"></i> Hapus
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="small">
                                <div class="d-flex justify-content-between gap-2 py-2 border-top">
                                    <span class="text-muted flex-shrink-0"><i class="bi bi-calendar3 me-1"></i> No.RM</span>
                                    <span class="text-dark text-end">'.$id_pasien.'</span>
                                </div>
                                 <div class="d-flex justify-content-between gap-2 py-2 border-top">
                                    <span class="text-muted flex-shrink-0"><i class="bi bi-calendar3 me-1"></i> Tanggal</span>
                                    <span class="text-dark text-end">'.$tanggalResep.'</span>
                                </div>
                                <div class="d-flex justify-content-between gap-2 py-2 border-top">
                                    <span class="text-muted flex-shrink-0"><i class="bi bi-person-badge me-1"></i> Dokter</span>
                                    <span class="text-dark text-end text-break">'.$dokter_nama.'</span>
                                </div>
                                <div class="d-flex justify-content-between gap-2 py-2 border-top">
                                    <span class="text-muted flex-shrink-0"><i class="bi bi-database me-1"></i> Sumber</span>
                                    <span class="text-dark text-end text-break">'.$sumber_resep.'</span>
                                </div>
                                <div class="d-flex justify-content-between gap-2 py-2 border-top">
                                    <span class="text-muted flex-shrink-0"><i class="bi bi-exclamation-circle me-1"></i> Priority</span>
                                    <span>'.$priorityBadge.'</span>
                                </div>
                                <div class="d-flex justify-content-between gap-2 py-2 border-top">
                                    <span class="text-muted flex-shrink-0"><i class="bi bi-check-circle me-1"></i> Status</span>
                                    <span>'.$statusBadge.'</span>
                                </div>
                                <div class="d-flex justify-content-between gap-2 pt-2 border-top">
                                    <span class="text-muted flex-shrink-0"><i class="bi bi-prescription2 me-1"></i> NRN</span>
                                    <span class="text-end text-break">'.$nrnHtml.'</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            ';

            $no++;
        }
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
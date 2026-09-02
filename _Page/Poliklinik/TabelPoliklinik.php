<?php

    header('Content-Type: application/json; charset=utf-8');

    // =========================================================
    // KONEKSI DAN SESSION
    // =========================================================
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // =========================================================
    // HELPER RESPONSE ERROR
    // =========================================================
    function responsePoliklinikError(string $message, int $page = 1, int $total_page = 1, int $total_data = 0): void
    {
        echo json_encode([
            "status"     => "error",
            "html"       => '
                <div class="col-12">
                    <div class="alert alert-danger text-center mb-0">
                        <h1 class="bi bi-exclamation-triangle"></h1>
                        <small>
                            ' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '
                        </small>
                    </div>
                </div>
            ',
            "page"       => $page,
            "total_page" => $total_page,
            "total_data" => $total_data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // =========================================================
    // VALIDASI SESI AKSES
    // =========================================================
    if (empty($SessionIdAkses)) {
        responsePoliklinikError(
            "Sesi akses sudah berakhir. Silakan login ulang.",
            1,
            1,
            0
        );
    }

    // =========================================================
    // AMBIL PARAMETER FILTER
    // =========================================================
    $page       = $_POST['page'] ?? 1;
    $batas      = $_POST['batas'] ?? 12;
    $OrderBy    = trim($_POST['OrderBy'] ?? 'polyclinicId');
    $ShortBy    = trim($_POST['ShortBy'] ?? 'DESC');
    $keyword_by = trim($_POST['keyword_by'] ?? '');
    $keyword    = trim($_POST['keyword'] ?? '');

    // =========================================================
    // VALIDASI PAGE
    // =========================================================
    $page = filter_var($page, FILTER_VALIDATE_INT);
    if ($page === false || $page < 1) {
        $page = 1;
    }

    // =========================================================
    // VALIDASI BATAS
    // =========================================================
    $batas = filter_var($batas, FILTER_VALIDATE_INT);
    if ($batas === false || $batas < 1) {
        $batas = 12;
    }
    if ($batas > 100) {
        $batas = 100;
    }

    // =========================================================
    // VALIDASI ORDER BY
    // =========================================================
    $allowedOrderBy = [
        'polyclinicId',
        'satuSehatCode',
        'polyclinicCode',
        'polyclinicName',
        'polyclinicStatus'
    ];

    if (!in_array($OrderBy, $allowedOrderBy, true)) {
        $OrderBy = 'polyclinicId';
    }

    // =========================================================
    // VALIDASI SORT
    // =========================================================
    $ShortBy = strtoupper($ShortBy);
    if (!in_array($ShortBy, ['ASC', 'DESC'], true)) {
        $ShortBy = 'ASC';
    }

    // =========================================================
    // VALIDASI KEYWORD BY
    // =========================================================
    $allowedKeywordBy = [
        'polyclinicId',
        'satuSehatCode',
        'polyclinicCode',
        'polyclinicName',
        'polyclinicStatus'
    ];

    if ($keyword_by !== '' && !in_array($keyword_by, $allowedKeywordBy, true)) {
        $keyword_by = '';
    }

    // =========================================================
    // BUILD WHERE
    // =========================================================
    $where      = '';
    $bindTypes  = '';
    $bindValues = [];

    if ($keyword !== '') {
        $keywordLike = '%' . $keyword . '%';

        if ($keyword_by !== '') {
            if ($keyword_by === 'polyclinicStatus') {
                $where = " WHERE polyclinic.polyclinicStatus = ? ";
            } else {
                $where = " WHERE polyclinic.$keyword_by LIKE ? ";
            }
            $bindTypes = 's';
            $bindValues[] = ($keyword_by === 'polyclinicStatus') ? $keyword : $keywordLike;
        } else {
            $where = "
                WHERE (
                    polyclinic.satuSehatCode LIKE ?
                    OR polyclinic.polyclinicCode LIKE ?
                    OR polyclinic.polyclinicName LIKE ?
                    OR polyclinic.polyclinicStatus LIKE ?
                )
            ";
            $bindTypes = 'ssss';
            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
        }
    }

    // =========================================================
    // HITUNG TOTAL DATA
    // =========================================================
    $sql_count = "
        SELECT COUNT(*) AS total
        FROM polyclinic
        $where
    ";

    $stmt_count = mysqli_prepare($Conn, $sql_count);
    if (!$stmt_count) {
        responsePoliklinikError(
            "Gagal mempersiapkan query jumlah data.",
            $page,
            1,
            0
        );
    }

    if (!empty($bindValues)) {
        mysqli_stmt_bind_param($stmt_count, $bindTypes, ...$bindValues);
    }

    if (!mysqli_stmt_execute($stmt_count)) {
        mysqli_stmt_close($stmt_count);
        responsePoliklinikError(
            "Gagal menghitung jumlah data.",
            $page,
            1,
            0
        );
    }

    $result_count = mysqli_stmt_get_result($stmt_count);
    if (!$result_count) {
        mysqli_stmt_close($stmt_count);
        responsePoliklinikError(
            "Gagal membaca jumlah data.",
            $page,
            1,
            0
        );
    }

    $data_count = mysqli_fetch_assoc($result_count);
    $total_data = (int)($data_count['total'] ?? 0);
    mysqli_stmt_close($stmt_count);

    // =========================================================
    // TOTAL HALAMAN
    // =========================================================
    $total_page = ($total_data > 0)
        ? (int)ceil($total_data / $batas)
        : 1;

    if ($page > $total_page) {
        $page = $total_page;
    }

    $posisi = ($page - 1) * $batas;

    // =========================================================
    // QUERY DATA
    // =========================================================
    $OrderBySql = "polyclinic.$OrderBy";

    $sql = "
        SELECT
            polyclinicId,
            satuSehatCode,
            polyclinicCode,
            polyclinicName,
            polyclinicStatus
        FROM polyclinic
        $where
        ORDER BY $OrderBySql $ShortBy
        LIMIT ?, ?
    ";

    $stmt = mysqli_prepare($Conn, $sql);
    if (!$stmt) {
        responsePoliklinikError(
            "Gagal mempersiapkan query data.",
            $page,
            $total_page,
            $total_data
        );
    }

    $bindTypesData  = $bindTypes . 'ii';
    $bindValuesData = $bindValues;
    $bindValuesData[] = $posisi;
    $bindValuesData[] = $batas;

    mysqli_stmt_bind_param($stmt, $bindTypesData, ...$bindValuesData);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        responsePoliklinikError(
            "Terjadi kesalahan saat mengambil data.",
            $page,
            $total_page,
            $total_data
        );
    }

    $query = mysqli_stmt_get_result($stmt);
    if (!$query) {
        mysqli_stmt_close($stmt);
        responsePoliklinikError(
            "Gagal membaca hasil data.",
            $page,
            $total_page,
            $total_data
        );
    }

    // =========================================================
    // BANGUN HTML
    // =========================================================
    $html = '';
    $no = $posisi + 1;

    if (mysqli_num_rows($query) < 1) {
        $html = '
            <div class="col-12">
                <div class="alert alert-warning text-center mb-0">
                    <h1 class="bi bi-exclamation-triangle"></h1>
                    <small>
                        <b>Opss!</b><br>
                        Tidak ada data poliklinik yang ditampilkan.<br>
                        Silahkan Tambahkan Data Poliklinik Terlebih Dulu
                    </small>
                </div>
            </div>
        ';
    } else {
        while ($data = mysqli_fetch_assoc($query)) {
            $polyclinicId        = (int)($data['polyclinicId'] ?? 0);
            $satuSehatCodeRaw    = trim((string)($data['satuSehatCode'] ?? ''));
            $satuSehatCodeFull   = htmlspecialchars($satuSehatCodeRaw !== '' ? $satuSehatCodeRaw : '-', ENT_QUOTES, 'UTF-8');
            $polyclinicCode      = htmlspecialchars($data['polyclinicCode'] ?? '-', ENT_QUOTES, 'UTF-8');
            $polyclinicName      = htmlspecialchars($data['polyclinicName'] ?? '-', ENT_QUOTES, 'UTF-8');
            $polyclinicStatusRaw = $data['polyclinicStatus'] ?? '-';
            $polyclinicStatus    = htmlspecialchars($polyclinicStatusRaw !== '' ? $polyclinicStatusRaw : '-', ENT_QUOTES, 'UTF-8');

            $statusBadgeClass = $polyclinicStatusRaw === 'Active'
                ? 'bg-success'
                : ($polyclinicStatusRaw === 'Inactive' ? 'bg-danger' : 'bg-secondary');
            $status_badge = '<span class="badge ' . $statusBadgeClass . ' rounded-pill px-2">' . $polyclinicStatus . '</span>';

            // ID lengkap tetap dikirim melalui data-id, tetapi tampilan dibuat singkat.
            if ($satuSehatCodeRaw !== '') {
                $satuSehatCodeDisplay = strlen($satuSehatCodeRaw) > 18
                    ? substr($satuSehatCodeRaw, 0, 10) . '...' . substr($satuSehatCodeRaw, -5)
                    : $satuSehatCodeRaw;
                $satuSehatCodeDisplay = htmlspecialchars($satuSehatCodeDisplay, ENT_QUOTES, 'UTF-8');
                $locationHtml = '
                    <a href="javascript:void(0);" class="text-decoration-none" title="' . $satuSehatCodeFull . '" data-bs-toggle="modal" data-bs-target="#ModalDetailLocation" data-id="' . $satuSehatCodeFull . '">
                        <code class="text-primary">' . $satuSehatCodeDisplay . '</code>
                    </a>
                ';
            } else {
                $locationHtml = '<span class="text-muted">-</span>';
            }

            $html .= '
                <div class="col-12 col-md-6 col-xl-4 col-xxl-3 mb-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-visible">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.8rem; font-weight: 600;">' . $no . '</span>
                                <div class="flex-grow-1 min-w-0 pe-1">
                                    <a href="javascript:void(0);" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#ModalDetail" data-id="' . $polyclinicId . '">
                                        <h6 class="card-title text-dark fw-semibold mb-0 text-break">' . $polyclinicName . '</h6>
                                    </a>
                                </div>
                                <div class="dropdown flex-shrink-0">
                                    <button type="button" class="p-0 border-0 bg-transparent text-muted fs-5 lh-1" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Opsi Poliklinik" title="Opsi Poliklinik">
                                        <i class="bi bi-three-dots-vertical" aria-hidden="true"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetail" data-id="' . $polyclinicId . '"><i class="bi bi-eye me-2"></i>Detail</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalEdit" data-id="' . $polyclinicId . '"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalHapus" data-id="' . $polyclinicId . '"><i class="bi bi-trash me-2"></i>Hapus</a></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="small">
                                <div class="d-flex align-items-center justify-content-between gap-2 py-1 border-top">
                                    <span class="text-muted flex-shrink-0"><i class="bi bi-upc me-1"></i>Kode</span>
                                    <code class="text-dark text-end text-break">' . $polyclinicCode . '</code>
                                </div>
                                <div class="d-flex align-items-center justify-content-between gap-2 py-1 border-top">
                                    <span class="text-muted flex-shrink-0"><i class="bi bi-geo-alt me-1"></i>Location</span>
                                    <span class="text-end text-break">' . $locationHtml . '</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between gap-2 pt-1 border-top">
                                    <span class="text-muted flex-shrink-0"><i class="bi bi-check-circle me-1"></i>Status</span>
                                    <span class="text-end">' . $status_badge . '</span>
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

    echo json_encode([
        "status"     => "success",
        "html"       => $html,
        "page"       => $page,
        "total_page" => $total_page,
        "total_data" => $total_data
    ], JSON_UNESCAPED_UNICODE);

    exit;
?>

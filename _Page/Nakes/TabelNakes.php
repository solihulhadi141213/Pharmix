<?php
    header('Content-Type: application/json; charset=utf-8');

    // KONEKSI DAN SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // HELPER RESPONSE ERROR
    function responseNakesError(string $message, int $page = 1, int $total_page = 1, int $total_data = 0): void {
        echo json_encode([
            "status"     => "error",
            "html"       => '
                <div class="col-12">
                    <div class="alert alert-danger text-center mb-0">
                        <h1 class="bi bi-exclamation-triangle"></h1>
                        <small>'.htmlspecialchars($message, ENT_QUOTES, 'UTF-8').'</small>
                    </div>
                </div>
            ',
            "page"       => $page,
            "total_page" => $total_page,
            "total_data" => $total_data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // VALIDASI SESI
    if (empty($SessionIdAkses)) {
        responseNakesError("Sesi akses sudah berakhir. Silakan login ulang.");
    }

    // AMBIL PARAMETER FILTER
    $page       = $_POST['page'] ?? 1;
    $batas      = $_POST['batas'] ?? 12;
    $OrderBy    = trim($_POST['OrderBy'] ?? 'medicalPersonelId');
    $ShortBy    = strtoupper(trim($_POST['ShortBy'] ?? 'DESC'));
    $keyword_by = trim($_POST['keyword_by'] ?? '');
    $keyword    = trim($_POST['keyword'] ?? '');

    // VALIDASI PAGE & BATAS
    $page = filter_var($page, FILTER_VALIDATE_INT);
    $batas = filter_var($batas, FILTER_VALIDATE_INT);
    if ($page === false || $page < 1) $page = 1;
    if ($batas === false || $batas < 1) $batas = 12;
    if ($batas > 100) $batas = 100;

    // VALIDASI ORDER BY
    $allowedOrderBy = [
        'medicalPersonelId',
        'medicalPersonelCode',
        'id_practitioner',
        'medicalPersonelCategory',
        'medicalPersonelName',
        'medicalPersonelNik',
        'medicalPersonelStatus'
    ];
    if (!in_array($OrderBy, $allowedOrderBy, true)) {
        $OrderBy = 'medicalPersonelId';
    }

    // VALIDASI SORT
    if (!in_array($ShortBy, ['ASC', 'DESC'], true)) {
        $ShortBy = 'DESC';
    }

    // VALIDASI KEYWORD BY
    $allowedKeywordBy = [
        'medicalPersonelCode',
        'id_practitioner',
        'medicalPersonelCategory',
        'medicalPersonelName',
        'medicalPersonelNik',
        'medicalPersonelStatus'
    ];
    if ($keyword_by !== '' && !in_array($keyword_by, $allowedKeywordBy, true)) {
        $keyword_by = '';
    }

    // BUILD WHERE
    $where      = '';
    $bindTypes  = '';
    $bindValues = [];

    if ($keyword !== '') {
        $keywordLike = '%'.$keyword.'%';

        if ($keyword_by !== '') {
            if (in_array($keyword_by, ['medicalPersonelCategory', 'medicalPersonelStatus'], true)) {
                $where = " WHERE medical_personel.$keyword_by = ? ";
                $bindValues[] = $keyword;
            } else {
                $where = " WHERE medical_personel.$keyword_by LIKE ? ";
                $bindValues[] = $keywordLike;
            }
            $bindTypes = 's';
        } else {
            $where = "
                WHERE (
                    medical_personel.medicalPersonelCode LIKE ?
                    OR medical_personel.id_practitioner LIKE ?
                    OR medical_personel.medicalPersonelCategory LIKE ?
                    OR medical_personel.medicalPersonelName LIKE ?
                    OR medical_personel.medicalPersonelNik LIKE ?
                    OR medical_personel.medicalPersonelStatus LIKE ?
                )
            ";
            $bindTypes  = 'ssssss';
            $bindValues = array_fill(0, 6, $keywordLike);
        }
    }

    // HITUNG TOTAL DATA
    $sql_count = "SELECT COUNT(*) AS total FROM medical_personel $where";
    $stmt_count = mysqli_prepare($Conn, $sql_count);
    if (!$stmt_count) {
        responseNakesError("Gagal mempersiapkan query jumlah data.", $page);
    }

    if (!empty($bindValues)) {
        mysqli_stmt_bind_param($stmt_count, $bindTypes, ...$bindValues);
    }

    if (!mysqli_stmt_execute($stmt_count)) {
        mysqli_stmt_close($stmt_count);
        responseNakesError("Gagal menghitung jumlah data.", $page);
    }

    $result_count = mysqli_stmt_get_result($stmt_count);
    if (!$result_count) {
        mysqli_stmt_close($stmt_count);
        responseNakesError("Gagal membaca jumlah data.", $page);
    }

    $data_count = mysqli_fetch_assoc($result_count);
    $total_data = (int)($data_count['total'] ?? 0);
    mysqli_stmt_close($stmt_count);

    // PAGINATION
    $total_page = ($total_data > 0) ? (int)ceil($total_data / $batas) : 1;
    if ($page > $total_page) $page = $total_page;
    $posisi = ($page - 1) * $batas;

    // QUERY DATA
    $sql = "
        SELECT
            medicalPersonelId,
            medicalPersonelCode,
            id_practitioner,
            medicalPersonelCategory,
            medicalPersonelNik,
            medicalPersonelName,
            medicalPersonelGender,
            medicalPersonelEmail,
            medicalPersonelPhone,
            medicalPersonelStatus
        FROM medical_personel
        $where
        ORDER BY medical_personel.$OrderBy $ShortBy
        LIMIT ?, ?
    ";

    $stmt = mysqli_prepare($Conn, $sql);
    if (!$stmt) {
        responseNakesError("Gagal mempersiapkan query tenaga kesehatan.", $page, $total_page, $total_data);
    }

    $bindTypesData   = $bindTypes.'ii';
    $bindValuesData  = $bindValues;
    $bindValuesData[] = $posisi;
    $bindValuesData[] = $batas;

    mysqli_stmt_bind_param($stmt, $bindTypesData, ...$bindValuesData);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        responseNakesError("Terjadi kesalahan saat mengambil data tenaga kesehatan.", $page, $total_page, $total_data);
    }

    $query = mysqli_stmt_get_result($stmt);
    if (!$query) {
        mysqli_stmt_close($stmt);
        responseNakesError("Gagal membaca data tenaga kesehatan.", $page, $total_page, $total_data);
    }

    // BANGUN HTML
    $html = '';
    $no = $posisi + 1;

    if (mysqli_num_rows($query) < 1) {
        $html = '
            <div class="col-12">
                <div class="alert alert-warning text-center mb-0">
                    <h1 class="bi bi-exclamation-triangle"></h1>
                    <small>
                        <b>Opss!</b><br>
                        Tidak ada data tenaga kesehatan yang ditampilkan.<br>
                        Silahkan tambahkan data tenaga kesehatan terlebih dahulu.
                    </small>
                </div>
            </div>
        ';
    } else {
        while ($data = mysqli_fetch_assoc($query)) {
            // DATA
            $medicalPersonelId       = (int)($data['medicalPersonelId'] ?? 0);
            $medicalPersonelCode     = htmlspecialchars($data['medicalPersonelCode'] ?? '-', ENT_QUOTES, 'UTF-8');
            $medicalPersonelName     = htmlspecialchars($data['medicalPersonelName'] ?? '-', ENT_QUOTES, 'UTF-8');
            $medicalPersonelCategory = htmlspecialchars($data['medicalPersonelCategory'] ?? '-', ENT_QUOTES, 'UTF-8');
            $medicalPersonelGender   = htmlspecialchars($data['medicalPersonelGender'] ?? '-', ENT_QUOTES, 'UTF-8');
            $medicalPersonelStatusRaw = trim((string)($data['medicalPersonelStatus'] ?? ''));
            $medicalPersonelStatus   = htmlspecialchars($medicalPersonelStatusRaw !== '' ? $medicalPersonelStatusRaw : '-', ENT_QUOTES, 'UTF-8');

            // NIK - SENSOR 3 DIGIT TERAKHIR
            $nikRaw = trim((string)($data['medicalPersonelNik'] ?? ''));
            if ($nikRaw !== '') {
                $nikDisplay = strlen($nikRaw) > 3 ? substr($nikRaw, 0, -3).'***' : str_repeat('*', strlen($nikRaw));
            } else {
                $nikDisplay = '-';
            }
            $nikDisplay = htmlspecialchars($nikDisplay, ENT_QUOTES, 'UTF-8');

            // ID PRACTITIONER - TAMPILKAN SINGKAT
            $practitionerRaw = trim((string)($data['id_practitioner'] ?? ''));
            if ($practitionerRaw !== '') {
                $practitionerFull = htmlspecialchars($practitionerRaw, ENT_QUOTES, 'UTF-8');
                $practitionerDisplay = strlen($practitionerRaw) > 18
                    ? substr($practitionerRaw, 0, 10).'...'.substr($practitionerRaw, -5)
                    : $practitionerRaw;
                $practitionerDisplay = htmlspecialchars($practitionerDisplay, ENT_QUOTES, 'UTF-8');
                $practitionerHtml = '<a href="javascript:void(0);" class="text-primary" data-bs-toggle="modal" data-bs-target="#ModalDetailPractitioner" data-id="'.$practitionerFull.'">'.$practitionerDisplay.'</a>';
            } else {
                $practitionerHtml = '<span class="text-muted">-</span>';
            }

            // STATUS
            $statusBadgeClass = $medicalPersonelStatusRaw === 'Active'
                ? 'bg-success'
                : ($medicalPersonelStatusRaw === 'Inactive' ? 'bg-danger' : 'bg-secondary');

            $statusBadge = '<span class="badge '.$statusBadgeClass.' rounded-pill px-2">'.$medicalPersonelStatus.'</span>';

            // GENDER
            if ($medicalPersonelGender === 'Male') {
                $genderHtml = '<span><i class="bi bi-gender-male"></i> Laki-laki</span>';
            } elseif ($medicalPersonelGender === 'Female') {
                $genderHtml = '<span><i class="bi bi-gender-female"></i> Perempuan</span>';
            } else {
                $genderHtml = '<span class="text-muted">-</span>';
            }

            // CARD
            $html .= '
                <div class="col-12 col-md-6 col-xl-4 col-xxl-3 mb-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-visible">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;font-size:.8rem;font-weight:600;">'.$no.'</span>
                                <div class="flex-grow-1 min-w-0 pe-1">
                                    <a href="javascript:void(0);" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#ModalDetail" data-id="'.$medicalPersonelId.'">
                                        <h6 class="card-title text-dark fw-semibold mb-0 text-break">'.$medicalPersonelName.'</h6>
                                    </a>
                                </div>
                                <div class="dropdown flex-shrink-0">
                                    <button type="button" class="p-0 border-0 bg-transparent text-muted fs-5 lh-1" data-bs-toggle="dropdown" aria-expanded="false" title="Opsi">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetail" data-id="'.$medicalPersonelId.'"><i class="bi bi-eye me-2"></i>Detail</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalEdit" data-id="'.$medicalPersonelId.'"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalAksesNakes" data-id="'.$medicalPersonelId.'"><i class="bi bi-key me-2"></i>Akses</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalHapus" data-id="'.$medicalPersonelId.'"><i class="bi bi-trash me-2"></i>Hapus</a></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="small">
                                <div class="d-flex align-items-center justify-content-between gap-2 py-1 border-top">
                                    <span class="text-muted flex-shrink-0"><i class="bi bi-tags me-1"></i>Kategori</span>
                                    <code class="text-dark text-end text-break">'.$medicalPersonelCategory.'</code>
                                </div>
                                <div class="d-flex align-items-center justify-content-between gap-2 py-1 border-top">
                                    <span class="text-muted flex-shrink-0"><i class="bi bi-upc me-1"></i>Kode</span>
                                    <code class="text-dark text-end text-break">'.$medicalPersonelCode.'</code>
                                </div>
                                <div class="d-flex align-items-center justify-content-between gap-2 py-1 border-top">
                                    <span class="text-muted flex-shrink-0"><i class="bi bi-person-badge me-1"></i>Practitioner</span>
                                    <span class="text-end text-break">'.$practitionerHtml.'</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between gap-2 py-1 border-top">
                                    <span class="text-muted flex-shrink-0"><i class="bi bi-credit-card-2-front me-1"></i>NIK</span>
                                    <span class="text-end">'.$nikDisplay.'</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between gap-2 py-1 border-top">
                                    <span class="text-muted flex-shrink-0"><i class="bi bi-person me-1"></i>Gender</span>
                                    <span class="text-end">'.$genderHtml.'</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between gap-2 pt-1 border-top">
                                    <span class="text-muted flex-shrink-0"><i class="bi bi-check-circle me-1"></i>Status</span>
                                    <span class="text-end">'.$statusBadge.'</span>
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

    // RESPONSE
    echo json_encode([
        "status"     => "success",
        "html"       => $html,
        "page"       => $page,
        "total_page" => $total_page,
        "total_data" => $total_data
    ], JSON_UNESCAPED_UNICODE);
    exit;
?>
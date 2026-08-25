<?php
// =========================================================
// KONFIGURASI & KONEKSI
// =========================================================
date_default_timezone_set('Asia/Jakarta');

include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

header('Content-Type: application/json; charset=utf-8');

// =========================================================
// FUNGSI RESPONSE ERROR
// =========================================================
function responseError($message, $detail = '') {
    $response_message = $message;
    if (!empty($detail)) {
        $response_message .= ' Pesan: ' . $detail;
    }

    echo json_encode([
        'status'  => 'error',
        'message' => $response_message,
        'html'    => '
            <tr>
                <td colspan="7" class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle"></i><br>
                    <small>' . htmlspecialchars($response_message, ENT_QUOTES, 'UTF-8') . '</small>
                </td>
            </tr>
        '
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// =========================================================
// VALIDASI SESSION & METHOD
// =========================================================
if (empty($SessionIdAkses)) {
    responseError('Sesi akses sudah berakhir.', 'Silakan login kembali.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responseError('Metode request tidak valid.', 'Request harus menggunakan POST.');
}

// =========================================================
// AMBIL & SANITASI INPUT
// =========================================================
$page       = isset($_POST['page']) ? (int) $_POST['page'] : 1;
$batas      = isset($_POST['batas']) ? (int) $_POST['batas'] : 10;
$OrderBy    = isset($_POST['OrderBy']) ? trim($_POST['OrderBy']) : '';
$ShortBy    = isset($_POST['ShortBy']) ? strtoupper(trim($_POST['ShortBy'])) : 'DESC';
$keyword_by = isset($_POST['keyword_by']) ? trim($_POST['keyword_by']) : '';
$keyword    = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';

// Validasi page & batas
if ($page < 1) {
    $page = 1;
}

$allowed_batas = [5, 10, 25, 50, 100, 250, 500];
if (!in_array($batas, $allowed_batas, true)) {
    $batas = 10;
}

// Validasi Order By
$allowed_order = [
    'judul'       => 'd.judul',
    'creat_at'    => 'd.creat_at',
    'author_name' => 'd.author_name',
    'status'      => 'd.status'
];
$order_column = $allowed_order[$OrderBy] ?? 'd.creat_at';

// Validasi Sort
if ($ShortBy !== 'ASC' && $ShortBy !== 'DESC') {
    $ShortBy = 'DESC';
}

// =========================================================
// FILTER QUERY & WHERE
// =========================================================
$where  = [];
$params = [];
$types  = '';

if ($keyword !== '') {
    //------ Filter Tags
    if ($keyword_by === 'tags') {
        $where[] = "
            EXISTS (
                SELECT 1
                FROM dokumentasi_tags dt_filter
                WHERE dt_filter.id_dokumentasi = d.id_dokumentasi
                  AND dt_filter.tags = ?
            )
        ";
        $params[] = $keyword;
        $types   .= 's';
    } 
    //------ Filter Tanggal
    elseif ($keyword_by === 'creat_at') {
        $keyword_tanggal = $keyword;

        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $keyword, $match)) {
            $keyword_tanggal = $match[3] . '-' . $match[2] . '-' . $match[1];
        }

        $tanggal_valid = DateTime::createFromFormat('Y-m-d', $keyword_tanggal);
        if ($tanggal_valid && $tanggal_valid->format('Y-m-d') === $keyword_tanggal) {
            $where[] = "DATE(d.creat_at) = ?";
            $params[] = $keyword_tanggal;
            $types   .= 's';
        } else {
            $where[] = "1 = 0";
        }
    } 
    //------ Filter Text
    elseif (in_array($keyword_by, ['judul', 'author_name', 'status'], true)) {
        $keyword_column = '';
        switch ($keyword_by) {
            case 'judul':
                $keyword_column = 'd.judul';
                break;
            case 'author_name':
                $keyword_column = 'd.author_name';
                break;
            case 'status':
                $keyword_column = 'd.status';
                break;
        }

        if (!empty($keyword_column)) {
            $where[] = "$keyword_column LIKE ?";
            $params[] = '%' . $keyword . '%';
            $types   .= 's';
        }
    }
}

$where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
$debug_mode = false;

// =========================================================
// HITUNG TOTAL DATA & PAGE
// =========================================================
try {
    $sql_count = "SELECT COUNT(DISTINCT d.id_dokumentasi) AS total FROM dokumentasi d $where_sql";
    $stmt_count = $Conn->prepare($sql_count);

    if (!$stmt_count) {
        responseError('Gagal mempersiapkan query jumlah data.', $Conn->error);
    }

    if (count($params) > 0) {
        $stmt_count->bind_param($types, ...$params);
    }

    if (!$stmt_count->execute()) {
        $error = $stmt_count->error;
        $stmt_count->close();
        responseError('Gagal menghitung jumlah dokumentasi.', $error);
    }

    $result_count = $stmt_count->get_result();
    $row_count = $result_count->fetch_assoc();
    $total_data = isset($row_count['total']) ? (int) $row_count['total'] : 0;
    $stmt_count->close();

} catch (Throwable $e) {
    responseError('Terjadi exception saat menghitung data.', $e->getMessage());
}

$total_page = $total_data > 0 ? (int) ceil($total_data / $batas) : 1;
if ($page > $total_page) {
    $page = $total_page;
}
$offset = ($page - 1) * $batas;

// =========================================================
// AMBIL DATA UTAMA
// =========================================================
try {
    $sql = "
        SELECT 
            d.id_dokumentasi,
            d.judul,
            d.deskripsi,
            d.status,
            d.id_akses,
            d.author_name,
            d.creat_at,
            d.update_at,
            GROUP_CONCAT(DISTINCT dt.tags ORDER BY dt.tags ASC SEPARATOR ', ') AS tags
        FROM dokumentasi d
        LEFT JOIN dokumentasi_tags dt ON dt.id_dokumentasi = d.id_dokumentasi
        $where_sql
        GROUP BY 
            d.id_dokumentasi, d.judul, d.deskripsi, d.status, 
            d.id_akses, d.author_name, d.creat_at, d.update_at
        ORDER BY $order_column $ShortBy
        LIMIT ?, ?
    ";

    $stmt = $Conn->prepare($sql);
    if (!$stmt) {
        responseError('Gagal mempersiapkan query data dokumentasi.', $Conn->error);
    }

    $params_data = $params;
    $types_data  = $types . 'ii';
    $params_data[] = $offset;
    $params_data[] = $batas;

    $stmt->bind_param($types_data, ...$params_data);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        responseError('Gagal mengambil data dokumentasi.', $error);
    }

    $result = $stmt->get_result();

} catch (Throwable $e) {
    responseError('Terjadi exception saat mengambil data.', $e->getMessage());
}

// =========================================================
// RENDER HTML
// =========================================================
$html = '';
$no = $offset + 1;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_dokumentasi = (int) ($row['id_dokumentasi'] ?? 0);
        $judul          = htmlspecialchars($row['judul'] ?? '', ENT_QUOTES, 'UTF-8');
        $author_name    = htmlspecialchars($row['author_name'] ?? '-', ENT_QUOTES, 'UTF-8');
        $status         = $row['status'] ?? 'Draft';
        $tags           = $row['tags'] ?? '';

        //------ Format Tanggal
        $tanggal = '-';
        if (!empty($row['creat_at'])) {
            $timestamp = strtotime($row['creat_at']);
            if ($timestamp !== false) {
                $tanggal = date('d/m/Y H:i', $timestamp);
            }
        }

        //------ Format Tags Badge
        $html_tags = '';
        if (!empty($tags)) {
            $tag_array = explode(', ', $tags);
            foreach ($tag_array as $tag) {
                $tag = trim($tag);
                if ($tag === '') {
                    continue;
                }
                $safe_tag = htmlspecialchars($tag, ENT_QUOTES, 'UTF-8');
                $html_tags .= '<span class="badge bg-light text-dark border me-1 mb-1">' . $safe_tag . '</span>';
            }
        } else {
            $html_tags = '<span class="text-muted">-</span>';
        }

        //------ Format Status Badge
        $html_status = ($status === 'Publish')
            ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Publish</span>'
            : '<span class="badge bg-secondary"><i class="bi bi-file-earmark"></i> Draft</span>';

        //------ Render Baris Tabel
        $html .= '
            <tr>
                <td><small class="text-muted">' . $no . '</small></td>
                <td>
                    <a href="javascript:void(0);" class="show_detail" data-id="' . $id_dokumentasi . '">
                        ' . $judul . '
                    </a>
                </td>
                <td>' . $html_tags . '</td>
                <td><small class="text-muted">' . $tanggal . '</small></td>
                <td><small class="text-muted"><i>' . $author_name . '</i></small></td>
                <td>' . $html_status . '</td>
                <td>
                    <button type="button" class="btn btn-sm btn-floating btn-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <li class="dropdown-header text-start"><h6>Option</h6></li>
                        <li>
                            <a class="dropdown-item show_detail" href="javascript:void(0)" data-id="' . $id_dokumentasi . '">
                                <i class="bi bi-info-circle"></i> Detail
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item edit_dokumentasi" href="javascript:void(0)" data-id="' . $id_dokumentasi . '">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item hapus_dokumentasi" href="javascript:void(0)" data-id="' . $id_dokumentasi . '">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        </li>
                    </ul>
                </td>
            </tr>
        ';
        $no++;
    }
} else {
    $html = '
        <tr>
            <td colspan="7" class="text-center py-4">
                <i class="bi bi-inbox fs-3 text-muted"></i><br>
                <small class="text-muted">Tidak ada data dokumentasi.</small>
            </td>
        </tr>
    ';
}

$stmt->close();

// =========================================================
// RESPONSE JSON
// =========================================================
$response = [
    'status'     => 'success',
    'message'    => 'Data berhasil ditampilkan.',
    'html'       => $html,
    'page'       => $page,
    'total_page' => $total_page,
    'total_data' => $total_data
];

if ($debug_mode) {
    $response['debug'] = [
        'keyword_by' => $keyword_by,
        'keyword'    => $keyword,
        'where_sql'  => $where_sql,
        'types'      => $types,
        'params'     => $params,
        'order_by'   => $order_column,
        'sort'       => $ShortBy,
        'offset'     => $offset,
        'batas'      => $batas
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
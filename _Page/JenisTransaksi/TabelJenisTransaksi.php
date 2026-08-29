<?php

// ============================================================
// KONEKSI DAN SESSION
// ============================================================
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

header('Content-Type: application/json; charset=utf-8');

// ============================================================
// INISIALISASI
// ============================================================
$JmlHalaman = 0;
$page       = 1;

// ============================================================
// VALIDASI SESSION
// ============================================================
if (empty($SessionIdAkses)) {

    echo json_encode([
        "status" => "error",
        "html"   => '
            <tr>
                <td colspan="8" class="text-center text-danger">
                    <small>Sesi akses sudah berakhir. Silakan login ulang.</small>
                </td>
            </tr>
        ',
        "page"       => 1,
        "total_page" => 1,
        "total_data" => 0
    ]);

    exit;
}

// ============================================================
// PARAMETER
// ============================================================
$page       = $_POST['page'] ?? 1;
$batas      = $_POST['batas'] ?? 10;
$OrderBy    = $_POST['OrderBy'] ?? 'id_transaksi_jenis';
$ShortBy    = $_POST['ShortBy'] ?? 'ASC';
$keyword_by = $_POST['keyword_by'] ?? '';
$keyword    = trim($_POST['keyword'] ?? '');

// ============================================================
// VALIDASI PAGE DAN BATAS
// ============================================================
$page  = (int)$page;
$batas = (int)$batas;

if ($page <= 0) {
    $page = 1;
}

if ($batas <= 0) {
    $batas = 10;
}

$posisi = ($page - 1) * $batas;

// ============================================================
// VALIDASI ORDER BY
// ============================================================
$allowedOrder = [
    'id_transaksi_jenis',
    'nama',
    'kategori',
    'id_akun_debet',
    'id_akun_kredit',
    'id_utang_piutang'
];

if (!in_array($OrderBy, $allowedOrder, true)) {
    $OrderBy = 'id_transaksi_jenis';
}

// ============================================================
// VALIDASI SORT
// ============================================================
$ShortBy = strtoupper($ShortBy);

if (!in_array($ShortBy, ['ASC', 'DESC'], true)) {
    $ShortBy = 'ASC';
}

// ============================================================
// VALIDASI FILTER
// ============================================================
$allowedKeywordBy = [
    'nama',
    'kategori',
    'id_akun_debet',
    'id_akun_kredit',
    'id_utang_piutang'
];

if (!empty($keyword_by) && !in_array($keyword_by, $allowedKeywordBy, true)) {
    $keyword_by = '';
}

// ============================================================
// BUILD FILTER QUERY
// ============================================================
$where      = "";
$bindTypes  = "";
$bindValues = [];

if ($keyword !== '') {

    $keywordLike = "%" . $keyword . "%";

    if ($keyword_by !== '') {

        $where .= " WHERE s.$keyword_by LIKE ? ";

        $bindTypes .= "s";
        $bindValues[] = $keywordLike;

    } else {

        $where .= "
            WHERE (
                s.nama LIKE ?
                OR s.kategori LIKE ?
                OR CAST(s.id_akun_debet AS CHAR) LIKE ?
                OR CAST(s.id_akun_kredit AS CHAR) LIKE ?
                OR CAST(s.id_utang_piutang AS CHAR) LIKE ?
                OR ad.nama LIKE ?
                OR ak.nama LIKE ?
            )
        ";

        $bindTypes .= "sssssss";

        $bindValues[] = $keywordLike;
        $bindValues[] = $keywordLike;
        $bindValues[] = $keywordLike;
        $bindValues[] = $keywordLike;
        $bindValues[] = $keywordLike;
        $bindValues[] = $keywordLike;
        $bindValues[] = $keywordLike;
    }
}

// ============================================================
// TOTAL DATA
// ============================================================
$sql_count = "
    SELECT COUNT(*) AS total
    FROM transaksi_jenis AS s

    LEFT JOIN akun_perkiraan AS ad
        ON ad.id_perkiraan = s.id_akun_debet

    LEFT JOIN akun_perkiraan AS ak
        ON ak.id_perkiraan = s.id_akun_kredit
    
    LEFT JOIN akun_perkiraan AS au
        ON au.id_perkiraan = s.id_utang_piutang

    $where
";

$stmt_count = $Conn->prepare($sql_count);

if (!$stmt_count) {

    echo json_encode([
        "status" => "error",
        "html"   => '
            <tr>
                <td colspan="8" class="text-center text-danger">
                    <small>Gagal mempersiapkan query count.</small>
                </td>
            </tr>
        ',
        "page"       => $page,
        "total_page" => 1,
        "total_data" => 0
    ]);

    exit;
}

// ============================================================
// BIND PARAMETER COUNT
// ============================================================
if (!empty($bindValues)) {
    $stmt_count->bind_param(
        $bindTypes,
        ...$bindValues
    );
}

// ============================================================
// EXECUTE COUNT
// ============================================================
if (!$stmt_count->execute()) {

    $stmt_count->close();

    echo json_encode([
        "status" => "error",
        "html"   => '
            <tr>
                <td colspan="8" class="text-center text-danger">
                    <small>Gagal menghitung data.</small>
                </td>
            </tr>
        ',
        "page"       => $page,
        "total_page" => 1,
        "total_data" => 0
    ]);

    exit;
}

$result_count = $stmt_count->get_result();
$data_count   = $result_count->fetch_assoc();

$total_data = (int)($data_count['total'] ?? 0);

$stmt_count->close();

// ============================================================
// TOTAL PAGE
// ============================================================
$total_page = ($total_data > 0)
    ? (int)ceil($total_data / $batas)
    : 1;

// Jika page melebihi halaman terakhir
if ($page > $total_page) {
    $page = $total_page;
}

// Hitung ulang posisi
$posisi = ($page - 1) * $batas;

// ============================================================
// ORDER BY
// ============================================================
$OrderBySql = "s.$OrderBy";

// ============================================================
// QUERY DATA
// ============================================================
$sql = "
    SELECT
        s.id_transaksi_jenis,
        s.nama,
        s.kategori,

        s.id_akun_debet,
        s.id_akun_kredit,
        s.id_utang_piutang,

        ad.kode AS kode_akun_debet,
        ad.nama AS nama_akun_debet,

        ak.kode AS kode_akun_kredit,
        ak.nama AS nama_akun_kredit,

        au.kode AS kode_akun_utang_piutang,
        au.nama AS nama_akun_utang_piutang,

        COALESCE(sb.jumlah_transaksi, 0) AS jumlah_transaksi

    FROM transaksi_jenis AS s

    LEFT JOIN akun_perkiraan AS ad
        ON ad.id_perkiraan = s.id_akun_debet

    LEFT JOIN akun_perkiraan AS ak
        ON ak.id_perkiraan = s.id_akun_kredit

    LEFT JOIN akun_perkiraan AS au
        ON au.id_perkiraan = s.id_utang_piutang

    LEFT JOIN (
        SELECT
            id_transaksi_jenis,
            COALESCE(SUM(jumlah), 0) AS jumlah_transaksi
        FROM transaksi
        GROUP BY id_transaksi_jenis
    ) AS sb
        ON sb.id_transaksi_jenis = s.id_transaksi_jenis

    $where

    ORDER BY $OrderBySql $ShortBy

    LIMIT ?, ?
";

$stmt = $Conn->prepare($sql);

if (!$stmt) {

    echo json_encode([
        "status" => "error",
        "html"   => '
            <tr>
                <td colspan="8" class="text-center text-danger">
                    <small>Gagal mempersiapkan query data.</small>
                </td>
            </tr>
        ',
        "page"       => $page,
        "total_page" => $total_page,
        "total_data" => $total_data
    ]);

    exit;
}

// ============================================================
// BIND PARAMETER DATA
// ============================================================
$bindTypesData  = $bindTypes . "ii";
$bindValuesData = $bindValues;

$bindValuesData[] = $posisi;
$bindValuesData[] = $batas;

$stmt->bind_param(
    $bindTypesData,
    ...$bindValuesData
);

// ============================================================
// EXECUTE QUERY
// ============================================================
if (!$stmt->execute()) {

    $stmt->close();

    echo json_encode([
        "status" => "error",
        "html"   => '
            <tr>
                <td colspan="8" class="text-center text-danger">
                    <small>Terjadi kesalahan saat mengambil data.</small>
                </td>
            </tr>
        ',
        "page"       => $page,
        "total_page" => $total_page,
        "total_data" => $total_data
    ]);

    exit;
}

// ============================================================
// GET RESULT
// ============================================================
$query = $stmt->get_result();

// ============================================================
// BUILD HTML
// ============================================================
$html = '';
$no   = 1 + $posisi;

if ($query->num_rows === 0) {

    $html .= '
        <tr>
            <td colspan="8" class="text-center text-danger">
                <small>Tidak ada data yang ditampilkan.</small>
            </td>
        </tr>
    ';

} else {

    while ($data = $query->fetch_assoc()) {

        // ----------------------------------------------------
        // DATA TRANSAKSI JENIS
        // ----------------------------------------------------
        $id_transaksi_jenis = (int)$data['id_transaksi_jenis'];

        $nama = htmlspecialchars(
            $data['nama'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        $kategori = htmlspecialchars(
            $data['kategori'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        // ----------------------------------------------------
        // AKUN DEBET
        // ----------------------------------------------------
        $kode_akun_debet = htmlspecialchars(
            $data['kode_akun_debet'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        $nama_akun_debet = htmlspecialchars(
            $data['nama_akun_debet'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        // ----------------------------------------------------
        // AKUN KREDIT
        // ----------------------------------------------------
        $kode_akun_kredit = htmlspecialchars(
            $data['kode_akun_kredit'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        $nama_akun_kredit = htmlspecialchars(
            $data['nama_akun_kredit'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        // ----------------------------------------------------
        // AKUN UTANG PIUTANG
        // ----------------------------------------------------
        $kode_akun_utang_piutang = htmlspecialchars(
            $data['kode_akun_utang_piutang'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        $nama_akun_utang_piutang = htmlspecialchars(
            $data['nama_akun_utang_piutang'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        // ----------------------------------------------------
        // JUMLAH TRANSAKSI
        // ----------------------------------------------------
        $jumlah_transaksi = (int)$data['jumlah_transaksi'];

        $jumlah_transaksi_rp = "Rp " . number_format(
            $jumlah_transaksi,
            0,
            ',',
            '.'
        );

        // ----------------------------------------------------
        // TAMPILAN AKUN
        // ----------------------------------------------------
        if ($nama_akun_debet !== '') {

            $akun_debet_html = $kode_akun_debet !== ''
                ? $nama_akun_debet
                : $nama_akun_debet;

        } else {

            $akun_debet_html = '<span class="text-muted">-</span>';
        }

        if ($nama_akun_kredit !== '') {

            $akun_kredit_html = $kode_akun_kredit !== ''
                ? $nama_akun_kredit
                : $nama_akun_kredit;

        } else {

            $akun_kredit_html = '<span class="text-muted">-</span>';
        }

        if ($nama_akun_utang_piutang !== '') {

            $akun_utang_piutang_html = $kode_akun_utang_piutang !== ''
                ? $nama_akun_utang_piutang
                : $nama_akun_utang_piutang;

        } else {

            $akun_utang_piutang_html = '<span class="text-muted">-</span>';
        }

        // Routing Kategori
        if($kategori=="Pengeluaran"){
            $label_kategori = '<span class="badge badge-danger">'.$kategori.'</span>';
        }else{
            $label_kategori = '<span class="badge badge-success">'.$kategori.'</span>';
        }

        // ----------------------------------------------------
        // HTML ROW
        // ----------------------------------------------------
        $html .= '
            <tr>

                <td>
                    <small class="text-muted">
                        ' . $no . '
                    </small>
                </td>

                <td>
                    <a
                        class="modal_detail_sesi"
                        href="javascript:void(0)"
                        data-bs-toggle="modal" data-bs-target="#ModalDetail" data-id="' . $id_transaksi_jenis . '"
                    >
                        <small>
                            ' . $nama . '
                        </small>
                    </a>
                </td>

                <td>
                    ' . $label_kategori . '
                </td>

                <td>
                    <small class="text-muted">
                        ' . $akun_debet_html . '
                    </small>
                </td>

                <td>
                    <small class="text-muted">
                        ' . $akun_kredit_html . '
                    </small>
                </td>

                <td>
                    <small class="text-muted">
                        ' . $akun_utang_piutang_html . '
                    </small>
                </td>

                <td>
                    <small class="text-muted">
                        ' . $jumlah_transaksi_rp . '
                    </small>
                </td>

                <td>

                    <button type="button" class="btn btn-sm btn-floating btn-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">

                        <li class="dropdown-header text-start">
                            <h6>Option</h6>
                        </li>

                        <li>
                            <a class="dropdown-item" href="javascript:void(0)"  data-bs-toggle="modal" data-bs-target="#ModalDetail" data-id="' . $id_transaksi_jenis . '">
                                <i class="bi bi-info-circle"></i> Detail
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalEdit" data-id="' . $id_transaksi_jenis . '">
                                <i class="bi bi-pencil"></i>
                                Edit
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalHapus" data-id="' . $id_transaksi_jenis . '">
                                <i class="bi bi-trash"></i>
                                Hapus
                            </a>
                        </li>

                    </ul>

                </td>

            </tr>
        ';

        $no++;
    }
}

// ============================================================
// CLOSE STATEMENT
// ============================================================
$stmt->close();

// ============================================================
// RESPONSE JSON
// ============================================================
echo json_encode([
    "status"     => "success",
    "html"       => $html,
    "page"       => $page,
    "total_page" => $total_page,
    "total_data" => $total_data
], JSON_UNESCAPED_UNICODE);

?>
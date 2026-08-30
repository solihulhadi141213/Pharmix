<?php
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');

function jurnalResponse($status, $html, $page = 1, $totalPage = 1)
{
    echo json_encode([
        'status' => $status,
        'html' => $html,
        'page' => (int) $page,
        'total_page' => (int) $totalPage
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($SessionIdAkses)) {
    jurnalResponse('error', '<tr><td colspan="8" class="text-center"><span class="text-danger">Sesi Akses Sudah Berakhir! Silahkan Login Ulang</span></td></tr>');
}

$batas = filter_input(INPUT_POST, 'batas', FILTER_VALIDATE_INT);
$batas = ($batas !== false && $batas !== null && $batas > 0 && $batas <= 100) ? $batas : 10;
$page = filter_input(INPUT_POST, 'page', FILTER_VALIDATE_INT);
$page = ($page !== false && $page !== null && $page > 0) ? $page : 1;
$keyword = trim((string) ($_POST['keyword'] ?? ''));
$keywordBy = trim((string) ($_POST['KeywordBy'] ?? $_POST['keyword_by'] ?? ''));
$shortBy = strtoupper(trim((string) ($_POST['ShortBy'] ?? 'DESC')));
$shortBy = in_array($shortBy, ['ASC', 'DESC'], true) ? $shortBy : 'DESC';

$referenceSql = "COALESCE(NULLIF(j.id_transaksi, ''), NULLIF(j.id_transaksi_jual_beli, ''), NULLIF(j.id_transaksi_pembayaran, ''), '')";
$filterColumns = [
    'uuid' => 'j.uuid',
    'id_transaksi' => 'j.id_transaksi',
    'id_transaksi_jual_beli' => 'j.id_transaksi_jual_beli',
    'id_transaksi_pembayaran' => 'j.id_transaksi_pembayaran',
    'tanggal' => 'j.tanggal',
    'kategori' => 'j.kategori',
    'referensi' => $referenceSql
];
$orderColumns = [
    'uuid' => 'j.uuid',
    'id_transaksi' => 'j.id_transaksi',
    'id_transaksi_jual_beli' => 'j.id_transaksi_jual_beli',
    'id_transaksi_pembayaran' => 'j.id_transaksi_pembayaran',
    'tanggal' => 'j.tanggal',
    'kategori' => 'j.kategori',
    'referensi' => $referenceSql
];
$orderBy = trim((string) ($_POST['OrderBy'] ?? 'tanggal'));
$orderSql = $orderColumns[$orderBy] ?? 'j.tanggal';

$fromSql = "
    FROM jurnal j
    LEFT JOIN transaksi t ON t.id_transaksi = j.id_transaksi
    LEFT JOIN transaksi_jual_beli tjb ON tjb.id_transaksi_jual_beli = j.id_transaksi_jual_beli
    LEFT JOIN transaksi_pembayaran tp ON tp.id_transaksi_pembayaran = j.id_transaksi_pembayaran
";
$whereSql = '';
$params = [];
$types = '';
if ($keyword !== '') {
    $searchColumns = ($keywordBy !== '' && isset($filterColumns[$keywordBy]))
        ? [$filterColumns[$keywordBy]]
        : array_values($filterColumns);
    $whereSql = ' WHERE ' . implode(' OR ', array_map(function ($column) {
        return "$column LIKE CONCAT('%', ?, '%')";
    }, $searchColumns));
    foreach ($searchColumns as $unused) {
        $params[] = $keyword;
        $types .= 's';
    }
}

$countStmt = mysqli_prepare($Conn, "SELECT COUNT(DISTINCT j.uuid) AS total $fromSql $whereSql");
if (!$countStmt) {
    jurnalResponse('error', '<tr><td colspan="8" class="text-center"><span class="text-danger">Gagal menyiapkan query jurnal</span></td></tr>');
}
if ($types !== '') {
    mysqli_stmt_bind_param($countStmt, $types, ...$params);
}
mysqli_stmt_execute($countStmt);
$totalData = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'];
mysqli_stmt_close($countStmt);

$totalPage = max(1, (int) ceil($totalData / $batas));
$page = min($page, $totalPage);
$offset = ($page - 1) * $batas;
if ($totalData === 0) {
    jurnalResponse('error', '<tr><td colspan="8" class="text-center"><span class="text-danger">Tidak ada data yang ditampilkan</span></td></tr>');
}

$groupSql = "
    SELECT j.uuid, MIN(j.kategori) AS kategori, MIN(j.tanggal) AS tanggal,
        MIN(j.id_transaksi) AS id_transaksi,
        MIN(j.id_transaksi_jual_beli) AS id_transaksi_jual_beli,
        MIN(j.id_transaksi_pembayaran) AS id_transaksi_pembayaran,
        COUNT(*) AS jumlah_row
    $fromSql $whereSql
    GROUP BY j.uuid
    ORDER BY $orderSql $shortBy
    LIMIT ?, ?
";
$groupStmt = mysqli_prepare($Conn, $groupSql);
if (!$groupStmt) {
    jurnalResponse('error', '<tr><td colspan="8" class="text-center"><span class="text-danger">Gagal menyiapkan data jurnal</span></td></tr>');
}
$groupParams = $params;
$groupParams[] = $offset;
$groupParams[] = $batas;
mysqli_stmt_bind_param($groupStmt, $types . 'ii', ...$groupParams);
mysqli_stmt_execute($groupStmt);
$groupResult = mysqli_stmt_get_result($groupStmt);

$detailStmt = mysqli_prepare($Conn, "SELECT id_jurnal, kode_perkiraan, nama_perkiraan, d_k, nilai FROM jurnal WHERE uuid = ? ORDER BY d_k ASC, id_jurnal ASC");
$html = '';
$no = $offset + 1;
while ($group = mysqli_fetch_assoc($groupResult)) {
    $uuid = $group['uuid'];
    $referensi = $group['id_transaksi'] ?: ($group['id_transaksi_jual_beli'] ?: ($group['id_transaksi_pembayaran'] ?: '-'));
    $referensi = htmlspecialchars($referensi, ENT_QUOTES, 'UTF-8');
    $kategori = htmlspecialchars($group['kategori'], ENT_QUOTES, 'UTF-8');
    $tanggal = htmlspecialchars($group['tanggal'], ENT_QUOTES, 'UTF-8');
    $jumlahRow = (int) $group['jumlah_row'];

    mysqli_stmt_bind_param($detailStmt, 's', $uuid);
    mysqli_stmt_execute($detailStmt);
    $detailResult = mysqli_stmt_get_result($detailStmt);
    $first = true;
    while ($detail = mysqli_fetch_assoc($detailResult)) {
        $html .= '<tr>';
        if ($first) {
            $html .= '<td rowspan="'.$jumlahRow.'">'.$no.'</td>';
            $html .= '<td rowspan="'.$jumlahRow.'">'.$referensi.'</td>';
            $html .= '<td rowspan="'.$jumlahRow.'">'.$kategori.'</td>';
            $html .= '<td rowspan="'.$jumlahRow.'">'.$tanggal.'</td>';
        }
        $kode = htmlspecialchars($detail['kode_perkiraan'], ENT_QUOTES, 'UTF-8');
        $nama = htmlspecialchars($detail['nama_perkiraan'], ENT_QUOTES, 'UTF-8');
        $nilai = number_format((int) $detail['nilai'], 0, ',', '.');
        $debet = $detail['d_k'] === 'D' ? $nilai : '-';
        $kredit = $detail['d_k'] === 'K' ? $nilai : '-';
        $html .= '<td>'.$kode.'</td><td>'.$nama.'</td><td>'.$debet.'</td><td>'.$kredit.'</td></tr>';
        $first = false;
    }
    $no++;
}
mysqli_stmt_close($detailStmt);
mysqli_stmt_close($groupStmt);

jurnalResponse('success', $html, $page, $totalPage);

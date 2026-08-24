<?php

date_default_timezone_set('Asia/Jakarta');

include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/SettingGeneral.php";
include "../../_Config/Session.php";

header('Content-Type: application/json; charset=utf-8');


// =====================================================
// RESPONSE ERROR
// =====================================================
function responseError($message)
{
    echo json_encode([
        'status'  => 'error',
        'message' => $message,
        'html'    => '
            <div class="alert alert-danger mb-0">
                <small>' .
                htmlspecialchars(
                    $message,
                    ENT_QUOTES,
                    'UTF-8'
                ) .
                '</small>
            </div>
        '
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// =====================================================
// VALIDASI SESI
// =====================================================
if (empty($SessionIdAkses)) {

    responseError(
        'Sesi akses sudah berakhir. Silakan login kembali.'
    );
}


// =====================================================
// VALIDASI METHOD
// =====================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    responseError(
        'Metode request tidak valid.'
    );
}


// =====================================================
// VALIDASI ID JURNAL
// =====================================================
$id_jurnal = trim(
    $_POST['id_jurnal'] ?? ''
);

if (
    $id_jurnal === '' ||
    !ctype_digit($id_jurnal)
) {

    responseError(
        'ID jurnal tidak valid.'
    );
}

$id_jurnal = (int)$id_jurnal;

if ($id_jurnal <= 0) {

    responseError(
        'ID jurnal tidak valid.'
    );
}


// =====================================================
// AMBIL DATA JURNAL
// =====================================================
$sql = "
    SELECT
        id_jurnal,
        kategori,
        uuid,
        id_transaksi,
        tanggal,
        kode_perkiraan,
        nama_perkiraan,
        d_k,
        nilai
    FROM jurnal
    WHERE id_jurnal = ?
    LIMIT 1
";

$stmt = mysqli_prepare(
    $Conn,
    $sql
);

if (!$stmt) {

    responseError(
        'Gagal mempersiapkan query jurnal.'
    );
}

mysqli_stmt_bind_param(
    $stmt,
    'i',
    $id_jurnal
);

if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    responseError(
        'Gagal mengambil data jurnal.'
    );
}

$result = mysqli_stmt_get_result($stmt);

if (
    !$result ||
    mysqli_num_rows($result) === 0
) {

    mysqli_stmt_close($stmt);

    responseError(
        'Data jurnal tidak ditemukan.'
    );
}

$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// =====================================================
// DATA JURNAL
// =====================================================
$kode_perkiraan = $data['kode_perkiraan'] ?? '';
$nama_perkiraan = $data['nama_perkiraan'] ?? '';
$d_k            = $data['d_k'] ?? '';
$nilai          = (int)($data['nilai'] ?? 0);


// =====================================================
// FORMAT NILAI
// =====================================================
$nilai_format = number_format(
    $nilai,
    0,
    ',',
    '.'
);


// =====================================================
// ESCAPE HTML
// =====================================================
$kode_perkiraan_html = htmlspecialchars(
    $kode_perkiraan,
    ENT_QUOTES,
    'UTF-8'
);

$nama_perkiraan_html = htmlspecialchars(
    $nama_perkiraan,
    ENT_QUOTES,
    'UTF-8'
);

$d_k_html = htmlspecialchars(
    $d_k,
    ENT_QUOTES,
    'UTF-8'
);


// =====================================================
// FORM EDIT
// =====================================================
$html = '

    <input
        type="hidden"
        name="id_jurnal"
        value="' . $id_jurnal . '"
    >

    <div class="row mb-3">

        <div class="col-md-12">

            <label for="kode_perkiraan_edit">
                Akun Perkiraan
            </label>

            <select
                name="kode_perkiraan"
                id="kode_perkiraan_edit"
                class="form-select"
                style="width:100%;"
                required
            >

                <option
                    value="' . $kode_perkiraan_html . '"
                    selected
                >
                    ' . $nama_perkiraan_html . '
                </option>

            </select>

        </div>

    </div>


    <div class="row mb-3">

        <div class="col-md-12">

            <label for="d_k_edit">
                Posisi (D/K)
            </label>

            <select
                name="d_k"
                id="d_k_edit"
                class="form-select"
                required
            >

                <option value="">
                    Pilih
                </option>

                <option
                    value="D"
                    ' . ($d_k === 'D' ? 'selected' : '') . '
                >
                    Debet
                </option>

                <option
                    value="K"
                    ' . ($d_k === 'K' ? 'selected' : '') . '
                >
                    Kredit
                </option>

            </select>

        </div>

    </div>


    <div class="row mb-3">

        <div class="col-md-12">

            <label for="nilai_edit">
                Nilai
            </label>

            <input
                type="text"
                name="nilai"
                id="nilai_edit"
                class="form-control"
                inputmode="numeric"
                autocomplete="off"
                value="' . $nilai_format . '"
                required
            >

        </div>

    </div>


    <div class="row mb-2">

        <div class="col-md-12 text-center">

            <small class="text-muted">
                Pastikan data jurnal yang Anda input sudah benar.
            </small>

        </div>

    </div>
';


// =====================================================
// RESPONSE
// =====================================================
echo json_encode([

    'status'  => 'success',

    'message' =>
        'Form edit jurnal berhasil dimuat.',

    'html'    => $html

], JSON_UNESCAPED_UNICODE);

exit;
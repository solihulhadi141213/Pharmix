<?php
// ============================================================
// KONFIGURASI
// ============================================================
date_default_timezone_set('Asia/Jakarta');

include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

header('Content-Type: application/json; charset=utf-8');


// ============================================================
// FUNCTION RESPONSE JSON
// ============================================================
function responseJson(string $status, string $message, array $data = []): void
{
    echo json_encode(
        array_merge(
            [
                'status'  => $status,
                'message' => $message
            ],
            $data
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


// ============================================================
// VALIDASI SESSION
// ============================================================
if (empty($SessionIdAkses)) {
    responseJson(
        'error',
        'Sesi akses sudah berakhir. Silakan login ulang.'
    );
}


// ============================================================
// VALIDASI METHOD
// ============================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responseJson(
        'error',
        'Metode request tidak valid.'
    );
}


// ============================================================
// VALIDASI ID KUNJUNGAN
// ============================================================
$id_kunjungan = trim($_POST['id_kunjungan'] ?? '');

if ($id_kunjungan === '') {
    responseJson(
        'error',
        'ID Kunjungan tidak boleh kosong.'
    );
}

if (!ctype_digit($id_kunjungan) || (int) $id_kunjungan <= 0) {
    responseJson(
        'error',
        'ID Kunjungan tidak valid.'
    );
}

$id_kunjungan = (int) $id_kunjungan;


// ============================================================
// MULAI TRANSAKSI
// ============================================================
$Conn->begin_transaction();

$stmt = null;

try {

    // ========================================================
    // CEK DATA KUNJUNGAN
    // ========================================================
    $stmt = $Conn->prepare("
        SELECT id_kunjungan
        FROM kunjungan
        WHERE id_kunjungan = ?
        LIMIT 1
    ");

    if (!$stmt) {
        throw new Exception(
            'Gagal menyiapkan pengecekan data: ' . $Conn->error
        );
    }

    $stmt->bind_param(
        'i',
        $id_kunjungan
    );

    if (!$stmt->execute()) {
        throw new Exception(
            'Gagal memeriksa data kunjungan: ' . $stmt->error
        );
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {

        $stmt->close();
        $stmt = null;

        throw new Exception(
            'Data kunjungan tidak ditemukan atau sudah dihapus.'
        );
    }

    $stmt->close();
    $stmt = null;


    // ========================================================
    // HAPUS DATA KUNJUNGAN
    // ========================================================
    $stmt = $Conn->prepare("
        DELETE FROM kunjungan
        WHERE id_kunjungan = ?
    ");

    if (!$stmt) {
        throw new Exception(
            'Gagal menyiapkan proses hapus: ' . $Conn->error
        );
    }

    $stmt->bind_param(
        'i',
        $id_kunjungan
    );

    if (!$stmt->execute()) {
        throw new Exception(
            'Gagal menghapus data kunjungan: ' . $stmt->error
        );
    }

    if ($stmt->affected_rows < 1) {
        throw new Exception(
            'Data kunjungan gagal dihapus.'
        );
    }

    $stmt->close();
    $stmt = null;


    // ========================================================
    // COMMIT
    // ========================================================
    $Conn->commit();


    // ========================================================
    // RESPONSE SUCCESS
    // ========================================================
    responseJson(
        'success',
        'Data kunjungan berhasil dihapus.',
        [
            'id_kunjungan' => $id_kunjungan
        ]
    );


} catch (Throwable $e) {

    // ========================================================
    // CLOSE STATEMENT JIKA MASIH TERBUKA
    // ========================================================
    if ($stmt instanceof mysqli_stmt) {
        $stmt->close();
    }


    // ========================================================
    // ROLLBACK
    // ========================================================
    $Conn->rollback();


    // ========================================================
    // RESPONSE ERROR
    // ========================================================
    responseJson(
        'error',
        $e->getMessage()
    );
}
<?php

// =========================================================
// KONEKSI & KONFIGURASI
// =========================================================
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

date_default_timezone_set('Asia/Jakarta');

header('Content-Type: application/json; charset=utf-8');

// =========================================================
// RESPONSE DEFAULT
// =========================================================
$response = [
    "status"  => false,
    "message" => "Belum ada proses yang dilakukan."
];

// =========================================================
// VALIDASI SESSION
// =========================================================
if (empty($SessionIdAkses)) {

    echo json_encode([
        "status"  => false,
        "message" => "Sesi akses sudah berakhir. Silakan login kembali."
    ]);

    exit;
}

// =========================================================
// VALIDASI METHOD
// =========================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        "status"  => false,
        "message" => "Metode request tidak valid."
    ]);

    exit;
}

// =========================================================
// AMBIL DATA
// =========================================================
$id_dokumentasi = trim(
    $_POST['id_dokumentasi'] ?? ''
);

$judul = trim(
    $_POST['judul_dokumentasi'] ?? ''
);

$deskripsi = trim(
    $_POST['deskripsi_dokumentasi'] ?? ''
);

$tags = $_POST['tags_dokumentasi'] ?? [];

// =========================================================
// VALIDASI ID
// =========================================================
if (
    $id_dokumentasi === '' ||
    !ctype_digit($id_dokumentasi)
) {

    echo json_encode([
        "status"  => false,
        "message" => "ID dokumentasi tidak valid."
    ]);

    exit;
}

$id_dokumentasi = (int) $id_dokumentasi;

// =========================================================
// VALIDASI JUDUL
// =========================================================
if ($judul === '') {

    echo json_encode([
        "status"  => false,
        "message" => "Judul dokumentasi wajib diisi."
    ]);

    exit;
}

// =========================================================
// VALIDASI TAG
// =========================================================
if (!is_array($tags)) {
    $tags = [];
}

// Bersihkan tag
$tags_bersih = [];

foreach ($tags as $tag) {

    $tag = trim($tag);

    if ($tag !== '') {

        $tags_bersih[] = $tag;
    }
}

// Hilangkan duplikasi
$tags_bersih = array_values(
    array_unique($tags_bersih)
);

// Jika tag wajib diisi
if (count($tags_bersih) === 0) {

    echo json_encode([
        "status"  => false,
        "message" => "Minimal satu tag harus dipilih."
    ]);

    exit;
}

// =========================================================
// CEK DATA DOKUMENTASI
// =========================================================
$QryCek = $Conn->prepare("
    SELECT
        id_dokumentasi
    FROM dokumentasi
    WHERE id_dokumentasi = ?
    LIMIT 1
");

if (!$QryCek) {

    echo json_encode([
        "status"  => false,
        "message" => "Gagal menyiapkan query pengecekan dokumentasi. Pesan: " . $Conn->error
    ]);

    exit;
}

$QryCek->bind_param(
    "i",
    $id_dokumentasi
);

if (!$QryCek->execute()) {

    $error = $QryCek->error;

    $QryCek->close();

    echo json_encode([
        "status"  => false,
        "message" => "Gagal memeriksa dokumentasi. Pesan: " . $error
    ]);

    exit;
}

$ResultCek = $QryCek->get_result();

if ($ResultCek->num_rows === 0) {

    $QryCek->close();

    echo json_encode([
        "status"  => false,
        "message" => "Dokumentasi tidak ditemukan."
    ]);

    exit;
}

$QryCek->close();

// =========================================================
// MULAI TRANSACTION
// =========================================================
$Conn->begin_transaction();

try {

    // =====================================================
    // UPDATE DOKUMENTASI
    // =====================================================
    $QryUpdate = $Conn->prepare("
        UPDATE dokumentasi
        SET
            judul = ?,
            deskripsi = ?,
            update_at = NOW()
        WHERE
            id_dokumentasi = ?
        LIMIT 1
    ");

    if (!$QryUpdate) {
        throw new Exception(
            "Gagal menyiapkan query update dokumentasi. Pesan: " .
            $Conn->error
        );
    }

    $QryUpdate->bind_param(
        "ssi",
        $judul,
        $deskripsi,
        $id_dokumentasi
    );

    if (!$QryUpdate->execute()) {

        $error = $QryUpdate->error;

        $QryUpdate->close();

        throw new Exception(
            "Gagal memperbarui dokumentasi. Pesan: " . $error
        );
    }

    $QryUpdate->close();

    // =====================================================
    // HAPUS TAG LAMA
    // =====================================================
    $QryDeleteTag = $Conn->prepare("
        DELETE FROM dokumentasi_tags
        WHERE id_dokumentasi = ?
    ");

    if (!$QryDeleteTag) {

        throw new Exception(
            "Gagal menyiapkan query hapus tag. Pesan: " .
            $Conn->error
        );
    }

    $QryDeleteTag->bind_param(
        "i",
        $id_dokumentasi
    );

    if (!$QryDeleteTag->execute()) {

        $error = $QryDeleteTag->error;

        $QryDeleteTag->close();

        throw new Exception(
            "Gagal menghapus tag lama. Pesan: " . $error
        );
    }

    $QryDeleteTag->close();

    // =====================================================
    // INSERT TAG BARU
    // =====================================================
    $QryInsertTag = $Conn->prepare("
        INSERT INTO dokumentasi_tags
        (
            id_dokumentasi,
            tags
        )
        VALUES
        (
            ?,
            ?
        )
    ");

    if (!$QryInsertTag) {

        throw new Exception(
            "Gagal menyiapkan query insert tag. Pesan: " .
            $Conn->error
        );
    }

    foreach ($tags_bersih as $tag) {

        $QryInsertTag->bind_param(
            "is",
            $id_dokumentasi,
            $tag
        );

        if (!$QryInsertTag->execute()) {

            $error = $QryInsertTag->error;

            $QryInsertTag->close();

            throw new Exception(
                "Gagal menyimpan tag. Pesan: " . $error
            );
        }
    }

    $QryInsertTag->close();

    // =====================================================
    // COMMIT
    // =====================================================
    $Conn->commit();

    echo json_encode([
        "status"  => true,
        "message" => "Dokumentasi berhasil diperbarui.",
        "data"    => [
            "id_dokumentasi" => $id_dokumentasi,
            "judul"          => $judul,
            "deskripsi"      => $deskripsi,
            "jumlah_tags"    => count($tags_bersih)
        ]
    ]);

    exit;

} catch (Throwable $e) {

    // =====================================================
    // ROLLBACK
    // =====================================================
    $Conn->rollback();

    echo json_encode([
        "status"  => false,
        "message" => $e->getMessage()
    ]);

    exit;
}
?>
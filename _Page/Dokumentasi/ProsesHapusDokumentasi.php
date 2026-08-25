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
// AMBIL ID
// =========================================================
$id_dokumentasi = trim(
    $_POST['id_dokumentasi'] ?? ''
);

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
// CEK DOKUMENTASI
// =========================================================
$QryCek = $Conn->prepare("
    SELECT
        id_dokumentasi,
        judul
    FROM dokumentasi
    WHERE id_dokumentasi = ?
    LIMIT 1
");

if (!$QryCek) {

    echo json_encode([
        "status"  => false,
        "message" => "Gagal menyiapkan query pengecekan. Pesan: " . $Conn->error
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

$DataDokumentasi = $ResultCek->fetch_assoc();

$judul = $DataDokumentasi['judul'];

$QryCek->close();

// =========================================================
// MULAI TRANSACTION
// =========================================================
$Conn->begin_transaction();

try {

    // =====================================================
    // HAPUS KONTEN
    // =====================================================
    $QryKonten = $Conn->prepare("
        DELETE FROM dokumentasi_konten
        WHERE id_dokumentasi = ?
    ");

    if (!$QryKonten) {

        throw new Exception(
            "Gagal menyiapkan query hapus konten. Pesan: " .
            $Conn->error
        );
    }

    $QryKonten->bind_param(
        "i",
        $id_dokumentasi
    );

    if (!$QryKonten->execute()) {

        $error = $QryKonten->error;

        $QryKonten->close();

        throw new Exception(
            "Gagal menghapus konten dokumentasi. Pesan: " .
            $error
        );
    }

    $jumlah_konten = $QryKonten->affected_rows;

    $QryKonten->close();

    // =====================================================
    // HAPUS TAG
    // =====================================================
    $QryTags = $Conn->prepare("
        DELETE FROM dokumentasi_tags
        WHERE id_dokumentasi = ?
    ");

    if (!$QryTags) {

        throw new Exception(
            "Gagal menyiapkan query hapus tag. Pesan: " .
            $Conn->error
        );
    }

    $QryTags->bind_param(
        "i",
        $id_dokumentasi
    );

    if (!$QryTags->execute()) {

        $error = $QryTags->error;

        $QryTags->close();

        throw new Exception(
            "Gagal menghapus tag dokumentasi. Pesan: " .
            $error
        );
    }

    $jumlah_tags = $QryTags->affected_rows;

    $QryTags->close();

    // =====================================================
    // HAPUS DOKUMENTASI
    // =====================================================
    $QryDokumentasi = $Conn->prepare("
        DELETE FROM dokumentasi
        WHERE id_dokumentasi = ?
        LIMIT 1
    ");

    if (!$QryDokumentasi) {

        throw new Exception(
            "Gagal menyiapkan query hapus dokumentasi. Pesan: " .
            $Conn->error
        );
    }

    $QryDokumentasi->bind_param(
        "i",
        $id_dokumentasi
    );

    if (!$QryDokumentasi->execute()) {

        $error = $QryDokumentasi->error;

        $QryDokumentasi->close();

        throw new Exception(
            "Gagal menghapus dokumentasi. Pesan: " .
            $error
        );
    }

    if ($QryDokumentasi->affected_rows === 0) {

        $QryDokumentasi->close();

        throw new Exception(
            "Dokumentasi gagal dihapus atau sudah tidak tersedia."
        );
    }

    $QryDokumentasi->close();

    // =====================================================
    // COMMIT
    // =====================================================
    $Conn->commit();

    echo json_encode([
        "status"  => true,
        "message" => "Dokumentasi berhasil dihapus.",
        "data"    => [
            "id_dokumentasi" => $id_dokumentasi,
            "judul"          => $judul,
            "jumlah_konten"  => $jumlah_konten,
            "jumlah_tags"    => $jumlah_tags
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
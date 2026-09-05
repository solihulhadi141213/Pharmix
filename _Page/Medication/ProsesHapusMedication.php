<?php
    // Koneksi, Function Dan Session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Format Response
    header('Content-Type: application/json; charset=utf-8');

    function responseHapusMedication(string $status, string $message): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // Validasi Session
    if (empty($SessionIdAkses)) {
        responseHapusMedication('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    // Validasi Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseHapusMedication('error', 'Metode request tidak valid.');
    }

    // Tangkap ID
    $id_index_medication = (int)($_POST['id_index_medication'] ?? 0);

    if ($id_index_medication < 1) {
        responseHapusMedication('error', 'ID medication tidak valid.');
    }

    // Cek Medication
    $stmt = $Conn->prepare("
        SELECT
            id_index_medication,
            id_medication,
            medication_code,
            medication_name
        FROM medication
        WHERE id_index_medication = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseHapusMedication('error', 'Gagal mempersiapkan data medication.');
    }

    $stmt->bind_param("i", $id_index_medication);

    if (!$stmt->execute()) {
        $stmt->close();
        responseHapusMedication('error', 'Gagal memeriksa data medication.');
    }

    $result = $stmt->get_result();
    $data   = $result->fetch_assoc();
    $stmt->close();

    if (!$data) {
        responseHapusMedication('error', 'Data medication tidak ditemukan.');
    }

    // Informasi
    $id_medication   = trim((string)($data['id_medication'] ?? ''));
    $medication_name = trim((string)($data['medication_name'] ?? ''));

    /*
     * Catatan:
     * id_medication merupakan ID resource SATUSEHAT.
     * DELETE di bawah hanya menghapus master medication lokal.
     * Resource Medication di SATUSEHAT tidak ikut dihapus.
     */

    // Transaction
    $Conn->begin_transaction();

    try {

        $stmt = $Conn->prepare("
            DELETE FROM medication
            WHERE id_index_medication = ?
        ");

        if (!$stmt) {
            throw new Exception('Gagal mempersiapkan proses hapus medication.');
        }

        $stmt->bind_param("i", $id_index_medication);

        if (!$stmt->execute()) {
            throw new Exception('Gagal menghapus medication.');
        }

        if ($stmt->affected_rows < 1) {
            throw new Exception('Tidak ada medication yang berhasil dihapus.');
        }

        $stmt->close();

        $Conn->commit();

        $message = 'Medication "'.$medication_name.'" berhasil dihapus dari database lokal.';

        if ($id_medication !== '') {
            $message .= ' Resource Medication di SATUSEHAT tetap tersedia.';
        }

        responseHapusMedication(
            'success',
            $message
        );

    } catch (Throwable $e) {

        $Conn->rollback();

        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }

        responseHapusMedication(
            'error',
            $e->getMessage()
        );
    }
?>
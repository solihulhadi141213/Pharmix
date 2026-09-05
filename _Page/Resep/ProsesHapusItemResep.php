<?php
    //------------------------------------------
    // Koneksi, Function Dan Session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    //------------------------------------------
    // Format Response
    header('Content-Type: application/json; charset=utf-8');

    //------------------------------------------
    // Default Response
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.'
    ];

    //------------------------------------------
    // Helper Error
    function responseError($message)
    {
        echo json_encode([
            'status'  => 'error',
            'message' => $message
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    //------------------------------------------
    // Validasi Session & Method
    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }

    //------------------------------------------
    // Tangkap MedicationRequestId
    $MedicationRequestId = trim($_POST['MedicationRequestId'] ?? '');

    if ($MedicationRequestId === '') {
        responseError('ID item resep tidak boleh kosong.');
    }

    //------------------------------------------
    // Cek Data Item Resep
    $sql = "
        SELECT
            MedicationRequestId,
            id_medication_request,
            id_medication_request_group
        FROM medication_request
        WHERE MedicationRequestId = ?
        LIMIT 1
    ";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {
        responseError('Gagal mempersiapkan data item resep.');
    }

    $stmt->bind_param("s", $MedicationRequestId);

    if (!$stmt->execute()) {
        $stmt->close();
        responseError('Gagal memeriksa data item resep.');
    }

    $result = $stmt->get_result();
    $data   = $result->fetch_assoc();
    $stmt->close();

    if (!$data) {
        responseError('Data item resep tidak ditemukan.');
    }

    //------------------------------------------
    // Informasi Item
    $id_medication_request     = trim($data['id_medication_request'] ?? '');
    $id_medication_request_group = (int) ($data['id_medication_request_group'] ?? 0);

    //------------------------------------------
    // Tolak Penghapusan Jika Sudah Dikirim Ke SATUSEHAT
    if ($id_medication_request !== '') {
        responseError('Item resep tidak dapat dihapus karena sudah memiliki ID MedicationRequest SATUSEHAT.');
    }

    //------------------------------------------
    // Mulai Transaction
    $Conn->begin_transaction();

    try {
        // Hapus Item Resep
        $sql = "
            DELETE FROM medication_request
            WHERE MedicationRequestId = ?
        ";

        $stmt = $Conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Gagal mempersiapkan proses hapus item resep.');
        }

        $stmt->bind_param("s", $MedicationRequestId);

        if (!$stmt->execute()) {
            throw new Exception('Gagal menghapus item resep.');
        }

        if ($stmt->affected_rows < 1) {
            throw new Exception('Item resep tidak berhasil dihapus.');
        }

        $stmt->close();

        // Commit Transaksi
        $Conn->commit();

        $response = [
            'status'  => 'success',
            'message' => 'Item resep berhasil dihapus.',
            'data'    => [
                'MedicationRequestId'         => $MedicationRequestId,
                'id_medication_request_group' => $id_medication_request_group
            ]
        ];

    } catch (Throwable $e) {
        // Rollback Transaksi
        $Conn->rollback();

        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }

        $response['message'] = $e->getMessage();
    }

    //------------------------------------------
    // Output JSON
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
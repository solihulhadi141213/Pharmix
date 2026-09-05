<?php
    //------------------------------------------
    // Koneksi, Session dan Helper
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    //------------------------------------------
    // Default JSON Response
    header('Content-Type: application/json; charset=utf-8');

    //------------------------------------------
    // Default Response
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.'
    ];

    //------------------------------------------
    // Validasi Session
    if (empty($SessionIdAkses)) {
        $response['message'] = 'Sesi akses sudah berakhir.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Validasi Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Metode request tidak valid.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Tangkap Parameter
    $id_medication_request_group = (int) ($_POST['id_medication_request_group'] ?? 0);

    //------------------------------------------
    // Validasi ID Resep
    if ($id_medication_request_group < 1) {
        $response['message'] = 'ID resep tidak valid.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Cek Data Resep
    $sql = "
        SELECT id_medication_request_group
        FROM medication_request_group
        WHERE id_medication_request_group = ?
        LIMIT 1
    ";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {
        $response['message'] = 'Gagal mempersiapkan data resep.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->bind_param("i", $id_medication_request_group);

    //------------------------------------------
    // Eksekusi Cek Data
    if (!$stmt->execute()) {
        $response['message'] = 'Gagal memeriksa data resep.';
        $stmt->close();

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result = $stmt->get_result();
    $data   = $result->fetch_assoc();

    $stmt->close();

    //------------------------------------------
    // Validasi Data
    if (!$data) {
        $response['message'] = 'Data resep tidak ditemukan.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Mulai Transaction
    $Conn->begin_transaction();

    try {

        //------------------------------------------
        // Hapus Data Resep
        $sql = "
            DELETE FROM medication_request_group
            WHERE id_medication_request_group = ?
        ";

        $stmt = $Conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Gagal mempersiapkan proses hapus resep.');
        }

        $stmt->bind_param("i", $id_medication_request_group);

        //------------------------------------------
        // Eksekusi Hapus
        if (!$stmt->execute()) {
            throw new Exception('Gagal menghapus data resep.');
        }

        //------------------------------------------
        // Pastikan Data Terhapus
        if ($stmt->affected_rows < 1) {
            throw new Exception('Data resep tidak berhasil dihapus.');
        }

        $stmt->close();

        //------------------------------------------
        // Commit
        $Conn->commit();

        //------------------------------------------
        // Response Success
        $response = [
            'status'  => 'success',
            'message' => 'Data resep berhasil dihapus.'
        ];

    } catch (Throwable $e) {

        //------------------------------------------
        // Rollback
        $Conn->rollback();

        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }

        $response['message'] = $e->getMessage();
    }

    //------------------------------------------
    // Response
    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
?>
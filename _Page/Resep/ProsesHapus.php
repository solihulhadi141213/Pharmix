<?php
    include "../../_Config/Connection.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    function hapusResepResponse($status, $message)
    {
        echo json_encode(['status' => $status, 'message' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($SessionIdAkses)) hapusResepResponse('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') hapusResepResponse('error', 'Metode request tidak valid.');

    $idGroup = (int) ($_POST['id_medication_request_group'] ?? 0);
    if ($idGroup <= 0) hapusResepResponse('error', 'ID resep tidak valid.');

    $check = $Conn->prepare("SELECT id_medication_request_group FROM medication_request_group WHERE id_medication_request_group = ? LIMIT 1");
    $check->bind_param('i', $idGroup);
    $check->execute();
    if (!$check->get_result()->fetch_assoc()) {
        $check->close();
        hapusResepResponse('error', 'Data resep tidak ditemukan.');
    }
    $check->close();

    $Conn->begin_transaction();
    try {
        $child = $Conn->prepare("DELETE FROM medication_dispense WHERE id_medication_request_group = ?");
        if (!$child) throw new Exception('Gagal menyiapkan penghapusan detail resep.');
        $child->bind_param('i', $idGroup);
        if (!$child->execute()) throw new Exception('Gagal menghapus detail resep.');
        $child->close();

        $stmt = $Conn->prepare("DELETE FROM medication_request_group WHERE id_medication_request_group = ? LIMIT 1");
        if (!$stmt) throw new Exception('Gagal menyiapkan penghapusan resep.');
        $stmt->bind_param('i', $idGroup);
        if (!$stmt->execute()) throw new Exception('Gagal menghapus resep.');
        $stmt->close();

        $Conn->commit();
        hapusResepResponse('success', 'Resep berhasil dihapus.');
    } catch (Throwable $error) {
        $Conn->rollback();
        hapusResepResponse('error', $error->getMessage());
    }
?>

<?php
    include "../../_Config/Connection.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    function editResepResponse($status, $message)
    {
        echo json_encode(['status' => $status, 'message' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($SessionIdAkses)) editResepResponse('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') editResepResponse('error', 'Metode request tidak valid.');

    $idGroup = (int) ($_POST['id_medication_request_group'] ?? 0);
    $idAnggota = trim((string) ($_POST['id_anggota'] ?? ''));
    $idKunjungan = trim((string) ($_POST['id_kunjungan'] ?? ''));
    $priority = trim((string) ($_POST['priority'] ?? ''));
    $dokterKode = trim((string) ($_POST['dokter_kode'] ?? ''));
    $dokterIhs = trim((string) ($_POST['dokter_ihs'] ?? ''));
    $dokterNama = trim((string) ($_POST['dokter_nama'] ?? ''));
    $reasonCode = trim((string) ($_POST['reason_code'] ?? ''));
    $reasonDisplay = trim((string) ($_POST['reason_display'] ?? ''));
    $reasonSystem = trim((string) ($_POST['reason_system'] ?? ''));
    $apotekerNama = trim((string) ($_POST['apoteker_nama'] ?? ''));
    $apotekerIhs = trim((string) ($_POST['apoteker_id_ihs'] ?? ''));
    $sumberData = trim((string) ($_POST['sumber_data'] ?? ''));
    $status = trim((string) ($_POST['status_resep'] ?? ''));

    if ($idGroup <= 0) editResepResponse('error', 'ID resep tidak valid.');
    if ($priority === '' || !in_array($priority, ['routine', 'urgent', 'asap', 'stat'], true)) editResepResponse('error', 'Priority resep tidak valid.');
    if (($idAnggota !== '' && !ctype_digit($idAnggota)) || ($idKunjungan !== '' && !ctype_digit($idKunjungan))) editResepResponse('error', 'ID pasien atau ID kunjungan tidak valid.');
    if ($dokterKode === '' || $dokterIhs === '' || $dokterNama === '') editResepResponse('error', 'Data dokter wajib diisi.');
    if ($sumberData === '') editResepResponse('error', 'Sumber data wajib diisi.');
    if (!in_array($status, ['Draft', 'Verified', 'Partially', 'Completed', 'Cancelled'], true)) editResepResponse('error', 'Status resep tidak valid.');

    $idAnggotaValue = $idAnggota === '' ? null : (int) $idAnggota;
    $idKunjunganValue = $idKunjungan === '' ? null : (int) $idKunjungan;

    $check = $Conn->prepare("SELECT id_medication_request_group FROM medication_request_group WHERE id_medication_request_group = ? LIMIT 1");
    $check->bind_param('i', $idGroup);
    $check->execute();
    if (!$check->get_result()->fetch_assoc()) {
        $check->close();
        editResepResponse('error', 'Data resep tidak ditemukan.');
    }
    $check->close();

    if ($idAnggotaValue !== null) {
        $check = $Conn->prepare("SELECT id_anggota FROM anggota WHERE id_anggota = ? LIMIT 1");
        $check->bind_param('i', $idAnggotaValue);
        $check->execute();
        if (!$check->get_result()->fetch_assoc()) {
            $check->close();
            editResepResponse('error', 'Pasien tidak ditemukan.');
        }
        $check->close();
    }

    $stmt = $Conn->prepare("UPDATE medication_request_group SET
        id_anggota = ?, id_kunjungan = ?, priority = ?, dokter_kode = ?, dokter_ihs = ?,
        dokter_nama = ?, reason_code = ?, reason_display = ?, reason_system = ?,
        apoteker_nama = ?, apoteker_id_ihs = ?, sumber_data = ?, status_resep = ?
        WHERE id_medication_request_group = ? LIMIT 1");

    if (!$stmt) editResepResponse('error', 'Gagal menyiapkan perubahan resep.');

    $stmt->bind_param('iissssssssssi',
        $idAnggotaValue, $idKunjunganValue, $priority, $dokterKode, $dokterIhs,
        $dokterNama, $reasonCode, $reasonDisplay, $reasonSystem, $apotekerNama,
        $apotekerIhs, $sumberData, $status, $idGroup
    );

    if (!$stmt->execute()) {
        $stmt->close();
        editResepResponse('error', 'Gagal memperbarui resep.');
    }

    $stmt->close();
    editResepResponse('success', 'Resep berhasil diperbarui.');
?>

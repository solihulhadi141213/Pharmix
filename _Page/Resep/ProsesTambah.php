<?php
    include "../../_Config/Connection.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    function tambahResepResponse($status, $message)
    {
        echo json_encode(['status' => $status, 'message' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($SessionIdAkses)) tambahResepResponse('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') tambahResepResponse('error', 'Metode request tidak valid.');

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
    $status = trim((string) ($_POST['status_resep'] ?? 'Draft'));

    if ($priority === '' || !in_array($priority, ['routine', 'urgent', 'asap', 'stat'], true)) tambahResepResponse('error', 'Priority resep tidak valid.');
    if (($idAnggota !== '' && !ctype_digit($idAnggota)) || ($idKunjungan !== '' && !ctype_digit($idKunjungan))) tambahResepResponse('error', 'ID pasien atau ID kunjungan tidak valid.');
    if ($dokterKode === '' || $dokterIhs === '' || $dokterNama === '') tambahResepResponse('error', 'Data dokter wajib diisi.');
    if ($sumberData === '') tambahResepResponse('error', 'Sumber data wajib diisi.');
    if (!in_array($status, ['Draft', 'Verified', 'Partially', 'Completed', 'Cancelled'], true)) tambahResepResponse('error', 'Status resep tidak valid.');

    $idAnggotaValue = $idAnggota === '' ? null : (int) $idAnggota;
    $idKunjunganValue = $idKunjungan === '' ? null : (int) $idKunjungan;

    if ($idAnggotaValue !== null) {
        $check = $Conn->prepare("SELECT id_anggota FROM anggota WHERE id_anggota = ? LIMIT 1");
        $check->bind_param('i', $idAnggotaValue);
        $check->execute();
        if (!$check->get_result()->fetch_assoc()) {
            $check->close();
            tambahResepResponse('error', 'Pasien tidak ditemukan.');
        }
        $check->close();
    }

    $now = date('Y-m-d H:i:s');
    $stmt = $Conn->prepare("INSERT INTO medication_request_group (
        id_anggota, id_kunjungan, priority, datetime_creat, datetime_verified,
        datetime_completed, dokter_kode, dokter_ihs, dokter_nama, reason_code,
        reason_display, reason_system, apoteker_nama, apoteker_id_ihs,
        sumber_data, status_resep
    ) VALUES (?, ?, ?, ?, NULL, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) tambahResepResponse('error', 'Gagal menyiapkan penyimpanan resep.');

    $stmt->bind_param('iissssssssssss',
        $idAnggotaValue, $idKunjunganValue, $priority, $now, $dokterKode,
        $dokterIhs, $dokterNama, $reasonCode, $reasonDisplay, $reasonSystem,
        $apotekerNama, $apotekerIhs, $sumberData, $status
    );

    if (!$stmt->execute()) {
        $stmt->close();
        tambahResepResponse('error', 'Gagal menambahkan resep.');
    }

    $stmt->close();
    tambahResepResponse('success', 'Resep berhasil ditambahkan.');
?>

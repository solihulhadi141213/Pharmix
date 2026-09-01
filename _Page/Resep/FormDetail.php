<?php
    include "../../_Config/Connection.php";
    include "../../_Config/Session.php";

    if (empty($SessionIdAkses)) {
        echo '<div class="alert alert-danger mb-0">Sesi akses sudah berakhir. Silakan login ulang.</div>';
        exit;
    }

    $idGroup = (int) ($_POST['id_medication_request_group'] ?? 0);
    if ($idGroup <= 0) {
        echo '<div class="alert alert-danger mb-0">ID resep tidak valid.</div>';
        exit;
    }

    $stmt = $Conn->prepare("SELECT m.*, a.id_pasien, a.nama AS pasien_nama
        FROM medication_request_group m
        LEFT JOIN anggota a ON a.id_anggota = m.id_anggota
        WHERE m.id_medication_request_group = ? LIMIT 1");
    $stmt->bind_param('i', $idGroup);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$data) {
        echo '<div class="alert alert-warning mb-0">Data resep tidak ditemukan.</div>';
        exit;
    }

    function detailValue($value)
    {
        $value = trim((string) ($value ?? ''));
        return htmlspecialchars($value === '' ? '-' : $value, ENT_QUOTES, 'UTF-8');
    }

    $tanggal = !empty($data['datetime_creat']) && strtotime($data['datetime_creat']) !== false
        ? date('d/m/Y H:i', strtotime($data['datetime_creat']))
        : '-';
?>
<div class="row">
    <div class="col-md-6 mb-3"><small class="text-muted d-block">Kode Resep</small><strong><?php echo detailValue('RSP-' . str_pad((string) $idGroup, 6, '0', STR_PAD_LEFT)); ?></strong></div>
    <div class="col-md-6 mb-3"><small class="text-muted d-block">Status</small><span><?php echo detailValue($data['status_resep']); ?></span></div>
    <div class="col-md-6 mb-3"><small class="text-muted d-block">No. RM</small><span><?php echo detailValue($data['id_pasien']); ?></span></div>
    <div class="col-md-6 mb-3"><small class="text-muted d-block">Nama Pasien</small><span><?php echo detailValue($data['pasien_nama']); ?></span></div>
    <div class="col-md-6 mb-3"><small class="text-muted d-block">Tanggal Dibuat</small><span><?php echo detailValue($tanggal); ?></span></div>
    <div class="col-md-6 mb-3"><small class="text-muted d-block">Priority</small><span><?php echo detailValue($data['priority']); ?></span></div>
    <div class="col-md-6 mb-3"><small class="text-muted d-block">Kode Dokter</small><span><?php echo detailValue($data['dokter_kode']); ?></span></div>
    <div class="col-md-6 mb-3"><small class="text-muted d-block">IHS Dokter</small><span><?php echo detailValue($data['dokter_ihs']); ?></span></div>
    <div class="col-md-6 mb-3"><small class="text-muted d-block">Nama Dokter</small><span><?php echo detailValue($data['dokter_nama']); ?></span></div>
    <div class="col-md-6 mb-3"><small class="text-muted d-block">Diagnosa</small><span><?php echo detailValue($data['reason_display']); ?></span></div>
</div>

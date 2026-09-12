<?php
     // Koneksi dan session
    require_once __DIR__."/../../_Config/Connection.php";
    require_once __DIR__."/../../_Config/GlobalFunction.php";
    require_once __DIR__."/../../_Config/Session.php";

    // Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opss!</b><br>
                    Sesi Akses Sudah Berakhir! Silahkan Login Ulang!
                </small>
            </div>
        ';
        exit;
    }

    // Parameter id dari AttachmentView adalah ID kunjungan, bukan ID grup resep.
    $id_kunjungan = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    if ($id_kunjungan === false || $id_kunjungan === null) {
        echo '<div class="alert alert-danger text-center"><small>ID Kunjungan tidak valid.</small></div>';
        return;
    }

    $escape = static function ($value) {
        $value = trim((string)($value ?? ''));
        return htmlspecialchars($value === '' ? '-' : $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    };
    $formatTanggal = static function ($value) {
        if (empty($value) || $value === '0000-00-00 00:00:00') {
            return '-';
        }
        $timestamp = strtotime($value);
        return $timestamp === false ? '-' : date('d-m-Y H:i', $timestamp);
    };

    // LEFT JOIN menjaga grup tanpa item tetap tampil; satu query untuk seluruh resep.
    $sql = "SELECT g.id_medication_request_group, g.no_resep_nasional,
                   g.datetime_creat, g.dokter_nama, g.apoteker_nama,
                   g.kategori_resep, g.sumber_resep, g.status_resep, g.priority,
                   r.MedicationRequestId, r.name_medication, r.status AS status_item,
                   r.dosage_inst_text, r.dosage_inst_frequency, r.dosage_inst_period,
                   r.dosage_inst_period_unit, r.dose_value, r.dose_unit,
                   r.route_display, r.dispense_value, r.dispense_unit,
                   r.supply_duration_value, r.supply_duration_unit, r.racikan_code
            FROM medication_request_group AS g
            LEFT JOIN medication_request AS r
                ON r.id_medication_request_group = g.id_medication_request_group
            WHERE g.id_kunjungan = ?
            ORDER BY g.datetime_creat DESC, g.id_medication_request_group DESC,
                     r.MedicationRequestId ASC";

    $stmtResep = null;
    try {
        $stmtResep = mysqli_prepare($Conn, $sql);
        if (!$stmtResep) {
            throw new RuntimeException('Gagal mempersiapkan data resep.');
        }
        mysqli_stmt_bind_param($stmtResep, 'i', $id_kunjungan);
        if (!mysqli_stmt_execute($stmtResep)) {
            throw new RuntimeException('Gagal mengambil data resep.');
        }
        $resultResep = mysqli_stmt_get_result($stmtResep);
        if (!$resultResep) {
            throw new RuntimeException('Gagal membaca data resep.');
        }
        $grupResep = [];
        while ($row = mysqli_fetch_assoc($resultResep)) {
            $groupId = $row['id_medication_request_group'];
            if (!isset($grupResep[$groupId])) {
                $grupResep[$groupId] = ['data' => $row, 'items' => []];
            }
            if ($row['MedicationRequestId'] !== null) {
                $grupResep[$groupId]['items'][] = $row;
            }
        }
        mysqli_free_result($resultResep);
    } catch (Exception $exception) {
        echo '<div class="alert alert-danger text-center"><small>Data resep gagal dimuat. Silakan coba kembali.</small></div>';
        return;
    } finally {
        if ($stmtResep) {
            mysqli_stmt_close($stmtResep);
        }
    }

    if (empty($grupResep)) {
        echo '<div class="alert alert-info text-center mb-0"><small>Belum ada resep untuk kunjungan ini.</small></div>';
        return;
    }

    $statusColors = [
        'Draft' => 'bg-secondary', 'Verified' => 'bg-primary',
        'Partially' => 'bg-warning text-dark', 'Completed' => 'bg-success',
        'Cancelled' => 'bg-danger'
    ];
    $priorityColors = [
        'routine' => 'bg-primary', 'urgent' => 'bg-warning text-dark',
        'asap' => 'bg-danger', 'stat' => 'bg-danger'
    ];
    $periodUnits = ['s' => 'detik', 'min' => 'menit', 'h' => 'jam', 'd' => 'hari', 'wk' => 'minggu', 'mo' => 'bulan', 'a' => 'tahun'];
?>
<style>
    .kunjungan-resep-detail {
        display: grid;
        grid-template-columns: minmax(0, 9rem) 0.5rem minmax(0, 1fr);
        gap: 0.5rem;
        align-items: start;
    }
    .kunjungan-resep-detail > dt {
        margin: 0;
        font-weight: normal;
        color: #6c757d;
        overflow-wrap: anywhere;
    }
    .kunjungan-resep-detail > dd {
        grid-column: 2 / 4;
        display: grid;
        grid-template-columns: 0.5rem minmax(0, 1fr);
        gap: 0.5rem;
        margin: 0;
        min-width: 0;
        overflow-wrap: anywhere;
    }
    .kunjungan-resep-detail > dd::before {
        content: ':';
    }
    .kunjungan-resep-detail > dd > span {
        min-width: 0;
    }
    @media (max-width: 575.98px) {
        .kunjungan-resep-detail {
            grid-template-columns: minmax(0, 7rem) 0.5rem minmax(0, 1fr);
        }
    }
</style>
<div class="d-flex justify-content-between align-items-center gap-2 mb-3">
    <h6 class="mb-0"><i class="bi bi-prescription2"></i> Medication Request</h6>
    <span class="badge bg-secondary"><?php echo count($grupResep); ?> Resep</span>
</div>
<ul class="list-group">
    <?php foreach ($grupResep as $grup):
        $resep = $grup['data'];
    ?>
        <li class="list-group-item p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <b>Resep #<?php echo $escape($resep['id_medication_request_group']); ?></b>
                <div class="d-flex flex-wrap gap-1">
                    <span class="badge <?php echo $priorityColors[$resep['priority']] ?? 'bg-secondary'; ?>"><?php echo $escape($resep['priority']); ?></span>
                    <span class="badge <?php echo $statusColors[$resep['status_resep']] ?? 'bg-secondary'; ?>"><?php echo $escape($resep['status_resep']); ?></span>
                </div>
            </div>
            <dl class="kunjungan-resep-detail small mb-3">
                <dt>Tanggal</dt><dd><span><?php echo $escape($formatTanggal($resep['datetime_creat'])); ?></span></dd>
                <dt>No. Resep Nasional</dt><dd><span><?php echo $escape($resep['no_resep_nasional']); ?></span></dd>
                <dt>Dokter</dt><dd><span><?php echo $escape($resep['dokter_nama']); ?></span></dd>
                <dt>Apoteker</dt><dd><span><?php echo $escape($resep['apoteker_nama']); ?></span></dd>
                <dt>Kategori</dt><dd><span><?php echo $escape($resep['kategori_resep']); ?></span></dd>
                <dt>Sumber</dt><dd><span><?php echo $escape($resep['sumber_resep']); ?></span></dd>
            </dl>
            <div class="small fw-bold mb-2">Item Resep (<?php echo count($grup['items']); ?>)</div>
            <hr>
            <?php if (empty($grup['items'])): ?>
                <div class="alert alert-light border small mb-0">Belum ada item obat pada resep ini.</div>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($grup['items'] as $index => $item): ?>
                        <li class="list-group-item px-0 text-break">
                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-1">
                                <b class="small"><?php echo ($index + 1).'. '.$escape($item['name_medication']); ?></b>
                                <span class="badge bg-light text-dark border"><?php echo $escape($item['status_item']); ?></span>
                            </div>
                            <dl class="kunjungan-resep-detail small mb-0">
                                <dt>Kode Item</dt><dd><span><?php echo $escape($item['MedicationRequestId']); ?>
                                <?php if (in_array($item['racikan_code'], ['SD', 'EP'], true)): ?>
                                    <span class="badge bg-info text-dark ms-1">Racikan</span>
                                <?php endif; ?>
                                </span></dd>
                                <dt>Dosis</dt><dd><span><?php echo $escape(trim($item['dose_value'].' '.$item['dose_unit'])); ?></span></dd>
                                <dt>Jumlah</dt><dd><span><?php echo $escape(trim($item['dispense_value'].' '.$item['dispense_unit'])); ?></span></dd>
                                <dt>Frekuensi</dt><dd><span><?php echo $escape($item['dosage_inst_frequency'].' kali / '.$item['dosage_inst_period'].' '.($periodUnits[$item['dosage_inst_period_unit']] ?? $item['dosage_inst_period_unit'])); ?></span></dd>
                                <dt>Rute</dt><dd><span><?php echo $escape($item['route_display']); ?></span></dd>
                                <dt>Durasi</dt><dd><span><?php echo $escape(trim($item['supply_duration_value'].' '.$item['supply_duration_unit'])); ?></span></dd>
                                <dt>Aturan Pakai</dt><dd><span><?php echo nl2br($escape($item['dosage_inst_text'])); ?></span></dd>
                            </dl>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>

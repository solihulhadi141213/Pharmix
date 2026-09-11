<?php
    header('Content-Type: application/json; charset=utf-8');
    include __DIR__ . '/../../_Config/Connection.php';
    include __DIR__ . '/../../_Config/GlobalFunction.php';
    include __DIR__ . '/../../_Config/Session.php';

    function responKunjungan($status, $html, $page = 1, $total_page = 1, $total_data = 0) {
        echo json_encode(compact('status', 'html', 'page', 'total_page', 'total_data'), JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }
    function teksKunjungan($value) {
        return htmlspecialchars((string) ($value ?? '-'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    if (empty($SessionIdAkses)) {
        responKunjungan('error', '<div class="alert alert-danger"><small>Sesi akses sudah berakhir. Silakan login ulang.</small></div>');
    }
    $id_anggota = filter_var($_POST['id_anggota'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id_anggota) {
        responKunjungan('error', '<div class="alert alert-danger"><small>ID pasien tidak valid.</small></div>');
    }
    $page = filter_var($_POST['page_kunjungan'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
    $batas = 5;
    try {
        $stmt = mysqli_prepare($Conn, 'SELECT COUNT(*) AS jumlah FROM kunjungan WHERE id_anggota = ?');
        if (!$stmt) throw new RuntimeException('Gagal menyiapkan jumlah kunjungan.');
        mysqli_stmt_bind_param($stmt, 'i', $id_anggota);
        if (!mysqli_stmt_execute($stmt)) throw new RuntimeException('Gagal menghitung kunjungan.');
        $result = mysqli_stmt_get_result($stmt);
        $total_data = (int) mysqli_fetch_assoc($result)['jumlah'];
        mysqli_stmt_close($stmt);
        if ($total_data === 0) {
            responKunjungan('success', '<div class="alert alert-warning text-center"><h1 class="bi bi-inboxes"></h1><small>Belum Ada Riwayat Kunjungan</small></div>');
        }
        $total_page = (int) ceil($total_data / $batas);
        $page = min($page, $total_page);
        $posisi = ($page - 1) * $batas;
        $stmt = mysqli_prepare($Conn, 'SELECT id_kunjungan, tanggal_kunjungan, nama_dokter_penerima, jenis_kunjungan, priority, status, id_encounter FROM kunjungan WHERE id_anggota = ? ORDER BY tanggal_kunjungan DESC, id_kunjungan DESC LIMIT ?, ?');
        if (!$stmt) throw new RuntimeException('Gagal menyiapkan daftar kunjungan.');
        mysqli_stmt_bind_param($stmt, 'iii', $id_anggota, $posisi, $batas);
        if (!mysqli_stmt_execute($stmt)) throw new RuntimeException('Gagal membaca kunjungan.');
        $result = mysqli_stmt_get_result($stmt);
        $statusClasses = [
            'planned' => 'bg-secondary', 'arrived' => 'bg-primary',
            'triaged' => 'bg-info text-dark', 'in-progress' => 'bg-warning text-dark',
            'onleave' => 'bg-dark', 'finished' => 'bg-success',
            'cancelled' => 'bg-danger', 'entered-in-error' => 'bg-secondary',
            'unknown' => 'bg-light text-dark'
        ];
        $priorityClasses = ['Normal' => 'bg-secondary', 'Urgent' => 'bg-warning text-dark', 'Emergency' => 'bg-danger'];
        $statusLabels = [
            'planned' => 'PLN', 'arrived' => 'ARV', 'triaged' => 'TRG',
            'in-progress' => 'INP', 'onleave' => 'ONL', 'finished' => 'FIN',
            'cancelled' => 'CNL', 'entered-in-error' => 'ERR', 'unknown' => 'UNK'
        ];
        $html = '<ul class="list-group list-group-flush">';
        while ($row = mysqli_fetch_assoc($result)) {
            $timestamp = empty($row['tanggal_kunjungan']) ? false : strtotime($row['tanggal_kunjungan']);
            $tanggal = $timestamp === false ? '-' : date('d/m/Y H:i', $timestamp);
            $status = $row['status'] ?: '-';
            $priority = $row['priority'] ?: '-';
            $encounter = $row['id_encounter'] ?: '-';
            $encounterRingkas = strlen($encounter) > 18
                ? substr($encounter, 0, 8) . '...' . substr($encounter, -6)
                : $encounter;
            $html .= '
                <li class="list-group-item px-0">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <a href="javascript:void(0);" class="detail_kunjungan" data-id="'.$row['id_kunjungan'].'">
                            <small><i class="bi bi-calendar-event"></i> ' . teksKunjungan($tanggal) . '</small>
                        </a>
                    </div>

                    <div class="small" style="display:grid;grid-template-columns:max-content auto minmax(0,1fr);column-gap:0.5rem;row-gap:0.25rem;">
                        <span>Dokter</span><span>:</span><span class="text-break">' . teksKunjungan($row['nama_dokter_penerima'] ?: '-') . '</span>
                        <span>Jenis Kunjungan</span><span>:</span><span class="text-break">' . teksKunjungan($row['jenis_kunjungan'] ?: '-') . '</span>
                        <span>Prioritas</span><span>:</span><span><span class="badge ' . ($priorityClasses[$priority] ?? 'bg-secondary') . '">' . teksKunjungan($priority) . '</span></span>
                        <span>Status</span><span>:</span><span><span class="badge ' . ($statusClasses[$status] ?? 'bg-secondary') . '" title="' . teksKunjungan(ucfirst($status)) . '">' . teksKunjungan($statusLabels[$status] ?? '-') . '</span></span>
                        <span class="text-muted">ID Encounter</span><span class="text-muted">:</span><span class="text-muted text-break" title="' . teksKunjungan($encounter) . '">' . teksKunjungan($encounterRingkas) . '</span>
                    </div>
                </li>
            ';
        }
        $html .= '</ul>';
        mysqli_stmt_close($stmt);
        responKunjungan('success', $html, $page, $total_page, $total_data);
    } catch (Exception $e) {
        error_log('Riwayat kunjungan pasien: ' . $e->getMessage());
        responKunjungan('error', '<div class="alert alert-danger"><small>Gagal memuat riwayat kunjungan. Silakan coba lagi.</small></div>');
    }

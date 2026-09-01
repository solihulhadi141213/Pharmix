<?php
    include "../../_Config/Connection.php";

    function resepValue($value)
    {
        $value = trim((string) ($value ?? ''));
        return htmlspecialchars($value === '' ? '-' : $value, ENT_QUOTES, 'UTF-8');
    }

    $page = max(1, (int) ($_POST['page'] ?? 1));
    $keyword = trim((string) ($_POST['keyword'] ?? ''));
    $limit = 8;
    $like = '%' . $keyword . '%';
    $where = "WHERE CAST(m.id_medication_request_group AS CHAR) LIKE ?
        OR COALESCE(a.nama, '') LIKE ?
        OR m.dokter_nama LIKE ?
        OR m.status_resep LIKE ?";

    $from = "FROM medication_request_group m
        LEFT JOIN anggota a ON a.id_anggota = m.id_anggota";

    $stmtCount = mysqli_prepare(
        $Conn,
        "SELECT COUNT(*) AS total {$from} {$where}"
    );

    if (!$stmtCount) {
        echo '<div class="col-12"><div class="card resep-state-card border-danger"><div class="card-body text-center text-danger">Gagal menyiapkan data resep.</div></div></div>';
        exit;
    }

    mysqli_stmt_bind_param($stmtCount, 'ssss', $like, $like, $like, $like);
    mysqli_stmt_execute($stmtCount);
    $countResult = mysqli_stmt_get_result($stmtCount);
    $totalData = (int) (mysqli_fetch_assoc($countResult)['total'] ?? 0);
    mysqli_stmt_close($stmtCount);

    $totalPage = max(1, (int) ceil($totalData / $limit));
    $page = min($page, $totalPage);
    $offset = ($page - 1) * $limit;

    $stmt = mysqli_prepare(
        $Conn,
        "SELECT m.id_medication_request_group, a.nama AS pasien_nama,
                m.dokter_nama, m.status_resep, m.datetime_creat
         {$from}
         {$where}
         ORDER BY m.datetime_creat DESC
         LIMIT ?, ?"
    );

    if (!$stmt) {
        echo '<div class="col-12"><div class="card resep-state-card border-danger"><div class="card-body text-center text-danger">Gagal menyiapkan daftar resep.</div></div></div>';
        exit;
    }

    mysqli_stmt_bind_param($stmt, 'ssssii', $like, $like, $like, $like, $offset, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($totalData === 0) {
        echo '
            <div class="col-12">
                <div class="card card-data-kosong resep-state-card">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                        <i class="bi bi-prescription2 fs-2 text-muted mb-3"></i>
                        <h6 class="mb-1">Belum Ada Resep</h6>
                        <small class="text-muted">Data resep yang sesuai akan tampil di sini.</small>
                    </div>
                </div>
            </div>
        ';
    } else {
        while ($data = mysqli_fetch_assoc($result)) {
            $idGroup    = (int) $data['id_medication_request_group'];
            $kodeResep  = resepValue('RSP-' . str_pad((string) $idGroup, 6, '0', STR_PAD_LEFT));
            $namaPasien = resepValue($data['pasien_nama'] ?? null);
            $namaDokter = resepValue($data['dokter_nama'] ?? null);
            $statusRaw  = trim((string) ($data['status_resep'] ?? ''));
            $status     = resepValue($statusRaw);
            $timestamp  = !empty($data['datetime_creat']) ? strtotime($data['datetime_creat']) : false;
            $tanggal    = $timestamp === false ? '-' : date('d/m/Y H:i', $timestamp);

            if ($statusRaw === 'Verified') {
                $badge = 'bg-primary';
            } elseif ($statusRaw === 'Partially') {
                $badge = 'bg-warning text-dark';
            } elseif ($statusRaw === 'Completed') {
                $badge = 'bg-success';
            } elseif ($statusRaw === 'Cancelled') {
                $badge = 'bg-danger';
            } else {
                $badge = 'bg-secondary';
            }

            echo '
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card card-resep h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="resep-card-heading">
                                <span class="resep-card-code">' . $kodeResep . '</span>
                            </div>
                            <div class="resep-card-info">
                                <div>
                                    Nama<strong>' . $namaPasien . '</strong>
                                </div>
                                <div>
                                    Tanggal<span>' . resepValue($tanggal) . '</span>
                                </div>
                                <div>
                                    Dokter<span>' . $namaDokter . '</span>
                                </div>
                                <div>
                                    Status<span class="badge ' . $badge . '">' . $status . '</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer resep-card-footer bg-white">
                            <div class="dropdown">
                                <button type="button" class="btn btn-secondary btn-floating" data-bs-toggle="dropdown" aria-expanded="false" title="Opsi Resep">
                                    <i class="bi bi-three-dots-vertical"></i>
                                    <span class="visually-hidden">Opsi Resep</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button type="button" class="dropdown-item detail_resep" data-id="' . $idGroup . '"><i class="bi bi-eye"></i> Detail</button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item edit_resep" data-id="' . $idGroup . '"><i class="bi bi-pencil"></i> Edit</button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item text-danger hapus_resep" data-id="' . $idGroup . '"><i class="bi bi-trash"></i> Hapus</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            ';
        }
    }

    mysqli_stmt_close($stmt);

    if ($totalPage > 1) {
        echo '<div class="col-12"><nav aria-label="Pagination resep"><ul class="pagination resep-pagination justify-content-center mb-0">';
        for ($number = 1; $number <= $totalPage; $number++) {
            $active = $number === $page ? ' active' : '';
            echo '<li class="page-item' . $active . '"><a class="page-link" href="#" data-page="' . $number . '">' . $number . '</a></li>';
        }
        echo '</ul></nav></div>';
    }
?>

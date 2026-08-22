<?php
    // Koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Inisialisasi pagination
    $JmlHalaman = 0;
    $page       = 1;

    // Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        echo '
            <tr>
                <td colspan="8" class="text-center">
                    <small class="text-danger">
                        Sesi Akses Sudah Berakhir! Silahkan Login Ulang
                    </small>
                </td>
            </tr>
        ';
        exit;
    }

    // Tangkap dan validasi parameter filter.
    $keyword_by = trim($_POST['keyword_by'] ?? '');
    $keyword    = trim($_POST['keyword'] ?? '');
    $page       = max(1, (int)($_POST['page'] ?? 1));
    $batas      = (int)($_POST['batas'] ?? 10);
    $batas      = min(100, max(1, $batas));
    $posisi     = ($page - 1) * $batas;

    $orderColumns = [
        'id_barang_bacth' => 'bb.id_barang_bacth',
        'no_batch'        => 'bb.no_batch',
        'expired_date'    => 'bb.expired_date',
        'qty_batch'       => 'bb.qty_batch',
        'reminder_date'   => 'bb.reminder_date',
        'status'          => 'bb.status',
        'kode_barang'     => 'b.kode_barang',
        'nama_barang'     => 'b.nama_barang',
        'satuan_barang'   => 'b.satuan_barang'
    ];
    $keywordColumns = [
        'no_batch'      => 'bb.no_batch',
        'expired_date'  => 'bb.expired_date',
        'reminder_date' => 'bb.reminder_date',
        'status'        => 'bb.status',
        'kode_barang'   => 'b.kode_barang',
        'nama_barang'   => 'b.nama_barang',
        'satuan_barang' => 'b.satuan_barang'
    ];

    $orderBy = $orderColumns[$_POST['OrderBy'] ?? ''] ?? 'bb.id_barang_bacth';
    $sortBy  = strtoupper($_POST['ShortBy'] ?? 'DESC');
    $sortBy  = in_array($sortBy, ['ASC', 'DESC'], true) ? $sortBy : 'DESC';

    $where = '';
    if ($keyword !== '') {
        $escapedKeyword = mysqli_real_escape_string($Conn, $keyword);
        if (isset($keywordColumns[$keyword_by])) {
            $where = " WHERE {$keywordColumns[$keyword_by]} LIKE '%$escapedKeyword%'";
        } else {
            $where = " WHERE bb.no_batch LIKE '%$escapedKeyword%'
                OR bb.expired_date LIKE '%$escapedKeyword%'
                OR bb.reminder_date LIKE '%$escapedKeyword%'
                OR bb.status LIKE '%$escapedKeyword%'
                OR b.kode_barang LIKE '%$escapedKeyword%'
                OR b.nama_barang LIKE '%$escapedKeyword%'
                OR b.satuan_barang LIKE '%$escapedKeyword%'";
        }
    }

    $from = ' FROM barang_bacth AS bb JOIN barang AS b ON bb.id_barang = b.id_barang';
    $result_jml = mysqli_query($Conn, "SELECT COUNT(bb.id_barang_bacth) AS total$from$where");
    if (!$result_jml) {
        die('Error dalam query jumlah data: ' . mysqli_error($Conn));
    }
    $jml_data = (int)(mysqli_fetch_assoc($result_jml)['total'] ?? 0);

    if ($jml_data == 0) {
        echo '
            <tr>
                <td colspan="8" class="text-center text-danger">
                    Tidak Ada Data Yang Ditampilkan.
                </td>
            </tr>
        ';
    } else {
        $no = 1 + $posisi;

        $query = "SELECT bb.*, b.kode_barang, b.nama_barang, b.satuan_barang$from$where
            ORDER BY $orderBy $sortBy LIMIT $posisi, $batas";

        $result = mysqli_query($Conn, $query);
        if (!$result) {
            die("Error dalam query utama: " . mysqli_error($Conn));
        }

        while ($data = mysqli_fetch_assoc($result)) {
            $id_barang_bacth = (int)$data['id_barang_bacth'];
            $id_barang       = (int)$data['id_barang'];
            $no_batch        = htmlspecialchars($data['no_batch'] ?? '', ENT_QUOTES, 'UTF-8');
            $expired_date    = htmlspecialchars($data['expired_date'] ?? '', ENT_QUOTES, 'UTF-8');
            $qty_batch_value = (float)($data['qty_batch'] ?? 0);
            $qty_batch       = $qty_batch_value == floor($qty_batch_value)
                ? number_format($qty_batch_value, 0, ',', '.')
                : number_format($qty_batch_value, 2, ',', '.');
            $reminder_date = htmlspecialchars($data['reminder_date'] ?? '', ENT_QUOTES, 'UTF-8');
            $status        = htmlspecialchars($data['status'] ?? '', ENT_QUOTES, 'UTF-8');
            $nama_barang   = htmlspecialchars($data['nama_barang'] ?? '', ENT_QUOTES, 'UTF-8');
            $kode_barang   = htmlspecialchars($data['kode_barang'] ?? '', ENT_QUOTES, 'UTF-8');
            $satuan_barang = htmlspecialchars($data['satuan_barang'] ?? '', ENT_QUOTES, 'UTF-8');

            echo '
                <tr>
                    <td><small class="text-muted">' . $no . '</small></td>
                    <td>
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetailBarang" data-id="' . $id_barang . '">
                            <small>' . $kode_barang . '</small>
                        </a>
                    </td>
                    <td><small class="text-muted">' . $nama_barang . '</small></td>
                    <td><small class="text-muted">' . $no_batch . '</small></td>
                    <td><small class="text-muted">' . $expired_date . '</small></td>
                    <td><small class="text-muted">' . $qty_batch . ' ' . $satuan_barang . '</small></td>
                    <td><small class="text-muted">' . $status . '</small></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-floating btn-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <li class="dropdown-header text-start">
                                <h6>Option</h6>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalDetail" data-id="' . $id_barang_bacth . '">
                                    <i class="bi bi-info-circle"></i> Detail
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalEdit" data-id="' . $id_barang_bacth . '">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalHapus" data-id="' . $id_barang_bacth . '">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </li>
                        </ul>
                    </td>
                </tr>
            ';
            $no++;
        }
        $JmlHalaman = ceil($jml_data / $batas);
    }
?>

<script>
    var page_count = <?php echo $JmlHalaman; ?>;
    var current_page = <?php echo $page; ?>;
    
    $('#page_info').html('Page ' + current_page + ' Of ' + page_count + '');
    
    if (current_page == 1) {
        $('#prev_button').prop('disabled', true);
    } else {
        $('#prev_button').prop('disabled', false);
    }
    if (page_count <= current_page) {
        $('#next_button').prop('disabled', true);
    } else {
        $('#next_button').prop('disabled', false);
    }
</script>
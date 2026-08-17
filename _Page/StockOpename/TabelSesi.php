<?php
    
    //koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    
    //inisiasi Variabe;
    $JmlHalaman = 0;
    $page       = 1;
    if (empty($SessionIdAkses)) {
        echo json_encode([
            "status" => "error",
            "html"   => '
                <tr>
                    <td colspan="7" class="text-center text-danger">
                        <small>Sesi akses sudah berakhir. Silakan login ulang.</small>
                    </td>
                </tr>
            ',
            "page" => 1,
            "total_page" => 1,
            "total_data" => 0
        ]);
        exit;
    }
    //batas
    $page       = $_POST['page'] ?? 1;
    $batas      = $_POST['batas'] ?? 10;
    $OrderBy    = $_POST['OrderBy'] ?? 'id_stock_opname';
    $ShortBy    = $_POST['ShortBy'] ?? 'ASC';
    $keyword_by = $_POST['keyword_by'] ?? '';
    $keyword    = trim($_POST['keyword'] ?? '');

   
    //Atur Page dan limit
    $page  = (int)$page;
    $batas = (int)$batas;
    if ($page <= 0) {
        $page = 1;
    }
    if ($batas <= 0) {
        $batas = 10;
    }
    $posisi = ($page - 1) * $batas;

    // VALIDASI ORDER BY
    $allowedOrder = [
        'id_stock_opname',
        'start_at',
        'finish_at',
        'status'
    ];

    // VALIDASI SORT
    $ShortBy = strtoupper($ShortBy);

    if (!in_array($ShortBy, ['ASC', 'DESC'])) {
        $ShortBy = 'ASC';
    }

    if (!in_array($OrderBy, $allowedOrder)) {
        $OrderBy = 'id_stock_opname';
    }

    // Build order SQL dari whitelist agar aman dan tetap optimal
    $OrderBySql = "s.$OrderBy";

    // VALIDASI FILTER
    $allowedKeywordBy = [
        'start_at',
        'finish_at',
        'status'
    ];
    if (!empty($keyword_by) && !in_array($keyword_by, $allowedKeywordBy)) {
        $keyword_by = '';
    }

    // FILTER QUERY
    $where      = "";
    $bindTypes  = "";
    $bindValues = [];

    if (!empty($keyword)) {
        $keywordLike = "%" . $keyword . "%";
        if (!empty($keyword_by)) {
            $where .= " WHERE s.$keyword_by LIKE ? ";
            $bindTypes .= "s";
            $bindValues[] = $keywordLike;

        } else {

            $where .= "
                WHERE (
                    start_at LIKE ? OR 
                    finish_at LIKE ? OR 
                    status LIKE ?
                )
            ";
            $bindTypes .= "sss";

            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
        }
    }

   // TOTAL DATA
    $sql_count = "SELECT COUNT(*) AS total FROM stock_opname AS s $where";
    $stmt_count = $Conn->prepare($sql_count);
    if (!$stmt_count) {
        echo json_encode([
            "status" => "error",
            "html"   => '
                <tr>
                    <td colspan="7" class="text-center text-danger">
                        <small>Gagal mempersiapkan query count.</small>
                    </td>
                </tr>
            ',
            "page" => $page,
            "total_page" => 1,
            "total_data" => 0
        ]);
        exit;
    }

    if (!empty($bindValues)) {
        $stmt_count->bind_param($bindTypes, ...$bindValues);
    }

    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $data_count   = $result_count->fetch_assoc();
    $total_data   = (int)$data_count['total'];
    $stmt_count->close();

    // TOTAL PAGE
    $total_page = ($total_data > 0) ? ceil($total_data / $batas) : 1;
    if ($page > $total_page) {
        $page = $total_page;
    }
    $posisi = ($page - 1) * $batas;

    // QUERY DATA
    $sql = "
        SELECT
            s.id_stock_opname,
            s.start_at,
            s.finish_at,
            s.status,
            COALESCE(sb.jumlah_item, 0) AS jumlah_item,
            COALESCE(sb.jumlah_kelebihan, 0) AS jumlah_kelebihan,
            COALESCE(sb.jumlah_kekurangan, 0) AS jumlah_kekurangan
        FROM stock_opname AS s
        LEFT JOIN (
            SELECT
                id_stock_opname,
                COUNT(id_stock_opname_barang) AS jumlah_item,
                SUM(CASE WHEN jumlah > 0 THEN jumlah ELSE 0 END) AS jumlah_kelebihan,
                SUM(CASE WHEN jumlah < 0 THEN jumlah ELSE 0 END) AS jumlah_kekurangan
            FROM stock_opname_barang
            GROUP BY id_stock_opname
        ) AS sb ON sb.id_stock_opname = s.id_stock_opname
        $where
        ORDER BY $OrderBySql $ShortBy
        LIMIT ?, ?
    ";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {

        echo json_encode([
            "status" => "error",
            "html"   => '
                <tr>
                    <td colspan="7" class="text-center text-danger">
                        <small>Gagal mempersiapkan query data.</small>
                    </td>
                </tr>
            ',
            "page" => $page,
            "total_page" => $total_page,
            "total_data" => $total_data
        ]);

        exit;
    }

    // BIND PARAMETER
    $bindTypesData    = $bindTypes . "ii";
    $bindValuesData   = $bindValues;
    $bindValuesData[] = $posisi;
    $bindValuesData[] = $batas;

    $stmt->bind_param($bindTypesData, ...$bindValuesData);

    // EXECUTE
    if (!$stmt->execute()) {

        echo json_encode([
            "status" => "error",
            "html"   => '
                <tr>
                    <td colspan="7" class="text-center text-danger">
                        <small>Terjadi kesalahan saat mengambil data.</small>
                    </td>
                </tr>
            ',
            "page" => $page,
            "total_page" => $total_page,
            "total_data" => $total_data
        ]);

        exit;
    }

    $query = $stmt->get_result();

    // BUILD HTML
    $html = '';
    $no   = 1 + $posisi;

    if ($query->num_rows == 0) {

        $html .= '
            <tr>
                <td colspan="7" class="text-center text-danger">
                    <small>Tidak ada data yang ditampilkan.</small>
                </td>
            </tr>
        ';

    } else {

        while ($data = $query->fetch_assoc()) {

            $id_stock_opname  = (int)$data['id_stock_opname'];
            $start_at         = htmlspecialchars($data['start_at']);
            $finish_at        = $data['finish_at'];
            $status           = $data['status'];
            $jumlah_item      = (int)$data['jumlah_item'];
            $JumlahKelebihan  = (int)$data['jumlah_kelebihan'];
            $JumlahKekurangan = (int)$data['jumlah_kekurangan'];
           
            //Routing status
            if($status=="On-Progress"){
                $label_status='<span class="badge badge-warning">On-Progress</span>';
            }else{
                $label_status='<span class="badge badge-success">Finished</span>';
            }
            $JumlahKelebihan_rp = "Rp " . number_format($JumlahKelebihan,0,',','.');
            $JumlahKekurangan_rp = "Rp " . number_format($JumlahKekurangan,0,',','.');

            // Format start_at
            $start_at_format = date('d F Y', strtotime($start_at));

            $html .= '
                <tr>
                    <td><small class="text-muted">'.$no.'</small></td>
                    <td>
                        <a class="modal_detail_sesi" href="javascript:void(0)" data-id="'.$id_stock_opname.'">
                            <small>'.$start_at_format.'</small>
                        </a>
                    </td>
                    <td><small class="text-muted">'.$jumlah_item.' Item</small></td>
                    <td><small class="text-muted">'.$JumlahKelebihan_rp.'</small></td>
                    <td><small class="text-muted">'.$JumlahKekurangan_rp.'</small></td>
                    <td><small class="text-muted">'.$label_status.'</small></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-floating btn-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" style="">
                            <li class="dropdown-header text-start">
                                <h6>Option</h6>
                            </li>
                            <li>
                                <a class="dropdown-item modal_detail_sesi" href="javascript:void(0)" data-id="'.$id_stock_opname.'">
                                    <i class="bi bi-info-circle"></i> Detail
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalEditSesi" data-id="'.$id_stock_opname.'" data-start_at="'.$start_at.'" data-status="'.$status.'">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalHapusSesi" data-id="'.$id_stock_opname.'" data-start_at="'.$start_at.'" data-status="'.$status.'">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </li>
                        </ul>
                    </td>
                </tr>
            ';
            $no++;
        }
    }

    $stmt->close();
    echo json_encode([
        "status"     => "success",
        "html"       => $html,
        "page"       => $page,
        "total_page" => $total_page,
        "total_data" => $total_data
    ]);
?>

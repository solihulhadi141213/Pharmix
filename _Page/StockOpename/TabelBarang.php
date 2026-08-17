<?php
    // Koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    if(empty($SessionIdAkses)){
        echo json_encode([
            "status" => "error",
            "message" => "Sesi Akses Sudah Berakhir! Silahkan Login Ulang",
            "html" => '<tr><td colspan="9" class="text-center text-danger">Sesi Akses Sudah Berakhir! Silahkan Login Ulang</td></tr>',
            "page" => 1,
            "total_page" => 1,
            "total_data" => 0
        ]);
        exit;
    }

    if(empty($_POST['id_stock_opname'])){
        echo json_encode([
            "status" => "error",
            "message" => "ID Sesi Stok Opename Tidak Boleh Kosong!",
            "html" => '<tr><td colspan="9" class="text-center text-danger">ID Sesi Stok Opename Tidak Boleh Kosong!</td></tr>',
            "page" => 1,
            "total_page" => 1,
            "total_data" => 0
        ]);
        exit;
    }

    $id_stock_opname = validateAndSanitizeInput($_POST['id_stock_opname']);
    $page            = !empty($_POST['page']) ? (int) $_POST['page'] : 1;
    $batas           = !empty($_POST['batas']) ? (int) $_POST['batas'] : 10;
    $OrderBy         = !empty($_POST['OrderBy']) ? $_POST['OrderBy'] : 'id_barang';
    $ShortBy         = !empty($_POST['ShortBy']) ? strtoupper($_POST['ShortBy']) : 'DESC';
    $keyword_by      = !empty($_POST['keyword_by']) ? $_POST['keyword_by'] : '';
    $keyword         = trim($_POST['keyword'] ?? '');

    // Variabel Status Stock Opename
    $status_stock_opname = GetDetailData($Conn, 'stock_opname', 'id_stock_opname', $id_stock_opname, 'status');

    if($page <= 0){
        $page = 1;
    }
    if($batas <= 0){
        $batas = 10;
    }
    $posisi = ($page - 1) * $batas;

    $allowedOrder = ['id_barang', 'kode_barang', 'nama_barang', 'kategori_barang', 'satuan_barang', 'harga_beli'];
    if(!in_array($OrderBy, $allowedOrder)){
        $OrderBy = 'id_barang';
    }
    if(!in_array($ShortBy, ['ASC', 'DESC'])){
        $ShortBy = 'DESC';
    }

    $allowedKeywordBy = ['kode_barang', 'nama_barang'];
    if(!empty($keyword_by) && !in_array($keyword_by, $allowedKeywordBy)){
        $keyword_by = '';
    }

    $where = "";
    $bindTypes = "";
    $bindValues = [];

    if(!empty($keyword)){
        $keywordLike = "%".$keyword."%";
        if(!empty($keyword_by)){
            $where .= " AND b.$keyword_by LIKE ? ";
            $bindTypes .= "s";
            $bindValues[] = $keywordLike;
        }else{
            $where .= " AND (b.kode_barang LIKE ? OR b.nama_barang LIKE ?) ";
            $bindTypes .= "ss";
            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
        }
    }

    $sql_count = "
        SELECT COUNT(*) AS total
        FROM barang AS b
        WHERE 1=1
        $where
    ";
    $stmt_count = mysqli_prepare($Conn, $sql_count);
    if(!$stmt_count){
        echo json_encode([
            "status" => "error",
            "message" => "Gagal mempersiapkan query count.",
            "html" => '<tr><td colspan="9" class="text-center text-danger">Gagal mempersiapkan query count.</td></tr>',
            "page" => $page,
            "total_page" => 1,
            "total_data" => 0
        ]);
        exit;
    }

    if(!empty($bindValues)){
        mysqli_stmt_bind_param($stmt_count, $bindTypes, ...$bindValues);
    }
    mysqli_stmt_execute($stmt_count);
    $ResultCount = mysqli_stmt_get_result($stmt_count);
    $DataCount = mysqli_fetch_assoc($ResultCount);
    $total_data = !empty($DataCount['total']) ? (int)$DataCount['total'] : 0;
    mysqli_stmt_close($stmt_count);

    $total_page = ($total_data > 0) ? ceil($total_data / $batas) : 1;
    if($page > $total_page){
        $page = $total_page;
        $posisi = ($page - 1) * $batas;
    }

    $sql = "
        SELECT
            b.id_barang,
            b.kode_barang,
            b.nama_barang,
            b.kategori_barang,
            b.satuan_barang,
            b.harga_beli AS harga_barang,
            sob.id_stock_opname_barang,
            sob.stok_awal,
            sob.stok_akhir,
            sob.stok_gap,
            sob.jumlah,
            sob.harga_beli AS harga_sesi
        FROM barang AS b
        LEFT JOIN stock_opname_barang AS sob
            ON sob.id_barang = b.id_barang
            AND sob.id_stock_opname = ?
        WHERE 1=1
        $where
        ORDER BY b.$OrderBy $ShortBy
        LIMIT ?, ?
    ";

    $stmt = mysqli_prepare($Conn, $sql);
    if(!$stmt){
        echo json_encode([
            "status" => "error",
            "message" => "Gagal mempersiapkan query data.",
            "html" => '<tr><td colspan="9" class="text-center text-danger">Gagal mempersiapkan query data.</td></tr>',
            "page" => $page,
            "total_page" => $total_page,
            "total_data" => $total_data
        ]);
        exit;
    }

    $bindTypesData = "i";
    $bindValuesData = [$id_stock_opname];
    if(!empty($bindTypes)){
        $bindTypesData .= $bindTypes;
        foreach($bindValues as $value){
            $bindValuesData[] = $value;
        }
    }
    $bindValuesData[] = $posisi;
    $bindValuesData[] = $batas;
    $bindTypesData .= "ii";

    mysqli_stmt_bind_param($stmt, $bindTypesData, ...$bindValuesData);
    mysqli_stmt_execute($stmt);
    $Result = mysqli_stmt_get_result($stmt);

    $html = '';
    $no = 1 + $posisi;

    if(mysqli_num_rows($Result) < 1){
        $html = '
            <tr>
                <td colspan="9" class="text-center text-danger">
                    Tidak Ada Data Yang Ditampilkan.
                </td>
            </tr>
        ';
    }else{
        while($data = mysqli_fetch_assoc($Result)){
            $id_barang              = (int)$data['id_barang'];
            $kode_barang            = htmlspecialchars($data['kode_barang']);
            $nama_barang            = htmlspecialchars($data['nama_barang']);
            $harga_beli             = !empty($data['harga_sesi']) ? (float)$data['harga_sesi'] : (float)$data['harga_barang'];
            $harga_beli_rp          = "Rp " . number_format($harga_beli, 0, ',', '.');
            $stok_awal              = isset($data['stok_awal']) && $data['stok_awal']   !== null ? $data['stok_awal'] : "-";
            $stok_akhir             = isset($data['stok_akhir']) && $data['stok_akhir'] !== null ? $data['stok_akhir'] : "-";
            $stok_gap               = isset($data['stok_gap']) && $data['stok_gap']     !== null ? $data['stok_gap'] : "-";
            $jumlah                 = isset($data['jumlah']) && $data['jumlah']         !== null ? "Rp " . number_format($data['jumlah'],0,',','.') : "-";
            $id_stock_opname_barang = !empty($data['id_stock_opname_barang']) ? (int)$data['id_stock_opname_barang'] : "";

            // Tombol sesuai $status_stock_opname
            if($status_stock_opname=="Finished"){
                $tombol = '
                    <button type="button" class="btn btn-sm btn-floating btn-primary" disabled>
                        <i class="bi bi-pencil"></i>
                    </button>
                ';
            }else{
                $tombol = '
                    <button type="button" class="btn btn-sm btn-floating btn-primary show_modal_stock_opname" data-id_barang="'.$id_barang.'" data-id_stock_opname="'.$id_stock_opname.'" data-id_stok_opename_barang="'.$id_stock_opname_barang.'">
                        <i class="bi bi-pencil"></i>
                    </button>
                ';
            }

            $html .= '
                <tr>
                    <td><small class="text-muted">'.$no.'</small></td>
                    <td>
                        <a href="javascript:void(0);" class="show_modal_detail_stock_opname_barang" data-id_barang="'.$id_barang.'" data-id_stock_opname="'.$id_stock_opname.'">
                            <small>'.$kode_barang.'</small>
                        </a>
                    </td>
                    <td><small class="text-muted">'.$nama_barang.'</small></td>
                    <td><small class="text-muted">'.$harga_beli_rp.'</small></td>
                    <td><small class="text-muted">'.$stok_awal.'</small></td>
                    <td><small class="text-muted">'.$stok_akhir.'</small></td>
                    <td><small class="text-muted">'.$stok_gap.'</small></td>
                    <td><small class="text-muted">'.$jumlah.'</small></td>
                    <td>'.$tombol.'</td>
                </tr>
            ';
            $no++;
        }
    }

    mysqli_stmt_close($stmt);

    echo json_encode([
        "status" => "success",
        "message" => "Data berhasil ditampilkan.",
        "html" => $html,
        "page" => $page,
        "total_page" => $total_page,
        "total_data" => $total_data
    ]);
?>

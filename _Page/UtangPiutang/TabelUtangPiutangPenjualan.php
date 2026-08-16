<?php
    //koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    //inisiasi Variabe;
    $JmlHalaman=0;
    $page=1;
    $allowed_order_by = ['id_transaksi_jual_beli','tanggal','kategori','total','cash','status'];
    $allowed_keyword_by = ['tanggal','kategori','nama'];
    if(!function_exists('bind_stmt_params')){
        function bind_stmt_params($stmt, $types, &$params){
            $bind_names = [];
            $bind_names[] = $types;
            foreach($params as $key => &$value){
                $bind_names[] = &$value;
            }
            return call_user_func_array('mysqli_stmt_bind_param', $bind_names);
        }
    }
    if(empty($SessionIdAkses)){
        echo '
            <tr>
                <td colspan="10" class="text-center text-danger">
                    Sesi Akses Sudah Berakhir! Silahkan Login Ulang
                </td>
            </tr>
        ';
    }else{
        //Keyword_by
        if(!empty($_POST['keyword_by'])){
            $keyword_by=$_POST['keyword_by'];
        }else{
            $keyword_by="";
        }
        //keyword
        if(!empty($_POST['keyword'])){
            $keyword=$_POST['keyword'];
        }else{
            $keyword="";
        }
        $keyword_like = '%'.$keyword.'%';
        //batas
        if(!empty($_POST['batas'])){
            $batas=$_POST['batas'];
        }else{
            $batas="10";
        }
        //ShortBy
        if(!empty($_POST['ShortBy'])){
            $ShortBy=$_POST['ShortBy'];
        }else{
            $ShortBy="DESC";
        }
        //OrderBy
        if(!empty($_POST['OrderBy'])){
            $OrderBy=$_POST['OrderBy'];
        }else{
            $OrderBy="tanggal";
        }
        //Atur Page
        if(!empty($_POST['page'])){
            $page=$_POST['page'];
            $posisi = ( $page - 1 ) * $batas;
        }else{
            $page="1";
            $posisi = 0;
        }
        if(!in_array($ShortBy, ['ASC','DESC'], true)){
            $ShortBy = "DESC";
        }
        if(!in_array($OrderBy, $allowed_order_by, true)){
            $OrderBy = "tanggal";
        }
        if(!empty($keyword_by) && !in_array($keyword_by, $allowed_keyword_by, true)){
            $keyword_by = "";
        }

        $base_from = "
            FROM transaksi_jual_beli tjb
            LEFT JOIN (
                SELECT id_transaksi_jual_beli, SUM(jumlah) AS total_angsuran
                FROM transaksi_pembayaran
                GROUP BY id_transaksi_jual_beli
            ) tp ON tp.id_transaksi_jual_beli = tjb.id_transaksi_jual_beli
            LEFT JOIN anggota a ON tjb.id_anggota = a.id_anggota
        ";
        $where_clause = " WHERE tjb.status='Kredit' ";
        $types = "";
        $params = [];
        if(!empty($keyword)){
            if(empty($keyword_by)){
                $where_clause .= " AND (tjb.tanggal LIKE ? OR tjb.kategori LIKE ? OR a.nama LIKE ?) ";
                $types .= "sss";
                $params[] = $keyword_like;
                $params[] = $keyword_like;
                $params[] = $keyword_like;
            }else{
                if($keyword_by === "nama"){
                    $where_clause .= " AND a.nama LIKE ? ";
                    $types .= "s";
                    $params[] = $keyword_like;
                }else{
                    $where_clause .= " AND tjb.$keyword_by LIKE ? ";
                    $types .= "s";
                    $params[] = $keyword_like;
                }
            }
        }

        $stmt_jml = mysqli_prepare($Conn, "SELECT COUNT(tjb.id_transaksi_jual_beli) AS jml_data ".$base_from.$where_clause);
        if(!empty($types)){
            bind_stmt_params($stmt_jml, $types, $params);
        }
        mysqli_stmt_execute($stmt_jml);
        $result_jml = mysqli_stmt_get_result($stmt_jml);
        $row_jml = mysqli_fetch_assoc($result_jml);
        $jml_data = isset($row_jml['jml_data']) ? (int) $row_jml['jml_data'] : 0;
        mysqli_stmt_close($stmt_jml);
        if(empty($jml_data)){
            echo '
                <tr>
                    <td colspan="10" class="text-center text-danger">
                        Tidak Ada Data Yang Ditampilkan.
                    </td>
                </tr>
            ';
        }else{
            $no = 1+$posisi;
            //KONDISI PENGATURAN MASING FILTER
            $sql = "
                SELECT tjb.*, a.nama, COALESCE(tp.total_angsuran, 0) AS total_angsuran
                ".$base_from."
                ".$where_clause."
                ORDER BY tjb.$OrderBy $ShortBy
                LIMIT ?, ?
            ";
            $query = mysqli_prepare($Conn, $sql);
            $limit_posisi = (int) $posisi;
            $limit_batas = (int) $batas;
            $bind_params = $params;
            $bind_params[] = $limit_posisi;
            $bind_params[] = $limit_batas;
            if(empty($types)){
                mysqli_stmt_bind_param($query, "ii", $limit_posisi, $limit_batas);
            }else{
                bind_stmt_params($query, $types."ii", $bind_params);
            }
            mysqli_stmt_execute($query);
            $result = mysqli_stmt_get_result($query);
            while ($data = mysqli_fetch_array($result)) {
                $id_transaksi_jual_beli= $data['id_transaksi_jual_beli'];
                $kategori= $data['kategori'];
                $tanggal= $data['tanggal'];
                $total= $data['total'];
                $cash= $data['cash'];
                $status= $data['status'];
                $total_rp = "Rp " . number_format($total,0,',','.');
                $cash_rp = "Rp " . number_format($cash,0,',','.');
                //Routing Penjualan
                if($kategori=="Penjualan"){
                    $label_kategori='<code class="text text-primary">Penjualan</code>';
                    $label_status='<span class="badge badge-success">Piutang</span>';
                }else{
                    if($kategori=="Retur Penjualan"){
                        $label_kategori='<code class="text text-info">Ret.Penjualan</code>';
                        $label_status='<span class="badge badge-danger">Utang</span>';
                    }else{
                        if($kategori=="Pembelian"){
                            $label_kategori='<code class="text text-warning">Pembelian</code>';
                            $label_status='<span class="badge badge-danger">Utang</span>';
                        }else{
                            if($kategori=="Retur Pembelian"){
                                $label_kategori='<code class="text text-danger">Ret.Pembelian</code>';
                                $label_status='<span class="badge badge-success">Piutang</span>';
                            }else{
                                $label_kategori='<code class="text text-muted">None</code>';
                                $label_status='<span class="badge badge-dark">None</span>';
                            }
                        }
                    }
                }
                //Buka nama anggota dari tabel anggota
                if(empty($data['id_anggota'])){
                    $id_anggota= "";
                    $nama_anggota="-";
                }else{
                    $id_anggota= $data['id_anggota'];
                    $nama_anggota=empty($data['nama']) ? '-' : $data['nama'];
                }
                
                //Format tanggal
                $strtotime=strtotime($tanggal);
                $TanggalTransaksi=date('d/m/Y', $strtotime);

                //Hitung Jumlah Pembayaran
                $total_angsuran = (float) $data['total_angsuran'];
                $total_angsuran_rp = "Rp " . number_format($total_angsuran,0,',','.');

                //Hitung Sisa/Selisish
                $sisa_pembayaran=$total-$cash-$total_angsuran;
                $sisa_pembayaran_rp = "Rp " . number_format($sisa_pembayaran,0,',','.');

                //Tampilkan Data
                echo '
                    <tr>
                        <td>
                            <input class="form-check-input item_penjualan" type="checkbox" name="id_transaksi_jual_beli[]" value="'.$id_transaksi_jual_beli.'">
                        </td>
                        <td>
                            <a href="javascript:void(0);" class="text text-decoration-underline" data-bs-toggle="modal" data-bs-target="#ModalDetailPenjualan" data-id="'.$id_transaksi_jual_beli.'">
                                <small>'.$TanggalTransaksi.'</small>
                            </a>
                        </td>
                        <td>
                            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetailAnggota" data-id="'.$id_anggota.'" data-mode="List" class="text-success text-decoration-underline">
                                <small class="text text-success">
                                    <i>'.$nama_anggota.'</i>
                                </small>
                            </a>
                        </td>
                        <td>'.$label_kategori.'</td>
                        <td><small>'.$total_rp.'</small></td>
                        <td><small>'.$cash_rp.'</small></td>
                        <td><small>'.$total_angsuran_rp.'</small></td>
                        <td><small>'.$sisa_pembayaran_rp.'</small></td>
                        <td>'.$label_status.'</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-floating btn-success" data-bs-toggle="modal" data-bs-target="#ModalPembayaranPiutangPenjualan" data-id="'.$id_transaksi_jual_beli.'" title="Bayar Piutang Penjualan">
                                <i class="bi bi-check"></i>
                            </button>
                        </td>
                    </tr>
                ';
                $no++;
            }
            mysqli_stmt_close($query);
            $JmlHalaman = ceil($jml_data/$batas); 
        }
    }
?>

<script>
    //Creat Javascript Variabel
    var page_count=<?php echo $JmlHalaman; ?>;
    var curent_page=<?php echo $page; ?>;
    
    //Put Into Pagging Element
    $('#page_info_penjualan').html('Page '+curent_page+' Of '+page_count+'');
    
    //Set Pagging Button
    if(curent_page==1){
        $('#prev_button_penjualan').prop('disabled', true);
    }else{
        $('#prev_button_penjualan').prop('disabled', false);
    }
    if(page_count<=curent_page){
        $('#next_button_penjualan').prop('disabled', true);
    }else{
        $('#next_button_penjualan').prop('disabled', false);
    }

    $(document).ready(function() {
         // Ketika checkbox "check_all_penjualan" dicentang atau dicopot
         console.log("Document is ready.");

        // Ketika checkbox "check_all_penjualan" dicentang atau dicopot
        $('#check_all_penjualan').change(function() {
            console.log('#check_all_penjualan checkbox changed. Checked:', $(this).prop('checked'));
            if ($(this).prop('checked')) {
                // Jika "check_all_penjualan" dicentang, semua checkbox item_penjualan dicentang
                $('.item_penjualan').prop('checked', true);
            } else {
                // Jika "check_all_penjualan" dicopot, semua checkbox item_penjualan dicopot
                $('.item_penjualan').prop('checked', false);
            }
        });

        // Ketika ada perubahan pada salah satu checkbox item_penjualan
        $(document).on('change', '.item_penjualan', function() {
            console.log('An item checkbox was changed.');
            // Cek apakah semua checkbox item_penjualan dicentang
            if ($('.item_penjualan:checked').length === $('.item_penjualan').length) {
                // Jika semua dicentang, centang checkbox check_all_penjualan
                $('#check_all_penjualan').prop('checked', true);
            } else {
                // Jika tidak semua dicentang, hapus centang pada checkbox check_all_penjualan
                $('#check_all_penjualan').prop('checked', false);
            }
        });
    });
</script>

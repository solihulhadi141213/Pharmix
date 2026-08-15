<?php
    //koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    date_default_timezone_set("Asia/Jakarta");
    $JmlHalaman=0;
    $page=0;
    $allowed_order_by = ['id_akses_fitur','kategori','nama','kode','keterangan'];
    $allowed_keyword_by = ['id_akses_fitur','kategori','nama','kode','keterangan'];
    //Validasi Akses
    if(empty($SessionIdAkses)){
        echo '
            <tr>
                <td colspan="6" class="text-center">
                    <small class="text-danger">Sesi Akses Sudah Berakhir! Silahkan Login Ulang!</small>
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
            $OrderBy="id_akses_fitur";
        }
        //Atur Page
        if(!empty($_POST['page'])){
            $page=$_POST['page'];
            $posisi = ( $page - 1 ) * $batas;
        }else{
            $page="1";
            $posisi = 0;
        }
        if(!in_array($OrderBy, $allowed_order_by, true)){
            $OrderBy = "id_akses_fitur";
        }
        if(!in_array($ShortBy, ['ASC','DESC'], true)){
            $ShortBy = "DESC";
        }
        if(!empty($keyword_by) && !in_array($keyword_by, $allowed_keyword_by, true)){
            $keyword_by = "";
        }
        if(empty($keyword_by)){
            if(empty($keyword)){
                $stmt_jml = mysqli_prepare($Conn, "SELECT COUNT(id_akses_fitur) AS jml_data FROM akses_fitur");
            }else{
                $stmt_jml = mysqli_prepare($Conn, "SELECT COUNT(id_akses_fitur) AS jml_data FROM akses_fitur WHERE kategori LIKE ? OR nama LIKE ? OR kode LIKE ? OR keterangan LIKE ?");
                mysqli_stmt_bind_param($stmt_jml, "ssss", $keyword_like, $keyword_like, $keyword_like, $keyword_like);
            }
        }else{
            if(empty($keyword)){
                $stmt_jml = mysqli_prepare($Conn, "SELECT COUNT(id_akses_fitur) AS jml_data FROM akses_fitur");
            }else{
                $sql_jml = "SELECT COUNT(id_akses_fitur) AS jml_data FROM akses_fitur WHERE $keyword_by LIKE ?";
                $stmt_jml = mysqli_prepare($Conn, $sql_jml);
                mysqli_stmt_bind_param($stmt_jml, "s", $keyword_like);
            }
        }
        mysqli_stmt_execute($stmt_jml);
        $result_jml = mysqli_stmt_get_result($stmt_jml);
        $row_jml = mysqli_fetch_assoc($result_jml);
        $jml_data = isset($row_jml['jml_data']) ? (int) $row_jml['jml_data'] : 0;
        mysqli_stmt_close($stmt_jml);
        //Mengatur Halaman
        $JmlHalaman = ceil($jml_data/$batas); 
        if(empty($jml_data)){
            echo '
                <tr>
                    <td colspan="6" class="text-center">
                        <small class="text-danger">Tidak Ada Data Fitur Aplikasi Yang Ditampilkan!</small>
                    </td>
                </tr>
            ';
        }else{
            $no = 1+$posisi;
            //KONDISI PENGATURAN MASING FILTER
            if(empty($keyword_by)){
                if(empty($keyword)){
                    $sql = "SELECT af.*, COALESCE(ap.jumlah_pengguna, 0) AS jumlah_pengguna
                            FROM akses_fitur af
                            LEFT JOIN (
                                SELECT id_akses_fitur, COUNT(id_akses) AS jumlah_pengguna
                                FROM akses_ijin
                                GROUP BY id_akses_fitur
                            ) ap ON ap.id_akses_fitur = af.id_akses_fitur
                            ORDER BY af.$OrderBy $ShortBy
                            LIMIT ?, ?";
                    $query = mysqli_prepare($Conn, $sql);
                    mysqli_stmt_bind_param($query, "ii", $posisi, $batas);
                }else{
                    $sql = "SELECT af.*, COALESCE(ap.jumlah_pengguna, 0) AS jumlah_pengguna
                            FROM akses_fitur af
                            LEFT JOIN (
                                SELECT id_akses_fitur, COUNT(id_akses) AS jumlah_pengguna
                                FROM akses_ijin
                                GROUP BY id_akses_fitur
                            ) ap ON ap.id_akses_fitur = af.id_akses_fitur
                            WHERE af.kategori LIKE ? OR af.nama LIKE ? OR af.kode LIKE ? OR af.keterangan LIKE ?
                            ORDER BY af.$OrderBy $ShortBy
                            LIMIT ?, ?";
                    $query = mysqli_prepare($Conn, $sql);
                    mysqli_stmt_bind_param($query, "ssssii", $keyword_like, $keyword_like, $keyword_like, $keyword_like, $posisi, $batas);
                }
            }else{
                if(empty($keyword)){
                    $sql = "SELECT af.*, COALESCE(ap.jumlah_pengguna, 0) AS jumlah_pengguna
                            FROM akses_fitur af
                            LEFT JOIN (
                                SELECT id_akses_fitur, COUNT(id_akses) AS jumlah_pengguna
                                FROM akses_ijin
                                GROUP BY id_akses_fitur
                            ) ap ON ap.id_akses_fitur = af.id_akses_fitur
                            ORDER BY af.$OrderBy $ShortBy
                            LIMIT ?, ?";
                    $query = mysqli_prepare($Conn, $sql);
                    mysqli_stmt_bind_param($query, "ii", $posisi, $batas);
                }else{
                    $sql = "SELECT af.*, COALESCE(ap.jumlah_pengguna, 0) AS jumlah_pengguna
                            FROM akses_fitur af
                            LEFT JOIN (
                                SELECT id_akses_fitur, COUNT(id_akses) AS jumlah_pengguna
                                FROM akses_ijin
                                GROUP BY id_akses_fitur
                            ) ap ON ap.id_akses_fitur = af.id_akses_fitur
                            WHERE af.$keyword_by LIKE ?
                            ORDER BY af.$OrderBy $ShortBy
                            LIMIT ?, ?";
                    $query = mysqli_prepare($Conn, $sql);
                    mysqli_stmt_bind_param($query, "sii", $keyword_like, $posisi, $batas);
                }
            }
            mysqli_stmt_execute($query);
            $result = mysqli_stmt_get_result($query);
            while ($data = mysqli_fetch_array($result)) {
                $id_akses_fitur= $data['id_akses_fitur'];
                $kategori= $data['kategori'];
                $nama= $data['nama'];
                $kode= $data['kode'];
                $keterangan= $data['keterangan'];
                //Jumlah Pengguna
                $JumlahPengguna = (int) $data['jumlah_pengguna'];
                if(empty($JumlahPengguna)){
                    $label_jumlah_pengguna='<span class="badge badge-danger">NULL</span>';
                }else{
                    $label_jumlah_pengguna='<span class="badge badge-success">'.$JumlahPengguna.' Orang</span>';
                }
                echo '
                    <tr>
                        <td><small>'.$no.'</small></td>
                        <td>
                            <a href="javascript:void(0);" class="text text-decoration-underline" data-bs-toggle="modal" data-bs-target="#ModalDetailFitur" data-id="'.$id_akses_fitur.'">
                                <small>'.$nama.'</small>
                            </a>
                        </td>
                        <td><small>'.$kategori.'</small></td>
                        <td><small class="text-muted">'.$kode.'</small></td>
                        <td><small>'.$label_jumlah_pengguna.'</small></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-secondary btn-floating"  data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" style="">
                                <li class="dropdown-header text-start">
                                    <h6>Option</h6>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalDetailFitur" data-id="'.$id_akses_fitur.'">
                                        <i class="bi bi-info-circle"></i> Detail
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalEditFitur" data-id="'.$id_akses_fitur.'">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalHapusFitur" data-id="'.$id_akses_fitur.'">
                                        <i class="bi bi-x"></i> Hapus
                                    </a>
                                </li>
                            </ul>
                        </td>
                    </tr>
                ';
                $no++;
            }
            mysqli_stmt_close($query);
        }
    }
?>
<script>
    //Creat Javascript Variabel
    var page_count=<?php echo $JmlHalaman; ?>;
    var curent_page=<?php echo $page; ?>;
    
    //Put Into Pagging Element
    $('#page_info').html('Page '+curent_page+' Of '+page_count+'');
    
    //Set Pagging Button
    if(curent_page==1){
        $('#prev_button').prop('disabled', true);
    }else{
        $('#prev_button').prop('disabled', false);
    }
    if(page_count<=curent_page){
        $('#next_button').prop('disabled', true);
    }else{
        $('#next_button').prop('disabled', false);
    }
</script>

<?php
    //koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    date_default_timezone_set("Asia/Jakarta");
    $JmlHalaman=0;
    $page=0;
    
    //Validasi Akses
    if(empty($SessionIdAkses)){
        echo '
            <tr>
                <td colspan="9" class="text-center">
                    <small class="text-danger">Sesi Akses Sudah Berakhir! Silahkan Login Ulang!</small>
                </td>
            </tr>
        ';
        exit;
    }
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
        $OrderBy="id_anggota";
    }
    //Atur Page
    if(!empty($_POST['page'])){
        $page=$_POST['page'];
        $posisi = ( $page - 1 ) * $batas;
    }else{
        $page="1";
        $posisi = 0;
    }
    if(empty($keyword_by)){
        if(empty($keyword)){
            $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT id_anggota  FROM anggota "));
        }else{
            $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT id_anggota  FROM anggota WHERE id_pasien like '%$keyword%' OR nik like '%$keyword%' OR nama like '%$keyword%' OR kontak like '%$keyword%' OR gender like '%$keyword%'"));
        }
    }else{
        if(empty($keyword)){
            $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT id_anggota  FROM anggota "));
        }else{
            $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT id_anggota  FROM anggota WHERE $keyword_by like '%$keyword%'"));
        }
    }
    //Mengatur Halaman
    $JmlHalaman = ceil($jml_data/$batas); 
    if(empty($jml_data)){
        echo '
            <tr>
                <td colspan="9" class="text-center">
                    <small class="text-danger">Tidak Ada Data Fitur Aplikasi Yang Ditampilkan!</small>
                </td>
            </tr>
        ';
    }else{
        $no = 1+$posisi;
        //KONDISI PENGATURAN MASING FILTER
        if(empty($keyword_by)){
            if(empty($keyword)){
                $query = mysqli_query($Conn, "SELECT*FROM anggota  ORDER BY $OrderBy $ShortBy LIMIT $posisi, $batas");
            }else{
                $query = mysqli_query($Conn, "SELECT*FROM anggota  WHERE id_pasien like '%$keyword%' OR nik like '%$keyword%' OR nama like '%$keyword%' OR kontak like '%$keyword%' OR gender like '%$keyword%' ORDER BY $OrderBy $ShortBy LIMIT $posisi, $batas");
            }
        }else{
            if(empty($keyword)){
                $query = mysqli_query($Conn, "SELECT*FROM anggota  ORDER BY $OrderBy $ShortBy LIMIT $posisi, $batas");
            }else{
                $query = mysqli_query($Conn, "SELECT*FROM anggota  WHERE $keyword_by like '%$keyword%' ORDER BY $OrderBy $ShortBy LIMIT $posisi, $batas");
            }
        }
        while ($data = mysqli_fetch_array($query)) {
           $id_anggota = $data['id_anggota'];
            $id_pasien  = $data['id_pasien'];
            $nik        = $data['nik'];
            $nama       = $data['nama'];
            $kontak     = $data['kontak'];
            $gender     = $data['gender'];
            $creat_at   = $data['creat_at'];
            $id_ihs     = $data['id_ihs'] ?? '-';

            // Sensor 3 digit terakhir NIK
            if (!empty($nik)) {
                $nik = substr($nik, 0, -3) . '***';
            }

            // Sensor 3 digit terakhir kontak
            if (!empty($kontak)) {
                $kontak = substr($kontak, 0, -3) . '***';
            }

            // Potong ID IHS
            if (!empty($id_ihs) && $id_ihs !== '-') {
                $id_ihs = strlen($id_ihs) > 12
                    ? substr($id_ihs, 0, 12) . '...'
                    : $id_ihs;
            }

            // Routing Gender
            if($gender=="Male"){
                $label_gender='<span class="badge badge-warning">L</span>';
            }else{
                $label_gender='<span class="badge badge-success">P</span>';
            }

            // Format tanggal daftar
            $creat_at = date('d F Y',strtotime($creat_at));
            
            echo '
                <tr>
                    <td><small>'.$no.'</small></td>
                    <td>
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetail" data-id="'.$id_anggota .'">
                            <small>'.$id_pasien.'</small>
                        </a>
                    </td>
                    <td><small>'.$nama.'</small></td>
                    <td>'.$label_gender.'</td>
                    <td><small class="text-muted">'.$nik.'</small></td>
                    <td><small class="text-muted">'.$kontak.'</small></td>
                    <td><small class="text-muted">'.$creat_at.'</small></td>
                    <td><small class="text-muted">'.$id_ihs.'</small></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-secondary btn-floating"  data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" style="">
                            <li class="dropdown-header text-start">
                                <h6>Option</h6>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalDetail" data-id="'.$id_anggota .'">
                                    <i class="bi bi-info-circle"></i> Detail
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalEdit" data-id="'.$id_anggota .'">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalDelete" data-id="'.$id_anggota .'">
                                    <i class="bi bi-x"></i> Delete
                                </a>
                            </li>
                        </ul>
                    </td>
                </tr>
            ';
            $no++;
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
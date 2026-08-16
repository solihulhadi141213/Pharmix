<?php
    //koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    
    //inisiasi Variabe;
    $JmlHalaman = 0;
    $page       = 1;

    // Validasi Sesi Akses
    if(empty($SessionIdAkses)){
        echo '
            <tr>
                <td colspan="6" class="text-center text-danger">
                    Sesi Akses Sudah Berakhir! Silahkan Login Ulang
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
        if($ShortBy=="ASC"){
            $NextShort="DESC";
        }else{
            $NextShort="ASC";
        }
    }else{
        $ShortBy="DESC";
        $NextShort="ASC";
    }
    //OrderBy
    if(!empty($_POST['OrderBy'])){
        $OrderBy=$_POST['OrderBy'];
    }else{
        $OrderBy="akses";
    }
    //Atur Page
    if(!empty($_POST['page'])){
        $page=$_POST['page'];
        $posisi = ( $page - 1 ) * $batas;
    }else{
        $page="1";
        $posisi = 0;
    }

    // Hitung Jumlah Data
    if(empty($keyword_by)){
        if(empty($keyword)){
            $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akses_entitas"));
        }else{
            $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akses_entitas WHERE akses like '%$keyword%' OR keterangan like '%$keyword%'"));
        }
    }else{
        if(empty($keyword)){
            $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akses_entitas"));
        }else{
            $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akses_entitas WHERE $keyword_by like '%$keyword%'"));
        }
    }
    //Mengatur Halaman
    $JmlHalaman = ceil($jml_data/$batas); 
    $prev=$page-1;
    $next=$page+1;
    if($next>$JmlHalaman){
        $next=$page;
    }else{
        $next=$page+1;
    }
    if($prev<"1"){
        $prev="1";
    }else{
        $prev=$page-1;
    }

    // Jika Data Tidak Ada
    if(empty($jml_data)){
        echo '
            <tr>
                <td colspan="6" class="text-center text-danger">
                    Tidak Ada Data Yang Ditampilkan.
                </td>
            </tr>
        ';
        exit;
    }

    // Nomor Baris Dan Posisi
    $no = 1+$posisi;

    //Buat Query Berdasarkan Koneisi
    if(empty($keyword_by)){
        if(empty($keyword)){
            $query = mysqli_query($Conn, "SELECT*FROM akses_entitas ORDER BY $OrderBy $ShortBy LIMIT $posisi, $batas");
        }else{
            $query = mysqli_query($Conn, "SELECT*FROM akses_entitas WHERE akses like '%$keyword%' OR keterangan like '%$keyword%' ORDER BY $OrderBy $ShortBy LIMIT $posisi, $batas");
        }
    }else{
        if(empty($keyword)){
            $query = mysqli_query($Conn, "SELECT*FROM akses_entitas ORDER BY $OrderBy $ShortBy LIMIT $posisi, $batas");
        }else{
            $query = mysqli_query($Conn, "SELECT*FROM akses_entitas WHERE $keyword_by like '%$keyword%' ORDER BY $OrderBy $ShortBy LIMIT $posisi, $batas");
        }
    }
    while ($data = mysqli_fetch_array($query)) {
        $uuid_akses_entitas = $data['uuid_akses_entitas'];
        $akses              = $data['akses'];
        $keterangan         = $data['keterangan'];
        
        //Jumlah
        $JumlahPengguna = mysqli_num_rows(mysqli_query($Conn, "SELECT id_akses FROM akses WHERE akses='$akses'"));
        $JumlahRole     = mysqli_num_rows(mysqli_query($Conn, "SELECT id_akses_referensi FROM akses_referensi WHERE uuid_akses_entitas='$uuid_akses_entitas'"));

        // Tampilkan Baris Sata
        echo '
            <tr>
                <td align="center"><small>'.$no.'</small></td>
                <td align="left">
                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetailEntitias" data-id="'.$uuid_akses_entitas.'">
                        <small>'.$akses.'</small>
                    </a>
                </td>
                <td align="left"><small class="text-muted">'.$keterangan.'</small></td>
                <td align="left"><small class="text-muted">'.$JumlahPengguna.' User</small></td>
                <td align="left"><small class="text-muted">'.$JumlahRole.' Record</small></td>
                <td align="center">
                    <a class="btn btn-sm btn-secondary btn-floating" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" style="">
                        <li class="dropdown-header text-start">
                            <h6>Option</h6>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalDetailEntitias" data-id="'.$uuid_akses_entitas.'">
                                <i class="bi bi-info-circle"></i> Detail
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalEditAksesEntitas" data-id="'.$uuid_akses_entitas.'">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalHapusAksesEntitas" data-id="'.$uuid_akses_entitas.'">
                                <i class="bi bi-x"></i> Hapus
                            </a>
                        </li>
                    </ul>
                </td>
            </tr>
        ';
        $no++;
    }
?>

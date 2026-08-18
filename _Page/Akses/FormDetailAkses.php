<?php
    //Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    //Harus Login Terlebih Dulu
    if(empty($SessionIdAkses)){
        echo '<div class="row">';
        echo '  <div class="col-md-12 mb-3 text-center">';
        echo '      <small>Sesi Login Sudah Berakhir, Silahkan Login Ulang!</small>';
        echo '  </div>';
        echo '</div>';
    }else{
        //Tangkap id_akses
        if(empty($_POST['id_akses'])){
            echo '<div class="row">';
            echo '  <div class="col-md-12 mb-3 text-center">';
            echo '      <small>ID Akses Tidak Boleh Kosong</small>';
            echo '  </div>';
            echo '</div>';
        }else{
            $id_akses=$_POST['id_akses'];
            //Bersihkan Variabel
            $id_akses=validateAndSanitizeInput($id_akses);
            //Buka data askes
            $nama_akses=GetDetailData($Conn,'akses','id_akses',$id_akses,'nama_akses');
            $kontak_akses=GetDetailData($Conn,'akses','id_akses',$id_akses,'kontak_akses');
            $email_akses=GetDetailData($Conn,'akses','id_akses',$id_akses,'email_akses');
            $image_akses=GetDetailData($Conn,'akses','id_akses',$id_akses,'image_akses');
            $akses=GetDetailData($Conn,'akses','id_akses',$id_akses,'akses');
            $datetime_daftar=GetDetailData($Conn,'akses','id_akses',$id_akses,'datetime_daftar');
            $datetime_update=GetDetailData($Conn,'akses','id_akses',$id_akses,'datetime_update');
            //Jumlah menggunakan Prepared Statement
            $stmtActivity = mysqli_prepare($Conn, "SELECT id_akses FROM log WHERE id_akses = ?");
            mysqli_stmt_bind_param($stmtActivity, "s", $id_akses);
            mysqli_stmt_execute($stmtActivity);
            $QryActivity = mysqli_stmt_get_result($stmtActivity);
            $JumlahAktivitas = mysqli_num_rows($QryActivity);
            mysqli_stmt_close($stmtActivity);
            
            $stmtRole = mysqli_prepare($Conn, "SELECT * FROM akses_ijin WHERE id_akses = ?");
            mysqli_stmt_bind_param($stmtRole, "s", $id_akses);
            mysqli_stmt_execute($stmtRole);
            $QryRole = mysqli_stmt_get_result($stmtRole);
            $JumlahRole = mysqli_num_rows($QryRole);
            mysqli_stmt_close($stmtRole);
            //Format Tanggal
            $strtotime1=strtotime($datetime_daftar);
            $strtotime2=strtotime($datetime_update);
            //Menampilkan Tanggal
            $DateDaftar=date('d/m/Y H:i:s T', $strtotime1);
            $DateUpdate=date('d/m/Y H:i:s T', $strtotime2);
            // Set gambar default jika kosong
            if(empty($image_akses)){
                $image_akses="No-Image.png";
            }
?>
            <div class="row mb-3 border-1 border-bottom">
                <div class="col-md-12 text-center mb-4">
                    <img src="image_proxy.php?dir=User&filename=<?php echo $image_akses; ?>" alt="" width="50%" class="rounded-circle">
                </div>
            </div>
            <div class="row mb-3 border-1 border-bottom">
                <div class="col-md-12 mb-4">
                    <div class="row mb-3">
                        <div class="col col-md-4">Nama Lengkap</div>
                        <div class="col col-md-8">
                            <small class="text text-grayish"><?php echo $nama_akses; ?></small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-4">Kontak</div>
                        <div class="col col-md-8">
                            <small class="text text-grayish"><?php echo $kontak_akses; ?></small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-4">Email</div>
                        <div class="col col-md-8">
                            <small class="text text-grayish"><?php echo $email_akses; ?></small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-4">Akses</div>
                        <div class="col col-md-8">
                            <small class="text text-grayish"><?php echo $akses; ?></small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-4">Creat</div>
                        <div class="col col-md-8">
                            <small class="text text-grayish"><?php echo $DateDaftar; ?></small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-4">Update</div>
                        <div class="col col-md-8">
                            <small class="text text-grayish"><?php echo $DateUpdate; ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                // Ambil SEMUA fitur dalam satu query (menghindari N+1 problem)
                $stmtAllFitur = mysqli_prepare($Conn, "SELECT id_akses_fitur, kategori, kode, nama, keterangan FROM akses_fitur ORDER BY kategori ASC, nama ASC");
                mysqli_stmt_execute($stmtAllFitur);
                $QryAllFitur = mysqli_stmt_get_result($stmtAllFitur);
                
                // Kelompokkan fitur berdasarkan kategori di PHP
                $FiturByKategori = [];
                while ($DataFitur = mysqli_fetch_array($QryAllFitur)) {
                    $kategori = $DataFitur['kategori'];
                    if (!isset($FiturByKategori[$kategori])) {
                        $FiturByKategori[$kategori] = [];
                    }
                    $FiturByKategori[$kategori][] = $DataFitur;
                }
                mysqli_stmt_close($stmtAllFitur);
                
                // Ambil semua izin akses user dalam satu query (menghindari N+1 problem di IjinAksesSaya)
                $stmtUserAccess = mysqli_prepare($Conn, "SELECT kode FROM akses_ijin WHERE id_akses = ?");
                mysqli_stmt_bind_param($stmtUserAccess, "s", $id_akses);
                mysqli_stmt_execute($stmtUserAccess);
                $QryUserAccess = mysqli_stmt_get_result($stmtUserAccess);
                
                // Simpan izin akses ke array untuk lookup O(1)
                $UserAccessCodes = [];
                while ($AccessData = mysqli_fetch_array($QryUserAccess)) {
                    $UserAccessCodes[] = $AccessData['kode'];
                }
                mysqli_stmt_close($stmtUserAccess);
                
                // Tampilkan fitur per kategori
                $no = 1;
                echo '<div class="row mb-3">';
                foreach ($FiturByKategori as $KategoriList => $FiturList) {
                    echo '  <div class="col-md-12">';
                    echo '     <small class="credit">'.$no.'. '.$KategoriList.'</small><br>';
                    echo '      <ul>';
                    foreach ($FiturList as $DataFitur) {
                        $NamaFitur = $DataFitur['nama'];
                        $KodeFitur = $DataFitur['kode'];
                        
                        // Cek apakah user punya akses ini (dari array, bukan query)
                        if (in_array($KodeFitur, $UserAccessCodes)) {
                            echo '<li><small class="text text-grayish">'.$NamaFitur.' <i class="bi bi-check text-success"></i></small></li>';
                        } else {
                            echo '<li><small class="text text-grayish">'.$NamaFitur.'</small></li>';
                        }
                    }
                    echo '      </ul>';
                    echo '  </div>';
                    $no++;
                }
                echo '</div>';
            ?>
<?php 
        } 
    } 
?>
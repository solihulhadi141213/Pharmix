<?php
    //Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";

    if(empty($SessionIdAkses)){
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opss!</b> Sesi Akses Sudah Berakhir! Silahkan Login Ulang!
                </small>
            </div>
        ';
        exit;
    }
    if(empty($_POST['id_jurnal'])){
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opss!</b> ID Jurnal Tidak Boleh Kosong!
                </small>
            </div>
        ';
        exit;
    }
    $id_jurnal=$_POST['id_jurnal'];
            
    //Bersihkan Variabe;
    $id_jurnal=validateAndSanitizeInput($id_jurnal);
            
    //Buka Detail Jurnal
    $Qry = $Conn->prepare("SELECT * FROM jurnal WHERE id_jurnal = ?");
    $Qry->bind_param("i", $id_jurnal);
    if (!$Qry->execute()) {
        $error=$Conn->error;
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opss!</b> Terjadi Kesalahan Pada Saat Membuka Data! <br>Keterangan : '.$error.'
                </small>
            </div>
        ';
        exit;
    }

    $Result = $Qry->get_result();
    $Data = $Result->fetch_assoc();
    $Qry->close();
        
    //Buat Variabel
    $kategori       = $Data['kategori'];
    $uuid           = $Data['uuid'];
    $tanggal        = $Data['tanggal'];
    $kode_perkiraan = $Data['kode_perkiraan'];
    $nama_perkiraan = $Data['nama_perkiraan'];
    $d_k            = $Data['d_k'];
    
          
    //Format tanggal
    $strtotime=strtotime($tanggal);
    $TanggalFormat=date('d/m/Y',$strtotime);

    //Format Rupiah
    if(!empty($Data['nilai'])){
        $nilai          = $Data['nilai'];
        $nilai = "Rp " . number_format($nilai,0,',','.');
    }else{
        $nilai = 0;
        $nilai = "Rp " . number_format($nilai,0,',','.');
    }
    
?>
<input type="hidden" name="id_jurnal" value="<?php echo $id_jurnal; ?>">
<div class="row mb-3">
    <div class="col-md-6">Kode Referensi</div>
    <div class="col-md-6">
        <small class="text text-grayish"><?php echo $uuid; ?></small>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-6">Kategori</div>
    <div class="col-md-6">
        <small class="text text-grayish"><?php echo $kategori; ?></small>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-6">Tanggal</div>
    <div class="col-md-6">
        <small class="text text-grayish"><?php echo $TanggalFormat; ?></small>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-6">Kode Akun</div>
    <div class="col-md-6">
        <small class="text text-grayish"><?php echo $kode_perkiraan; ?></small>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-6">Nama Akun</div>
    <div class="col-md-6">
        <small class="text text-grayish"><?php echo $nama_perkiraan; ?></small>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-6">Debet/Kredit</div>
    <div class="col-md-6">
        <small class="text text-grayish"><?php echo $d_k; ?></small>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-6">Nilai (Rp)</div>
    <div class="col-md-6">
        <small class="text text-grayish"><?php echo $nilai; ?></small>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-danger text-center">
            <small>
                <b>Penting!</b> <br>
                Data yang sudah dihapus tidak akan bisa dikembalikan lagi. <br>
                <i>Apakah Anda Yakin akan Menghapus Jurnal Tersebut?</i>
            </small>
        </div>
    </div>
</div>

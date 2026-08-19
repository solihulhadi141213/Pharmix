<?php
    // Json Format
    header('Content-Type: application/json; charset=utf-8');

    //Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Sesi Akses
    if (empty($SessionIdAkses)) {
        echo json_encode([
            "status" => "error",
            "message" => "Sesi Akses Sudah Berakhir. Silahkan Login Ulang!",
            "html"   => ''
        ]);
        exit;
    }

    //Tangkap 'id_supplier'
    if(empty($_POST['id_supplier'])){
        echo json_encode([
            "status" => "error",
            "message" => "ID Supplier Tidak Boleh Kosong!",
            "html"   => ''
        ]);
        exit;
    }

    $id_supplier = (int) $_POST['id_supplier'];

    //Ambil detail supplier dan total volume transaksi dalam 1 query
    $sql = "
        SELECT
            s.id_supplier,
            s.nama_supplier,
            s.alamat_supplier,
            s.email_supplier,
            s.kontak_supplier,
            s.pic,
            s.npwp,
            COALESCE(SUM(t.total), 0) AS total_transaksi
        FROM supplier s
        LEFT JOIN transaksi_jual_beli t ON t.id_supplier = s.id_supplier
        WHERE s.id_supplier = ?
        GROUP BY
            s.id_supplier,
            s.nama_supplier,
            s.alamat_supplier,
            s.email_supplier,
            s.kontak_supplier,
            s.pic,
            s.npwp
        LIMIT 1
    ";
    $stmt = $Conn->prepare($sql);
    if(!$stmt){
        echo json_encode([
            "status" => "error",
            "message" => "Gagal menyiapkan data supplier!",
            "html"   => ''
        ]);
        exit;
    }
    $stmt->bind_param("i", $id_supplier);
    $stmt->execute();
    $result = $stmt->get_result();
    $DataSupplier = $result->fetch_assoc();
    $stmt->close();

    if(empty($DataSupplier)){
        echo json_encode([
            "status" => "error",
            "message" => "Data supplier tidak ditemukan!",
            "html"   => ''
        ]);
        exit;
    }

    $id_supplier = $DataSupplier['id_supplier'];
    $nama_supplier = $DataSupplier['nama_supplier'];
    if(empty($DataSupplier['alamat_supplier'])){
        $alamat_supplier='';
    }else{
        $alamat_supplier= $DataSupplier['alamat_supplier'];
    }
    if(empty($DataSupplier['email_supplier'])){
        $email_supplier='';
    }else{
        $email_supplier= $DataSupplier['email_supplier'];
    }
    if(empty($DataSupplier['kontak_supplier'])){
        $kontak_supplier='';
    }else{
        $kontak_supplier= $DataSupplier['kontak_supplier'];
    }
    if(empty($DataSupplier['pic'])){
        $pic='';
    }else{
        $pic= $DataSupplier['pic'];
    }
    if(empty($DataSupplier['npwp'])){
        $npwp='';
    }else{
        $npwp= $DataSupplier['npwp'];
    }

    //Hitung volume transaksi
    $jumlah_transaksi = (float) ($DataSupplier['total_transaksi'] ?? 0);
    $VolumeTransaksi  = "Rp " . number_format($jumlah_transaksi,0,',','.');

    echo json_encode([
        "status" => "success",
        "message" => "Form Berhasil Ditampilkan",
        "html"   => '
            <input type="hidden" name="id_supplier" id="id_supplier" value="'.$id_supplier.'">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="nama_supplier_edit"><span class="text-danger">*</span> Nama Supplier</label>
                    <input type="text" name="nama_supplier" id="nama_supplier_edit" class="form-control" required value="'.$nama_supplier.'">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="email_supplier_edit">Email Perusahaan</label>
                    <input type="email" name="email_supplier" id="email_supplier_edit" class="form-control" placeholder="email@domain.com" value="'.$email_supplier.'">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="kontak_supplier_edit">Kontak Perusahaan</label>
                    <input type="text" name="kontak_supplier" id="kontak_supplier_edit" class="form-control" placeholder="62" value="'.$kontak_supplier.'">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="alamat_supplier_edit">Alamat Kantor</label>
                    <input type="text" name="alamat_supplier" id="alamat_supplier_edit" class="form-control" placeholder="Contoh : Jalan Anggrek 4 Nomor 5 Kabupaten Kuningan-Jawa Barat" value="'.$alamat_supplier.'">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="pic_edit">PIC <i>(Person In Charge)</i></label>
                    <input type="text" name="pic" id="pic_edit" class="form-control" value="'.$pic.'">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="npwp_edit">NPWP (Nomor Pokok Wajib Pajak)</label>
                    <input type="text" name="npwp" id="npwp_edit" class="form-control" value="'.$npwp.'">
                </div>
            </div>
        '
    ]);
    exit;
?>

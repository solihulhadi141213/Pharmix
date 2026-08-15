<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'eQhEWIf1fV6xwMNr8J9');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
?>
    <div class="pagetitle">
        <h1>
            <a href="">
                <i class="bi bi-gear"></i> Auto Jurnal</a>
            </a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active"> Auto Jurnal</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row">
            <div class="col-md-12">
                <?php
                    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">';
                    echo '  <small>';
                    echo '      Berikut ini adalah halaman pengaturan <i>Auto Jurnal</i>.';
                    echo '      Parameter berikut ini digunakan untuk mengatur alur pembukuan jurnal keuangan secara otomatis berdasarkan transaksi yang berlangsung.';
                    echo '      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    echo '  </small>';
                    echo '</div>';
                ?>
            </div>
        </div>
        
        <?php
            //Penjualan
            $QryAutoJurnalPenjualan= mysqli_query($Conn,"SELECT * FROM setting_autojurnal_jual_beli WHERE kategori='Penjualan'")or die(mysqli_error($Conn));
            $DataAutoJurnalPenjualan = mysqli_fetch_array($QryAutoJurnalPenjualan);
            if(!empty($DataAutoJurnalPenjualan['id_autojurnal_jual_beli'])){
                $id_autojurnal_penjualan= $DataAutoJurnalPenjualan['id_autojurnal_jual_beli'];
                $DebetPenjualan= $DataAutoJurnalPenjualan['debet'];
                $KreditPenjualan= $DataAutoJurnalPenjualan['kredit'];
                $HppPenjualan= $DataAutoJurnalPenjualan['hpp'];
                $PersediaanPenjualan= $DataAutoJurnalPenjualan['persediaan'];
                $UtangPiutangPenjualan= $DataAutoJurnalPenjualan['utang_piutang'];
            }else{
                $id_autojurnal_penjualan="";
                $DebetPenjualan="";
                $KreditPenjualan="";
                $HppPenjualan="";
                $PersediaanPenjualan="";
                $UtangPiutangPenjualan="";
            }

            //Pembelian
            $QryAutoJurnalPembelian= mysqli_query($Conn,"SELECT * FROM setting_autojurnal_jual_beli WHERE kategori='Pembelian'")or die(mysqli_error($Conn));
            $DataAutoJurnalPembelian = mysqli_fetch_array($QryAutoJurnalPembelian);
            if(!empty($DataAutoJurnalPembelian['id_autojurnal_jual_beli'])){
                $id_autojurnal_pembelian= $DataAutoJurnalPembelian['id_autojurnal_jual_beli'];
                $DebetPembelian= $DataAutoJurnalPembelian['debet'];
                $KreditPembelian= $DataAutoJurnalPembelian['kredit'];
                $HppPembelian= $DataAutoJurnalPembelian['hpp'];
                $PersediaanPembelian= $DataAutoJurnalPembelian['persediaan'];
                $UtangPiutangPembelian= $DataAutoJurnalPembelian['utang_piutang'];
            }else{
                $id_autojurnal_pembelian="";
                $DebetPembelian="";
                $KreditPembelian="";
                $HppPembelian="";
                $PersediaanPembelian="";
                $UtangPiutangPembelian="";
            }
        ?>
        <div class="row">
            <div class="col-md-12">
                <form action="javascript:void(0);" id="ProssesSimpanAutoJurnalJualBeli">
                    <div class="card">
                        <div class="card-header">
                            <b class="card-title"># Auto Jurnal Transaksi Jual/Beli</b>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-12 mb-3">
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            1. Transaksi Penjualan
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4">
                                            <label for="debet_penjualan">
                                                <small>Akun Kas</small>
                                            </label>
                                        </div>
                                        <div class="col-8">
                                            <select name="debet_penjualan" id="debet_penjualan" class="form-control">
                                                <?php
                                                    echo '<option value="">Pilih</option>';
                                                    // Query untuk mengambil akun level 1 (group utama)
                                                    $QryGroupUtama = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE level='1' ORDER BY nama");
                                                    while ($GroupUtama = mysqli_fetch_array($QryGroupUtama)) {
                                                        $id_perkiraan_utama = $GroupUtama['id_perkiraan'];
                                                        $kode_utama = $GroupUtama['kode'];
                                                        $nama_utama = $GroupUtama['nama'];
                                                        $saldo_normal_utama = $GroupUtama['saldo_normal'];
                                                        // Tampilkan group utama
                                                        echo '<optgroup label="'.$nama_utama.' ('.$saldo_normal_utama.')">';
                                                        // Query untuk mengambil anak group dari group utama berdasarkan kode
                                                        $QryAnakGroup = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE kode LIKE '$kode_utama%' AND level != '1' ORDER BY nama");
                                                        while ($AnakGroup = mysqli_fetch_array($QryAnakGroup)) {
                                                            $id_perkiraan = $AnakGroup['id_perkiraan'];
                                                            $nama_perkiraan = $AnakGroup['nama'];
                                                            $saldo_normal = $AnakGroup['saldo_normal'];
                                                            $kode = $AnakGroup['kode'];
                                                            $level = $AnakGroup['level'];
                                                            $LevelTerbawah = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akun_perkiraan WHERE kd$level='$kode'"));
                                                            // Tampilkan anak group
                                                            if($LevelTerbawah=="1"){
                                                                if($DebetPenjualan==$id_perkiraan){
                                                                    echo '<option selected value="'.$id_perkiraan.'">'.$nama_perkiraan.' ('.$saldo_normal.')</option>';
                                                                }else{
                                                                    echo '<option value="'.$id_perkiraan.'">'.$nama_perkiraan.' ('.$saldo_normal.')</option>';
                                                                }
                                                            }
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4">
                                            <label for="kredit_penjualan">
                                                <small>Akun Pendapatan Penjualan</small>
                                            </label>
                                        </div>
                                        <div class="col-8">
                                            <select name="kredit_penjualan" id="kredit_penjualan" class="form-control">
                                                <?php
                                                    echo '<option value="">Pilih</option>';
                                                    // Query untuk mengambil akun level 1 (group utama)
                                                    $QryGroupUtama = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE level='1' ORDER BY nama");
                                                    while ($GroupUtama = mysqli_fetch_array($QryGroupUtama)) {
                                                        $id_perkiraan_utama = $GroupUtama['id_perkiraan'];
                                                        $kode_utama = $GroupUtama['kode'];
                                                        $nama_utama = $GroupUtama['nama'];
                                                        $saldo_normal_utama = $GroupUtama['saldo_normal'];
                                                        // Tampilkan group utama
                                                        echo '<optgroup label="'.$nama_utama.' ('.$saldo_normal_utama.')">';
                                                        // Query untuk mengambil anak group dari group utama berdasarkan kode
                                                        $QryAnakGroup = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE kode LIKE '$kode_utama%' AND level != '1' ORDER BY nama");
                                                        while ($AnakGroup = mysqli_fetch_array($QryAnakGroup)) {
                                                            $id_perkiraan = $AnakGroup['id_perkiraan'];
                                                            $nama_perkiraan = $AnakGroup['nama'];
                                                            $saldo_normal = $AnakGroup['saldo_normal'];
                                                            $kode = $AnakGroup['kode'];
                                                            $level = $AnakGroup['level'];
                                                            $LevelTerbawah = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akun_perkiraan WHERE kd$level='$kode'"));
                                                            // Tampilkan anak group
                                                            if($LevelTerbawah=="1"){
                                                                if($KreditPenjualan==$id_perkiraan){
                                                                    echo '<option selected value="'.$id_perkiraan.'">'.$nama_perkiraan.' ('.$saldo_normal.')</option>';
                                                                }else{
                                                                    echo '<option value="'.$id_perkiraan.'">'.$nama_perkiraan.' ('.$saldo_normal.')</option>';
                                                                }
                                                            }
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4">
                                            <label for="hpp_penjualan">
                                                <small>Akun HPP (Harga Pokok Penjualan)</small>
                                            </label>
                                        </div>
                                        <div class="col-8">
                                            <select name="hpp_penjualan" id="hpp_penjualan" class="form-control">
                                                <?php
                                                    echo '<option value="">Pilih</option>';
                                                    // Query untuk mengambil akun level 1 (group utama)
                                                    $QryGroupUtama = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE level='1' ORDER BY nama");
                                                    while ($GroupUtama = mysqli_fetch_array($QryGroupUtama)) {
                                                        $id_perkiraan_utama = $GroupUtama['id_perkiraan'];
                                                        $kode_utama = $GroupUtama['kode'];
                                                        $nama_utama = $GroupUtama['nama'];
                                                        $saldo_normal_utama = $GroupUtama['saldo_normal'];
                                                        // Tampilkan group utama
                                                        echo '<optgroup label="'.$nama_utama.' ('.$saldo_normal_utama.')">';
                                                        // Query untuk mengambil anak group dari group utama berdasarkan kode
                                                        $QryAnakGroup = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE kode LIKE '$kode_utama%' AND level != '1' ORDER BY nama");
                                                        while ($AnakGroup = mysqli_fetch_array($QryAnakGroup)) {
                                                            $id_perkiraan = $AnakGroup['id_perkiraan'];
                                                            $nama_perkiraan = $AnakGroup['nama'];
                                                            $saldo_normal = $AnakGroup['saldo_normal'];
                                                            $kode = $AnakGroup['kode'];
                                                            $level = $AnakGroup['level'];
                                                            $LevelTerbawah = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akun_perkiraan WHERE kd$level='$kode'"));
                                                            // Tampilkan anak group
                                                            if($LevelTerbawah=="1"){
                                                                if($HppPenjualan==$id_perkiraan){
                                                                    echo '<option selected value="'.$id_perkiraan.'">'.$nama_perkiraan.' ('.$saldo_normal.')</option>';
                                                                }else{
                                                                    echo '<option value="'.$id_perkiraan.'">'.$nama_perkiraan.' ('.$saldo_normal.')</option>';
                                                                }
                                                            }
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4">
                                            <label for="persediaan_penjualan">
                                                <small>Akun Persediaan</small>
                                            </label>
                                        </div>
                                        <div class="col-8">
                                            <select name="persediaan_penjualan" id="persediaan_penjualan" class="form-control">
                                                <?php
                                                    echo '<option value="">Pilih</option>';
                                                    // Query untuk mengambil akun level 1 (group utama)
                                                    $QryGroupUtama = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE level='1' ORDER BY nama");
                                                    while ($GroupUtama = mysqli_fetch_array($QryGroupUtama)) {
                                                        $id_perkiraan_utama = $GroupUtama['id_perkiraan'];
                                                        $kode_utama = $GroupUtama['kode'];
                                                        $nama_utama = $GroupUtama['nama'];
                                                        $saldo_normal_utama = $GroupUtama['saldo_normal'];
                                                        // Tampilkan group utama
                                                        echo '<optgroup label="'.$nama_utama.' ('.$saldo_normal_utama.')">';
                                                        // Query untuk mengambil anak group dari group utama berdasarkan kode
                                                        $QryAnakGroup = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE kode LIKE '$kode_utama%' AND level != '1' ORDER BY nama");
                                                        while ($AnakGroup = mysqli_fetch_array($QryAnakGroup)) {
                                                            $id_perkiraan = $AnakGroup['id_perkiraan'];
                                                            $nama_perkiraan = $AnakGroup['nama'];
                                                            $saldo_normal = $AnakGroup['saldo_normal'];
                                                            $kode = $AnakGroup['kode'];
                                                            $level = $AnakGroup['level'];
                                                            $LevelTerbawah = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akun_perkiraan WHERE kd$level='$kode'"));
                                                            // Tampilkan anak group
                                                            if($LevelTerbawah=="1"){
                                                                if($PersediaanPenjualan==$id_perkiraan){
                                                                    echo '<option selected value="'.$id_perkiraan.'">'.$nama_perkiraan.' ('.$saldo_normal.')</option>';
                                                                }else{
                                                                    echo '<option value="'.$id_perkiraan.'">'.$nama_perkiraan.' ('.$saldo_normal.')</option>';
                                                                }
                                                            }
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4">
                                            <label for="utang_piutang_penjualan">
                                                <small>* Akun Piutang</small>
                                            </label>
                                        </div>
                                        <div class="col-8">
                                            <select name="utang_piutang_penjualan" id="utang_piutang_penjualan" class="form-control">
                                                <?php
                                                    echo '<option value="">Pilih</option>';
                                                    // Query untuk mengambil akun level 1 (group utama)
                                                    $QryGroupUtama = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE level='1' ORDER BY nama");
                                                    while ($GroupUtama = mysqli_fetch_array($QryGroupUtama)) {
                                                        $id_perkiraan_utama = $GroupUtama['id_perkiraan'];
                                                        $kode_utama = $GroupUtama['kode'];
                                                        $nama_utama = $GroupUtama['nama'];
                                                        $saldo_normal_utama = $GroupUtama['saldo_normal'];
                                                        // Tampilkan group utama
                                                        echo '<optgroup label="'.$nama_utama.' ('.$saldo_normal_utama.')">';
                                                        // Query untuk mengambil anak group dari group utama berdasarkan kode
                                                        $QryAnakGroup = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE kode LIKE '$kode_utama%' AND level != '1' ORDER BY nama");
                                                        while ($AnakGroup = mysqli_fetch_array($QryAnakGroup)) {
                                                            $id_perkiraan = $AnakGroup['id_perkiraan'];
                                                            $nama_perkiraan = $AnakGroup['nama'];
                                                            $saldo_normal = $AnakGroup['saldo_normal'];
                                                            $kode = $AnakGroup['kode'];
                                                            $level = $AnakGroup['level'];
                                                            $LevelTerbawah = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akun_perkiraan WHERE kd$level='$kode'"));
                                                            // Tampilkan anak group
                                                            if($LevelTerbawah=="1"){
                                                                if($UtangPiutangPenjualan==$id_perkiraan){
                                                                    echo '<option selected value="'.$id_perkiraan.'">'.$nama_perkiraan.' ('.$saldo_normal.')</option>';
                                                                }else{
                                                                    echo '<option value="'.$id_perkiraan.'">'.$nama_perkiraan.' ('.$saldo_normal.')</option>';
                                                                }
                                                            }
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="row mb-3 mt-3">
                                        <div class="col-12">
                                            2. Transaksi Pembelian
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4">
                                            <label for="kredit_pembelian">
                                                <small>Akun Kas</small>
                                            </label>
                                        </div>
                                        <div class="col-8">
                                            <select name="kredit_pembelian" id="kredit_pembelian" class="form-control">
                                                <?php
                                                    echo '<option value="">Pilih</option>';
                                                    // Query untuk mengambil akun level 1 (group utama)
                                                    $QryGroupUtama = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE level='1' ORDER BY nama");
                                                    while ($GroupUtama = mysqli_fetch_array($QryGroupUtama)) {
                                                        $id_perkiraan_utama = $GroupUtama['id_perkiraan'];
                                                        $kode_utama = $GroupUtama['kode'];
                                                        $nama_utama = $GroupUtama['nama'];
                                                        $saldo_normal_utama = $GroupUtama['saldo_normal'];
                                                        // Tampilkan group utama
                                                        echo '<optgroup label="'.$nama_utama.' ('.$saldo_normal_utama.')">';
                                                        // Query untuk mengambil anak group dari group utama berdasarkan kode
                                                        $QryAnakGroup = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE kode LIKE '$kode_utama%' AND level != '1' ORDER BY nama");
                                                        while ($AnakGroup = mysqli_fetch_array($QryAnakGroup)) {
                                                            $id_perkiraan = $AnakGroup['id_perkiraan'];
                                                            $nama_perkiraan = $AnakGroup['nama'];
                                                            $saldo_normal = $AnakGroup['saldo_normal'];
                                                            $kode = $AnakGroup['kode'];
                                                            $level = $AnakGroup['level'];
                                                            $LevelTerbawah = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akun_perkiraan WHERE kd$level='$kode'"));
                                                            // Tampilkan anak group
                                                            if($LevelTerbawah=="1"){
                                                                if($KreditPembelian==$id_perkiraan){
                                                                    echo '<option selected value="'.$id_perkiraan.'">'.$nama_perkiraan.' ('.$saldo_normal.')</option>';
                                                                }else{
                                                                    echo '<option value="'.$id_perkiraan.'">'.$nama_perkiraan.' ('.$saldo_normal.')</option>';
                                                                }
                                                            }
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4">
                                            <label for="debet_pembelian">
                                                <small>Akun Persediaan</small>
                                            </label>
                                        </div>
                                        <div class="col-8">
                                            <select name="debet_pembelian" id="debet_pembelian" class="form-control">
                                                <?php
                                                    echo '<option value="">Pilih</option>';
                                                    // Query untuk mengambil akun level 1 (group utama)
                                                    $QryGroupUtama = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE level='1' ORDER BY nama");
                                                    while ($GroupUtama = mysqli_fetch_array($QryGroupUtama)) {
                                                        $id_perkiraan_utama = $GroupUtama['id_perkiraan'];
                                                        $kode_utama = $GroupUtama['kode'];
                                                        $nama_utama = $GroupUtama['nama'];
                                                        $saldo_normal_utama = $GroupUtama['saldo_normal'];
                                                        // Tampilkan group utama
                                                        echo '<optgroup label="'.$nama_utama.' ('.$saldo_normal_utama.')">';
                                                        // Query untuk mengambil anak group dari group utama berdasarkan kode
                                                        $QryAnakGroup = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE kode LIKE '$kode_utama%' AND level != '1' ORDER BY nama");
                                                        while ($AnakGroup = mysqli_fetch_array($QryAnakGroup)) {
                                                            $id_perkiraan = $AnakGroup['id_perkiraan'];
                                                            $nama_perkiraan = $AnakGroup['nama'];
                                                            $saldo_normal = $AnakGroup['saldo_normal'];
                                                            $kode = $AnakGroup['kode'];
                                                            $level = $AnakGroup['level'];
                                                            $LevelTerbawah = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akun_perkiraan WHERE kd$level='$kode'"));
                                                            // Tampilkan anak group
                                                            if($LevelTerbawah=="1"){
                                                                if($DebetPembelian==$id_perkiraan){
                                                                    echo '<option selected value="'.$id_perkiraan.'">'.$nama_perkiraan.' ('.$saldo_normal.')</option>';
                                                                }else{
                                                                    echo '<option value="'.$id_perkiraan.'">'.$nama_perkiraan.' ('.$saldo_normal.')</option>';
                                                                }
                                                            }
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4">
                                            <label for="utang_piutang_pembelian">
                                                <small>* Akun Utang</small>
                                            </label>
                                        </div>
                                        <div class="col-8">
                                            <select name="utang_piutang_pembelian" id="utang_piutang_pembelian" class="form-control">
                                                <?php
                                                    echo '<option value="">Pilih</option>';
                                                    // Query untuk mengambil akun level 1 (group utama)
                                                    $QryGroupUtama = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE level='1' ORDER BY nama");
                                                    while ($GroupUtama = mysqli_fetch_array($QryGroupUtama)) {
                                                        $id_perkiraan_utama = $GroupUtama['id_perkiraan'];
                                                        $kode_utama = $GroupUtama['kode'];
                                                        $nama_utama = $GroupUtama['nama'];
                                                        $saldo_normal_utama = $GroupUtama['saldo_normal'];
                                                        // Tampilkan group utama
                                                        echo '<optgroup label="'.$nama_utama.' ('.$saldo_normal_utama.')">';
                                                        // Query untuk mengambil anak group dari group utama berdasarkan kode
                                                        $QryAnakGroup = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE kode LIKE '$kode_utama%' AND level != '1' ORDER BY nama");
                                                        while ($AnakGroup = mysqli_fetch_array($QryAnakGroup)) {
                                                            $id_perkiraan = $AnakGroup['id_perkiraan'];
                                                            $nama_perkiraan = $AnakGroup['nama'];
                                                            $saldo_normal = $AnakGroup['saldo_normal'];
                                                            $kode = $AnakGroup['kode'];
                                                            $level = $AnakGroup['level'];
                                                            $LevelTerbawah = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akun_perkiraan WHERE kd$level='$kode'"));
                                                            // Tampilkan anak group
                                                            if($LevelTerbawah=="1"){
                                                                if($UtangPiutangPembelian==$id_perkiraan){
                                                                    echo '<option selected value="'.$id_perkiraan.'">'.$nama_perkiraan.' ('.$saldo_normal.')</option>';
                                                                }else{
                                                                    echo '<option value="'.$id_perkiraan.'">'.$nama_perkiraan.' ('.$saldo_normal.')</option>';
                                                                }
                                                            }
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12" id="NotifikasiSimpanAutoJurnalJualBeli">
                                    <small></small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-md btn-primary btn-rounded">
                                <i class="bi bi-save"></i> Simpan Auto Jurnal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
    </section>
<?php } ?>
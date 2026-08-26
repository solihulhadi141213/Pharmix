<?php
    // Koneksi & Konfigurasi
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/SettingGeneral.php";

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');
    $now = date('Y-m-d H:i:s');

    // Validasi Sesi Login
    if (empty($SessionIdAkses)) {
        echo '
            <div class="alert alert-danger">
                Sesi Akses Sudah Berakhir! Silahkan Login Ulang!
            </div>
        ';
    } elseif (!isset($_POST['id_transaksi_jual_beli']) || empty($_POST['id_transaksi_jual_beli'])) {
        echo '
            <div class="alert alert-danger">
                ID Transaksi Penjualan Tidak Boleh Kosong!
            </div>
        ';
    } else {
        // Ambil Data Transaksi
        $id_transaksi_jual_beli = validateAndSanitizeInput($_POST['id_transaksi_jual_beli']);
        //Buka Data
        $Qry = $Conn->prepare("SELECT * FROM transaksi_jual_beli WHERE id_transaksi_jual_beli = ?");
        $Qry->bind_param("s", $id_transaksi_jual_beli);
        if (!$Qry->execute()) {
            echo '
                <div class="alert alert-danger">
                    Terjadi Kesalahan : '.$Conn->error.'
                </div>
            ';
        } else {
            $Result = $Qry->get_result();
            $Data = $Result->fetch_assoc();
            $Qry->close();
            if (!$Data) {
                echo '
                    <div class="alert alert-danger">
                        Data Transaksi Yang Anda Pilih Tidak Ditemukan Pada Database
                    </div>
                ';
            } else {
                // Ambil Data Transaksi
                $id_supplier = $Data['id_supplier'];
                $kategori    = $Data['kategori'];
                $tanggal     = $Data['tanggal'];
                $subtotal    = pembulatan_nilai($Data['subtotal']);
                $ppn         = pembulatan_nilai($Data['ppn']);
                $diskon      = pembulatan_nilai($Data['diskon']);
                $total       = pembulatan_nilai($Data['total']);
                $cash        = pembulatan_nilai($Data['cash']);
                $kembalian   = pembulatan_nilai($Data['kembalian']);
                $status      = $Data['status'];

                // Format Rupiah
                $subtotal_rp  = "" . number_format($subtotal, 0, ',', '.');
                $ppn_rp       = "" . number_format($ppn, 0, ',', '.');
                $diskon_rp    = "" . number_format($diskon, 0, ',', '.');
                $total_rp     = "" . number_format($total, 0, ',', '.');
                $cash_rp      = "" . number_format($cash, 0, ',', '.');
                $kembalian_rp = "" . number_format($kembalian, 0, ',', '.');

                // Ambil Nama Supplier
                $nama_supplier = (!empty($id_supplier)) ? GetDetailData($Conn, 'supplier', 'id_supplier', $id_supplier, 'nama_supplier') : "-";

                // Ambil Rincian Transaksi
                $list_rincian = [];
                $stmt = $Conn->prepare("SELECT * FROM transaksi_jual_beli_rincian WHERE id_transaksi_jual_beli = ?");
                $stmt->bind_param("s", $id_transaksi_jual_beli);
                $stmt->execute();
                $result_rincian = $stmt->get_result();

                
                //Tampilkan Data
                echo '
                    <div class="row">
                        <div class="col-12 mb-3 text-center">
                            <b>'.$title_page.'</b><br>
                            '.$alamat_bisnis.'<br>
                            Telp : '.$telepon_bisnis.'
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 text-center dashed-underline">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3 text-center">
                            '.$id_transaksi_jual_beli.'
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4">Tgl/Jam</div>
                        <div class="col-8 text-end">'.$tanggal.'</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4">Supplier/PBF</div>
                        <div class="col-8 text-end">'.$nama_supplier.'</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">Transaksi</div>
                        <div class="col-8  text-end">'.$kategori.'</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 text-center dashed-underline">
                        </div>
                    </div>
                ';
                echo '<div class="row mb-3">';
                echo '   <div class="col-12 dashed-underline">';
                echo '      <table width="100%">';
                echo '
                    <tr>
                        <td><b>Uraian</b></td>
                        <td><b>HRG*QTY</b></td>
                        <td align="right"><b>JML</b></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <div class="row">
                                <div class="col-12 mb-3 dashed-underline"></div>
                            </div>
                        </td>
                    </tr>
                ';
                $no=1;
                $sum_subtotal=0;
                $sum_ppn=0;
                while ($data_rincian = $result_rincian->fetch_assoc()) {
                    $nama_barang    = $data_rincian['nama_barang'];
                    $qty            = $data_rincian['qty'];
                    $harga          = $data_rincian['harga'];
                    $ppn_rincian    = $data_rincian['ppn'];

                    // Menentukan Diskon
                    $diskon_rincian =0;
                    $diskon_rincian_format ="";
                    if(!empty($data_rincian['diskon'])){
                        $diskon_rincian = $data_rincian['diskon'];
                        if($diskon_rincian>0){
                            $diskon_rincian = pembulatan_nilai($diskon_rincian);
                            $diskon_rincian_format = "" . number_format($data_rincian['diskon'], 0, ',', '.');
                            $diskon_rincian_format ="<br>$diskon_rincian_format";
                        }
                    }
                    
                    //Bulatkan Nilai
                    $qty            = pembulatan_nilai($qty);
                    $harga          = pembulatan_nilai($harga);
                    $ppn_rincian    = pembulatan_nilai($ppn_rincian);
                    
                    //Format RP
                    $harga_format          = "" . number_format($data_rincian['harga'], 0, ',', '.');
                    $jumlah                = $qty*$harga;
                    $subtotal              = $jumlah-$diskon_rincian;
                    $subtotal_format       = "" . number_format($subtotal, 0, ',', '.');


                    //Arry
                    $sum_subtotal=$sum_subtotal+$subtotal;
                    $sum_ppn=$sum_ppn+$ppn_rincian;
                    echo '
                        <tr>
                            <td>'.$nama_barang.' '.$diskon_rincian_format.'</td>
                            <td>'.$harga_format.' * '.$qty.'</td>
                            <td align="right">'.$subtotal_format.'</td>
                        </tr>
                    ';
                    $no++;
                }
                //Format Subtotal
                $sum_subtotal_format= "" . number_format($sum_subtotal, 0, ',', '.');
                $sum_ppn_format= "" . number_format($sum_ppn, 0, ',', '.');
                $total_penjualan=$sum_subtotal+$sum_ppn;
                $total_penjualan_format= "" . number_format($total_penjualan, 0, ',', '.');
                echo '
                    <tr>
                        <td colspan="3"><br></td>
                    </tr>
                    <tr>
                        <td colspan="2">SUBTOTAL</td>
                        <td align="right">'.$sum_subtotal_format.'</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            PPN
                        </td>
                        <td align="right">'.$sum_ppn_format.'</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            TOTAL
                        </td>
                        <td align="right">'.$total_penjualan_format.'</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            CASH/UANG
                        </td>
                        <td align="right">'.$cash_rp.'</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            KEMBALIAN
                        </td>
                        <td align="right">'.$kembalian_rp.'</td>
                    </tr>
                ';
                echo '      </table>';
                echo '  </div>';
                echo '</div>';
            }
        }
    }
?>

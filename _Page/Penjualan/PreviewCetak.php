<?php
    // Koneksi & Konfigurasi
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";
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
                $id_anggota = $Data['id_anggota'];
                $kategori   = $Data['kategori'];
                $tanggal    = $Data['tanggal'];
                $subtotal   = pembulatan_nilai($Data['subtotal']);
                $ppn        = pembulatan_nilai($Data['ppn']);
                $diskon     = pembulatan_nilai($Data['diskon']);
                $total      = pembulatan_nilai($Data['total']);
                $cash       = pembulatan_nilai($Data['cash']);
                $kembalian  = pembulatan_nilai($Data['kembalian']);
                $status     = $Data['status'];

                  // Format Rupiah
                $subtotal_rp  = "" . number_format($subtotal, 0, ',', '.');
                $ppn_rp       = "" . number_format($ppn, 0, ',', '.');
                $diskon_rp    = "" . number_format($diskon, 0, ',', '.');
                $total_rp     = "" . number_format($total, 0, ',', '.');
                $cash_rp      = "" . number_format($cash, 0, ',', '.');
                $kembalian_rp = "" . number_format($kembalian, 0, ',', '.');

                // Ambil Nama Anggota
                $nama_anggota = (!empty($id_anggota)) ? GetDetailData($Conn, 'anggota', 'id_anggota', $id_anggota, 'nama') : "-";

                // Ambil Rincian Transaksi
                $list_rincian = [];
                $stmt = $Conn->prepare("SELECT * FROM transaksi_jual_beli_rincian WHERE id_transaksi_jual_beli = ?");
                $stmt->bind_param("s", $id_transaksi_jual_beli);
                $stmt->execute();
                $result_rincian = $stmt->get_result();

                //Format 
                $tanggal_format=date('d/m/Y H:i',strtotime($tanggal));

                $id_transaksi_pendek = substr($id_transaksi_jual_beli, 0, 8);
                //Tampilkan Data
                echo '
                    <div class="row">
                        <div class="col-12 mb-3 text-center dashed-underline">
                            <b>'.$title_page.'</b><br>
                            '.$alamat_bisnis.'<br>
                            Telp : '.$telepon_bisnis.'
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 text-center">
                            ID.'.$id_transaksi_jual_beli.'
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <table width="100%">
                                <tr>
                                    <td>Tgl/Jam</td>
                                    <td align="right">'.$tanggal_format.'</td>
                                </tr>
                                <tr>
                                    <td>Kepada Yth</td>
                                    <td align="right">'.$nama_anggota.'</td>
                                </tr>
                                <tr>
                                    <td>Petugas</td>
                                    <td align="right">'.$SessionNama.'</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3 dashed-underline"></div>
                    </div>
                ';
                echo '<div class="row mb-3">';
                echo '   <div class="col-12">';
                echo '      <table width="100%">';
                echo '
                    <tr>
                        <td><strong>URAIAN</strong></td>
                        <td><strong>QTY*HRG</strong></td>
                        <td align="right"><strong>JML</strong></td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="row">
                                <div class="col-12 mb-3 dashed-underline"></div>
                            </div>
                        </td>
                    </tr>
                ';
                $no=1;
                $sum_subtotal=0;
                $sum_ppn=0;
                $sum_diskon=0;
                while ($data_rincian = $result_rincian->fetch_assoc()) {
                    $nama_barang=$data_rincian['nama_barang'];
                    $qty=$data_rincian['qty'];
                    $harga=$data_rincian['harga'];
                    $ppn_rincian=$data_rincian['ppn'];
                    $diskon_rincian=$data_rincian['diskon'];
                    //Bulatkan Nilai
                    $qty=pembulatan_nilai($qty);
                    $harga=pembulatan_nilai($harga);
                    $ppn_rincian=pembulatan_nilai($ppn_rincian);
                    $diskon_rincian=pembulatan_nilai($diskon_rincian);

                    // Jika ada diskon
                    $tampilan_diskon = "";
                    if(!empty($data_rincian['diskon'])){
                        if(0<$data_rincian['diskon']){
                            $diskon_rincian_format = "" . number_format($data_rincian['diskon'], 0, ',', '.');
                            $tampilan_diskon = "<br>(DSC $diskon_rincian_format)";
                        }
                    }
                    //Format RP
                    $harga_format          = "" . number_format($data_rincian['harga'], 0, ',', '.');
                    $jumlah                = $qty*$harga;
                    $subtotal              = $jumlah-$diskon_rincian;
                    $subtotal_format       = "" . number_format($subtotal, 0, ',', '.');

                    //Arry
                    $sum_subtotal = $sum_subtotal+$subtotal;
                    $sum_ppn      = $sum_ppn+$ppn_rincian;
                    $sum_diskon   = $sum_diskon+$diskon_rincian;
                    echo '
                        <tr>
                            <td valign="top" class="text-dark">
                                '.$nama_barang.' '.$tampilan_diskon.'
                            </td>
                            <td valign="top" class="text-dark">
                                '.$qty.' * '.$harga_format.'
                            </td>
                            <td valign="top" align="right" class="text-dark">
                                '.$subtotal_format.'
                            </td>
                        </tr>
                    ';
                    $no++;
                    
                }
                //Format Subtotal
                $sum_subtotal_format    = "" . number_format($sum_subtotal, 0, ',', '.');
                $sum_ppn_format         = "" . number_format($sum_ppn, 0, ',', '.');
                $sum_diskon_format      = "" . number_format($sum_diskon, 0, ',', '.');
                $total_penjualan        = $sum_subtotal+$sum_ppn;
                $total_penjualan_format = "" . number_format($total_penjualan, 0, ',', '.');
               
                echo '
                    <tr>
                        <td colspan="3">
                            <div class="row">
                                <div class="col-12 mb-3 dashed-underline"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">SUBTOTAL</td>
                        <td align="right">'.$sum_subtotal_format.'</td>
                    </tr>
                    <tr>
                        <td colspan="2">PPN</td>
                        <td align="right">'.$sum_ppn_format.'</td>
                    </tr>
                    <tr>
                        <td colspan="2">DISKON</td>
                        <td align="right">'.$sum_diskon_format.'</td>
                    </tr>
                    <tr>
                        <td colspan="2"><b>TOTAL</b></td>
                        <td align="right"><b>'.$total_penjualan_format.'</b></td>
                    </tr>
                    <tr>
                        <td colspan="2">CASH/UANG</td>
                        <td align="right" colspan="2">'.$cash_rp.'</td>
                    </tr>
                    <tr>
                        <td colspan="2">KEMBALIAN</td>
                        <td align="right" colspan="2">'.$kembalian_rp.'</td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <div class="row">
                                <div class="col-12 mb-3 dashed-underline"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" align="center">
                            <i>Terima Kasih Atas Kepercayaan Anda</i>
                        </td>
                    </tr>
                ';
                echo '      </table>';
                echo '  </div>';
                echo '</div>';
            }
        }
    }
?>

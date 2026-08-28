<?php
    
    //Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Default Response JSON
    header('Content-Type: application/json; charset=utf-8');

    // Default $html
    $html ="";
    //Validasi 'id_transaksi_jual_beli'
    if(empty($_POST['id_transaksi_jual_beli'])){
        $response = [
            "status"     => "error",
            "message"       => "ID Transaksi Tidak Boleh Kosong",
            "html"       => ""
        ];
        echo json_encode($response);
        exit;
    }

    // Variabel dan sanitasi
    $id_transaksi_jual_beli = validateAndSanitizeInput($_POST['id_transaksi_jual_beli']);
    
    //Buka Data 'transaksi_jual_beli'
    $Qry = $Conn->prepare("SELECT * FROM transaksi_jual_beli WHERE id_transaksi_jual_beli = ?");
    $Qry->bind_param("s", $id_transaksi_jual_beli);
    if (!$Qry->execute()) {
        $response = [
            "status"     => "error",
            "message"       => 'Terjadi Kesalahan : '.$Conn->error.'',
            "html"       => ""
        ];
        echo json_encode($response);
        exit;
    }
    $Result = $Qry->get_result();
    $Data = $Result->fetch_assoc();
    $Qry->close();
    if (!$Data) {
        $response = [
            "status"     => "error",
            "message"       => 'ID Transaksi <i>'.$id_transaksi_jual_beli.'</i> Tidak Ditemukan Pada Database',
            "html"       => ""
        ];
        echo json_encode($response);
        exit;
    }

    // Ambil Data Transaksi
    $id_anggota     = $Data['id_anggota'];
    $id_supplier    = $Data['id_supplier'];
    $kategori       = $Data['kategori'];
    $tanggal        = $Data['tanggal'];
    $subtotal       = pembulatan_nilai($Data['subtotal']);
    $ppn            = pembulatan_nilai($Data['ppn']);
    $diskon         = pembulatan_nilai($Data['diskon']);
    $total          = pembulatan_nilai($Data['total']);
    $cash           = pembulatan_nilai($Data['cash']);
    $kembalian      = pembulatan_nilai($Data['kembalian']);
    $status         = $Data['status'];
    $creat_by_id    = $Data['creat_by_id'];
    $creat_by_name  = $Data['creat_by_name'];
    $creat_at       = $Data['creat_at'];
    $update_by_id   = $Data['update_by_id'];
    $update_by_name = $Data['update_by_name'];
    $update_at      = $Data['update_at'];

    // Format Rupiah
    $subtotal_rp  = "" . number_format($subtotal, 0, ',', '.');
    $ppn_rp       = "" . number_format($ppn, 0, ',', '.');
    $diskon_rp    = "" . number_format($diskon, 0, ',', '.');
    $total_rp     = "" . number_format($total, 0, ',', '.');
    $cash_rp      = "" . number_format($cash, 0, ',', '.');
    $kembalian_rp = "" . number_format($kembalian, 0, ',', '.');

    // Ambil Nama Anggota & Supplier
    $nama_anggota  = (!empty($id_anggota)) ? GetDetailData($Conn, 'anggota', 'id_anggota', $id_anggota, 'nama') : "-";
    $nama_supplier = (!empty($id_supplier)) ? GetDetailData($Conn, 'supplier', 'id_supplier', $id_supplier, 'nama_supplier') : "-";

    // Ambil Rincian Transaksi
    $stmt = $Conn->prepare("
        SELECT 
            tjbr.id_transaksi_jual_beli_rincian,
            tjbr.id_barang,
            COALESCE(NULLIF(b.nama_barang, ''), tjbr.nama_barang) AS nama_barang,
            tjbr.satuan,
            tjbr.qty,
            tjbr.hpp,
            tjbr.harga,
            tjbr.ppn,
            tjbr.diskon,
            tjbr.subtotal
        FROM transaksi_jual_beli_rincian AS tjbr
        LEFT JOIN barang AS b 
            ON tjbr.id_barang = b.id_barang
        WHERE tjbr.id_transaksi_jual_beli = ?
        ORDER BY tjbr.id_transaksi_jual_beli_rincian ASC
    ");

    if (!$stmt) {
        $response = [
            "status"  => "error",
            "message" => "Gagal menyiapkan query rincian: " . $Conn->error,
            "html"    => ""
        ];

        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("s", $id_transaksi_jual_beli);

    if (!$stmt->execute()) {
        $response = [
            "status"  => "error",
            "message" => "Gagal mengambil rincian transaksi: " . $stmt->error,
            "html"    => ""
        ];

        echo json_encode($response);
        exit;
    }

    $result_rincian = $stmt->get_result();

    //Format 
    $tanggal=date('d/m/Y H:i',strtotime($tanggal));
    $creat_at=date('d/m/Y H:i',strtotime($creat_at));
    $update_at=date('d/m/Y H:i',strtotime($update_at));

    // Ambil nama creator dan updater
    $creator = (!empty($creat_by_id)) ? GetDetailData($Conn, 'akses', 'id_akses', $creat_by_id, 'nama_akses') : "$creat_by_name";
    $updater = (!empty($update_by_id)) ? GetDetailData($Conn, 'akses', 'id_akses', $update_by_id, 'nama_akses') : "$update_by_name";

    // Routing Subject berdasarkan id_supplier or id_anggota
    $subject = '';
    if(!empty($id_supplier)){
        $subject = '
            <div class="row mb-2">
                <div class="col-4">
                    <small>Supplier</small>
                </div>
                <div class="col-8">
                    <small class="text-muted">'.$nama_supplier.'</small>
                </div>
            </div>
        ';
    }
    if(!empty($id_anggota)){
        $subject = '
            <div class="row mb-2">
                <div class="col-4">
                    <small>Pasien</small>
                </div>
                <div class="col-8">
                    <small class="text-muted">'.$nama_anggota.'</small>
                </div>
            </div>
        ';
    }

    $html_rincian = "";
    $no = 1;

    if ($result_rincian->num_rows > 0) {

        while ($DataRincian = $result_rincian->fetch_assoc()) {

            $nama_barang = !empty($DataRincian['nama_barang'])
                ? $DataRincian['nama_barang']
                : "-";

            $satuan = !empty($DataRincian['satuan'])
                ? $DataRincian['satuan']
                : "-";

            $qty = pembulatan_nilai($DataRincian['qty']);
            $harga = pembulatan_nilai($DataRincian['harga']);
            $jumlah = pembulatan_nilai($DataRincian['subtotal']);

            // Format angka
            $harga_rp = number_format($harga, 0, ',', '.');
            $jumlah_rp = number_format($jumlah, 0, ',', '.');

            // Format QTY
            $qty_format = rtrim(
                rtrim(number_format($qty, 2, ',', '.'), '0'),
                ','
            );

            // Escape output HTML
            $nama_barang = htmlspecialchars(
                $nama_barang,
                ENT_QUOTES,
                'UTF-8'
            );

            $satuan = htmlspecialchars(
                $satuan,
                ENT_QUOTES,
                'UTF-8'
            );

            $html_rincian .= '
                <tr>
                    <td class="text-center">'.$no.'</td>
                    <td>'.$nama_barang.'</td>
                    <td class="text">'.$harga_rp.'</td>
                    <td class="text-center">'.$qty_format.'</td>
                    <td>'.$satuan.'</td>
                    <td class="text-end">'.$jumlah_rp.'</td>
                </tr>
            ';

            $no++;
        }

    } else {

        $html_rincian = '
            <tr>
                <td class="text-center" colspan="6">
                    <small class="text-muted">
                        Tidak ada rincian transaksi
                    </small>
                </td>
            </tr>
        ';
    }

    $stmt->close();

    $html.='
        <input type="hidden" name="id" value="'.$id_transaksi_jual_beli.'">

        <div class="row mb-2">
            <div class="col-12">
                <b># Informasi Transaksi</b>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                
                <div class="row mb-2">
                    <div class="col-4">
                        <small>ID Transaksi</small>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">'.$id_transaksi_jual_beli.'</small>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-4">
                        <small>Tanggal & Jam</small>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">'.$tanggal.'</small>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-4">
                        <small>Transaksi</small>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">'.$kategori.'</small>
                    </div>
                </div>

                '.$subject.'

            </div>
            <div class="col-md-6">
                
                <div class="row mb-2">
                    <div class="col-4">
                        <small>Creat At</small>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">'.$creat_at.'</small>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-4">
                        <small>Update At</small>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">'.$update_at.'</small>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-4">
                        <small>Creat By</small>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">'.$creator.'</small>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-4">
                        <small>Update By</small>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">'.$updater.'</small>
                    </div>
                </div>

            </div>
        </div>

        <div class="row mb-2 mt-3">
            <div class="col-12">
                <b># Rincian Transaksi</b>
            </div>
        </div>

        <div class="row mb-2 mt-3">
            <div class="col-12">
                <div class="table table-responsive">
                    <table class="table table-sm table-striped table-hover">
                        <thead>
                            <tr>
                                <th><b>No</b></th>
                                <th><b>Uraian</b></th>
                                <th><b>Harga</b></th>
                                <th><b>QTY</b></th>
                                <th><b>Satuan</b></th>
                                <th class="text-end"><b>Jumlah</b></th>
                            </tr>
                        </thead>
                        <tbody>
                            '.$html_rincian.'
                            <tr>
                                <td></td>
                                <td class="text-left" colspan="4"><b>SUBTOTAL</b></td>
                                <td class="text-end"><b>'.$subtotal_rp.'</b></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td class="text-left" colspan="4"><b>PPN</b></td>
                                <td class="text-end"><b>'.$ppn_rp.'</b></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td class="text-left" colspan="4"><b>DISKON</b></td>
                                <td class="text-end"><b>'.$diskon_rp.'</b></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td class="text-left" colspan="4"><b>TOTAL</b></td>
                                <td class="text-end"><b>'.$total_rp.'</b></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td class="text-left" colspan="4"><b>CASH/TUNAI</b></td>
                                <td class="text-end"><b>'.$cash_rp.'</b></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td class="text-left" colspan="4"><b>KEMBALIAN</b></td>
                                <td class="text-end"><b>'.$kembalian_rp.'</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    ';
    $response = [
        "status"  => "success",
        "message" => 'Data Berhasil Ditampilkan',
        "html"    => $html
    ];
    echo json_encode($response);
?>


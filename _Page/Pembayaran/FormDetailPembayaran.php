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
    
    // Validasi 'id_pembayaran'
    if(empty($_POST['id_pembayaran'])){
        $response = [
            "status"  => "error",
            "message" => "ID Pembayaran Tidak Boleh Kosong",
            "html"    => ""
        ];
        echo json_encode($response);
        exit;
    }

    // Variabel dan sanitasi
    $id_transaksi_pembayaran = validateAndSanitizeInput($_POST['id_pembayaran']);
    
    // Buka Data 'transaksi_pembayaran'
    $Qry = $Conn->prepare("SELECT * FROM transaksi_pembayaran WHERE id_transaksi_pembayaran = ?");
    $Qry->bind_param("s", $id_transaksi_pembayaran);
    if (!$Qry->execute()) {
        $response = [
            "status"  => "error",
            "message" => 'Terjadi Kesalahan : '.$Conn->error,
            "html"    => ""
        ];
        echo json_encode($response);
        exit;
    }
    $Result = $Qry->get_result();
    $Data = $Result->fetch_assoc();
    $Qry->close();

    if (!$Data) {
        $response = [
            "status"  => "error",
            "message" => 'ID Pembayaran <i>'.$id_transaksi_pembayaran.'</i> Tidak Ditemukan Pada Database',
            "html"    => ""
        ];
        echo json_encode($response);
        exit;
    }

    // Ambil Data Transaksi Pembayaran
    $id_transaksi           = $Data['id_transaksi'];
    $id_transaksi_jual_beli = $Data['id_transaksi_jual_beli'];
    $kategori_pembayaran    = $Data['kategori_pembayaran'];
    $kategori_transaksi     = $Data['kategori_transaksi'];
    $tanggal                = $Data['tanggal'];
    $jumlah                 = pembulatan_nilai($Data['jumlah']);
    $creat_by_id            = $Data['creat_by_id'];
    $creat_by_name          = $Data['creat_by_name'];
    $creat_at               = $Data['creat_at'];
    $update_by_id           = $Data['update_by_id'];
    $update_by_name         = $Data['update_by_name'];
    $update_at              = $Data['update_at'];

    // Format Rupiah Total Pembayaran
    $jumlah_rp  = "Rp " . number_format($jumlah, 0, ',', '.');

    // Ambil nama creator dan updater dari tabel akses
    $creator = (!empty($creat_by_id)) ? GetDetailData($Conn, 'akses', 'id_akses', $creat_by_id, 'nama_akses') : "$creat_by_name";
    $updater = (!empty($update_by_id)) ? GetDetailData($Conn, 'akses', 'id_akses', $update_by_id, 'nama_akses') : "$update_by_name";

    // Routing Berdasarkan Transaksi Induk
    if(!empty($id_transaksi)){
        $KodeTransaksi = $id_transaksi;
        $database      = 'transaksi';
    }else{
        $KodeTransaksi = $id_transaksi_jual_beli;
        $database      = 'transaksi_jual_beli';
    }

    // Format tanggal
    $tanggal   = $tanggal ? date('d/m/Y H:i:s', strtotime($tanggal)) : '-';
    $creat_at  = $creat_at ? date('d/m/Y H:i:s', strtotime($creat_at)) : '-';
    $update_at = $update_at ? date('d/m/Y H:i:s', strtotime($update_at)) : '-';

    // ==========================================
    // AMBIL DATA JURNAL TERKAIT
    // ==========================================
    $html_jurnal = "";
    $no = 1;
    $total_debet = 0;
    $total_kredit = 0;

    // Query untuk mengambil jurnal berdasarkan id_transaksi_pembayaran
    $QryJurnal = $Conn->prepare("
        SELECT j.*, a.nama AS nama_akun_master 
        FROM jurnal j 
        LEFT JOIN akun_perkiraan a ON j.kode_perkiraan = a.kode 
        WHERE j.id_transaksi_pembayaran = ?
    ");
    
    if($QryJurnal){
        $QryJurnal->bind_param("i", $id_transaksi_pembayaran);
        $QryJurnal->execute();
        $ResultJurnal = $QryJurnal->get_result();

        while ($RowJurnal = $ResultJurnal->fetch_assoc()) {
            $id_jurnal = htmlspecialchars($RowJurnal['id_jurnal']);
            $kode_perkiraan = htmlspecialchars($RowJurnal['kode_perkiraan']);
            
            // Ambil nama akun (prioritas dari master akun_perkiraan, fallback ke nama_perkiraan di tabel jurnal)
            $nama_akun = !empty($RowJurnal['nama_akun_master']) ? $RowJurnal['nama_akun_master'] : ($RowJurnal['nama_perkiraan'] ?? '-');
            $nama_akun = htmlspecialchars($nama_akun);

            $d_k          = strtoupper(trim($RowJurnal['d_k'])); // 'D' atau 'K'
            $nilai_jurnal = (float) $RowJurnal['nilai'];

            $debet_str  = "-";
            $kredit_str = "-";

            if ($d_k === 'D') {
                $total_debet += $nilai_jurnal;
                $debet_str  = "Rp " . number_format($nilai_jurnal, 0, ',', '.');
            } elseif ($d_k === 'K') {
                $total_kredit += $nilai_jurnal;
                $kredit_str = "Rp " . number_format($nilai_jurnal, 0, ',', '.');
            }

            $html_jurnal .= '
                <tr>
                    <td><small>'.$no.'</small></td>
                    <td><small>'.$kode_perkiraan.'</small></td>
                    <td><small>'.$nama_akun.'</small></td>
                    <td><small>'.$debet_str.'</small></td>
                    <td><small>'.$kredit_str.'</small></td>
                    <td>
                        <a class="btn btn-sm btn-secondary btn-floating" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" title="Opsi">
                            <i class="bi bi-three-dots-vertical"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">
                                <h6 class="mb-0">Opsi</h6>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalEditJurnal" data-id="' . $id_jurnal . '">
                                    <i class="bi bi-pencil me-2"></i>Ubah/Edit
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalHapusJurnnal" data-id="' . $id_jurnal . '">
                                    <i class="bi bi-trash me-2"></i>Hapus
                                </a>
                            </li>
                        </ul>
                    </td>
                </tr>
            ';
            $no++;
        }
        $QryJurnal->close();
    }

    // Jika data jurnal kosong
    if($no === 1){
        $html_jurnal = '
            <tr>
                <td colspan="6" class="text-center"><small class="text-muted">Tidak ada data jurnal untuk pembayaran ini.</small></td>
            </tr>
        ';
    }

    $total_debet_str  = "Rp " . number_format($total_debet, 0, ',', '.');
    $total_kredit_str = "Rp " . number_format($total_kredit, 0, ',', '.');

    // ==========================================
    // RANGKAI HTML UTAMA
    // ==========================================
    $html='
        <input type="hidden" name="id_transaksi_pembayaran" value="'.$id_transaksi_pembayaran.'">
        
        <div class="row mb-2">
            <div class="col-md-6">
                
                <div class="row mb-2">
                    <div class="col-12">
                        <small>
                            <b># Info Pembayaran</b>
                        </small>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-4">
                        <small>ID Transaksi</small>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">'.$KodeTransaksi.'</small>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-4">
                        <small>Transaksi</small>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">'.$kategori_transaksi.'</small>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-4">
                        <small>Kategori Pembayaran</small>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">'.$kategori_pembayaran.'</small>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-4">
                        <small>Tanggal Pembayaran</small>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">'.$tanggal.'</small>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-4">
                        <small>Nominal Pembayaran</small>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">'.$jumlah_rp.'</small>
                    </div>
                </div>

            </div>
            <div class="col-md-6">

                <div class="row mt-3 mb-2">
                    <div class="col-12">
                        <small>
                            <b># Metadata</b>
                        </small>
                    </div>
                </div>
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

        <div class="row mt-3 mb-2">
            <div class="col-8">
                <small>
                    <b># Jurnal Pembayaran</b>
                </small>
            </div>
            <div class="col-4 text-end">
                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalTambahJurnal" data-id="'.$id_transaksi_pembayaran.'" title="Tambah Jurnal">
                    <small><i class="bi bi-plus-lg"></i> Tambah Jurnal</small>
                </a>
            </div>
        </div>
        <div class="row mb-2 mt-3">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="bg-info text-white"><b>No</b></th>
                                <th class="bg-info text-white"><b>Kode</b></th>
                                <th class="bg-info text-white"><b>Akun</b></th>
                                <th class="bg-info text-white"><b>Debet</b></th>
                                <th class="bg-info text-white"><b>Kredit</b></th>
                                <th class="bg-info text-white"><b>Opsi</b></th>
                            </tr>
                        </thead>
                        <tbody>
                            '.$html_jurnal.'
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"><b>Jumlah/Saldo</b></td>
                                <td><b>'.$total_debet_str.'</b></td>
                                <td><b>'.$total_kredit_str.'</b></td>
                                <td></td>
                            </tr>
                        </tfoot>
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
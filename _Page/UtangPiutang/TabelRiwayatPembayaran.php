<?php
    // KONEKSI, FUNGSI DAN SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Default JSON format
    header('Content-Type: application/json; charset=utf-8');
    $html= "";

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        $response["status"]  = 'error';
        $response["message"] = 'Sesi Akses Sudah Berakhir! Silahkan Login Ulang';
        $response["html"]    = '';
        echo json_encode($response);
        exit;
    }

    // Validasi ID dan Kategori
    if(empty($_POST['id'])){
        $response["status"]  = 'error';
        $response["message"] = '<b>Opss!</b> <br> ID Transaksi Tidak Boleh Kosong!';
        $response["html"]    = '';
        echo json_encode($response);
        exit;
    }
    if(empty($_POST['kategori'])){
        $response["status"]  = 'error';
        $response["message"] = '<b>Opss!</b> <br> Kategori Transaksi Tidak Boleh Kosong!';
        $response["html"]    = '';
        echo json_encode($response);
        exit;
    }

    // Buat Variabel Dan Sanitasi
    $id       = validateAndSanitizeInput($_POST['id']);
    $kategori = validateAndSanitizeInput($_POST['kategori']);

    // Rouitng 'jml_data', 'nama_transaksi' berdasarkan 'kategori'
    if($kategori=="jual_beli"){
        $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT id_transaksi_pembayaran FROM transaksi_pembayaran WHERE id_transaksi_jual_beli='$id'"));
        $nama_transaksi = "Transaksi Jual/Beli";
    }else{
        $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT id_transaksi_pembayaran FROM transaksi_pembayaran WHERE id_transaksi='$id'"));
        $nama_transaksi = "Transaksi Operasional";
    }

    // Validasi Jika Data Tidak Ditemukan
    if(empty($jml_data)){
        $response["status"]  = 'success';
        $response["message"] = 'Data Riwayat Pembayaran Tidak Ada';
        $response["html"]    = '
            <tr>
                <td colspan="6" class="text-center">
                    <a href="javascript:void(0);" class="btn btn-md btn-success btn-rounded mt-3 mb-3" data-modal-target="#ModalPembayaran" data-id="'.$id.'" data-kategori="'.$kategori.'">
                        <i class="bi bi-plus-lg"></i> Tambah Pembayaran
                    </a>
                </td>
            </tr>
            <tr>
                <td colspan="6" class="text-center">
                    <small>
                        Tidak ada riwayat pembayaran untuk <b>'.$nama_transaksi.'</b> dengan ID transaksi <b>'.$id.'</b>
                    </small>
                </td>
            </tr>
        ';
        echo json_encode($response);
        exit;
    }

    // Jika Data Ada Tampilkan
    $html             .= '
        <tr>
            <td colspan="6" class="text-center">
                <a href="javascript:void(0);" class="btn btn-md btn-success btn-rounded mt-3 mb-3" data-modal-target="#ModalPembayaran" data-id="'.$id.'" data-kategori="'.$kategori.'">
                    <i class="bi bi-plus-lg"></i> Tambah Pembayaran
                </a>
            </td>
        </tr>
    ';
    $no                = 1;
    $total_pembayaran  = 0;
    if($kategori=="jual_beli"){
        $query = mysqli_query($Conn, "SELECT*FROM transaksi_pembayaran WHERE id_transaksi_jual_beli='$id' ORDER BY id_transaksi_pembayaran DESC");
    }else{
        $query = mysqli_query($Conn, "SELECT*FROM transaksi_pembayaran WHERE id_transaksi='$id' ORDER BY id_transaksi_pembayaran DESC");
    }
    while ($data = mysqli_fetch_array($query)) {
        $id_transaksi_pembayaran = $data['id_transaksi_pembayaran'];
        $kategori_pembayaran     = $data['kategori_pembayaran'];
        $kategori_transaksi      = $data['kategori_transaksi'];
        $tanggal                 = $data['tanggal'];
        $jumlah                  = $data['jumlah'];
        $total_pembayaran        = $total_pembayaran + $jumlah;
        
        // Format
        $tanggal = date('d/m/Y H:i', strtotime($tanggal));
        $jumlah  = "Rp " . number_format($jumlah, 0, ',', '.');

        $html .= '
            <tr>
                <td>'.$no.'</td>
                <td>'.$tanggal.'</td>
                <td>'.$kategori_transaksi.'</td>
                <td>'.$kategori_pembayaran.'</td>
                <td>'.$jumlah.'</td>
                <td>
                    <a href="Javascript:(0);" class="btn btn-sm btn-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" style="">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" data-modal-target="#ModalEditPembayaran" data-id_transaksi_pembayaran="'.$id_transaksi_pembayaran.'" data-id="'.$id.'" data-kategori="'.$kategori.'">
                                <i class="bi bi-pencil"></i> Ubah/Edit
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" data-modal-target="#ModalHapusPembayaran" data-id_transaksi_pembayaran="'.$id_transaksi_pembayaran.'" data-id="'.$id.'" data-kategori="'.$kategori.'">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        </li>
                    </ul>
                </td>
            </tr>
        ';
        $no++;
    }

    $total_pembayaran  = "Rp " . number_format($total_pembayaran, 0, ',', '.');
    $html .= '
        <tr>
            <td></td>
            <td colspan="3"><b>TOTAL PEMBAYARAN</b></td>
            <td><b>'.$total_pembayaran.'</b></td>
            <td></td>
        </tr>
    ';
    $response["status"]  = 'success';
    $response["message"] = 'data Berhasil Ditampilkan';
    $response["html"]    = $html;
    echo json_encode($response);
    exit;
   
?>
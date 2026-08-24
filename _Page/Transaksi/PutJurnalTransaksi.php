<?php
    //Koneksi Session dan Helper
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";
    $html = "";
    
    // Default Response
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.',
        'html'    => ''
    ];

    // Default JSON
    header('Content-Type: application/json; charset=utf-8');

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {

        $response['status']  = 'error';
        $response['message'] = 'Sesi akses sudah berakhir.';
        $response['html']    = '
            <tr>
                <td colspan="6" class="text-center">
                    <small class="text-danger">
                        Sesi akses sudah berakhir. Silahkan Login Ulang
                    </small>
                </td>
            </tr>
        ';

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // VALIDASI ID TRANSAKSI
    $id_transaksi = $_POST['id_transaksi'] ?? '';
    if (empty($id_transaksi)) {
        $response['status']  = 'error';
        $response['message'] = 'ID Transaksi Tidak Boleh Kosong!';
        $response['html'] = '
            <tr>
                <td colspan="6" class="text-center">
                    <small class="text-danger">
                        ID Transaksi Tidak Boleh Kosong!
                    </small>
                </td>
            </tr>
        ';

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    $id_transaksi = validateAndSanitizeInput($id_transaksi);

    // Hitung Jumlah Jurnal
    $JumlahJurnal = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM jurnal WHERE id_transaksi='$id_transaksi'"));
    
    //Menampilkan Jurnal
    $JumlahDebet=0;
    $JumlahKredit=0;
    if(empty($JumlahJurnal)){
        $response['status']  = 'error';
        $response['message'] = 'ID Transaksi Tidak Boleh Kosong!';
        $response['html'] = '
            <tr>
                <td colspan="6" class="text-center">
                    <small class="text-danger">
                        Tidak Ada Data Jurnal Untuk Transaksi Ini!
                    </small>
                </td>
            </tr>
        ';

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    $response['status']  = 'success';
    $response['message'] = 'Jurnal Berhasil Ditampilkan';
    $query = mysqli_query($Conn, "SELECT*FROM jurnal WHERE kategori='Transaksi' AND id_transaksi='$id_transaksi' ORDER BY id_jurnal ASC");
    while ($data = mysqli_fetch_array($query)) {
        $id_jurnal= $data['id_jurnal'];
        $kode_perkiraan= $data['kode_perkiraan'];
        $nama_perkiraan= $data['nama_perkiraan'];
        $d_k= $data['d_k'];
        $nilai= $data['nilai'];
        $NilaiFormat = "Rp " . number_format($nilai,0,',','.');

        // Lajur Debet
        if($d_k=="D"){
            $JumlahDebet=$JumlahDebet+$nilai;
            $JumlahKredit=$JumlahKredit+0;
            $response['html'].='
                <tr>
                    <td>'.$kode_perkiraan.'</td>
                    <td>'.$nama_perkiraan.'</td>
                    <td>'.$NilaiFormat.'</td>
                    <td>-</td>
                    <td>
                        <a class="btn btn-sm btn-secondary btn-floating" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" style="">
                            <li class="dropdown-header text-start">
                                <h6>Option</h6>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalEditJurnal" data-id="'.$id_jurnal.'">
                                    <i class="bi bi-pencil"></i> Ubah/Edit
                                </a>
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalHapusJurnal" data-id="'.$id_jurnal.'">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </li>
                        </ul>
                    </td>
                </tr>
            ';
            
        }else{
            $JumlahDebet=$JumlahDebet+0;
            $JumlahKredit=$JumlahKredit+$nilai;
            $response['html'].='
                <tr>
                    <td>'.$kode_perkiraan.'</td>
                    <td>'.$nama_perkiraan.'</td>
                    <td>-</td>
                    <td>'.$NilaiFormat.'</td>
                    <td>
                        <a class="btn btn-sm btn-secondary btn-floating" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" style="">
                            <li class="dropdown-header text-start">
                                <h6>Option</h6>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalEditJurnal" data-id="'.$id_jurnal.'">
                                    <i class="bi bi-pencil"></i> Ubah/Edit
                                </a>
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalHapusJurnal" data-id="'.$id_jurnal.'">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </li>
                        </ul>
                    </td>
                </tr>
            ';
        }
    }
    $JumlahDebetFormat = "Rp " . number_format($JumlahDebet,0,',','.');
    $JumlahKreditFormat = "Rp " . number_format($JumlahKredit,0,',','.');
    $response['html'].='
        <tr>
            <td colspan="2">
                <b>JUMLAH/SALDO</b>
            </td>
            <td><b>'.$JumlahDebetFormat.'</b></td>
            <td><b>'.$JumlahKreditFormat.'</b></td>
            <td></td>
        </tr>
    ';

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
?>
<?php
    //koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    //Zona Waktu
    date_default_timezone_set("Asia/Jakarta");

    //Session Akses
    if(empty($SessionIdAkses)){
        echo '
            <tr>
                <td colspan="8" class="text-center">
                    <small class="text-danger">Sesi Akses Sudah Berakhir! Silahkan Login Ulang!</small>
                </td>
            </tr>
        ';
        exit;
    }

    //Hitung Jumlah Data
    $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT id_connection_satu_sehat FROM connection_satu_sehat"));

    //Jika Tidak Ada Data Kelas
    if(empty($jml_data)){
        echo '
            <tr>
                <td colspan="7" class="text-center">
                    <small class="text-danger">Tidak ada data <b>koneksi Satu Sehat</b> yang ditampilkan</small>
                </td>
            </tr>
        ';
        exit;
    }

    //Tampilkan Data
    $no=1;
    $qry = mysqli_query($Conn, "SELECT * FROM connection_satu_sehat ORDER BY id_connection_satu_sehat DESC");
    while ($data = mysqli_fetch_array($qry)) {
        $id_connection_satu_sehat     = $data['id_connection_satu_sehat'];
        $name_connection_satu_sehat   = $data['name_connection_satu_sehat'];
        $url_connection_satu_sehat    = $data['url_connection_satu_sehat'];
        $organization_id              = $data['organization_id'];
        $client_key                   = $data['client_key'];
        $secret_key                   = $data['secret_key'];
        $status_connection_satu_sehat = $data['status_connection_satu_sehat'];

        // Potong hanya 10 karakter pertama lalu tambahkan ***
        $organization_id_masked = substr($organization_id, 0, 10) . '***';
        $client_key_masked      = substr($client_key, 0, 10) . '***';
        $secret_key_masked      = substr($secret_key, 0, 10) . '***';


        //Routing status koneksi
        if(empty($data['status_connection_satu_sehat'])){
            $label_status = '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Inactive</span>';
        }else{
            $label_status = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Active</span>';
        }
        //Tampilkan Data
        echo '
            <tr>
                <td class="text-center"><small>'.$no.'</small></td>
                <td>
                    <a href="javascript:void(0);" class="modal_detail" data-id="'.$id_connection_satu_sehat .'" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Tambah Pengaturan Koneksi SIMRS">
                        <small class="text text-primary underscore_doted">
                            '.$name_connection_satu_sehat.'
                        </small>
                    </a>
                </td>
                <td>
                    <small>
                        <code class="text text-grayish">'.$url_connection_satu_sehat.'</code>
                    </small>
                </td>
                <td><small>'.$organization_id_masked.'</small></td>
                <td><small>'.$client_key_masked.'</small></td>
                <td><small>'.$secret_key_masked.'</small></td>
                <td class="text-center">'.$label_status.'</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-dark btn-floating"  data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow bg-body-secondary" style="">
                        <li class="dropdown-header text-center">
                            <h6>Option</h6>
                        </li>
                        <li><hr class="dropdown-divider border-1 border-bottom"></li>
                        <li>
                            <a class="dropdown-item modal_detail" href="javascript:void(0)" data-id="'.$id_connection_satu_sehat .'">
                                <i class="bi bi-info-circle"></i> Detail Koneksi
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item modal_uji_koneksi" href="javascript:void(0)" data-id="'.$id_connection_satu_sehat .'">
                                <i class="bi bi-arrow-left-right"></i> Uji Koneksi
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item modal_edit" href="javascript:void(0)" data-id="'.$id_connection_satu_sehat .'">
                                <i class="bi bi-pencil"></i> Edit Koneksi
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item modal_delete" href="javascript:void(0)" data-id="'.$id_connection_satu_sehat .'">
                                <i class="bi bi-x"></i> Hapus Koneksi
                            </a>
                        </li>
                    </ul>
                </td>
            </tr>
        ';
        $no++;
    }
?>
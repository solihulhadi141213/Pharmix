<?php
    // Koneksi
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Time Now Tmp
    $now = date('Y-m-d H:i:s');

    // Inisialisasi respons default
    $response = [
        "status" => "error",
        "message" => "Belum ada proses yang dilakukan pada sistem."
    ];

    // Validasi sesi login
    if (empty($SessionIdAkses)) {
        $response = [
            "status" => "error",
            "message" => "Sesi Akses Sudah Berakhir, Silahkan Login Ulang"
        ];
        echo json_encode($response);
        exit;
    }

    // Validasi 'id_dokumentasi' tidak boleh kosong
    if(empty($_POST['id_dokumentasi'])){
        $response = [
            "status" => "error",
            "message" => "ID Dokumentasi Tidak Boleh Kosong"
        ];
        echo json_encode($response);
        exit;
    }
        
    // Buat Variabel
    $id_dokumentasi = validateAndSanitizeInput($_POST['id_dokumentasi']);
        
    //Buka Data
    $Qry = $Conn->prepare("SELECT * FROM dokumentasi WHERE id_dokumentasi = ?");
    $Qry->bind_param("s", $id_dokumentasi);
    if (!$Qry->execute()) {
        $error=$Conn->error;
        $response = [
            "status" => "error",
            "message" => "Terjadi Kesalahan Pada Saat Membuka Dokumentasi. Pesan : $error"
        ];
        echo json_encode($response);
        exit;
    }

    $Result = $Qry->get_result();
    $Data   = $Result->fetch_assoc();
    $Qry->close();

    // Informasi Dokumentasi
    $judul       = $Data['judul'];
    $deskripsi   = $Data['deskripsi'];
    $status      = $Data['status'];
    $id_akses    = $Data['id_akses'];
    $author_name = $Data['author_name'];
    $creat_at    = $Data['creat_at'];
    $update_at   = $Data['update_at'];

    // Routing Status
    if($status=="Publish"){
        $label_status = '<span class="badge bg-success text-light border border-success rounded-pill"> <i class="bi bi-check-circle"></i> Publish</span>';
    }else{
        $label_status = '<span class="badge bg-secondary text-light border border-secondary rounded-pill"> <i class="bi bi-file-earmark"></i> Draft</span>';
    }

    //Buat Variabel
    $detail = [
        "id_dokumentasi" => $Data['id_dokumentasi'],
        "judul"          => $Data['judul'],
        "deskripsi"      => $Data['deskripsi'],
        "status"         => "$label_status",
        "id_akses"       => $Data['id_akses'],
        "author_name"    => $Data['author_name'],
        "creat_at"       => $Data['creat_at'],
        "update_at"      => $Data['update_at']
    ];
    
    //Tags
    $tags=[];
    $qry_tags = mysqli_query($Conn, "SELECT * FROM dokumentasi_tags WHERE id_dokumentasi='$id_dokumentasi' ORDER BY tags ASC");
    while ($data_tags = mysqli_fetch_array($qry_tags)) {
        $tags[] = [
            "id_dokumentasi_tags" => $data_tags['id_dokumentasi_tags'],
            "tags"    => $data_tags['tags'],
        ];
    }

    //Content
    $content=[];
    $query_artikel_lain = mysqli_query($Conn, "SELECT * FROM dokumentasi_konten WHERE id_dokumentasi='$id_dokumentasi' ORDER BY sequence ASC");
    while ($data_artikel_lain = mysqli_fetch_array($query_artikel_lain)) {
        $id_dokumentasi_konten = $data_artikel_lain['id_dokumentasi_konten'];
        $sequence              = $data_artikel_lain['sequence'];
        $tipe_konten           = $data_artikel_lain['tipe_konten'];
        $text_konten           = $data_artikel_lain['text_konten'];
        $local_image_konten    = $data_artikel_lain['local_image_konten'];
        $url_image_konten      = $data_artikel_lain['url_image_konten'];

        // Khusus List Konten
        $list_konten = [];
        if (!empty($data_artikel_lain['list_konten'])) {
            $decoded_list = json_decode(
                $data_artikel_lain['list_konten'],
                true
            );

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_list)) {
                $list_konten = $decoded_list;
            }
        }

        $content[] = [
            "id_dokumentasi_konten" => $id_dokumentasi_konten,
            "sequence"              => $sequence,
            "tipe_konten"           => $tipe_konten,
            "text_konten"           => $text_konten,
            "list_konten"           => $list_konten,
            "local_image_konten"    => $local_image_konten,
            "url_image_konten"      => $url_image_konten,
        ];
    }
    

    //Buat Response
    $response = [
        "status"  => "success",
        "message" => "Data Ditemukan",
        "detail"  => $detail,
        "tags"    => $tags,
        "content" => $content
    ];
    echo json_encode($response);
    exit;
?>

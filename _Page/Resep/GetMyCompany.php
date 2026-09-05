<?php
    //---------------------------------------
    // KONFIGURASI
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    //---------------------------------------
    // RESPONSE DEFAULT
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.',
        'data'    => null
    ];

    //---------------------------------------
    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        $response['message'] = 'Sesi akses sudah berakhir.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //---------------------------------------
    // AMBIL DATA SETTING GENERAL
    $id_setting_general = 1;

    $sql = "
        SELECT title_page
        FROM setting_general
        WHERE id_setting_general = ?
        LIMIT 1
    ";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {
        $response['message'] = 'Gagal mempersiapkan query.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->bind_param("i", $id_setting_general);

    //---------------------------------------
    // EKSEKUSI
    if (!$stmt->execute()) {
        $response['message'] = 'Gagal mengambil data perusahaan.';
        $stmt->close();

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //---------------------------------------
    // AMBIL HASIL
    $result = $stmt->get_result();
    $data   = $result->fetch_assoc();
    $stmt->close();

    //---------------------------------------
    // VALIDASI DATA
    if (!$data) {
        $response['message'] = 'Data perusahaan tidak ditemukan.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $title_page = trim($data['title_page'] ?? '');

    if ($title_page === '') {
        $response['message'] = 'Nama perusahaan belum dikonfigurasi.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    //---------------------------------------
    // RESPONSE SUCCESS
    $response = [
        'status'  => 'success',
        'message' => 'Data perusahaan berhasil ditemukan.',
        'data'    => [
            'sumber_resep' => $title_page
        ]
    ];

    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
?>
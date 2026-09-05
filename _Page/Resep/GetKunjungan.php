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
        'data'    => []
    ];

    //---------------------------------------
    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        $response['message'] = 'Sesi akses sudah berakhir.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //---------------------------------------
    // TANGKAP PARAMETER
    $id_anggota = (int) ($_GET['id_anggota'] ?? 0);

    //---------------------------------------
    // VALIDASI PARAMETER
    if ($id_anggota < 1) {
        $response['message'] = 'ID pasien tidak valid.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //---------------------------------------
    // AMBIL DATA KUNJUNGAN
    $sql = "
        SELECT
            id_kunjungan,
            id_encounter,
            tanggal_kunjungan,
            priority,
            jenis_kunjungan,
            nama_dokter_penerima,
            nama_dpjp,
            nama_poli,
            status
        FROM kunjungan
        WHERE id_anggota = ?
        ORDER BY tanggal_kunjungan DESC
    ";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {
        $response['message'] = 'Gagal mempersiapkan query.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->bind_param("i", $id_anggota);

    //---------------------------------------
    // EKSEKUSI
    if (!$stmt->execute()) {
        $response['message'] = 'Gagal mengambil data kunjungan.';
        $stmt->close();

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //---------------------------------------
    // AMBIL HASIL
    $result = $stmt->get_result();
    $data   = [];

    while ($row = $result->fetch_assoc()) {
        //---------------------------------------
        // FORMAT TANGGAL
        $tanggal_kunjungan = $row['tanggal_kunjungan'] ?? null;
        $tanggal_display   = !empty($tanggal_kunjungan)
            ? date('d-m-Y H:i', strtotime($tanggal_kunjungan))
            : '-';

        //---------------------------------------
        // FORMAT JENIS KUNJUNGAN
        $jenis_kunjungan = $row['jenis_kunjungan'] ?? '';

        switch ($jenis_kunjungan) {
            case 'AMB':
                $jenis_display = 'Rawat Jalan';
                break;

            case 'IMP':
                $jenis_display = 'Rawat Inap';
                break;

            case 'EMER':
                $jenis_display = 'IGD';
                break;

            default:
                $jenis_display = '-';
                break;
        }

        //---------------------------------------
        // FORMAT POLIKLINIK / RUANG
        $nama_poli = trim($row['nama_poli'] ?? '');

        if ($nama_poli === '') {
            $nama_poli = '-';
        }

        //---------------------------------------
        // DISPLAY OPTION
        $display = $tanggal_display . ' | ' . $jenis_display . ' | ' . $nama_poli;

        //---------------------------------------
        // DATA
        $data[] = [
            'id_kunjungan'      => (int) $row['id_kunjungan'],
            'id_encounter'      => $row['id_encounter'] ?? '',
            'tanggal_kunjungan' => $tanggal_display,
            'priority'          => $row['priority'] ?? '',
            'jenis_kunjungan'   => $jenis_kunjungan,
            'jenis_display'     => $jenis_display,
            'dokter_penerima'   => $row['nama_dokter_penerima'] ?? '',
            'dpjp'              => $row['nama_dpjp'] ?? '',
            'poliklinik'        => $nama_poli,
            'status'            => $row['status'] ?? '',
            'display'           => $display
        ];
    }

    $stmt->close();

    //---------------------------------------
    // RESPONSE SUCCESS
    $response = [
        'status'  => 'success',
        'message' => count($data) > 0
            ? 'Data kunjungan berhasil ditemukan.'
            : 'Pasien belum memiliki data kunjungan.',
        'data'    => $data
    ];

    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
?>
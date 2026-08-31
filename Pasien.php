<?php
    date_default_timezone_set("Asia/Jakarta");

    include "_Config/Connection.php";
    include "_Config/GlobalFunction.php";
    include "_Config/Session.php";
    include "_Config/FungsiAkses.php";

    // =========================================================
    // VALIDASI SESSION
    // =========================================================
    if (empty($SessionIdAkses)) {
        exit('Sesi akses sudah berakhir. Silakan login ulang.');
    }

    // =========================================================
    // KONFIGURASI
    // =========================================================
    $now = date('Y-m-d H:i:s');

    $tempat_lahir_list = [
        'Kuningan',
        'Cirebon',
        'Majalengka',
        'Indramayu',
        'Ciamis',
        'Tasikmalaya',
        'Garut',
        'Bandung',
        'Sumedang',
        'Subang',
        'Karawang',
        'Bekasi',
        'Bogor',
        'Sukabumi',
        'Jakarta'
    ];

    // =========================================================
    // AMBIL DATA ANGGOTA
    // =========================================================
    $query = mysqli_query(
        $Conn,
        "SELECT id_anggota FROM anggota ORDER BY id_anggota ASC"
    );

    if (!$query) {
        exit('Gagal mengambil data anggota: ' . mysqli_error($Conn));
    }

    // =========================================================
    // PREPARE UPDATE
    // =========================================================
    $stmt = mysqli_prepare(
        $Conn,
        "UPDATE anggota SET
            id_pasien      = ?,
            tempat_lahir   = ?,
            tanggal_lahir  = ?,
            creat_at       = ?,
            creat_by_id    = ?,
            creat_by_name  = ?,
            update_at      = ?,
            update_by_id   = ?,
            update_by_name = ?
        WHERE id_anggota = ?"
    );

    if (!$stmt) {
        exit('Gagal menyiapkan statement: ' . mysqli_error($Conn));
    }

    // =========================================================
    // COUNTER
    // =========================================================
    $berhasil = 0;
    $gagal    = 0;

    // =========================================================
    // LOOP DATA
    // =========================================================
    while ($data = mysqli_fetch_assoc($query)) {

        $id_anggota = (int) $data['id_anggota'];

        // -----------------------------------------------------
        // ID PASIEN RANDOM 8 DIGIT
        // -----------------------------------------------------
        $random = str_pad(
            (string) random_int(0, 99999999),
            8,
            '0',
            STR_PAD_LEFT
        );

        $id_pasien = 'P-' . $random;

        // -----------------------------------------------------
        // DUMMY TANGGAL LAHIR
        // -----------------------------------------------------
        $tanggal_lahir = date(
            'Y-m-d',
            random_int(
                strtotime('1950-01-01'),
                strtotime('2010-12-31')
            )
        );

        // -----------------------------------------------------
        // DUMMY TEMPAT LAHIR
        // -----------------------------------------------------
        $tempat_lahir = $tempat_lahir_list[
            array_rand($tempat_lahir_list)
        ];

        // -----------------------------------------------------
        // BIND PARAMETER
        // -----------------------------------------------------
        mysqli_stmt_bind_param(
            $stmt,
            "sssssssssi",
            $id_pasien,
            $tempat_lahir,
            $tanggal_lahir,
            $now,
            $SessionIdAkses,
            $SessionNama,
            $now,
            $SessionIdAkses,
            $SessionNama,
            $id_anggota
        );

        // -----------------------------------------------------
        // EXECUTE
        // -----------------------------------------------------
        if (mysqli_stmt_execute($stmt)) {

            $berhasil++;

            echo '
                <span style="color:green;">
                    Update ID Anggota ' . $id_anggota . '
                    berhasil → ' . htmlspecialchars($id_pasien) . '
                </span>
                <br>
            ';

        } else {

            $gagal++;

            echo '
                <span style="color:red;">
                    Update ID Anggota ' . $id_anggota . '
                    gagal: ' . htmlspecialchars(mysqli_stmt_error($stmt)) . '
                </span>
                <br>
            ';
        }
    }

    // =========================================================
    // TUTUP STATEMENT
    // =========================================================
    mysqli_stmt_close($stmt);

    // =========================================================
    // HASIL AKHIR
    // =========================================================
    echo '<hr>';
    echo '<b>Proses Selesai</b><br>';
    echo 'Berhasil: ' . $berhasil . '<br>';
    echo 'Gagal: ' . $gagal . '<br>';
?>
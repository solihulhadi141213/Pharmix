<?php
    //------------------------------------------
    // Koneksi, Session dan Helper
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    //------------------------------------------
    // Format Response
    header('Content-Type: application/json; charset=utf-8');

    //------------------------------------------
    // Timezone
    date_default_timezone_set('Asia/Jakarta');

    //------------------------------------------
    // Default Response
    $response = [
        'status'   => 'error',
        'message'  => 'Terjadi kesalahan.',
        'html'     => '',
        'filename' => ''
    ];

    //------------------------------------------
    // Helper Response Error
    function responseError($message)
    {
        echo json_encode([
            'status'   => 'error',
            'message'  => $message,
            'html'     => '',
            'filename' => ''
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    //------------------------------------------
    // Format Decimal
    function formatDecimal($value)
    {
        if ($value === null || $value === '') {
            return '0';
        }

        $value = number_format(
            (float) $value,
            2,
            '.',
            ''
        );

        $value = rtrim($value, '0');
        $value = rtrim($value, '.');

        return str_replace('.', ',', $value);
    }

    //------------------------------------------
    // Escape HTML
    function e($value)
    {
        return htmlspecialchars(
            (string) ($value ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );
    }

    //------------------------------------------
    // Validasi Session
    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir.');
    }

    //------------------------------------------
    // Validasi Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }

    //------------------------------------------
    // Tangkap Parameter
    $id_medication_request_group = (int) (
        $_POST['id_medication_request_group'] ?? 0
    );

    if ($id_medication_request_group < 1) {
        responseError('ID resep tidak valid.');
    }

    //------------------------------------------
    // Ambil Setting General
    $id_setting_general = 1;

    $stmt = $Conn->prepare("
        SELECT
            title_page,
            alamat_bisnis,
            email_bisnis,
            telepon_bisnis,
            logo
        FROM setting_general
        WHERE id_setting_general = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseError('Gagal mempersiapkan setting aplikasi.');
    }

    $stmt->bind_param("i", $id_setting_general);
    $stmt->execute();

    $result  = $stmt->get_result();
    $setting = $result->fetch_assoc();

    $stmt->close();

    //------------------------------------------
    // Informasi Faskes
    $title_page     = trim($setting['title_page'] ?? '');
    $alamat_bisnis  = trim($setting['alamat_bisnis'] ?? '');
    $email_bisnis   = trim($setting['email_bisnis'] ?? '');
    $telepon_bisnis = trim($setting['telepon_bisnis'] ?? '');

    //------------------------------------------
    // Ambil Resep + Pasien
    $stmt = $Conn->prepare("
        SELECT
            mrg.*,
            a.id_pasien,
            a.nama,
            a.gender,
            a.tempat_lahir,
            a.tanggal_lahir
        FROM medication_request_group AS mrg
        LEFT JOIN anggota AS a
            ON a.id_anggota = mrg.id_anggota
        WHERE mrg.id_medication_request_group = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseError('Gagal mempersiapkan data resep.');
    }

    $stmt->bind_param(
        "i",
        $id_medication_request_group
    );

    $stmt->execute();

    $result = $stmt->get_result();
    $resep  = $result->fetch_assoc();

    $stmt->close();

    //------------------------------------------
    // Validasi Resep
    if (!$resep) {
        responseError('Data resep tidak ditemukan.');
    }

    //------------------------------------------
    // Informasi Pasien
    $id_pasien     = trim($resep['id_pasien'] ?? '');
    $nama_pasien   = trim($resep['nama'] ?? $resep['nama_pasien'] ?? '');
    $gender         = trim($resep['gender'] ?? '');
    $tempat_lahir  = trim($resep['tempat_lahir'] ?? '');
    $tanggal_lahir = $resep['tanggal_lahir'] ?? '';

    //------------------------------------------
    // Format Gender
    if ($gender === 'Male') {
        $gender_display = 'Laki-laki';
    } elseif ($gender === 'Female') {
        $gender_display = 'Perempuan';
    } else {
        $gender_display = '-';
    }

    //------------------------------------------
    // Format Tanggal Lahir
    if (!empty($tanggal_lahir)) {
        $tanggal_lahir = date(
            'd-m-Y',
            strtotime($tanggal_lahir)
        );
    } else {
        $tanggal_lahir = '-';
    }

    //------------------------------------------
    // Tempat Tanggal Lahir
    $ttl = '';

    if ($tempat_lahir !== '') {
        $ttl .= $tempat_lahir;
    }

    if ($tanggal_lahir !== '-') {

        if ($ttl !== '') {
            $ttl .= ', ';
        }

        $ttl .= $tanggal_lahir;
    }

    if ($ttl === '') {
        $ttl = '-';
    }

    //------------------------------------------
    // Informasi Resep
    $datetime_creat = $resep['datetime_creat'] ?? '';

    if (!empty($datetime_creat)) {
        $datetime_creat = date(
            'd-m-Y H:i',
            strtotime($datetime_creat)
        );
    } else {
        $datetime_creat = '-';
    }

    $dokter_nama = trim(
        $resep['dokter_nama'] ?? ''
    );

    if ($dokter_nama === '') {
        $dokter_nama = '-';
    }

    $no_resep_nasional = trim(
        $resep['no_resep_nasional'] ?? ''
    );

    //------------------------------------------
    // Ambil Item Resep
    $stmt = $Conn->prepare("
        SELECT *
        FROM medication_request
        WHERE id_medication_request_group = ?
        ORDER BY MedicationRequestId ASC
    ");

    if (!$stmt) {
        responseError('Gagal mempersiapkan item resep.');
    }

    $stmt->bind_param(
        "i",
        $id_medication_request_group
    );

    $stmt->execute();

    $resultItem = $stmt->get_result();

    //------------------------------------------
    // Item HTML
    $itemHtml = '';
    $no = 1;

    while ($item = $resultItem->fetch_assoc()) {

        //------------------------------------------
        // Data Item
        $name_medication = trim(
            $item['name_medication'] ?? ''
        );

        $frequency = (int) (
            $item['dosage_inst_frequency'] ?? 0
        );

        $dose_value = formatDecimal(
            $item['dose_value'] ?? 0
        );

        $dose_unit = trim(
            $item['dose_unit'] ?? ''
        );

        $dispense_value = formatDecimal(
            $item['dispense_value'] ?? 0
        );

        $dispense_unit = trim(
            $item['dispense_unit'] ?? ''
        );

        $dosage_inst_text = trim(
            $item['dosage_inst_text'] ?? ''
        );

        $racikan_code = trim(
            $item['racikan_code'] ?? ''
        );

        //------------------------------------------
        // Fallback
        if ($dosage_inst_text === '') {
            $dosage_inst_text = '-';
        }

        //------------------------------------------
        // Ingredient
        $ingredientHtml = '';

        if (
            $racikan_code !== 'NC' &&
            !empty($item['ingredient'])
        ) {

            $ingredient = json_decode(
                $item['ingredient'],
                true
            );

            if (is_array($ingredient) && count($ingredient) > 0) {

                $ingredientHtml .= '
                    <div style="
                        margin-top:8px;
                        padding:8px 10px;
                        background:#f8f8f8;
                        border-left:3px solid #666;
                    ">
                        <div style="
                            font-size:11px;
                            font-weight:bold;
                            margin-bottom:5px;
                        ">
                            Komposisi Racikan:
                        </div>
                ';

                foreach ($ingredient as $ing) {

                    $nama_kfa = e(
                        $ing['nama_kfa'] ?? '-'
                    );

                    $jumlah_numerator = e(
                        $ing['jumlah_numerator'] ?? ''
                    );

                    $nama_numerator = e(
                        $ing['nama_numerator'] ?? ''
                    );

                    $jumlah_denominator = e(
                        $ing['jumlah_denominator'] ?? ''
                    );

                    $nama_denominator = e(
                        $ing['nama_denominator'] ?? ''
                    );

                    //------------------------------------------
                    // Strength
                    $strength = '';

                    if ($jumlah_numerator !== '') {
                        $strength .=
                            $jumlah_numerator .
                            ' ' .
                            $nama_numerator;
                    }

                    if ($jumlah_denominator !== '') {

                        if ($strength !== '') {
                            $strength .= ' / ';
                        }

                        $strength .=
                            $jumlah_denominator .
                            ' ' .
                            $nama_denominator;
                    }

                    $ingredientHtml .= '
                        <div style="
                            margin-bottom:3px;
                            font-size:11px;
                        ">
                            • '.$nama_kfa;

                    if ($strength !== '') {
                        $ingredientHtml .= '
                            <span style="color:#555;">
                                ('.$strength.')
                            </span>
                        ';
                    }

                    $ingredientHtml .= '
                        </div>
                    ';
                }

                $ingredientHtml .= '</div>';
            }
        }

        //------------------------------------------
        // Item
        $itemHtml .= '
            <div style="
                border-bottom:1px solid #d5d5d5;
                padding:10px 0;
            ">

                <table style="
                    width:100%;
                    border-collapse:collapse;
                ">
                    <tr>
                        <td style="
                            width:28px;
                            vertical-align:top;
                            font-size:12px;
                            font-weight:bold;
                        ">
                            '.$no.'.
                        </td>

                        <td style="
                            vertical-align:top;
                        ">
                            <div style="
                                font-size:13px;
                                font-weight:bold;
                                margin-bottom:5px;
                            ">
                                '.e($name_medication).'
                            </div>

                            <table style="
                                width:100%;
                                font-size:11px;
                                border-collapse:collapse;
                            ">
                                <tr>
                                    <td style="width:100px;">
                                        Dosis
                                    </td>
                                    <td style="width:10px;">:</td>
                                    <td>
                                        '.$frequency.'
                                        ×
                                        '.$dose_value.'
                                        '.e($dose_unit).'
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        Jumlah
                                    </td>
                                    <td>:</td>
                                    <td>
                                        '.$dispense_value.'
                                        '.e($dispense_unit).'
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        Instruksi
                                    </td>
                                    <td>:</td>
                                    <td>
                                        '.e($dosage_inst_text).'
                                    </td>
                                </tr>
                            </table>

                            '.$ingredientHtml.'
                        </td>
                    </tr>
                </table>

            </div>
        ';

        $no++;
    }

    $stmt->close();

    //------------------------------------------
    // Jika Item Kosong
    if ($no === 1) {

        $itemHtml = '
            <div style="
                padding:20px;
                text-align:center;
                font-size:12px;
                color:#777;
            ">
                Belum ada item resep.
            </div>
        ';
    }

    //------------------------------------------
    // NRN
    $nrnHtml = '';

    if ($no_resep_nasional !== '') {

        $nrnHtml = '
            <tr>
                <td>No. Resep Nasional</td>
                <td>:</td>
                <td>'.e($no_resep_nasional).'</td>
            </tr>
        ';
    }

    //------------------------------------------
    // Susun Preview
    $html = '
        <div
            id="lembar_resep"
            data-id="'.$id_medication_request_group.'"
            style="
                width:100%;
                max-width:210mm;
                margin:0 auto;
                background:#ffffff;
                color:#222222;
                padding:8mm;
                font-family:Arial, Helvetica, sans-serif;
                box-sizing:border-box;
            "
        >

            <!-- HEADER -->
            <div style="
                text-align:center;
                border-bottom:2px solid #222;
                padding-bottom:10px;
                margin-bottom:15px;
            ">

                <div style="
                    font-size:20px;
                    font-weight:bold;
                    text-transform:uppercase;
                ">
                    '.e($title_page).'
                </div>

                <div style="
                    margin-top:5px;
                    font-size:11px;
                    line-height:1.5;
                ">
                    '.e($alamat_bisnis).'
                </div>

                <div style="
                    font-size:11px;
                    line-height:1.5;
                ">
                    Telp: '.e($telepon_bisnis).'
                    &nbsp; | &nbsp;
                    Email: '.e($email_bisnis).'
                </div>

            </div>

            <!-- TITLE -->
            <div style="
                text-align:center;
                font-size:16px;
                font-weight:bold;
                margin-bottom:15px;
                text-transform:uppercase;
            ">
                RESEP OBAT
            </div>

            <!-- INFORMASI -->
            <table style="
                width:100%;
                border-collapse:collapse;
                margin-bottom:15px;
            ">
                <tr>

                    <!-- PASIEN -->
                    <td style="
                        width:50%;
                        vertical-align:top;
                        padding-right:15px;
                    ">

                        <div style="
                            font-size:12px;
                            font-weight:bold;
                            margin-bottom:7px;
                            border-bottom:1px solid #aaa;
                            padding-bottom:4px;
                        ">
                            Informasi Pasien
                        </div>

                        <table style="
                            width:100%;
                            font-size:11px;
                            border-collapse:collapse;
                        ">
                            <tr>
                                <td style="width:100px;">No. RM</td>
                                <td style="width:10px;">:</td>
                                <td>'.e($id_pasien).'</td>
                            </tr>

                            <tr>
                                <td>Nama</td>
                                <td>:</td>
                                <td>
                                    <b>'.e($nama_pasien).'</b>
                                </td>
                            </tr>

                            <tr>
                                <td>Jenis Kelamin</td>
                                <td>:</td>
                                <td>'.e($gender_display).'</td>
                            </tr>

                            <tr>
                                <td>Tempat/Tgl Lahir</td>
                                <td>:</td>
                                <td>'.e($ttl).'</td>
                            </tr>
                        </table>

                    </td>

                    <!-- RESEP -->
                    <td style="
                        width:50%;
                        vertical-align:top;
                        padding-left:15px;
                        border-left:1px solid #ccc;
                    ">

                        <div style="
                            font-size:12px;
                            font-weight:bold;
                            margin-bottom:7px;
                            border-bottom:1px solid #aaa;
                            padding-bottom:4px;
                        ">
                            Informasi Resep
                        </div>

                        <table style="
                            width:100%;
                            font-size:11px;
                            border-collapse:collapse;
                        ">
                            <tr>
                                <td style="width:100px;">
                                    Tanggal
                                </td>
                                <td style="width:10px;">:</td>
                                <td>
                                    '.e($datetime_creat).'
                                </td>
                            </tr>

                            <tr>
                                <td>Dokter</td>
                                <td>:</td>
                                <td>
                                    <b>'.e($dokter_nama).'</b>
                                </td>
                            </tr>

                            '.$nrnHtml.'
                        </table>

                    </td>

                </tr>
            </table>

            <!-- ITEM RESEP -->
            <div style="
                font-size:12px;
                font-weight:bold;
                padding:6px 8px;
                background:#eeeeee;
                border-top:1px solid #aaa;
                border-bottom:1px solid #aaa;
            ">
                Item Resep
            </div>

            '.$itemHtml.'

            <!-- FOOTER -->
            <div style="
                margin-top:35px;
                width:100%;
                font-size:11px;
            ">
                <table style="
                    width:100%;
                    border-collapse:collapse;
                ">
                    <tr>
                        <td style="
                            width:60%;
                        ">
                        </td>

                        <td style="
                            width:40%;
                            text-align:center;
                            vertical-align:top;
                        ">
                            <div>
                                Dokter Pemberi Resep
                            </div>

                            <div style="
                                height:65px;
                            ">
                            </div>

                            <div style="
                                font-weight:bold;
                                border-bottom:1px solid #333;
                                display:inline-block;
                                min-width:150px;
                                padding-bottom:2px;
                            ">
                                '.e($dokter_nama).'
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    ';

    //------------------------------------------
    // Filename
    $filename = 'Resep';

    if ($id_pasien !== '') {
        $filename .= '_' . preg_replace(
            '/[^A-Za-z0-9\-]/',
            '',
            $id_pasien
        );
    }

    $filename .= '_' . date('Ymd_His');

    //------------------------------------------
    // Response Success
    echo json_encode([
        'status'   => 'success',
        'message'  => 'Preview resep berhasil dibuat.',
        'html'     => $html,
        'filename' => $filename
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
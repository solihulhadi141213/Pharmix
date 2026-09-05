<?php
    //------------------------------------------
    // Koneksi, Session dan Helper
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    //------------------------------------------
    // Timezone
    date_default_timezone_set('Asia/Jakarta');

    //------------------------------------------
    // Validasi Session
    if (empty($SessionIdAkses)) {
        die('Sesi akses sudah berakhir.');
    }

    //------------------------------------------
    // Tangkap ID
    $id_medication_request_group = (int) ($_GET['id_medication_request_group'] ?? 0);

    if ($id_medication_request_group < 1) {
        die('ID resep tidak valid.');
    }

    //------------------------------------------
    // Helper
    function e($value)
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    function formatDecimal($value)
    {
        if ($value === null || $value === '') {
            return '0';
        }

        $value = number_format((float) $value, 2, '.', '');
        $value = rtrim($value, '0');
        $value = rtrim($value, '.');

        return str_replace('.', ',', $value);
    }

    //------------------------------------------
    // Ambil Setting
    $id_setting_general = 1;

    $stmt = $Conn->prepare("
        SELECT
            title_page,
            alamat_bisnis,
            email_bisnis,
            telepon_bisnis
        FROM setting_general
        WHERE id_setting_general = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $id_setting_general);
    $stmt->execute();

    $result  = $stmt->get_result();
    $setting = $result->fetch_assoc();

    $stmt->close();

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

    $stmt->bind_param("i", $id_medication_request_group);
    $stmt->execute();

    $result = $stmt->get_result();
    $resep  = $result->fetch_assoc();

    $stmt->close();

    if (!$resep) {
        die('Data resep tidak ditemukan.');
    }

    //------------------------------------------
    // Informasi Pasien
    $id_pasien     = trim($resep['id_pasien'] ?? '');
    $nama_pasien   = trim($resep['nama'] ?? $resep['nama_pasien'] ?? '');
    $gender        = trim($resep['gender'] ?? '');
    $tempat_lahir  = trim($resep['tempat_lahir'] ?? '');
    $tanggal_lahir = $resep['tanggal_lahir'] ?? '';

    $gender_display = '-';

    if ($gender === 'Male') {
        $gender_display = 'Laki-laki';
    }

    if ($gender === 'Female') {
        $gender_display = 'Perempuan';
    }

    //------------------------------------------
    // TTL
    if (!empty($tanggal_lahir)) {
        $tanggal_lahir = date('d-m-Y', strtotime($tanggal_lahir));
    } else {
        $tanggal_lahir = '-';
    }

    $ttl = trim($tempat_lahir . ($tempat_lahir !== '' ? ', ' : '') . $tanggal_lahir);

    //------------------------------------------
    // Informasi Resep
    $datetime_creat = !empty($resep['datetime_creat'])
        ? date('d-m-Y H:i', strtotime($resep['datetime_creat']))
        : '-';

    $dokter_nama = trim($resep['dokter_nama'] ?? '-');
    $no_resep_nasional = trim($resep['no_resep_nasional'] ?? '');

    //------------------------------------------
    // Ambil Item Resep
    $stmt = $Conn->prepare("
        SELECT *
        FROM medication_request
        WHERE id_medication_request_group = ?
        ORDER BY MedicationRequestId ASC
    ");

    $stmt->bind_param("i", $id_medication_request_group);
    $stmt->execute();

    $resultItem = $stmt->get_result();

    $itemHtml = '';
    $no       = 1;

    while ($item = $resultItem->fetch_assoc()) {
        $name_medication  = trim($item['name_medication'] ?? '');
        $frequency        = (int) ($item['dosage_inst_frequency'] ?? 0);
        $dose_value       = formatDecimal($item['dose_value'] ?? 0);
        $dose_unit        = trim($item['dose_unit'] ?? '');
        $dispense_value   = formatDecimal($item['dispense_value'] ?? 0);
        $dispense_unit    = trim($item['dispense_unit'] ?? '');
        $dosage_inst_text = trim($item['dosage_inst_text'] ?? '-');
        $racikan_code     = trim($item['racikan_code'] ?? '');

        //------------------------------------------
        // Ingredient
        $ingredientHtml = '';

        if ($racikan_code !== 'NC' && !empty($item['ingredient'])) {
            $ingredient = json_decode($item['ingredient'], true);

            if (is_array($ingredient)) {
                $ingredientHtml .= '
                    <div class="ingredient">
                        <b>Komposisi Racikan:</b>
                ';

                foreach ($ingredient as $ing) {
                    $strength = '';

                    if (!empty($ing['jumlah_numerator'])) {
                        $strength .= e($ing['jumlah_numerator']) . ' ' . e($ing['nama_numerator'] ?? '');
                    }

                    if (!empty($ing['jumlah_denominator'])) {
                        if ($strength !== '') {
                            $strength .= ' / ';
                        }

                        $strength .= e($ing['jumlah_denominator']) . ' ' . e($ing['nama_denominator'] ?? '');
                    }

                    $ingredientHtml .= '
                        <div>
                            • ' . e($ing['nama_kfa'] ?? '-') . '
                            ' . ($strength !== '' ? '(' . $strength . ')' : '') . '
                        </div>
                    ';
                }

                $ingredientHtml .= '</div>';
            }
        }

        //------------------------------------------
        // Item
        $itemHtml .= '
            <div class="item-resep">

                <div class="nama-obat">
                    ' . $no . '. ' . e($name_medication) . '
                </div>

                <table class="table-item">
                    <tr>
                        <td>Dosis</td>
                        <td>:</td>
                        <td>
                            ' . $frequency . '
                            ×
                            ' . $dose_value . '
                            ' . e($dose_unit) . '
                        </td>
                    </tr>

                    <tr>
                        <td>Jumlah</td>
                        <td>:</td>
                        <td>
                            ' . $dispense_value . '
                            ' . e($dispense_unit) . '
                        </td>
                    </tr>

                    <tr>
                        <td>Instruksi</td>
                        <td>:</td>
                        <td>
                            ' . e($dosage_inst_text) . '
                        </td>
                    </tr>
                </table>

                ' . $ingredientHtml . '

            </div>
        ';

        $no++;
    }

    $stmt->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Resep</title>
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
            background: #fff;
        }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        .lembar-resep {
            width: 100%;
            max-width: 190mm;
            margin: 0 auto;
        }

        .header-resep {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .nama-faskes {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .alamat-faskes,
        .kontak-faskes {
            font-size: 11px;
            line-height: 1.5;
        }

        .judul-resep {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 14px;
        }

        .informasi {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .informasi > tbody > tr > td {
            width: 50%;
            vertical-align: top;
        }

        .informasi-pasien {
            padding-right: 10px;
        }

        .informasi-resep {
            padding-left: 10px;
            border-left: 1px solid #bbb;
        }

        .subjudul {
            font-size: 12px;
            font-weight: bold;
            border-bottom: 1px solid #aaa;
            padding-bottom: 4px;
            margin-bottom: 5px;
        }

        .table-info {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .table-info td:first-child {
            width: 100px;
        }

        .judul-item {
            font-size: 12px;
            font-weight: bold;
            background: #eee;
            padding: 6px 8px;
            border-top: 1px solid #aaa;
            border-bottom: 1px solid #aaa;
        }

        .item-resep {
            padding: 9px 0;
            border-bottom: 1px solid #ddd;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .nama-obat {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .table-item {
            width: 100%;
            font-size: 11px;
            border-collapse: collapse;
        }

        .table-item td:first-child {
            width: 100px;
        }

        .table-item td:nth-child(2) {
            width: 10px;
        }

        .ingredient {
            margin-top: 6px;
            padding: 7px 10px;
            background: #f7f7f7;
            border-left: 3px solid #666;
            font-size: 10px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .tanda-tangan {
            width: 100%;
            margin-top: 30px;
            font-size: 11px;
        }

        .tanda-tangan td:first-child {
            width: 60%;
        }

        .dokter {
            width: 40%;
            text-align: center;
        }

        .ruang-ttd {
            height: 60px;
        }

        @media print {
            html,
            body {
                width: 210mm;
                background: #fff;
            }

            .lembar-resep {
                width: 100%;
                max-width: none;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="lembar-resep">

        <!-- Header -->
        <div class="header-resep">
            <div class="nama-faskes"><?= e($title_page); ?></div>
            <div class="alamat-faskes"><?= e($alamat_bisnis); ?></div>
            <div class="kontak-faskes">
                Telp: <?= e($telepon_bisnis); ?>
                |
                Email: <?= e($email_bisnis); ?>
            </div>
        </div>

        <!-- Judul -->
        <div class="judul-resep">
            RESEP OBAT
        </div>

        <!-- Informasi -->
        <table class="informasi">
            <tr>
                <td class="informasi-pasien">
                    <div class="subjudul">
                        Informasi Pasien
                    </div>
                    <table class="table-info">
                        <tr>
                            <td>No. RM</td>
                            <td>:</td>
                            <td><?= e($id_pasien); ?></td>
                        </tr>
                        <tr>
                            <td>Nama</td>
                            <td>:</td>
                            <td><b><?= e($nama_pasien); ?></b></td>
                        </tr>
                        <tr>
                            <td>Jenis Kelamin</td>
                            <td>:</td>
                            <td><?= e($gender_display); ?></td>
                        </tr>
                        <tr>
                            <td>Tempat/Tgl Lahir</td>
                            <td>:</td>
                            <td><?= e($ttl); ?></td>
                        </tr>
                    </table>
                </td>

                <td class="informasi-resep">
                    <div class="subjudul">
                        Informasi Resep
                    </div>
                    <table class="table-info">
                        <tr>
                            <td>Tanggal</td>
                            <td>:</td>
                            <td><?= e($datetime_creat); ?></td>
                        </tr>
                        <tr>
                            <td>Dokter</td>
                            <td>:</td>
                            <td><b><?= e($dokter_nama); ?></b></td>
                        </tr>
                        <?php if ($no_resep_nasional !== '') { ?>
                            <tr>
                                <td>No. Resep Nasional</td>
                                <td>:</td>
                                <td><?= e($no_resep_nasional); ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Item -->
        <div class="judul-item">
            Item Resep
        </div>

        <?= $itemHtml; ?>

        <!-- Tanda tangan -->
        <table class="tanda-tangan">
            <tr>
                <td></td>
                <td class="dokter">
                    <div>
                        Dokter Pemberi Resep
                    </div>
                    <div class="ruang-ttd"></div>
                    <div>
                        <b><?= e($dokter_nama); ?></b>
                    </div>
                </td>
            </tr>
        </table>

    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });

        window.addEventListener('afterprint', function () {
            window.close();
        });
    </script>

</body>

</html>
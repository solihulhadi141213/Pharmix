<?php
    //------------------------------------------
    // Koneksi, Session dan Setting
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/SettingGeneral.php";

    //------------------------------------------
    // Timezone & Validasi
    date_default_timezone_set('Asia/Jakarta');

    if (empty($SessionIdAkses)) {
        die('Sesi akses sudah berakhir.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die('Metode request tidak valid.');
    }

    //------------------------------------------
    // Helper & Escape
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
    // Tangkap MedicationRequestId
    $MedicationRequestId = trim($_POST['MedicationRequestId'] ?? '');

    if ($MedicationRequestId === '') {
        die('ID item resep tidak valid.');
    }

    //------------------------------------------
    // Ambil Data
    $sql = "
        SELECT
            mr.MedicationRequestId, mr.dosage_inst_text, mr.dosage_inst_frequency,
            mr.dosage_inst_period, mr.dosage_inst_period_unit, mr.dose_value,
            mr.dose_unit, mr.dispense_value, mr.dispense_unit,
            mrg.nama_pasien, mrg.datetime_creat, a.id_pasien
        FROM medication_request AS mr
        INNER JOIN medication_request_group AS mrg
            ON mrg.id_medication_request_group = mr.id_medication_request_group
        LEFT JOIN anggota AS a
            ON a.id_anggota = mrg.id_anggota
        WHERE mr.MedicationRequestId = ?
        LIMIT 1
    ";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {
        die('Gagal mempersiapkan data etiket.');
    }

    $stmt->bind_param("s", $MedicationRequestId);

    if (!$stmt->execute()) {
        $stmt->close();
        die('Gagal mengambil data etiket.');
    }

    $result = $stmt->get_result();
    $data   = $result->fetch_assoc();
    $stmt->close();

    if (!$data) {
        die('Data item resep tidak ditemukan.');
    }

    //------------------------------------------
    // Variabel Data
    $id_pasien        = trim($data['id_pasien'] ?? '') !== '' ? trim($data['id_pasien']) : '-';
    $nama_pasien      = trim($data['nama_pasien'] ?? '') !== '' ? trim($data['nama_pasien']) : '-';
    $frequency        = (int) ($data['dosage_inst_frequency'] ?? 0);
    $dose_value       = formatDecimal($data['dose_value'] ?? 0);
    $dose_unit        = trim($data['dose_unit'] ?? '');
    $dispense_value   = formatDecimal($data['dispense_value'] ?? 0);
    $dispense_unit    = trim($data['dispense_unit'] ?? '');
    $dosage_inst_text = trim($data['dosage_inst_text'] ?? '') !== '' ? trim($data['dosage_inst_text']) : '-';

    $datetime_creat = !empty($data['datetime_creat'])
        ? date('d-m-Y H:i', strtotime($data['datetime_creat']))
        : '-';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Etiket <?= e($MedicationRequestId); ?></title>
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
            color: #111111;
        }

        @page {
            size: 80mm 50mm;
            margin: 2.5mm;
        }

        .etiket {
            width: 75mm;
            min-height: 45mm;
            margin: 0 auto;
            padding: 0;
            font-size: 9.5pt;
            line-height: 1.15;
        }

        .header {
            text-align: center;
            border-bottom: 0.3mm solid #555;
            padding-bottom: 1.2mm;
            margin-bottom: 1.2mm;
        }

        .header-title {
            font-size: 11.5pt;
            line-height: 1.05;
            font-weight: bold;
        }

        .header-address,
        .header-contact {
            font-size: 7pt;
            line-height: 1.1;
        }

        .header-address {
            margin-top: 0.7mm;
        }

        .item-id {
            text-align: right;
            font-size: 6.5pt;
            color: #555;
            margin-bottom: 0.8mm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            line-height: 1.2;
        }

        td {
            padding: 0.35mm 0;
            vertical-align: top;
        }

        td.label {
            width: 19mm;
        }

        td.separator {
            width: 3mm;
            text-align: center;
        }

        .separator-line {
            border-top: 0.25mm dashed #999;
            margin: 1mm 0;
        }

        .footer {
            border-top: 0.25mm solid #999;
            margin-top: 1.2mm;
            padding-top: 1mm;
            text-align: center;
            font-size: 8pt;
            font-style: italic;
        }

        @media print {
            html,
            body {
                width: 80mm;
                height: 50mm;
            }

            .etiket {
                width: 75mm;
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="etiket">

        <!-- HEADER -->
        <div class="header">
            <div class="header-title"><?= e($title_page); ?></div>
            <div class="header-address"><?= e($alamat_bisnis); ?></div>
            <div class="header-contact">
                Telp. <?= e($telepon_bisnis); ?> | <?= e($email_bisnis); ?>
            </div>
        </div>

        <!-- ID ITEM -->
        <div class="item-id">
            ID.Item : <?= e($MedicationRequestId); ?>
        </div>

        <!-- PASIEN -->
        <table>
            <tr>
                <td class="label">No. RM</td>
                <td class="separator">:</td>
                <td><?= e($id_pasien); ?></td>
            </tr>
            <tr>
                <td class="label">Nama</td>
                <td class="separator">:</td>
                <td><?= e($nama_pasien); ?></td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td class="separator">:</td>
                <td><?= e($datetime_creat); ?></td>
            </tr>
        </table>

        <div class="separator-line"></div>

        <!-- ATURAN PAKAI -->
        <table>
            <tr>
                <td class="label">Dosis</td>
                <td class="separator">:</td>
                <td><?= e($frequency); ?> × <?= e($dose_value); ?> <?= e($dose_unit); ?></td>
            </tr>
            <tr>
                <td class="label">Jumlah</td>
                <td class="separator">:</td>
                <td><?= e($dispense_value); ?> <?= e($dispense_unit); ?></td>
            </tr>
            <tr>
                <td class="label">Aturan Pakai</td>
                <td class="separator">:</td>
                <td><?= e($dosage_inst_text); ?></td>
            </tr>
        </table>

        <!-- FOOTER -->
        <div class="footer">
            "Semoga Lekas Sembuh"
        </div>

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
<?php
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";

    if (empty($SessionIdAkses)) {
        die('Sesi akses sudah berakhir. Silakan login kembali.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die('Metode request tidak valid.');
    }

    // -------------------------------------------------
    // Tangkap Parameter
    // -------------------------------------------------
    $periode_data = trim($_POST['periode_data'] ?? '');
    $mode_data    = trim($_POST['mode_data'] ?? '');
    $type_file    = strtoupper(trim($_POST['type_file'] ?? 'HTML'));
    $bulan        = trim($_POST['bulan'] ?? '');
    $tahun        = trim($_POST['tahun'] ?? '');

    // -------------------------------------------------
    // Validasi Parameter
    // -------------------------------------------------
    if (!in_array($periode_data, ['Tahunan', 'Bulanan'], true)) {
        die('Periode data tidak valid.');
    }

    if (!in_array($mode_data, ['Daftar Transaksi', 'Rincian Transaksi'], true)) {
        die('Mode data tidak valid.');
    }

    if (!in_array($type_file, ['HTML', 'PDF', 'CSV'], true)) {
        die('Tipe file tidak valid.');
    }

    if ($tahun === '' || !ctype_digit($tahun)) {
        die('Tahun tidak valid.');
    }

    $tahun = (int)$tahun;

    if ($tahun < 1900 || $tahun > 2100) {
        die('Tahun tidak valid.');
    }

    // -------------------------------------------------
    // Validasi Bulan
    // -------------------------------------------------
    if ($periode_data === 'Bulanan') {
        if ($bulan === '' || !ctype_digit($bulan)) {
            die('Bulan tidak valid.');
        }

        $bulan = (int)$bulan;

        if ($bulan < 1 || $bulan > 12) {
            die('Bulan tidak valid.');
        }
    }

    // -------------------------------------------------
    // Tentukan Periode
    // -------------------------------------------------
    $nama_bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    if ($periode_data === 'Tahunan') {
        $tanggal_awal  = $tahun . '-01-01';
        $tanggal_akhir = ($tahun + 1) . '-01-01';
        $periode_label = 'Tahun ' . $tahun;
    } else {
        $tanggal_awal  = sprintf('%04d-%02d-01', $tahun, $bulan);
        $tanggal_akhir = date('Y-m-d', strtotime($tanggal_awal . ' +1 month'));
        $periode_label = $nama_bulan[$bulan] . ' ' . $tahun;
    }

    // -------------------------------------------------
    // Query Data
    // -------------------------------------------------
    $data = [];

    if ($mode_data === 'Daftar Transaksi') {
        $sql = "
            SELECT
                t.id_transaksi,
                t.tanggal,
                t.jumlah,
                t.pembayaran,
                t.status,
                tj.nama AS nama_transaksi,
                tj.kategori AS kategori_transaksi,
                COALESCE(
                    (
                        SELECT SUM(tp.jumlah)
                        FROM transaksi_pembayaran tp
                        WHERE tp.id_transaksi = t.id_transaksi
                    ),
                    0
                ) AS total_pembayaran_termin
            FROM transaksi t
            INNER JOIN transaksi_jenis tj
                ON tj.id_transaksi_jenis = t.id_transaksi_jenis
            WHERE t.tanggal >= ?
            AND t.tanggal < ?
            ORDER BY t.creat_at DESC, t.id_transaksi ASC
        ";
    } else {
        $sql = "
            SELECT
                r.id_transaksi_rincian,
                r.id_transaksi,
                r.rincian_transaksi,
                r.harga,
                r.qty,
                r.satuan,
                r.jumlah,
                t.tanggal,
                t.keterangan,
                t.status
            FROM transaksi_rincian r
            INNER JOIN transaksi t
                ON t.id_transaksi = r.id_transaksi
            WHERE t.tanggal >= ?
            AND t.tanggal < ?
            ORDER BY
                t.tanggal ASC,
                t.id_transaksi ASC,
                r.id_transaksi_rincian ASC
        ";
    }

    $stmt = mysqli_prepare($Conn, $sql);

    if (!$stmt) {
        die('Gagal mempersiapkan query data.');
    }

    mysqli_stmt_bind_param($stmt, 'ss', $tanggal_awal, $tanggal_akhir);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        die('Gagal mengambil data transaksi.');
    }

    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        mysqli_stmt_close($stmt);
        die('Gagal membaca hasil query.');
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    mysqli_stmt_close($stmt);

    // -------------------------------------------------
    // Jika Data Kosong
    // -------------------------------------------------
    if (count($data) === 0) {
        die('Tidak terdapat data transaksi pada periode ' . $periode_label . '.');
    }

    // -------------------------------------------------
    // Helper
    // -------------------------------------------------
    $judul = 'Data Transaksi Operasional Periode ' . $periode_label;

    // =================================================
    // HTML
    // =================================================
    if ($type_file === 'HTML') {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title><?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?></title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                margin: 30px;
            }

            h2 {
                text-align: center;
                margin-bottom: 5px;
            }

            .periode {
                text-align: center;
                margin-bottom: 20px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 6px;
            }

            th {
                background: #eee;
                text-align: center;
            }

            .text-right {
                text-align: right;
            }

            .text-center {
                text-align: center;
            }
        </style>
    </head>

    <body>

    <h2><?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?></h2>
    <div class="periode">
        <?= htmlspecialchars($mode_data, ENT_QUOTES, 'UTF-8') ?>
    </div>

    <?php if ($mode_data === 'Daftar Transaksi') { ?>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Jumlah</th>
                    <th>Cash/Tunai</th>
                    <th>Termin</th>
                    <th>Utang/Piutang</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>

            <?php $no = 1; ?>

            <?php foreach ($data as $row) { ?>
                <?php
                    $id_transaksi = $row['id_transaksi'];
                    $tanggal = $row['tanggal'] ?? '';
                    $nama_transaksi = htmlspecialchars($row['nama_transaksi'] ?? '-', ENT_QUOTES, 'UTF-8');
                    $kategori_transaksi = htmlspecialchars($row['kategori_transaksi'] ?? '-', ENT_QUOTES, 'UTF-8');
                    $jumlah = (int)($row['jumlah'] ?? 0);
                    $pembayaran_cash = (int)($row['pembayaran'] ?? 0);
                    $total_pembayaran_termin = (int)($row['total_pembayaran_termin'] ?? 0);
                    $status = htmlspecialchars($row['status'] ?? '-', ENT_QUOTES, 'UTF-8');
                    
                    // Hitung utang/piutang
                    $total_pembayaran = $pembayaran_cash + $total_pembayaran_termin;
                    $sisa_tagihan = $jumlah - $total_pembayaran;
                    if ($sisa_tagihan < 0) {
                        $sisa_tagihan = 0;
                    }
                ?>

                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($id_transaksi, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($tanggal, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $nama_transaksi ?></td>
                    <td><?= $kategori_transaksi ?></td>
                    <td class="text-right"><?= number_format($jumlah, 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($pembayaran_cash, 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($total_pembayaran_termin, 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($sisa_tagihan, 0, ',', '.') ?></td>
                    <td class="text-center"><?= $status ?></td>
                </tr>

            <?php } ?>

            </tbody>
        </table>

    <?php } else { ?>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Status</th>
                    <th>Rincian</th>
                    <th>Harga</th>
                    <th>QTY</th>
                    <th>Satuan</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>

            <?php $no = 1; ?>

            <?php foreach ($data as $row) { ?>

                <tr>
                    <td class="text-center"><?= $no++ ?></td>

                    <td>
                        <?= htmlspecialchars($row['tanggal'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['keterangan'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td class="text-center">
                        <?= htmlspecialchars($row['status'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['rincian_transaksi'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td class="text-right">
                        <?= number_format((float)($row['harga'] ?? 0), 0, ',', '.') ?>
                    </td>

                    <td class="text-center">
                        <?= htmlspecialchars($row['qty'] ?? '0', ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['satuan'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td class="text-right">
                        <?= number_format((float)($row['jumlah'] ?? 0), 0, ',', '.') ?>
                    </td>
                </tr>

            <?php } ?>

            </tbody>
        </table>

    <?php } ?>

    </body>
    </html>

    <?php
        exit;
    }

    // =================================================
    // CSV
    // =================================================
    if ($type_file === 'CSV') {
        $filename = 'Transaksi_Operasional_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $periode_label) . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        fputcsv($output, [$judul], ';');
        fputcsv($output, [], ';');

        if ($mode_data === 'Daftar Transaksi') {

            fputcsv($output, [
                'No',
                'Kode',
                'Tanggal',
                'Nama',
                'Kategori',
                'Jumlah',
                'Cash/Tunai',
                'Termin',
                'Utang/Piutang',
                'Status'
            ], ';');

            $no = 1;

            foreach ($data as $row) {
                $jumlah = (int)($row['jumlah'] ?? 0);
                $pembayaran_cash = (int)($row['pembayaran'] ?? 0);
                $total_pembayaran_termin = (int)($row['total_pembayaran_termin'] ?? 0);
                $total_pembayaran = $pembayaran_cash + $total_pembayaran_termin;
                $sisa_tagihan = $jumlah - $total_pembayaran;
                if ($sisa_tagihan < 0) {
                    $sisa_tagihan = 0;
                }
                
                fputcsv($output, [
                    $no++,
                    $row['id_transaksi'],
                    $row['tanggal'],
                    $row['nama_transaksi'],
                    $row['kategori_transaksi'],
                    $jumlah,
                    $pembayaran_cash,
                    $total_pembayaran_termin,
                    $sisa_tagihan,
                    $row['status']
                ], ';');
            }

        } else {

            fputcsv($output, [
                'No',
                'Tanggal',
                'Keterangan',
                'Status',
                'Rincian',
                'Harga',
                'QTY',
                'Satuan',
                'Jumlah'
            ], ';');

            $no = 1;

            foreach ($data as $row) {
                fputcsv($output, [
                    $no++,
                    $row['tanggal'],
                    $row['keterangan'],
                    $row['status'],
                    $row['rincian_transaksi'],
                    $row['harga'],
                    $row['qty'],
                    $row['satuan'],
                    $row['jumlah']
                ], ';');
            }
        }

        fclose($output);
        exit;
    }

    // =================================================
    // PDF - MPDF
    // =================================================
    if ($type_file === 'PDF') {

        require_once '../../vendor/autoload.php';

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10
        ]);

        $mpdf->SetTitle($judul);

        $html = '
        <style>
            body {
                font-family: sans-serif;
                font-size: 9px;
            }

            h2 {
                text-align: center;
                margin-bottom: 3px;
            }

            .periode {
                text-align: center;
                margin-bottom: 12px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 4px;
            }

            th {
                background-color: #eeeeee;
                text-align: center;
            }

            .right {
                text-align: right;
            }

            .center {
                text-align: center;
            }
        </style>

        <h2>' . htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') . '</h2>
        <div class="periode">
            ' . htmlspecialchars($mode_data, ENT_QUOTES, 'UTF-8') . '
        </div>
        ';

        if ($mode_data === 'Daftar Transaksi') {

            $html .= '
            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="12%">Kode</th>
                        <th width="13%">Tanggal</th>
                        <th width="14%">Nama</th>
                        <th width="11%">Kategori</th>
                        <th width="10%">Jumlah</th>
                        <th width="10%">Cash/Tunai</th>
                        <th width="10%">Termin</th>
                        <th width="10%">Utang/Piutang</th>
                        <th width="9%">Status</th>
                    </tr>
                </thead>
                <tbody>
            ';

            $no = 1;

            foreach ($data as $row) {
                $jumlah = (int)($row['jumlah'] ?? 0);
                $pembayaran_cash = (int)($row['pembayaran'] ?? 0);
                $total_pembayaran_termin = (int)($row['total_pembayaran_termin'] ?? 0);
                $total_pembayaran = $pembayaran_cash + $total_pembayaran_termin;
                $sisa_tagihan = $jumlah - $total_pembayaran;
                if ($sisa_tagihan < 0) {
                    $sisa_tagihan = 0;
                }

                $html .= '
                    <tr>
                        <td class="center">' . $no++ . '</td>
                        <td>' . htmlspecialchars($row['id_transaksi'] ?? '-', ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . htmlspecialchars($row['tanggal'] ?? '-', ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . htmlspecialchars($row['nama_transaksi'] ?? '-', ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . htmlspecialchars($row['kategori_transaksi'] ?? '-', ENT_QUOTES, 'UTF-8') . '</td>
                        <td class="right">' . number_format($jumlah, 0, ',', '.') . '</td>
                        <td class="right">' . number_format($pembayaran_cash, 0, ',', '.') . '</td>
                        <td class="right">' . number_format($total_pembayaran_termin, 0, ',', '.') . '</td>
                        <td class="right">' . number_format($sisa_tagihan, 0, ',', '.') . '</td>
                        <td class="center">' . htmlspecialchars($row['status'] ?? '-', ENT_QUOTES, 'UTF-8') . '</td>
                    </tr>
                ';
            }

        } else {

            $html .= '
            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="11%">Tanggal</th>
                        <th>Keterangan</th>
                        <th width="9%">Status</th>
                        <th>Rincian</th>
                        <th width="12%">Harga</th>
                        <th width="7%">QTY</th>
                        <th width="10%">Satuan</th>
                        <th width="12%">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
            ';

            $no = 1;

            foreach ($data as $row) {

                $html .= '
                    <tr>
                        <td class="center">' . $no++ . '</td>

                        <td>
                            ' . htmlspecialchars($row['tanggal'] ?? '-', ENT_QUOTES, 'UTF-8') . '
                        </td>

                        <td>
                            ' . htmlspecialchars($row['keterangan'] ?? '-', ENT_QUOTES, 'UTF-8') . '
                        </td>

                        <td class="center">
                            ' . htmlspecialchars($row['status'] ?? '-', ENT_QUOTES, 'UTF-8') . '
                        </td>

                        <td>
                            ' . htmlspecialchars($row['rincian_transaksi'] ?? '-', ENT_QUOTES, 'UTF-8') . '
                        </td>

                        <td class="right">
                            ' . number_format((float)($row['harga'] ?? 0), 0, ',', '.') . '
                        </td>

                        <td class="center">
                            ' . htmlspecialchars($row['qty'] ?? '0', ENT_QUOTES, 'UTF-8') . '
                        </td>

                        <td>
                            ' . htmlspecialchars($row['satuan'] ?? '-', ENT_QUOTES, 'UTF-8') . '
                        </td>

                        <td class="right">
                            ' . number_format((float)($row['jumlah'] ?? 0), 0, ',', '.') . '
                        </td>
                    </tr>
                ';
            }
        }

        $html .= '
                </tbody>
            </table>
        ';

        $mpdf->WriteHTML($html);

        $filename = 'Transaksi_Operasional_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $periode_label) . '.pdf';

        $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        exit;
    }

    die('Tipe export tidak dikenali.');
?>
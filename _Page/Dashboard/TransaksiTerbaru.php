<?php
    // Koneksi Database
    include "../../_Config/Connection.php";

    // Query 5 rincian transaksi terbaru
    $Qry = $Conn->prepare("
        SELECT 
            tjbr.id_transaksi_jual_beli_rincian,
            tjbr.nama_barang,
            tjbr.subtotal,
            tjb.tanggal,
            tjb.kategori
        FROM transaksi_jual_beli_rincian tjbr
        INNER JOIN transaksi_jual_beli tjb 
            ON tjbr.id_transaksi_jual_beli = tjb.id_transaksi_jual_beli
        ORDER BY 
            tjb.tanggal DESC,
            tjbr.id_transaksi_jual_beli_rincian DESC
        LIMIT 5
    ");

    if (!$Qry) {
        echo '
            <div class="alert alert-danger">
                Terjadi kesalahan pada query database.
            </div>
        ';
        exit;
    }

    if (!$Qry->execute()) {
        echo '
            <div class="alert alert-danger">
                Gagal mengambil data transaksi.
            </div>
        ';
        $Qry->close();
        exit;
    }

    $Result = $Qry->get_result();

    if ($Result->num_rows > 0) {

        echo '<div class="list-group list-group-flush">';

        while ($Data = $Result->fetch_assoc()) {

            // Sanitasi Nama Barang
            $nama_barang = htmlspecialchars(
                $Data['nama_barang'] ?? '',
                ENT_QUOTES,
                'UTF-8'
            );

            // Format Tanggal
            $tanggal = date(
                'd/m/Y H:i',
                strtotime($Data['tanggal'])
            );

            // Subtotal
            $subtotal = $Data['subtotal'] ?? 0;

            $subtotal_rp = 'Rp ' . number_format(
                $subtotal,
                0,
                ',',
                '.'
            );

            // Kategori Transaksi
            $kategori = $Data['kategori'] ?? '';

            /*
             * Default Tampilan
             */
            $class_warna = 'text-secondary';
            $icon         = 'bi-circle';

            /*
             * Penjualan
             */
            if ($kategori === 'Penjualan') {
                $class_warna = 'text-success';
                $icon         = 'bi-arrow-up-circle-fill';
            }

            /*
             * Pembelian
             */
            if ($kategori === 'Pembelian') {
                $class_warna = 'text-warning';
                $icon         = 'bi-arrow-down-circle-fill';
            }

            echo '
                <div class="list-group-item px-0">
                    <div class="d-flex justify-content-between align-items-center">

                        <div class="me-2">
                            <div class="fw-bold text-dark">
                                ' . $nama_barang . '
                            </div>

                            <small class="text-muted">
                                <i class="bi bi-calendar-event"></i>
                                ' . $tanggal . '
                            </small>
                            <br>
                            <small class="text-muted"> ' . $kategori . ' </small>
                        </div>

                        <div class="text-end ' . $class_warna . '">
                            <div class="fw-bold">
                                <i class="bi ' . $icon . '"></i>
                                ' . $subtotal_rp . '
                            </div>
                        </div>

                    </div>
                </div>
            ';
        }

        echo '</div>';

    } else {

        echo '
            <div class="text-center text-muted py-3">
                <i class="bi bi-inbox"></i>
                <br>
                Belum ada transaksi.
            </div>
        ';
    }

    $Qry->close();
?>
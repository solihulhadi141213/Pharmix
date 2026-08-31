<?php
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

header('Content-Type: application/json; charset=utf-8');

$response = [
    'status' => 'Error',
    'message' => 'Gagal mengambil data peringatan dashboard.',
    'barang_expire' => [],
    'barang_limit' => [],
    'jatuh_tempo' => []
];

if (empty($SessionIdAkses)) {
    $response['message'] = 'Sesi akses sudah berakhir. Silakan login ulang.';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

function fetchDashboardRows(mysqli $Conn, string $sql): array
{
    $stmt = $Conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Gagal mempersiapkan query dashboard.');
    }
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new Exception('Gagal mengambil data dashboard: ' . $error);
    }

    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

try {
    $response['barang_expire'] = fetchDashboardRows($Conn, "
        SELECT
            bb.id_barang_bacth,
            b.id_barang,
            b.kode_barang,
            b.nama_barang,
            bb.no_batch,
            bb.expired_date,
            bb.reminder_date,
            bb.qty_batch,
            b.satuan_barang
        FROM barang_bacth AS bb
        INNER JOIN barang AS b ON b.id_barang = bb.id_barang
        WHERE bb.status = 'Terdaftar'
          AND bb.qty_batch > 0
          AND bb.expired_date >= CURDATE()
          AND bb.reminder_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY bb.reminder_date ASC, bb.expired_date ASC
        LIMIT 5
    ");

    $response['barang_limit'] = fetchDashboardRows($Conn, "
        SELECT
            id_barang,
            kode_barang,
            nama_barang,
            stok_barang,
            stok_minimum,
            satuan_barang
        FROM barang
        WHERE COALESCE(stok_barang, 0) <= stok_minimum
        ORDER BY (COALESCE(stok_minimum, 0) - COALESCE(stok_barang, 0)) DESC,
                 stok_barang ASC,
                 nama_barang ASC
        LIMIT 5
    ");

    $response['jatuh_tempo'] = fetchDashboardRows($Conn, "
        SELECT *
        FROM (
            SELECT
                tt.id_transaksi_tempo,
                tt.tanggal_tempo,
                tt.kategori,
                tjb.id_transaksi_jual_beli AS id_transaksi,
                COALESCE(tjb.total, 0) - COALESCE(tjb.cash, 0)
                    - COALESCE((
                        SELECT SUM(tp.jumlah)
                        FROM transaksi_pembayaran AS tp
                        WHERE tp.id_transaksi_jual_beli = tjb.id_transaksi_jual_beli
                    ), 0) AS sisa_tagihan
            FROM transaksi_tempo AS tt
            INNER JOIN transaksi_jual_beli AS tjb
                ON tjb.id_transaksi_jual_beli = tt.id_transaksi_jual_beli
            WHERE tt.id_transaksi_jual_beli IS NOT NULL
              AND tjb.status IN ('Utang', 'Piutang')
              AND tt.tanggal_tempo BETWEEN CURDATE()
                  AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)

            UNION ALL

            SELECT
                tt.id_transaksi_tempo,
                tt.tanggal_tempo,
                tt.kategori,
                t.id_transaksi AS id_transaksi,
                COALESCE(t.jumlah, 0) - COALESCE(t.pembayaran, 0)
                    - COALESCE((
                        SELECT SUM(tp.jumlah)
                        FROM transaksi_pembayaran AS tp
                        WHERE tp.id_transaksi = t.id_transaksi
                    ), 0) AS sisa_tagihan
            FROM transaksi_tempo AS tt
            INNER JOIN transaksi AS t ON t.id_transaksi = tt.id_transaksi
            WHERE tt.id_transaksi IS NOT NULL
              AND t.status IN ('Utang', 'Piutang')
              AND tt.tanggal_tempo BETWEEN CURDATE()
                  AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ) AS tempo
        WHERE tempo.sisa_tagihan > 0
        ORDER BY tempo.tanggal_tempo ASC, tempo.id_transaksi_tempo ASC
        LIMIT 5
    ");

    $response['status'] = 'Success';
    $response['message'] = 'Data peringatan dashboard berhasil diambil.';
} catch (Throwable $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);

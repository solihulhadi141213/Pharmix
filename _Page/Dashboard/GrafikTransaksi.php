<?php

header('Content-Type: application/json; charset=utf-8');

// ======================================================
// KONFIGURASI DATABASE
// ======================================================
$host     = '127.0.0.1';
$dbname   = 'pharmix';
$username = 'root';
$password = 'arunaparasilvanursari';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        'status'  => false,
        'message' => 'Koneksi database gagal',
        'error'   => $e->getMessage()
    ]);

    exit;
}


// ======================================================
// TAHUN YANG DITAMPILKAN
// ======================================================
$tahun = date('Y');


// ======================================================
// QUERY TRANSAKSI PENJUALAN & PEMBELIAN
// ======================================================
$sql = "
    SELECT
        MONTH(tanggal) AS bulan,

        SUM(
            CASE
                WHEN kategori = 'Penjualan'
                THEN COALESCE(total, 0)
                ELSE 0
            END
        ) AS penjualan,

        SUM(
            CASE
                WHEN kategori = 'Pembelian'
                THEN COALESCE(total, 0)
                ELSE 0
            END
        ) AS pembelian

    FROM transaksi_jual_beli

    WHERE YEAR(tanggal) = :tahun

    GROUP BY MONTH(tanggal)

    ORDER BY MONTH(tanggal)
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':tahun' => $tahun
]);


// ======================================================
// AMBIL HASIL QUERY
// ======================================================
$dataTransaksi = [];

while ($row = $stmt->fetch()) {

    $dataTransaksi[(int)$row['bulan']] = [
        'penjualan' => (float)$row['penjualan'],
        'pembelian' => (float)$row['pembelian']
    ];
}


// ======================================================
// NAMA BULAN
// ======================================================
$namaBulan = [
    1  => 'Januari',
    2  => 'Februari',
    3  => 'Maret',
    4  => 'April',
    5  => 'Mei',
    6  => 'Juni',
    7  => 'Juli',
    8  => 'Agustus',
    9  => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];


// ======================================================
// BENTUK DATA UNTUK APEXCHARTS
// ======================================================
$result = [];

for ($bulan = 1; $bulan <= 12; $bulan++) {

    $penjualan = 0;
    $pembelian = 0;

    if (isset($dataTransaksi[$bulan])) {
        $penjualan = $dataTransaksi[$bulan]['penjualan'];
        $pembelian = $dataTransaksi[$bulan]['pembelian'];
    }

    $result[] = [
        'x'          => $namaBulan[$bulan],
        'ySimpanan'  => $penjualan,
        'yPinjaman'  => $pembelian
    ];
}


// ======================================================
// OUTPUT JSON
// ======================================================
echo json_encode(
    $result,
    JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK
);
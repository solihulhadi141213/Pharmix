<?php
// ---------------------------------------
// KONFIGURASI
// ---------------------------------------
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');

function responseJson(string $status, string $message, int $code = 200): void {
    http_response_code($code);
    echo json_encode([
        'status'  => $status,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function tanggalValid(string $tanggal): bool {
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $tanggal);
    return $date !== false
        && $date->format('Y-m-d') === $tanggal
        && $tanggal >= '1000-01-01';
}

// ---------------------------------------
// VALIDASI AKSES DAN REQUEST
// ---------------------------------------
if (empty($SessionIdAkses)) {
    responseJson('error', 'Sesi akses sudah berakhir. Silakan login ulang.', 401);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    responseJson('error', 'Metode request tidak valid.', 405);
}

// ---------------------------------------
// AMBIL INPUT
// ---------------------------------------
$input = [];
foreach ([
    'id_barang', 'no_batch', 'expired_date', 'reminder_date',
    'qty_batch', 'status', 'id_barang_satuan'
] as $field) {
    $value = $_POST[$field] ?? '';
    if (!is_string($value)) {
        responseJson('error', 'Format parameter tidak valid.', 422);
    }
    $input[$field] = trim($value);
}

$id_barang        = $input['id_barang'];
$no_batch         = $input['no_batch'];
$expired_date     = $input['expired_date'];
$reminder_date    = $input['reminder_date'];
$qty_batch        = $input['qty_batch'];
$status           = $input['status'];
$id_barang_satuan = $input['id_barang_satuan'];

// ---------------------------------------
// VALIDASI INPUT
// ---------------------------------------
if (!ctype_digit($id_barang) || (float) $id_barang < 1 ||
    (float) $id_barang > 4294967295) {
    responseJson('error', 'ID barang tidak valid.', 422);
}

if ($no_batch === '') {
    responseJson('error', 'Nomor batch wajib diisi.', 422);
}
if (!preg_match('/^.{1,20}$/us', $no_batch)) {
    responseJson('error', 'Nomor batch maksimal 20 karakter dan harus berupa teks valid.', 422);
}

if (!tanggalValid($expired_date)) {
    responseJson('error', 'Tanggal expired tidak valid.', 422);
}
if (!tanggalValid($reminder_date)) {
    responseJson('error', 'Tanggal pemberitahuan tidak valid.', 422);
}
if ($reminder_date > $expired_date) {
    responseJson('error', 'Tanggal pemberitahuan tidak boleh melewati tanggal expired.', 422);
}

// DECIMAL(15,2): maksimal 13 digit bilangan bulat dan 2 desimal.
if (!preg_match('/^\d{1,13}(?:\.\d{1,2})?$/D', $qty_batch) ||
    str_replace(['0', '.'], '', $qty_batch) === '') {
    responseJson('error', 'QTY harus lebih dari 0, maksimal 13 digit dan 2 angka desimal.', 422);
}

if (!in_array($status, ['Terdaftar', 'Terjual'], true)) {
    responseJson('error', 'Status barang tidak valid.', 422);
}

// String kosong berarti menggunakan satuan utama.
if ($id_barang_satuan !== '' &&
    (!ctype_digit($id_barang_satuan) || (float) $id_barang_satuan < 1 ||
     (float) $id_barang_satuan > 4294967295)) {
    responseJson('error', 'ID satuan barang tidak valid.', 422);
}

// ---------------------------------------
// TRANSAKSI DATABASE
// ---------------------------------------
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$transaction = false;

try {
    $Conn->begin_transaction();
    $transaction = true;

    // ---------------------------------------
    // VALIDASI BARANG DAN KONVERSI
    // ---------------------------------------
    $stmt = $Conn->prepare(
        "SELECT konversi FROM barang WHERE id_barang = ? FOR UPDATE"
    );
    $stmt->bind_param('s', $id_barang);
    $stmt->execute();
    $barang = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$barang) {
        throw new DomainException('Barang tidak ditemukan.');
    }

    $qty = $qty_batch;

    if ($id_barang_satuan !== '') {
        $stmt = $Conn->prepare(
            "SELECT konversi_multi FROM barang_satuan
             WHERE id_barang_satuan = ? AND id_barang = ?
             FOR UPDATE"
        );
        $stmt->bind_param('ss', $id_barang_satuan, $id_barang);
        $stmt->execute();
        $satuan = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$satuan) {
            throw new DomainException('Satuan tidak ditemukan atau bukan milik barang yang dipilih.');
        }

        $konversi       = $barang['konversi'];
        $konversi_multi = $satuan['konversi_multi'];

        if ((float) $konversi <= 0 || (float) $konversi_multi <= 0) {
            throw new DomainException('Nilai konversi satuan harus lebih dari 0.');
        }

        // Hitung dengan DECIMAL di MySQL untuk menghindari hitungan float PHP.
        $stmt = $Conn->prepare(
            "SELECT ROUND(
                CAST(? AS DECIMAL(15,2)) *
                CAST(? AS DECIMAL(10,0)) /
                CAST(? AS DECIMAL(10,2)), 2
             ) AS qty"
        );
        $stmt->bind_param('sss', $qty_batch, $konversi_multi, $konversi);
        $stmt->execute();
        $qty = (string) $stmt->get_result()->fetch_assoc()['qty'];
        $stmt->close();
    }

    if (!preg_match('/^\d{1,13}(?:\.\d{1,2})?$/D', $qty) ||
        str_replace(['0', '.'], '', $qty) === '') {
        throw new DomainException(
            'QTY hasil konversi terlalu kecil atau melebihi kapasitas DECIMAL(15,2).'
        );
    }

    // ---------------------------------------
    // VALIDASI NOMOR BATCH
    // ---------------------------------------
    $stmt = $Conn->prepare(
        "SELECT id_barang_bacth FROM barang_bacth WHERE no_batch = ? LIMIT 1"
    );
    $stmt->bind_param('s', $no_batch);
    $stmt->execute();
    $duplikat = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($duplikat) {
        throw new DomainException('Nomor batch yang Anda gunakan sudah ada.');
    }

    // ---------------------------------------
    // SIMPAN DATA BATCH
    // ---------------------------------------
    $stmt = $Conn->prepare(
        "INSERT INTO barang_bacth
            (id_barang, no_batch, expired_date, qty_batch, reminder_date, status)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        'ssssss',
        $id_barang,
        $no_batch,
        $expired_date,
        $qty,
        $reminder_date,
        $status
    );
    $stmt->execute();
    $stmt->close();

    // ---------------------------------------
    // SIMPAN LOG
    // ---------------------------------------
    $hasilLog = addLog(
        $Conn,
        $SessionIdAkses,
        date('Y-m-d H:i:s'),
        'Barang',
        'Tambah Barang Batch & Expired'
    );

    if ($hasilLog !== 'Success') {
        throw new RuntimeException('Gagal menyimpan log batch barang.');
    }

    $Conn->commit();
    $transaction = false;
} catch (Throwable $e) {
    if ($transaction) {
        $Conn->rollback();
    }

    if ($e instanceof DomainException) {
        responseJson('error', $e->getMessage(), 422);
    }

    if ($e instanceof mysqli_sql_exception && (int) $e->getCode() === 1062) {
        responseJson('error', 'Data duplikat. Nomor batch mungkin sudah digunakan.', 409);
    }

    error_log('ProsesTambahBarangExpired: ' . $e->getMessage());
    responseJson('error', 'Gagal menyimpan data batch dan log.', 500);
}

responseJson('success', 'Data batch dan expired berhasil disimpan.');
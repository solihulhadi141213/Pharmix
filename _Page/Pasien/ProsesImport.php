<?php
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

function pasienImportResponse($status, $message, $html = '')
{
    echo json_encode([
        'status' => $status,
        // Frontend memasukkan pesan ke HTML.
        'message' => htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        'html' => $html,
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function pasienImportDate($value)
{
    if ($value === '') {
        return null;
    }
    foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
        $date = DateTimeImmutable::createFromFormat('!' . $format, $value);
        if ($date && $date->format($format) === $value) {
            return $date->format('Y-m-d');
        }
    }
    throw new RuntimeException('Tanggal Lahir harus tanggal valid (YYYY-MM-DD atau DD/MM/YYYY).');
}

ob_start();
require __DIR__ . '/../../_Config/Connection.php';
require __DIR__ . '/../../_Config/GlobalFunction.php';
require __DIR__ . '/../../_Config/Session.php';
ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Jakarta');

if (empty($SessionIdAkses)) {
    pasienImportResponse('error', 'Sesi akses telah berakhir. Silakan login ulang.');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    pasienImportResponse('error', 'Metode request tidak valid.');
}

$file = $_FILES['file_pasien'] ?? null;
if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
    || !is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name'])) {
    pasienImportResponse('error', 'File Excel tidak valid atau gagal diupload.');
}
if (filesize($file['tmp_name']) > 5 * 1024 * 1024) {
    pasienImportResponse('error', 'Ukuran file maksimal 5 MB.');
}
$extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
if (!in_array($extension, ['xls', 'xlsx'], true)) {
    pasienImportResponse('error', 'Format file harus XLS atau XLSX.');
}

require __DIR__ . '/../../vendor/autoload.php';
$spreadsheet = null;
$transaction = false;
$row = 1;
try {
    $readerType = IOFactory::identify($file['tmp_name']);
    if (!in_array($readerType, ['Xls', 'Xlsx'], true)) {
        throw new RuntimeException('Isi file harus berupa workbook Excel XLS atau XLSX.');
    }
    $spreadsheet = IOFactory::createReader($readerType)->load($file['tmp_name']);
    $sheet = $spreadsheet->getActiveSheet();
    $headers = ['ID Pasien', 'IHS', 'NIK', 'Nama', 'Email', 'Kontak',
        'Alamat', 'Gender', 'Tempat Lahir', 'Tanggal Lahir'];
    $fields = ['id_pasien', 'id_ihs', 'nik', 'nama', 'email', 'kontak',
        'alamat', 'gender', 'tempat_lahir', 'tanggal_lahir'];
    $offset = strcasecmp(trim((string) $sheet->getCell('A1')->getValue()), 'No') === 0 ? 2 : 1;
    foreach ($headers as $index => $header) {
        $actual = trim((string) $sheet->getCell([$offset + $index, 1])->getValue());
        if ($index === 0 && in_array(strtolower($actual), ['no rm', 'no.rm'], true)) {
            $actual = 'ID Pasien';
        }
        if (strcasecmp($actual, $header) !== 0) {
            throw new RuntimeException('Header kolom ' . ($offset + $index) . ' harus ' . $header . '.');
        }
    }

    // Ambil nama pengguna dari sesi yang sudah divalidasi.
    require __DIR__ . '/../../_Config/FungsiAkses.php';
    $checks = [];
    foreach (['id_pasien', 'id_ihs', 'nik', 'email', 'kontak'] as $field) {
        // Nama kolom berasal dari daftar tetap, bukan input pengguna.
        $checks[$field] = $Conn->prepare("SELECT id_anggota FROM anggota WHERE `$field` = ? LIMIT 1");
        if (!$checks[$field]) {
            throw new Exception('Gagal menyiapkan pemeriksaan duplikat.');
        }
    }
    $insert = $Conn->prepare('INSERT INTO anggota
        (id_pasien, id_ihs, nik, nama, email, kontak, alamat, gender, tempat_lahir,
         tanggal_lahir, creat_at, creat_by_id, creat_by_name, update_at, update_by_id, update_by_name)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$insert || !$Conn->begin_transaction()) {
        throw new Exception('Gagal memulai import.');
    }
    $transaction = true;
    $count = 0;
    $html = '';
    $now = date('Y-m-d H:i:s');
    $limits = ['id_pasien' => 255, 'id_ihs' => 255, 'nik' => 50, 'nama' => 255,
        'email' => 255, 'kontak' => 20, 'tempat_lahir' => 255];
    $highestRow = $sheet->getHighestDataRow();
    for ($row = 2; $row <= $highestRow; $row++) {
        $data = [];
        foreach ($fields as $index => $field) {
            $cell = $sheet->getCell([$offset + $index, $row]);
            if ($cell->getDataType() === DataType::TYPE_FORMULA) {
                throw new RuntimeException($headers[$index] . ' tidak boleh berisi rumus Excel.');
            }
            $value = $cell->getValue();
            if ($field === 'tanggal_lahir' && is_numeric($value)) {
                if ((float) $value < 1 || (float) $value > 2958465) {
                    throw new RuntimeException('Tanggal Lahir Excel tidak valid.');
                }
                $value = Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } elseif ($field !== 'tanggal_lahir' && is_numeric($value)
                && $cell->getDataType() === DataType::TYPE_NUMERIC) {
                $value = $cell->getFormattedValue();
            }
            $data[$field] = trim((string) ($value ?? ''));
        }
        if (implode('', $data) === '') {
            continue;
        }
        foreach (['id_pasien' => 'ID Pasien', 'nama' => 'Nama', 'gender' => 'Gender'] as $field => $label) {
            if ($data[$field] === '') {
                throw new RuntimeException($label . ' wajib diisi.');
            }
        }
        foreach ($limits as $field => $limit) {
            if (mb_strlen($data[$field], 'UTF-8') > $limit) {
                throw new RuntimeException($headers[array_search($field, $fields, true)] . ' maksimal ' . $limit . ' karakter.');
            }
        }
        if (strlen($data['alamat']) > 65535) {
            throw new RuntimeException('Alamat terlalu panjang.');
        }
        $gender = strtolower($data['gender']);
        if (!in_array($gender, ['male', 'female'], true)) {
            throw new RuntimeException('Gender harus Male atau Female.');
        }
        $data['gender'] = ucfirst($gender);
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Format Email tidak valid.');
        }
        $data['tanggal_lahir'] = pasienImportDate($data['tanggal_lahir']);

        // Insert sebelumnya dalam transaksi juga terlihat di sini: duplikat antarbaris ikut ditolak.
        foreach ($checks as $field => $check) {
            if ($data[$field] === '') {
                continue;
            }
            $check->bind_param('s', $data[$field]);
            if (!$check->execute()) {
                throw new Exception('Pemeriksaan duplikat gagal.');
            }
            $result = $check->get_result();
            $duplicate = $result->num_rows > 0;
            $result->free();
            if ($duplicate) {
                throw new RuntimeException($headers[array_search($field, $fields, true)] . ' sudah terdaftar atau duplikat dengan baris sebelumnya.');
            }
        }
        $insert->bind_param('sssssssssssissis',
            $data['id_pasien'], $data['id_ihs'], $data['nik'], $data['nama'],
            $data['email'], $data['kontak'], $data['alamat'], $data['gender'],
            $data['tempat_lahir'], $data['tanggal_lahir'], $now, $SessionIdAkses,
            $SessionNama, $now, $SessionIdAkses, $SessionNama);
        if (!$insert->execute()) {
            throw new Exception('Penyimpanan pasien gagal.');
        }
        $count++;
        $html .= '<tr>';
        foreach (['id_pasien', 'id_ihs', 'nik', 'nama', 'kontak', 'gender'] as $field) {
            $html .= '<td>' . htmlspecialchars($data[$field], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</td>';
        }
        $html .= '</tr>';
    }
    if ($count === 0) {
        throw new RuntimeException('File tidak memiliki data pasien.');
    }
    if (!$Conn->commit()) {
        throw new Exception('Gagal menyelesaikan import.');
    }
    $transaction = false;
    $spreadsheet->disconnectWorksheets();
    $html = '<tr><td colspan="6" class="text-success">Berhasil mengimport ' . $count . ' pasien.</td></tr>' . $html;
    pasienImportResponse('success', 'Berhasil mengimport ' . $count . ' pasien.', $html);
} catch (Throwable $exception) {
    if ($transaction) {
        $Conn->rollback();
    }
    if ($spreadsheet !== null) {
        $spreadsheet->disconnectWorksheets();
    }
    $message = $exception instanceof RuntimeException && !($exception instanceof mysqli_sql_exception)
        ? $exception->getMessage() : 'File tidak dapat diproses atau data gagal disimpan.';
    pasienImportResponse('error', 'Import dibatalkan (baris ' . $row . '). ' . $message . ' Tidak ada data yang disimpan.');
}

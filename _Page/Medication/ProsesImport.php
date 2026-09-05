<?php
    // Koneksi, Function, Session Dan Composer
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    require "../../vendor/autoload.php";

    use PhpOffice\PhpSpreadsheet\IOFactory;

    header('Content-Type: application/json; charset=utf-8');
    date_default_timezone_set('Asia/Jakarta');

    // Response
    function responseImport(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // Escape
    function e($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    // Normalisasi Value Excel
    function cellValue($value): string {
        return trim((string)($value ?? ''));
    }

    // Validasi Session
    if (empty($SessionIdAkses)) {
        responseImport('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    // Validasi Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseImport('error', 'Metode request tidak valid.');
    }

    // Validasi File
    if (
        empty($_FILES['file_medication_excel']) ||
        $_FILES['file_medication_excel']['error'] !== UPLOAD_ERR_OK
    ) {
        responseImport('error', 'File medication tidak valid atau gagal diupload.');
    }

    $file     = $_FILES['file_medication_excel'];
    $filename = $file['name'] ?? '';
    $tmpFile  = $file['tmp_name'] ?? '';
    $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
        responseImport('error', 'Format file harus XLSX, XLS, atau CSV.');
    }

    // Batasi Ukuran File 10 MB
    if (($file['size'] ?? 0) > (10 * 1024 * 1024)) {
        responseImport('error', 'Ukuran file maksimal 10 MB.');
    }

    // Baca Spreadsheet
    try {
        $spreadsheet = IOFactory::load($tmpFile);
        $sheet       = $spreadsheet->getActiveSheet();
        $highestRow  = $sheet->getHighestDataRow();
    } catch (Throwable $e) {
        responseImport('error', 'File tidak dapat dibaca: '.e($e->getMessage()));
    }

    if ($highestRow < 2) {
        responseImport('error', 'File tidak memiliki data medication.');
    }

    // Validasi Header Template
    $expectedHeader = [
        'A1' => 'No',
        'B1' => 'ID Index',
        'C1' => 'ID Medication SATUSEHAT',
        'D1' => 'Kode Lokal',
        'E1' => 'Nama Medication',
        'F1' => 'Kategori',
        'G1' => 'Kode KFA',
        'H1' => 'Display KFA',
        'I1' => 'Kode Sediaan',
        'J1' => 'Display Sediaan',
        'K1' => 'Kode Racikan',
        'L1' => 'Display Racikan',
        'M1' => 'ID Manufacturer',
        'N1' => 'Nama Manufacturer',
        'O1' => 'Ingredient'
    ];

    foreach ($expectedHeader as $cell => $expected) {
        $actual = cellValue($sheet->getCell($cell)->getValue());

        if ($actual !== $expected) {
            $spreadsheet->disconnectWorksheets();
            responseImport(
                'error',
                'Format template tidak sesuai. Kolom '.$cell.' seharusnya "'.$expected.'".'
            );
        }
    }

    // Prepared Statement Cek ID
    $stmtCheckId = $Conn->prepare("
        SELECT id_index_medication
        FROM medication
        WHERE id_index_medication = ?
        LIMIT 1
    ");

    // Cek medication_code agar UNIQUE tidak bentrok dengan ID lain
    $stmtCheckCode = $Conn->prepare("
        SELECT id_index_medication
        FROM medication
        WHERE medication_code = ?
        LIMIT 1
    ");

    // Insert Dengan ID Index Dari Template
    $stmtInsert = $Conn->prepare("
        INSERT INTO medication (
            id_index_medication,
            id_medication,
            medication_code,
            medication_name,
            medication_category,
            kfa_code,
            kfa_display,
            sediaan_code,
            sediaan_display,
            racikan_code,
            racikan_display,
            manufacturer_id,
            manufacturer_name,
            ingredient
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");

    // Update Berdasarkan ID Index
    $stmtUpdate = $Conn->prepare("
        UPDATE medication SET
            id_medication      = ?,
            medication_code    = ?,
            medication_name    = ?,
            medication_category= ?,
            kfa_code           = ?,
            kfa_display        = ?,
            sediaan_code       = ?,
            sediaan_display    = ?,
            racikan_code       = ?,
            racikan_display    = ?,
            manufacturer_id    = ?,
            manufacturer_name  = ?,
            ingredient         = ?
        WHERE id_index_medication = ?
    ");

    if (!$stmtCheckId || !$stmtCheckCode || !$stmtInsert || !$stmtUpdate) {
        responseImport('error', 'Gagal mempersiapkan query import medication.');
    }

    // Statistik
    $jumlahInsert = 0;
    $jumlahUpdate = 0;
    $jumlahGagal  = 0;
    $jumlahSkip   = 0;
    $logs         = [];

    // Proses Per Baris
    for ($row = 2; $row <= $highestRow; $row++) {

        $id_index_medication = (int)cellValue($sheet->getCell('B'.$row)->getValue());
        $id_medication       = cellValue($sheet->getCell('C'.$row)->getValue());
        $medication_code     = cellValue($sheet->getCell('D'.$row)->getValue());
        $medication_name     = cellValue($sheet->getCell('E'.$row)->getValue());
        $medication_category = cellValue($sheet->getCell('F'.$row)->getValue());
        $kfa_code            = cellValue($sheet->getCell('G'.$row)->getValue());
        $kfa_display         = cellValue($sheet->getCell('H'.$row)->getValue());
        $sediaan_code        = cellValue($sheet->getCell('I'.$row)->getValue());
        $sediaan_display     = cellValue($sheet->getCell('J'.$row)->getValue());
        $racikan_code        = cellValue($sheet->getCell('K'.$row)->getValue());
        $racikan_display     = cellValue($sheet->getCell('L'.$row)->getValue());
        $manufacturer_id     = cellValue($sheet->getCell('M'.$row)->getValue());
        $manufacturer_name   = cellValue($sheet->getCell('N'.$row)->getValue());
        $ingredient          = cellValue($sheet->getCell('O'.$row)->getValue());

        // Baris Kosong
        if (
            $id_index_medication === 0 &&
            $medication_code === '' &&
            $medication_name === ''
        ) {
            $jumlahSkip++;
            continue;
        }

        $labelId = $id_index_medication > 0
            ? (string)$id_index_medication
            : 'Baris '.$row;

        // Mandatory Database
        $errors = [];

        if ($id_index_medication < 1) {
            $errors[] = 'ID Index wajib diisi dan harus berupa angka lebih dari 0.';
        }

        if ($medication_code === '') {
            $errors[] = 'Kode Lokal wajib diisi.';
        }

        if ($medication_name === '') {
            $errors[] = 'Nama Medication wajib diisi.';
        }

        if ($medication_category === '') {
            $errors[] = 'Kategori wajib diisi.';
        } elseif (!in_array($medication_category, ['Obat', 'Alkes', 'Lainnya'], true)) {
            $errors[] = 'Kategori hanya boleh Obat, Alkes, atau Lainnya.';
        }

        // Racikan
        if (
            $racikan_code !== '' &&
            !in_array($racikan_code, ['NC', 'SD', 'EP'], true)
        ) {
            $errors[] = 'Kode racikan tidak valid.';
        }

        // JSON Ingredient
        if ($ingredient !== '') {

            $ingredientDecode = json_decode($ingredient, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = 'Format JSON ingredient tidak valid: '.json_last_error_msg();
            } elseif (!is_array($ingredientDecode)) {
                $errors[] = 'Ingredient harus berupa JSON array.';
            } else {
                // Normalisasi kembali JSON
                $ingredient = json_encode(
                    $ingredientDecode,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            }

        } else {
            $ingredient = null;
        }

        // Racikan NC Tidak Perlu Ingredient
        if ($racikan_code === 'NC') {
            $ingredient = null;
        }

        // Racikan SD / EP Wajib Ada Ingredient
        if (
            in_array($racikan_code, ['SD', 'EP'], true) &&
            empty($ingredient)
        ) {
            $errors[] = 'Medication racikan wajib memiliki ingredient.';
        }

        // Jika Mandatory / JSON Gagal
        if (!empty($errors)) {

            $jumlahGagal++;

            $logs[] = [
                'status'  => 'error',
                'id'      => $labelId,
                'action'  => 'Gagal',
                'message' => implode(' ', $errors)
            ];

            continue;
        }

        // Cek Duplicate medication_code
        $stmtCheckCode->bind_param("s", $medication_code);

        if (!$stmtCheckCode->execute()) {

            $jumlahGagal++;

            $logs[] = [
                'status'  => 'error',
                'id'      => $labelId,
                'action'  => 'Gagal',
                'message' => 'Gagal memeriksa kode medication.'
            ];

            continue;
        }

        $resultCode  = $stmtCheckCode->get_result();
        $existingCode = $resultCode->fetch_assoc();

        if (
            $existingCode &&
            (int)$existingCode['id_index_medication'] !== $id_index_medication
        ) {

            $jumlahGagal++;

            $logs[] = [
                'status'  => 'error',
                'id'      => $labelId,
                'action'  => 'Gagal',
                'message' => 'Kode lokal "'.$medication_code.'" sudah digunakan oleh ID Index '.$existingCode['id_index_medication'].'.'
            ];

            continue;
        }

        // Cek ID Index
        $stmtCheckId->bind_param("i", $id_index_medication);
        $stmtCheckId->execute();

        $resultId = $stmtCheckId->get_result();
        $exists   = $resultId->fetch_assoc();

        // UPDATE
        if ($exists) {

            $stmtUpdate->bind_param(
                "sssssssssssssi",
                $id_medication,
                $medication_code,
                $medication_name,
                $medication_category,
                $kfa_code,
                $kfa_display,
                $sediaan_code,
                $sediaan_display,
                $racikan_code,
                $racikan_display,
                $manufacturer_id,
                $manufacturer_name,
                $ingredient,
                $id_index_medication
            );

            if ($stmtUpdate->execute()) {

                $jumlahUpdate++;

                $logs[] = [
                    'status'  => 'success',
                    'id'      => $labelId,
                    'action'  => 'Updated',
                    'message' => $medication_name
                ];

            } else {

                $jumlahGagal++;

                $logs[] = [
                    'status'  => 'error',
                    'id'      => $labelId,
                    'action'  => 'Update gagal',
                    'message' => $stmtUpdate->error
                ];
            }

            continue;
        }

        // INSERT
        $stmtInsert->bind_param(
            "isssssssssssss",
            $id_index_medication,
            $id_medication,
            $medication_code,
            $medication_name,
            $medication_category,
            $kfa_code,
            $kfa_display,
            $sediaan_code,
            $sediaan_display,
            $racikan_code,
            $racikan_display,
            $manufacturer_id,
            $manufacturer_name,
            $ingredient
        );

        if ($stmtInsert->execute()) {

            $jumlahInsert++;

            $logs[] = [
                'status'  => 'success',
                'id'      => $labelId,
                'action'  => 'Insert',
                'message' => $medication_name
            ];

        } else {

            $jumlahGagal++;

            $logs[] = [
                'status'  => 'error',
                'id'      => $labelId,
                'action'  => 'Insert gagal',
                'message' => $stmtInsert->error
            ];
        }
    }

    // Close Statement
    $stmtCheckId->close();
    $stmtCheckCode->close();
    $stmtInsert->close();
    $stmtUpdate->close();

    $spreadsheet->disconnectWorksheets();

    // Susun Log
    $html = '
        <div class="mt-3">

            <div class="alert alert-light border">
                <div class="row">

                    <div class="col-6 mb-1">
                        <small>Insert</small>
                    </div>
                    <div class="col-6 text-end">
                        <small class="text-success">'.$jumlahInsert.'</small>
                    </div>

                    <div class="col-6 mb-1">
                        <small>Update</small>
                    </div>
                    <div class="col-6 text-end">
                        <small class="text-success">'.$jumlahUpdate.'</small>
                    </div>

                    <div class="col-6 mb-1">
                        <small>Gagal</small>
                    </div>
                    <div class="col-6 text-end">
                        <small class="text-danger">'.$jumlahGagal.'</small>
                    </div>

                    <div class="col-6">
                        <small>Skip</small>
                    </div>
                    <div class="col-6 text-end">
                        <small class="text-muted">'.$jumlahSkip.'</small>
                    </div>

                </div>
            </div>

            <div
                class="list-group"
                style="max-height:350px; overflow-y:auto;"
            >
    ';

    foreach ($logs as $log) {

        $isSuccess = $log['status'] === 'success';

        $class  = $isSuccess
            ? 'list-group-item-success'
            : 'list-group-item-danger';

        $icon = $isSuccess
            ? 'bi-check-circle'
            : 'bi-x-circle';

        $html .= '
            <div class="list-group-item '.$class.'">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small>
                            <i class="bi '.$icon.'"></i>
                            <b>ID '.$log['id'].'</b>
                            → '.$log['action'].'
                        </small>

                        <div>
                            <small>'.$log['message'].'</small>
                        </div>
                    </div>
                </div>
            </div>
        ';
    }

    $html .= '
            </div>
        </div>
    ';

    // Response
    $jumlahBerhasil = $jumlahInsert + $jumlahUpdate;

    responseImport(
        'success',
        'Import selesai. '.$jumlahBerhasil.' berhasil dan '.$jumlahGagal.' gagal.',
        $html
    );
?>
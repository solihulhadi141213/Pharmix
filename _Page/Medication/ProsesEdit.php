<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // RESPONSE
    date_default_timezone_set('Asia/Jakarta');
    header('Content-Type: application/json; charset=utf-8');

    function response(string $status, string $message): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function cleanNullable($value): ?string {
        $value = trim((string)($value ?? ''));
        return $value !== '' ? $value : null;
    }

    function getOperationOutcomeMessage(array $data): string {
        if (($data['resourceType'] ?? '') !== 'OperationOutcome' || empty($data['issue'])) {
            return 'SATUSEHAT mengembalikan response error.';
        }

        $messages = [];

        foreach ($data['issue'] as $issue) {
            $message =
                $issue['details']['text'] ??
                $issue['diagnostics'] ??
                $issue['code'] ??
                '';

            if ($message !== '') {
                $messages[] = $message;
            }
        }

        return !empty($messages)
            ? implode(' | ', $messages)
            : 'SATUSEHAT mengembalikan OperationOutcome.';
    }

    // VALIDASI SESSION & METHOD
    if (empty($SessionIdAkses)) {
        response('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response('error', 'Metode request tidak valid.');
    }

    // TANGKAP PARAMETER
    $id_index_medication = (int)($_POST['id_index_medication'] ?? 0);
    $medication_category = trim((string)($_POST['medication_category'] ?? ''));
    $medication_name     = trim((string)($_POST['medication_name'] ?? ''));
    $kfa_code            = cleanNullable($_POST['kfa_code'] ?? '');
    $kfa_display         = cleanNullable($_POST['kfa_display'] ?? '');
    $sediaan_code        = cleanNullable($_POST['sediaan_code'] ?? '');
    $sediaan_display     = cleanNullable($_POST['sediaan_display'] ?? '');
    $manufacturer_id     = cleanNullable($_POST['manufacturer_id'] ?? '');
    $manufacturer_name   = cleanNullable($_POST['manufacturer_name'] ?? '');
    $racikan_code        = trim((string)($_POST['racikan_code'] ?? ''));

    // VALIDASI PARAMETER
    if ($id_index_medication < 1) {
        response('error', 'ID Index Medication tidak valid.');
    }

    if ($medication_name === '') {
        response('error', 'Nama medication tidak boleh kosong.');
    }

    if (!in_array($medication_category, ['Obat', 'Alkes', 'Lainnya'], true)) {
        response('error', 'Kategori medication tidak valid.');
    }

    // VALIDASI RACIKAN
    if ($medication_category === 'Obat') {
        if (!in_array($racikan_code, ['NC', 'SD', 'EP'], true)) {
            response('error', 'Jenis racikan medication tidak valid.');
        }
    } else {
        $racikan_code = null;
    }

    // RACIKAN DISPLAY
    $racikan_display = null;

    if ($racikan_code === 'NC') {
        $racikan_display = 'Non-compound';
    } elseif ($racikan_code === 'SD') {
        $racikan_display = 'Gives of such doses';
    } elseif ($racikan_code === 'EP') {
        $racikan_display = 'Divide into equal parts';
    }

    // AMBIL DATA MEDICATION LOKAL
    $Qry = $Conn->prepare("
        SELECT
            id_index_medication,
            id_medication,
            medication_code
        FROM medication
        WHERE id_index_medication = ?
        LIMIT 1
    ");

    if (!$Qry) {
        response('error', 'Gagal mempersiapkan pengecekan medication.');
    }

    $Qry->bind_param("i", $id_index_medication);

    if (!$Qry->execute()) {
        $error = $Qry->error;
        $Qry->close();

        response(
            'error',
            'Gagal memeriksa medication.<br>Keterangan : '.
            htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
        );
    }

    $Result = $Qry->get_result();
    $Exists = $Result->fetch_assoc();
    $Qry->close();

    if (!$Exists) {
        response('error', 'Data medication tidak ditemukan.');
    }

    $id_medication   = trim((string)($Exists['id_medication'] ?? ''));
    $medication_code = trim((string)($Exists['medication_code'] ?? ''));

    // AMBIL PAYLOAD INGREDIENT
    $payloadIngredient = $_POST['payload_ingridient']
        ?? $_POST['payload_ingridient_edit']
        ?? [];

    if (!is_array($payloadIngredient)) {
        $payloadIngredient = [];
    }

    $ingredientArray = [];

    // INGREDIENT HANYA UNTUK RACIKAN
    if (
        $medication_category === 'Obat' &&
        in_array($racikan_code, ['SD', 'EP'], true)
    ) {
        foreach ($payloadIngredient as $item) {
            $item = trim((string)$item);

            if ($item === '') {
                continue;
            }

            $decoded = json_decode($item, true);

            if (
                json_last_error() !== JSON_ERROR_NONE ||
                !is_array($decoded)
            ) {
                response('error', 'Format JSON ingredient tidak valid.');
            }

            $kode_kfa           = trim((string)($decoded['kode_kfa'] ?? ''));
            $nama_kfa           = trim((string)($decoded['nama_kfa'] ?? ''));
            $kode_numerator     = trim((string)($decoded['kode_numerator'] ?? ''));
            $nama_numerator     = trim((string)($decoded['nama_numerator'] ?? ''));
            $jumlah_numerator   = trim((string)($decoded['jumlah_numerator'] ?? ''));
            $kode_denominator   = trim((string)($decoded['kode_denominator'] ?? ''));
            $nama_denominator   = trim((string)($decoded['nama_denominator'] ?? ''));
            $jumlah_denominator = trim((string)($decoded['jumlah_denominator'] ?? ''));

            if ($kode_kfa === '') {
                response('error', 'Kode KFA pada ingredient tidak boleh kosong.');
            }

            if ($nama_kfa === '') {
                response('error', 'Nama KFA pada ingredient tidak boleh kosong.');
            }

            if (
                $jumlah_numerator === '' ||
                $kode_numerator === ''
            ) {
                response('error', 'Numerator ingredient belum lengkap.');
            }

            if (
                $jumlah_denominator === '' ||
                $kode_denominator === ''
            ) {
                response('error', 'Denominator ingredient belum lengkap.');
            }

            if (
                !is_numeric($jumlah_numerator) ||
                !is_numeric($jumlah_denominator)
            ) {
                response('error', 'Nilai numerator dan denominator harus berupa angka.');
            }

            $ingredientArray[] = [
                'kode_kfa'           => $kode_kfa,
                'nama_kfa'           => $nama_kfa,
                'kode_numerator'     => $kode_numerator,
                'nama_numerator'     => $nama_numerator,
                'jumlah_numerator'   => $jumlah_numerator,
                'kode_denominator'   => $kode_denominator,
                'nama_denominator'   => $nama_denominator,
                'jumlah_denominator' => $jumlah_denominator
            ];
        }

        if (empty($ingredientArray)) {
            response('error', 'Medication racikan wajib memiliki minimal satu ingredient.');
        }
    }

    // JSON INGREDIENT DATABASE
    $ingredient = null;

    if (!empty($ingredientArray)) {
        $ingredient = json_encode(
            $ingredientArray,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($ingredient === false) {
            response('error', 'Gagal membentuk JSON ingredient.');
        }
    }

    /*
     * ============================================================
     * JIKA SUDAH MEMILIKI ID MEDICATION SATUSEHAT
     * Update SATUSEHAT terlebih dahulu.
     * Database lokal baru di-update setelah SATUSEHAT berhasil.
     * ============================================================
     */
    if ($id_medication !== '') {

        // Non-racikan wajib mempunyai KFA
        if ($racikan_code === 'NC' || $medication_category !== 'Obat') {
            if (empty($kfa_code) || empty($kfa_display)) {
                response(
                    'error',
                    'Medication yang sudah terhubung SATUSEHAT dan bukan racikan wajib memiliki Kode KFA dan Nama KFA.'
                );
            }
        }

        // BUKA KONFIGURASI SATUSEHAT
        $QrySetting = $Conn->prepare("
            SELECT url_connection_satu_sehat
            FROM connection_satu_sehat
            WHERE status_connection_satu_sehat = 1
            LIMIT 1
        ");

        if (!$QrySetting) {
            response('error', 'Gagal membuka konfigurasi SATUSEHAT.');
        }

        if (!$QrySetting->execute()) {
            $QrySetting->close();
            response('error', 'Gagal membaca konfigurasi SATUSEHAT.');
        }

        $ResultSetting = $QrySetting->get_result();
        $Setting       = $ResultSetting->fetch_assoc();
        $QrySetting->close();

        if (!$Setting) {
            response('error', 'Konfigurasi SATUSEHAT aktif tidak ditemukan.');
        }

        $base_url = rtrim(
            trim((string)($Setting['url_connection_satu_sehat'] ?? '')),
            '/'
        );

        if ($base_url === '') {
            response('error', 'URL koneksi SATUSEHAT belum dikonfigurasi.');
        }

        // GENERATE TOKEN
        $tokenResult = generateTokenSatuSehat($Conn);

        if (($tokenResult['status'] ?? 'error') !== 'success') {
            response(
                'error',
                'Gagal membuat token SATUSEHAT.<br>'.
                htmlspecialchars(
                    (string)($tokenResult['message'] ?? ''),
                    ENT_QUOTES,
                    'UTF-8'
                )
            );
        }

        $token = trim((string)($tokenResult['token'] ?? ''));

        if ($token === '') {
            response('error', 'Token SATUSEHAT tidak tersedia.');
        }

        // GET RESOURCE MEDICATION TERKINI
        $urlMedication = $base_url.
            '/fhir-r4/v1/Medication/'.
            rawurlencode($id_medication);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $urlMedication,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer '.$token,
                'Accept: application/fhir+json'
            ],
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $responseGet = curl_exec($curl);

        if ($responseGet === false) {
            $curlError = curl_error($curl);
            curl_close($curl);

            response(
                'error',
                'Gagal mengambil resource Medication dari SATUSEHAT.<br>'.
                htmlspecialchars($curlError, ENT_QUOTES, 'UTF-8')
            );
        }

        $httpGet = (int)curl_getinfo(
            $curl,
            CURLINFO_HTTP_CODE
        );

        curl_close($curl);

        $resourceMedication = json_decode(
            $responseGet,
            true
        );

        if (
            json_last_error() !== JSON_ERROR_NONE ||
            !is_array($resourceMedication)
        ) {
            response(
                'error',
                'Response detail Medication dari SATUSEHAT bukan JSON yang valid.'
            );
        }

        if (
            $httpGet < 200 ||
            $httpGet >= 300 ||
            ($resourceMedication['resourceType'] ?? '') === 'OperationOutcome'
        ) {
            response(
                'error',
                'Gagal mengambil Medication dari SATUSEHAT.<br>'.
                htmlspecialchars(
                    getOperationOutcomeMessage($resourceMedication),
                    ENT_QUOTES,
                    'UTF-8'
                )
            );
        }

        if (($resourceMedication['resourceType'] ?? '') !== 'Medication') {
            response('error', 'Resource SATUSEHAT yang diterima bukan Medication.');
        }

        /*
         * MODIFIKASI RESOURCE
         *
         * identifier, amount, batch, dan elemen lain yang tidak diedit
         * dipertahankan dari resource SATUSEHAT yang sudah ada.
         */

        // Hapus metadata versi karena akan dibuat kembali oleh server
        unset($resourceMedication['meta']);

        $resourceMedication['resourceType'] = 'Medication';
        $resourceMedication['id']           = $id_medication;
        $resourceMedication['status']       = 'active';

        // MEDICATION CODE / KFA
        if (
            $racikan_code === 'NC' ||
            $medication_category !== 'Obat'
        ) {
            $resourceMedication['code'] = [
                'coding' => [
                    [
                        'system'  => 'http://sys-ids.kemkes.go.id/kfa',
                        'code'    => $kfa_code,
                        'display' => $kfa_display
                    ]
                ],
                'text' => $medication_name
            ];
        } else {
            // Medication.code dapat dikosongkan untuk racikan
            if (!empty($kfa_code) && !empty($kfa_display)) {
                $resourceMedication['code'] = [
                    'coding' => [
                        [
                            'system'  => 'http://sys-ids.kemkes.go.id/kfa',
                            'code'    => $kfa_code,
                            'display' => $kfa_display
                        ]
                    ],
                    'text' => $medication_name
                ];
            } else {
                unset($resourceMedication['code']);
            }
        }

        // BENTUK SEDIAAN
        if (!empty($sediaan_code) && !empty($sediaan_display)) {
            $resourceMedication['form'] = [
                'coding' => [
                    [
                        'system'  => 'http://terminology.kemkes.go.id/CodeSystem/medication-form',
                        'code'    => $sediaan_code,
                        'display' => $sediaan_display
                    ]
                ]
            ];
        } else {
            unset($resourceMedication['form']);
        }

        // MANUFACTURER
        if (!empty($manufacturer_id)) {
            $resourceMedication['manufacturer'] = [
                'reference' => 'Organization/'.$manufacturer_id
            ];

            if (!empty($manufacturer_name)) {
                $resourceMedication['manufacturer']['display'] =
                    $manufacturer_name;
            }
        } else {
            unset($resourceMedication['manufacturer']);
        }

        // PERTAHANKAN EXTENSION LAIN, HAPUS MEDICATION TYPE LAMA
        $extensions = [];

        if (
            !empty($resourceMedication['extension']) &&
            is_array($resourceMedication['extension'])
        ) {
            foreach ($resourceMedication['extension'] as $extension) {
                if (
                    ($extension['url'] ?? '') !==
                    'https://fhir.kemkes.go.id/r4/StructureDefinition/MedicationType'
                ) {
                    $extensions[] = $extension;
                }
            }
        }

        // TAMBAH MEDICATION TYPE BARU UNTUK OBAT
        if (
            $medication_category === 'Obat' &&
            !empty($racikan_code)
        ) {
            $extensions[] = [
                'url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/MedicationType',
                'valueCodeableConcept' => [
                    'coding' => [
                        [
                            'system'  => 'http://terminology.kemkes.go.id/CodeSystem/medication-type',
                            'code'    => $racikan_code,
                            'display' => $racikan_display
                        ]
                    ]
                ]
            ];
        }

        if (!empty($extensions)) {
            $resourceMedication['extension'] = $extensions;
        } else {
            unset($resourceMedication['extension']);
        }

        // INGREDIENT
        if (
            $medication_category === 'Obat' &&
            in_array($racikan_code, ['SD', 'EP'], true)
        ) {
            $ingredientFHIR = [];

            foreach ($ingredientArray as $item) {

                /*
                 * SD / d.t.d
                 * numerator   : UCUM
                 * denominator : Orderable Drug Form
                 *
                 * EP / non-d.t.d
                 * numerator   : Orderable Drug Form
                 * denominator : Orderable Drug Form
                 */

                if ($racikan_code === 'SD') {
                    $numeratorSystem =
                        'http://unitsofmeasure.org';

                    $denominatorSystem =
                        'http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm';
                } else {
                    $numeratorSystem =
                        'http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm';

                    $denominatorSystem =
                        'http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm';
                }

                $ingredientFHIR[] = [
                    'itemCodeableConcept' => [
                        'coding' => [
                            [
                                'system'  => 'http://sys-ids.kemkes.go.id/kfa',
                                'code'    => $item['kode_kfa'],
                                'display' => $item['nama_kfa']
                            ]
                        ]
                    ],
                    'isActive' => true,
                    'strength' => [
                        'numerator' => [
                            'value'  => (float)$item['jumlah_numerator'],
                            'system' => $numeratorSystem,
                            'code'   => $item['kode_numerator']
                        ],
                        'denominator' => [
                            'value'  => (float)$item['jumlah_denominator'],
                            'system' => $denominatorSystem,
                            'code'   => $item['kode_denominator']
                        ]
                    ]
                ];
            }

            $resourceMedication['ingredient'] =
                $ingredientFHIR;

        } else {
            // NC / Alkes / Lainnya tidak memakai ingredient racikan
            unset($resourceMedication['ingredient']);
        }

        // ENCODE RESOURCE
        $payloadSatusehat = json_encode(
            $resourceMedication,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );

        if ($payloadSatusehat === false) {
            response('error', 'Gagal membentuk payload Medication SATUSEHAT.');
        }

        // PUT MEDICATION KE SATUSEHAT
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $urlMedication,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => $payloadSatusehat,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer '.$token,
                'Content-Type: application/json',
                'Accept: application/fhir+json'
            ],
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $responsePut = curl_exec($curl);

        if ($responsePut === false) {
            $curlError = curl_error($curl);
            curl_close($curl);

            response(
                'error',
                'Gagal memperbarui Medication di SATUSEHAT.<br>'.
                htmlspecialchars($curlError, ENT_QUOTES, 'UTF-8')
            );
        }

        $httpPut = (int)curl_getinfo(
            $curl,
            CURLINFO_HTTP_CODE
        );

        curl_close($curl);

        $responsePutData = json_decode(
            $responsePut,
            true
        );

        if (
            json_last_error() !== JSON_ERROR_NONE ||
            !is_array($responsePutData)
        ) {
            response(
                'error',
                'SATUSEHAT mengembalikan response yang bukan JSON.<br>HTTP Code : '.
                $httpPut
            );
        }

        if (
            $httpPut < 200 ||
            $httpPut >= 300 ||
            ($responsePutData['resourceType'] ?? '') === 'OperationOutcome'
        ) {
            response(
                'error',
                'Update Medication SATUSEHAT gagal.<br>'.
                htmlspecialchars(
                    getOperationOutcomeMessage($responsePutData),
                    ENT_QUOTES,
                    'UTF-8'
                )
            );
        }

        if (($responsePutData['resourceType'] ?? '') !== 'Medication') {
            response(
                'error',
                'SATUSEHAT tidak mengembalikan resource Medication setelah update.'
            );
        }
    }

    /*
     * UPDATE DATABASE LOKAL
     *
     * Dijalankan setelah:
     * - Tidak ada id_medication, atau
     * - PUT SATUSEHAT berhasil.
     */

    $sql = "
        UPDATE medication SET
            medication_category = ?,
            medication_name     = ?,
            kfa_code            = ?,
            kfa_display         = ?,
            sediaan_code        = ?,
            sediaan_display     = ?,
            racikan_code        = ?,
            racikan_display     = ?,
            manufacturer_id     = ?,
            manufacturer_name   = ?,
            ingredient          = ?
        WHERE id_index_medication = ?
    ";

    $Qry = $Conn->prepare($sql);

    if (!$Qry) {
        response(
            'error',
            $id_medication !== ''
                ? 'Resource SATUSEHAT berhasil diperbarui, tetapi gagal mempersiapkan update database lokal.'
                : 'Gagal mempersiapkan proses update medication.'
        );
    }

    $Qry->bind_param(
        "sssssssssssi",
        $medication_category,
        $medication_name,
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

    if (!$Qry->execute()) {
        $error = $Qry->error;
        $Qry->close();

        $message = $id_medication !== ''
            ? 'Resource SATUSEHAT berhasil diperbarui, tetapi database lokal gagal diperbarui.'
            : 'Gagal memperbarui medication.';

        response(
            'error',
            $message.'<br>Keterangan : '.
            htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
        );
    }

    $Qry->close();

    // RESPONSE AKHIR
    if ($id_medication !== '') {
        response(
            'success',
            'Data medication lokal dan resource Medication SATUSEHAT berhasil diperbarui.'
        );
    }

    response(
        'success',
        'Data medication lokal berhasil diperbarui.'
    );
?>
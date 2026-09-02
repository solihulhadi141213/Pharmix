<?php

    // ============================================================
    // HEADER
    // ============================================================
    header('Content-Type: application/json; charset=utf-8');

    // ============================================================
    // INCLUDE
    // ============================================================
    include __DIR__ . "/../../_Config/Connection.php";
    include __DIR__ . "/../../_Config/GlobalFunction.php";
    include __DIR__ . "/../../_Config/Session.php";
    include __DIR__ . "/../../_Config/FungsiAkses.php";

    date_default_timezone_set('Asia/Jakarta');


    // ============================================================
    // FUNCTION RESPONSE
    // ============================================================
    function responsePoliklinikEdit(
        string $status,
        string $message,
        array $metadata = []
    ): void {

        echo json_encode([
            'status'   => $status,
            'message'  => $message,
            'metadata' => $metadata
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // ============================================================
    // FUNCTION AMBIL PESAN ERROR FHIR
    // ============================================================
    function getFhirErrorMessageEdit(array $data): string
    {
        if (
            ($data['resourceType'] ?? '') === 'OperationOutcome' &&
            !empty($data['issue']) &&
            is_array($data['issue'])
        ) {

            $messages = [];

            foreach ($data['issue'] as $issue) {

                $message =
                    $issue['details']['text']
                    ?? $issue['diagnostics']
                    ?? $issue['code']
                    ?? '';

                if ($message !== '') {
                    $messages[] = $message;
                }
            }

            if (!empty($messages)) {
                return implode('<br>', $messages);
            }
        }

        return 'SATUSEHAT mengembalikan response yang tidak diketahui.';
    }


    // ============================================================
    // VALIDASI SESSION
    // ============================================================
    if (empty($SessionIdAkses)) {
        responsePoliklinikEdit(
            'error',
            'Sesi akses telah berakhir. Silakan login ulang.'
        );
    }


    // ============================================================
    // VALIDASI METHOD
    // ============================================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responsePoliklinikEdit(
            'error',
            'Metode request tidak valid.'
        );
    }


    // ============================================================
    // TANGKAP INPUT
    // ============================================================
    $polyclinicId = trim(
        (string) ($_POST['polyclinicId'] ?? '')
    );

    $polyclinicCode = trim(
        (string) ($_POST['polyclinicCode'] ?? '')
    );

    $polyclinicName = trim(
        (string) ($_POST['polyclinicName'] ?? '')
    );

    $polyclinicStatus = trim(
        (string) ($_POST['polyclinicStatus'] ?? '')
    );

    $satuSehatCode = trim(
        (string) ($_POST['satuSehatCode'] ?? '')
    );


    // ============================================================
    // CHECKBOX UPDATE / INSERT LOCATION
    // ============================================================
    $updateInsertLocation = (
        isset($_POST['update_insert_location_satusehat']) &&
        (string) $_POST['update_insert_location_satusehat'] === '1'
    );


    // ============================================================
    // VALIDASI INPUT
    // ============================================================
    if ($polyclinicId === '') {
        responsePoliklinikEdit(
            'error',
            'ID poliklinik tidak boleh kosong.'
        );
    }

    if (!ctype_digit($polyclinicId)) {
        responsePoliklinikEdit(
            'error',
            'ID poliklinik tidak valid.'
        );
    }

    $polyclinicId = (int) $polyclinicId;


    if ($polyclinicCode === '') {
        responsePoliklinikEdit(
            'error',
            'Kode poliklinik tidak boleh kosong.'
        );
    }

    if ($polyclinicName === '') {
        responsePoliklinikEdit(
            'error',
            'Nama poliklinik tidak boleh kosong.'
        );
    }

    if ($polyclinicStatus === '') {
        responsePoliklinikEdit(
            'error',
            'Status poliklinik tidak boleh kosong.'
        );
    }

    if (
        !in_array(
            $polyclinicStatus,
            ['Active', 'Inactive'],
            true
        )
    ) {
        responsePoliklinikEdit(
            'error',
            'Status poliklinik tidak valid.'
        );
    }


    // ============================================================
    // VALIDASI FORMAT KODE POLIKLINIK
    // ============================================================
    if (
        !preg_match(
            '/^PLY-[0-9]{8}$/',
            $polyclinicCode
        )
    ) {
        responsePoliklinikEdit(
            'error',
            'Format kode poliklinik tidak valid. Contoh: PLY-12345678'
        );
    }


    // ============================================================
    // BUKA DATA POLIKLINIK LAMA
    // ============================================================
    $stmtOld = $Conn->prepare(
        "
        SELECT
            polyclinicId,
            satuSehatCode,
            polyclinicCode,
            polyclinicName,
            polyclinicStatus
        FROM polyclinic
        WHERE polyclinicId = ?
        LIMIT 1
        "
    );

    if ($stmtOld === false) {
        responsePoliklinikEdit(
            'error',
            'Gagal mempersiapkan data poliklinik.'
        );
    }

    $stmtOld->bind_param(
        'i',
        $polyclinicId
    );

    if (!$stmtOld->execute()) {

        $stmtOld->close();

        responsePoliklinikEdit(
            'error',
            'Gagal membuka data poliklinik.'
        );
    }

    $resultOld = $stmtOld->get_result();

    $dataOld = $resultOld
        ? $resultOld->fetch_assoc()
        : null;

    $stmtOld->close();


    if (!$dataOld) {
        responsePoliklinikEdit(
            'error',
            'Data poliklinik tidak ditemukan.'
        );
    }


    // ============================================================
    // SIMPAN DATA LAMA
    // ============================================================
    $oldSatuSehatCode = trim(
        (string) ($dataOld['satuSehatCode'] ?? '')
    );


    // ============================================================
    // CEK DUPLIKASI KODE
    // ============================================================
    $stmtDuplicate = $Conn->prepare(
        "
        SELECT polyclinicId
        FROM polyclinic
        WHERE polyclinicCode = ?
        AND polyclinicId != ?
        LIMIT 1
        "
    );

    if ($stmtDuplicate === false) {
        responsePoliklinikEdit(
            'error',
            'Gagal mempersiapkan validasi kode poliklinik.'
        );
    }

    $stmtDuplicate->bind_param(
        'si',
        $polyclinicCode,
        $polyclinicId
    );

    if (!$stmtDuplicate->execute()) {

        $stmtDuplicate->close();

        responsePoliklinikEdit(
            'error',
            'Gagal memvalidasi kode poliklinik.'
        );
    }

    $duplicateResult =
        $stmtDuplicate->get_result();

    $isDuplicate = (
        $duplicateResult !== false &&
        $duplicateResult->num_rows > 0
    );

    $stmtDuplicate->close();


    if ($isDuplicate) {
        responsePoliklinikEdit(
            'error',
            'Kode poliklinik sudah digunakan oleh poliklinik lain.'
        );
    }


    // ============================================================
    // VARIABLE INFORMASI PROSES SATUSEHAT
    // ============================================================
    $locationAction = 'none';
    $locationId     = $satuSehatCode;


    // ============================================================
    // UPDATE / INSERT LOCATION SATUSEHAT
    // ============================================================
    if ($updateInsertLocation) {

        // ========================================================
        // BUKA KONFIGURASI SATUSEHAT
        // ========================================================
        $stmtSetting = $Conn->prepare(
            "
            SELECT
                url_connection_satu_sehat,
                organization_id
            FROM connection_satu_sehat
            WHERE status_connection_satu_sehat = 1
            LIMIT 1
            "
        );

        if ($stmtSetting === false) {
            responsePoliklinikEdit(
                'error',
                'Gagal membuka konfigurasi SATUSEHAT.'
            );
        }

        if (!$stmtSetting->execute()) {

            $stmtSetting->close();

            responsePoliklinikEdit(
                'error',
                'Gagal membaca konfigurasi SATUSEHAT.'
            );
        }

        $settingResult =
            $stmtSetting->get_result();

        $setting = $settingResult
            ? $settingResult->fetch_assoc()
            : null;

        $stmtSetting->close();


        // ========================================================
        // VALIDASI SETTING
        // ========================================================
        if (!$setting) {
            responsePoliklinikEdit(
                'error',
                'Konfigurasi SATUSEHAT aktif tidak ditemukan.'
            );
        }


        $baseurlSatusehat = rtrim(
            trim(
                (string) (
                    $setting['url_connection_satu_sehat']
                    ?? ''
                )
            ),
            '/'
        );


        $organizationIhs = trim(
            (string) (
                $setting['organization_id']
                ?? ''
            )
        );


        if ($baseurlSatusehat === '') {
            responsePoliklinikEdit(
                'error',
                'URL koneksi SATUSEHAT tidak tersedia.'
            );
        }


        if ($organizationIhs === '') {
            responsePoliklinikEdit(
                'error',
                'ID Organization SATUSEHAT belum dikonfigurasi.'
            );
        }


        // ========================================================
        // GENERATE TOKEN
        // ========================================================
        $tokenResult =
            generateTokenSatuSehat($Conn);


        if (
            ($tokenResult['status'] ?? 'error')
            !== 'success'
        ) {

            $messageToken =
                $tokenResult['message']
                ?? 'Tidak diketahui';

            responsePoliklinikEdit(
                'error',
                'Gagal membuat token SATUSEHAT.<br>' .
                htmlspecialchars(
                    $messageToken,
                    ENT_QUOTES,
                    'UTF-8'
                )
            );
        }


        $token = trim(
            (string) (
                $tokenResult['token']
                ?? ''
            )
        );


        if ($token === '') {
            responsePoliklinikEdit(
                'error',
                'Token SATUSEHAT tidak tersedia.'
            );
        }


        // ========================================================
        // KONVERSI STATUS
        // ========================================================
        $locationStatus = (
            $polyclinicStatus === 'Active'
        )
            ? 'active'
            : 'inactive';


        // ========================================================
        // PAYLOAD DASAR LOCATION
        // ========================================================
        $payloadLocation = [

            'resourceType' => 'Location',

            'identifier' => [
                [
                    'use' => 'official',

                    'system' =>
                        'http://sys-ids.kemkes.go.id/location/' .
                        $organizationIhs,

                    'value' =>
                        $polyclinicCode
                ]
            ],

            'status' =>
                $locationStatus,

            'name' =>
                $polyclinicName,

            'physicalType' => [
                'coding' => [
                    [
                        'system' =>
                            'http://terminology.hl7.org/CodeSystem/location-physical-type',

                        'code' =>
                            'ro',

                        'display' =>
                            'Room'
                    ]
                ]
            ],

            'managingOrganization' => [
                'reference' =>
                    'Organization/' .
                    $organizationIhs
            ]
        ];


        // ========================================================
        // TENTUKAN INSERT ATAU UPDATE
        // ========================================================

        /*
         * Jika satuSehatCode ADA
         * berarti resource Location sudah ada.
         *
         * Gunakan PUT:
         *
         * /Location/{id}
         */
        if ($satuSehatCode !== '') {

            // ----------------------------------------------------
            // UPDATE
            // ----------------------------------------------------
            $locationAction = 'update';


            /*
             * Pada PUT resource.id sebaiknya ikut dikirim
             * dan nilainya harus sama dengan ID pada URL.
             */
            $payloadLocation['id'] =
                $satuSehatCode;


            $urlLocation =
                $baseurlSatusehat .
                '/fhir-r4/v1/Location/' .
                rawurlencode($satuSehatCode);


            $httpMethod = 'PUT';

        } else {

            // ----------------------------------------------------
            // INSERT
            // ----------------------------------------------------
            $locationAction = 'insert';


            $urlLocation =
                $baseurlSatusehat .
                '/fhir-r4/v1/Location';


            $httpMethod = 'POST';
        }


        // ========================================================
        // JSON ENCODE
        // ========================================================
        $payloadJson = json_encode(
            $payloadLocation,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );


        if ($payloadJson === false) {
            responsePoliklinikEdit(
                'error',
                'Gagal membentuk payload Location SATUSEHAT.'
            );
        }


        // ========================================================
        // CURL
        // ========================================================
        $curl = curl_init();


        curl_setopt_array(
            $curl,
            [

                CURLOPT_URL =>
                    $urlLocation,

                CURLOPT_RETURNTRANSFER =>
                    true,

                CURLOPT_HEADER =>
                    false,

                CURLOPT_ENCODING =>
                    '',

                CURLOPT_MAXREDIRS =>
                    10,

                CURLOPT_TIMEOUT =>
                    30,

                CURLOPT_CONNECTTIMEOUT =>
                    10,

                CURLOPT_FOLLOWLOCATION =>
                    true,

                CURLOPT_CUSTOMREQUEST =>
                    $httpMethod,

                CURLOPT_POSTFIELDS =>
                    $payloadJson,

                CURLOPT_HTTPHEADER => [

                    'Authorization: Bearer ' .
                    $token,

                    'Content-Type: application/json',

                    'Accept: application/fhir+json'
                ],

                CURLOPT_SSL_VERIFYPEER =>
                    true
            ]
        );


        // ========================================================
        // EXECUTE
        // ========================================================
        $satusehatResponse =
            curl_exec($curl);


        // ========================================================
        // CURL ERROR
        // ========================================================
        if ($satusehatResponse === false) {

            $curlError =
                curl_error($curl);

            curl_close($curl);

            responsePoliklinikEdit(
                'error',
                'Gagal terhubung dengan SATUSEHAT.<br>' .
                htmlspecialchars(
                    $curlError,
                    ENT_QUOTES,
                    'UTF-8'
                )
            );
        }


        // ========================================================
        // HTTP CODE
        // ========================================================
        $httpCode = (int)
            curl_getinfo(
                $curl,
                CURLINFO_HTTP_CODE
            );

        curl_close($curl);


        // ========================================================
        // DECODE RESPONSE
        // ========================================================
        $satusehatData =
            json_decode(
                $satusehatResponse,
                true
            );


        if (
            json_last_error() !== JSON_ERROR_NONE ||
            !is_array($satusehatData)
        ) {
            responsePoliklinikEdit(
                'error',
                'Response SATUSEHAT bukan JSON yang valid.'
            );
        }


        // ========================================================
        // OPERATION OUTCOME
        // ========================================================
        if (
            ($satusehatData['resourceType'] ?? '')
            === 'OperationOutcome'
        ) {

            $actionText = (
                $locationAction === 'update'
            )
                ? 'memperbarui'
                : 'membuat';


            responsePoliklinikEdit(
                'error',
                'Gagal ' .
                $actionText .
                ' Location SATUSEHAT.<br>' .
                getFhirErrorMessageEdit(
                    $satusehatData
                )
            );
        }


        // ========================================================
        // CEK HTTP CODE
        // ========================================================
        if (
            $httpCode < 200 ||
            $httpCode >= 300
        ) {

            responsePoliklinikEdit(
                'error',
                'Proses Location SATUSEHAT gagal.' .
                '<br>HTTP Code : ' .
                $httpCode
            );
        }


        // ========================================================
        // VALIDASI RESOURCE TYPE
        // ========================================================
        if (
            ($satusehatData['resourceType'] ?? '')
            !== 'Location'
        ) {

            responsePoliklinikEdit(
                'error',
                'Response SATUSEHAT bukan resource Location.'
            );
        }


        // ========================================================
        // AMBIL ID LOCATION RESPONSE
        // ========================================================
        $locationId = trim(
            (string) (
                $satusehatData['id']
                ?? ''
            )
        );


        if ($locationId === '') {

            responsePoliklinikEdit(
                'error',
                'Proses Location berhasil tetapi ID Location tidak ditemukan pada response SATUSEHAT.'
            );
        }


        // ========================================================
        // PASTIKAN ID SESUAI SAAT UPDATE
        // ========================================================
        if (
            $locationAction === 'update' &&
            $locationId !== $satuSehatCode
        ) {

            responsePoliklinikEdit(
                'error',
                'ID Location response SATUSEHAT tidak sesuai dengan ID Location yang diperbarui.'
            );
        }


        // ========================================================
        // ISI SATUSEHAT CODE
        // ========================================================
        $satuSehatCode =
            $locationId;
    }


    // ============================================================
    // NULL JIKA ID LOCATION KOSONG
    // ============================================================
    $satuSehatValue = (
        $satuSehatCode === ''
    )
        ? null
        : $satuSehatCode;


    // ============================================================
    // DATA AUDIT
    // ============================================================
    $now =
        date('Y-m-d H:i:s');

    $accessId =
        (int) $SessionIdAkses;

    $accessName = trim(
        (string) (
            $SessionNama
            ?? ''
        )
    );


    // ============================================================
    // TRANSACTION DATABASE
    // ============================================================
    $Conn->begin_transaction();


    try {

        // ========================================================
        // UPDATE POLYCLINIC
        // ========================================================
        $stmtUpdate = $Conn->prepare(
            "
            UPDATE polyclinic
            SET
                satuSehatCode = ?,
                polyclinicCode = ?,
                polyclinicName = ?,
                polyclinicStatus = ?,
                update_at = ?,
                update_by_id = ?,
                update_by_name = ?
            WHERE polyclinicId = ?
            LIMIT 1
            "
        );


        if ($stmtUpdate === false) {
            throw new Exception(
                'Gagal mempersiapkan perubahan data poliklinik.'
            );
        }


        $stmtUpdate->bind_param(
            'sssssisi',
            $satuSehatValue,
            $polyclinicCode,
            $polyclinicName,
            $polyclinicStatus,
            $now,
            $accessId,
            $accessName,
            $polyclinicId
        );


        if (!$stmtUpdate->execute()) {

            $errorUpdate =
                $stmtUpdate->error;

            $stmtUpdate->close();

            throw new Exception(
                'Poliklinik gagal diperbarui. ' .
                $errorUpdate
            );
        }


        $stmtUpdate->close();


        // ========================================================
        // COMMIT DATABASE
        // ========================================================
        $Conn->commit();


        // ========================================================
        // MESSAGE
        // ========================================================
        $successMessage =
            'Data poliklinik berhasil diperbarui.';


        if (
            $updateInsertLocation &&
            $locationAction === 'update'
        ) {

            $successMessage =
                'Poliklinik dan Location SATUSEHAT berhasil diperbarui.';

        } elseif (
            $updateInsertLocation &&
            $locationAction === 'insert'
        ) {

            $successMessage =
                'Poliklinik berhasil diperbarui dan Location SATUSEHAT berhasil dibuat.';
        }


        // ========================================================
        // RESPONSE SUCCESS
        // ========================================================
        responsePoliklinikEdit(
            'success',
            $successMessage,
            [
                'polyclinicId' =>
                    $polyclinicId,

                'satuSehatCode' =>
                    $satuSehatCode,

                'locationAction' =>
                    $locationAction,

                'satusehatProcessed' =>
                    $updateInsertLocation
            ]
        );


    } catch (Throwable $e) {

        // ========================================================
        // ROLLBACK DATABASE
        // ========================================================
        $Conn->rollback();


        responsePoliklinikEdit(
            'error',
            $e->getMessage()
        );
    }

?>
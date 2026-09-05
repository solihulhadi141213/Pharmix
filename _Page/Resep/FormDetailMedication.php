<?php
    // INCLUDE
    include __DIR__ . "/../../_Config/Connection.php";
    include __DIR__ . "/../../_Config/GlobalFunction.php";
    include __DIR__ . "/../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');
    date_default_timezone_set('Asia/Jakarta');

    // RESPONSE
    function responseMedication(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // ESCAPE OUTPUT
    function escMedication($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    // TAMPILKAN VALUE
    function showMedicationValue($value): string {
        $value = trim((string)($value ?? ''));
        return $value !== '' ? escMedication($value) : '-';
    }

    // AMBIL PESAN ERROR FHIR
    function getFhirErrorMedication(array $data): string {
        if (($data['resourceType'] ?? '') === 'OperationOutcome' && !empty($data['issue']) && is_array($data['issue'])) {
            $messages = [];
            foreach ($data['issue'] as $issue) {
                $message = $issue['details']['text'] ?? $issue['diagnostics'] ?? $issue['code'] ?? '';
                if ($message !== '') {
                    $messages[] = $message;
                }
            }
            if (!empty($messages)) {
                return implode('<br>', array_map(fn($item) => escMedication($item), $messages));
            }
        }
        return 'SATUSEHAT mengembalikan response yang tidak diketahui.';
    }

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        responseMedication('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    // VALIDASI METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseMedication('error', 'Metode request tidak valid.');
    }

    // TANGKAP ID MEDICATION
    $idMedication = trim((string)($_POST['id_medication'] ?? ''));
    if ($idMedication === '') {
        responseMedication('error', 'ID Medication tidak boleh kosong.');
    }

    if (!preg_match('/^[A-Za-z0-9\-.]+$/', $idMedication)) {
        responseMedication('error', 'Format ID Medication tidak valid.');
    }

    // BUKA KONFIGURASI SATUSEHAT
    $stmtSetting = $Conn->prepare("
        SELECT url_connection_satu_sehat
        FROM connection_satu_sehat
        WHERE status_connection_satu_sehat = 1
        LIMIT 1
    ");

    if ($stmtSetting === false) {
        responseMedication('error', 'Gagal membuka konfigurasi SATUSEHAT.');
    }

    if (!$stmtSetting->execute()) {
        $stmtSetting->close();
        responseMedication('error', 'Gagal membaca konfigurasi SATUSEHAT.');
    }

    $settingResult = $stmtSetting->get_result();
    $setting = $settingResult ? $settingResult->fetch_assoc() : null;
    $stmtSetting->close();

    if (!$setting) {
        responseMedication('error', 'Konfigurasi SATUSEHAT aktif tidak ditemukan.');
    }

    $baseurlSatusehat = rtrim(trim((string)($setting['url_connection_satu_sehat'] ?? '')), '/');
    if ($baseurlSatusehat === '') {
        responseMedication('error', 'URL koneksi SATUSEHAT tidak tersedia.');
    }

    // GENERATE TOKEN SATUSEHAT
    $tokenResult = generateTokenSatuSehat($Conn);

    if (($tokenResult['status'] ?? 'error') !== 'success') {
        $messageToken = $tokenResult['message'] ?? 'Tidak diketahui';
        responseMedication(
            'error',
            'Gagal membuat token SATUSEHAT.<br>'.escMedication($messageToken)
        );
    }

    $token = trim((string)($tokenResult['token'] ?? ''));
    if ($token === '') {
        responseMedication('error', 'Token SATUSEHAT tidak tersedia.');
    }

    // URL RESOURCE MEDICATION
    $urlMedication = $baseurlSatusehat.'/fhir-r4/v1/Medication/'.rawurlencode($idMedication);

    // CURL GET MEDICATION
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => $urlMedication,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer '.$token,
            'Accept: application/fhir+json'
        ],
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $satusehatResponse = curl_exec($curl);

    if ($satusehatResponse === false) {
        $curlError = curl_error($curl);
        curl_close($curl);
        responseMedication(
            'error',
            'Gagal terhubung dengan SATUSEHAT.<br>'.escMedication($curlError)
        );
    }

    $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // DECODE RESPONSE
    $resource = json_decode($satusehatResponse, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($resource)) {
        responseMedication('error', 'Response SATUSEHAT bukan JSON yang valid.');
    }

    if (($resource['resourceType'] ?? '') === 'OperationOutcome') {
        responseMedication(
            'error',
            'Gagal mengambil Medication.<br>'.getFhirErrorMedication($resource)
        );
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        responseMedication('error', 'Gagal mengambil Medication.<br>HTTP Code : '.$httpCode);
    }

    if (($resource['resourceType'] ?? '') !== 'Medication') {
        responseMedication('error', 'Response SATUSEHAT bukan resource Medication.');
    }

    // INFORMASI RESOURCE
    $resourceType = $resource['resourceType'] ?? '-';
    $resourceId   = $resource['id'] ?? '-';
    $status       = $resource['status'] ?? '-';
    $versionId    = $resource['meta']['versionId'] ?? '-';
    $lastUpdated  = $resource['meta']['lastUpdated'] ?? '-';

    // IDENTIFIER
    $identifierHtml = '<small class="text-muted">Tidak ada identifier.</small>';

    if (!empty($resource['identifier']) && is_array($resource['identifier'])) {
        $identifierHtml = '';

        foreach ($resource['identifier'] as $identifier) {
            $identifierHtml .= '
                <div class="border rounded p-2 mb-2">
                    <div><small><b>Use :</b> '.showMedicationValue($identifier['use'] ?? '').'</small></div>
                    <div class="text-break"><small><b>System :</b> '.showMedicationValue($identifier['system'] ?? '').'</small></div>
                    <div class="text-break"><small><b>Value :</b> '.showMedicationValue($identifier['value'] ?? '').'</small></div>
                </div>
            ';
        }
    }

    // CODE / KFA
    $codeHtml = '<small class="text-muted">Tidak ada medication code.</small>';

    if (!empty($resource['code']['coding']) && is_array($resource['code']['coding'])) {
        $codeHtml = '';

        foreach ($resource['code']['coding'] as $coding) {
            $codeHtml .= '
                <div class="border rounded p-2 mb-2">
                    <div class="text-break"><small><b>System :</b> '.showMedicationValue($coding['system'] ?? '').'</small></div>
                    <div><small><b>Code :</b> '.showMedicationValue($coding['code'] ?? '').'</small></div>
                    <div><small><b>Display :</b> '.showMedicationValue($coding['display'] ?? '').'</small></div>
                </div>
            ';
        }
    }

    $codeText = $resource['code']['text'] ?? '';

    // FORM / SEDIAAN
    $formHtml = '<small class="text-muted">Tidak ada data bentuk sediaan.</small>';

    if (!empty($resource['form']['coding']) && is_array($resource['form']['coding'])) {
        $formHtml = '';

        foreach ($resource['form']['coding'] as $coding) {
            $formHtml .= '
                <div class="border rounded p-2 mb-2">
                    <div class="text-break"><small><b>System :</b> '.showMedicationValue($coding['system'] ?? '').'</small></div>
                    <div><small><b>Code :</b> '.showMedicationValue($coding['code'] ?? '').'</small></div>
                    <div><small><b>Display :</b> '.showMedicationValue($coding['display'] ?? '').'</small></div>
                </div>
            ';
        }
    }

    // MANUFACTURER
    $manufacturerReference = $resource['manufacturer']['reference'] ?? '';
    $manufacturerDisplay   = $resource['manufacturer']['display'] ?? '';

    // AMOUNT
    $amountHtml = '<small class="text-muted">Tidak ada data amount.</small>';

    if (!empty($resource['amount']) && is_array($resource['amount'])) {
        $numerator   = $resource['amount']['numerator'] ?? [];
        $denominator = $resource['amount']['denominator'] ?? [];

        $amountHtml = '
            <div class="border rounded p-2">
                <div>
                    <small>
                        <b>Numerator :</b>
                        '.showMedicationValue($numerator['value'] ?? '').'
                        '.showMedicationValue($numerator['unit'] ?? $numerator['code'] ?? '').'
                    </small>
                </div>
                <div>
                    <small>
                        <b>Denominator :</b>
                        '.showMedicationValue($denominator['value'] ?? '').'
                        '.showMedicationValue($denominator['unit'] ?? $denominator['code'] ?? '').'
                    </small>
                </div>
            </div>
        ';
    }

    // INGREDIENT
    $ingredientHtml = '
        <div class="alert alert-secondary mb-0">
            <small>Resource Medication tidak memiliki ingredient.</small>
        </div>
    ';

    if (!empty($resource['ingredient']) && is_array($resource['ingredient'])) {
        $ingredientHtml = '
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th class="text-center"><small>No</small></th>
                            <th><small>Ingredient</small></th>
                            <th><small>Strength</small></th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        $no = 1;

        foreach ($resource['ingredient'] as $ingredient) {
            $ingredientName = '-';

            if (!empty($ingredient['itemCodeableConcept']['coding'][0])) {
                $coding = $ingredient['itemCodeableConcept']['coding'][0];
                $code    = trim((string)($coding['code'] ?? ''));
                $display = trim((string)($coding['display'] ?? ''));

                $ingredientName = trim($code.' - '.$display, ' -');
                if ($ingredientName === '') {
                    $ingredientName = '-';
                }
            } elseif (!empty($ingredient['itemReference'])) {
                $reference = trim((string)($ingredient['itemReference']['reference'] ?? ''));
                $display   = trim((string)($ingredient['itemReference']['display'] ?? ''));

                $ingredientName = trim($reference.' - '.$display, ' -');
                if ($ingredientName === '') {
                    $ingredientName = '-';
                }
            }

            $strengthText = '-';

            if (!empty($ingredient['strength'])) {
                $numerator   = $ingredient['strength']['numerator'] ?? [];
                $denominator = $ingredient['strength']['denominator'] ?? [];

                $numValue = trim((string)($numerator['value'] ?? ''));
                $numUnit  = trim((string)($numerator['unit'] ?? $numerator['code'] ?? ''));
                $denValue = trim((string)($denominator['value'] ?? ''));
                $denUnit  = trim((string)($denominator['unit'] ?? $denominator['code'] ?? ''));

                $parts = [];

                if ($numValue !== '') {
                    $parts[] = trim($numValue.' '.$numUnit);
                }

                if ($denValue !== '') {
                    $strengthText = implode('', $parts);
                    $strengthText .= ($strengthText !== '' ? ' / ' : '').trim($denValue.' '.$denUnit);
                } else {
                    $strengthText = implode('', $parts);
                }

                if ($strengthText === '') {
                    $strengthText = '-';
                }
            }

            $ingredientHtml .= '
                <tr>
                    <td class="text-center"><small>'.$no.'</small></td>
                    <td><small>'.escMedication($ingredientName).'</small></td>
                    <td><small>'.escMedication($strengthText).'</small></td>
                </tr>
            ';

            $no++;
        }

        $ingredientHtml .= '
                    </tbody>
                </table>
            </div>
        ';
    }

    // BATCH
    $batchHtml = '<small class="text-muted">Tidak ada data batch.</small>';

    if (!empty($resource['batch']) && is_array($resource['batch'])) {
        $lotNumber      = $resource['batch']['lotNumber'] ?? '';
        $expirationDate = $resource['batch']['expirationDate'] ?? '';

        $batchHtml = '
            <div class="row mb-2">
                <div class="col-4"><small>Lot Number</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small>'.showMedicationValue($lotNumber).'</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-4"><small>Expiration Date</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small>'.showMedicationValue($expirationDate).'</small></div>
            </div>
        ';
    }

    // RAW JSON
    $rawJson = json_encode(
        $resource,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    // SUSUN HTML
    $html = '
        <div class="row mb-3">
            <div class="col-md-12"><h6 class="text-primary">A. Informasi Resource</h6></div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>Resource Type</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.showMedicationValue($resourceType).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>ID Medication</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7 text-break"><small>'.showMedicationValue($resourceId).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Status</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.showMedicationValue($status).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Version</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.showMedicationValue($versionId).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Last Updated</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.showMedicationValue($lastUpdated).'</small></div>
        </div>

        <div class="row mt-4 mb-2">
            <div class="col-md-12">
                <h6 class="text-primary">B. Identifier</h6>
                '.$identifierHtml.'
            </div>
        </div>

        <div class="row mt-4 mb-2">
            <div class="col-md-12">
                <h6 class="text-primary">C. Medication Code / KFA</h6>
                '.$codeHtml.'
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>Code Text</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.showMedicationValue($codeText).'</small></div>
        </div>

        <div class="row mt-4 mb-2">
            <div class="col-md-12">
                <h6 class="text-primary">D. Bentuk Sediaan</h6>
                '.$formHtml.'
            </div>
        </div>

        <div class="row mt-4 mb-2">
            <div class="col-md-12"><h6 class="text-primary">E. Manufacturer</h6></div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>Reference</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7 text-break"><small>'.showMedicationValue($manufacturerReference).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Display</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.showMedicationValue($manufacturerDisplay).'</small></div>
        </div>

        <div class="row mt-4 mb-2">
            <div class="col-md-12">
                <h6 class="text-primary">F. Amount</h6>
                '.$amountHtml.'
            </div>
        </div>

        <div class="row mt-4 mb-2">
            <div class="col-md-12">
                <h6 class="text-primary">G. Ingredient / Komposisi</h6>
                '.$ingredientHtml.'
            </div>
        </div>

        <div class="row mt-4 mb-2">
            <div class="col-md-12">
                <h6 class="text-primary">H. Batch</h6>
                '.$batchHtml.'
            </div>
        </div>

        <div class="row mt-4 mb-2">
            <div class="col-md-12">
                <h6 class="text-primary">I. Raw FHIR Response</h6>
                <details>
                    <summary class="text-primary" style="cursor:pointer;">
                        <small>Tampilkan JSON</small>
                    </summary>

                    <pre class="bg-light border rounded p-3 mt-2" style="max-height:400px; overflow:auto; white-space:pre-wrap; word-break:break-word; font-size:11px;">'.escMedication($rawJson).'</pre>
                </details>
            </div>
        </div>
    ';

    responseMedication('success', 'Data Medication berhasil ditemukan.', $html);
?>
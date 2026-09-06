<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');
    header('Content-Type: application/json; charset=utf-8');

    function responseCariNrn(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function escNrn($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    function getReferenceId($reference): string {
        $reference = trim((string)$reference);
        if ($reference === '') return '';
        $parts = explode('/', $reference);
        return trim((string)end($parts));
    }

    function formReadonly(string $label, $name, $value): string {
        $value = trim((string)($value ?? ''));

        return '
            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="'.$name.'"><small>'.$label.'</small></label>
                </div>
                <div class="col-md-8">
                    <input type="text" name="'.$name.'" id="'.$name.'" class="form-control form-control-sm" value="'.escNrn($value !== '' ? $value : '-').'"readonly>
                </div>
            </div>
        ';
    }

    // VALIDASI SESSION & METHOD
    if (empty($SessionIdAkses)) {
        responseCariNrn('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseCariNrn('error', 'Metode request tidak valid.');
    }

    // NRN
    $nrn = strtoupper(trim((string)($_POST['nomor_resep_nasional'] ?? '')));

    if ($nrn === '') {
        responseCariNrn(
            'error',
            'Silakan masukan Nomor Resep Nasional (NRN) pada kolom pencarian.'
        );
    }

    // KONFIGURASI SATUSEHAT
    $stmt = $Conn->prepare("SELECT url_connection_satu_sehat FROM connection_satu_sehat WHERE status_connection_satu_sehat = 1 LIMIT 1");
    if (!$stmt) {
        responseCariNrn('error', 'Gagal membuka konfigurasi SATUSEHAT.');
    }
    if (!$stmt->execute()) {
        $stmt->close();
        responseCariNrn('error', 'Gagal membaca konfigurasi SATUSEHAT.');
    }

    $config = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$config) {
        responseCariNrn('error', 'Konfigurasi SATUSEHAT aktif tidak ditemukan.');
    }
    $base_url = rtrim(trim((string)($config['url_connection_satu_sehat'] ?? '')),'/');

    if ($base_url === '') {
        responseCariNrn('error', 'URL SATUSEHAT belum dikonfigurasi.');
    }

    // TOKEN
    $tokenResult = generateTokenSatuSehat($Conn);
    if (empty($tokenResult) || ($tokenResult['status'] ?? '') !== 'success' || empty($tokenResult['token'])) {
        responseCariNrn(
            'error',
            'Gagal membuat token SATUSEHAT.<br>'.
            escNrn($tokenResult['message'] ?? '')
        );
    }
    $token = trim((string)$tokenResult['token']);

    // GET DOCUMENT REFERENCE BERDASARKAN NRN
    $identifier = 'http://sys-ids.kemkes.go.id/prescription/national|'.$nrn;

    $url = $base_url
        .'/fhir-r4/v1/DocumentReference/'
        .'?_include=DocumentReference%3Arelated'
        .'&identifier='.urlencode($identifier);

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL            => $url,
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

    $satusehatResponse = curl_exec($curl);
    if ($satusehatResponse === false) { 
        $curlError = curl_error($curl); 
        curl_close($curl);
        responseCariNrn(
            'error',
            'Gagal melakukan pencarian resep.<br>'.
            escNrn($curlError)
        );
    }

    $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // DECODE
    $bundle = json_decode($satusehatResponse, true);
    if (
        json_last_error() !== JSON_ERROR_NONE ||
        !is_array($bundle)
    ) {
        responseCariNrn(
            'error',
            'Response SATUSEHAT tidak valid.<br>HTTP Code : '.$httpCode
        );
    }

    // ERROR SATUSEHAT
    if (
        $httpCode < 200 ||
        $httpCode >= 300 ||
        ($bundle['resourceType'] ?? '') === 'OperationOutcome'
    ) {
        $errorMessage = '';

        if (!empty($bundle['issue'])) {
            foreach ($bundle['issue'] as $issue) {
                $msg = $issue['details']['text']
                    ?? $issue['diagnostics']
                    ?? $issue['code']
                    ?? '';

                if ($msg !== '') {
                    $errorMessage .= ($errorMessage !== '' ? ' | ' : '').$msg;
                }
            }
        }

        responseCariNrn(
            'error',
            $errorMessage !== ''
                ? escNrn($errorMessage)
                : 'Pencarian resep gagal. HTTP Code : '.$httpCode
        );
    }

    // DATA TIDAK DITEMUKAN
    if (
        ($bundle['resourceType'] ?? '') !== 'Bundle' ||
        (int)($bundle['total'] ?? 0) < 1 ||
        empty($bundle['entry'])
    ) {
        responseCariNrn(
            'error',
            'Data resep dengan NRN <b>'.escNrn($nrn).'</b> tidak ditemukan.'
        );
    }

    // PISAHKAN RESOURCE
    $documentReference  = null;
    $medicationRequests = [];
    $observations       = [];

    foreach ($bundle['entry'] as $entry) {
        $resource = $entry['resource'] ?? [];

        if (!is_array($resource)) continue;

        $resourceType = $resource['resourceType'] ?? '';

        if ($resourceType === 'DocumentReference' && $documentReference === null) {
            $documentReference = $resource;
        }

        if ($resourceType === 'MedicationRequest') {
            $medicationRequests[] = $resource;
        }

        if ($resourceType === 'Observation') {
            $observations[] = $resource;
        }
    }

    if (!$documentReference) {
        responseCariNrn(
            'error',
            'DocumentReference untuk NRN <b>'.escNrn($nrn).'</b> tidak ditemukan.'
        );
    }

    // VALIDASI NRN DARI MASTER IDENTIFIER
    $nrnResponse = trim(
        (string)($documentReference['masterIdentifier']['value'] ?? '')
    );

    $nrnSystem = trim(
        (string)($documentReference['masterIdentifier']['system'] ?? '')
    );

    if ($nrnResponse === '') {
        responseCariNrn(
            'error',
            'DocumentReference ditemukan, tetapi Nomor Resep Nasional tidak tersedia.'
        );
    }

    if (
        $nrnSystem !== '' &&
        $nrnSystem !== 'http://sys-ids.kemkes.go.id/prescription/national'
    ) {
        responseCariNrn(
            'error',
            'Master Identifier yang ditemukan bukan Nomor Resep Nasional.'
        );
    }

    // DOCUMENT REFERENCE
    $id_document_reference = trim(
        (string)($documentReference['id'] ?? '')
    );

    $document_status = trim(
        (string)($documentReference['status'] ?? '')
    );

    $document_date = trim(
        (string)($documentReference['date'] ?? '')
    );

    $description = trim(
        (string)($documentReference['description'] ?? '')
    );

    // PASIEN
    $patientReference = trim(
        (string)($documentReference['subject']['reference'] ?? '')
    );

    $patient_ihs = getReferenceId($patientReference);

    $patient_name = trim(
        (string)($documentReference['subject']['display'] ?? '')
    );

    // ENCOUNTER
    $encounterReference = trim(
        (string)($documentReference['context']['encounter'][0]['reference'] ?? '')
    );

    $id_encounter = getReferenceId($encounterReference);

    // DOKTER
    $doctorReference = trim(
        (string)($documentReference['author'][0]['reference'] ?? '')
    );

    $dokter_ihs = getReferenceId($doctorReference);

    $dokter_nama = trim(
        (string)($documentReference['author'][0]['display'] ?? '')
    );

    // FASYANKES ASAL
    $custodianReference = trim(
        (string)($documentReference['custodian']['reference'] ?? '')
    );

    $organization_id = getReferenceId($custodianReference);

    $organization_name = trim(
        (string)($documentReference['custodian']['display'] ?? '')
    );

    // IDENTIFIER RESEP LOKAL FASKES ASAL
    $prescription_identifier = '';
    $coverage_type           = '';
    $claim_number            = '';

    foreach (($documentReference['identifier'] ?? []) as $identifierItem) {
        $system = trim((string)($identifierItem['system'] ?? ''));
        $value  = trim((string)($identifierItem['value'] ?? ''));

        if (str_contains($system, '/prescription/')) {
            $prescription_identifier = $value;
        }

        if ($system === 'http://terminology.kemkes.go.id/CodeSystem/coverage-type') {
            $coverage_type = $value;
        }

        if (str_contains($system, '/claim-number/')) {
            $claim_number = $value;
        }
    }

    // CEK PASIEN LOKAL
    $patientExists = false;
    $id_anggota    = 0;
    $id_pasien     = '';

    if ($patient_ihs !== '') {
        $stmt = $Conn->prepare("
            SELECT id_anggota, id_pasien
            FROM anggota
            WHERE id_ihs = ?
            LIMIT 1
        ");

        if ($stmt) {
            $stmt->bind_param("s", $patient_ihs);
            $stmt->execute();

            $patientLocal = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($patientLocal) {
                $patientExists = true;
                $id_anggota = (int)$patientLocal['id_anggota'];
                $id_pasien  = trim((string)($patientLocal['id_pasien'] ?? ''));
            }
        }
    }

    // CEK KUNJUNGAN LOKAL
    $encounterExists = false;
    $id_kunjungan    = 0;

    if ($id_encounter !== '') {
        $stmt = $Conn->prepare("
            SELECT id_kunjungan
            FROM kunjungan
            WHERE id_encounter = ?
            LIMIT 1
        ");

        if ($stmt) {
            $stmt->bind_param("s", $id_encounter);
            $stmt->execute();

            $encounterLocal = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($encounterLocal) {
                $encounterExists = true;
                $id_kunjungan = (int)$encounterLocal['id_kunjungan'];
            }
        }
    }

    // CEK NRN SUDAH ADA
    $nrnExists = false;
    $idGroupExisting = 0;

    $stmt = $Conn->prepare("
        SELECT id_medication_request_group
        FROM medication_request_group
        WHERE no_resep_nasional = ?
        LIMIT 1
    ");

    if ($stmt) {
        $stmt->bind_param("s", $nrnResponse);
        $stmt->execute();

        $nrnLocal = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($nrnLocal) {
            $nrnExists = true;
            $idGroupExisting = (int)$nrnLocal['id_medication_request_group'];
        }
    }

    // CEK DOKTER LOKAL
    $dokterExists = false;
    $dokter_id    = 0;

    if ($dokter_ihs !== '') {
        $stmt = $Conn->prepare("
            SELECT medicalPersonelId
            FROM medical_personel
            WHERE id_practitioner = ?
            LIMIT 1
        ");

        if ($stmt) {
            $stmt->bind_param("s", $dokter_ihs);
            $stmt->execute();

            $doctorLocal = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($doctorLocal) {
                $dokterExists = true;
                $dokter_id = (int)$doctorLocal['medicalPersonelId'];
            }
        }
    }

    // BADGE STATUS
    $badgePatient = $patientExists
        ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Pasien Sudah Terdaftar</span>'
        : '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Pasien Belum Terdaftar</span>';

    $badgeEncounter = $encounterExists
        ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Kunjungan Sudah Ada</span>'
        : '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Kunjungan Belum Ada</span>';

    $badgeNrn = $nrnExists
        ? '<span class="badge bg-danger"><i class="bi bi-exclamation-circle"></i> NRN Sudah Ada</span>'
        : '<span class="badge bg-success"><i class="bi bi-check-circle"></i> NRN Belum Ada</span>';

    $badgeDokter = $dokterExists
        ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Dokter Ditemukan</span>'
        : '<span class="badge bg-secondary"><i class="bi bi-info-circle"></i> Dokter Belum Ada</span>';

    // FORMAT TANGGAL
    $tanggalResep = $document_date;

    if ($document_date !== '' && strtotime($document_date) !== false) {
        try {
            $dt = new DateTime($document_date);
            $dt->setTimezone(new DateTimeZone('Asia/Jakarta'));
            $tanggalResep = $dt->format('d-m-Y H:i');
        } catch (Throwable $e) {
            $tanggalResep = $document_date;
        }
    }

    // DAFTAR ITEM RESEP
    $itemHtml = '';

    foreach ($medicationRequests as $index => $mr) {
        $idMR     = trim((string)($mr['id'] ?? ''));
        $statusMR = trim((string)($mr['status'] ?? ''));
        $intentMR = trim((string)($mr['intent'] ?? ''));
        $priority = trim((string)($mr['priority'] ?? ''));

        $medicationName = trim(
            (string)($mr['medicationReference']['display'] ?? '')
        );

        // Jika display tidak tersedia, cari contained Medication
        if ($medicationName === '' && !empty($mr['contained'])) {
            foreach ($mr['contained'] as $contained) {
                if (($contained['resourceType'] ?? '') !== 'Medication') continue;

                $medicationName = trim(
                    (string)($contained['code']['coding'][0]['display'] ?? '')
                );

                if ($medicationName === '') {
                    $medicationName = trim(
                        (string)($contained['form']['coding'][0]['display'] ?? 'Medication')
                    );
                }

                break;
            }
        }

        if ($medicationName === '') {
            $medicationName = 'Medication';
        }

        $instruction = trim(
            (string)($mr['dosageInstruction'][0]['patientInstruction'] ?? '')
        );

        $itemHtml .= '
            <div class="row py-2 '.($index > 0 ? 'border-top' : '').'">
                <div class="col-md-1 text-center">
                    <small>'.($index + 1).'</small>
                </div>
                <div class="col-md-5">
                    <small>'.escNrn($medicationName).'</small><br>
                    <small class="text-muted">'.escNrn($idMR).'</small>
                </div>
                <div class="col-md-3">
                    <small>
                        '.escNrn($statusMR).' /
                        '.escNrn($intentMR).' /
                        '.escNrn($priority).'
                    </small>
                </div>
                <div class="col-md-3">
                    <small>'.escNrn($instruction !== '' ? $instruction : '-').'</small>
                </div>
            </div>
        ';
    }

    if ($itemHtml === '') {
        $itemHtml = '
            <div class="row">
                <div class="col-md-12 text-center py-3">
                    <small class="text-muted">Tidak ada MedicationRequest.</small>
                </div>
            </div>
        ';
    }

    // WARNING
    $warning = '';

    if ($nrnExists) {
        $warning .= '
            <div class="alert alert-danger">
                <small>
                    <i class="bi bi-exclamation-triangle"></i>
                    NRN ini sudah tersimpan pada database lokal
                    dengan ID resep <b>'.$idGroupExisting.'</b>.
                    Data tidak dapat diimport kembali.
                </small>
            </div>
        ';
    }

    if (!$patientExists) {
        $warning .= '
            <div class="alert alert-warning">
                <small>
                    Pasien dengan IHS <b>'.escNrn($patient_ihs).'</b>
                    belum ditemukan pada tabel anggota.
                    Saat proses import, pasien perlu dipetakan atau dibuat terlebih dahulu.
                </small>
            </div>
        ';
    }

    if (!$encounterExists) {
        $warning .= '
            <div class="alert alert-warning">
                <small>
                    Encounter <b>'.escNrn($id_encounter).'</b>
                    belum ditemukan pada tabel kunjungan.
                    Resep tetap dapat dipreview, tetapi kunjungan lokal belum dapat dihubungkan.
                </small>
            </div>
        ';
    }

    // HTML HASIL
    $html = '
        <!-- DATA YANG AKAN DIKIRIM KETIKA SIMPAN -->
        <input type="hidden" name="nomor_resep_nasional" value="'.escNrn($nrnResponse).'">
        <input type="hidden" name="id_document_reference" value="'.escNrn($id_document_reference).'">

        <div class="border border-secondary border-opacity-50 rounded-3 p-3">

            <div class="row mb-3">
                <div class="col-md-8">
                    <h6 class="mb-1">
                        <i class="bi bi-prescription2"></i>
                        Hasil Pencarian Resep
                    </h6>
                    <small class="text-muted">
                        Data diperoleh dari SATUSEHAT berdasarkan Nomor Resep Nasional.
                    </small>
                </div>
                <div class="col-md-4 text-md-end">
                    '.$badgeNrn.'
                </div>
            </div>

            '.$warning.'

            <hr>

            <div class="row mb-3">
                <div class="col-md-12">
                    <small class="text-muted">Nomor Resep Nasional</small>
                    <h4 class="mb-0">'.escNrn($nrnResponse).'</h4>
                </div>
            </div>

            '.formReadonly(
                'ID DocumentReference',
                'preview_id_document_reference',
                $id_document_reference
            ).'

            '.formReadonly(
                'Status Document',
                'document_status',
                $document_status
            ).'

            '.formReadonly(
                'Tanggal Resep',
                'document_date',
                $tanggalResep
            ).'

            '.formReadonly(
                'Identifier Resep Asal',
                'prescription_identifier',
                $prescription_identifier
            ).'

            '.formReadonly(
                'Jenis Pembiayaan',
                'coverage_type',
                $coverage_type
            ).'

            '.formReadonly(
                'Nomor Klaim / SEP',
                'claim_number',
                $claim_number
            ).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-4">
                    <small>Pasien</small>
                </div>
                <div class="col-md-8">
                    '.$badgePatient.'
                </div>
            </div>

            '.formReadonly(
                'Patient IHS',
                'patient_ihs',
                $patient_ihs
            ).'

            '.formReadonly(
                'Nama Pasien',
                'patient_name',
                $patient_name
            ).'

            '.formReadonly(
                'ID Anggota Lokal',
                'id_anggota',
                $patientExists ? $id_anggota : ''
            ).'

            '.formReadonly(
                'Nomor RM Lokal',
                'id_pasien',
                $id_pasien
            ).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-4">
                    <small>Kunjungan</small>
                </div>
                <div class="col-md-8">
                    '.$badgeEncounter.'
                </div>
            </div>

            '.formReadonly(
                'Encounter ID',
                'id_encounter',
                $id_encounter
            ).'

            '.formReadonly(
                'ID Kunjungan Lokal',
                'id_kunjungan',
                $encounterExists ? $id_kunjungan : ''
            ).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-4">
                    <small>Dokter</small>
                </div>
                <div class="col-md-8">
                    '.$badgeDokter.'
                </div>
            </div>

            '.formReadonly(
                'Practitioner IHS',
                'dokter_ihs',
                $dokter_ihs
            ).'

            '.formReadonly(
                'Nama Dokter',
                'dokter_nama',
                $dokter_nama
            ).'

            '.formReadonly(
                'ID Dokter Lokal',
                'dokter_id',
                $dokterExists ? $dokter_id : ''
            ).'

            <hr>

            '.formReadonly(
                'Organization Asal',
                'organization_id',
                $organization_id
            ).'

            '.formReadonly(
                'Fasyankes Asal',
                'sumber_resep',
                $organization_name
            ).'

            <div class="row mb-2">
                <div class="col-md-4">
                    <small>Description</small>
                </div>
                <div class="col-md-8">
                    <textarea
                        name="description"
                        class="form-control form-control-sm"
                        rows="2"
                        readonly
                    >'.escNrn($description).'</textarea>
                </div>
            </div>

            <hr>

            <div class="row mb-2">
                <div class="col-md-8">
                    <small><b>Item Medication Request</b></small>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-secondary">
                        '.count($medicationRequests).' Item
                    </span>
                </div>
            </div>

            <div class="row border-bottom pb-2">
                <div class="col-md-1"><small>No</small></div>
                <div class="col-md-5"><small>Medication</small></div>
                <div class="col-md-3"><small>Status</small></div>
                <div class="col-md-3"><small>Instruksi</small></div>
            </div>

            '.$itemHtml.'

            <div class="row mt-3">
                <div class="col-md-12">
                    <small class="text-muted">
                        Resource terkait: '.count($medicationRequests).' MedicationRequest
                        dan '.count($observations).' Observation.
                    </small>
                </div>
            </div>

            '.(!$nrnExists ? '
                <div class="row mt-4">
                    <div class="col-md-12 d-grid">
                        <button
                            type="submit"
                            class="btn btn-primary btn-lg"
                            id="TombolSimpanResepDariNrn"
                        >
                            <i class="bi bi-download"></i>
                            Simpan Resep Ke Database Lokal
                        </button>
                    </div>
                </div>
            ' : '').'

        </div>
    ';

    responseCariNrn(
        'success',
        'Resep berhasil ditemukan.',
        $html
    );
?>
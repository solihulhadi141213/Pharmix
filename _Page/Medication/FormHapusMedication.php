<?php
    // Koneksi, Function Dan Session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Format Response
    header('Content-Type: application/json; charset=utf-8');

    function responseHapusMedication(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function e($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    function showValue($value): string {
        $value = trim((string)($value ?? ''));
        return $value !== '' ? e($value) : '-';
    }

    // Validasi Session
    if (empty($SessionIdAkses)) {
        responseHapusMedication('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    // Validasi Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseHapusMedication('error', 'Metode request tidak valid.');
    }

    // Tangkap ID
    $id_index_medication = (int)($_POST['id_index_medication'] ?? 0);

    if ($id_index_medication < 1) {
        responseHapusMedication('error', 'ID medication tidak valid.');
    }

    // Ambil Medication
    $stmt = $Conn->prepare("
        SELECT
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
            manufacturer_name
        FROM medication
        WHERE id_index_medication = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseHapusMedication('error', 'Gagal mempersiapkan data medication.');
    }

    $stmt->bind_param("i", $id_index_medication);

    if (!$stmt->execute()) {
        $stmt->close();
        responseHapusMedication('error', 'Gagal mengambil data medication.');
    }

    $result = $stmt->get_result();
    $data   = $result->fetch_assoc();
    $stmt->close();

    if (!$data) {
        responseHapusMedication('error', 'Data medication tidak ditemukan.');
    }

    // Hitung penggunaan pada item resep
    $stmt = $Conn->prepare("
        SELECT COUNT(*) AS total
        FROM medication_request
        WHERE id_index_medication = ?
    ");

    if (!$stmt) {
        responseHapusMedication('error', 'Gagal memeriksa penggunaan medication.');
    }

    $stmt->bind_param("i", $id_index_medication);
    $stmt->execute();

    $resultPenggunaan = $stmt->get_result();
    $penggunaan       = $resultPenggunaan->fetch_assoc();
    $jumlahPenggunaan = (int)($penggunaan['total'] ?? 0);

    $stmt->close();

    // Status SATUSEHAT
    $id_medication = trim((string)($data['id_medication'] ?? ''));

    $satusehatInfo = $id_medication !== ''
        ? '<span class="text-warning">Sudah memiliki ID Medication SATUSEHAT</span>'
        : '<span class="text-muted">Belum memiliki ID Medication SATUSEHAT</span>';

    // Informasi Penggunaan
    $penggunaanInfo = $jumlahPenggunaan > 0
        ? '<span class="text-warning">Digunakan pada '.$jumlahPenggunaan.' item resep</span>'
        : '<span class="text-muted">Belum digunakan pada item resep</span>';

    // HTML
    $html = '
        <input type="hidden" name="id_index_medication" value="'.$id_index_medication.'">

        <div class="row mb-2">
            <div class="col-4"><small>ID Index</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$id_index_medication.'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>Kode Medication</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.showValue($data['medication_code']).'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>Nama Medication</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.showValue($data['medication_name']).'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>Kategori</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.showValue($data['medication_category']).'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>KFA</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7">
                <small>
                    '.showValue($data['kfa_code']).'
                    -
                    '.showValue($data['kfa_display']).'
                </small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>Sediaan</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7">
                <small>
                    '.showValue($data['sediaan_code']).'
                    -
                    '.showValue($data['sediaan_display']).'
                </small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>Racikan</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7">
                <small>
                    '.showValue($data['racikan_code']).'
                    -
                    '.showValue($data['racikan_display']).'
                </small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>SATUSEHAT</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$satusehatInfo.'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>Penggunaan</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$penggunaanInfo.'</small></div>
        </div>

        <div class="alert alert-danger text-center mt-4 mb-0">
            <small>
                <b>PENTING!</b><br>
                Data medication yang dihapus tidak dapat dikembalikan.<br><br>
                Jika medication sudah digunakan pada item resep, referensi
                <i>id_index_medication</i> pada item resep akan menjadi NULL.<br><br>
                <i>Apakah Anda yakin akan menghapus medication ini?</i>
            </small>
        </div>
    ';

    responseHapusMedication(
        'success',
        'Data medication berhasil ditemukan.',
        $html
    );
?>
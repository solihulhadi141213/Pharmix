<?php
    // CONNECTION, HELPER & SESSION
    require_once __DIR__ . '/../../_Config/Connection.php';
    require_once __DIR__ . '/../../_Config/GlobalFunction.php';
    require_once __DIR__ . '/../../_Config/Session.php';

    // Json Type Format Response
    header('Content-Type: application/json; charset=utf-8');

    // Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Sesi Akses Sudah Berakhir! Silahkan Login Ulang!',
            'html'    => ''
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // Validasi id_barang
    if(empty($_POST['id_barang'])){
        echo json_encode([
            'status'  => 'error',
            'message' => 'ID Barang Tidak Boleh Kosong',
            'html'    => ''
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // Validasi id_index_medication
    if(empty($_POST['id_index_medication'])){
        echo json_encode([
            'status'  => 'error',
            'message' => 'ID Index Tidak Boleh Kosong',
            'html'    => ''
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // id_barang & id_index_medication
    $id_barang = filter_var($_POST['id_barang'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $id_index_medication = filter_var($_POST['id_index_medication'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id_barang === false || $id_index_medication === false) {
        echo json_encode([
            'status' => 'error',
            'message' => 'ID Barang dan ID Index harus berupa bilangan bulat positif.',
            'html' => ''
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $tampil = static function ($value): string {
        $value = trim((string) ($value ?? ''));
        return htmlspecialchars($value === '' ? '-' : $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    };

    try {
    // Buka Barang
    $sqlBarang = "SELECT*FROM barang WHERE id_barang = ? LIMIT 1";
    $stmtBarang = $Conn->prepare($sqlBarang);
    if (!$stmtBarang) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Gagal menyiapkan query barang: '.$Conn->error.'',
            'html'    => ''
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    $stmtBarang->bind_param("i", $id_barang);
    if (!$stmtBarang->execute()) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Gagal menyiapkan query barang: '.$stmtBarang->error.'',
            'html'    => ''
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    $resultBarang = $stmtBarang->get_result();
    $DataBarang= $resultBarang->fetch_assoc();
    $stmtBarang->close();
    if (!$DataBarang) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Data Barang Tidak Ditemukan',
            'html'    => ''
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    if (!empty($DataBarang['id_index_medication'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Barang sudah memiliki index medication.',
            'html' => ''
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $kode_barang     = $tampil($DataBarang['kode_barang'] ?? null);
    $nama_barang     = $tampil($DataBarang['nama_barang'] ?? null);
    $kategori_barang = $tampil($DataBarang['kategori_barang'] ?? null);

    // Buka Medication
    $Qry = $Conn->prepare("SELECT * FROM medication WHERE id_index_medication = ?");
    if (!$Qry) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menyiapkan query medication.',
            'html' => ''
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $Qry->bind_param("i", $id_index_medication);
    if (!$Qry->execute()) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Terjadi kesalahan saat membuka data! '.$Conn->error.'',
            'html'    => ''
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    $Result = $Qry->get_result();
    $DataMedication   = $Result->fetch_assoc();
    $Qry->close();
    if (!$DataMedication) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Data Medication Tidak Ditemukan.',
            'html'    => ''
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    } catch (mysqli_sql_exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengambil data konfirmasi index medication.',
            'html' => ''
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $id_medication       = $tampil($DataMedication['id_medication'] ?? null);
    $medication_code     = $tampil($DataMedication['medication_code'] ?? null);
    $medication_name     = $tampil($DataMedication['medication_name'] ?? null);
    $medication_category = $tampil($DataMedication['medication_category'] ?? null);

    $html = '
        <input type="hidden" name="id_barang" value="'.$id_barang.'">
        <input type="hidden" name="id_index_medication" value="'.$id_index_medication.'">
        <div class="row mb-2">
            <div class="col-12">
                <small><b>A. Informasi Barang</b></small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>ID Barang</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7">
                <small>'.$id_barang.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Kode Barang</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7">
                <small>'.$kode_barang.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Nama Barang</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7">
                <small>'.$nama_barang.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Kategori Barang</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7">
                <small>'.$kategori_barang.'</small>
            </div>
        </div>

        <hr>

        <div class="row mb-2 mt-3">
            <div class="col-12">
                <small><b>B. Informasi Index Medication</b></small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>ID Index</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7">
                <small>'.$id_index_medication.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>ID Medication</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7">
                <small>'.$id_medication.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Kode Medication</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7">
                <small>'.$medication_code.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Nama Medication</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7">
                <small>'.$medication_name.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Kategori Medication</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7">
                <small>'.$medication_category.'</small>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <small>
                        <b>PENTING!</b><br>
                        Pastikan Index Medication Yang Digunakan Sudah Sesuai.
                    </small>
                </div>
            </div>
        </div>
    ';

    // Validasi id_index_medication
    echo json_encode([
        'status'  => 'success',
        'message' => 'Data konfirmasi index medication berhasil dimuat.',
        'html'    => $html
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;

?>

<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // ZONA WAKTU & RESPONSE
    date_default_timezone_set('Asia/Jakarta');
    header('Content-Type: application/json; charset=utf-8');

    function response(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function esc($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    // VALIDASI AKSES
    if (empty($SessionIdAkses)) {
        response('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if (empty($_POST['id_index_medication'])) {
        response('error', 'ID Index Obat/Alkes Tidak Boleh Kosong.');
    }

    // AMBIL ID
    $id_index_medication = (int)validateAndSanitizeInput($_POST['id_index_medication']);

    if ($id_index_medication < 1) {
        response('error', 'ID Index Obat/Alkes tidak valid.');
    }

    // QUERY DATABASE
    $Qry = $Conn->prepare("
        SELECT
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
        FROM medication
        WHERE id_index_medication = ?
        LIMIT 1
    ");

    if (!$Qry) {
        response('error', 'Gagal mempersiapkan query medication.');
    }

    $Qry->bind_param("i", $id_index_medication);

    if (!$Qry->execute()) {
        $error = $Qry->error;
        $Qry->close();

        response(
            'error',
            'Terjadi kesalahan saat membuka data!<br>Keterangan : '.esc($error)
        );
    }

    $Result = $Qry->get_result();
    $Data   = $Result->fetch_assoc();
    $Qry->close();

    if (!$Data) {
        response('error', 'Data medication tidak ditemukan.');
    }

    // MAPPING DATA
    $id_medication       = trim((string)($Data['id_medication'] ?? ''));
    $medication_code     = trim((string)($Data['medication_code'] ?? ''));
    $medication_name     = trim((string)($Data['medication_name'] ?? ''));
    $medication_category = trim((string)($Data['medication_category'] ?? ''));
    $kfa_code            = trim((string)($Data['kfa_code'] ?? ''));
    $kfa_display         = trim((string)($Data['kfa_display'] ?? ''));
    $sediaan_code        = trim((string)($Data['sediaan_code'] ?? ''));
    $sediaan_display     = trim((string)($Data['sediaan_display'] ?? ''));
    $racikan_code        = trim((string)($Data['racikan_code'] ?? ''));
    $racikan_display     = trim((string)($Data['racikan_display'] ?? ''));
    $manufacturer_id     = trim((string)($Data['manufacturer_id'] ?? ''));
    $manufacturer_name   = trim((string)($Data['manufacturer_name'] ?? ''));
    $ingredient_raw      = $Data['ingredient'] ?? null;

    // TOMBOL TAMBAH INGREDIENT
    $disable_ingridient = 'disabled';

    if (
        $medication_category === 'Obat' &&
        in_array($racikan_code, ['SD', 'EP'], true)
    ) {
        $disable_ingridient = '';
    }

    // DECODE INGREDIENT
    $ingredient = [];

    if (!empty($ingredient_raw)) {
        $decodedIngredient = json_decode($ingredient_raw, true);

        if (
            json_last_error() === JSON_ERROR_NONE &&
            is_array($decodedIngredient)
        ) {
            $ingredient = $decodedIngredient;
        }
    }

    // LIST INGREDIENT
    $content_row = '';

    if (!empty($ingredient)) {

        $no = 1;

        foreach ($ingredient as $ingredient_list) {

            if (!is_array($ingredient_list)) {
                continue;
            }

            $kode_kfa           = trim((string)($ingredient_list['kode_kfa'] ?? ''));
            $nama_kfa           = trim((string)($ingredient_list['nama_kfa'] ?? ''));
            $kode_numerator     = trim((string)($ingredient_list['kode_numerator'] ?? ''));
            $nama_numerator     = trim((string)($ingredient_list['nama_numerator'] ?? ''));
            $jumlah_numerator   = trim((string)($ingredient_list['jumlah_numerator'] ?? ''));
            $kode_denominator   = trim((string)($ingredient_list['kode_denominator'] ?? ''));
            $nama_denominator   = trim((string)($ingredient_list['nama_denominator'] ?? ''));
            $jumlah_denominator = trim((string)($ingredient_list['jumlah_denominator'] ?? ''));

            // Tampilan numerator
            $numerator_display = '-';

            if ($jumlah_numerator !== '') {
                $numerator_display = $jumlah_numerator;

                if ($nama_numerator !== '') {
                    $numerator_display .= ' '.$nama_numerator;
                } elseif ($kode_numerator !== '') {
                    $numerator_display .= ' '.$kode_numerator;
                }
            }

            // Tampilan denominator
            $denominator_display = '-';

            if ($jumlah_denominator !== '') {
                $denominator_display = $jumlah_denominator;

                if ($nama_denominator !== '') {
                    $denominator_display .= ' '.$nama_denominator;
                } elseif ($kode_denominator !== '') {
                    $denominator_display .= ' '.$kode_denominator;
                }
            }

            // Payload hidden
            $payloadIngredient = json_encode(
                $ingredient_list,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            $content_row .= '
                <div class="card border border-secondary border-opacity-50 rounded-3 mt-3 position-relative shadow-none">
                    <div class="card-body p-3 pe-5">

                        <a href="javascript:void(0);"
                           class="text-danger position-absolute top-0 end-0 m-2 p-1 lh-1 hapus_ingridient_edit"
                           title="Hapus Ingredient"
                           style="width:28px; height:28px;">
                            <i class="bi bi-x-lg" style="font-size:12px;"></i>
                        </a>

                        <div class="text-dark mb-1">
                            <span class="badge bg-secondary me-1">'.$no.'</span>
                            '.esc($kode_kfa).'
                        </div>

                        <div class="mt-1">
                            <small><i>'.esc($nama_kfa).'</i></small>
                        </div>

                        <div class="small text-muted d-flex flex-wrap gap-3 mt-2">
                            <div>
                                <span class="fw-semibold">Numerator:</span>
                                '.esc($numerator_display).'
                            </div>

                            <div>
                                <span class="fw-semibold">Denominator:</span>
                                '.esc($denominator_display).'
                            </div>
                        </div>

                        <input type="hidden" name="payload_ingridient_edit[]" value="'.esc($payloadIngredient).'">

                    </div>
                </div>
            ';

            $no++;
        }
    }

    // JIKA INGREDIENT KOSONG
    if ($content_row === '') {
        $content_row = '
            <div class="alert alert-secondary text-center mb-0">
                <small>Belum Ada Data Ingredient</small>
            </div>
        ';
    }

    // FORM
    $html = '
        <input type="hidden" name="id_index_medication" value="'.$id_index_medication.'">

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="medication_category_edit">Kategori Index</label>

                <select name="medication_category" id="medication_category_edit" class="form-control" required>
                    <option value="">Pilih Kategori</option>
                    <option value="Obat" '.($medication_category === 'Obat' ? 'selected' : '').'>Obat</option>
                    <option value="Alkes" '.($medication_category === 'Alkes' ? 'selected' : '').'>Alkes</option>
                    <option value="Lainnya" '.($medication_category === 'Lainnya' ? 'selected' : '').'>Lainnya</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="medication_name_edit">Nama <i>Medication</i></label>
                <input type="text" name="medication_name" id="medication_name_edit" class="form-control" value="'.esc($medication_name).'" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="kfa_code_edit"><i>Kode KFA</i></label>
                <input type="text" name="kfa_code" id="kfa_code_edit" class="form-control" value="'.esc($kfa_code).'">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="kfa_display_edit"><i>Nama KFA</i></label>
                <input type="text" name="kfa_display" id="kfa_display_edit" class="form-control" value="'.esc($kfa_display).'">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="sediaan_code_edit"><i>Kode Sediaan</i></label>
                <input type="text" name="sediaan_code" id="sediaan_code_edit" class="form-control" value="'.esc($sediaan_code).'">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="sediaan_display_edit"><i>Nama Sediaan</i></label>
                <input type="text" name="sediaan_display" id="sediaan_display_edit" class="form-control" value="'.esc($sediaan_display).'">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="manufacturer_id_edit">Kode Manufaktur</label>
                <input type="text" name="manufacturer_id" id="manufacturer_id_edit" class="form-control" value="'.esc($manufacturer_id).'">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="manufacturer_name_edit">Nama Manufaktur</label>
                <input type="text" name="manufacturer_name" id="manufacturer_name_edit" class="form-control" value="'.esc($manufacturer_name).'">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="racikan_code_edit">Racikan / Non Racikan</label>

                <select name="racikan_code" id="racikan_code_edit" class="form-control">
                    <option value="">Pilih</option>
                    <option value="NC" '.($racikan_code === 'NC' ? 'selected' : '').'>Non-compound</option>
                    <option value="SD" '.($racikan_code === 'SD' ? 'selected' : '').'>Gives of such doses</option>
                    <option value="EP" '.($racikan_code === 'EP' ? 'selected' : '').'>Divide into equal parts</option>
                </select>
            </div>
        </div>

        <div class="row mb-2 mt-3">
            <div class="col-md-12">
                <button type="button" '.$disable_ingridient.' class="btn btn-md btn-block btn-secondary" id="modal_tambah_ingridient_edit">
                    <i class="bi bi-plus"></i> Tambah Ingredient
                </button>
            </div>
        </div>

        <div class="row mb-2 mt-3">
            <div class="col-md-12" id="list_ingridient_edit">
                '.$content_row.'
            </div>
        </div>
    ';

    response('success', 'Form Berhasil Ditampilkan', $html);
?>
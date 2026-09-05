<?php
    // Koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Header output JSON
    header('Content-Type: application/json');

    // Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        echo json_encode([
            "status" => "error",
            "message" => "Sesi akses sudah berakhir. Silakan login ulang."
        ]);
        exit;
    }

    // Tangkap id_medication_request_group dari POST
    $id_medication_request_group = $_POST['id_medication_request_group'] ?? '';

    if (empty($id_medication_request_group)) {
        echo json_encode([
            "status" => "error",
            "message" => "ID Resep Tidak Boleh Kosong."
        ]);
        exit;
    }

    // Query ambil data kunjungan dengan LEFT JOIN ke tabel anggota
    $query = "SELECT * FROM medication_request_group WHERE id_medication_request_group = ?";
    $stmt  = mysqli_prepare($Conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_medication_request_group);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$data) {
        echo json_encode([
            "status" => "error",
            "message" => "ID Resep Tidak Valid"
        ]);
        exit;
    }

    // Informasi Resep
    $datetime_creat    = $data['datetime_creat'] ?: "-";
    $priority          = $data['priority'] ?: "-";
    $reason_code       = $data['reason_code'];
    $reason_display    = $data['reason_display'];
    $sumber_resep      = $data['sumber_resep']?: "-";
    $status_resep      = $data['status_resep']?: "-";
    $no_resep_nasional = $data['no_resep_nasional']?: "-";


    // Susun HTML untuk ditampilkan di modal body (FormDetail)
    $html = '
        <input type="hidden" name="id_medication_request_group" value="'.$id_medication_request_group.'">
        <div class="row mb-3 mt-3">
            <div class="col-md-12">
                <small><b>A. Informasi Umum</b></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="intent"><small>* Tujuan Permintaan</small></label>
            </div>
            <div class="col-md-8">
                <select name="intent" id="intent" class="form-control" required>
                    <option value="">Pilih</option>
                    <option value="order">Perintah/resep aktual</option>
                    <option value="plan">Rencana pemberian obat</option>
                    <option value="proposal">Usulan pemberian obat</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="id_index_medication"><small>Index Data Obat</small></label>
            </div>
            <div class="col-md-8">
                <select name="id_index_medication" id="id_index_medication" class="form-control">
                    <option value="">Pilih</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="name_medication"><small>* Nama Obat</small></label>
            </div>
            <div class="col-md-8">
                <input type="text" name="name_medication" id="name_medication" class="form-control" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="status_item_resep"><small>* Status</small></label>
            </div>
            <div class="col-md-8">
                <select name="status" id="status_item_resep" class="form-control" required>
                    <option value="">Pilih</option>
                    <option selected value="active">Aktif</option>
                    <option value="on-hold">Ditunda sementara</option>
                    <option value="completed">Selesai</option>
                    <option value="stopped">Dihentikan</option>
                    <option value="cancelled">Dibatalkan</option>
                    <option value="entered-in-error">Salah input</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="dosage_inst_text"><small>Instruksi</small></label>
            </div>
            <div class="col-md-8">
                <textarea name="dosage_inst_text" id="dosage_inst_text" class="form-control" required></textarea>
            </div>
        </div>


        <div class="row mb-3 mt-3">
            <div class="col-md-12">
                <small><b>B. Dosis, Frekuensi, Interval, Route</b></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <label for="dose_value"><small><i>* Dosis Obat</i></small></label>
            </div>
            <div class="col-md-3 mb-2">
                <input type="number" step="0.01" min="0" name="dose_value" id="dose_value" required class="form-control">
                <small><small>* Jumlah dosis obat per sekali digunakan</small></small>
            </div>
            <div class="col-md-5 mb-2">
                <select name="dose_code" id="dose_code" class="form-control select_satuan" required>
                    <option value="">Pilih Satuan</option>
                </select>
                <small><small>* Unit satuan dosis</small></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <label for="dosage_inst_frequency"><small><i>* Frequency</i></small></label>
            </div>
            <div class="col-md-8 mb-2">
                <input type="number" step="1" min="0" name="dosage_inst_frequency" id="dosage_inst_frequency" class="form-control" required>
                <small><small>* Jumlah Berapa Kali Obat Diminum Dalam Sehari (satuan Waktu)</small></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <label for="dosage_inst_period"><small><i>* Interval</i></small></label>
            </div>
            <div class="col-md-3 mb-2">
                <input type="number" step="1" min="0" name="dosage_inst_period" id="dosage_inst_period" class="form-control" value="1" required>
                <small><small>Interval waktu per setiap frekuensi obat yang diminum (Dikonsumsi, Digunakan, Dimasukan Ke Tubuh)</small></small>
            </div>
            <div class="col-md-5 mb-2">
                <select name="dosage_inst_period_unit" id="dosage_inst_period_unit" class="form-control" required>
                    <option value="s|second">Detik (Second)</option>
                    <option value="m|minute">Menit (Minute)</option>
                    <option value="h|hour">Jam (Hour)</option>
                    <option selected value="d|day">Hari (Day)</option>
                    <option value="wk|week">Minggu (Week)</option>
                    <option value="mo|month">Bulan (Month)</option>
                </select>
                <small><small>* Satuan waktu yang digunakan</small></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <label for="route_code"><small><i>* Route</i></small></label>
            </div>
            <div class="col-md-8 mb-2">
                <select name="route_code" id="route_code" class="form-control" required>
                    <option value="">Pilih</option>
                </select>
                <small><small>Cara obat masuk ke dalam tubuh (Dikonsumsi)</small></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <label for="dispense_value"><small><i>* Dispense Value</i></small></label>
            </div>
            <div class="col-md-3 mb-2">
                <input type="number" step="1" min="0" name="dispense_value" id="dispense_value" class="form-control" required>
                <small><small>Jumlah Total Obat Yang Harus Diserahkan Kepada Pasien</small></small>
            </div>
            <div class="col-md-5 mb-2">
                <select name="dispense_code" id="dispense_code" class="form-control select_satuan">
                    <option value="">Pilih Satuan</option>
                </select>
                <small><small>Satuan Obat Yang Diserahkan</small></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 md-2">
                <label for="supply_duration_value"><small><i>* Supply Duration Value</i></small></label>
            </div>
            <div class="col-md-3 md-2">
                <input type="number" step="1" min="0" name="supply_duration_value" id="supply_duration_value" class="form-control" required>
                <small><small>* Durasi waktu / berapa lama obat harus dikonsumsi</small></small>
            </div>
            <div class="col-md-5 md-2">
                <select name="supply_duration_code" id="supply_duration_code" class="form-control" required>
                    <option value="s|second">Detik (Second)</option>
                    <option value="m|minute">Menit (Minute)</option>
                    <option value="h|hour">Jam (Hour)</option>
                    <option selected value="d|day">Hari (Day)</option>
                    <option value="wk|week">Minggu (Week)</option>
                    <option value="mo|month">Bulan (Month)</option>
                </select>
                <small><small>Satuan waktu</small></small>
            </div>
        </div>
        <div class="row mb-3 mt-3">
            <div class="col-md-12">
                <small><b>C. Ingredient (Untuk Obat Racikan)</b></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="racikan_code"><small>* Kode Racikan</small></label>
            </div>
            <div class="col-md-8">
                <select name="racikan_code" id="racikan_code" class="form-control" required>
                    <option value="">Pilih</option>
                    <option value="NC">Non-compound</option>
                    <option value="SD">Gives of such doses</option>
                    <option value="EP">Divide into equal parts</option>
                </select>
            </div>
        </div>
        <div class="row mb-3 mt-3">
            <div class="col-md-12">
                <button type="button" disabled class="btn btn-md btn-block btn-secondary" id="modal_tambah_ingridient">
                    <i class="bi bi-plus"></i> Tambah Ingredient
                </button>
            </div>
        </div>
        <div class="row mb-2 mt-3">
            <div class="col-md-12" id="table_ingridient">
                <div class="table table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <td class="text-center"><small><b>No</b></small></td>
                                <td><small><b>Kode</b></small></td>
                                <td><small><b>Nama</b></small></td>
                                <td class="text-center"><small><b>Numerator</b></small></td>
                                <td class="text-center"><small><b>Denominator</b></small></td>
                                <td class="text-center"><small><b>Opsi</b></small></td>
                            </tr>
                        </thead>
                        <tbody id="table_list_ingridient">
                            <tr>
                                <td colspan="6" class="text-center"><small>No Data</small></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    ';

    echo json_encode([
        "status"       => "success",
        "html"         => $html
    ]);
?>


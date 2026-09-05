<?php
    // Koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        echo '
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Opss!</b><br>
                            Sesi Akses Sudah Berakhir! Silahkan Login Ulang!
                        </small>
                    </div>
                </div>
            </div>
        ';
        exit;
    }

    //------------------------------------------
    // Format Nilai Decimal
    function formatDecimal($value)
    {
        if ($value === null || $value === '') {
            return '0';
        }

        $value = (float) $value;
        $formatted = number_format($value, 2, '.', '');
        $formatted = rtrim($formatted, '0');
        $formatted = rtrim($formatted, '.');

        return str_replace('.', ',', $formatted);
    }

    //------------------------------------------
    // Potong ID Panjang
    function shortId($value, $length = 18)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '-';
        }

        if (strlen($value) <= $length) {
            return $value;
        }

        return substr($value, 0, $length) . '...';
    }

    //------------------------------------------
    // Tangkap ID Medication Request Group
    $id_medication_request_group = (int) ($_POST['id_medication_request_group'] ?? 0);

    if ($id_medication_request_group < 1) {
        echo '
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Opss!</b><br>
                            ID Resep Tidak Boleh Kosong!
                        </small>
                    </div>
                </div>
            </div>
        ';
        exit;
    }

    //------------------------------------------
    // Ambil Item Resep
    $sql = "
        SELECT *
        FROM medication_request
        WHERE id_medication_request_group = ?
        ORDER BY name_medication ASC
    ";

    $stmt = $Conn->prepare($sql);
    $stmt->bind_param("i", $id_medication_request_group);
    $stmt->execute();

    $query_resep = $stmt->get_result();

    //------------------------------------------
    // Jika Data Kosong
    if ($query_resep->num_rows < 1) {
        $stmt->close();

        echo '
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-danger text-center">
                        <small>
                            Belum Ada Item Resep Yang Dibuat
                        </small>
                    </div>
                </div>
            </div>
        ';
        exit;
    }

    //------------------------------------------
    // Loop Item Resep
    $no_urut = 1;

    while ($data_resep = $query_resep->fetch_assoc()) {
        //------------------------------------------
        // RESET CONTENT SETIAP ITEM
        $content_info = '';

        //------------------------------------------
        // Data Resep
        $MedicationRequestId     = $data_resep['MedicationRequestId'];
        $id_index_medication     = $data_resep['id_index_medication'];
        $name_medication         = $data_resep['name_medication'];
        $intent                  = $data_resep['intent'];
        $racikan_code            = $data_resep['racikan_code'];
        $racikan_display         = $data_resep['racikan_display'];
        $dosage_inst_text        = $data_resep['dosage_inst_text'];
        $dosage_inst_frequency   = $data_resep['dosage_inst_frequency'];
        $dosage_inst_period      = $data_resep['dosage_inst_period'];
        $dosage_inst_period_unit = $data_resep['dosage_inst_period_unit'];

        //------------------------------------------
        // Format Decimal
        $dose_value     = formatDecimal($data_resep['dose_value']);
        $dispense_value = formatDecimal($data_resep['dispense_value']);

        $dose_unit             = $data_resep['dose_unit'];
        $dose_code             = $data_resep['dose_code'];
        $dispense_unit         = $data_resep['dispense_unit'];
        $supply_duration_value = $data_resep['supply_duration_value'];
        $supply_duration_unit  = $data_resep['supply_duration_unit'];
        $ingredient            = $data_resep['ingredient'];

        //------------------------------------------
        // Routing ID Medication
        if (empty($id_index_medication)) {
            $id_medication = '';
            $label_id_medication = '<a href="javascript:void(0);" class="text-danger modal_tambah_medication" data-id="'.$MedicationRequestId.'"><i class="bi bi-send"></i> <i>ID Medication</i></a>';
        } else {
            $id_medication = GetDetailData($Conn, 'medication', 'id_index_medication', $id_index_medication, 'id_medication');

            if (empty($id_medication)) {
                $label_id_medication = '<a href="javascript:void(0);" class="text-danger modal_tambah_medication" data-id="'.$MedicationRequestId.'"><i class="bi bi-send"></i> <i>ID Medication</i></a>';
            } else {
                $id_medication_short = shortId($id_medication, 18);
                $label_id_medication = '<a href="javascript:void(0);" class="text-info modal_detail_medication" data-id="'.$id_medication.'" title="'.$id_medication.'">'.$id_medication_short.' <i class="bx bx-windows"></i></a>';
            }
        }

        //------------------------------------------
        // Routing Medication Request
        if (empty($data_resep['id_medication_request'])) {
            $id_medication_request = '';
            $label_medication_request = '<a href="javascript:void(0);" class="text-danger modal_tambah_medication_request" data-id="'.$MedicationRequestId.'"><i class="bi bi-send"></i> <i>Medication Request</i></a>';
        } else {
            $id_medication_request = $data_resep['id_medication_request'];
            $id_medication_request_short = shortId($id_medication_request, 18);
            $label_medication_request = '<a href="javascript:void(0);" class="text-info modal_detail_medication_request" data-id="'.$id_medication_request.'" title="'.$id_medication_request.'">'.$id_medication_request_short.' <i class="bx bx-windows"></i></a>';
        }

        //------------------------------------------
        // Routing Medication Dispense
        $kode_medication_dispense = GetDetailData($Conn, 'medication_dispense', 'MedicationRequestId', $MedicationRequestId, 'kode_medication_dispense');
        $id_medication_dispense   = GetDetailData($Conn, 'medication_dispense', 'MedicationRequestId', $MedicationRequestId, 'id_medication_dispense');

        if (empty($id_medication_dispense)) {
            $label_medication_dispense = '<a href="javascript:void(0);" class="text-danger modal_creat_medication_dispense" data-id="'.$MedicationRequestId.'"><small><i><i class="bi bi-send"></i> Medication Dispense</i></small></a>';
        } else {
            $id_medication_dispense_short = shortId($id_medication_dispense, 18);
            $label_medication_dispense = '<a href="javascript:void(0);" class="text-info modal_detail_medication_dispense" data-id="'.$id_medication_dispense.'" title="'.$id_medication_dispense.'"><small>'.$id_medication_dispense_short.' <i class="bx bx-windows"></i></small></a>';
        }

        //------------------------------------------
        // Content Item
        $content_info .= '
            <div class="row mb-2 border-1 border-bottom">
                <div class="col-10 mb-2">
                    <small>
                        <a href="javascript:void(0);" class="modal_detail_item_resep" data-id="'.$MedicationRequestId.'">
                            <b>'.$no_urut.'. '.$name_medication.'</b>
                        </a>
                    </small>
                </div>
                <div class="col-2 mb-2 text-end">
                    <button type="button" class="btn btn-sm btn-secondary btn-floating" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow shadow-2-strong border border-2 border-secondary shadow-3-strong">
                        <li class="dropdown-header text-start">
                            <h6>Option</h6>
                        </li>
                        <li>
                            <a class="dropdown-item modal_detail_item_resep" href="javascript:void(0)" data-id="'.$MedicationRequestId.'">
                                <i class="bi bi-info-circle"></i> Detail
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item modal_edit_item_resep" href="javascript:void(0)" data-id="'.$MedicationRequestId.'">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item modal_cetak_item_resep" href="javascript:void(0)" data-id="'.$MedicationRequestId.'">
                                <i class="bi bi-printer"></i> Cetak Label
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item modal_hapus_item_resep" href="javascript:void(0)" data-id="'.$MedicationRequestId.'">
                                <i class="bi bi-x"></i> Hapus
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small><i>ID Medication</i></small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small>'.$label_id_medication.'</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small><i>Medication Request</i></small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small>'.$label_medication_request.'</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small><i>Medication Dispense</i></small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7">'.$label_medication_dispense.'</div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Tipe Resep</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-grayish">('.$racikan_code.') '.$racikan_display.'</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Dosis</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-grayish">'.$dosage_inst_frequency.' × '.$dose_value.' '.$dose_unit.'</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Jumlah Total</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-grayish">'.$dispense_value.' '.$dispense_unit.' | '.$supply_duration_value.' '.$supply_duration_unit.'</small></div>
            </div>
        ';

        //------------------------------------------
        // Non Racikan
        if ($racikan_code === 'NC') {
            $content_info .= '
                <div class="row mb-4">
                    <div class="col-4 mb-2"><small>Instruksi</small></div>
                    <div class="col-1 mb-2"><small>:</small></div>
                    <div class="col-7 mb-2"><small class="text-grayish">'.$dosage_inst_text.'</small></div>
                </div>
            ';
        } else {
            //------------------------------------------
            // Hitung Ingredient
            $ingredient_data = 0;

            if (!empty($ingredient)) {
                $ingredient_array = json_decode($ingredient, true);

                if (is_array($ingredient_array)) {
                    $ingredient_data = count($ingredient_array);
                }
            }

            $content_info .= '
                <div class="row mb-2">
                    <div class="col-4"><small>Instruksi</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-7"><small class="text-grayish">'.$dosage_inst_text.'</small></div>
                </div>

                <div class="row mb-4">
                    <div class="col-4 mb-2"><small>Ingredient</small></div>
                    <div class="col-1 mb-2"><small>:</small></div>
                    <div class="col-7 mb-2">
                        <a href="javascript:void(0);" class="text-warning modal_detail_ingredient" data-id="'.$MedicationRequestId.'">
                            <small>('.$ingredient_data.' Item) <i class="bx bx-windows"></i></small>
                        </a>
                    </div>
                </div>
            ';
        }

        //------------------------------------------
        // Tampilkan Card
        echo '
            <div class="col-md-6 mb-3 d-flex">
                <div class="card h-100 w-100">
                    <div class="card-body">
                        <div class="mt-3 mb-3">
                            '.$content_info.'
                        </div>
                    </div>
                </div>
            </div>
        ';

        $no_urut++;
    }

    $stmt->close();
?>
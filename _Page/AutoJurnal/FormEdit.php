<?php
    // KONEKSI DAN SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Default Response Format
    header('Content-Type: application/json; charset=utf-8');

    // Validasi Session
    if (empty($SessionIdAkses)) {
        echo json_encode([
            "status"  => "error",
            "message" => "Sesi akses sudah berakhir! Silahkan Login Ulang!"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validasi ID Auto Jurnal
    if (empty($_POST['id_autojurnal_jual_beli'])) {
        echo json_encode([
            "status"  => "error",
            "message" => "ID Auto Jurnal tidak boleh kosong!"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $id_autojurnal_jual_beli = validateAndSanitizeInput($_POST['id_autojurnal_jual_beli']);

    // Ambil data auto jurnal yang akan diedit
    $Qry = $Conn->prepare("SELECT * FROM setting_autojurnal_jual_beli WHERE id_autojurnal_jual_beli = ? LIMIT 1");
    $Qry->bind_param("i", $id_autojurnal_jual_beli);
    $Qry->execute();
    $Data = $Qry->get_result()->fetch_assoc();
    $Qry->close();

    if (!$Data) {
        echo json_encode([
            "status"  => "error",
            "message" => "Data pengaturan auto jurnal tidak ditemukan."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $kategori      = $Data['kategori'];
    $selected_deb  = $Data['debet'];
    $selected_kre  = $Data['kredit'];
    $selected_up   = $Data['utang_piutang'];

    // Ambil daftar seluruh akun perkiraan untuk pilihan select option
    $list_akun = [];
    $QryAkun = $Conn->prepare("SELECT id_perkiraan, kode, nama FROM akun_perkiraan ORDER BY kode ASC");
    $QryAkun->execute();
    $ResAkun = $QryAkun->get_result();
    while ($RowAkun = $ResAkun->fetch_assoc()) {
        $list_akun[] = $RowAkun;
    }
    $QryAkun->close();

    // Fungsi helper untuk merender pilihan <option>
    function renderOptions($list_akun, $selected_id) {
        $options = '<option value="">-- Pilih Akun Perkiraan --</option>';
        foreach ($list_akun as $akun) {
            $id   = $akun['id_perkiraan'];
            $text = $akun['kode'] . ' - ' . $akun['nama'];
            $sel  = ($id == $selected_id) ? 'selected' : '';
            $options .= '<option value="'.$id.'" '.$sel.'>'.$text.'</option>';
        }
        return $options;
    }

    // Routing utang/piutnag
    if($kategori=="Penjualan" || $kategori=="Retur Pembelian"){
        $label_utang_piutang = "Akun Piutang";
    }else{
        $label_utang_piutang = "Akun Utang";
    }

    // Susun HTML Form
    $html = '
        <input type="hidden" name="id_autojurnal_jual_beli" value="'.$id_autojurnal_jual_beli.'">
        
        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Kategori Transaksi</label>
                <input type="text" class="form-control" value="'.$kategori.'" readonly>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="debet_edit" class="form-label">Akun Debet</label>
                <select name="debet" id="debet_edit" class="form-select select2-edit" style="width: 100%;">
                    '.renderOptions($list_akun, $selected_deb).'
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="kredit_edit" class="form-label">Akun Kredit</label>
                <select name="kredit" id="kredit_edit" class="form-select select2-edit" style="width: 100%;">
                    '.renderOptions($list_akun, $selected_kre).'
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="utang_piutang_edit" class="form-label">'.$label_utang_piutang.'</label>
                <select name="utang_piutang" id="utang_piutang_edit" class="form-select select2-edit" style="width: 100%;">
                    '.renderOptions($list_akun, $selected_up).'
                </select>
            </div>
        </div>

        <script>
            // Inisialisasi Select2 pada modal agar fitur pencarian aktif
            $(document).ready(function() {
                if ($.fn.select2) {
                    $(".select2-edit").select2({
                        dropdownParent: $("#ModalEdit"),
                        placeholder: "-- Pilih Akun Perkiraan --",
                        theme: "bootstrap-5",
                        allowClear: true
                    });
                }
            });
        </script>
    ';

    echo json_encode([
        "status" => "success",
        "html"   => $html
    ], JSON_UNESCAPED_UNICODE);
    exit;
?>
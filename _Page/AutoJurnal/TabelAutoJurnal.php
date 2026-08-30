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
            "status"     => "error",
            "html"       => '
                <div class="col-12">
                    <div class="alert alert-danger text-center">
                        <h1 class="bi bi-exclamation-circle"></h1>
                        Sesi akses sudah berakhir! Silahkan Login Ulang!
                    </div>
                </div>
            '
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Default HTML
    $html = '';
    $no   = 1;

    // Lakukan LEFT JOIN ke tabel akun_perkiraan sebanyak 3 kali untuk debet, kredit, dan utang_piutang
    $query = "SELECT 
                a.*, 
                d.kode AS kode_debet, d.nama AS nama_debet,
                k.kode AS kode_kredit, k.nama AS nama_kredit,
                u.kode AS kode_up, u.nama AS nama_up
              FROM setting_autojurnal_jual_beli a
              LEFT JOIN akun_perkiraan d ON a.debet = d.id_perkiraan
              LEFT JOIN akun_perkiraan k ON a.kredit = k.id_perkiraan
              LEFT JOIN akun_perkiraan u ON a.utang_piutang = u.id_perkiraan
              ORDER BY a.id_autojurnal_jual_beli ASC";

    $Qry = $Conn->prepare($query);
    $Qry->execute();
    $Result = $Qry->get_result();

    while ($Row = $Result->fetch_assoc()) {
        $id_autojurnal_jual_beli = $Row['id_autojurnal_jual_beli'];
        $kategori                = $Row['kategori'];
        
        // Format Tampilan Akun (Kode - Nama) atau '-' jika kosong/null
        $txt_debet  = !empty($Row['kode_debet']) ? $Row['kode_debet'] . ' - ' . $Row['nama_debet'] : '<span class="text-muted">- Belum diatur -</span>';
        $txt_kredit = !empty($Row['kode_kredit']) ? $Row['kode_kredit'] . ' - ' . $Row['nama_kredit'] : '<span class="text-muted">- Belum diatur -</span>';
        $txt_up     = !empty($Row['kode_up']) ? $Row['kode_up'] . ' - ' . $Row['nama_up'] : '<span class="text-muted">- Belum diatur -</span>';

        // Routing utang/piutnag
        if($kategori=="Penjualan" || $kategori=="Retur Pembelian"){
            $label_utang_piutang = "Akun Piutang";
        }else{
            $label_utang_piutang = "Akun Utang";
        }

        $html.='
            <div class="col-md-4 mb-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-8"><b>'.$no.'. '.$kategori.'</b></div>
                            <div class="col-4 text-end">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#ModalEdit" data-id="'.$id_autojurnal_jual_beli.'">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2 mt-3">
                            <div class="col-4"><small class="text-muted">Akun Debet</small></div>
                            <div class="col-8 text-end"><small><b>'.$txt_debet.'</b></small></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small class="text-muted">Akun Kredit</small></div>
                            <div class="col-8 text-end"><small><b>'.$txt_kredit.'</b></small></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small class="text-muted">'.$label_utang_piutang.'</small></div>
                            <div class="col-8 text-end"><small><b>'.$txt_up.'</b></small></div>
                        </div>
                    </div>
                </div>
            </div>
        ';
        $no++;
    }

    if (empty($html)) {
        $html = '
            <div class="col-md-12">
                <div class="alert alert-warning text-center mt-4 mb-4">
                    <h1 class="bi bi-exclamation-circle"></h1>
                    No Data
                </div>
            </div>
        ';
    }

    echo json_encode([
        "status"     => "success",
        "html"       => $html
    ], JSON_UNESCAPED_UNICODE);

    exit;
?>
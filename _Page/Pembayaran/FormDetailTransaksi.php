<?php
    //Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Default Response JSON
    header('Content-Type: application/json; charset=utf-8');

    // Default $html
    $html ="";
    
    // Validasi 'id_ref'
    if(empty($_POST['id_ref'])){
        $response = [
            "status"  => "error",
            "message" => "ID Referensi Transaksi Tidak Boleh Kosong",
            "html"    => ""
        ];
        echo json_encode($response);
        exit;
    }

    // Validasi 'database_transaksi'
    if(empty($_POST['database_transaksi'])){
        $response = [
            "status"  => "error",
            "message" => "Kategori Transaksi Tidak Boleh Kosong",
            "html"    => ""
        ];
        echo json_encode($response);
        exit;
    }

    // Variabel dan sanitasi
    $id_ref             = validateAndSanitizeInput($_POST['id_ref']);
    $database_transaksi = validateAndSanitizeInput($_POST['database_transaksi']);

    // Memastikan 'database_transaksi' hanya 'tranasksi' dan 'tranasksi_jual_beli'
    if($database_transaksi!=='transaksi' && $database_transaksi!=='transaksi_jual_beli'){
        $response = [
            "status"  => "error",
            "message" => "Kategori Transaksi Tidak Valid",
            "html"    => ""
        ];
        echo json_encode($response);
        exit;
    }

    // Routing ID Transaksi Berdasarkan 'database_transaksi'

    // ----------------------------------------------------
    // TRANSAKSI OPERASIONAL
    // ----------------------------------------------------
    if($database_transaksi=="transaksi"){
        $id_transaksi = $id_ref;

        // QUERY DATA TRANSAKSI
        $sql = "
            SELECT
                t.id_transaksi,
                t.id_transaksi_jenis,
                t.tanggal,
                t.jumlah,
                t.pembayaran,
                t.keterangan,
                t.status,
                t.creat_at,
                t.creat_by_id,
                t.creat_by_name,
                t.update_at,
                t.update_by_id,
                t.update_by_name,
                tj.nama AS nama_transaksi,
                tj.kategori AS kategori
            FROM transaksi AS t
            LEFT JOIN transaksi_jenis AS tj
                ON tj.id_transaksi_jenis = t.id_transaksi_jenis
            WHERE t.id_transaksi = ?
            LIMIT 1
        ";

        $stmt = mysqli_prepare($Conn, $sql);

        // VALIDASI PREPARE
        if (!$stmt) {
            $response['message'] = 'Gagal menyiapkan query transaksi.';
            $response['html'] = '
                <div class="alert alert-danger">
                    <small>
                        Gagal mengambil data transaksi.
                    </small>
                </div>
            ';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        // BIND PARAMETER
        mysqli_stmt_bind_param($stmt, "i", $id_transaksi);

        // EXECUTE
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            $response['message'] = 'Gagal menjalankan query transaksi.';
            $response['html'] = '
                <div class="alert alert-danger">
                    <small>
                        Gagal mengambil data transaksi.
                    </small>
                </div>
            ';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        // AMBIL HASIL
        $result = mysqli_stmt_get_result($stmt);

        // CEK DATA
        if (!$result || mysqli_num_rows($result) === 0) {
            mysqli_stmt_close($stmt);
            $response['message'] = 'Data transaksi tidak ditemukan.';
            $response['html'] = '
                <div class="row">
                    <div class="col-md-12 mb-2 text-center">
                        <small class="text-danger">
                            Data transaksi tidak ditemukan.
                        </small>
                    </div>
                </div>
            ';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        // AMBIL DATA
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // VARIABEL DATA
        $id_transaksi_jenis = (int) ($data['id_transaksi_jenis'] ?? 0);
        $nama_transaksi     = $data['nama_transaksi'] ?? '-';
        $kategori           = $data['kategori'] ?? '-';
        $tanggal            = $data['tanggal'] ?? '';
        $jumlah             = (int) ($data['jumlah'] ?? 0);
        $pembayaran         = (int) ($data['pembayaran'] ?? 0);
        $keterangan         = $data['keterangan'] ?? '';
        $status             = $data['status'] ?? '';
        $creat_at           = $data['creat_at'] ?? '';
        $creat_by_id        = $data['creat_by_id'] ?? '';
        $creat_by_name      = $data['creat_by_name'] ?? '';
        $update_at          = $data['update_at'] ?? '';
        $update_by_id       = $data['update_by_id'] ?? '';
        $update_by_name     = $data['update_by_name'] ?? '';

        // Format Tanggal
        $creat_at_format = '-';
        if (!empty($creat_at)) {
            $timestamp = strtotime($creat_at);
            if ($timestamp !== false) {
                $creat_at_format = date('d/m/Y H:i:s', $timestamp);
            }
        }

        $update_at_format = '-';
        if (!empty($update_at)) {
            $timestamp = strtotime($update_at);
            if ($timestamp !== false) {
                $update_at_format = date('d/m/Y H:i:s', $timestamp);
            }
        }

        // Menentukan Creator Dan Updater (Berdasarkan tabel akses: id_akses & nama_akses)
        if (!empty($creat_by_id)) {
            $creator = GetDetailData($Conn, 'akses', 'id_akses', $creat_by_id, 'nama_akses');
        } else {
            $creator = !empty($creat_by_name) ? $creat_by_name : '-';
        }
        if (!empty($update_by_id)) {
            $updater = GetDetailData($Conn, 'akses', 'id_akses', $update_by_id, 'nama_akses');
        } else {
            $updater = !empty($update_by_name) ? $update_by_name : '-';
        }

        // AMBIL DATA RINCIAN TRANSAKSI
        $sql_rincian = "
            SELECT
                id_transaksi_rincian,
                rincian_transaksi,
                harga,
                qty,
                satuan,
                jumlah
            FROM transaksi_rincian
            WHERE id_transaksi = ?
            ORDER BY id_transaksi_rincian ASC
        ";
        $stmt_rincian = mysqli_prepare($Conn, $sql_rincian);
        $JumlahRincian = 0;
        $HtmlRincian   = '';
        if ($stmt_rincian) {
            mysqli_stmt_bind_param($stmt_rincian, "i", $id_transaksi);
            if (mysqli_stmt_execute($stmt_rincian)) {
                $result_rincian = mysqli_stmt_get_result($stmt_rincian);
                if ($result_rincian) {
                    $no_rincian = 1;
                    while ($data_rincian = mysqli_fetch_assoc($result_rincian)) {
                        $uraian = htmlspecialchars($data_rincian['rincian_transaksi'] ?? '', ENT_QUOTES, 'UTF-8');
                        $harga = (float) ($data_rincian['harga'] ?? 0);
                        $qty = (float) ($data_rincian['qty'] ?? 0);
                        $satuan = htmlspecialchars($data_rincian['satuan'] ?? '', ENT_QUOTES, 'UTF-8');
                        $jumlah_rincian = (float) ($data_rincian['jumlah'] ?? 0);

                        // Format Rupiah
                        $harga_format = 'Rp ' . number_format($harga, 0, ',', '.');
                        $jumlah_rincian_format = 'Rp ' . number_format($jumlah_rincian, 0, ',', '.');

                        // Tambahkan HTML
                        $HtmlRincian .= '
                            <tr>
                                <td class="text-center">' . $no_rincian . '</td>
                                <td>' . $uraian . '</td>
                                <td>' . $harga_format . '</td>
                                <td>' . number_format($qty, 0, ',', '.') . '</td>
                                <td>' . $satuan . '</td>
                                <td>' . $jumlah_rincian_format . '</td>
                            </tr>
                        ';

                        $no_rincian++;
                        $JumlahRincian++;
                    }
                }
            }
            mysqli_stmt_close($stmt_rincian);
        }

        if (empty($HtmlRincian)) {
            $HtmlRincian = '
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        <small>Tidak ada data rincian transaksi</small>
                    </td>
                </tr>
            ';
        }

        // HITUNG JUMLAH JURNAL
        $JumlahJurnal = 0;

        // FORMAT DATA
        $JumlahFormat     = 'Rp ' . number_format($jumlah, 0, ',', '.');
        $PembayaranFormat = 'Rp ' . number_format($pembayaran, 0, ',', '.');

        // FORMAT TANGGAL
        $TanggalFormat = '-';
        if (!empty($tanggal)) {
            $strtotime = strtotime($tanggal);
            if ($strtotime !== false) {
                $TanggalFormat = date('d/m/Y H:i:s', $strtotime);
            }
        }

        // ESCAPE OUTPUT
        $id_transaksi_html       = htmlspecialchars((string) $id_transaksi, ENT_QUOTES, 'UTF-8');
        $id_transaksi_jenis_html = htmlspecialchars((string) $id_transaksi_jenis, ENT_QUOTES, 'UTF-8');
        $nama_transaksi_html     = htmlspecialchars($nama_transaksi, ENT_QUOTES, 'UTF-8');
        $kategori_html           = htmlspecialchars($kategori, ENT_QUOTES, 'UTF-8');
        $status_html             = htmlspecialchars($status, ENT_QUOTES, 'UTF-8');
        $keterangan_html         = nl2br(htmlspecialchars($keterangan, ENT_QUOTES, 'UTF-8'));
        $creator_html            = htmlspecialchars($creator, ENT_QUOTES, 'UTF-8');
        $updater_html            = htmlspecialchars($updater, ENT_QUOTES, 'UTF-8');

        // BADGE KATEGORI
        if ($kategori === 'Pengeluaran') {
            $kategori_label = '<small class="text-danger"><i class="bi bi-arrow-down-circle me-1"></i> Pengeluaran</small>';
        } elseif ($kategori === 'Pemasukan') {
            $kategori_label = '<small class="text-success"><i class="bi bi-arrow-up-circle me-1"></i> Pemasukan</small>';
        } else {
            $kategori_label = '<small class="text-secondary">' . $kategori_html . '</small>';
        }

        // BADGE STATUS
        switch ($status) {
            case 'Lunas':
                $status_label = '<span class="badge bg-success">Lunas</span>';
                break;
            case 'Utang':
                $status_label = '<span class="badge bg-danger">Utang</span>';
                break;
            case 'Piutang':
                $status_label = '<span class="badge bg-warning text-dark">Piutang</span>';
                break;
            default:
                $status_label = '<span class="badge bg-secondary">' . $status_html . '</span>';
                break;
        }

        // HTML DETAIL
        $html = '
            <input type="hidden" name="id_transaksi" id="put_id_transaksi" value="' . $id_transaksi_html . '">
            <input type="hidden" name="id_transaksi_jenis" value="' . $id_transaksi_jenis_html . '">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="row mb-2">
                        <div class="col-6"><small>ID Transaksi</small></div>
                        <div class="col-6"><small class="text-muted">' . $id_transaksi_html . '</small></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><small>Tanggal Transaksi</small></div>
                        <div class="col-6"><small class="text-muted">' . $TanggalFormat . '</small></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><small>Jenis Transaksi</small></div>
                        <div class="col-6"><small class="text-muted">' . $nama_transaksi_html . '</small></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><small>Kategori</small></div>
                        <div class="col-6">' . $kategori_label . '</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><small>Status</small></div>
                        <div class="col-6">' . $status_label . '</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row mb-2">
                        <div class="col-6"><small>Keterangan</small></div>
                        <div class="col-6"><small class="text-muted">' . ($keterangan !== '' ? $keterangan_html : '-') . '</small></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><small>Creat At</small></div>
                        <div class="col-6"><small class="text-muted">' . $creat_at_format . '</small></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><small>Update At</small></div>
                        <div class="col-6"><small class="text-muted">' . $update_at_format . '</small></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><small>Creat By</small></div>
                        <div class="col-6"><small class="text-muted">' . $creator_html . '</small></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><small>Update By</small></div>
                        <div class="col-6"><small class="text-muted">' . $updater_html . '</small></div>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <div class="table table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="bg-dark text-white"><b>No</b></th>
                                    <th class="bg-dark text-white"><b>Uraian</b></th>
                                    <th class="bg-dark text-white"><b>Harga</b></th>
                                    <th class="bg-dark text-white"><b>QTY</b></th>
                                    <th class="bg-dark text-white"><b>Satuan</b></th>
                                    <th class="bg-dark text-white"><b>Jumlah</b></th>
                                </tr>
                            </thead>
                            <tbody>
                                ' . $HtmlRincian . '
                                <tr>
                                    <td></td>
                                    <td colspan="4"><b>Jumlah</b></td>
                                    <td><b>' . $JumlahFormat . '</b></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td colspan="4"><b>Pembayaran</b></td>
                                    <td><b>' . $PembayaranFormat . '</b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        ';

        // RESPONSE SUCCESS
        $response = [
            'status'  => 'success',
            'message' => 'Data transaksi berhasil ditemukan.',
            'html'    => $html
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ----------------------------------------------------
    // TRANSAKSI JUAL BELI
    // ----------------------------------------------------
    if($database_transaksi=="transaksi_jual_beli"){
        $id_transaksi_jual_beli = $id_ref;

        //Buka Data 'transaksi_jual_beli'
        $Qry = $Conn->prepare("SELECT * FROM transaksi_jual_beli WHERE id_transaksi_jual_beli = ?");
        $Qry->bind_param("s", $id_transaksi_jual_beli);
        if (!$Qry->execute()) {
            $response = [
                "status"     => "error",
                "message"       => 'Terjadi Kesalahan : '.$Conn->error.'',
                "html"       => ""
            ];
            echo json_encode($response);
            exit;
        }
        $Result = $Qry->get_result();
        $Data = $Result->fetch_assoc();
        $Qry->close();
        if (!$Data) {
            $response = [
                "status"     => "error",
                "message"       => 'ID Transaksi <i>'.$id_transaksi_jual_beli.'</i> Tidak Ditemukan Pada Database',
                "html"       => ""
            ];
            echo json_encode($response);
            exit;
        }

        // Ambil Data Transaksi
        $id_anggota     = $Data['id_anggota'];
        $id_supplier    = $Data['id_supplier'];
        $kategori       = $Data['kategori'];
        $tanggal        = $Data['tanggal'];
        $subtotal       = pembulatan_nilai($Data['subtotal']);
        $ppn            = pembulatan_nilai($Data['ppn']);
        $diskon         = pembulatan_nilai($Data['diskon']);
        $total          = pembulatan_nilai($Data['total']);
        $cash           = pembulatan_nilai($Data['cash']);
        $kembalian      = pembulatan_nilai($Data['kembalian']);
        $status         = $Data['status'];
        $creat_by_id    = $Data['creat_by_id'];
        $creat_by_name  = $Data['creat_by_name'];
        $creat_at       = $Data['creat_at'];
        $update_by_id   = $Data['update_by_id'];
        $update_by_name = $Data['update_by_name'];
        $update_at      = $Data['update_at'];

        // Format Rupiah
        $subtotal_rp  = "" . number_format($subtotal, 0, ',', '.');
        $ppn_rp       = "" . number_format($ppn, 0, ',', '.');
        $diskon_rp    = "" . number_format($diskon, 0, ',', '.');
        $total_rp     = "" . number_format($total, 0, ',', '.');
        $cash_rp      = "" . number_format($cash, 0, ',', '.');
        $kembalian_rp = "" . number_format($kembalian, 0, ',', '.');

        // Ambil Nama Anggota & Supplier
        $nama_anggota  = (!empty($id_anggota)) ? GetDetailData($Conn, 'anggota', 'id_anggota', $id_anggota, 'nama') : "-";
        $nama_supplier = (!empty($id_supplier)) ? GetDetailData($Conn, 'supplier', 'id_supplier', $id_supplier, 'nama_supplier') : "-";

        // Ambil Rincian Transaksi
        $stmt = $Conn->prepare("
            SELECT 
                tjbr.id_transaksi_jual_beli_rincian,
                tjbr.id_barang,
                COALESCE(NULLIF(b.nama_barang, ''), tjbr.nama_barang) AS nama_barang,
                tjbr.satuan,
                tjbr.qty,
                tjbr.hpp,
                tjbr.harga,
                tjbr.ppn,
                tjbr.diskon,
                tjbr.subtotal
            FROM transaksi_jual_beli_rincian AS tjbr
            LEFT JOIN barang AS b 
                ON tjbr.id_barang = b.id_barang
            WHERE tjbr.id_transaksi_jual_beli = ?
            ORDER BY tjbr.id_transaksi_jual_beli_rincian ASC
        ");

        if (!$stmt) {
            $response = [
                "status"  => "error",
                "message" => "Gagal menyiapkan query rincian: " . $Conn->error,
                "html"    => ""
            ];

            echo json_encode($response);
            exit;
        }

        $stmt->bind_param("s", $id_transaksi_jual_beli);

        if (!$stmt->execute()) {
            $response = [
                "status"  => "error",
                "message" => "Gagal mengambil rincian transaksi: " . $stmt->error,
                "html"    => ""
            ];

            echo json_encode($response);
            exit;
        }

        $result_rincian = $stmt->get_result();

        //Format 
        $tanggal=date('d/m/Y H:i',strtotime($tanggal));
        $creat_at=date('d/m/Y H:i',strtotime($creat_at));
        $update_at=date('d/m/Y H:i',strtotime($update_at));

        // Ambil nama creator dan updater
        $creator = (!empty($creat_by_id)) ? GetDetailData($Conn, 'akses', 'id_akses', $creat_by_id, 'nama_akses') : "$creat_by_name";
        $updater = (!empty($update_by_id)) ? GetDetailData($Conn, 'akses', 'id_akses', $update_by_id, 'nama_akses') : "$update_by_name";

        // Routing Subject berdasarkan id_supplier or id_anggota
        $subject = '';
        if(!empty($id_supplier)){
            $subject = '
                <div class="row mb-2">
                    <div class="col-4">
                        <small>Supplier</small>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">'.$nama_supplier.'</small>
                    </div>
                </div>
            ';
        }
        if(!empty($id_anggota)){
            $subject = '
                <div class="row mb-2">
                    <div class="col-4">
                        <small>Pasien</small>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">'.$nama_anggota.'</small>
                    </div>
                </div>
            ';
        }

        $html_rincian = "";
        $no = 1;

        if ($result_rincian->num_rows > 0) {

            while ($DataRincian = $result_rincian->fetch_assoc()) {

                $nama_barang = !empty($DataRincian['nama_barang'])
                    ? $DataRincian['nama_barang']
                    : "-";

                $satuan = !empty($DataRincian['satuan'])
                    ? $DataRincian['satuan']
                    : "-";

                $qty = pembulatan_nilai($DataRincian['qty']);
                $harga = pembulatan_nilai($DataRincian['harga']);
                $jumlah = pembulatan_nilai($DataRincian['subtotal']);

                // Format angka
                $harga_rp = number_format($harga, 0, ',', '.');
                $jumlah_rp = number_format($jumlah, 0, ',', '.');

                // Format QTY
                $qty_format = rtrim(
                    rtrim(number_format($qty, 2, ',', '.'), '0'),
                    ','
                );

                // Escape output HTML
                $nama_barang = htmlspecialchars(
                    $nama_barang,
                    ENT_QUOTES,
                    'UTF-8'
                );

                $satuan = htmlspecialchars(
                    $satuan,
                    ENT_QUOTES,
                    'UTF-8'
                );

                $html_rincian .= '
                    <tr>
                        <td class="text-center">'.$no.'</td>
                        <td>'.$nama_barang.'</td>
                        <td class="text">'.$harga_rp.'</td>
                        <td class="text-center">'.$qty_format.'</td>
                        <td>'.$satuan.'</td>
                        <td class="text-end">'.$jumlah_rp.'</td>
                    </tr>
                ';

                $no++;
            }

        } else {

            $html_rincian = '
                <tr>
                    <td class="text-center" colspan="6">
                        <small class="text-muted">
                            Tidak ada rincian transaksi
                        </small>
                    </td>
                </tr>
            ';
        }

        $stmt->close();

        $html.='
            <input type="hidden" name="id" value="'.$id_transaksi_jual_beli.'">

            <div class="row mb-2">
                <div class="col-12">
                    <b># Informasi Transaksi</b>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    
                    <div class="row mb-2">
                        <div class="col-4">
                            <small>ID Transaksi</small>
                        </div>
                        <div class="col-8">
                            <small class="text-muted">'.$id_transaksi_jual_beli.'</small>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4">
                            <small>Tanggal & Jam</small>
                        </div>
                        <div class="col-8">
                            <small class="text-muted">'.$tanggal.'</small>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4">
                            <small>Transaksi</small>
                        </div>
                        <div class="col-8">
                            <small class="text-muted">'.$kategori.'</small>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4">
                            <small>Status</small>
                        </div>
                        <div class="col-8">
                            <small class="text-muted">'.$status.'</small>
                        </div>
                    </div>

                    '.$subject.'

                </div>
                <div class="col-md-6">
                    
                    <div class="row mb-2">
                        <div class="col-4">
                            <small>Creat At</small>
                        </div>
                        <div class="col-8">
                            <small class="text-muted">'.$creat_at.'</small>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4">
                            <small>Update At</small>
                        </div>
                        <div class="col-8">
                            <small class="text-muted">'.$update_at.'</small>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4">
                            <small>Creat By</small>
                        </div>
                        <div class="col-8">
                            <small class="text-muted">'.$creator.'</small>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4">
                            <small>Update By</small>
                        </div>
                        <div class="col-8">
                            <small class="text-muted">'.$updater.'</small>
                        </div>
                    </div>

                </div>
            </div>

            <div class="row mb-2 mt-3">
                <div class="col-12">
                    <b># Rincian Transaksi</b>
                </div>
            </div>

            <div class="row mb-2 mt-3">
                <div class="col-12">
                    <div class="table table-responsive">
                        <table class="table table-sm table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="bg bg-dark text-white"><b>No</b></th>
                                    <th class="bg bg-dark text-white"><b>Uraian</b></th>
                                    <th class="bg bg-dark text-white"><b>Harga</b></th>
                                    <th class="bg bg-dark text-white"><b>QTY</b></th>
                                    <th class="bg bg-dark text-white"><b>Satuan</b></th>
                                    <th class="bg bg-dark text-white text-end"><b>Jumlah</b></th>
                                </tr>
                            </thead>
                            <tbody>
                                '.$html_rincian.'
                                <tr>
                                    <td></td>
                                    <td class="text-left" colspan="4"><b>SUBTOTAL</b></td>
                                    <td class="text-end"><b>'.$subtotal_rp.'</b></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="text-left" colspan="4"><b>PPN</b></td>
                                    <td class="text-end"><b>'.$ppn_rp.'</b></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="text-left" colspan="4"><b>DISKON</b></td>
                                    <td class="text-end"><b>'.$diskon_rp.'</b></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="text-left" colspan="4"><b>TOTAL</b></td>
                                    <td class="text-end"><b>'.$total_rp.'</b></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="text-left" colspan="4"><b>CASH/TUNAI</b></td>
                                    <td class="text-end"><b>'.$cash_rp.'</b></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="text-left" colspan="4"><b>KEMBALIAN</b></td>
                                    <td class="text-end"><b>'.$kembalian_rp.'</b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        ';
        $response = [
            "status"  => "success",
            "message" => 'Data Berhasil Ditampilkan',
            "html"    => $html
        ];
        echo json_encode($response);
    }
    
?>
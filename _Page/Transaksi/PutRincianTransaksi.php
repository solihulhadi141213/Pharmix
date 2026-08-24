<?php

    // ============================================================
    // KONFIGURASI
    // ============================================================
    date_default_timezone_set('Asia/Jakarta');

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";

    // ============================================================
    // RESPONSE DEFAULT
    // ============================================================
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.',
        'html'    => ''
    ];

    // ============================================================
    // HEADER RESPONSE
    // ============================================================
    header('Content-Type: application/json; charset=utf-8');

    // ============================================================
    // VALIDASI SESSION
    // ============================================================
    if (empty($SessionIdAkses)) {

        $response['message'] = 'Sesi akses sudah berakhir.';
        $response['html'] = '
            <tr>
                <td colspan="7" class="text-center">
                    <small class="text-danger">
                        Sesi akses sudah berakhir. Silahkan Login Ulang
                    </small>
                </td>
            </tr>
        ';

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================================
    // VALIDASI ID TRANSAKSI
    // ============================================================
    $id_transaksi = $_POST['id_transaksi'] ?? '';

    if (empty($id_transaksi)) {

        $response['message'] = 'ID Transaksi Tidak Boleh Kosong!';
        $response['html'] = '
            <tr>
                <td colspan="7" class="text-center">
                    <small class="text-danger">
                        ID Transaksi Tidak Boleh Kosong!
                    </small>
                </td>
            </tr>
        ';

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================================
    // SANITASI INPUT
    // ============================================================
    $id_transaksi = validateAndSanitizeInput($id_transaksi);

    // ============================================================
    // QUERY RINCIAN TRANSAKSI
    // ============================================================
    $sql = "
        SELECT
            id_transaksi_rincian,
            id_transaksi,
            rincian_transaksi,
            harga,
            qty,
            satuan,
            jumlah
        FROM transaksi_rincian
        WHERE id_transaksi = ?
        ORDER BY id_transaksi_rincian ASC
    ";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {

        $response['message'] = 'Gagal menyiapkan query: ' . $Conn->error;

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================================
    // BIND PARAMETER
    // ============================================================
    // Gunakan "s" jika id_transaksi bertipe VARCHAR/CHAR.
    // Jika id_transaksi bertipe INTEGER, gunakan "i".
    $stmt->bind_param("s", $id_transaksi);

    // ============================================================
    // EXECUTE QUERY
    // ============================================================
    if (!$stmt->execute()) {

        $response['message'] = 'Gagal menjalankan query: ' . $stmt->error;

        $stmt->close();

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================================
    // AMBIL HASIL QUERY
    // ============================================================
    $result = $stmt->get_result();

    // ============================================================
    // TAMPILKAN RINCIAN
    // ============================================================
    $html = '';
    $no    = 1;
    $total = 0;

    if ($result->num_rows > 0) {

        while ($data = $result->fetch_assoc()) {

            // ====================================================
            // Ambil Data
            // ====================================================
            $id_transaksi_rincian = $data['id_transaksi_rincian'];
            $rincian_transaksi    = $data['rincian_transaksi'];
            $harga                = (float) $data['harga'];
            $qty                  = $data['qty'];
            $satuan               = $data['satuan'];
            $jumlah_list          = (float) $data['jumlah'];

            // ====================================================
            // Format Harga
            // ====================================================
            $HargaFormat = "Rp " . number_format(
                $harga,
                0,
                ',',
                '.'
            );

            $JumlahListFormat = "Rp " . number_format(
                $jumlah_list,
                0,
                ',',
                '.'
            );

            // ====================================================
            // Hitung Total
            // ====================================================
            $total += $jumlah_list;

            // ====================================================
            // Escape Output HTML
            // ====================================================
            $rincian_transaksi = htmlspecialchars(
                $rincian_transaksi,
                ENT_QUOTES,
                'UTF-8'
            );

            $qty = htmlspecialchars(
                $qty,
                ENT_QUOTES,
                'UTF-8'
            );

            $satuan = htmlspecialchars(
                $satuan,
                ENT_QUOTES,
                'UTF-8'
            );

            // ====================================================
            // HTML
            // ====================================================
            $html .= '
                <tr>
                    <td align="left">' . $no . '</td>
                    <td align="left">' . $rincian_transaksi . '</td>
                    <td align="left">' . $HargaFormat . '</td>
                    <td align="left">' . $qty . '</td>
                    <td align="left">' . $satuan . '</td>
                    <td align="left">' . $JumlahListFormat . '</td>
                    <td align="left">
                        <a class="btn btn-sm btn-secondary btn-floating" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" title="Opsi">
                            <i class="bi bi-three-dots-vertical"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">
                                <h6 class="mb-0">Opsi</h6>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalEditRincian" data-id="' . $id_transaksi_rincian . '">
                                    <i class="bi bi-pencil me-2"></i>Ubah/Edit
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalHapusRincian" data-id="' . $id_transaksi_rincian . '">
                                    <i class="bi bi-trash me-2"></i>Hapus
                                </a>
                            </li>
                        </ul>
                    </td>
                </tr>
            ';

            $no++;
        }

        // ========================================================
        // Total Transaksi
        // ========================================================
        $total_format = "Rp " . number_format(
            $total,
            0,
            ',',
            '.'
        );

        $html .= '
            <tr>
                <td align="left" colspan="5">
                    <b>TOTAL</b>
                </td>
                <td align="left">
                    <b>' . $total_format . '</b>
                </td>
                <td align="left"></td>
            </tr>
        ';

    } else {

        // ========================================================
        // Tidak Ada Data
        // ========================================================
        $html .= '
            <tr>
                <td colspan="7" class="text-center">
                    <small class="text-danger">
                        Tidak ada data rincian yang ditampilkan
                    </small>
                </td>
            </tr>
        ';
    }

    // ============================================================
    // Tutup Statement
    // ============================================================
    $stmt->close();

    // ============================================================
    // RESPONSE SUCCESS
    // ============================================================
    $response = [
        'status'  => 'success',
        'message' => 'Data transaksi berhasil ditemukan.',
        'html'    => $html
    ];

    // ============================================================
    // OUTPUT JSON
    // ============================================================
    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
?>
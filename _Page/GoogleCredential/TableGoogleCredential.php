<?php
    require_once __DIR__ . '/../../_Config/Connection.php';
    require_once __DIR__ . '/../../_Config/GlobalFunction.php';
    require_once __DIR__ . '/../../_Config/Session.php';

    $colspan = 6;
    $renderMessage = static function (string $message, string $class = 'text-muted') use ($colspan): void {
        $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        echo '<tr><td class="text-center py-4 ' . $class . '" colspan="' . $colspan . '"><small>' . $safeMessage . '</small></td></tr>';
    };

    if (empty($SessionIdAkses)) {
        $renderMessage('Sesi akses sudah berakhir. Silakan login kembali.', 'text-danger');
        exit;
    }

    if (IjinAksesSaya($Conn, $SessionIdAkses, 'iL7XcgWU23DEhlPqQVQ') !== 'Ada') {
        $renderMessage('Anda tidak memiliki izin untuk melihat data Google Credential.', 'text-danger');
        exit;
    }

    $sql = "SELECT id_google_credential, credential_name, client_id, client_secret, status
            FROM google_credential
            ORDER BY id_google_credential DESC";
    $result = $Conn->query($sql);

    if (!$result) {
        $renderMessage('Data Google Credential gagal dimuat.', 'text-danger');
        exit;
    }

    if ($result->num_rows === 0) {
        $renderMessage('Tidak ada data yang ditampilkan');
        $result->free();
        exit;
    }

    $no = 1;
    while ($data = $result->fetch_assoc()) {
        $id             = (int) ($data['id_google_credential'] ?? 0);
        $credentialName = htmlspecialchars($data['credential_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $clientId       = htmlspecialchars(mb_substr((string) ($data['client_id'] ?? ''), 0, 3, 'UTF-8'), ENT_QUOTES, 'UTF-8') . '***';
        $clientSecret   = htmlspecialchars(mb_substr((string) ($data['client_secret'] ?? ''), 0, 3, 'UTF-8'), ENT_QUOTES, 'UTF-8') . '***';
        $status         = $data['status'] ?? '';

        if ($status === 'Active') {
            $statusHtml = '<span class="badge bg-success">Active</span>';
        } elseif ($status === 'Inactive') {
            $statusHtml = '<span class="badge bg-secondary">Inactive</span>';
        } else {
            $statusHtml = '<span class="text-muted">-</span>';
        }

        echo '
            <tr>
                <td class="text-center"><small>' . $no . '</small></td>
                <td><small>' . $credentialName . '</small></td>
                <td><small>' . $clientId . '</small></td>
                <td><small>' . $clientSecret . '</small></td>
                <td>' . $statusHtml . '</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-floating btn-secondary" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Opsi credential ' . $id . '">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <li class="dropdown-header text-start"><h6>Opsi</h6></li>
                        <li><a class="dropdown-item modal_detail" href="javascript:void(0)" data-id="' . $id . '"><i class="bi bi-info-circle"></i> Detail</a></li>
                        <li><a class="dropdown-item modal_uji_koneksi" href="javascript:void(0)" data-id="' . $id . '"><i class="bi bi-plug"></i> Uji Koneksi</a></li>
                        <li><a class="dropdown-item modal_edit" href="javascript:void(0)" data-id="' . $id . '"><i class="bi bi-pencil"></i> Edit</a></li>
                        <li><a class="dropdown-item modal_delete" href="javascript:void(0)" data-id="' . $id . '"><i class="bi bi-trash"></i> Hapus</a></li>
                    </ul>
                </td>
            </tr>
        ';
        $no++;
    }

    $result->free();
?>

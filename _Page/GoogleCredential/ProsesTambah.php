<?php
    header('Content-Type: application/json; charset=utf-8');

    require_once __DIR__ . '/../../_Config/Connection.php';
    require_once __DIR__ . '/../../_Config/GlobalFunction.php';
    require_once __DIR__ . '/../../_Config/Session.php';

    $respond = static function (string $status, string $message): void {
        echo json_encode(
            ['status' => $status, 'message' => $message],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    };

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $respond('error', 'Metode request tidak valid.');
    }

    if (empty($SessionIdAkses)) {
        $respond('error', 'Sesi akses sudah berakhir. Silakan login kembali.');
    }

    if (IjinAksesSaya($Conn, $SessionIdAkses, 'iL7XcgWU23DEhlPqQVQ') !== 'Ada') {
        $respond('error', 'Anda tidak memiliki izin untuk menambahkan Google Credential.');
    }

    $credentialName = isset($_POST['credential_name']) && is_string($_POST['credential_name'])
        ? trim($_POST['credential_name'])
        : '';
    $clientId = isset($_POST['client_id']) && is_string($_POST['client_id'])
        ? trim($_POST['client_id'])
        : '';
    $clientSecret = isset($_POST['client_secret']) && is_string($_POST['client_secret'])
        ? trim($_POST['client_secret'])
        : '';
    $status = isset($_POST['status']) && is_string($_POST['status'])
        ? trim($_POST['status'])
        : '';

    if ($credentialName === '' || $clientId === '' || $clientSecret === '') {
        $respond('error', 'Nama credential, Client ID, dan Client Secret wajib diisi.');
    }

    if (mb_strlen($credentialName, 'UTF-8') > 255 || mb_strlen($clientId, 'UTF-8') > 255 || mb_strlen($clientSecret, 'UTF-8') > 255) {
        $respond('error', 'Setiap nilai credential maksimal 255 karakter.');
    }

    if (!in_array($status, ['Active', 'Inactive'], true)) {
        $respond('error', 'Status credential tidak valid.');
    }

    $stmt = $Conn->prepare(
        'INSERT INTO google_credential (credential_name, client_id, client_secret, status) VALUES (?, ?, ?, ?)'
    );

    if (!$stmt) {
        $respond('error', 'Query penyimpanan Google Credential gagal disiapkan.');
    }

    $stmt->bind_param('ssss', $credentialName, $clientId, $clientSecret, $status);
    $saved = $stmt->execute();
    $stmt->close();

    if (!$saved) {
        $respond('error', 'Google Credential gagal disimpan.');
    }

    $respond('success', 'Google Credential berhasil ditambahkan.');
?>

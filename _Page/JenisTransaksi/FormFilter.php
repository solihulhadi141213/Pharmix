<?php
    include "../../_Config/Connection.php";

    $keyword_by = trim($_POST['keyword_by'] ?? '');
    $allowedKeywordBy = ['kategori', 'id_akun_debet', 'id_akun_kredit'];

    // Helper function untuk render input text default
    function renderInputText() {
        echo '<label for="keyword">Keyword</label>'
        . '<input type="text" name="keyword" id="keyword" class="form-control" autocomplete="off">';
        exit;
    }

    // Validasi input
    if ($keyword_by === '' || !in_array($keyword_by, $allowedKeywordBy, true)) {
        renderInputText();
    }

    // Tampilkan Label
    echo '<label for="keyword">Keyword</label>';

    if ($keyword_by === 'kategori') {
        $sql = "SELECT DISTINCT kategori FROM transaksi_jenis WHERE kategori IS NOT NULL AND kategori <> '' ORDER BY kategori ASC";
        $errorMsg = "Gagal memuat kategori";
    } else {
        // Untuk debet / kredit menggunakan query dinamis kolom
        $kolom = $keyword_by; // id_akun_debet atau id_akun_kredit
        $labelAkun = ($keyword_by === 'id_akun_debet') ? 'akun debet' : 'akun kredit';
        
        $sql = "SELECT DISTINCT tj.$kolom, ap.kode, ap.nama, ap.saldo_normal 
                FROM transaksi_jenis AS tj 
                INNER JOIN akun_perkiraan AS ap ON ap.id_perkiraan = tj.$kolom 
                WHERE tj.$kolom IS NOT NULL 
                ORDER BY ap.nama ASC";
        $errorMsg = "Gagal memuat " . $labelAkun;
    }

    $stmt = $Conn->prepare($sql);
    if (!$stmt) {
        echo '<select name="keyword" id="keyword" class="form-select"><option value="">' . $errorMsg . '</option></select>';
        exit;
    }

    $stmt->execute();
    $result = $stmt->get_result();

    echo '<select name="keyword" id="keyword" class="form-select"><option value="">Pilih</option>';

    while ($data = $result->fetch_assoc()) {
        if ($keyword_by === 'kategori') {
            $val = $text = htmlspecialchars($data['kategori'], ENT_QUOTES, 'UTF-8');
        } else {
            $val = (int)$data[$keyword_by];
            $kode = htmlspecialchars($data['kode'] ?? '', ENT_QUOTES, 'UTF-8');
            $nama = htmlspecialchars($data['nama'] ?? '', ENT_QUOTES, 'UTF-8');
            $saldo = htmlspecialchars($data['saldo_normal'] ?? '', ENT_QUOTES, 'UTF-8');
            
            $text = $nama;
            if ($kode !== '') {
                $text = $kode . ' - ' . $nama;
            }
            if ($saldo !== '') {
                $text .= ' (' . $saldo . ')';
            }
        }
        
        echo '<option value="' . $val . '">' . $text . '</option>';
    }

    echo '</select>';
    $stmt->close();
?>
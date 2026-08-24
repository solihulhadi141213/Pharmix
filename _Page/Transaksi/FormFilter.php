<?php
    // ============================================================
    // KONEKSI & INISIALISASI
    // ============================================================
    include "../../_Config/Connection.php";

    // Ambil parameter filter keyword_by dari request POST
    $keyword_by = trim($_POST['keyword_by'] ?? '');

    // Cetak label form utama
    echo '<label for="keyword">Keyword</label>';

    // [PETUNJUK PENGEMBANGAN] Fungsi bantu untuk mencegah XSS pada output HTML option
    function escapeHtml($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    // ============================================================
    // SWITCH FILTER DINAMIS
    // ============================================================
    switch ($keyword_by) {

        // Filter Tanggal (Input Tipe Date)
        case 'tanggal':
            echo '<input type="date" name="keyword" id="keyword" class="form-control">';
            break;

        // Filter Nama Transaksi (Dropdown dari tabel transaksi_jenis)
        case 'nama_transaksi':
            $sql = "SELECT DISTINCT tj.nama AS nama_transaksi FROM transaksi AS t INNER JOIN transaksi_jenis AS tj ON tj.id_transaksi_jenis = t.id_transaksi_jenis WHERE tj.nama IS NOT NULL AND tj.nama <> '' ORDER BY tj.nama ASC";
            $query = mysqli_query($Conn, $sql);
            
            echo '<select name="keyword" id="keyword" class="form-select"><option value="">Pilih</option>';
            if ($query) {
                while ($data = mysqli_fetch_assoc($query)) {
                    $value = escapeHtml($data['nama_transaksi']);
                    echo '<option value="' . $value . '">' . $value . '</option>';
                }
            }
            echo '</select>';
            break;

        // Filter Kategori (Dropdown dari tabel transaksi_jenis)
        case 'kategori':
            $sql = "SELECT DISTINCT tj.kategori FROM transaksi AS t INNER JOIN transaksi_jenis AS tj ON tj.id_transaksi_jenis = t.id_transaksi_jenis WHERE tj.kategori IS NOT NULL AND tj.kategori <> '' ORDER BY tj.kategori ASC";
            $query = mysqli_query($Conn, $sql);
            
            echo '<select name="keyword" id="keyword" class="form-select"><option value="">Pilih</option>';
            if ($query) {
                while ($data = mysqli_fetch_assoc($query)) {
                    $value = escapeHtml($data['kategori']);
                    echo '<option value="' . $value . '">' . $value . '</option>';
                }
            }
            echo '</select>';
            break;

        // Filter Status (Dropdown dari tabel transaksi)
        case 'status':
            $sql = "SELECT DISTINCT status FROM transaksi WHERE status IS NOT NULL AND status <> '' ORDER BY status ASC";
            $query = mysqli_query($Conn, $sql);
            
            echo '<select name="keyword" id="keyword" class="form-select"><option value="">Pilih</option>';
            if ($query) {
                while ($data = mysqli_fetch_assoc($query)) {
                    $value = escapeHtml($data['status']);
                    echo '<option value="' . $value . '">' . $value . '</option>';
                }
            }
            echo '</select>';
            break;

        // Filter ID Transaksi (Input Angka)
        case 'id_transaksi':
            echo '<input type="number" name="keyword" id="keyword" class="form-control" placeholder="ID Transaksi" min="1">';
            break;

        // Filter ID Jenis Transaksi (Input Angka)
        case 'id_transaksi_jenis':
            echo '<input type="number" name="keyword" id="keyword" class="form-control" placeholder="ID Jenis Transaksi" min="1">';
            break;

        // [PETUNJUK PENGEMBANGAN] Default / Pencarian Umum (Input Text Bebas)
        default:
            echo '<input type="text" name="keyword" id="keyword" class="form-control" placeholder="Keyword">';
            break;
    }
?>
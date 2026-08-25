<?php
// ==== KONFIGURASI
include "../../_Config/Connection.php";

// ==== AMBIL KEYWORD BY
$keyword_by = trim($_POST['keyword_by'] ?? '');

// ==== LABEL
echo '<label for="keyword"><small>Keyword</small></label>';

// ==== FORM BERDASARKAN KEYWORD BY
switch ($keyword_by) {
    // ==== TANGGAL DIBUAT
    case 'creat_at':
        echo '<input type="date" name="keyword" id="keyword" class="form-control" autocomplete="off">';
        break;

    // ==== STATUS
    case 'status':
        echo '
            <select name="keyword" id="keyword" class="form-control">
                <option value="">Pilih Status</option>
                <option value="Publish">Publish</option>
                <option value="Draft">Draft</option>
            </select>
        ';
        break;

    // ==== TAGS
    case 'tags':
        echo '<select name="keyword" id="keyword" class="form-control"><option value="">Pilih Tags</option>';
        //------ Ambil daftar tag unik
        $query = mysqli_query($Conn, "
            SELECT DISTINCT tags 
            FROM dokumentasi_tags 
            WHERE tags IS NOT NULL AND tags != '' 
            ORDER BY tags ASC
        ");
        if ($query) {
            while ($data = mysqli_fetch_assoc($query)) {
                $tags = trim($data['tags'] ?? '');
                if ($tags === '') {
                    continue;
                }
                $safe_tag = htmlspecialchars($tags, ENT_QUOTES, 'UTF-8');
                echo '<option value="' . $safe_tag . '">' . $safe_tag . '</option>';
            }
            mysqli_free_result($query);
        }
        echo '</select>';
        break;

    // ==== DEFAULT / KEYWORD UMUM
    default:
        echo '<input type="text" name="keyword" id="keyword" class="form-control" autocomplete="off">';
        break;
}
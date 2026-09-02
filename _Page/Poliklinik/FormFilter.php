<?php
    include "../../_Config/Connection.php";

    $keyword_by = trim($_POST['keyword_by'] ?? '');

    if ($keyword_by === 'polyclinicStatus') {
        echo '
            <label for="keyword">
                <small>Keyword</small>
            </label>
            <select name="keyword" id="keyword" class="form-control">
                <option value="">Pilih</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        ';
        exit;
    }

    echo '
        <label for="keyword">
            <small>Keyword</small>
        </label>
        <input type="text" name="keyword" id="keyword" class="form-control" autocomplete="off">
    ';
?>

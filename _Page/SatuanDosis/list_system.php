<?php
    include "../../_Config/Connection.php";
    $query = mysqli_query($Conn, "SELECT DISTINCT system_satuan_dosis FROM referensi_satuan_dosis ORDER BY system_satuan_dosis ASC");
    while ($data = mysqli_fetch_array($query)) {
        $system_satuan_dosis = $data['system_satuan_dosis'];
        echo '<option value="'.$system_satuan_dosis.'">';
    }
?>
<?php
    include "../../_Config/Connection.php";
    $query = mysqli_query($Conn, "SELECT DISTINCT system_referensi FROM referensi_sediaan ORDER BY system_referensi ASC");
    while ($data = mysqli_fetch_array($query)) {
        $system_referensi = $data['system_referensi'];
        echo '<option value="'.$system_referensi.'">';
    }
?>
<?php
    include "../../_Config/Connection.php";
    $query = mysqli_query($Conn, "SELECT DISTINCT system_denominator FROM referensi_denominator ORDER BY system_denominator ASC");
    while ($data = mysqli_fetch_array($query)) {
        $system_denominator = $data['system_denominator'];
        echo '<option value="'.$system_denominator.'">';
    }
?>
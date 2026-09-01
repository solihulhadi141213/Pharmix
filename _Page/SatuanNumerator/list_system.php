<?php
    include "../../_Config/Connection.php";
    $query = mysqli_query($Conn, "SELECT DISTINCT system_numerator FROM referensi_numerator ORDER BY system_numerator ASC");
    while ($data = mysqli_fetch_array($query)) {
        $system_numerator = $data['system_numerator'];
        echo '<option value="'.$system_numerator.'">';
    }
?>
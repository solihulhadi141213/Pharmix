<?php
    include "../../_Config/Connection.php";
    $query = mysqli_query($Conn, "SELECT DISTINCT system_route FROM  referensi_route ORDER BY system_route ASC");
    while ($data = mysqli_fetch_array($query)) {
        $system_route = $data['system_route'];
        echo '<option value="'.$system_route.'">';
    }
?>
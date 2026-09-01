<?php
    include "../../_Config/Connection.php";
    $query = mysqli_query($Conn, "SELECT DISTINCT category FROM referensi_sediaan ORDER BY category ASC");
    while ($data = mysqli_fetch_array($query)) {
        $category = $data['category'];
        echo '<option value="'.$category.'">';
    }
?>
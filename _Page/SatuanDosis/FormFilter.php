<?php
    include "../../_Config/Connection.php";
    if(empty($_POST['keyword_by'])){
        echo '<input type="text" name="keyword" id="keyword" class="form-control">';
    }else{
        $keyword_by=$_POST['keyword_by'];
        if($keyword_by=="system_satuan_dosis"){
            echo '<select type="text" name="keyword" id="keyword" class="form-control">';
            echo '  <option value="">Pilih</option>';
            $query = mysqli_query($Conn, "SELECT DISTINCT system_satuan_dosis FROM referensi_satuan_dosis ORDER BY system_satuan_dosis ASC");
            while ($data = mysqli_fetch_array($query)) {
                $system_satuan_dosis= $data['system_satuan_dosis'];
                echo '  <option value="'.$system_satuan_dosis.'">'.$system_satuan_dosis.'</option>';
            }
            echo '</select>';
        }else{
            echo '<input type="text" name="keyword" id="keyword" class="form-control">';
        }
    }
?>
<?php
    include "../../_Config/Connection.php";
    if(empty($_POST['keyword_by'])){
        echo '<input type="text" name="keyword" id="keyword" class="form-control">';
    }else{
        $keyword_by=$_POST['keyword_by'];
        if($keyword_by=="category"){
            echo '<select type="text" name="keyword" id="keyword" class="form-control">';
            echo '  <option value="">Pilih</option>';
            $query = mysqli_query($Conn, "SELECT DISTINCT category FROM referensi_sediaan ORDER BY category ASC");
            while ($data = mysqli_fetch_array($query)) {
                $category= $data['category'];
                echo '  <option value="'.$category.'">'.$category.'</option>';
            }
            echo '</select>';
        }else{
            if($keyword_by=="group_name"){
                echo '<select type="text" name="keyword" id="keyword" class="form-control">';
                echo '  <option value="">Pilih</option>';
                echo '  <option value="Obat">Obat</option>';
                echo '  <option value="Alkes">Alkes</option>';
                echo '</select>';
            }else{
                echo '<input type="text" name="keyword" id="keyword" class="form-control">';
            }
        }
    }
?>
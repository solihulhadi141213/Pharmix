<?php
    include "../../_Config/Connection.php";
    if(empty($_POST['keyword_by'])){
        echo '<input type="text" name="keyword" id="keyword" class="form-control">';
    }else{
        $keyword_by=$_POST['keyword_by'];
        if($keyword_by=="sediaan_display"){
            echo '<select type="text" name="keyword" id="keyword" class="form-control">';
            echo '  <option value="">Pilih</option>';
            $query = mysqli_query($Conn, "SELECT DISTINCT sediaan_display FROM medication ORDER BY sediaan_display ASC");
            while ($data = mysqli_fetch_array($query)) {
                $sediaan_display= $data['sediaan_display'];
                echo '  <option value="'.$sediaan_display.'">'.$sediaan_display.'</option>';
            }
            echo '</select>';
        }else{
            if($keyword_by=="medication_category"){
                echo '<select type="text" name="keyword" id="keyword" class="form-control">';
                echo '  <option value="">Pilih</option>';
                echo '  <option value="Obat">Obat</option>';
                echo '  <option value="Alkes">Alkes</option>';
                echo '  <option value="Lainnya">Lainnya</option>';
                echo '</select>';
            }else{
                echo '<input type="text" name="keyword" id="keyword" class="form-control">';
            }
        }
    }
?>
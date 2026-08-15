<?php
    include "../../_Config/Connection.php";
    if(empty($_POST['keyword_by'])){
       
        echo '<input type="text" name="keyword" id="keyword" class="form-control">';
    }else{
        $keyword_by=$_POST['keyword_by'];
        if($keyword_by=="tanggal_masuk"){
           
            echo '<input type="date" name="keyword" id="keyword" class="form-control">';
        }else{
            if($keyword_by=="tanggal_keluar"){
               
                echo '<input type="date" name="keyword" id="keyword" class="form-control">';
            }else{
                if($keyword_by=="gender"){
                   
                    echo '<select name="keyword" id="keyword" class="form-control">';
                    echo '  <option value="">Pilih</option>';
                    echo '  <option value="Male">Laki-laki</option>';
                    echo '  <option value="Female">Perempuan</option>';
                    echo '</select>';
                }else{
                    echo '<input type="text" name="keyword" id="keyword" class="form-control">';
                }
            }
        }
    }
?>
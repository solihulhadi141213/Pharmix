<?php
    include "../../_Config/Connection.php";
    
    if(!empty($_POST['keyword_by'])){
        $KeywordBy=$_POST['keyword_by'];
        if($KeywordBy=="tanggal"){
            echo ' 
                <label for="keyword">
                    <small>Keyword</small>
                </label>
                <input type="date" name="keyword" id="keyword" class="form-control">
            ';
        }else{
            if($KeywordBy=="kategori"){
                echo '
                    <label for="keyword">
                        <small>Keyword</small>
                    </label>
                ';
                echo '<select name="keyword" id="keyword" class="form-control">';
                echo '  <option value="">Pilih</option>';
                $query = mysqli_query($Conn, "SELECT DISTINCT kategori FROM transaksi_jual_beli ORDER BY kategori ASC");
                while ($data = mysqli_fetch_array($query)) {
                    $kategori= $data['kategori'];
                    echo '  <option value="'.$kategori.'">'.$kategori.'</option>';
                }
                echo '</select>';
            }else{
                if($KeywordBy=="tanggal"){
                    echo '
                        <label for="keyword"><small>Keyword</small></label>
                        <input type="date" name="keyword" id="keyword" class="form-control">
                    ';
                }else{
                    echo '
                        <label for="keyword"><small>Keyword</small></label>
                        <input type="text" name="keyword" id="keyword" class="form-control">
                    ';
                }
            }
        }
    }else{
        echo ' 
            <label for="keyword"><small>Keyword</small></label>
            <input type="text" name="keyword" id="keyword" class="form-control">
        ';
    }
?>
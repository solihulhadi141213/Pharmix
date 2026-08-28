<?php
    include "../../_Config/Connection.php";
    
    if(!empty($_POST['keyword_by_operasional'])){

        // Variabel keyword_by_operasional
        $KeywordBy=$_POST['keyword_by_operasional'];

        // Apabila tanggal
        if($KeywordBy=="tanggal"){
            echo ' 
                <label for="keyword_operasional">
                    <small>Keyword</small>
                </label>
                <input type="date" name="keyword" id="keyword_operasional" class="form-control">
            ';
        }else{

            if($KeywordBy=="nama"){
                echo '
                    <label for="keyword_operasional">
                        <small>Keyword</small>
                    </label>
                ';
                echo '<select name="keyword" id="keyword" class="form-control">';
                echo '  <option value="">Pilih</option>';
                $query = mysqli_query($Conn, "SELECT id_transaksi_jenis, nama, kategori FROM transaksi_jenis ORDER BY nama ASC");
                while ($data = mysqli_fetch_array($query)) {
                    $id_transaksi_jenis = $data['id_transaksi_jenis'];
                    $nama               = $data['nama'];
                    $kategori           = $data['kategori'];
                    echo '  <option value="'.$id_transaksi_jenis.'">'.$nama.' ('.$kategori.')</option>';
                }
                echo '</select>';
            }else{
                echo ' 
                    <label for="keyword_operasional"><small>Keyword</small></label>
                    <input type="text" name="keyword" id="keyword_operasional" class="form-control">
                ';
            }
        }
    }else{
        echo ' 
            <label for="keyword_operasional"><small>Keyword</small></label>
            <input type="text" name="keyword" id="keyword_operasional" class="form-control">
        ';
    }
?>
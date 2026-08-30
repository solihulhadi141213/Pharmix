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
            if($KeywordBy=="kategori_transaksi"){
                echo '
                    <label for="keyword">
                        <small>Keyword</small>
                    </label>
                ';
                echo '<select name="keyword" id="keyword" class="form-control">';
                echo '  <option value="">Pilih</option>';
                //Buka Tabel Supplier
                $query = mysqli_query($Conn, "SELECT DISTINCT kategori_transaksi FROM transaksi_pembayaran ORDER BY kategori_transaksi ASC");
                while ($data = mysqli_fetch_array($query)) {
                    $kategori_transaksi= $data['kategori_transaksi'];
                    echo '<option value="'.$kategori_transaksi.'">'.$kategori_transaksi.'</option>';
                }
                echo '</select>';
            }else{
                if($KeywordBy=="kategori_pembayaran"){
                    echo '
                        <label for="keyword">
                            <small>Keyword</small>
                        </label>
                    ';
                    echo '<select name="keyword" id="keyword" class="form-control">';
                    echo '  <option value="">Pilih</option>';
                    //Buka Tabel Supplier
                    $query = mysqli_query($Conn, "SELECT DISTINCT kategori_pembayaran FROM transaksi_pembayaran ORDER BY kategori_pembayaran ASC");
                    while ($data = mysqli_fetch_array($query)) {
                        $kategori_pembayaran= $data['kategori_pembayaran'];
                        echo '<option value="'.$kategori_pembayaran.'">'.$kategori_pembayaran.'</option>';
                    }
                    echo '</select>';
                }else{
                    if($KeywordBy=="creat_by_name"){
                        echo '
                            <label for="keyword">
                                <small>Keyword</small>
                            </label>
                        ';
                        echo '<select name="keyword" id="keyword" class="form-control">';
                        echo '  <option value="">Pilih</option>';
                        //Buka Tabel Supplier
                        $query = mysqli_query($Conn, "SELECT DISTINCT creat_by_name FROM transaksi_pembayaran ORDER BY creat_by_name ASC");
                        while ($data = mysqli_fetch_array($query)) {
                            $creat_by_name= $data['creat_by_name'];
                            echo '<option value="'.$creat_by_name.'">'.$creat_by_name.'</option>';
                        }
                        echo '</select>';
                    }else{
                        echo '
                            <label for="keyword">
                                <small>Keyword</small>
                            </label>
                            <input type="text" name="keyword" id="keyword" class="form-control">
                        ';
                    }
                }
            }
        }
    }else{
        echo '
            <label for="keyword">
                <small>Keyword</small>
            </label>
            <input type="text" name="keyword" id="keyword" class="form-control">
        ';
    }
?>
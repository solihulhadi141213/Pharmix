<?php
    include "../../_Config/Connection.php";
    if(empty($_POST['keyword_by'])){
        echo '
            <label for="keyword">Keyword</label>
            <input type="text" name="keyword" id="keyword" class="form-control">
        ';
    }else{
        $keyword_by=$_POST['keyword_by'];
        if($keyword_by=="tanggal_kunjungan"){
            echo '
                <label for="keyword">Keyword</label>
                <input type="date" name="keyword" id="keyword" class="form-control">
            ';
        }else{
            if($keyword_by=="priority"){
                 echo '
                    <label for="keyword">Keyword</label>
                    <select name="keyword" id="keyword" class="form-control">
                        <option value="">Pilih</option>
                        <option value="Normal">Normal</option>
                        <option value="Urgent">Urgent</option>
                        <option value="Emergency">Emergency</option>
                    </select>
                 ';
            }else{
                 if($keyword_by=="jenis_kunjungan"){
               
                    echo '
                        <label for="keyword">Keyword</label>
                        <select name="keyword" id="keyword" class="form-control">
                            <option value="AMB">Rawat Jalan</option>
                            <option value="IMP">Rawat Inap</option>
                            <option value="EMER">Emergency / Gawat Darurat</option>
                        </select>
                    ';
                }else{
                    if($keyword_by=="status"){
                        echo '
                            <label for="keyword">Keyword</label>
                            <select name="keyword" id="keyword" class="form-control">
                                <option value="planned">Planned</option>
                                <option selected value="arrived">Arrived</option>
                                <option value="triaged">Triaged</option>
                                <option value="in-progress">In-Progress</option>
                                <option value="onleave">Onleave</option>
                                <option value="finished">Finished</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="entered-in-error">Entered in error</option>
                                <option value="unknown">Unknown</option>
                            </select>
                        ';
                    }else{
                        echo '
                            <label for="keyword">Keyword</label>
                            <input type="text" name="keyword" id="keyword" class="form-control">
                        ';
                    }
                }
            }
        }
    }
?>
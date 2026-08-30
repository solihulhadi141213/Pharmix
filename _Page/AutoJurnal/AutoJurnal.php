<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'eQhEWIf1fV6xwMNr8J9');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
?>
    <div class="pagetitle">
        <h1>
            <a href="">
                <i class="bi bi-gear"></i> Auto Jurnal</a>
            </a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active"> Auto Jurnal</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row" id="list_auto_jurnal">
            <div class="col-md-12">
                <div class="alert alert-warning text-center mt-4 mb-4">
                    <h1 class="bi bi-exclamation-circle"></h1>
                    No Data
                </div>
            </div>
        </div>
        
    </section>
<?php } ?>
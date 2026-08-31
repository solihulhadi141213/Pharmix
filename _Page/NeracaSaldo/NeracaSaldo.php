<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'incMmh5yyCmCs4IwCYz');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
?>
    <div class="pagetitle">
        <h1>
            <a href="">
                <i class="bi bi-graph-down-arrow"></i> Neraca Saldo</a>
            </a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active"> Neraca Saldo</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-md btn-secondary" data-bs-toggle="modal" data-bs-target="#ModalFilter">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button type="button" class="btn btn-md btn-secondary" data-bs-toggle="modal" data-bs-target="#ModalExport">
                                    <i class="bi bi-download"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mt-4 mb-4">
                            <div class="col-12 text-center" id="title_report">
                                <b>LAPORAN NERACA SALDO</b><br>
                                Periode Data
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th class="bg-dark text-white"><b>No</b></th>
                                                <th class="bg-dark text-white"><b>Kode</b></th>
                                                <th class="bg-dark text-white"><b>Nama Akun</b></th>
                                                <th class="bg-dark text-white"><b>SN</b></th>
                                                <th class="bg-dark text-white"><b>Debet</b></th>
                                                <th class="bg-dark text-white"><b>Kredit</b></th>
                                                <th class="bg-dark text-white"><b>saldo</b></th>
                                            </tr>
                                        </thead>
                                        <tbody id="table_neraca">
                                            <tr>
                                                <td class="text-center text-grayish" colspan="7">
                                                    <h1 class="bi bi-exclamation-triangle"></h1>
                                                    Tidak Ada Data Yang Ditampilkan
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <small id="data_count">Data Count : 0 Record</small>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php } ?>
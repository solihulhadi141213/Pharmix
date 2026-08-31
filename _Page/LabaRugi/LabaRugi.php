<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'N6O5Qc64hOEhPQukZSh');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
?>
    <div class="pagetitle">
        <h1>
            <a href="">
                <i class="bi bi-graph-down-arrow"></i> Laba Rugi</a>
            </a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active"> Laba Rugi</li>
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
                            <div class="col-md-12 text-center" id="title_laba_rugi">
                                <b>LAPORAN LABA / RUGI</b>
                                <p>Periode Data & Akun Perkiraan</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                               <div class="table table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th class="bg-dark text-white"><b>No</b></th>
                                                <th class="bg-dark text-white"><b>Tanggal</b></th>
                                                <th class="bg-dark text-white"><b>Kategori</b></th>
                                                <th class="bg-dark text-white"><b>Akun Perkiraan</b></th>
                                                <th class="bg-dark text-white"><b>Debet/Kredit</b></th>
                                                <th class="bg-dark text-white"><b>Nominal</b></th>
                                                <th class="bg-dark text-white"><b>Saldo</b></th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabel_laba_rugi">
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    <h1 class="bi bi-exclamation-triangle text-grayish"></h1>
                                                    <span class="text text-grayish">Belum Ada Data Yang Ditampilkan!</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                               </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12">
                                <small id="data_count">Data Count : 00 Record</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php } ?>
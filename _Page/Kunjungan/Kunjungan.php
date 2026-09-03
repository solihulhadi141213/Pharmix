<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'oWpF1xPn8dLgRi8hRJx');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
?>
    <div class="pagetitle">
        <h1>
            <a href="">
                <i class="bi bi-clipboard-plus"></i> Kunjungan</a>
            </a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Kunjungan</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">

        <!-- Table View -->
        <div class="row">
            <div class="col-lg-12" id="table_view">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-12 text-end">
                                <button type="button" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalFilter" title="Filter Data">
                                    <i class="bi bi-search"></i>
                                </button>

                                <button type="button" class="btn btn-md btn-primary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalTambah" title="Tambah Data Pasien Baru">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table table-responsive mt-4 mb-4">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><b>No</b></th>
                                        <th><b>RM</b></th>
                                        <th><b>Nama</b></th>
                                        <th><b>Tanggal</b></th>
                                        <th><b>Kategori</b></th>
                                        <th><b><i>Priority</i></b></th>
                                        <th><b><i>ID Encounter</i></b></th>
                                        <th><b>Status</b></th>
                                        <th><b>Opsi</b></th>
                                    </tr>
                                </thead>
                                <tbody id="tabel_kunjungan">
                                    <tr>
                                        <td colspan="9" class="text-center">No Data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-6">
                                <small id="page_info">
                                    Page 1 Of 100
                                </small>
                            </div>
                            <div class="col-6 text-end">
                                <button type="button" class="btn btn-md btn-outline-info btn-floating" id="prev_button">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-md btn-outline-info btn-floating" id="next_button">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tampilan Detail -->
        <div class="row">
            <div class="col-12" id="detail_view">
                <!-- Form Detail Pasien -->
            </div>
        </div>
    </section>
<?php } ?>
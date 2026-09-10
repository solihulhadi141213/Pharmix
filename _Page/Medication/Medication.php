<?php
   // Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya = IjinAksesSaya($Conn, $SessionIdAkses, 'WbtmIx8OxP90BbsMd0m');
    if ($IjinAksesSaya !== "Ada") {
        include "_Page/Error/NoAccess.php";
    } else {
?>
    <div class="pagetitle">
        <h1>
            <a href="">
                <i class="bi bi-bookmark"></i> Index Obat & Alkes <i>(Medication)</i></a>
            </a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Medication</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <small>
                        Halaman ini digunakan untuk mengelola master data obat dan alat kesehatan (ALKES) dan integrasi data lokal dengan referensi Satu Sehat melalui resource <i>Medication</i>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </small>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-table card-data">
                    <div class="card-header">
                       <div class="row">
                            <div class="col-md-12 text-end">
                                <button type="button" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalFilter">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button type="button" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-download"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalImport">
                                            <i class="bi bi-upload"></i> Import
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalExport">
                                            <i class="bi bi-download"></i> Export
                                        </a>
                                    </li>
                                </ul>
                                <button type="button" class="btn btn-md btn-primary btn-floating" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-plus"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalCariKfa">
                                            <i class="bi bi-plus"></i> Tambah Dari KFA
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item modal_tambah_manual" href="javascript:void(0)">
                                            <i class="bi bi-plus"></i> Tambah Manual
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="table-load-container mt-3">
                        <table class="table table-hover table-responsive-card" id="tabel_medication">
                            <thead>
                                <tr>
                                    <th><b>No</b></th>
                                    <th><b>Kode</b></tthd>
                                    <th><b>Index Obat/Alkes</b></th>
                                    <th><b>Kategori</b></th>
                                    <th><b>Sediaan</b></th>
                                    <th><b>KFA</b></th>
                                    <th><b>NC</b></th>
                                    <th>
                                        <b><i>ID Medication</i></b>
                                    </tdth>
                                    <th><b>Status</b></th>
                                    <th><b>Opsi</b></th>
                                </tr>
                            </thead>
                            <tbody id="TabelMedication">
                                <tr>
                                    <td class="text-center" colspan="10">
                                        <small>Loading...</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer border-0">
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
    </section>
<?php } ?>
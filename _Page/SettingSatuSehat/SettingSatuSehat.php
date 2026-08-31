<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'io72fRzU0o0c9pf4ICe');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
?>
    <div class="pagetitle">
        <h1>
            <a href="">
                <i class="bi bi-plug"></i> Koneksi Satu Sehat</a>
            </a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Koneksi Satu Sehat</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <small>
                        Berikut ini adalah halaman untuk melakukan pengaturan koneksi dengan Satu Sehat. 
                        Baca dokumentasi lengkap pada URL Postman <a href="https://rsuelsyifa.postman.co/workspace/SIRS-RSU-El-Syifa~d71f9396-0edb-41e8-8420-68ef7e205628/collection/12795177-2f2bc49a-0c6b-4e28-9c76-028a1da62aaf?action=share&source=copy-link&creator=12795177&active-environment=aebd0e34-e6c3-4328-93f3-e7ad8406e1a4">Berikut Ini</a>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </small>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-md btn-primary btn-floating modal_tambah" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Tambah Pengaturan Koneksi Satu Sehat">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <td class="text-center"><b><small>No</small></b></td>
                                        <td class="text-left"><b><small>Nama Koneksi</small></b></td>
                                        <td class="text-left"><b><small>Base URL</small></b></td>
                                        <td class="text-left"><b><small>Organization ID</small></b></td>
                                        <td class="text-left"><b><small>Client Key</small></b></td>
                                        <td class="text-left"><b><small>Secret Key</small></b></td>
                                        <td class="text-center"><b><small>Status</small></b></td>
                                        <td class="text-center"><b><small>Opsi</small></b></td>
                                    </tr>
                                </thead>
                                <tbody id="tabel_koneksi">
                                    <tr>
                                        <td class="text-center" colspan="8">
                                            <small>Tidak ada data yang ditampilkan</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12">
                                <small>
                                    Keterangan : <span class="text text-muted">Silahkan lakukan uji coba koneksi untuk memastikan pengaturan sudah benar</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php } ?>
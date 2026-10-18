<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'iL7XcgWU23DEhlPqQVQ');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
?>
    <div class="pagetitle">
        <h1>
            <a href="">
                <i class="bi bi-google"></i> Google Credential</a>
            </a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Google Credential</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <small>
                        Berikut ini adalah halaman untuk mengelola pengaturan google credential. 
                        Buat profil pengaturan google credential menggunakan parameter API yang anda miliki.
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
                                <button type="button" class="btn btn-md btn-primary btn-floating modal_tambah" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Tambah Pengaturan Google">
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
                                        <td class="text-left"><b><small>Nama Credential</small></b></td>
                                        <td class="text-left"><b><small>Client ID</small></b></td>
                                        <td class="text-left"><b><small>Client Secret</small></b></td>
                                        <td class="text-left"><b><small>Status</small></b></td>
                                        <td class="text-center"><b><small>Opsi</small></b></td>
                                    </tr>
                                </thead>
                                <tbody id="tabel_credential">
                                    <tr>
                                        <td class="text-center" colspan="6">
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
<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'5MH1cfu7LzOpalmhbT2');
    $IjinTambahDokumentasi=IjinAksesSaya($Conn,$SessionIdAkses,'QbQ4qF57AzCEp5qG0KG');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
?>
    <div class="pagetitle">
        <h1>
            <a href="">
                <i class="bi bi-file-earmark-text"></i> Dokumentasi</a>
            </a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Dokumentasi</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        
        <!-- TABEL DOKUMENTASI -->
        <div class="row" id="tabel_view">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-8">
                                <b class="card-title">
                                    <i class="bi bi-file-earmark-text"></i> Daftar Dokumentasi
                                </b>
                            </div>
                            <div class="col-4 text-end">
                                <button type="button" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalFilter">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button type="button" class="btn btn-md btn-primary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalTambah">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mt-3 mb-3">
                            <div class="col-md-12">
                                <div class="table table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead>
                                            <tr>
                                                <th><b>No</b></th>
                                                <th><b>Judul</b></th>
                                                <th><b>Tags</b></th>
                                                <th><b>Tanggal</b></th>
                                                <th><b>Author</b></th>
                                                <th><b>Status</b></th>
                                                <th><b>Opsi</b></th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabel_dokumentasi">
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    <small class="text-muted">No Data</small>
                                                </td>
                                            </tr>
                                            <!-- Menampilkan Tabel Help -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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

        <!-- DETAIL DOKUMENTASI -->
        <div class="row" id="detail_view">

            <input type="hidden" name="id_dokumentasi" id="put_id_dokumentasi" value="0">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-8">
                                <b class="card-title">
                                    <i class="bi bi-info-circle"></i> Detail Dokumentasi
                                </b>
                            </div>
                            <div class="col-4 text-end">
                                <button type="button" class="btn btn-md btn-secondary btn-floating back_to_table_view" title="Kembali">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-md btn-outline-secondary btn-floating" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" style="">
                                    <li class="dropdown-header text-start">
                                        <h6>Option</h6>
                                    </li>
                                    <li>
                                        <a class="dropdown-item edit_dokumentasi2" href="javascript:void(0)">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a class="dropdown-item hapus_dokumentasi2" href="javascript:void(0)">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3 mt-3">
                            <div class="col-12">
                                <h3>
                                    <b id="put_judul">
                                        <!-- Judul Konten Akan Tampil Disini -->
                                    </b>
                                </h3>
                                <figure>
                                    <figcaption class="blockquote-footer mt-3" id="put_deskripsi">
                                        <!-- Deskripsi Konten Akan Tampil Disini -->
                                    </figcaption>
                                </figure>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12" id="detail_tags">
                                <div id="put_tags">

                                    <!-- List Tags Akan Muncul Disini -->
                                    <!-- <span class="badge bg-success-subtle text-success border border-success rounded-pill">
                                        Akses
                                    </span>

                                    <span class="badge bg-success-subtle text-success border border-success rounded-pill">
                                        Barang
                                    </span> -->
                                </div>
                                
                            </div>
                        </div>

                       <hr style="border-top: 1px dashed #adb5bd; opacity: 1;">

                        <div class="put_list_konten mb-3">

                            <!-- Contoh List Ke 1 -->
                            <!-- <div class="row mb-3 mt-3 hover-shadow">
                                <div class="col-12  mt-3 mb-3">
                                    <p>
                                        Menu <strong>Pengelolaan Data Barang</strong> digunakan untuk mengelola
                                        seluruh data barang yang tersedia pada aplikasi. Melalui menu ini,
                                        pengguna dapat menambahkan, mengubah, melihat, dan menghapus data barang
                                        sesuai dengan hak akses yang dimiliki.
                                    </p>
                                    <p>
                                        Untuk menambahkan barang baru, pengguna dapat mengikuti langkah-langkah
                                        berikut:
                                    </p>
                                </div>
                            </div>
                            <hr style="border-top: 1px dashed #adb5bd; opacity: 1;"> -->

                            <!-- Contoh List Ke 2 -->
                            <!-- <div class="row mb-3 mt-3 hover-shadow">
                                <div class="col-12 mt-3 mb-3">
                                    <ol>
                                        <li>Buka menu <strong>Master Data</strong>.</li>
                                        <li>Pilih submenu <strong>Barang</strong>.</li>
                                        <li>Klik tombol <strong>Tambah Data</strong>.</li>
                                        <li>Isi seluruh informasi barang yang diperlukan.</li>
                                        <li>Klik tombol <strong>Simpan</strong>.</li>
                                    </ol>
                                </div>
                            </div>
                            <hr style="border-top: 1px dashed #adb5bd; opacity: 1;"> -->

                            <!-- Contoh List Ke 2 -->
                        </div>
                        
                        <hr style="border-top: 1px dashed #adb5bd; opacity: 1;">
                        <div class="row mb-3 mt-4">
                            <div class="col-12 text-center">
                                <button type="button" class="btn btn-lg btn-secondary btn-floating tambah_dokumentasi_konten">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-8">
                                <small>
                                    <i>Creat By : <span id="put_author"></span></i>
                                </small>
                            </div>
                            <div class="col-4 text-end" id="put_status">
                                <!-- <span class="badge bg-success text-light border border-success rounded-pill">
                                    Publish
                                </span> -->
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>

        
    </section>
<?php } ?>
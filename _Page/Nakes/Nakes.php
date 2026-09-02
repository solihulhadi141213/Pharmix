<?php
    // Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya = IjinAksesSaya($Conn, $SessionIdAkses, 'kElsUUFAQ6rCScZuAiK');

    if ($IjinAksesSaya !== "Ada") {
        include "_Page/Error/NoAccess.php";
    } else {
?>
    <div class="pagetitle">
        <h1>
            <a href=""><i class="bi bi-person-badge"></i> Tenaga Kesehatan</a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.php">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Tenaga Kesehatan</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">

        <div class="row g-3 MobileCard-grid">
            <!-- Card Aksi -->
            <div class="col-md-12">
                <div class="card card-tambah h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                        <div class="aksi-resep mb-3">
                            <button type="button" class="icon-tambah" id="tombol_tambah" data-bs-toggle="modal" data-bs-target="#ModalTambah" title="Tambah Nakes">
                                <i class="bi bi-plus-lg"></i>
                                <span class="visually-hidden">Tambah Nakes</span>
                            </button>
                            <button type="button" class="icon-tambah" id="tombol_cari" data-bs-toggle="modal" data-bs-target="#ModalFilter" title="Cari Nakes">
                                <i class="bi bi-search"></i>
                                <span class="visually-hidden">Cari Nakes</span>
                            </button>
                        </div>
                        <h6 class="mb-1">Kelola Nakes</h6>
                        <small class="text-muted">
                            Tambah Tenaga Kesehatan baru Atau Cari Tenaga Kesehatan
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 mb-4" id="list_nakes">
            <!-- Daftar Poliklinik AKan Tampil Dengan AJAX -->
            
        </div>

        <div class="row mt-4">
            <div class="col-12 mt-4 text-center">
                <button type="button" class="btn btn-lg btn-info btn-floating" id="prev_button">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-lg btn-info btn-floating" id="next_button">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>

    </section>
<?php } ?>

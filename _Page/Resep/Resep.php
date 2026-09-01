<?php
    // Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya = IjinAksesSaya($Conn, $SessionIdAkses, 'G9ayRXZeV0SIdRsfund');

    if ($IjinAksesSaya !== "Ada") {
        include "_Page/Error/NoAccess.php";
    } else {
?>
    <div class="pagetitle">
        <h1>
            <a href=""><i class="bi bi-receipt"></i> Resep</a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.php">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Resep</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">

        <div class="row g-3 resep-grid">

            <!-- Card Aksi -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card card-tambah-resep h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">

                        <div class="aksi-resep mb-3">
                            <button type="button" class="icon-tambah-resep" id="tombol_tambah" title="Tambah Resep">
                                <i class="bi bi-plus-lg"></i>
                                <span class="visually-hidden">Tambah Resep</span>
                            </button>

                            <button type="button" class="icon-tambah-resep" id="tombol_cari" title="Cari Resep">
                                <i class="bi bi-search"></i>
                                <span class="visually-hidden">Cari Resep</span>
                            </button>
                        </div>

                        <h6 class="mb-1">Kelola Resep</h6>

                        <small class="text-muted">
                            Tambah resep baru atau cari resep
                        </small>

                    </div>
                </div>
            </div>

            <!-- Daftar Resep AJAX -->
            <div id="list_resep"></div>

        </div>

    </section>
<?php } ?>

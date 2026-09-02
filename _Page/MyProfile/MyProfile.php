<?php
    $strtotime1                 = strtotime($SessionDatetimeDaftar);
    $strtotime2                 = strtotime($SessionDatetimeUpdate);
    $SessionWaktuDaftarDatetime = date('d/m/Y H:i T',$strtotime1);
    $SessionWaktuUpdateDatetime = date('d/m/Y H:i T',$strtotime2);
?>
<div class="pagetitle">
    <h1>
        <a href="">
            <i class="bi bi-person-circle"></i> Profil Saya</a>
        </a>
    </h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active"> Profil Saya</li>
        </ol>
    </nav>
</div>
<section class="section dashboard">
    <div class="row mb-3">
        <div class="col-md-12">
            <?php
                echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">';
                echo '  <small>';
                echo '      Berikut ini adalah halaman profil yang digunakan untuk mengelola informasi akses anda.';
                echo '      Pada halaman ini anda bisa melakukan perubahan data akses (Nama, Email, Password dan Foto Profile).';
                echo '      Pada bagian kolom izin akses menunjukan informasi fitur apa saja yang bisa anda gunakan pada aplikasi ini. ';
                echo '      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '  </small>';
                echo '</div>';
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header text-center">
                    <b class="card-title">
                        <i class="bi bi-info-circle"></i> Informasi Pengguna
                    </b>
                </div>
                <div class="card-body">
                    <div class="row mb-3 mt-4 border-1 border-bottom">
                        <div class="col col-md-12 text-center mb-4">
                            <img src="image_proxy.php?dir=User&filename=<?php echo "$SessionGambar"; ?>" alt="" width="70%" class="rounded-circle">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-6">
                            Nama Lengkap
                        </div>
                        <div class="col col-md-6">
                            <small class="text-muted">
                                <?php echo "$SessionNama"; ?>
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-6">Kontak</div>
                        <div class="col col-md-6">
                            <small class="text-muted">
                                <?php echo "$SessionKontakAkses"; ?>
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-6">Email</div>
                        <div class="col col-md-6">
                            <small class="text-muted">
                                <?php echo "$SessionEmailAkses"; ?>
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-6">Level Akses</div>
                        <div class="col col-md-6">
                            <small class="text-muted">
                                <?php echo "$SessionLevelAkses"; ?>
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-6">Waktu Daftar</div>
                        <div class="col col-md-6">
                            <small class="text-muted">
                                <?php echo "$SessionWaktuDaftarDatetime"; ?>
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-6 mb-3">Update</div>
                        <div class="col col-md-6 mb-3">
                            <small class="text-muted">
                               <?php echo "$SessionWaktuUpdateDatetime"; ?>
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <a href="javascript:void(0);" class="text-dark"  data-bs-toggle="modal" data-bs-target="#ModalUbahIdentitasProfil">
                                        <i class="bi bi-pencil me-1 text-primary"></i> 
                                        <small class="credit">Ubah Identitias</small>
                                    </a>
                                </li>
                                <li class="list-group-item">
                                    <a href="javascript:void(0);" class="text-dark" data-bs-toggle="modal" data-bs-target="#ModalUbahFotoProfil">
                                        <i class="bi bi-image me-1 text-primary"></i> 
                                        <small class="credit">Ubah Foto Profil</small>
                                    </a>
                                </li>
                                <li class="list-group-item">
                                    <a href="javascript:void(0);" class="text-dark" data-bs-toggle="modal" data-bs-target="#ModalUbahPasswordProfil">
                                        <i class="bi bi-key me-1 text-primary"></i> 
                                        <small class="credit">Ubah Password</small>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <b class="card-title">
                        <i class="bi bi-list-check"></i> Izin Akses
                    </b>
                </div>
                <div class="card-body">
                    <div class="row mt-3 mb-3">
                        <div class="col-md-12">
                            <?php
                                //Tampilkan Kategori Ijin Akses
                                $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akses_fitur"));
                                if(empty($jml_data)){
                                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                                    echo '  <small>Belum ada data fitur aplikasi, silahkan tambahkan fitur aplikasi terlebih dulu</small>';
                                    echo '  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                                    echo '</div>';
                                }else{
                                    $QryKategoriFitur = mysqli_query($Conn, "SELECT DISTINCT kategori FROM akses_fitur ORDER BY kategori ASC");
                                    $kategori_list = [];
                                    while ($DataKategori = mysqli_fetch_array($QryKategoriFitur)) {
                                        $kategori_list[] = $DataKategori['kategori'];
                                    }
                                    
                                    // Tampilkan seluruh izin dalam accordion berdasarkan kategori.
                                    echo '<div class="accordion accordion-flush rounded-3 shadow-sm" id="IzinAksesAccordion">';
                                    foreach ($kategori_list as $idx => $kategori) {
                                        $kategori_id = 'IzinKategori' . ($idx + 1);
                                        $kategori_tampil = htmlspecialchars($kategori, ENT_QUOTES, 'UTF-8');
                                        $collapse_class = ($idx === 0) ? 'show' : '';
                                        $button_class = ($idx === 0) ? '' : 'collapsed';
                                        $expanded = ($idx === 0) ? 'true' : 'false';

                                        echo '<div class="accordion-item">';
                                        echo '  <h2 class="accordion-header" id="' . $kategori_id . 'Header">';
                                        echo '    <button class="accordion-button ' . $button_class . ' fw-semibold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#' . $kategori_id . '" aria-expanded="' . $expanded . '" aria-controls="' . $kategori_id . '">';
                                        echo '      <i class="bi bi-folder2-open me-2 text-primary"></i>' . $kategori_tampil;
                                        echo '    </button>';
                                        echo '  </h2>';
                                        echo '  <div id="' . $kategori_id . '" class="accordion-collapse collapse ' . $collapse_class . '" aria-labelledby="' . $kategori_id . 'Header" data-bs-parent="#IzinAksesAccordion">';
                                        echo '    <div class="accordion-body p-2">';
                                        $QryFitur = mysqli_query($Conn, "SELECT * FROM akses_fitur WHERE kategori='$kategori' ORDER BY nama ASC");
                                        $jml_fitur = mysqli_num_rows($QryFitur);
                                        
                                        echo '<div class="list-group">';
                                        $no_fitur = 1;
                                        while ($DataFitur = mysqli_fetch_array($QryFitur)) {
                                            $id_akses_fitur = $DataFitur['id_akses_fitur'];
                                            $nama = htmlspecialchars($DataFitur['nama'], ENT_QUOTES, 'UTF-8');
                                            $keterangan = htmlspecialchars($DataFitur['keterangan'], ENT_QUOTES, 'UTF-8');
                                            $kode = $DataFitur['kode'];
                                            
                                            $Validasi = IjinAksesSaya($Conn, $SessionIdAkses, $kode);
                                            $badge_class = ($Validasi == "Ada") ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                                            $icon_class = ($Validasi == "Ada") ? 'bi-check-circle' : 'bi-x-circle';
                                            $status_text = ($Validasi == "Ada") ? 'Diizinkan' : 'Tidak Diizinkan';
                                            
                                            echo '<div class="list-group-item px-2 py-2">';
                                            echo '  <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">';
                                            echo '    <div class="flex-grow-1 min-w-0">';
                                            echo '      <div class="fw-semibold small">' . $no_fitur . '. ' . $nama . '</div>';
                                            echo '      <small class="text-muted d-block text-break">' . $keterangan . '</small>';
                                            echo '    </div>';
                                            echo '    <span class="badge ' . $badge_class . ' rounded-pill flex-shrink-0 align-self-start align-self-sm-center">';
                                            echo '      <i class="bi ' . $icon_class . ' me-1"></i>' . $status_text;
                                            echo '    </span>';
                                            echo '  </div>';
                                            echo '</div>';
                                            
                                            $no_fitur++;
                                        }
                                        echo '</div>';
                                        
                                        if ($jml_fitur === 0) {
                                            echo '<div class="alert alert-info py-2 px-3 mt-2 mb-0">';
                                            echo '  <small>Tidak ada fitur dalam kategori ini</small>';
                                            echo '</div>';
                                        }
                                        
                                        echo '    </div>';
                                        echo '  </div>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

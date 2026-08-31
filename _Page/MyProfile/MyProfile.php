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
                                    
                                    // Tab Navigation
                                    echo '<ul class="nav nav-tabs mb-3" id="izinAksesTab" role="tablist">';
                                    foreach ($kategori_list as $idx => $kategori) {
                                        $tab_id = 'kategori' . ($idx + 1);
                                        $active_class = ($idx === 0) ? 'active' : '';
                                        echo '<li class="nav-item" role="presentation">';
                                        echo '  <button class="nav-link ' . $active_class . '" id="' . $tab_id . '-tab" data-bs-toggle="tab" data-bs-target="#' . $tab_id . '" type="button" role="tab" aria-controls="' . $tab_id . '" aria-selected="' . ($idx === 0 ? 'true' : 'false') . '">';
                                        echo '    <i class="bi bi-folder"></i> ' . htmlspecialchars($kategori, ENT_QUOTES, 'UTF-8');
                                        echo '  </button>';
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                    
                                    // Tab Content
                                    echo '<div class="tab-content" id="izinAksesTabContent">';
                                    foreach ($kategori_list as $idx => $kategori) {
                                        $tab_id = 'kategori' . ($idx + 1);
                                        $active_class = ($idx === 0) ? 'show active' : '';
                                        echo '<div class="tab-pane fade ' . $active_class . '" id="' . $tab_id . '" role="tabpanel" aria-labelledby="' . $tab_id . '-tab">';
                                        
                                        $QryFitur = mysqli_query($Conn, "SELECT * FROM akses_fitur WHERE kategori='$kategori' ORDER BY nama ASC");
                                        $jml_fitur = mysqli_num_rows($QryFitur);
                                        
                                        echo '<ul class="list-group list-group-flush">';
                                        $no_fitur = 1;
                                        while ($DataFitur = mysqli_fetch_array($QryFitur)) {
                                            $id_akses_fitur = $DataFitur['id_akses_fitur'];
                                            $nama = htmlspecialchars($DataFitur['nama'], ENT_QUOTES, 'UTF-8');
                                            $keterangan = htmlspecialchars($DataFitur['keterangan'], ENT_QUOTES, 'UTF-8');
                                            $kode = $DataFitur['kode'];
                                            
                                            $Validasi = IjinAksesSaya($Conn, $SessionIdAkses, $kode);
                                            $badge_class = ($Validasi == "Ada") ? 'bg-success' : 'bg-danger';
                                            $icon_class = ($Validasi == "Ada") ? 'bi-check-circle text-success' : 'bi-x-circle text-danger';
                                            $status_text = ($Validasi == "Ada") ? 'Diizinkan' : 'Tidak Diizinkan';
                                            
                                            echo '<li class="list-group-item d-flex justify-content-between align-items-start">';
                                            echo '  <div class="flex-grow-1">';
                                            echo '    <div class="fw-bold">';
                                            echo '      <small>' . $no_fitur . '. ' . $nama . '</small>';
                                            echo '    </div>';
                                            echo '    <small class="text-muted">' . $keterangan . '</small>';
                                            echo '  </div>';
                                            echo '  <div class="text-end ms-2">';
                                            echo '    <span class="badge ' . $badge_class . '">';
                                            echo '      <i class="bi ' . $icon_class . '"></i> ' . $status_text;
                                            echo '    </span>';
                                            echo '  </div>';
                                            echo '</li>';
                                            
                                            $no_fitur++;
                                        }
                                        echo '</ul>';
                                        
                                        if ($jml_fitur === 0) {
                                            echo '<div class="alert alert-info alert-sm mt-3 mb-0">';
                                            echo '  <small>Tidak ada fitur dalam kategori ini</small>';
                                            echo '</div>';
                                        }
                                        
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

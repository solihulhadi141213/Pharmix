<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'qLECdqLVgBMjV0BXHUC');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
?>
    <div class="pagetitle">
        <h1>
            <a href="">
                <i class="bi bi-gear"></i> Pengaturan Umum</a>
            </a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active"> Pengaturan Umum</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        
        <div class="row align-items-stretch">
            <div class="col-md-6">
                <form action="javascript:void(0);" id="ProsesSettingGeneral">
                    <div class="card h-100">
                        <div class="card-header">
                            <b class="card-title">A. Informasi Umum</b>
                        </div>
                        <div class="card-body">
                            
                            <!-- Nama Perusahaan -->
                            <div class="row mb-3 mt-4">
                                <div class="col-md-3">
                                    <label for="title_page">
                                        <small>Perusahaan</small>
                                    </label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" name="title_page" id="title_page" class="form-control" placeholder="Koperasi Andalan Jaya" value="<?php echo "$title_page"; ?>">
                                    <small>
                                        <small class="text text-grayish">Nama Perusahaan Maksimal 100 karakter</small>
                                    </small>
                                </div>
                            </div>

                            <!-- Kata Kunci (Keyword) -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="kata_kunci">
                                        <small>Kata Kunci</small>
                                    </label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" name="kata_kunci" id="kata_kunci" class="form-control" value="<?php echo "$kata_kunci"; ?>">
                                    <small>
                                        <small class="text text-grayish">(Contoh: keyword1, keyword2, keyword3)</small>
                                    </small>
                                </div>
                            </div>

                            <!-- Deskripsi -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="deskripsi">Deskripsi</label>
                                </div>
                                <div class="col-md-9">
                                    <textarea name="deskripsi" id="deskripsi" cols="30" rows="3" class="form-control"><?php echo "$deskripsi"; ?></textarea>
                                    <small>
                                        <small class="text text-grayish">Jelaskan tentang perusahaan anda</small>
                                    </small>
                                </div>
                            </div>

                            <!-- Alamat Perusahaan -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="alamat_bisnis">
                                        <small>Alamat</small>
                                    </label>
                                </div>
                                <div class="col-md-9">
                                    <textarea name="alamat_bisnis" id="alamat_bisnis" cols="30" rows="3" class="form-control"><?php echo "$alamat_bisnis"; ?></textarea>
                                </div>
                            </div>

                            <!-- Email Perusahaan -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="email">
                                        <small>Email</small>
                                    </label>
                                </div>
                                <div class="col-md-9">
                                    <input type="email" name="email_bisnis" id="email_bisnis" class="form-control" placeholder="email@domain.com" value="<?php echo "$email_bisnis"; ?>">
                                </div>
                            </div>

                            <!-- Kontak / Telepon -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="telepon_bisnis">
                                        <small>Kontak</small>
                                    </label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" name="telepon_bisnis" id="telepon_bisnis" class="form-control" placeholder="+62" value="<?php echo "$telepon_bisnis"; ?>">
                                </div>
                            </div>

                            <!-- Domain / URL -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="base_url">
                                        <small>Domain / URL</small>
                                    </label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" name="base_url" id="base_url" class="form-control" placeholder="https://" value="<?php echo "$base_url"; ?>">
                                </div>
                            </div>

                            <!-- Author Aplikasi -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="author">
                                        <small>Author</small>
                                    </label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" name="author" id="author" class="form-control" value="<?php echo "$AuthorAplikasi"; ?>">
                                </div>
                            </div>

                            <!-- Notifikasi -->
                            <div class="row mt-3">
                                <div class="col-md-12 text-right" id="NotifikasiSimpanSettingGeneral">
                                    
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-md btn-primary w-100" id="ButtonSimpanSettingGeneral">
                                <i class="bi bi-save"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-md-3">
                <form action="javascript:void(0);" id="ProsesUpdateFavicon">
                    <div class="card h-100">
                        <div class="card-header">
                            <b class="card-title">B. Favicon</b>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3 mt-4">
                                <div class="col-12 mb-3 text-center">
                                    <div class="d-flex align-items-center justify-content-center" style="height:220px;" id="FaviconPreview">
                                        <?php
                                            if(!empty($favicon)){
                                                echo '<img src="assets/img/'.$favicon.'" alt="Favicon" class="img-fluid" style="max-height:200px;">';
                                            }else{
                                                echo '<small class="text-danger">No Image Favicon</small>';
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-2">
                                <div class="col-12">
                                    <label for="favicon">
                                        <small>File Favicon</small>
                                    </label>
                                    <input type="file" name="favicon" id="favicon" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12" id="NotifikasiUpdateFavicon">
                                    <!-- Notifikasi Update Favicon -->
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-md btn-primary w-100" id="ButtonUpdateFavicon">
                                <i class="bi bi-upload"></i> Upload & Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-md-3">
                <form action="javascript:void(0);" id="ProsesUpdateLogo">
                    <div class="card h-100">
                        <div class="card-header">
                            <b class="card-title">C. Logo</b>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3 mt-4">
                                <div class="col-12 mb-3 text-center">
                                    <div class="d-flex align-items-center justify-content-center" style="height:220px;" id="LogoPreview">
                                        <?php
                                            if(!empty($logo)){
                                                echo '<img src="assets/img/'.$logo.'" alt="Logo" class="img-fluid" style="max-height:200px;">';
                                            }else{
                                                echo '<small class="text-danger">No Image Logo</small>';
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-2">
                                <div class="col-12">
                                    <label for="logo">
                                        <small>File Logo</small>
                                    </label>
                                    <input type="file" name="logo" id="logo" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12" id="NotifikasiUpdateLogo">
                                    <!-- Notifikasi Update Logo -->
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-md btn-primary w-100" id="ButtonUpdateLogo">
                                <i class="bi bi-save"></i> Upload & Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>

    </section>
<?php } ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php
            //Koneksi
            session_start();
            include "_Config/Connection.php";
            include "_Config/GlobalFunction.php";
            include "_Config/SettingGeneral.php";
            include "_Partial/Head.php";
        ?>
    </head>
    <body>
        <main class="landing_background login-page">
            <div class="container">
                <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="container login-shell">
                        <div class="row justify-content-center g-0">
                            <div class="col-lg-5 d-none d-lg-flex login-illustration-panel">
                                <div class="login-illustration-inner">
                                    <div class="login-illustration-badge">
                                        <i class="bi bi-shield-lock"></i>
                                        <span>Protected Workspace</span>
                                    </div>
                                    <h2>Pharmix</h2>
                                    <p>Pengelolaan apotek yang modern, rapi, dan siap mendukung alur kerja profesional.</p>
                                    <div class="login-illustration-card">
                                        <div class="login-illustration-orbit login-orbit-1"></div>
                                        <div class="login-illustration-orbit login-orbit-2"></div>
                                        <div class="login-illustration-orbit login-orbit-3"></div>
                                        <div class="login-illustration-logo">
                                            <img src="assets/img/<?php echo $logo;?>" alt="<?php echo $title_page;?>">
                                        </div>
                                        <div class="login-illustration-stats">
                                            <div>
                                                <strong><i class="bi bi-box-seam"></i> Barang</strong>
                                                <span>Master data & stok</span>
                                            </div>
                                            <div>
                                                <strong><i class="bi bi-cart-check"></i> Transaksi</strong>
                                                <span>Jual, beli, dan rekap</span>
                                            </div>
                                            <div>
                                                <strong><i class="bi bi-graph-up-arrow"></i> Laporan</strong>
                                                <span>Keuangan dan aktivitas</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5 col-md-8 col-sm-11 d-flex flex-column align-items-center justify-content-center login-form-column">
                                <div class="login-brand">
                                    <div class="login-brand-badge">
                                        <i class="bi bi-shield-check"></i>
                                        <span>Secure Access</span>
                                    </div>
                                    <img src="assets/img/<?php echo $logo;?>" alt="<?php echo $title_page;?>" class="login-brand-logo">
                                    <h1 class="login-brand-title"><?php echo $title_page;?></h1>
                                    <p class="login-brand-text">Masuk untuk mengelola operasional apotek dengan aman dan terstruktur.</p>
                                    <div class="login-highlights">
                                        <span><i class="bi bi-box-seam"></i> Stok</span>
                                        <span><i class="bi bi-cash-coin"></i> Transaksi</span>
                                        <span><i class="bi bi-graph-up-arrow"></i> Laporan</span>
                                    </div>
                                </div>
                                <form action="javascript:void(0);" class="row g-3 login-form" id="ProsesLogin" autocomplete="off" autocapitalize="off" spellcheck="false">
                                    <div class="login-form-inner">
                                            <div class="pb-2 login-card-header">
                                                <h5 class="card-title text-center pb-0 fs-4">Login Ke Akun Anda</h5>
                                                <p class="text-center small">Masukan email, password, dan captcha untuk melanjutkan.</p>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <label for="email" class="form-label">Email</label>
                                                    <div class="input-group has-validation login-input-group">
                                                        <span class="input-group-text login-input-icon">
                                                            <i class="bi bi-envelope"></i>
                                                        </span>
                                                        <input type="email" name="email" class="form-control login-control" id="email" placeholder="nama@email.com" autocomplete="off" autocapitalize="off" spellcheck="false" required>
                                                        <div class="invalid-feedback">Please enter your username.</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <label for="password" class="form-label">Password</label>
                                                    <div class="login-password-wrap">
                                                        <div class="input-group has-validation login-input-group">
                                                            <span class="input-group-text login-input-icon">
                                                                <i class="bi bi-key"></i>
                                                            </span>
                                                            <input type="password" name="password" class="form-control login-control login-password-input" id="password" placeholder="Masukkan password" autocomplete="new-password" autocapitalize="off" spellcheck="false" required>
                                                            <button type="button" class="btn login-password-toggle" id="TampilkanPassword2" aria-label="Tampilkan password">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                            <div class="invalid-feedback">Please enter your password!</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                                
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <label class="form-label">Captcha</label>
                                                    <div class="login-captcha-wrap">
                                                        <button type="button" class="btn btn-floating login-captcha-reload" onclick="reloadCaptcha()" title="Muat ulang kode captcha">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                        <img src="_Page/Login/Captcha.php" id="captchaImage" alt="No Image" class="login-captcha-image">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <small class="login-helper-text">
                                                        Masukan karakter <i>Captcha</i>
                                                    </small>
                                                    <div class="input-group has-validation login-input-group">
                                                        <span class="input-group-text login-input-icon">
                                                            <i class="bi bi-shield-exclamation"></i>
                                                        </span>
                                                        <input type="text" name="captcha" class="form-control login-control" id="captcha" placeholder="Ketikan captcha di atas" autocomplete="off" autocapitalize="off" spellcheck="false" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-12" id="NotifikasiLogin"></div>
                                                <div class="col-12">
                                                    <button class="btn btn-lg btn-primary w-100 login-submit-btn" id="TombolLogin" type="submit">
                                                        <i class="bi bi-box-arrow-in-right"></i>
                                                        <span>Login</span>
                                                    </button>
                                                </div>
                                            </div>
                                    </div>
                                </form>
                                <div class="credits text-center">
                                    <small>
                                        <div class="login-footer-note text-dark">
                                            Aplikasi apotek untuk operasional, penjualan, pembelian, dan laporan.
                                        </div>
                                        <div class="copyright text-dark">
                                            &copy; Copyright <strong><span><?php echo "$title_page"; ?></span></strong>. All Rights Reserved 2023
                                        </div>
                                        <div class="credits text-dark">
                                            Designed by <span class="text text-decoration-underline"><?php echo "$AuthorAplikasi"; ?></span>
                                        </div>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
    </main>
        <?php
            include "_Partial/FooterJs.php";
        ?>
        <script>
            //Fungsi Reload Captcha
            function reloadCaptcha() {
                document.getElementById('captchaImage').src = '_Page/Login/Captcha.php?' + new Date().getTime();
            }

            // Jalankan reloadCaptcha setiap 1 menit (60.000 ms)
            setInterval(reloadCaptcha, 60000); // 60000 ms = 1 menit
            
            //Kondisi saat tampilkan password
            $('#TampilkanPassword2').click(function(){
                const isVisible = $('#password').attr('type') === 'text';
                $('#password').attr('type', isVisible ? 'password' : 'text');
                $('#TampilkanPassword2 i').toggleClass('bi-eye bi-eye-slash');
                $('#TampilkanPassword2').attr('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
            });

            //Submit Login
            $('#ProsesLogin').submit(function(){
                var ProsesLogin = $('#ProsesLogin').serialize();
                var Loading='<div class="spinner-border text-info" role="status"><span class="visually-hidden">Loading...</span></div>';
                $('#TombolLogin').html(Loading);
                $.ajax({
                    type 	    : 'POST',
                    url 	    : '_Page/Login/ProsesLogin.php',
                    data 	    :  ProsesLogin,
                    dataType    : 'json',
                    success     : function(response){
                        $('#TombolLogin').html('<i class="bi bi-box-arrow-in-right"></i><span>Login</span>');
                        if (response.status === 'success') {
                            // Redirect jika login berhasil
                            window.location.href = 'index.php';
                        } else {
                            // Tampilkan notifikasi error jika gagal
                            $('#NotifikasiLogin').html('<div class="alert alert-danger">' + response.message + '</div>');
                        }
                    }
                });
            });

        </script>
    </body>
</html>

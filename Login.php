<!DOCTYPE html>
<html lang="id">
    <head>
        <?php
            // Koneksi dan konfigurasi halaman.
            session_start();
            include "_Config/Connection.php";
            include "_Config/GlobalFunction.php";
            include "_Config/SettingGeneral.php";
            include "_Partial/Head.php";
        ?>
        <style>
            .login-page {
                --login-blue: #0876c9;
                --login-panel: #aec9e8;
                min-height: 100vh;
                overflow: hidden;
                position: relative;
                background: var(--login-panel);
                color: #18283d;
            }
            .login-page::before { display: none; }
            .login-page > .container { max-width: none; padding: 0; }
            .login-page .login-section { min-height: 100vh; padding: 0 !important; }
            .login-page .login-shell { width: 100%; max-width: none; margin: 0; padding: 0; }
            .login-page .login-layout { min-height: 100vh; }
            .login-page .login-welcome {
                position: relative;
                z-index: 1;
                min-height: 100vh;
                padding: clamp(3rem, 7vw, 7rem);
                display: flex;
                flex-direction: column;
                justify-content: center;
                color: #fff;
                background-image: url('assets/img/Left-Background.png');
                background-position: center;
                background-size: cover;
                background-repeat: no-repeat;
            }
            .login-page .login-mobile-brand { display: none; }
            .login-page .welcome-eyebrow { margin: 0 0 2.1rem; font-size: clamp(1.25rem, 2vw, 2rem); font-weight: 600; }
            .login-page .welcome-title { margin: 0 0 1.7rem; color: #fff; font-size: clamp(3.2rem, 6vw, 5.4rem); font-weight: 800; letter-spacing: -.04em; text-shadow: 0 5px 2px rgba(0,0,0,.32); }
            .login-page .welcome-copy { max-width: 31rem; margin: 0; color: rgba(255,255,255,.92); font-size: clamp(1.05rem, 1.4vw, 1.4rem); line-height: 1.45; }
            .login-page .login-form-column {
                min-height: 100vh;
                padding: clamp(2rem, 4vw, 4rem) clamp(1.5rem, 5vw, 5rem);
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                background: transparent;
                border-radius: 0;
                box-shadow: none;
            }
            .login-page .login-brand { width: min(100%, 31rem); margin: 0 auto 2rem; }
            .login-page .login-brand-heading { display: flex; align-items: center; justify-content: center; gap: 1.1rem; }
            .login-page .login-brand-logo { width: 6rem; height: 6rem; margin: 0; filter: drop-shadow(0 5px 4px rgba(0,0,0,.16)); }
            .login-page .login-brand-title { color: #172a3e; font-size: 2.5rem; font-weight: 700; letter-spacing: 0; }
            .login-page .login-brand-version { margin: .15rem 0 0; font-size: 1.45rem; line-height: 1.2; }
            .login-page .login-brand-text { max-width: 28rem; margin: 1.5rem 0 0; color: #3f5063; font-size: 1.1rem; line-height: 1.35; }
            .login-page .login-form { width: min(100%, 31rem); }
            .login-page .login-form-inner { padding: 0; }
            .login-page .login-form .row.mb-3 { margin-bottom: 1.15rem !important; }
            .login-page .form-label { margin-bottom: .35rem; color: #34485d; font-size: 1rem; }
            .login-page .login-input-group { min-height: 3rem; border: 0; border-radius: .65rem; background: rgba(255,255,255,.48); box-shadow: none; }
            .login-page .login-input-icon { order: 2; width: 3.25rem; padding: 0; border: 0; background: transparent; color: #647386; display: inline-flex; align-items: center; justify-content: center; align-self: stretch; font-size: 1.3rem; line-height: 1; }
            .login-page .login-control { min-height: 3rem; padding: .65rem 1rem; border-radius: .65rem; background: rgba(255,255,255,.48); }
            .login-page .login-input-group:focus-within { outline: 2px solid #80b8f3; background: #f8fbff; box-shadow: 0 2px 5px rgba(27,73,123,.12); }
            .login-page .login-control:focus { background: #f8fbff; }
            .login-page .login-password-input { border-radius: .65rem 0 0 .65rem; }
            .login-page .login-password-toggle { order: 2; width: 3.25rem; padding: 0; border: 0; background: transparent; color: #647386; display: inline-flex; align-items: center; justify-content: center; align-self: stretch; font-size: 1.3rem; line-height: 1; }
            .login-page .login-password-toggle:hover, .login-page .login-password-toggle:focus { background: transparent; color: #34485d; box-shadow: none; }
            .login-page .login-password-wrap .login-input-icon { display: none; }
            .login-page .login-captcha-wrap { border: 0; border-radius: .65rem; box-shadow: none; }
            .login-page .login-captcha-image { min-height: 6rem; object-fit: cover; }
            .login-page .login-captcha-reload { top: .35rem; right: .35rem; width: 2.4rem; height: 2.4rem; min-width: 2.4rem; min-height: 2.4rem; border: 0; background: rgba(255,255,255,.85); color: #526174; box-shadow: none; display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem; line-height: 1; }
            .login-page .login-helper-text { margin: 0 0 .2rem; color: #34485d; }
            .login-page .login-submit-btn, .login-page .login-google-btn { min-height: 3.35rem; border-radius: 999px; font-size: 1rem; font-weight: 500; }
            .login-page .login-submit-btn { padding: .75rem 1rem; background: var(--login-blue); border-color: var(--login-blue); box-shadow: none; }
            .login-page .login-submit-btn:hover { transform: none; background: #0668b3; box-shadow: 0 5px 12px rgba(8,118,201,.2); }
            .login-page .login-submit-btn i { display: none; }
            .login-page .login-google-btn { border: 1px solid var(--login-blue); background: transparent; color: #13243a; }
            .login-page .login-google-btn:hover { background: rgba(255,255,255,.25); }
            .login-page .login-google-btn img { width: 1.15rem; height: 1.15rem; margin-right: .5rem; object-fit: contain; }
            .login-page .login-footer { width: min(100%, 36rem); margin-top: auto; padding-top: 2rem; }
            .login-page .login-footer-note { font-size: .82rem; }
            .login-page .credits .text-dark, .login-page .login-footer-note.text-dark { color: #172a3e !important; }
            @media (max-width: 991.98px) {
                .login-page .login-layout { min-height: 100vh; flex-direction: column; }
                .login-page .login-welcome {
                    --mobile-welcome-height: clamp(14rem, 58vw, 25rem);
                    display: flex !important;
                    width: 100%;
                    height: var(--mobile-welcome-height);
                    min-height: 0;
                    padding: 1.25rem 1.5rem;
                    overflow: hidden;
                    align-items: center;
                    justify-content: flex-start;
                    background: var(--login-panel);
                }
                .login-page .login-welcome > * { display: none; }
                .login-page .login-mobile-brand {
                    position: relative;
                    z-index: 1;
                    width: 100%;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    text-align: center;
                }
                .login-page .login-mobile-brand .login-brand-heading { gap: .85rem; }
                .login-page .login-mobile-brand .login-brand-logo { width: clamp(4.25rem, 15vw, 6rem); height: clamp(4.25rem, 15vw, 6rem); }
                .login-page .login-mobile-brand .login-brand-title { margin: 0; color: #fff; font-size: clamp(1.75rem, 6vw, 2.5rem); }
                .login-page .login-mobile-brand .login-brand-version { margin: .15rem 0 0; color: #fff; font-size: clamp(1rem, 3.5vw, 1.35rem); }
                .login-page .login-mobile-brand .login-brand-text { max-width: 30rem; margin: 1rem 0 0; color: rgba(255,255,255,.96); font-size: clamp(.9rem, 3.5vw, 1.1rem); line-height: 1.3; }
                .login-page .login-welcome::before {
                    content: "";
                    position: absolute;
                    z-index: -1;
                    top: 50%;
                    left: 50%;
                    width: var(--mobile-welcome-height);
                    height: 100vw;
                    background: url('assets/img/Left-Background.png') center / 100% 100% no-repeat;
                    transform: translate(-50%, -50%) rotate(90deg);
                    pointer-events: none;
                }
                .login-page .welcome-eyebrow { margin-bottom: 1rem; }
                .login-page .welcome-title { margin-bottom: .8rem; font-size: clamp(2.4rem, 8vw, 3.5rem); }
                .login-page .welcome-copy { max-width: 32rem; font-size: 1rem; }
                .login-page .login-form-column { width: 100%; min-height: auto; padding: 2rem clamp(1.25rem, 6vw, 3rem); }
                .login-page .login-form-column > .login-brand { display: none; }
                .login-page .login-footer { margin-top: 1rem; }
            }
            @media (max-width: 575.98px) {
                .login-page .login-brand-logo { width: 4.5rem; height: 4.5rem; }
                .login-page .login-brand-title { font-size: 2rem; }
                .login-page .login-brand-version { font-size: 1.15rem; }
                .login-page .login-brand-text { font-size: .98rem; }
                .login-page .login-footer { padding-top: 1.5rem; }
            }
        </style>
    </head>
    <body>
        <main class="login-page">
            <div class="container">
                <section class="section register login-section d-flex flex-column align-items-center justify-content-center">
                    <div class="container login-shell">
                        <div class="row g-0 login-layout">
                            <div class="col-lg-6 login-welcome">
                                <p class="welcome-eyebrow">Selamat Datang</p>
                                <h2 class="welcome-title">Pharmix V1</h2>
                                <p class="welcome-copy">Pengelolaan apotek yang modern, rapi, dan siap mendukung alur kerja profesional.</p>
                                <div class="login-mobile-brand">
                                    <div class="login-brand-heading">
                                        <img src="assets/img/<?php echo $logo;?>" alt="Logo <?php echo $title_page;?>" class="login-brand-logo">
                                        <div class="text-start">
                                            <h1 class="login-brand-title"><?php echo $title_page;?></h1>
                                            <p class="login-brand-version">V01.00</p>
                                        </div>
                                    </div>
                                    <p class="login-brand-text">Silahkan login dengan memasukan alamat email dan password yang benar</p>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-8 col-sm-11 mx-auto login-form-column">
                                <div class="login-brand">
                                    <div class="login-brand-heading">
                                        <img src="assets/img/<?php echo $logo;?>" alt="Logo <?php echo $title_page;?>" class="login-brand-logo">
                                        <div class="text-start">
                                            <h1 class="login-brand-title"><?php echo $title_page;?></h1>
                                            <p class="login-brand-version">V01.00</p>
                                        </div>
                                    </div>
                                    <p class="login-brand-text">Silahkan login dengan memasukan alamat email dan password yang benar</p>
                                </div>
                                <form action="javascript:void(0);" class="row g-3 login-form" id="ProsesLogin" autocomplete="off" autocapitalize="off" spellcheck="false">
                                    <div class="login-form-inner">
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <label for="email" class="form-label">Email</label>
                                                <div class="input-group has-validation login-input-group">
                                                    <input type="email" name="email" class="form-control login-control" id="email" autocomplete="off" autocapitalize="off" spellcheck="false" autofocus required>
                                                    <span class="login-input-icon"><i class="bi bi-envelope"></i></span>
                                                    <div class="invalid-feedback">Masukkan alamat email yang valid.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <label for="password" class="form-label">Password</label>
                                                <div class="login-password-wrap">
                                                    <div class="input-group has-validation login-input-group">
                                                        <input type="password" name="password" class="form-control login-control login-password-input" id="password" autocomplete="new-password" autocapitalize="off" spellcheck="false" required>
                                                        <button type="button" class="btn login-password-toggle" id="TampilkanPassword2" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
                                                        <div class="invalid-feedback">Masukkan password.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <label class="form-label">Captcha</label>
                                                <div class="login-captcha-wrap">
                                                    <button type="button" class="btn btn-floating login-captcha-reload" onclick="reloadCaptcha()" title="Muat ulang kode captcha" aria-label="Muat ulang captcha"><i class="bi bi-arrow-repeat"></i></button>
                                                    <img src="_Page/Login/Captcha.php" id="captchaImage" alt="Kode captcha" class="login-captcha-image">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <label for="captcha" class="form-label">Input Captcha</label>
                                                <div class="input-group has-validation login-input-group">
                                                    <input type="text" name="captcha" class="form-control login-control" id="captcha" autocomplete="off" autocapitalize="off" spellcheck="false" required>
                                                    <span class="login-input-icon"><i class="bi bi-lock"></i></span>
                                                    <div class="invalid-feedback">Masukkan kode captcha.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-12" id="NotifikasiLogin"></div>
                                            <div class="col-12 mb-3">
                                                <button class="btn btn-lg btn-primary w-100 login-submit-btn" id="TombolLogin" type="submit"><span>LOGIN</span></button>
                                            </div>
                                            <div class="col-12">
                                                <button class="btn w-100 login-google-btn" type="button"><span class="fw-semibold">G</span> Login Dengan Google</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="credits text-center login-footer">
                                    <small>
                                        <div class="login-footer-note text-dark">Aplikasi apotek untuk operasional, penjualan, pembelian, dan laporan.</div>
                                        <div class="copyright text-dark">&copy; Copyright <strong><span><?php echo "$title_page"; ?></span></strong>. All Rights Reserved 2026</div>
                                        <div class="credits text-dark">Designed by <span class="text text-decoration-underline">Solihul Hadi</span></div>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
        <?php include "_Partial/FooterJs.php"; ?>
        <script>
            reloadCaptcha();
            $(function() { $('#email').trigger('focus'); });

            function reloadCaptcha() {
                document.getElementById('captchaImage').src = '_Page/Login/Captcha.php?' + new Date().getTime();
            }
            setInterval(reloadCaptcha, 60000);

            $('#TampilkanPassword2').click(function(){
                const isVisible = $('#password').attr('type') === 'text';
                $('#password').attr('type', isVisible ? 'password' : 'text');
                $('#TampilkanPassword2 i').toggleClass('bi-eye bi-eye-slash');
                $('#TampilkanPassword2').attr('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
            });

            $('#ProsesLogin').submit(function(){
                var ProsesLogin = $('#ProsesLogin').serialize();
                var Loading='<div class="spinner-border text-info" role="status"><span class="visually-hidden">Loading...</span></div>';
                $('#TombolLogin').html(Loading);
                $.ajax({
                    type: 'POST',
                    url: '_Page/Login/ProsesLogin.php',
                    data: ProsesLogin,
                    dataType: 'json',
                    success: function(response){
                        $('#TombolLogin').html('<span>LOGIN</span>');
                        if (response.status === 'success') {
                            window.location.href = 'index.php';
                        } else {
                            $('#NotifikasiLogin').html('<div class="alert alert-danger">' + response.message + '</div>');
                        }
                    }
                });
            });
        </script>
    </body>
</html>

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
        <main class="landing_background">
            <div class="container">
                <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
                                <img src="assets/img/<?php echo $logo;?>" alt="<?php echo $title_page;?>" width="100px">
                                <div class="d-flex justify-content-center py-2">
                                    <p>
                                        <a href="" class="logo d-flex align-items-center w-auto">
                                            <span class="d-none d-lg-block text-light"><?php echo $title_page;?></span>
                                        </a>
                                    </p>
                                </div>
                                <form action="javascript:void(0);" class="row g-3" id="ProsesLogin">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <div class="pb-2">
                                                <h5 class="card-title text-center pb-0 fs-4">Login Ke Akun Anda</h5>
                                                <p class="text-center small">Masukan Email Dan Password Untuk Melakukan Login</p>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <label for="email" class="form-label">Email</label>
                                                    <div class="input-group has-validation">
                                                        <span class="input-group-text" id="inputGroupPrepend">@</span>
                                                        <input type="email" name="email" class="form-control" id="email" required>
                                                        <div class="invalid-feedback">Please enter your username.</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <label for="password" class="form-label">Password</label>
                                                    <div class="input-group has-validation">
                                                        <span class="input-group-text" id="inputGroupPrepend">
                                                            <i class="bi bi-key"></i>
                                                        </span>
                                                        <input type="password" name="password" class="form-control" id="password" required>
                                                    </div>
                                                    <div class="invalid-feedback">Please enter your password!</div>
                                                    <small class="credit">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="Tampilkan" id="TampilkanPassword2" name="TampilkanPassword2">
                                                            <label class="form-check-label" for="TampilkanPassword2">
                                                                <small>Tampilkan Password</small>
                                                            </label>
                                                        </div>
                                                    </small>
                                                </div>
                                            </div>
                                                
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <img src="_Page/Login/Captcha.php" class="mb-2" id="captchaImage" alt="No Image" width="100%" style="border: 1px solid #ddd; margin-right: 10px;"/>
                                                    <a href="javascript:void(0);" onclick="reloadCaptcha()" title="Buat kode captcha baru">
                                                        <small>
                                                            <i class="bi bi-repeat"></i> Muat ulang kode captcha
                                                        </small>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <small>
                                                        Masukan karakter <i>Captcha</i>
                                                    </small>
                                                    <div class="input-group has-validation">
                                                        <span class="input-group-text" id="inputGroupPrepend">
                                                            <i class="bi bi-shield-exclamation"></i>
                                                        </span>
                                                        <input type="text" name="captcha" class="form-control" id="captcha" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                
                                                <div class="col-12" id="NotifikasiLogin"></div>
                                                <div class="col-12">
                                                    <button class="btn btn-primary w-100" id="TombolLogin" type="submit">Login</button>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </form>
                                <div class="credits text-center">
                                    <small>
                                        <div class="copyright text-white">
                                            &copy; Copyright <strong><span><?php echo "$title_page"; ?></span></strong>. All Rights Reserved 2023
                                        </div>
                                        <div class="credits text-white">
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
                if($(this).is(':checked')){
                    $('#password').attr('type','text');
                }else{
                    $('#password').attr('type','password');
                }
            });
            //Kondisi saat tampilkan password
            $('#TampilkanPassword2').click(function(){
                if($(this).is(':checked')){
                    $('#password').attr('type','text');
                }else{
                    $('#password').attr('type','password');
                }
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
                        $('#TombolLogin').html('Login');
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
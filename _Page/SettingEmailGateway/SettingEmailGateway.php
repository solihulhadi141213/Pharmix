<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'rfLn8WEkAqzC1gu5z45');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
        // Include Email Gateway
        include "_Config/SettingEmail.php";
?>

<div class="pagetitle">
    <h1>
        <a href="">
            <i class="bi bi-envelope"></i> Email Gateway</a>
        </a>
    </h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active"> Email Gateway</li>
        </ol>
    </nav>
</div>
<section class="section dashboard">
    <div class="row mb-3 align-items-stretch">
        
        <!-- Form Pengaturan Email Gateway -->
        <div class="col-md-6 d-flex">
            <form action="javascript:void(0);" id="ProsesSettingEmail" class="w-100 h-100">
                <div class="card h-100 w-100">
                    <div class="card-header">
                        <b class="card-title">
                            <i class="bi bi-gear"></i> Pengaturan Email Gateway
                        </b>
                    </div>
                    <div class="card-body flex-grow-1">
                        
                        <div class="row mb-3 mt-4">
                            <div class="col-md-4">
                                <label class="form-label" for="url_service">
                                    <small>URL Service</small>
                                </label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="url_service" id="url_service" class="form-control" required value="<?php echo "$url_service"; ?>">
                                <small>
                                    <small class="text text-grayish">URL API Service Email Gateway</small>
                                </small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label" for="url_provider">
                                    <small>Provider SMTP</small>
                                </label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="url_provider" id="url_provider" class="form-control" required value="<?php echo "$url_provider"; ?>">
                                <small>
                                    <small class="text text-grayish">
                                        URL yang mengarah pada provider SMTP.(Contoh : smtp.hostinger.com)
                                    </small>
                                </small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label" for="email_gateway">
                                    <small>Akun Email</small>
                                </label>
                            </div>
                            <div class="col-md-8">
                                <input type="email" name="email_gateway" id="email_gateway" class="form-control" required value="<?php echo "$email_gateway"; ?>">
                                <small class="credit">
                                    <small class="text text-grayish">
                                        Akun email dari web mail pada layanan hosting.
                                    </small>
                                </small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label" for="password_gateway">
                                    <small>Password Email</small>
                                </label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="password_gateway" id="password_gateway" class="form-control" required value="<?php echo "$password_gateway"; ?>">
                                <small class="credit">
                                    <small class="text text-grayish">
                                        Password akun email.
                                    </small>
                                </small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label" for="nama_pengirim">
                                    <small>Nama Pengirim</small>
                                </label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="nama_pengirim" id="nama_pengirim" class="form-control" required value="<?php echo "$nama_pengirim"; ?>">
                                <small class="credit">
                                    <small class="text text-grayish">
                                        Nama pengirim yang disematkan pada saat mengirim email.
                                    </small>
                                </small>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label" for="port_gateway">
                                    <small>Port SMTP</small>
                                </label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="port_gateway" id="port_gateway" class="form-control" required value="<?php echo "$port_gateway"; ?>">
                                <small class="credit">
                                    <small class="text text-grayish">
                                        Port SMTP yang terbuka untuk proses pengiriman email.
                                    </small>
                                </small>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12" id="NotifikasiSimpanSettingEmail">
                                
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-md btn-primary w-100" id="ButtonSimpanSettingEmail">
                            <i class="bi bi-save"></i> Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Uji Coba Kirim Email -->
        <div class="col-md-6 d-flex">
            <form action="javascript:void(0);" id="ProsesTestKirimEmail" class="w-100 h-100">
                <div class="card h-100 w-100">
                    <div class="card-header">
                        <b class="card-title">
                            <i class="bi bi-send"></i> Uji Coba Kirim Email
                        </b>
                    </div>
                    <div class="card-body flex-grow-1">
                        
                        <div class="row mb-3 mt-4">
                            <div class="col-md-12">
                                <label for="nama_tujuan">
                                    <small>Nama Penerima</small>
                                </label>
                                <input type="text" name="nama_tujuan" id="nama_tujuan" class="form-control" placeholder="Contoh : Bapak Syamsul Maarif">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="email_tujuan">
                                    <small>Email Tujuan</small>
                                </label>
                                <input type="email" name="email_tujuan" id="email_tujuan" class="form-control" placeholder="email@domain.com">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="subjek">
                                    <small>Subjek Pesan</small>
                                </label>
                                <input type="text" name="subjek" id="subjek" class="form-control" placeholder="">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="pesan">
                                    <small>Isi Pesan</small>
                                </label>
                                <textarea name="pesan" id="pesan" cols="30" rows="3" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div
                                    id="NotifikasiTestKirimEmail"
                                    class="border rounded p-2"
                                    style="
                                        height: 250px;
                                        overflow-y: auto;
                                        white-space: pre-wrap;
                                        background-color: #f8f9fa;
                                    ">
                                    <!-- Log Proses Disini -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-md btn-primary w-100" id="ButtonTestKirimEmail">
                            <i class="bi bi-send"></i> Kirim Pesan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<?php } ?>
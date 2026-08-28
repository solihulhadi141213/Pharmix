<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'RZufXfHVLW9f0EsjYSB');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
?>
    <div class="pagetitle">
        <h1>
            <a href="">
                <i class="bi bi-cart-check"></i> Transaksi Operasional</a>
            </a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active"> Transaksi Operasional</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <small>
                        Berikut ini adalah halaman transaksi.
                        Anda bisa mencatat data transaksi sesuai referensi jenis transaksi yang sudah ada.
                        Anda juga bisa mencatat data transaksi sekaligus melakukan posting jurnal pada halaman ini.
                    </small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <!-- Data View -->
        <div class="row" id="data_view">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-12 text-end">
                                <button type="button" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalFilter">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button type="button" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalExport">
                                    <i class="bi bi-download"></i>
                                </button>
                                <a href="javascript:void(0);" class="btn btn-md btn-primary btn-floating tambah_transaksi" title="Tambah Transaksi Operasional">
                                    <i class="bi bi-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table table-responsive mt-3">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center"><small><b>No</b></small></th>
                                        <th><small><b>Tanggal</b></small></th>
                                        <th><small><b>Nama Transaksi</b></small></th>
                                        <th><small><b>Kategori</b></small></th>
                                        <th><small><b>Jumlah</b></small></th>
                                        <th><small><b>Cash/Tunai</b></small></th>
                                        <th class="text-center"><small><b>Status</b></small></th>
                                        <th class="text-center"><small><b>Opsi</b></small></th>
                                    </tr>
                                </thead>
                                <tbody id="tabel_transaksi">
                                    <tr>
                                        <td class="text-center" colspan="8">
                                            <small>No Data</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
                                <button type="button" class="btn btn-sm btn-outline-info btn-floating" id="prev_button">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info btn-floating" id="next_button">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
         
        <!-- Detail View -->
         <div class="row" id="detail_view">
            <div class="col-12">

                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-8">
                                <b class="card-title">
                                    <i class="bi bi-info-circle"></i> Detail Transaksi
                                </b>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="javascript:void(0);" class="btn btn-md btn-secondary btn-floating back_to_data" title="kembali">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                                <button type="button" class="btn btn-md btn-primary btn-floating edit_transaksi" title="Edit Transaksi">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" id="put_detail_transaksi">
                        <!-- Detail Transaksi Akan Muncul Disini -->
                    </div>
                </div>

                <div class="card">
                    <div class="card-hheader">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <ul class="nav nav-tabs nav-tabs-bordered d-flex" id="borderedTabJustified" role="tablist">
                                    <li class="nav-item flex-fill" role="presentation">
                                        <button class="card-title nav-link w-100 active" id="home-tab" data-bs-toggle="tab" data-bs-target="#bordered-justified-home" type="button" role="tab" aria-controls="home" aria-selected="true">
                                            Rincian/Uraian Transaksi
                                        </button>
                                    </li>
                                    <li class="nav-item flex-fill" role="presentation">
                                        <button class="card-title nav-link w-100" id="profile-tab" data-bs-toggle="tab" data-bs-target="#bordered-justified-profile" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1">
                                            Jurnal Keuangan
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                                    <div class="tab-pane fade active show" id="bordered-justified-home" role="tabpanel" aria-labelledby="home-tab">
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-12 text-end">
                                                <button type="button" class="btn btn-md btn-primary btn-block"  data-bs-toggle="modal" data-bs-target="#ModalTambahRincian" title="Tambah Rincian">
                                                    <i class="bi bi-plus"></i> Tambah Rincian
                                                </button>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-md-12 table table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th align="center"><b>No</b></th>
                                                            <th align="center"><b>Uraian</b></th>
                                                            <th align="center"><b>Harga</b></th>
                                                            <th align="center"><b>QTY</b></th>
                                                            <th align="center"><b>Satuan</b></th>
                                                            <th align="center"><b>Jumlah</b></th>
                                                            <th align="center"><b>Opsi</b></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="put_rincian_transaksi">
                                                        <tr>
                                                            <td colspan="7" class="text-center">
                                                                <small>No Data</small>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="bordered-justified-profile" role="tabpanel" aria-labelledby="profile-tab">
                                        <div class="row mb-3">
                                            <div class="col-md-12 text-end">
                                                <button type="button" class="btn btn-md btn-primary btn-block"  data-bs-toggle="modal" data-bs-target="#ModalTambahJurnal" title="Tambah Jurnal">
                                                    <i class="bi bi-plus"></i> Tambah Jurnal
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 table table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th align="center"><b>Kode</b></th>
                                                            <th align="center"><b>Akun Perkiraan</b></th>
                                                            <th align="center"><b>Debet</b></th>
                                                            <th align="center"><b>Kredit</b></th>
                                                            <th align="center"><b>Opsi</b></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tabel_jurnal_transaksi">
                                                         <!-- Menampilkan Jurnal Disini -->
                                                        <tr>
                                                            <td colspan="4">
                                                                <small>No Data</small>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tambah Transaksi View-->
        <form action="javascript:void(0);" id="ProsesTambahTransaksi" autocomplete="off">
            <div class="row" id="tambah_transaksi_view">
                
                <div class="col-md-12">

                    <!-- Informasi Umum -->
                     <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-8">
                                    <b class="card-title">
                                        <i class="bi bi-plus"></i> Transaksi Operasional
                                    </b>
                                </div>
                                <div class="col-4 text-end">
                                    <a href="javascript:void(0);" class="btn btn-md btn-secondary btn-floating back_to_data" title="Kembali">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mt-3 mb-3">
                                <div class="col-md-4">
                                    <label for="id_transaksi_jenis">* Kategori Operasional</label>
                                </div>
                                <div class="col-md-8">
                                    <select name="id_transaksi_jenis" id="id_transaksi_jenis" class="form-select" style="width: 100%;">
                                        <option value="">Pilih</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="tanggal">* Tanggal Transaksi</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="jam">* Jam Transaksi</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="time" name="jam" id="jam" class="form-control" value="<?php echo date('H:i'); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <br>
                        </div>
                    </div>

                    <!-- Rincian Transaksi -->
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-8">
                                    <b class="card-title">
                                        <i class="bi bi-list"></i> Uraian/Rincian
                                    </b>
                                </div>
                                <div class="col-4 text-end">
                                    <button type="button" class="btn btn-md btn-primary btn-floating" id="TambahUraian" title="Tambah Rincian">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="table table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <td><b>Uraian/Keterangan</b></td>
                                                    <td><b>Harga</b></td>
                                                    <td><b>QTY</b></td>
                                                    <td><b>Satuan</b></td>
                                                    <td><b>Jumlah</b></td>
                                                    <td><b>Opsi</b></td>
                                                </tr>
                                            </thead>
                                            <tbody id="UraianTransaksi">
                                                <tr>
                                                    <td align="right" colspan="6"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-12 mt-3 mb-3">
                                    <h3>SUBTOTAL : <b id="JumlahTotal2">0</b></h3>
                                    
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pembayaran Dan Keterangan -->

                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-12">
                                    <b class="card-title">
                                        # Informasi Pembayaran
                                    </b>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3 mt-3">
                                <div class="col-md-4">
                                    <label for="JumlahTotal">Jumlah (Rp)</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="JumlahTotal" id="JumlahTotal" class="form-control" readonly value="0">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="JumlahPembayaran">Pembayaran (Rp)</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="JumlahPembayaran" id="JumlahPembayaran" class="form-control" inputmode="numeric" value="0">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="status">Status</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="status" id="status" class="form-control" readonly value="Lunas">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="keterangan">Keterangan</label>
                                </div>
                                <div class="col-md-8">
                                    <textarea name="keterangan" id="keterangan" class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12 mb-3" id="NotifikasiTambahTransaksi">
                                    <!-- Notifikasi Tambah Transaksi Akan Muncul Disini -->
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-md btn-block btn-rounded btn-primary" id="TombolTambahTransaksi">
                                <i class="bi bi-save"></i> Simpan Transaksi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
<?php } ?>
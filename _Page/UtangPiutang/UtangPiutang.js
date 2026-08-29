//=======================================================
// MODAL HABIT
//=======================================================
$(document).on('click', '[data-modal-target]', function (e) {
    e.preventDefault();

    var target = $(this).attr('data-modal-target');
    var modalElement = document.querySelector(target);

    if (modalElement) {
        bootstrap.Modal.getOrCreateInstance(modalElement).show(this);
    }
});

$(document).on('show.bs.modal', '.modal', function () {
    var zIndex = 1050 + (10 * $('.modal.show').length);

    $(this).css('z-index', zIndex);
    setTimeout(function () {
        $('.modal-backdrop').not('.modal-stack')
            .css('z-index', zIndex - 1)
            .addClass('modal-stack');
    }, 0);
});

$(document).on('hidden.bs.modal', '.modal', function () {
    if ($('.modal.show').length) {
        $('body').addClass('modal-open');
    }
});

//=======================================================
// FUNCTION
//=======================================================

//Fungsi Untuk Menampilkan dashboard Utang Piutang
function ShowCount() {

    // Kosongkan Notifikasi
    $('#NotifikasiSistem').html(``);
    
    // Loading HTML
    $('#utang_jual_beli').html('...');
    $('#utang_pembelian').html('...');
    $('#utang_retur_penjualan').html('...');
    $('#piutang_jual_beli').html('...');
    $('#piutang_penjualan').html('...');
    $('#piutang_retur_pembelian').html('...');
    $('#utang_operasional').html('...');
    $('#piutang_operasional').html('...');
    $('#total_utang').html('...');
    $('#total_piutang').html('...');

    // Ambil Data Dengan AJAX
    $.ajax({
        type    : 'POST',
        url     : '_Page/UtangPiutang/ProsesCountUtangPiutang.php',
        dataType: 'JSON',
        success : function(response) {

            // Gunakan setTimeout untuk memberikan delay sebelum data dipasang
            setTimeout(function() {

                // Status & Message
                var status = response.status;
                var message = response.message;
                var data = response.data;

                // Jika Berhasil
                if(status=='success'){
                    
                    // Tempelkan Data
                    $('#utang_jual_beli').html(data.utang_jual_beli);
                    $('#utang_pembelian').html(data.utang_pembelian);
                    $('#utang_retur_penjualan').html(data.utang_retur_penjualan);
                    $('#piutang_jual_beli').html(data.piutang_jual_beli);
                    $('#piutang_penjualan').html(data.piutang_penjualan);
                    $('#piutang_retur_pembelian').html(data.piutang_retur_pembelian);
                    $('#utang_operasional').html(data.utang_operasional);
                    $('#piutang_operasional').html(data.piutang_operasional);
                    $('#total_utang').html(data.total_utang);
                    $('#total_piutang').html(data.total_piutang);
                }else{
                    
                    //Tempelkan Notifikasi
                    $('#NotifikasiSistem').html(`
                        <div class="alert alert-danger text-center" role="alert">
                            <small>
                                <b>Opss!</b><br>
                                ${message}
                            </small>
                        </div>
                    `);
                }

            }, 500); // Angka 500 artinya delay selama 500 milidetik (0.5 detik). Ubah sesuai kebutuhan.

        },
        error: function () {
            
            //Tempelkan Notifikasi
            $('#NotifikasiSistem').html(`
                <div class="alert alert-danger text-center" role="alert">
                    <small>
                        <b>Opss!</b><br>
                        Terjadi kesalahan pada sistem. Silakan coba lagi.
                    </small>
                </div>
            `);
        },
    });
}

//Fungsi Untuk Menampilkan Data Utang Piutang Operasional
function ShowUtangPiutangOperasional() {
    // Target And Filter
    let target = $('#tabel_operasional');
    let data   = $('#ProsesFilterOperasional').serialize();

    target.addClass('blur-loading');

    $.ajax({
        type    : 'POST',
        url     : '_Page/UtangPiutang/TabelUtangPiutangOperasional.php',
        data    : data,
        dataType: 'JSON',
        success : function(res) {

            if(res.status === "success"){

                target.fadeOut(150, function () {
                    target.html(res.html).fadeIn(150);
                });

                // Update info page
                $('#page_info_operasional').html('Page ' + res.page + ' Of ' + res.total_page);

                // Handle tombol
                $('#prev_button_operasional').prop('disabled', res.page <= 1);
                $('#next_button_operasional').prop('disabled', res.page >= res.total_page);

            }else{
                target.html(res.html);
            }

            target.removeClass('blur-loading');
        }
    });
}

//Fungsi Untuk Menampilkan Data Utang Piutang Jual/Beli
function ShowUtangPiutangJualBeli() {
    // Target And Filter
    let target = $('#tabel_utang_piutang');
    let data   = $('#ProsesFilter').serialize();

    target.addClass('blur-loading');

    $.ajax({
        type    : 'POST',
        url     : '_Page/UtangPiutang/TabelUtangPiutangJualBeli.php',
        data    : data,
        dataType: 'JSON',
        success : function(res) {

            if(res.status === "success"){

                target.fadeOut(150, function () {
                    target.html(res.html).fadeIn(150);
                });

                // Update info page
                $('#page_info').html('Page ' + res.page + ' Of ' + res.total_page);

                // Handle tombol
                $('#prev_button').prop('disabled', res.page <= 1);
                $('#next_button').prop('disabled', res.page >= res.total_page);

            }else{
                target.html(res.html);
            }

            target.removeClass('blur-loading');
        }
    });
}


//Fungsi Menampilkan Riwayat Pembayaran
function ShowRiwayatPembayaran(id, kategori) {
   // Load Baris Tabel
    $('#tabel_riwayat_pembayaran').html(`
        <tr>
            <td colspan="6" class="text-center">
                <small>Loading...</small>
            </td>
        </tr>
    `);

    //Buka Tabel Riwayat Pembayaran
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/UtangPiutang/TabelRiwayatPembayaran.php',
        data        : {id: id, kategori: kategori},
        dataType    : "JSON",
        success     : function(response){

            // Jika Berhasil
            if(response.status=="success"){

                //Tempelkan html pada 'tabel_riwayat_pembayaran'
                $('#tabel_riwayat_pembayaran').html(response.html);
                
            }else{
                //Tempelkan Notifikasi
                $('#tabel_riwayat_pembayaran').html(
                    `
                        <tr>
                            <td colspan="6" class="text-center">
                                <small>
                                    <b>Opsss!</b><br>
                                    Terjadi kesalahan pada sistem. <br>
                                    ${response.message}
                                </small>
                            </td>
                        </tr>
                    `
                );
            }
        },
        error: function () {
            
            //Tempelkan Notifikasi
            $('#tabel_riwayat_pembayaran').html(
                `
                    <tr>
                        <td colspan="6" class="text-center">
                            <small>
                                <b>Opsss!</b><br>
                                Terjadi kesalahan pada sistem.
                            </small>
                        </td>
                    </tr>
                `
            );
        },
    });
}

//Fungsi Untuk Format Rupiah
function formatRupiah(angka) {
    return 'Rp ' + parseFloat(angka).toLocaleString('id-ID', { minimumFractionDigits: 0 });
}

// Fungsi untuk memproses input pada elemen dengan class form-money
function processInput(event) {
    let input = event.target;
    let originalValue = input.value;

    // Hilangkan titik dari nilai asli untuk penghitungan
    let rawValue = originalValue.replace(/\./g, "");

    // Format nilai input
    let formattedValue = formatMoney(rawValue);

    // Update nilai input dengan nilai yang telah diformat
    input.value = formattedValue;
}

// Fungsi untuk memformat angka menjadi format ribuan
function formatMoney(value) {
    if (!value) return ""; // Jika kosong, kembalikan string kosong
    // Hilangkan karakter selain angka
    value = value.toString().replace(/[^0-9]/g, "");
    // Tambahkan pemisah ribuan (titik)
    return value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Fungsi untuk menginisialisasi elemen form-money
function initializeMoneyInputs() {
    const moneyInputs = document.querySelectorAll(".form-money");
    moneyInputs.forEach(function (input) {
        // Format nilai awal jika sudah ada
        input.value = formatMoney(input.value);

        // Pastikan input diformat dengan benar
        input.removeEventListener("input", processInput); // Menghapus event listener sebelumnya
        input.addEventListener("input", processInput);
    });
}

$(document).ready(function() {

    //Menampilkan Data Pertama Kali
    ShowCount();
    ShowUtangPiutangOperasional();
    ShowUtangPiutangJualBeli();

    // Ketika 'ReloadCount' di click
    $('#ReloadCount').click(function() {
        ShowCount();
    });

    // ---------------------------------------------
    // PENANGANAN TABEL OPERASIONAL
    // ---------------------------------------------
    //Ketika 'keyword_by_operasional' diubah
    $('#keyword_by_operasional').change(function() {
        var keyword_by_operasional= $('#keyword_by_operasional').val();
        $.ajax({
            type    : 'POST',
            url     : '_Page/UtangPiutang/FormFilterOperasional.php',
            data    : {keyword_by_operasional: keyword_by_operasional},
            success: function(data) {
                $('#FormFilterOperasional').html(data);
            }
        });
    });

    //Ketika 'ProsesFilterOperasional' Di Submit
    $('#ProsesFilterOperasional').submit(function(e) {
        e.preventDefault();

        // Reset Halaman
        $('#page_filter_operasional').val(1);

        // Tampilkan Data
        ShowUtangPiutangOperasional();

        // Tutup Modal Bootstrap 5
        const modalElement = document.getElementById('ModalFilterOperasional');
        const modal = bootstrap.Modal.getInstance(modalElement);

        if (modal) {modal.hide();}
    });

    //Pagging
    $(document).on('click', '#next_button_operasional', function() {
        var page = parseInt($('#page_filter_operasional').val(), 10); // Pastikan nilai diambil sebagai angka
        var page = page + 1;
        $('#page_filter_operasional').val(page);
        ShowUtangPiutangOperasional();
    });
    $(document).on('click', '#prev_button_operasional', function() {
        var page = parseInt($('#page_filter_operasional').val(), 10); // Pastikan nilai diambil sebagai angka
        var page = page - 1;
        $('#page_filter_operasional').val(page);
        ShowUtangPiutangOperasional();
    });

    // ---------------------------------------------
    // PENANGANAN TABEL JUAL BELI
    // ---------------------------------------------
    //Ketika 'keyword_by' diubah
    $('#keyword_by').change(function() {
        var keyword_by= $('#keyword_by').val();
        $.ajax({
            type    : 'POST',
            url     : '_Page/UtangPiutang/FormFilter.php',
            data    : {keyword_by: keyword_by},
            success: function(data) {
                $('#FormFilter').html(data);
            }
        });
    });

    //Ketika Filter Di Submit
    $('#ProsesFilter').submit(function() {
        ShowUtangPiutangJualBeli();

        //Tutup Modal
        $('#ModalFilterPenjualan').modal('hide');
    });

    //Pagging
    $(document).on('click', '#next_button', function() {
        var page = parseInt($('#page_filter').val(), 10); // Pastikan nilai diambil sebagai angka
        var page = page + 1;
        $('#page_filter').val(page);
        ShowUtangPiutang();
    });
    $(document).on('click', '#prev_button', function() {
        var page = parseInt($('#page_filter').val(), 10); // Pastikan nilai diambil sebagai angka
        var page = page - 1;
        $('#page_filter').val(page);
        ShowUtangPiutang();
    });

    // ---------------------------------------------
    // Modal Detail Transaksi Operasional
    // ---------------------------------------------
    $('#ModalDetailTransaksiOperasional').on('show.bs.modal', function (e) {
        
        //Tangkap 'id_transaksi' 
        var id_transaksi = $(e.relatedTarget).data('id');

        // Load Form
        $('#FormDetailTransaksiOperasional').html("Loading...");

        //Buka Detail Barang
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Transaksi/FormDetail.php',
            data        : {id_transaksi: id_transaksi},
            dataType    : "JSON",
            success     : function(response){
                if(response.status=="success"){
                    var html = response.html;

                    //Tempelkan Detail
                    $('#FormDetailTransaksiOperasional').html(html);

                }else{
                    //Tempelkan ke 'FormDetailTransaksiJualBeli'
                    $('#FormDetailTransaksiOperasional').html(
                        `
                            <div class="alert alert-danger text-center" role="alert">
                                <small>
                                    <b>Opsss!</b><br>
                                    Terjadi kesalahan pada sistem. <br>
                                    ${response.message}
                                </small>
                            </div>
                        `
                    );

                }
            },
            error: function () {
                //Tempelkan ke 'FormDetailTransaksiOperasional'
                $('#FormDetailTransaksiOperasional').html(
                    `
                        <div class="alert alert-danger text-center" role="alert">
                            <small>
                                <b>Opsss!</b><br>
                                Terjadi kesalahan pada sistem. Silahkan Coba Lagi<br>
                            </small>
                        </div>
                    `
                );

            },
        });
    });

    // ---------------------------------------------
    // Modal Detail Transaksi Jual Beli
    // ---------------------------------------------
    $('#ModalDetailTransaksiJualBeli').on('show.bs.modal', function (e) {
        
        //Tangkap id_transaksi_jual_beli 
        var id_transaksi_jual_beli = $(e.relatedTarget).data('id');

        // Load Form
        $('#FormDetailTransaksiJualBeli').html("Loading...");

        //Disable tombol
        $('#ButtonTransaksiJualBeliSelengkapnya').prop("disabled", true);

        //Buka Detail Barang
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/UtangPiutang/FormDetailTransaksiJualBeli.php',
            data        : {id_transaksi_jual_beli: id_transaksi_jual_beli},
            dataType    : "JSON",
            success     : function(response){
                if(response.status=="success"){
                    var html = response.html;

                    //Tempelkan Detail
                    $('#FormDetailTransaksiJualBeli').html(html);

                    // Enable Tombol
                    $('#ButtonTransaksiJualBeliSelengkapnya').prop("disabled", false);
                }else{
                    //Tempelkan ke 'FormDetailTransaksiJualBeli'
                    $('#FormDetailTransaksiJualBeli').html(
                        `
                            <div class="alert alert-danger text-center" role="alert">
                                <small>
                                    <b>Opsss!</b><br>
                                    Terjadi kesalahan pada sistem. <br>
                                    ${response.message}
                                </small>
                            </div>
                        `
                    );
                    
                    //Disable tombol
                    $('#ButtonTransaksiJualBeliSelengkapnya').prop("disabled", true);
                }
            },
            error: function () {
                //Tempelkan ke 'FormDetailTransaksiJualBeli'
                $('#FormDetailTransaksiJualBeli').html(
                    `
                        <div class="alert alert-danger text-center" role="alert">
                            <small>
                                <b>Opsss!</b><br>
                                Terjadi kesalahan pada sistem. Silahkan Coba Lagi<br>
                            </small>
                        </div>
                    `
                );

                //Disable tombol
                $('#ButtonTransaksiSelengkapnya').prop("disabled", true);
            },
        });
    });

    // -----------------------------------------------------------
    // Modal Riwayat Pembayaran
    // -----------------------------------------------------------
    $('#ModalRiwayatPembayaran').on('show.bs.modal', function (e) {
        
        //Tangkap id dan Kategori transaksi
        var id       = $(e.relatedTarget).data('id');
        var kategori = $(e.relatedTarget).data('kategori');

        // Show Riwayat Transaksi
        ShowRiwayatPembayaran(id, kategori);
    });
   
    //Modal Pembayaran
    $('#ModalPembayaran').on('show.bs.modal', function (e) {

        //Tangkap id_transaksi_jual_beli dari modal detail
        var id       = $(e.relatedTarget).data('id');
        var kategori = $(e.relatedTarget).data('kategori');

        //Kosongkan 'NotifikasiPembayaran'
        $("#NotifikasiPembayaran").html("");

        // Loading Form
        $("#FormPembayaran").html("Loading...");

        //Buka Detail Transaksi
        $.ajax({
            type        : 'POST',
            url         : '_Page/UtangPiutang/FormPembayaran.php',
            data        : {
                id: id,
                kategori: kategori
            },
            success     : function(response){

                //Tampilkan Form
                $("#FormPembayaran").html(response);

                //Inisialisasi Format Money
                initializeMoneyInputs();

                //Tandai Form Sudah Siap 
                $('#ModalPembayaran').data('form-ready', true); 
                
                //Jika Modal Sudah Terbuka 
                if($('#ModalPembayaran').hasClass('show')){ 
                    //Fokus Ke Input 
                    $('#ModalPembayaran #nominal_pembayaran').focus();
                }

            },
            error       : function(){
                $("#FormPembayaran").html(
                    '<div class="alert alert-danger">' +
                        'Terjadi kesalahan saat memuat form.' +
                    '</div>'
                );
            }
        });
    });

    //Ketika Modal Sudah Benar-Benar Ditampilkan
    $('#ModalPembayaran').on('shown.bs.modal', function () {

        //Periksa Apakah Form Sudah Siap
        if($(this).data('form-ready') === true){

            //Fokus Ke Input
            $(this).find('#nominal_pembayaran').focus();

        }
    });

    //Proses Pembayaran
    $("#ProsesPembayaran").on("submit", function (e) {
        e.preventDefault();
        
        // Proses Pembayaran
        var ProsesPembayaran=$("#ProsesPembayaran").serialize();

        //Loading Notifikasi
        $('#NotifikasiPembayaran').html('Loading...');

        // Disable Button
        $('#ButtonPembayaran').prop("disabled", true);

        // Send Data
        $.ajax({
            type    : 'POST',
            url     : '_Page/UtangPiutang/ProsesPembayaran.php',
            data    : ProsesPembayaran,
            dataType: 'JSON',
            success: function(response) {

                // Status & Message
                var status  = response.status;
                var message = response.message;
                var id = response.id;
                var kategori = response.kategori;

                if(status=='success'){

                    // Kosongkan Notifikasi
                    $('#NotifikasiPembayaran').html('');

                    // Enable Button
                    $('#ButtonPembayaran').prop("disabled", false);

                    // Close Modal
                    $("#ModalPembayaran").modal('hide');

                    // Reload Data
                    ShowRiwayatPembayaran(id, kategori);
                    ShowUtangPiutangOperasional();
                    ShowUtangPiutangJualBeli();

                    showToast(
                        'success',
                        'Berhasil',
                        'Data Pembayaran Berhasil Disimpan.'
                    );

                }else{

                    // Jika Gagal, Tampilkan Pada Notifikasi
                    $('#NotifikasiPembayaran').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
                    
                    // Enable Button
                    $('#ButtonPembayaran').prop("disabled", false);
                }
            },
            error : function(){
                $('#NotifikasiPembayaran').html('<div class="alert alert-danger"><small><b>Opss!</b> Terjadi kesalahan pada sistem!</small></div>');
                // Enable Button
                $('#ButtonPembayaran').prop("disabled", false);
            }
        });
    });

    //Modal Edit Pembayaran
    $('#ModalEditPembayaran').on('show.bs.modal', function (e) {
        //Tangkap id_anggota dari modal detail
        var id_transaksi_pembayaran = $(e.relatedTarget).data('id_transaksi_pembayaran');
        var id                      = $(e.relatedTarget).data('id');
        var kategori                = $(e.relatedTarget).data('kategori');

        //Loading
        $("#FormEditPembayaran").html('Loading...');

        //Kosongkan Notifikasi
        $("#NotifikasiEditPembayaran").html('');

        //Tampilkan Data Dengan AJAX
        $.ajax({
            url         : "_Page/UtangPiutang/FormEditPembayaran.php",
            type        : "POST",
            data        : {id_transaksi_pembayaran: id_transaksi_pembayaran, id: id, kategori: kategori},
            success: function (response) {
                $('#FormEditPembayaran').html(response);
            }
        });
    });

    //Proses Edit Pembayaran
    $("#ProsesEditPembayaran").on("submit", function (e) {
        e.preventDefault();
        
        // Tombol loading
        $("#ButtonEditPembayaran").html('Loading..');
        $("#ButtonEditPembayaran").prop("disabled", true);
        let ButtonElement = '<i class="bi bi-save"></i> Simpan';
        
        // Ambil data form
        let formData = new FormData(this);

        // Kirim data ke server
        $.ajax({
            url         : "_Page/UtangPiutang/ProsesEditPembayaran.php",
            type        : "POST",
            data        : formData,
            contentType : false,
            processData : false,
            dataType    : "json",
            success: function (response) {
                //Apabila Proses Berhasil
                if (response.status === "Success") {

                    // Buat Variabel dari response
                    var id       = response.id;
                    var kategori = response.kategori;

                    // Enable Button
                    $("#ButtonEditPembayaran").html(ButtonElement).prop("disabled", false);
                    $('#NotifikasiEditPembayaran').html('');
                    
                    //Tutup Modal
                    $('#ModalEditPembayaran').modal('hide');
                    
                    // Reload Data
                    ShowRiwayatPembayaran(id, kategori);
                    ShowUtangPiutangOperasional();
                    ShowUtangPiutangJualBeli();

                    showToast(
                        'success',
                        'Berhasil',
                        'Data Pembayaran Berhasil Diperbaharui.'
                    );
                } else {
                    // Tampilkan pesan error
                    $("#NotifikasiEditPembayaran").html(
                        `<div class="alert alert-danger" role="alert">${response.message}</div>`
                    );
                    $("#ButtonEditPembayaran").html(ButtonElement).prop("disabled", false);
                }
            },
            error: function () {
                $("#NotifikasiEditPembayaran").html(
                    '<div class="alert alert-danger" role="alert">Terjadi kesalahan pada sistem. Silakan coba lagi.</div>'
                );
                $("#ButtonEditPembayaran").html(ButtonElement).prop("disabled", false);
            },
        });
    });

    //Modal Hapus Pembayaran
    $('#ModalHapusPembayaran').on('show.bs.modal', function (e) {
        
        //Tangkap id_anggota dari modal detail
        var id_transaksi_pembayaran = $(e.relatedTarget).data('id_transaksi_pembayaran');
        var id                      = $(e.relatedTarget).data('id');
        var kategori                = $(e.relatedTarget).data('kategori');
        
        //Loading
        $("#FormHapusPembayaran").html('Loading...');

        //Kosongkan Notifikasi
        $("#NotifikasiHapusPembayaran").html('');

        //Tampilkan Data Dengan AJAX
        $.ajax({
            url         : "_Page/UtangPiutang/FormHapusPembayaran.php",
            type        : "POST",
            data        : {id_transaksi_pembayaran: id_transaksi_pembayaran, id: id, kategori: kategori},
            success: function (response) {
                $('#FormHapusPembayaran').html(response);
            }
        });
    });

    //Proses Hapus Pembayaran
    $("#ProsesHapusPembayaran").on("submit", function (e) {
        e.preventDefault();
        
        // Tombol loading
        $("#ButtonHapusPembayaran").html('Loading..');
        $("#ButtonHapusPembayaran").prop("disabled", true);
        let ButtonElement = '<i class="bi bi-check"></i> Ya, Hapus';
        
        // Ambil data form
        let formData = new FormData(this);

        // Kirim data ke server
        $.ajax({
            url         : "_Page/UtangPiutang/ProsesHapusPembayaran.php",
            type        : "POST",
            data        : formData,
            contentType : false,
            processData : false,
            dataType    : "json",
            success: function (response) {
                
                //Apabila Proses Berhasil
                if (response.status === "Success") {

                    // Buat Variabel dari response
                    var id       = response.id;
                    var kategori = response.kategori;

                    // Penanganan Button
                    $("#ButtonHapusPembayaran").html(ButtonElement).prop("disabled", false);
                    $('#NotifikasiHapusPembayaran').html('');
                    
                    //Tutup Modal
                    $('#ModalHapusPembayaran').modal('hide');
                    
                    // Reload Data
                    ShowRiwayatPembayaran(id, kategori);
                    ShowUtangPiutangOperasional();
                    ShowUtangPiutangJualBeli();

                    showToast(
                        'success',
                        'Berhasil',
                        'Data Pembayaran Berhasil Dihapus.'
                    );
                } else {
                    // Tampilkan pesan error
                    $("#NotifikasiHapusPembayaran").html(
                        `<div class="alert alert-danger" role="alert">${response.message}</div>`
                    );
                    $("#ButtonHapusPembayaran").html(ButtonElement).prop("disabled", false);
                }
            },
            error: function () {
                $("#NotifikasiHapusPembayaran").html(
                    '<div class="alert alert-danger" role="alert">Terjadi kesalahan pada sistem. Silakan coba lagi.</div>'
                );
                $("#ButtonHapusPembayaran").html(ButtonElement).prop("disabled", false);
            },
        });
    });

    // -----------------------------------------------------------
    // Tempo
    // -----------------------------------------------------------
    
    //Modal Tempo
    $('#ModalTempo').on('show.bs.modal', function (e) {

        //Tangkap id_transaksi_jual_beli dari modal detail
        var id       = $(e.relatedTarget).data('id');
        var kategori = $(e.relatedTarget).data('kategori');

        //Kosongkan 'NotifikasiTempo'
        $("#NotifikasiTempo").html("");

        // Loading Form
        $("#FormTempo").html("Loading...");

        //Buka Detail Transaksi
        $.ajax({
            type        : 'POST',
            url         : '_Page/UtangPiutang/FormTempo.php',
            data        : {id: id, kategori: kategori},
            success     : function(response){

                //Tampilkan Form
                $("#FormTempo").html(response);
            }
        });
    });

    //Proses Tambah Tempo Pembayaran
    $("#ProsesTempo").on("submit", function (e) {
        e.preventDefault();
        
        // Tombol loading
        $("#ButtonTempo").html('Loading..');
        $("#ButtonTempo").prop("disabled", true);
        let ButtonElement = '<i class="bi bi-save"></i> Simpan';
        
        // Ambil data form
        let formData = new FormData(this);

        // Kirim data ke server
        $.ajax({
            url         : "_Page/UtangPiutang/ProsesTempo.php",
            type        : "POST",
            data        : formData,
            contentType : false,
            processData : false,
            dataType    : "json",
            success: function (response) {
                //Apabila Proses Berhasil
                if (response.status === "Success") {
                    $("#ButtonTempo").html(ButtonElement).prop("disabled", false);
                    $('#NotifikasiTempo').html('');
                    
                    //Tutup Modal
                    $('#ModalTempo').modal('hide');
                    
                    //Tampilkan Alert
                    showToast(
                        'success',
                        'Berhasil',
                        'Tempo Pembayaran Berhasil Diperbaharui!.'
                    );
                    
                    //Reload Data
                    ShowCount();
                    ShowUtangPiutangOperasional();
                    ShowUtangPiutangJualBeli();

                } else {
                    // Tampilkan pesan error
                    $("#NotifikasiTempo").html(
                        `<div class="alert alert-danger" role="alert">${response.message}</div>`
                    );
                    $("#ButtonTempo").html(ButtonElement).prop("disabled", false);
                }
            },
            error: function () {
                $("#NotifikasiTempo").html(
                    '<div class="alert alert-danger" role="alert">Terjadi kesalahan pada sistem. Silakan coba lagi.</div>'
                );
                $("#ButtonTempo").html(ButtonElement).prop("disabled", false);
            },
        });
    });

    //Modal Hapus Tempo
    $('#ModalHapusTempo').on('show.bs.modal', function (e) {

        //Tangkap id_transaksi_jual_beli dari modal detail
        var id       = $(e.relatedTarget).data('id');
        var kategori = $(e.relatedTarget).data('kategori');

        //Kosongkan 'NotifikasiHapusTempo'
        $("#NotifikasiHapusTempo").html("");

        // Loading Form
        $("#FormHapusTempo").html("Loading...");

        // Disable Button
        $("#ButtonHapusTempo").prop("disabled", true);

        //Buka Detail Transaksi
        $.ajax({
            type    : 'POST',
            url     : '_Page/UtangPiutang/FormHapusTempo.php',
            data    : {id: id, kategori: kategori},
            dataType: 'JSON',
            success : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;

                // Apabila berhasil
                if(status=='success'){
                    
                    // Tangkap HTML
                    var html = response.html;

                    //Tampilkan Form
                    $("#FormHapusTempo").html(html);

                    // Kosongkan Notifikasi
                    $("#NotifikasiHapusTempo").html(`
                        <div class="alert alert-warning text-center mt-3" role="alert">
                            <small>
                                <b>Penting!</b><br>
                                Menghapus informasi tanggal jatuh tempo akan menyebabkan anda kehilangan informasi waktu/batas akhir pembayaran Utang/Piutang.<br>
                                <i>Apakah anda yakin akan menghapus informasi tersebut?</i>
                            </small>
                        </div>
                    `);

                    // Enable Button
                    $("#ButtonHapusTempo").prop("disabled", false);
                }else{
                    //Kosongkan Form
                    $("#FormHapusTempo").html('');

                    // Disable Button
                    $("#ButtonHapusTempo").prop("disabled", true);

                    // Tampilkan Notifikasi
                    $("#NotifikasiHapusTempo").html(`
                        <div class="alert alert-danger text-center mt-3" role="alert">
                            <small><b>Oppss!</b><br>Terjadi kesalahan pada sistem.<br> Pesan : ${message}</small>
                        </div>
                    `);
                }
                
            },
            error: function () {
                //Kosongkan Form
                $("#FormHapusTempo").html('');

                // Tampilkan Notifikasi
                $("#NotifikasiHapusTempo").html(
                    '<div class="alert alert-danger text-center mt-3" role="alert"><small><b>Oppss!</b><br>Terjadi kesalahan pada sistem.<br> Silakan coba lagi.</small></div>'
                );

                // Disable Button
                $("#ButtonHapusTempo").prop("disabled", true);
            },
        });
    });

    //Proses Hapus Tempo
    $("#ProsesHapusTempo").on("submit", function (e) {
        e.preventDefault();
        
        // Tombol loading
        $("#ButtonHapusTempo").html('Loading..');
        $("#ButtonHapusTempo").prop("disabled", true);
        let ButtonElement = '<i class="bi bi-save"></i> Simpan';
        
        // Ambil data form
        let formData = new FormData(this);

        // Kirim data ke server
        $.ajax({
            url         : "_Page/UtangPiutang/ProsesHapusTempo.php",
            type        : "POST",
            data        : formData,
            contentType : false,
            processData : false,
            dataType    : "JSON",
            success: function (response) {
                //Apabila Proses Berhasil
                if (response.status === "Success") {
                    $("#ButtonHapusTempo").html(ButtonElement).prop("disabled", false);
                    $('#NotifikasiHapusTempo').html('');
                    
                    //Tutup Modal
                    $('#ModalHapusTempo').modal('hide');
                    
                    //Tampilkan Alert
                    showToast(
                        'success',
                        'Berhasil',
                        'Tempo Pembayaran Berhasil Dihapus!.'
                    );
                    
                    //Reload Data
                    ShowCount();
                    ShowUtangPiutangOperasional();
                    ShowUtangPiutangJualBeli();

                } else {
                    // Tampilkan pesan error
                    $("#NotifikasiHapusTempo").html(
                        `<div class="alert alert-danger" role="alert">${response.message}</div>`
                    );
                    $("#ButtonHapusTempo").html(ButtonElement).prop("disabled", false);
                }
            },
            error: function () {
                $("#NotifikasiHapusTempo").html(
                    '<div class="alert alert-danger" role="alert">Terjadi kesalahan pada sistem. Silakan coba lagi.</div>'
                );
                $("#ButtonHapusTempo").html(ButtonElement).prop("disabled", false);
            },
        });
    });

});
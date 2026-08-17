//Fungsi Menampilkan Tabel Sesi
function ShowSesi() {

    // Target And Filter
    let target = $('#TabelSesi');
    let data   = $('#ProsesFilter').serialize();

    target.addClass('blur-loading');

    $.ajax({
        type: 'POST',
        url: '_Page/StockOpename/TabelSesi.php',
        data: data,
        dataType: 'json',
        success: function(res) {

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

//Fungsi Menampilkan Detail Sesi
function ShowDetailSesi(id_stock_opname) {
    $.ajax({
        type    : 'POST',
        url     : '_Page/StockOpename/InfoSesi.php',
        data    : {id_stock_opname: id_stock_opname},
        dataType: 'JSON',
        success: function(response) {
            // Status Dan Pesan
            var status  = response.status;
            var message = response.message;

            if(status=='success'){
                var html = response.html;
                $('#info_sesi_stock_opename').html(html);
            }else{
                $('#info_sesi_stock_opename').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
            }
        }
    });
}

//Fungsi Menampilkan Barang
function ShowBarang() {
    
    // Target And Filter
    let target = $('#TabelBarang');
    let data   = $('#ProsesFilterBarang').serialize();

    target.addClass('blur-loading');

    $.ajax({
        type    : 'POST',
        url     : '_Page/StockOpename/TabelBarang.php',
        data    : data,
        dataType: 'json',
        success : function(res) {

            if(res.status === "success"){

                target.fadeOut(150, function () {
                    target.html(res.html).fadeIn(150);
                });

                // Update info page
                $('#page_info_barang').html('Page ' + res.page + ' Of ' + res.total_page);

                // Handle tombol
                $('#prev_button_barang').prop('disabled', res.page <= 1);
                $('#next_button_barang').prop('disabled', res.page >= res.total_page);

            }else{
                target.html(res.html);
            }

            target.removeClass('blur-loading');
        }
    });
}

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

// -------------------------
// Inisialisasi Tampilan
// -------------------------
$(document).ready(function() {
    //Menampilkan Sesi Pertama Kali (tampilkan data_view dan sembunyikan detail_view)
    $('#data_view').show();
    $('#detail_view').hide();

    // Tampilkan Data Tabel
    ShowSesi();

    //Ketika keyword By Diubah
    $('#keyword_by_sesi').change(function(){
        var keyword_by_sesi = $('#keyword_by_sesi').val();
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/StockOpename/FormFilterKeywordSesi.php',
            data 	    :  {keyword_by: keyword_by_sesi},
            success     : function(data){
                $('#FormFilterKeywordSesi').html(data);
            }
        });
    });

    //Submit Filter Sesi
    $('#ProsesFilter').submit(function(){
        // Kembalikan ke halaman 1
        $('#page').val(1);

        // Reload Data
        ShowSesi();

        // Tutup Modal
        $('#ModalFilterSesi').modal('hide');
    });
    
    //Pagging Sesi
    $(document).on('click', '#next_button', function() {
        var page_now = parseInt($('#page').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page = page_now + 1;
        $('#page').val(next_page);
        ShowSesi(0);
    });
    $(document).on('click', '#prev_button', function() {
        var page_now = parseInt($('#page').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page = page_now - 1;
        $('#page').val(next_page);
        ShowSesi(0);
    });

    //Proses Tambah Sesi
    $('#ProsesTambahSesi').submit(function(){

        // Tangkap Data
        var ProsesTambahSesi = $('#ProsesTambahSesi').serialize();

        // Tombol
        var TombolSimpanSesi = $('#TombolSimpanSesi').html();

        // Loading Tombol
        $('#TombolSimpanSesi').html('...');

        // Clear Notifikasi Text
        $('#NotifikasiTambahSesi').html("");

        // Disable tombol
        $('#TombolSimpanSesi').prop('disabled', true);

        // Insert Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/StockOpename/ProsesTambahSesi.php',
            dataType    : 'JSON',
            data 	    :  ProsesTambahSesi,
            success     : function(response){

                // Status & message
                let status = response.status;
                let message = response.message;

                // Jika Berhasil
                if(status=='success'){
                    //tutup modal
                    $('#ModalTambahSesi').modal('hide');

                    //Reset halaman
                    $('#page').val(1);

                    //Reset Form
                    $('#ProsesTambahSesi')[0].reset();

                    //Tampilkan Data
                    ShowSesi();

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiTambahSesi').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#NotifikasiTambahSesi').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#TombolSimpanSesi').prop('disabled', false);
                $('#TombolSimpanSesi').html(TombolSimpanSesi);
            }
        });
    });

    //Modal Detail Sesi
    $(document).on('click', '.modal_detail_sesi', function () {
        var id_stock_opname   = $(this).data('id');

        // Reset Filter Barang (Rincian SO)
        $('#ProsesFilterBarang')[0].reset();

        // Tempelkan ke 'put_id_stock_opname'
        $('#put_id_stock_opname').val(id_stock_opname);

        // tempelkan juga ke 'put_id_stock_opname_filter_barang'
        $('#put_id_stock_opname_filter_barang').val(id_stock_opname);

        // Modal Show
        $('#FormDetailSesi').html('Loading...');

        // Loading
        $('#ModalDetailSesi').modal('show');

        // Disable Tombol
        $('#TombolDetailSesiSelengkapnya').prop('disabled', true);

        // Tampilkan Data Dengan AJAX
        $.ajax({
            type    : 'POST',
            url     : '_Page/StockOpename/FormDetailSesi.php',
            dataType: 'JSON',
            data    : {id_stock_opname: id_stock_opname},
            success : function(response){

                // Status Dan Pesan
                var status  = response.status;
                var message = response.message;

                if(status=='success'){
                    var html = response.html;
                    $('#FormDetailSesi').html(html);

                    // Enable Tombol
                    $('#TombolDetailSesiSelengkapnya').prop('disabled', false);
                }else{
                    $('#FormDetailSesi').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormDetailSesi').html(`<div class="alert alert-danger"><small>Terjadi kesalahan server.</small></div>`);
            },

        });
    });

    // Submit Detail Sesi - Masuk ke tampilan detail Stock Opname
    $('#ProsesDetailSesi').submit(function(){

        // Tangkap 'id_stock_opname'
        var id_stock_opname = $('#put_id_stock_opname').val();

        // Tampilkan 'detail_view' dan sembunyikan 'data_view'
        $('#data_view').hide();
        $('#detail_view').show();

        // Kembali Ke Atas
        $('html, body').scrollTop(0);

        // Tutup modal
        $('#ModalDetailSesi').modal('hide');

        // Tampilkan Detail
        ShowDetailSesi(id_stock_opname);

        // Menampilkan Rincian
        ShowBarang();
    });

    // Ketika Tombol 'KembaliKeSesiSo' di click
    $('#KembaliKeSesiSo').click(function(){

        // Tampilkan 'data_view' dan sembunyikan 'detail_view'
        $('#data_view').show();
        $('#detail_view').hide();

        // Kembali Ke Atas
        $('html, body').scrollTop(0);
    });

    //Modal Edit Sesi
    $('#ModalEditSesi').on('show.bs.modal', function (e) {

        // Tangkap 'id_stock_opname'
        var id_stock_opname = $(e.relatedTarget).data('id');

        //Kosongkan Notifikasi
        $('#NotifikasiEditSesi').html("");

        // Loading Form
        $('#FormEditSesi').html("Loading...");

        // Disable Button
        $('#TombolEditSesi').prop('disabled', true);

        // Tampilkan Form Dengan AJAX
        $.ajax({
            type    : 'POST',
            url     : '_Page/StockOpename/FormEditSesi.php',
            data    : {id_stock_opname: id_stock_opname},
            success : function(response){
                $('#FormEditSesi').html(response);
                $('#TombolEditSesi').prop('disabled', false);
            }
        });

    });

    //Proses Edit Sesi
    $('#ProsesEditSesi').submit(function(){

        // Tangkap Data Dari Form
        var ProsesEditSesi = $('#ProsesEditSesi').serialize();

        // Kosongkan Notifikasi
        $('#NotifikasiEditSesi').html('');

        // Tangkap HTML Button
        var TombolEditSesi = $('#TombolEditSesi').html();

        // Loading Button
        $('#TombolEditSesi').html('...');

        // Disable Button
        $('#TombolEditSesi').prop('disabled', true);

        // Kirim Data ke 'ProsesEditSesi.php' dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/StockOpename/ProsesEditSesi.php',
            dataType    : 'JSON',
            data 	    :  ProsesEditSesi,
            success     : function(response){
                // Status & message
                var status = response.status;
                var message = response.message;

                // Jika Berhasil
                if(status=='success'){
                    // Kosongkan Notifikasi
                    $('#NotifikasiEditSesi').html('');

                    //tutup modal
                    $('#ModalEditSesi').modal('hide');

                    //Tampilkan Data
                    ShowSesi();

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiEditSesi').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#NotifikasiEditSesi').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#TombolEditSesi').prop('disabled', false);
                $('#TombolEditSesi').html(TombolEditSesi);
            }
        });
    });

    //Modal Hapus Sesi
    $('#ModalHapusSesi').on('show.bs.modal', function (e) {

        // Tangkap 'id_stock_opname'
        var id_stock_opname = $(e.relatedTarget).data('id');
        
        //Kosongkan Notifikasi
        $('#NotifikasiHapusSesi').html("");

        // Loading Form
        $('#FormHapusSesi').html("Loading...");

        // Disable Button
        $('#TombolHapusSesi').prop('disabled', true);

        // Tampilkan Form Dengan AJAX
        $.ajax({
            type    : 'POST',
            url     : '_Page/StockOpename/FormHapusSesi.php',
            data    : {id_stock_opname: id_stock_opname},
            dataType: 'JSON',
            success : function(response){
                
                // Status & message
                var status  = response.status;
                var message = response.message;
                var html    = response.html;

                // Tampilkan Form
                $('#FormHapusSesi').html(html);

                // Jika Berhasil
                if(status=='success'){

                    //Kosongkan Notifikasi
                    $('#NotifikasiHapusSesi').html("");

                     // Enable Button
                    $('#TombolHapusSesi').prop('disabled', false);
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiHapusSesi').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#NotifikasiHapusSesi').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            }
        });
    });

    //Proses Hapus Sesi
    $('#ProsesHapusSesi').submit(function(){

        // Tangkap Data Dari Form
        var ProsesHapusSesi = $('#ProsesHapusSesi').serialize();

        // tangkap element tombol
        var TombolHapusSesi = $('#TombolHapusSesi').html();

        // Kosongkan Notifikasi
        $('#NotifikasiHapusSesi').html("");

        // Loading Button
        $('#TombolHapusSesi').html('Loading...');

        // Disable Button
        $('#TombolHapusSesi').prop('disabled', true);

        // Kirim data Ke 'ProsesHapusSesi.php' dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/StockOpename/ProsesHapusSesi.php',
            dataType    : 'JSON',
            data 	    :  ProsesHapusSesi,
            success     : function(response){
                
                // Status & message
                var status  = response.status;
                var message = response.message;

                // Jika Berhasil
                if(status=='success'){
                    
                    //Tutup modal
                    $('#ModalHapusSesi').modal('hide');

                    //Tampilkan Data
                    ShowSesi();

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil dihapus.'
                    );
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiHapusSesi').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#NotifikasiHapusSesi').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#TombolHapusSesi').prop('disabled', false);
                $('#TombolHapusSesi').html(TombolHapusSesi);
            }
        });
    });

    // ---------------------------------------
    // Kelola Barang Pada Rincian Stock Opname
    // ---------------------------------------
    
    //Pagging
    $(document).on('click', '#next_button_barang', function() {
        var page_now_barang = parseInt($('#page_barang').val(), 10);
        var next_page_barang = page_now_barang + 1;
        $('#page_barang').val(next_page_barang);
        ShowBarang(0);
    });
    $(document).on('click', '#prev_button_barang', function() {
        var page_now_barang = parseInt($('#page_barang').val(), 10);
        var next_page_barang = page_now_barang - 1;
        $('#page_barang').val(next_page_barang);
        ShowBarang(0);
    });


    //Submit Filter Barang
    $('#ProsesFilterBarang').submit(function(e){
        e.preventDefault();

        // Kembali Ke Halaman 1
        $('#page_barang').val(1);

        // Tampilkan data
        ShowBarang();

        // Tutup Modal
        $('#ModalFilterBarang').modal('hide');
    });

    // Saat Modal Filter Barang dibuka, pastikan ID sesi terisi
    $('#ModalFilterBarang').on('show.bs.modal', function () {
        var id_stock_opname = $('#put_id_stock_opname').val();
        if(id_stock_opname !== ""){
            $('#put_id_stock_opname_filter_barang').val(id_stock_opname);
        }
        $('#page_barang').val(1);
    });

    //Modal Stock Opename
    $(document).on('click', '.show_modal_stock_opname', function() {

        // Tangkap data dari tombol
        var id_barang       = $(this).data('id_barang');
        var id_stock_opname = $(this).data('id_stock_opname');

        // Kosongkan Notifikasi
        $('#NotifikasiStockOpnameBarang').html('');

        // Tampilkan Modal
        $('#ModalStockOpnameBarang').modal('show');

        // Loading Form
        $('#FormStockOpnameBarang').html('Loading...');

        // Disable Button
        $('#TombolStockOpnameBarang').prop('disabled', true);

        // Tampilkan Form Dengan AJAX
        $.ajax({
            type    : 'POST',
            url     : '_Page/StockOpename/FormStockOpnameBarang.php',
            data    : {id_barang: id_barang, id_stock_opname: id_stock_opname},
            dataType: 'JSON',
            success : function(response){
                
                // Status & message
                var status  = response.status;
                var message = response.message;
                var html    = response.html;

                // Tampilkan Form
                $('#FormStockOpnameBarang').html(html);

                // Jika Berhasil
                if(status=='success'){

                    //Kosongkan Notifikasi
                    $('#NotifikasiStockOpnameBarang').html("");

                     // Enable Button
                    $('#TombolStockOpnameBarang').prop('disabled', false);

                    initializeMoneyInputs();
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiStockOpnameBarang').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#NotifikasiStockOpnameBarang').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            }
        });
    });

    

    //Proses Simpan Stock Opename
    $('#ProsesStockOpnameBarang').submit(function(){

        // Tangkap 'id_stock_opname'
        var id_stock_opname = $('#put_id_stock_opname').val();

        // Tangkap data dari form
        var ProsesStockOpnameBarang = $('#ProsesStockOpnameBarang').serialize();

        // tangkap element tombol
        var TombolStockOpnameBarang = $('#TombolStockOpnameBarang').html();

        // Kosongkan Notifikasi
        $('#NotifikasiStockOpnameBarang').html('');

        // Loading Tombol
        $('#TombolStockOpnameBarang').html('Loading...');

        // Disable tombol
        $('#TombolStockOpnameBarang').prop('disabled', true);

        // Kirim Data Ke 'ProsesStockOpnameBarang'
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/StockOpename/ProsesStockOpnameBarang.php',
            dataType    : 'JSON',
            data 	    :  ProsesStockOpnameBarang,
            success     : function(response){
                
                // Status & message
                var status  = response.status;
                var message = response.message;

                // Jika Berhasil
                if(status=='success'){

                    // Kosongkan Notifikasi
                    $('#NotifikasiStockOpnameBarang').html('');
                    
                    //Tutup modal
                    $('#ModalStockOpnameBarang').modal('hide');

                    // Tampilkan Detail
                    ShowDetailSesi(id_stock_opname);

                    // Menampilkan Rincian
                    ShowBarang();

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiStockOpnameBarang').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#NotifikasiStockOpnameBarang').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#TombolStockOpnameBarang').prop('disabled', false);
                $('#TombolStockOpnameBarang').html(TombolStockOpnameBarang);
            }
        });
    });

    //Modal Stock Opename
    $(document).on('click', '.show_modal_detail_stock_opname_barang', function() {

        // Tangkap data dari tombol
        var id_barang       = $(this).data('id_barang');
        var id_stock_opname = $(this).data('id_stock_opname');

        // Tampilkan Modal
        $('#ModalDetailStockOpnameBarang').modal('show');

        // Loading Form
        $('#FormDetailStockOpnameBarang').html('Loading...');

        // Tampilkan Form Dengan AJAX
        $.ajax({
            type    : 'POST',
            url     : '_Page/StockOpename/FormDetailStockOpnameBarang.php',
            data    : {id_barang: id_barang, id_stock_opname: id_stock_opname},
            success : function(response){
                
                $('#FormDetailStockOpnameBarang').html(response);
            }
        });
    });

    //Modal Export Stock Opename
    $('#ModalExportStockOpnameBarang').on('show.bs.modal', function (e) {
        
        // Tangkap 'id_stock_opname'
        var id_stock_opname = $('#put_id_stock_opname').val();

        // Loading Form
        $('#FormExportStockOpnameBarang').html("Loading...");

        // Disable Button
        $('#TombolExportStockOpnameBarang').prop('disabled', true);

        // Disable Tombol
        $('#TombolExportStockOpnameBarang').prop('disabled', true);

        // Tampilkan Data Dengan AJAX
        $.ajax({
            type    : 'POST',
            url     : '_Page/StockOpename/FormExportStockOpnameBarang.php',
            dataType: 'JSON',
            data    : {id_stock_opname: id_stock_opname},
            success : function(response){

                // Status Dan Pesan
                var status  = response.status;
                var message = response.message;

                if(status=='success'){
                    var html = response.html;
                    $('#FormExportStockOpnameBarang').html(html);

                    // Enable Tombol
                    $('#TombolExportStockOpnameBarang').prop('disabled', false);
                }else{
                    $('#FormExportStockOpnameBarang').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormExportStockOpnameBarang').html(`<div class="alert alert-danger"><small>Terjadi kesalahan server.</small></div>`);
            },

        });
    });

});

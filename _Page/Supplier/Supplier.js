//Fungsi Menampilkan Data
function ShowData() {

    // Target And Filter
    let target = $('#tabel_supplier');
    let data   = $('#ProsesFilter').serialize();

    target.addClass('blur-loading');

    $.ajax({
        type    : 'POST',
        url     : '_Page/Supplier/TabelSupplier.php',
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


//Fungsi Menampilkan Informasi Detail Supplier
function ShowDetailSupplier(id_supplier) {
    //Loading element
    $('#detail_supplier').html('<div class="row"><div class="col-md-12 text-center">Loading...</div></div>');
    $.ajax({
        type        : 'POST',
        url         : '_Page/Supplier/_detail_supplier.php',
        data        : {id_supplier: id_supplier},
        dataType    : "json",
        success: function(response) {
            if(response.status=="Success"){
                $('#detail_supplier').html(`
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="row mb-2">
                                <div class="col-4"><small>ID.Supplier</small></div>
                                <div class="col-8"><small class="text text-muted">${response.dataset.id_supplier}</small></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><small>Nama Supplier</small></div>
                                <div class="col-8"><small class="text text-muted">${response.dataset.nama_supplier}</small></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><small>Email</small></div>
                                <div class="col-8"><small class="text text-muted">${response.dataset.email_supplier}</small></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><small>Kontak</small></div>
                                <div class="col-8"><small class="text text-muted">${response.dataset.kontak_supplier}</small></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><small>Alamat</small></div>
                                <div class="col-8"><small class="text text-muted">${response.dataset.alamat_supplier}</small></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row mb-2">
                                <div class="col-4"><small>PIC</small></div>
                                <div class="col-8"><small class="text text-muted">${response.dataset.pic}</small></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><small>NPWP</small></div>
                                <div class="col-8"><small class="text text-muted">${response.dataset.npwp}</small></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><small>Jumlah Pembelian</small></div>
                                <div class="col-8"><small class="text text-muted">${response.dataset.jumlah_transaksi_format}</small></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><small>Retur Pembelian</small></div>
                                <div class="col-8"><small class="text text-muted">${response.dataset.jumlah_transaksi_retur_format}</small></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><small>Utang Usaha</small></div>
                                <div class="col-8"><small class="text text-muted">${response.dataset.jumlah_transaksi_kredit_format}</small></div>
                            </div>
                        </div>
                    </div>
                `);
            }else{
                //Apabila Response Error
                Swal.fire({
                    title: "Opss!",
                    text: response.message,
                    icon: "error",
                    confirmButtonText: "Tutup"
                }).then((result) => {
                    if (result.isConfirmed || result.isDismissed) {
                        // Redirect ke halaman yang diinginkan
                        window.location.href = "index.php?Page=Supplier"; 
                    }
                });
            }
        },
        error: function () {
            //Apabila format json gagal dibaca
            Swal.fire({
                title: "Opss!",
                text: "Terjadi kesalahan pada saat akan menampilkan detail supplier",
                icon: "error",
                confirmButtonText: "Tutup"
            }).then((result) => {
                if (result.isConfirmed || result.isDismissed) {
                    window.location.href = "index.php?Page=Supplier"; 
                }
            });
        },
    });
}

//Fungsi Menampilkan Riwayat Transaksi
function ShowRiwayatTransaksi(id_supplier) {
    //Tempelkan id_supplier ke form filter
    $('#put_id_supplier_on_riwayat_transaksi').val(id_supplier);
    var ProsesFilterriwayatTransaksi = $('#ProsesFilterriwayatTransaksi').serialize();
    //Loading
    $('#TabelTransaksiSupplier').html(`
        <tr>
            <td colspan="8" class="text-center">Loading...</td>
        </tr>
    `);
    $.ajax({
        type    : 'POST',
        url     : '_Page/Supplier/TabelRiwayatTransaksi.php',
        data    : ProsesFilterriwayatTransaksi,
        success: function(data) {
            $('#TabelTransaksiSupplier').html(data);
        }
    });
}

//Fungsi Menampilkan Riwayat Riwayat Transaksi
function ShowRiwayatRincianTransaksi(id_supplier) {
    //Tempelkan id_supplier ke form filter
    $('#put_id_supplier_on_riwayat_rincian_transaksi').val(id_supplier);
    var ProsesFilterRincian = $('#ProsesFilterRincian').serialize();
    //Loading
    $('#TabelRincianTransaksiSupplier').html(`
        <tr>
            <td colspan="10" class="text-center">Loading...</td>
        </tr>
    `);
    $.ajax({
        type    : 'POST',
        url     : '_Page/Supplier/TabelRiwayatRincianTransaksi.php',
        data    : ProsesFilterRincian,
        success: function(data) {
            $('#TabelRincianTransaksiSupplier').html(data);
        }
    });
}

$(document).ready(function() {

    //Inisiasi Data Pertama Kali
    ShowData();

    // Auto Focus ModalFilter
    $('#ModalFilter').on('shown.bs.modal', function () {
        $('#keyword').trigger('focus');
    });

    //Ketika Submit Filter
    $('#ProsesFilter').submit(function(){
        
        //Kembalikan ke halaman 1
        $('#page').val(1);
        
        // Reload Data
        ShowData();

        //Tutup Modal
        $('#ModalFilter').modal('hide');
    });
    
    //Pagging
    $(document).on('click', '#next_button', function() {
        var page_now = parseInt($('#page').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page = page_now + 1;
        $('#page').val(next_page);
        ShowData(0);
    });
    $(document).on('click', '#prev_button', function() {
        var page_now = parseInt($('#page').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page = page_now - 1;
        $('#page').val(next_page);
        ShowData(0);
    });
    
    // Auto Focus ModalTambahSupplier
    $('#ModalTambahSupplier').on('shown.bs.modal', function () {
        $('#nama_supplier').trigger('focus');
    });

    //Proses Tambah Supplier
    $('#ProsesTambahSupplier').submit(function(){
        
        // Tangkap Data
        var ProsesTambahSupplier = $('#ProsesTambahSupplier').serialize();

        // Tombol
        var TombolTambahSupplier = $('#TombolTambahSupplier').html();

        // Loading Tombol
        $('#TombolSimpanSesi').html('...');

        // Clear Notifikasi Text
        $('#NotifikasiTambahSupplier').html("");

        // Disable tombol
        $('#TombolTambahSupplier').prop('disabled', true);

        // Insert Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Supplier/ProsesTambahSupplier.php',
            dataType    : 'JSON',
            data 	    :  ProsesTambahSupplier,
            success     : function(response){

                // Status & message
                let status = response.status;
                let message = response.message;

                // Jika Berhasil
                if(status=='success'){
                    //tutup modal
                    $('#ModalTambahSupplier').modal('hide');

                    //Reset halaman
                    $('#page').val(1);

                    //Reset Form
                    $('#ProsesTambahSupplier')[0].reset();

                    //Tampilkan Data
                    ShowData();

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiTambahSupplier').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
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
                $('#NotifikasiTambahSupplier').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#TombolTambahSupplier').prop('disabled', false);
                $('#TombolTambahSupplier').html(TombolSimpanSesi);
            }
        });
    });

    //Ketika menampilkan detail supplier
    if ($("#put_id_supplier_on_detail").length) {
        var id_supplier=$("#put_id_supplier_on_detail").val();

        //Menampilkan Detail
        ShowDetailSupplier(id_supplier);

        //Menampilkan Riwayat Transaksi
        ShowRiwayatTransaksi(id_supplier);

        //Menampilkan Riwayat Rincian Transaksi
        ShowRiwayatRincianTransaksi(id_supplier);

        //Pagging Riwayat Transaksi
        $(document).on('click', '#next_button_transaksi', function() {
            var page_now = parseInt($('#page_riwayat_transaksi').val(), 10);
            var next_page = page_now + 1;
            $('#page_riwayat_transaksi').val(next_page);
            ShowRiwayatTransaksi(id_supplier);
        });
        $(document).on('click', '#prev_button_transaksi', function() {
            var page_now = parseInt($('#page_riwayat_transaksi').val(), 10);
            var next_page = page_now - 1;
            $('#page_riwayat_transaksi').val(next_page);
            ShowRiwayatTransaksi(id_supplier);
        });

        //Pagging Riwayat Rincian Transaksi
        $(document).on('click', '#next_button_rincian_transaksi', function() {
            var page_now = parseInt($('#page_riwayat_rincian_transaksi').val(), 10);
            var next_page = page_now + 1;
            $('#page_riwayat_rincian_transaksi').val(next_page);
            ShowRiwayatRincianTransaksi(id_supplier);
        });
        $(document).on('click', '#prev_button_rincian_transaksi', function() {
            var page_now = parseInt($('#page_riwayat_rincian_transaksi').val(), 10);
            var next_page = page_now - 1;
            $('#page_riwayat_rincian_transaksi').val(next_page);
            ShowRiwayatRincianTransaksi(id_supplier);
        });

        //Ketiika keyword_by_riwayat_transaksi Diubah
        $('#keyword_by_riwayat_transaksi').change(function(){
            var keyword_by_riwayat_transaksi = $('#keyword_by_riwayat_transaksi').val();
            $('#FormFilterKeywordRiwayatTransaksi').html('Loading...');
            $.ajax({
                type 	    : 'POST',
                url 	    : '_Page/Supplier/FormFilterKeywordRiwayatTransaksi.php',
                data 	    :  {keyword_by_riwayat_transaksi: keyword_by_riwayat_transaksi},
                success     : function(data){
                    $('#FormFilterKeywordRiwayatTransaksi').html(data);
                }
            });
        });

        //Ketiika keyword_by_riwayat_rincian_transaksi Diubah
        $('#keyword_by_riwayat_rincian_transaksi').change(function(){
            var keyword_by_riwayat_rincian_transaksi = $('#keyword_by_riwayat_rincian_transaksi').val();
            $('#FormFilterKeywordRiwayatRincianTransaksi').html('Loading...');
            $.ajax({
                type 	    : 'POST',
                url 	    : '_Page/Supplier/FormFilterKeywordRiwayatRincianTransaksi.php',
                data 	    :  {keyword_by_riwayat_rincian_transaksi: keyword_by_riwayat_rincian_transaksi},
                success     : function(data){
                    $('#FormFilterKeywordRiwayatRincianTransaksi').html(data);
                }
            });
        });
        
        //Ketika Submit Filter
        $('#ProsesFilterriwayatTransaksi').submit(function(){
            
            //Kembalikan ke halaman 1
            $('#page_riwayat_transaksi').val(1);

            //Tampilkan Data
            ShowRiwayatTransaksi(id_supplier);

            //Tutup 'ModalFilterriwayatTransaksi'
            $('#ModalFilterriwayatTransaksi').modal('hide');
        });

        //Ketika Submit ProsesFilterRincian 
        $('#ProsesFilterRincian').submit(function(){
            
            //Kembalikan ke halaman 1
            $('#page_riwayat_rincian_transaksi').val(1);

            //Tampilkan Data
            ShowRiwayatRincianTransaksi(id_supplier);

            //Tutup 'ModalFilterRincian'
            $('#ModalFilterRincian').modal('hide');
        });
    }

    //Modal Export Supplier
    $('#ModalExportSupplier').on('show.bs.modal', function (e) {
        $('#FormExportSupplier').html("Loading...");
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Supplier/FormExportSupplier.php',
            success     : function(data){
                $('#FormExportSupplier').html(data);
            }
        });
    });

    

    
    //Detail Supplier
    $('#ModalDetailSupplier').on('show.bs.modal', function (e) {
        var id_supplier= $(e.relatedTarget).data('id');
        $('#FormDetailSupplier').html("Loading...");
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Supplier/FormDetailSupplier.php',
            data        : {id_supplier: id_supplier},
            success     : function(data){
                $('#FormDetailSupplier').html(data);
            }
        });
    });

    //Modal Edit Supplier
    $('#ModalEditSupplier').on('show.bs.modal', function (e) {

        // Tangkap 'id_supplier'
        var id_supplier = $(e.relatedTarget).data('id');

        // Loading Form 'FormEditSupplier'
        $('#FormEditSupplier').html("Loading...");

        // Kosongkan Notifikasi 'NotifikasiEditSupplier'
        $('#NotifikasiEditSupplier').html("");

        // Disable Button
        $('#TombolEditSupplier').prop('disabled', true);

        // Tampilkan Form Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Supplier/FormEditSupplier.php',
            data        : {id_supplier: id_supplier},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;
                var html    = response.html;

                // Jika Berhasil
                if(status=='success'){

                    // Tampilkan Form
                    $('#FormEditSupplier').html(html);

                    // Enable Tombol
                    $('#TombolEditSupplier').prop('disabled', false);
                }else{
                    $('#NotifikasiEditSupplier').html('<div class="alert alert-danger text-center"><small><b>Opss!</b> '+message+'</small></div>');
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
                $('#NotifikasiEditSupplier').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            }
        });
    });

    //Proses Edit Supplier
    $('#ProsesEditSupplier').submit(function(e){
        e.preventDefault();

        // Menangkap Data
        var form = $('#ProsesEditSupplier')[0];
        var data = new FormData(form);

        // Tangkap Elemnt Tombol
        var TombolEditSupplier = $('#TombolEditSupplier').html();

        // Disable Tombol 'TombolEditSupplier'
        $('#TombolEditSupplier').prop('disabled', true);

        // Loading Tombol 'TombolEditSupplier'
        $('#TombolEditSupplier').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');

        // Kosongkan Notifikasi  'NotifikasiEditSupplier'
        $('#NotifikasiEditSupplier').html('');
        
        $.ajax({
            type       : 'POST',
            url        : '_Page/Supplier/ProsesEditSupplier.php',
            data       : data,
            cache      : false,
            processData: false,
            contentType: false,
            dataType   : 'JSON',
            enctype    : 'multipart/form-data',
            success    : function(response){

                // Message & Status
                var status  = response.status;
                var message = response.message;

                // Apabila Berhasil
                if(status=='success'){

                    //Tutup Modal
                    $('#ModalEditSupplier').modal('hide');
                    
                    // Reload Data
                    ShowData(0);

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );

                    //Jika Posisi Sedang Dalam Detail Supplier
                    if ($("#put_id_supplier_on_detail").length) {
                        var id_supplier=$("#put_id_supplier_on_detail").val();
                        ShowDetailSupplier(id_supplier);
                    }

                }else{
                    $('#NotifikasiEditSupplier').html('<div class="alert alert-danger"><small>' + message + '</small></div>');
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
                $('#NotifikasiEditSupplier').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            },
            complete: function(){
                // Kembalikan Tombol
                $('#TombolEditSupplier').prop('disabled', false);
                $('#TombolEditSupplier').html(TombolEditSupplier);
            }
        });
    });

    //Modal Hapus Supplier
    $('#ModalHapusSupplier').on('show.bs.modal', function (e) {

        // Tangkap 'id_supplier'
        var id_supplier = $(e.relatedTarget).data('id');

        // Loading Form 'FormHapusSupplier'
        $('#FormHapusSupplier').html("Loading...");

        // Kosongkan Notifikasi 'NotifikasiHapusSupplier'
        $('#NotifikasiHapusSupplier').html("");

        // Disable Button
        $('#TombolHapusSupplier').prop('disabled', true);

        // Tampilkan Form Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Supplier/FormHapusSupplier.php',
            data        : {id_supplier: id_supplier},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;
                var html    = response.html;

                // Jika Berhasil
                if(status=='success'){

                    // Tampilkan Form
                    $('#FormHapusSupplier').html(html);

                    // Enable Tombol
                    $('#TombolHapusSupplier').prop('disabled', false);
                }else{
                    $('#NotifikasiHapusSupplier').html('<div class="alert alert-danger text-center"><small><b>Opss!</b> '+message+'</small></div>');
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
                $('#NotifikasiHapusSupplier').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            }
        });
    });

    //Proses Hapus Supplier
    $('#ProsesHapusSupplier').submit(function(e){
        e.preventDefault();

        // Tangkap data
        var form = $('#ProsesHapusSupplier')[0];
        var data = new FormData(form);

        // Element Tombol
        var TombolHapusSupplier = $('#TombolHapusSupplier').html();

        // Disable Button 'TombolHapusSupplier'
        $('#TombolHapusSupplier').prop('disabled', true);

        // Loading Button 'TombolHapusSupplier'
        $('#TombolHapusSupplier').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');

        // Bersihkan Notifikasi
        $('#NotifikasiHapusSupplier').html('');
       
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Supplier/ProsesHapusSupplier.php',
            data 	    :  data,
            cache       : false,
            processData : false,
            contentType : false,
            enctype     : 'multipart/form-data',
            dataType    : 'JSON',
            success     : function(response){
                
                // Status & Message
                var status  = response.status;
                var message = response.message;
                var html    = response.html;

                // Jika Berhasil
                if(status=='success'){

                    //Tutup Modal
                    $('#ModalHapusSupplier').modal('hide');
                    
                    // Reload Data
                    ShowData(0);

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil dihapus.'
                    );

                }else{
                    $('#NotifikasiHapusSupplier').html('<div class="alert alert-danger text-center"><small><b>Opss!</b> '+message+'</small></div>');
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
                $('#NotifikasiHapusSupplier').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            },
            complete: function(){
                // Kembalikan Tombol
                $('#TombolHapusSupplier').prop('disabled', false);
                $('#TombolHapusSupplier').html(TombolHapusSupplier);
            }
        });
    });

    //Modal Detail Transaksi
    $('#ModalDetailTransaksi').on('show.bs.modal', function (e) {
        //Tangkap id_transaksi_jual_beli dari modal detail
        var id_transaksi_jual_beli = $(e.relatedTarget).data('id');
        
        //Buka Detail Barang
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Pembelian/detail_pembelian.php',
            data        : {id_transaksi_jual_beli: id_transaksi_jual_beli},
            dataType    : "json",
            success     : function(response){
                if(response.status=="Success"){

                    var data = response.dataset;
                    var list_rincian = response.list_rincian;
                    
                    //Tempelkan Ke Element
                    $('#FormDetailTransaksi').html(`
                        <input type="hidden" name="id" value="${id_transaksi_jual_beli}">
                        <div class="row mb-2">
                            <div class="col-4"><small>Tanggal</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.tanggal}</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>Supplier</small></div>
                            <div class="col-8">
                                <a href="javascriipt:void(0);" data-bs-toggle="modal" data-bs-target="#ModalListSupplierEdit" data-id="${id_transaksi_jual_beli}" data-mode="List">
                                    <small class="text text-grayish">${data.nama_supplier}</small>
                                </a>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>Kategori</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.kategori}</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>Subtotal</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.subtotal_rp}</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>PPN</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.ppn_rp}</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>Diskon</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.diskon_rp}</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>Total</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.total_rp}</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>Cash</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.cash_rp}</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>Kembalian</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.kembalian_rp}</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>Status</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.status}</small>
                            </div>
                        </div>
                    `);
                    var rincianList = response.list_rincian;
                    var html = "";

                    // Inisialisasi total
                    var totalPpn = 0;
                    var totalDiskon = 0;
                    var totalSubtotal = 0;

                    if (rincianList.length > 0) {
                        $.each(rincianList, function(index, item) {
                            html += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.nama_barang}</td>
                                    <td>${item.qty}</td>
                                    <td class="text-end">${item.harga_rp}</td>
                                    <td class="text-end">${item.ppn_rp}</td>
                                    <td class="text-end">${item.diskon_rp}</td>
                                    <td class="text-end">${item.subtotal_rp}</td>
                                </tr>
                            `;

                            // Hitung total
                            totalPpn += parseFloat(item.ppn);
                            totalDiskon += parseFloat(item.diskon);
                            totalSubtotal += parseFloat(item.subtotal);
                        });

                        // Tambahkan baris total di akhir tabel
                        html += `
                            <tr class="fw-bold bg-light">
                                <td colspan="4" class="text-center">Total</td>
                                <td class="text-end">Rp ${totalPpn.toLocaleString("id-ID")}</td>
                                <td class="text-end">Rp ${totalDiskon.toLocaleString("id-ID")}</td>
                                <td class="text-end">Rp ${totalSubtotal.toLocaleString("id-ID")}</td>
                            </tr>
                        `;
                    } else {
                        html = '<tr><td colspan="7" class="text-center">Tidak ada rincian transaksi</td></tr>';
                    }

                    // Masukkan ke dalam tabel
                    $("#ListRincianTransaksi").html(html);

                    //Enable tombol
                    $('#ButtonSelengkapnyaTransaksi').prop("disabled", false);
                }else{
                    //Tempelkan Notifikasi
                    $('#FormDetailTransaksi').html(
                        `<div class="alert alert-danger" role="alert">${response.message}</div>`
                    );
                    //Disable tombol
                    $('#ButtonSelengkapnyaTransaksi').prop("disabled", true);
                }
            },
            error: function () {
                //Tempelkan Notifikasi
                $('#FormDetailTransaksi').html(
                    '<div class="alert alert-danger" role="alert">Terjadi kesalahan pada sistem. Silakan coba lagi.</div>'
                );
                //Disable tombol
                $('#ButtonSelengkapnyaTransaksi').prop("disabled", true);
            },
        });
    });

    //Modal Export Transaksi
    $('#ModalExportTransaksi').on('show.bs.modal', function (e) {
        //Kosongkan Notifikasi
        $('#NotifikasiExportTransaksi').html("");

        //Kembalikan Button
        $('#ButtonExportTransaksi').prop('disabled', false).html('<i class="bi bi-download"></i> Download/Export');

        //Tangkap ID Supplier
        var id_supplier= $(e.relatedTarget).data('id');
        
        //Tempelkan Ke Form
        $('#put_id_supplier_for_export_transaksi').val(id_supplier);
        
        //Buka Detail Supplier
        $.ajax({
            type        : 'POST',
            url         : '_Page/Supplier/_detail_supplier.php',
            data        : {id_supplier: id_supplier},
            dataType    : "json",
            success: function(response) {
                if(response.status=="Success"){
                    $('#put_nama_supplier').html(response.dataset.nama_supplier);
                }else{
                    $('#put_nama_supplier').html('<small class="text-danger">'+response.message+'</small>');
                }
            },
            error: function () {
                //Apabila format json gagal dibaca
                $('#put_nama_supplier').html('<small class="text-danger">Error</small>');
            },
        });
    });

    //Proses Export Transaksi
    $('#ProsesExportTransaksi').on('submit', function (e) {
        e.preventDefault();
        
        let periodeAwal = $('#periode_transaksi_1').val();
        let periodeAkhir = $('#periode_transaksi_2').val();
        let idSupplier = $('#put_id_supplier_for_export_transaksi').val();
        let button = $('#ButtonExportTransaksi');
        let notif = $('#NotifikasiExportTransaksi');
        
        // Validasi periode
        if (periodeAwal && periodeAkhir && periodeAkhir < periodeAwal) {
            notif.html('<div class="alert alert-danger">Periode akhir tidak boleh lebih kecil dari periode awal.</div>');
            return;
        }
        
        // Disable tombol dan tampilkan loading
        button.prop('disabled', true).html('<i class="bi bi-arrow-clockwise"></i> Memproses...');
        notif.html('');
        
        $.ajax({
            url: '_Page/Supplier/ProsesExportRiwayatTransaksi.php',
            type: 'POST',
            data: {
                id_supplier: idSupplier,
                periode_awal: periodeAwal,
                periode_akhir: periodeAkhir
            },
            xhrFields: {
                responseType: 'blob' // Menerima file sebagai blob
            },
            success: function (response, status, xhr) {
                let filename = "Riwayat_Transaksi.xlsx";
                let blob = new Blob([response], { type: xhr.getResponseHeader('Content-Type') });
                let link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                $('#ModalExportTransaksi').modal('hide');
                $('#ProsesExportTransaksi')[0].reset();
                
                // Tampilkan Swal
                Swal.fire(
                    'Success!',
                    'Download Riwayat Transaksi Berhasil!',
                    'success'
                );
            },
            error: function () {
                notif.html('<div class="alert alert-danger">Terjadi kesalahan dalam proses ekspor.</div>');
            },
            complete: function () {
                // Kembalikan tombol ke kondisi semula
                button.prop('disabled', false).html('<i class="bi bi-download"></i> Download/Export');
            }
        });
    });


    //Modal Export Rincian Transaksi
    $('#ModalExportRincian').on('show.bs.modal', function (e) {
        //Kosongkan Notifikasi
        $('#NotifikasiExportRincian').html('');

        //Kembalikan Button
        $('#ButtonExportRincian').prop('disabled', false).html('<i class="bi bi-download"></i> Download/Export');

        //Tangkap ID Supplier
        var id_supplier= $(e.relatedTarget).data('id');
        
        //Tempelkan Ke Form
        $('#put_id_supplier_for_export_rincian_transaksi').val(id_supplier);
        
        //Buka Detail Supplier
        $.ajax({
            type        : 'POST',
            url         : '_Page/Supplier/_detail_supplier.php',
            data        : {id_supplier: id_supplier},
            dataType    : "json",
            success: function(response) {
                if(response.status=="Success"){
                    $('#put_nama_supplier_for_export_rincian').html(response.dataset.nama_supplier);
                }else{
                    $('#put_nama_supplier_for_export_rincian').html('<small class="text-danger">'+response.message+'</small>');
                }
            },
            error: function () {
                //Apabila format json gagal dibaca
                $('#put_nama_supplier_for_export_rincian').html('<small class="text-danger">Error</small>');
            },
        });
    });
    //Proses Export Rincian
    $('#ProsesExportRincian').on('submit', function() {
        // Menutup modal setelah form di-submit
        $('#ModalExportRincian').modal('hide');

        // Mereset form setelah beberapa detik (opsional)
        setTimeout(function() {
            $('#ProsesExportRincian')[0].reset();
        }, 1000); // Delay 1 detik sebelum reset form
    });


});







$('#RincianBarang').click(function(){
    $('#HalamanDetailSupplier').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');
    var GetIdSupplier =$('#GetIdSupplier').html();
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Supplier/RincianBarang.php',
        data 	    :  {GetIdSupplier: GetIdSupplier},
        success     : function(data){
            $('#HalamanDetailSupplier').html(data);
        }
    });
});
$('#RiwayatTransaksi').click(function(){
    $('#HalamanDetailSupplier').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');
    var GetIdSupplier =$('#GetIdSupplier').html();
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Supplier/RiwayatTransaksi.php',
        data 	    :  {GetIdSupplier: GetIdSupplier},
        success     : function(data){
            $('#HalamanDetailSupplier').html(data);
        }
    });
});

// Modal Import
$('#ModalImportSupplier').on('show.bs.modal', function (e) {
    //Kosongkan Notifikasi
    $('#NotifikasiImportSupplier').html('<tr><td colspan="7" class="text-center"><small>No Data</small></td></tr>');

    //Disabled Button
    $('#TombolImport').prop('disabled', true);

    // Reset Form
    $('#ProsesImportSupplier')[0].reset();
});

//Validasi File Import
$('#file_supplier').on('change', function () {
    var file = this.files[0];
    var validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
    var maxSize = 10 * 1024 * 1024; // 10 MB

    // Reset notifikasi
    $('#NotifikasiImportSupplier').html('');

    if (file) {
        if (!validTypes.includes(file.type)) {
            $('#NotifikasiImportSupplier').html('<tr><td colspan="7" class="text-center"><small class="text-danger">Tipe File Tidak Valid</small></td></tr>');
            $(this).val(''); // Reset input file
            return;
        }

        if (file.size > maxSize) {
            $('#NotifikasiImportSupplier').html('<tr><td colspan="7" class="text-center"><small class="text-danger">Ukuran file terlalu besar. Maksimal 10 MB.</small></td></tr>');
            $(this).val(''); // Reset input file
            return;
        }
        $('#NotifikasiImportSupplier').html('<tr><td colspan="7" class="text-center"><small class="text-success">Siap Import</small></td></tr>');
        $('#TombolImport').prop('disabled', false);
    }
});

//Proses Import
$('#ProsesImportSupplier').on('submit', function (e) {
    e.preventDefault();

    // Tangkap Data
    var formData = new FormData(this);

    // Loading Notifikasi 'NotifikasiImportSupplier'
    $('#NotifikasiImportSupplier').html('<tr><td colspan="7" class="text-center"><small>Loading...</small></td></tr>');

    // Disabled 'TombolImport' dan 'TombolSelesai'
    $('#TombolImport').prop('disabled', true);
    $('#TombolSelesai').prop('disabled', true);

    // Proses Data Dengan 'AJAX'
    $.ajax({
        url        : '_Page/Supplier/ProsesImportSupplier.php',
        type       : 'POST',
        data       : formData,
        dataType   : 'JSON',
        contentType: false,
        processData: false,
        beforeSend : function () {
            $('#NotifikasiImportSupplier').html('<tr><td colspan="7" class="text-center"><small>Sedang Memproses Data</small></td></tr>');
        },

        success: function (response) {
            var status  = response.status;
            var message = response.message;
            var html    = response.html;

            // Apabila Berhasil
            if(status=="success"){
                // Tampilkan Data
                $('#NotifikasiImportSupplier').html(html);

                // Enable Tombol Selesai
                $('#TombolSelesai').prop('disabled', false);
            }else{
                $('#NotifikasiImportSupplier').html('<tr><td colspan="7" class="text-center"><small class="text-danger">'+message+'</small></td></tr>');

                // Enamble Tombol
                $('#TombolImport').prop('disabled', false);
            }
        },

        error: function(xhr, status, error){
            // Consol
            console.log("XHR:", xhr);
            console.log("STATUS:", status);
            console.log("ERROR:", error);
            console.log("RESPONSE:", xhr.responseText);

            // Tampilkan Notifikasi
            $('#NotifikasiImportSupplier').html('<tr><td colspan="7" class="text-center"><small class="text-danger">Terjadi kesalahan saat mengimpor data.</small></td></tr>');
            
            // Enamble Tombol
            $('#TombolImport').prop('disabled', false);
        }
    });
});

// Tombol Selesai
$('#TombolSelesai').on('click', function () {
    //Reset Filter
    $('#ProsesFilter')[0].reset();
    $('#ProsesImportSupplier')[0].reset();

    //Tampilkan Data
    ShowData();

    // Tutup Modal
    $('#ModalImportSupplier').modal('hide');

    // Enable Tombol TombolImport dan TombolSelesai
    $('#TombolImport').prop('disabled', true);
    $('#TombolSelesai').prop('disabled', true);
});


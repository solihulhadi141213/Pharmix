// =======================================
// FUNCTION
// =======================================

//Fungsi Untuk Menampilkan Data Pembayaran
function ShowPembayaran() {
    // Target And Filter
    let target = $('#tabel_pembayaran');
    let data   = $('#ProsesFilter').serialize();

    target.addClass('blur-loading');

    $.ajax({
        type    : 'POST',
        url     : '_Page/Pembayaran/TabelPembayaran.php',
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

// Fungsi Menampilkan Detail Transaksi
function ShowDetailTransaksi(id_ref,database_transaksi){
    // Load Form
    $('#FormDetailTransaksi').html("Loading...");

    //Buka Detail Barang
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Pembayaran/FormDetailTransaksi.php',
        data        : {id_ref: id_ref, database_transaksi: database_transaksi},
        dataType    : "JSON",
        success     : function(response){
            if(response.status=="success"){
                var html = response.html;

                //Tempelkan Detail
                $('#FormDetailTransaksi').html(html);

            }else{
                //Tempelkan ke 'FormDetailTransaksiJualBeli'
                $('#FormDetailTransaksi').html(
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
            //Tempelkan ke 'FormDetailTransaksi'
            $('#FormDetailTransaksi').html(
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
}

// =======================================
// EVENT LISTENER
// =======================================
$(document).ready(function() {

    //------------------------------------
    // Menampilkan Data Pertama Kali
    //------------------------------------

    // Reload Data
    ShowPembayaran();

    //Ketika 'keyword_by' diubah
    $('#keyword_by').change(function() {
        var keyword_by= $('#keyword_by').val();
        $.ajax({
            type    : 'POST',
            url     : '_Page/Pembayaran/FormFilter.php',
            data    : {keyword_by: keyword_by},
            success: function(data) {
                $('#FormFilter').html(data);
            }
        });
    });

    //Ketika 'ProsesFilter' Di Submit
    $('#ProsesFilter').submit(function(e) {
        e.preventDefault();

        // Reset Halaman
        $('#page').val(1);

        // Reload Data
        ShowPembayaran();

        // Tutup Modal Bootstrap 5
        const modalElement = document.getElementById('ModalFilter');
        const modal = bootstrap.Modal.getInstance(modalElement);

        if (modal) {modal.hide();}
    });

    //Pagging
    $(document).on('click', '#next_button', function() {
        var page = parseInt($('#page').val(), 10); // Pastikan nilai diambil sebagai angka
        var page = page + 1;
        $('#page').val(page);
        ShowPembayaran();
    });
    $(document).on('click', '#prev_button', function() {
        var page = parseInt($('#page').val(), 10); // Pastikan nilai diambil sebagai angka
        var page = page - 1;
        $('#page').val(page);
        ShowPembayaran();
    });

    //------------------------------------
    // TAMBAH PEMBAYARAN
    //------------------------------------
    
    // Inisialisasi Select2 saat Modal 'ModalTambahPembayaran' dibuka
    $('#ModalTambahPembayaran').on('shown.bs.modal', function (e) {
        $('#jumlah').focus();
        initializeMoneyInputs();

        $('#id').select2({
            dropdownParent: $('#ModalTambahPembayaran'),
            placeholder: "Pilih Transaksi...",
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '_Page/Pembayaran/GetListTransaksi.php',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term,
                        page: params.page || 1,
                        kategori_transaksi: $('#kategori_transaksi').val()
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            templateResult: function(item) {
                if (!item.id) { return item.text; }
                var $container = $(
                    '<div class="d-flex justify-content-between align-items-center border-bottom py-1">' +
                        '<div>' +
                            '<span class="fw-bold text-dark">#' + item.id + '</span> &bull; ' +
                            '<small class="text-muted">' + item.tanggal + '</small>' +
                            '<div class="small text-primary">' + item.kategori + ' (' + item.relasi + ')</div>' +
                        '</div>' +
                        '<div class="text-end">' +
                            '<span class="fw-bold text-success">' + item.nominal + '</span>' +
                        '</div>' +
                    '</div>'
                );
                return $container;
            },
            templateSelection: function(item) {
                if (!item.id) { return item.text; }
                return 'ID: ' + item.id + ' | ' + item.kategori + ' (' + item.nominal + ')';
            }
        });
    });

    // Event ketika user memilih suatu transaksi pada Select2
    $('#id').on('select2:select', function (e) {
        var idTrans = e.params.data.id;
        var katTrans = $('#kategori_transaksi').val();

        // Tampilkan loading kecil di area info
        $('#InfoJumlahSisaTagihan').html(`
            <div class="alert alert-light border text-center py-2 mb-3">
                <small class="text-muted">Memuat informasi tagihan...</small>
            </div>
        `);

        $.ajax({
            url: '_Page/Pembayaran/GetDetailTagihan.php',
            type: 'POST',
            data: { id: idTrans, kategori_transaksi: katTrans },
            dataType: 'json',
            success: function(response) {
                if (response.status === "Success") {
                    // Tampilkan atribut penting dan sisa tagihan dalam card Bootstrap yang rapi
                    $('#InfoJumlahSisaTagihan').html(`
                        <div class="alert alert-info py-2 px-3 mb-3">
                            <div class="small fw-bold border-bottom pb-1 mb-1 text-dark">
                                <i class="bi bi-info-circle"></i> Informasi Detail Tagihan
                            </div>
                            <div class="row small">
                                <div class="col-6">Tanggal Transaksi: <b>${response.tanggal}</b></div>
                                <div class="col-6 text-end">Status: <span class="badge bg-warning text-dark">${response.status_transaksi}</span></div>
                                <div class="col-6">Jenis/Kategori: <b>${response.sub_kategori}</b></div>
                                <div class="col-6 text-end">Total Tagihan: <b>${response.jumlah_tagihan}</b></div>
                                <div class="col-6">Sudah Dibayar: <span class="text-success"><b>${response.total_terbayar}</b></span></div>
                                <div class="col-6 text-end">Sisa Tagihan: <span class="text-danger fw-bold fs-6">${response.sisa_tagihan}</span></div>
                            </div>
                        </div>
                    `);

                    // Opsional: Otomatis isi input jumlah dengan sisa tagihan jika ingin memudahkan
                    // let cleanVal = response.sisa_tagihan_val;
                    // $('#jumlah').val(formatMoney(cleanVal)).trigger('input');
                } else {
                    $('#InfoJumlahSisaTagihan').html(`
                        <div class="alert alert-danger py-2 text-center mb-3">
                            <small>${response.message}</small>
                        </div>
                    `);
                }
            },
            error: function() {
                $('#InfoJumlahSisaTagihan').html(`
                    <div class="alert alert-danger py-2 text-center mb-3">
                        <small>Gagal mengambil data sisa tagihan.</small>
                    </div>
                `);
            }
        });
    });

    // Event ketika pilihan ID Transaksi dibersihkan (clear)
    $('#id').on('select2:clear', function (e) {
        $('#InfoJumlahSisaTagihan').html('');
    });

    // Reset dan kosongkan pilihan ID Transaksi ketika Kategori Transaksi diubah
    $('#kategori_transaksi').on('change', function() {
        $('#id').val(null).trigger('change');
        $('#InfoJumlahSisaTagihan').html('');
    });

    // Hancurkan (destroy) Select2 saat modal ditutup agar tidak terjadi duplikasi elemen dropdown
    $('#ModalTambahPembayaran').on('hidden.bs.modal', function (e) {
        if ($('#id').data('select2')) {
            $('#id').select2('destroy');
        }
        $('#ProsesTambahPembayaran')[0].reset();
        $('#InfoJumlahSisaTagihan').html('');
        $('#NotifikasiTambahPembayaran').html('');
    });

    // Proses Tambah Pembayaran
    $("#ProsesTambahPembayaran").on("submit", function (e) {
        e.preventDefault();
        
        // Tombol loading
        let $btn = $("#ModalTambahPembayaran button[type='submit']");
        let originalBtnHtml = $btn.html();
        $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="server"></span> Loading...').prop("disabled", true);
        
        // Kosongkan notifikasi sebelumnya
        $("#NotifikasiTambahPembayaran").html('');

        // Ambil data form
        let formData = new FormData(this);

        // Kirim data ke server
        $.ajax({
            url         : "_Page/Pembayaran/ProsesTambahPembayaran.php",
            type        : "POST",
            data        : formData,
            contentType : false,
            processData : false,
            dataType    : "json",
            success: function (response) {
                if (response.status === "Success") {

                    // Reset Halaman
                    $('#page').val(1);

                    // Reset Filter
                    $('#ProsesFilter')[0].reset();

                    // Reload data
                    ShowPembayaran();

                    // Tutup Modal
                    $('#ModalTambahPembayaran').modal('hide');
                    
                    // Tampilkan Toast Sukses
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );
                    
                } else {
                    // Tampilkan pesan error di area notifikasi modal
                    $("#NotifikasiTambahPembayaran").html(`
                        <div class="alert alert-danger text-center py-2" role="alert">
                            <small><b>Opsss!</b><br>${response.message}</small>
                        </div>
                    `);
                }
                $btn.html(originalBtnHtml).prop("disabled", false);
            },
            error: function () {
                $("#NotifikasiTambahPembayaran").html(`
                    <div class="alert alert-danger text-center py-2" role="alert">
                        <small><b>Opsss!</b><br>Terjadi kesalahan pada sistem. Silakan coba lagi.</small>
                    </div>
                `);
                $btn.html(originalBtnHtml).prop("disabled", false);
            }
        });
    });

    //------------------------------------
    // DETAIL PEMBAYARAN
    //------------------------------------
    $('#ModalDetailPembayaran').on('show.bs.modal', function (e) {
        
        //Tangkap 'id_pembayaran' 
        var id_pembayaran = $(e.relatedTarget).data('id');

        // Load Form
        $('#FormDetailPembayaran').html("Loading...");

        //Disable tombol
        $('#ButtonDetailSelengkapnya').prop("disabled", true);

        //Buka Detail Barang
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Pembayaran/FormDetailPembayaran.php',
            data        : {id_pembayaran: id_pembayaran},
            dataType    : "JSON",
            success     : function(response){

                // Jika Berhasil
                if(response.status=="success"){
                    var html = response.html;

                    //Tempelkan Detail
                    $('#FormDetailPembayaran').html(html);

                    // Enable Tombol
                    $('#ButtonDetailSelengkapnya').prop("disabled", false);
                }else{
                    //Tempelkan ke 'FormDetailTransaksiJualBeli'
                    $('#FormDetailPembayaran').html(
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
                    $('#ButtonDetailSelengkapnya').prop("disabled", true);
                }
            },
            error: function () {
                //Tempelkan ke 'FormDetailTransaksiJualBeli'
                $('#FormDetailPembayaran').html(
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
                $('#ButtonDetailSelengkapnya').prop("disabled", true);
            },
        });
    });

    // ---------------------------------------------
    // Modal Detail Transaksi
    // ---------------------------------------------
    $('#ModalDetailTransaksi').on('show.bs.modal', function (e) {
        
        //Tangkap 'id_ref' 
        var id_ref             = $(e.relatedTarget).data('id');
        var database_transaksi = $(e.relatedTarget).data('database');

        ShowDetailTransaksi(id_ref,database_transaksi);
    });

    //------------------------------------
    // EDIT PEMBAYARAN
    //------------------------------------
    $('#ModalEditPembayaran').on('show.bs.modal', function (e) {
        
        //Tangkap 'id_pembayaran' 
        var id_pembayaran = $(e.relatedTarget).data('id');

        // Load Form
        $('#FormEditPembayaran').html("Loading...");

        // Clear Notification
        $('#NotifikasiEditPembayaran').html("");

        //Disable tombol
        $('#ButtonEditPembayaran').prop("disabled", true);

        //Buka Detail Barang
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Pembayaran/FormEditPembayaran.php',
            data        : {id_pembayaran: id_pembayaran},
            dataType    : "JSON",
            success     : function(response){

                // Jika Berhasil
                if(response.status=="success"){
                    var html = response.html;

                    //Tempelkan Detail
                    $('#FormEditPembayaran').html(html);

                    // Enable Tombol
                    $('#ButtonEditPembayaran').prop("disabled", false);

                    $('#jumlah_edit').focus();
                    initializeMoneyInputs();
                }else{
                    //Tempelkan ke 'FormDetailTransaksiJualBeli'
                    $('#FormEditPembayaran').html(
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
                    $('#ButtonEditPembayaran').prop("disabled", true);
                }
            },
            error: function () {
                //Tempelkan ke 'FormDetailTransaksiJualBeli'
                $('#FormHapusPembayaran').html(
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
                $('#ButtonHapusPembayaran').prop("disabled", true);
            },
        });
    });

    $('#ModalEditPembayaran').on('shown.bs.modal', function (e) {
        $('#jumlah_edit').focus();
        initializeMoneyInputs();
    });

    // Proses Edit Pembayaran
    $("#ProsesEditPembayaran").on("submit", function (e) {
        e.preventDefault();
        
        // Tombol loading
        let $btn = $("#ModalEditPembayaran button[type='submit']");
        let originalBtnHtml = $btn.html();
        $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="server"></span> Loading...').prop("disabled", true);
        
        // Kosongkan notifikasi sebelumnya
        $("#NotifikasiEditPembayaran").html('');

        // Ambil data form
        let formData = new FormData(this);

        // Kirim data ke server
        $.ajax({
            url         : "_Page/Pembayaran/ProsesEditPembayaran.php",
            type        : "POST",
            data        : formData,
            contentType : false,
            processData : false,
            dataType    : "json",
            success: function (response) {
                if (response.status === "Success") {

                   // Reload data
                    ShowPembayaran();

                    // Tutup Modal
                    $('#ModalEditPembayaran').modal('hide');
                    
                    // Tampilkan Toast Sukses
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );
                    
                } else {
                    // Tampilkan pesan error di area notifikasi modal
                    $("#NotifikasiEditPembayaran").html(`
                        <div class="alert alert-danger text-center py-2" role="alert">
                            <small><b>Opsss!</b><br>${response.message}</small>
                        </div>
                    `);
                }
                $btn.html(originalBtnHtml).prop("disabled", false);
            },
            error: function () {
                $("#NotifikasiEditPembayaran").html(`
                    <div class="alert alert-danger text-center py-2" role="alert">
                        <small><b>Opsss!</b><br>Terjadi kesalahan pada sistem. Silakan coba lagi.</small>
                    </div>
                `);
                $btn.html(originalBtnHtml).prop("disabled", false);
            }
        });
    });

    //------------------------------------
    // HAPUS PEMBAYARAN
    //------------------------------------
    $('#ModalHapusPembayaran').on('show.bs.modal', function (e) {
        
        //Tangkap 'id_pembayaran' 
        var id_pembayaran = $(e.relatedTarget).data('id');

        // Load Form
        $('#FormHapusPembayaran').html("Loading...");

        // Clear Notification
        $('#NotifikasiHapusPembayaran').html("");

        //Disable tombol
        $('#ButtonHapusPembayaran').prop("disabled", true);

        //Buka Detail Barang
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Pembayaran/FormHapusPembayaran.php',
            data        : {id_pembayaran: id_pembayaran},
            dataType    : "JSON",
            success     : function(response){

                // Jika Berhasil
                if(response.status=="success"){
                    var html = response.html;

                    //Tempelkan Detail
                    $('#FormHapusPembayaran').html(html);

                    // Enable Tombol
                    $('#ButtonHapusPembayaran').prop("disabled", false);
                }else{
                    //Tempelkan ke 'FormDetailTransaksiJualBeli'
                    $('#FormHapusPembayaran').html(
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
                    $('#ButtonHapusPembayaran').prop("disabled", true);
                }
            },
            error: function () {
                //Tempelkan ke 'FormDetailTransaksiJualBeli'
                $('#FormHapusPembayaran').html(
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
                $('#ButtonHapusPembayaran').prop("disabled", true);
            },
        });
    });

    //Proses Hapus Pembayaran
    $('#ProsesHapusPembayaran').submit(function(){

        // Menangkap Element Tombol
        var ButtonHapusPembayaran = $('#ButtonHapusPembayaran').html();

        // Disable Button
        $('#ButtonHapusPembayaran').prop('disabled', true);

        // Loading Tombol
        $('#ButtonHapusPembayaran').html('Loading...');

        // Kosongkan Notifikasi
        $('#NotifikasiHapusPembayaran').html('');

        // Tangkap Data Dari Form
        var form = $('#ProsesHapusPembayaran')[0];
        var data = new FormData(form);

        // Kirim Dengan AJAX
        $.ajax({
            type       : 'POST',
            url        : '_Page/Pembayaran/ProsesHapusPembayaran.php',
            data       : data,
            cache      : false,
            processData: false,
            contentType: false,
            dataType   : 'JSON',
            enctype    : 'multipart/form-data',
            success    : function(response){

                // Tangkap status & message
                var status = response.status;
                var message = response.message;

                // Apabila Berhasil
                if(status=='success'){
                    
                    // RESET SELURUH FORM
                    ShowPembayaran();

                    // Tutup Modal
                    $('#ModalHapusPembayaran').modal('hide');

                    // Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil dihapus.'
                    );

                }else{
                    $('#NotifikasiHapusPembayaran').html('<div class="alert alert-danger"><small><b>Opss!</b> '+message+'</small></div>');
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
                $('#NotifikasiHapusPembayaran').html(`<div class="alert alert-danger"><small>Terjadi kesalahan server.</small></div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#ButtonHapusPembayaran').prop('disabled', false);
                $('#ButtonHapusPembayaran').html(ButtonHapusPembayaran);
            }
        });
    });

    //------------------------------------
    // TAMBAH JURNAL
    //------------------------------------
    $('#ModalTambahJurnal').on('show.bs.modal', function (e) {
        
        //Tangkap 'kode' dan 'database'
        var id_transaksi_pembayaran = $(e.relatedTarget).data('id');

        // Load Form
        $('#FormTambahJurnal').html("Loading...");

        // Clear Notification
        $('#NotifikasiTambahJurnal').html("");

        //Disable tombol
        $('#ButtonTambahJurnal').prop("disabled", true);

        //Buka Detail Barang
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Pembayaran/FormTambahJurnal.php',
            data        : {id_transaksi_pembayaran: id_transaksi_pembayaran},
            dataType    : "JSON",
            success     : function(response){

                // Jika Berhasil
                if(response.status=="success"){
                    var html = response.html;

                    //Tempelkan Detail
                    $('#FormTambahJurnal').html(html);

                    // Enable Tombol
                    $('#ButtonTambahJurnal').prop("disabled", false);
                }else{
                    //Tempelkan ke 'FormTambahJurnal'
                    $('#FormTambahJurnal').html(
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
                    $('#ButtonTambahJurnal').prop("disabled", true);
                }
            },
            error: function () {
                //Tempelkan ke 'FormTambahJurnal'
                $('#FormTambahJurnal').html(
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
                $('#ButtonTambahJurnal').prop("disabled", true);
            },
        });
    });



});
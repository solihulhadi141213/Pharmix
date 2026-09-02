// ============================================================
// TARGET PENCARIAN LOCATION
// ============================================================

let TargetPencarianLocation = 'add';

// =======================================
// FUNCTION
// =======================================

//Fungsi Untuk Menampilkan Data Poliklinik
function ShowPoliklinik() {
    // Target And Filter
    let target = $('#list_poliklinik');
    let data   = $('#ProsesFilter').serialize();

    target.addClass('blur-loading');

    $.ajax({
        type    : 'POST',
        url     : '_Page/Poliklinik/TabelPoliklinik.php',
        data    : data,
        dataType: 'JSON',
        success : function(res) {

            if(res.status === "success"){

                target.fadeOut(150, function () {
                    target.html(res.html).fadeIn(150);
                });

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

//Fungsi Pencarian Location
function PencarianLocation() {
    // Tangkap Data
    var ProsesPencarianLocation = $('#ProsesPencarianLocation').serialize();

    // Loading List
    $('#ListLocation').html('<div class="row"><div class="col-12 text-center">Loading...</div></div>');

    // Insert Data Dengan AJAX
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Poliklinik/ProsesPencarianLocation.php',
        dataType    : 'JSON',
        data 	    :  ProsesPencarianLocation,
        success     : function(response){

            // Status & message
            let status = response.status;
            let message = response.message;

            // Jika Berhasil
            if(status=='success'){

                // Tangkap List
                let html = response.html;
                $('#ListLocation').html(html);
            }else{
                
                // Jika gagal tampilkan notifikasi text
                $('#ListLocation').html('<div class="alert alert-danger mt-3 mb-3"><small>'+message+'</small></div>');
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
            $('#ListLocation').html(`<div class="alert alert-danger mt-3 mb-3">Terjadi kesalahan server.</div>`);
        }
    });
}

// =======================================
// EVENT LISTENER
// =======================================
$(document).ready(function() {

    // Menampilkan Data Pertama Kali
    ShowPoliklinik();

    // Auto Focus ModalFilter
    $('#ModalFilter').on('shown.bs.modal', function () {
        $('#keyword').trigger('focus');
    });

    // Pagging
    // Pagination - Next
    $(document).on('click', '#next_button', function() {

        let page_now  = parseInt($('#page').val(), 10) || 1;
        let next_page = page_now + 1;

        $('#page').val(next_page);

        ShowPoliklinik(0);

        // Scroll ke atas
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });

    });


    // Pagination - Previous
    $(document).on('click', '#prev_button', function() {

        let page_now  = parseInt($('#page').val(), 10) || 1;
        let prev_page = page_now - 1;

        // Mencegah halaman kurang dari 1
        if (prev_page < 1) {
            prev_page = 1;
        }

        $('#page').val(prev_page);

        ShowPoliklinik(0);

        // Scroll ke atas
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });

    });

    // Change keyword_by
    $('#keyword_by').change(function(){
        var keyword_by = $('#keyword_by').val();
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Poliklinik/FormFilter.php',
            data 	    :  {keyword_by: keyword_by},
            success     : function(data){
                $('#FormFilter').html(data);
            }
        });
    });

    // Submit Filter
    $('#ProsesFilter').submit(function(){

        // Reset Page
        $('#page').val("1");

        // Close Modal
        $('#ModalFilter').modal('hide');

        // Reload Data
        ShowPoliklinik();
        
    });

    // --------------------------------------------
    // TAMBAH POLIKLINIK
    // --------------------------------------------
    // Auto Focus ModalTambah
    $('#ModalTambah').on('shown.bs.modal', function () {
        $('#polyclinicCode').trigger('focus');
    });

    // Buka modal pencarian di atas ModalTambah.
    $(document).on('click', '#TombolCariLocation', function () {

        // Tentukan target
        TargetPencarianLocation = 'add';

        // Ambil nama poliklinik
        let namaPoli = $('#polyclinicName').val();

        // Isi keyword pencarian
        $('#nama_lokasi').val(namaPoli);

        // Buka modal pencarian
        const modalElement =
            document.getElementById('ModalCariLocation');

        const modalInstance =
            bootstrap.Modal.getOrCreateInstance(modalElement);

        modalInstance.show();

    });

    // Atur lapisan modal kedua agar ModalTambah tetap terbuka di belakangnya.
    $('#ModalCariLocation').on('show.bs.modal', function () {
        this.style.zIndex = '1065';

        setTimeout(function () {
            var backdropList = document.querySelectorAll('.modal-backdrop');
            var backdropTerakhir = backdropList[backdropList.length - 1];

            if (backdropTerakhir) {
                backdropTerakhir.style.zIndex = '1060';
            }
        }, 0);
    });

    $('#ModalCariLocation').on('hidden.bs.modal', function () {
        this.style.zIndex = '';

        // Bootstrap dapat melepas class ini ketika modal kedua ditutup.
        if ($('#ModalTambah').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });

    // Generate Kode Poli
    $(document).on('click', '#GenerateKodePoli', function () {

        // Generate angka acak 8 digit: 10000000 - 99999999
        const randomNumber = Math.floor(10000000 + Math.random() * 90000000);

        // Bentuk kode poliklinik
        const polyclinicCode = 'PLY-' + randomNumber;

        // Masukkan ke input
        $('#polyclinicCode').val(polyclinicCode);

    });

    // Ketika 'creat_id_location' Dipilih
    $(document).on('change','#update_insert_location_satusehat',function () {

            // Jika dicentang
            if ($(this).is(':checked')) {
                // Readonly
                $('#editSatuSehatCode').prop('readonly', true);

                // Pencarian dinonaktifkan
                $('#TombolCariLocationEdit').prop('disabled', true);

            } else {

                // Bisa diedit kembali
                $('#editSatuSehatCode').prop('readonly', false);

                // Pencarian diaktifkan
                $('#TombolCariLocationEdit').prop('disabled', false);
            }

        }
    );

    // Auto Focus ModalCariLocation
    $('#ModalCariLocation').on('shown.bs.modal', function () {

        let namaPoli = '';

        // MODE TAMBAH
        if (TargetPencarianLocation === 'add') {
            namaPoli = $('#polyclinicName').val() || '';
        }

        // MODE EDIT
        else if (TargetPencarianLocation === 'edit') {
            namaPoli = $('#editPolyclinicName').val() || '';
        }

        // Isi keyword
        $('#nama_lokasi').val(namaPoli);

        // Focus
        $('#nama_lokasi').trigger('focus');

        // Cari otomatis jika nama tersedia
        if ($.trim(namaPoli) !== '') {
            PencarianLocation();
        }

    });

    // Proses Pencarian Locationn
    $('#ProsesPencarianLocation').submit(function(){
        PencarianLocation();
    });

    // Ketika PilihLocation di click
    $(document).on('click','#ListLocation .PilihLocation',function (event) {

            event.preventDefault();

            // Ambil ID Location
            const idLocation = $(this).data('id');
            if (!idLocation) {
                return;
            }
            
            // FORM TAMBAH
            if (TargetPencarianLocation === 'add') {
                $('#satuSehatCode').val(idLocation);
            }


           // FORM EDIT
            else if (TargetPencarianLocation === 'edit') {
                $('#editSatuSehatCode').val(idLocation);
            }

            // TUTUP MODAL PENCARIAN
            const modalElement =
                document.getElementById('ModalCariLocation');
            const modalInstance =
                bootstrap.Modal.getOrCreateInstance(
                    modalElement
                );
            modalInstance.hide();

            // FOCUS
            setTimeout(function () {
                if (TargetPencarianLocation === 'add') {
                    $('#satuSehatCode').trigger('focus');
                } else {
                    $('#editSatuSehatCode').trigger('focus');
                }
            }, 300);
        }
    );

    // Proses Tambah Poliklinik
    $('#ProsesTambah').submit(function(){
        
        // Tangkap Data
        var ProsesTambah = $('#ProsesTambah').serialize();

        // Tombol
        var ButtonTambah = $('#ButtonTambah').html();

        // Loading Tombol
        $('#ButtonTambah').html('...');

        // Clear Notifikasi Text
        $('#NotifikasiTambah').html("");

        // Disable tombol
        $('#ButtonTambah').prop('disabled', true);

        // Insert Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Poliklinik/ProsesTambah.php',
            dataType    : 'JSON',
            data 	    :  ProsesTambah,
            success     : function(response){

                // Status & message
                let status = response.status;
                let message = response.message;

                // Jika Berhasil
                if(status=='success'){
                    //tutup modal
                    $('#ModalTambah').modal('hide');

                    //Reset halaman
                    $('#page').val(1);

                    //Reset Form
                    $('#ProsesTambah')[0].reset();

                    $('#satuSehatCode').prop('readonly', false);

                    //Tampilkan Data
                    ShowPoliklinik();

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiTambah').html('<div class="alert alert-danger mt-3 mb-3"><small>'+message+'</small></div>');
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
                $('#NotifikasiTambah').html(`<div class="alert alert-danger mt-3 mb-3">Terjadi kesalahan server.</div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#ButtonTambah').prop('disabled', false);
                $('#ButtonTambah').html(ButtonTambah);
            }
        });
    });

    // --------------------------------------------
    // DETAIL POLIKLINIK
    // --------------------------------------------

    // Ketika Modal Muncul
    $('#ModalDetail').on('show.bs.modal', function (e) {

        // Tangkap 'polyclinicId'
        var polyclinicId= $(e.relatedTarget).data('id');

        // Loading Form
        $('#FormDetail').html("Loading...");

        // Ambil Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Poliklinik/FormDetail.php',
            data        : {polyclinicId: polyclinicId},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;

                // Apabila status success
                if(status=='success'){
                    var html = response.html;
                    $('#FormDetail').html(html);
                }else{
                    $('#FormDetail').html(`
                        <div class="alert alert-danger text-center">
                            <small>
                                <b>Ops!!</b><br>
                                Terjadi Kesalahan : ${message}
                            </small>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormDetail').html(`
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Ops!!</b><br>
                            Terjadi Kesalahan Pada Sistem.
                        </small>
                    </div>
                `);
            },

        });
    });

    // --------------------------------------------
    // DETAIL LOCATION
    // --------------------------------------------

    // Ketika Modal Muncul
    $('#ModalDetailLocation').on('show.bs.modal', function (e) {

        // Tangkap 'satuSehatCode'
        var satuSehatCode= $(e.relatedTarget).data('id');

        // Loading Form
        $('#FormDetailLocation').html("Loading...");

        // Ambil Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Poliklinik/FormDetailLocation.php',
            data        : {satuSehatCode: satuSehatCode},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;

                // Apabila status success
                if(status=='success'){
                    var html = response.html;
                    $('#FormDetailLocation').html(html);
                }else{
                    $('#FormDetailLocation').html(`
                        <div class="alert alert-danger text-center">
                            <small>
                                <b>Ops!!</b><br>
                                Terjadi Kesalahan : ${message}
                            </small>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormDetailLocation').html(`
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Ops!!</b><br>
                            Terjadi Kesalahan Pada Sistem.
                        </small>
                    </div>
                `);
            },

        });
    });

    // --------------------------------------------
    // EDIT POLIKLINIK
    // --------------------------------------------

    // Ketika Modal Muncul
    $('#ModalEdit').on('show.bs.modal', function (e) {

        // Tangkap 'polyclinicId'
        var polyclinicId= $(e.relatedTarget).data('id');

        // Kosongkan Notifikasi
        $('#NotifikasiEdit').html("");

        // Loading Form
        $('#FormEdit').html("Loading...");

        // Disable Button
        $('#ButtonEdit').prop('disabled', true);

        // Ambil Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Poliklinik/FormEdit.php',
            data        : {polyclinicId: polyclinicId},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;

                // Apabila status success
                if(status=='success'){

                    // Tangkap HTML dan Tempelkan Ke Form
                    var html = response.html;
                    $('#FormEdit').html(html);

                    // Enamble Tombol
                    $('#ButtonEdit').prop('disabled', false);
                }else{
                    $('#FormEdit').html(`
                        <div class="alert alert-danger text-center">
                            <small>
                                <b>Ops!!</b><br>
                                Terjadi Kesalahan : ${message}
                            </small>
                        </div>
                    `);

                    // Disable Tombol
                    $('#ButtonEdit').prop('disabled', true);
                }
            },
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormEdit').html(`
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Ops!!</b><br>
                            Terjadi Kesalahan Pada Sistem.
                        </small>
                    </div>
                `);
                // Disable Tombol
                $('#ButtonEdit').prop('disabled', true);
            },

        });
    });

    // GENERATE KODE POLIKLINIK - EDIT
    $(document).on('click', '#GenerateKodePoliEdit', function () {

        // Angka random 8 digit
        const randomNumber = Math.floor(
            10000000 + Math.random() * 90000000
        );

        // Format
        const polyclinicCode = 'PLY-' + randomNumber;

        // Isi form edit
        $('#editPolyclinicCode').val(polyclinicCode);

    });

    // CARI LOCATION - FORM EDIT
    $(document).on('click', '#TombolCariLocationEdit', function () {

        // Tentukan target
        TargetPencarianLocation = 'edit';

        // Ambil nama poliklinik dari form edit
        let namaPoli = $('#editPolyclinicName').val();

        // Isi keyword pencarian
        $('#nama_lokasi').val(namaPoli);

        // Buka modal
        const modalElement =
            document.getElementById('ModalCariLocation');

        const modalInstance =
            bootstrap.Modal.getOrCreateInstance(modalElement);

        modalInstance.show();

    });

    // Proses Edit Poliklinik
    $('#ProsesEdit').submit(function(){
        
        // Tangkap Data
        var ProsesEdit = $('#ProsesEdit').serialize();

        // Tombol
        var ButtonEdit = $('#ButtonEdit').html();

        // Loading Tombol
        $('#ButtonEdit').html('...');

        // Clear Notifikasi Text
        $('#NotifikasiEdit').html("");

        // Disable tombol
        $('#ButtonEdit').prop('disabled', true);

        // Insert Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Poliklinik/ProsesEdit.php',
            dataType    : 'JSON',
            data 	    :  ProsesEdit,
            success     : function(response){

                // Status & message
                let status = response.status;
                let message = response.message;

                // Jika Berhasil
                if(status=='success'){
                    //Tutup modal
                    $('#ModalEdit').modal('hide');

                    // Kosongkan Notifikasi
                    $('#NotifikasiEdit').html('');

                    //Tampilkan Data
                    ShowPoliklinik();

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiEdit').html('<div class="alert alert-danger mt-3 mb-3"><small>'+message+'</small></div>');
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
                $('#NotifikasiEdit').html(`<div class="alert alert-danger mt-3 mb-3">Terjadi kesalahan server.</div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#ButtonEdit').prop('disabled', false);
                $('#ButtonEdit').html(ButtonEdit);
            }
        });
    });

    // --------------------------------------------
    // HAPUS POLIKLINIK
    // --------------------------------------------

    // Ketika Modal Muncul
    $('#ModalHapus').on('show.bs.modal', function (e) {

        // Tangkap 'polyclinicId'
        var polyclinicId= $(e.relatedTarget).data('id');

        // Kosongkan Notifikasi
        $('#NotifikasiHapus').html("");

        // Loading Form
        $('#FormHapus').html("Loading...");

        // Disable Button
        $('#ButtonHapus').prop('disabled', true);

        // Ambil Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Poliklinik/FormHapus.php',
            data        : {polyclinicId: polyclinicId},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;

                // Apabila status success
                if(status=='success'){

                    // Tangkap HTML dan Tempelkan Ke Form
                    var html = response.html;
                    $('#FormHapus').html(html);

                    // Enamble Tombol
                    $('#ButtonHapus').prop('disabled', false);
                }else{
                    $('#FormHapus').html(`
                        <div class="alert alert-danger text-center">
                            <small>
                                <b>Ops!!</b><br>
                                Terjadi Kesalahan : ${message}
                            </small>
                        </div>
                    `);

                    // Disable Tombol
                    $('#ButtonHapus').prop('disabled', true);
                }
            },
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormHapus').html(`
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Ops!!</b><br>
                            Terjadi Kesalahan Pada Sistem.
                        </small>
                    </div>
                `);
                // Disable Tombol
                $('#ButtonHapus').prop('disabled', true);
            },

        });
    });

    // Proses Hapus Poliklinik
    $('#ProsesHapus').submit(function(){
        
        // Tangkap Data
        var ProsesHapus = $('#ProsesHapus').serialize();

        // Tombol
        var ButtonHapus = $('#ButtonHapus').html();

        // Loading Tombol
        $('#ButtonHapus').html('...');

        // Clear Notifikasi Text
        $('#NotifikasiHapus').html("");

        // Disable tombol
        $('#ButtonHapus').prop('disabled', true);

        // Insert Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Poliklinik/ProsesHapus.php',
            dataType    : 'JSON',
            data 	    :  ProsesHapus,
            success     : function(response){

                // Status & message
                let status = response.status;
                let message = response.message;

                // Jika Berhasil
                if(status=='success'){
                    //Tutup modal
                    $('#ModalHapus').modal('hide');

                    // Kosongkan Notifikasi
                    $('#NotifikasiHapus').html('');

                    //Tampilkan Data
                    ShowPoliklinik();

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil dihapus.'
                    );
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiHapus').html('<div class="alert alert-danger mt-3 mb-3"><small>'+message+'</small></div>');
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
                $('#NotifikasiHapus').html(`<div class="alert alert-danger mt-3 mb-3">Terjadi kesalahan server.</div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#ButtonHapus').prop('disabled', false);
                $('#ButtonHapus').html(ButtonHapus);
            }
        });
    });

});

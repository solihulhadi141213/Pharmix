// ============================================================
// TARGET PENCARIAN PRACTITIONER
// ============================================================

let TargetPencarianPractitioner = 'add';

// =======================================
// FUNCTION
// =======================================

//Fungsi Untuk Menampilkan Data Nakes
function ShowNakes() {
    // Target And Filter
    let target = $('#list_nakes');
    let data   = $('#ProsesFilter').serialize();

    target.addClass('blur-loading');

    $.ajax({
        type    : 'POST',
        url     : '_Page/Nakes/TabelNakes.php',
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

//Fungsi Pencarian Practitioner
function PencarianPractitioner() {
    // Tangkap Data
    var ProsesPencarianPractitioner = $('#ProsesPencarianPractitioner').serialize();

    // Loading List
    $('#ListPractitioner').html('<div class="row"><div class="col-12 text-center">Loading...</div></div>');

    // Insert Data Dengan AJAX
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Nakes/ProsesPencarianPractitioner.php',
        dataType    : 'JSON',
        data 	    :  ProsesPencarianPractitioner,
        success     : function(response){

            // Status & message
            let status = response.status;
            let message = response.message;

            // Jika Berhasil
            if(status=='success'){

                // Tangkap List
                let html = response.html;
                $('#ListPractitioner').html(html);
            }else{
                
                // Jika gagal tampilkan notifikasi text
                $('#ListPractitioner').html('<div class="alert alert-danger mt-3 mb-3"><small>'+message+'</small></div>');
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
            $('#ListPractitioner').html(`<div class="alert alert-danger mt-3 mb-3">Terjadi kesalahan server.</div>`);
        }
    });
}

// =======================================
// EVENT LISTENER
// =======================================
$(document).ready(function() {

    // Menampilkan Data Pertama Kali
    ShowNakes();

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

        ShowNakes(0);

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

        ShowNakes(0);

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
            url 	    : '_Page/Nakes/FormFilter.php',
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
        ShowNakes();
        
    });

    // --------------------------------------------
    // TAMBAH NAKES
    // --------------------------------------------
    
    // Auto Focus ModalTambah
    $('#ModalTambah').on('shown.bs.modal', function () {
        $('#medicalPersonelCode').trigger('focus');
    });

    // Form Kontak
    $('#medicalPersonelPhone').on('keypress', function(e) {
        // Ambil kode karakter yang ditekan
        var charCode = (e.which) ? e.which : e.keyCode;
        
        // Kode ASCII 48-57 adalah angka 0 sampai 9
        // Tombol kontrol seperti Backspace (8) atau Delete (46) tetap diizinkan
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
    });

    // Buka modal pencarian di atas ModalTambah.
    $(document).on('click', '#TombolCariPractitioner', function () {

        // Tentukan target
        TargetPencarianPractitioner = 'add';

        // Ambil Nik Nakes
        let NikNakes = $('#medicalPersonelNik').val();

        // Isi keyword pencarian
        $('#NikNakes').val(NikNakes);

        // Buka modal pencarian
        const modalElement =
            document.getElementById('ModalCariPractitioner');

        const modalInstance =
            bootstrap.Modal.getOrCreateInstance(modalElement);

        modalInstance.show();

    });

    // Atur lapisan modal kedua agar ModalTambah tetap terbuka di belakangnya.
    $('#ModalCariPractitioner').on('show.bs.modal', function () {
        this.style.zIndex = '1065';

        setTimeout(function () {
            var backdropList = document.querySelectorAll('.modal-backdrop');
            var backdropTerakhir = backdropList[backdropList.length - 1];

            if (backdropTerakhir) {
                backdropTerakhir.style.zIndex = '1060';
            }
        }, 0);
    });

    $('#ModalCariPractitioner').on('hidden.bs.modal', function () {
        this.style.zIndex = '';

        // Bootstrap dapat melepas class ini ketika modal kedua ditutup.
        if ($('#ModalTambah').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });

    // Generate Kode Nakes
    $(document).on('click', '#GenerateKodeNakes', function () {

        // Generate angka acak 8 digit: 10000000 - 99999999
        const randomNumber = Math.floor(10000000 + Math.random() * 90000000);

        // Bentuk kode nakes
        const medicalPersonelCode = 'MP-' + randomNumber;

        // Masukkan ke input
        $('#medicalPersonelCode').val(medicalPersonelCode);

    });

    // Auto Focus ModalCariPractitioner
    $('#ModalCariPractitioner').on('shown.bs.modal', function () {

        let NikNakes = '';

        // MODE TAMBAH
        if (TargetPencarianPractitioner === 'add') {
            NikNakes = $('#medicalPersonelNik').val() || '';
        }

        // MODE EDIT
        else if (TargetPencarianPractitioner === 'edit') {
            NikNakes = $('#editNakesName').val() || '';
        }

        // Isi keyword
        $('#medicalPersonelNik').val(NikNakes);

        // Focus
        $('#medicalPersonelNik').trigger('focus');

        // Cari otomatis jika nama tersedia
        if ($.trim(NikNakes) !== '') {
            PencarianPractitioner();
        }

    });

    // Proses Pencarian Practitionern
    $('#ProsesPencarianPractitioner').submit(function(){
        PencarianPractitioner();
    });

    // Ketika PilihPractitioner di click
    $(document).on('click','#ListPractitioner .PilihPractitioner',function (event) {

            event.preventDefault();

            // Ambil ID Practitioner
            const idPractitioner = $(this).data('id');
            if (!idPractitioner) {
                return;
            }
            
            // FORM TAMBAH
            if (TargetPencarianPractitioner === 'add') {
                $('#id_practitioner').val(idPractitioner);
            }


           // FORM EDIT
            else if (TargetPencarianPractitioner === 'edit') {
                $('#edit_id_practitioner').val(idPractitioner);
            }

            // TUTUP MODAL PENCARIAN
            const modalElement =
                document.getElementById('ModalCariPractitioner');
            const modalInstance =
                bootstrap.Modal.getOrCreateInstance(
                    modalElement
                );
            modalInstance.hide();

            // FOCUS
            setTimeout(function () {
                if (TargetPencarianPractitioner === 'add') {
                    $('#id_practitioner').trigger('focus');
                } else {
                    $('#edit_id_practitioner').trigger('focus');
                }
            }, 300);
        }
    );

    // Proses Tambah Nakes
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
            url 	    : '_Page/Nakes/ProsesTambah.php',
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

                    $('#id_practitioner').prop('readonly', false);

                    //Tampilkan Data
                    ShowNakes();

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
    // DETAIL NAKES
    // --------------------------------------------

    // Ketika Modal Muncul
    $('#ModalDetail').on('show.bs.modal', function (e) {

        // Tangkap 'medicalPersonelId'
        var medicalPersonelId= $(e.relatedTarget).data('id');

        // Loading Form
        $('#FormDetail').html("Loading...");

        // Ambil Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Nakes/FormDetail.php',
            data        : {medicalPersonelId: medicalPersonelId},
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
    // DETAIL PRACTITIONER
    // --------------------------------------------

    // Ketika Modal Muncul
    $('#ModalDetailPractitioner').on('show.bs.modal', function (e) {

        // Tangkap 'id_practitioner'
        var id_practitioner= $(e.relatedTarget).data('id');

        // Loading Form
        $('#FormDetailPractitioner').html("Loading...");

        // Ambil Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Nakes/FormDetailPractitioner.php',
            data        : {id_practitioner: id_practitioner},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;

                // Apabila status success
                if(status=='success'){
                    var html = response.html;
                    $('#FormDetailPractitioner').html(html);
                }else{
                    $('#FormDetailPractitioner').html(`
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
                $('#FormDetailPractitioner').html(`
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
    // EDIT NAKES
    // --------------------------------------------

    // Ketika Modal Muncul
    $('#ModalEdit').on('show.bs.modal', function (e) {

        // Tangkap 'medicalPersonelId'
        var medicalPersonelId= $(e.relatedTarget).data('id');

        // Kosongkan Notifikasi
        $('#NotifikasiEdit').html("");

        // Loading Form
        $('#FormEdit').html("Loading...");

        // Disable Button
        $('#ButtonEdit').prop('disabled', true);

        // Ambil Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Nakes/FormEdit.php',
            data        : {medicalPersonelId: medicalPersonelId},
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

    // GENERATE KODE NAKES - EDIT
    $(document).on('click', '#GenerateKodeNakesEdit', function () {

        // Angka random 8 digit
        const randomNumber = Math.floor(
            10000000 + Math.random() * 90000000
        );

        // Format
        const medicalPersonelCode = 'PLY-' + randomNumber;

        // Isi form edit
        $('#editNakesCode').val(medicalPersonelCode);

    });

    // CARI PRACTITIONER - FORM EDIT
    $(document).on('click', '#TombolCariPractitionerEdit', function () {

        // Tentukan target
        TargetPencarianPractitioner = 'edit';

        // Ambil Nik nakes dari form edit
        let NikNakes = $('#editNakesNik').val();

        // Isi keyword pencarian
        $('#NikNakes').val(NikNakes);

        // Buka modal
        const modalElement =
            document.getElementById('ModalCariPractitioner');

        const modalInstance =
            bootstrap.Modal.getOrCreateInstance(modalElement);

        modalInstance.show();

    });

    // Proses Edit Nakes
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
            url 	    : '_Page/Nakes/ProsesEdit.php',
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
                    ShowNakes();

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
    // HAPUS NAKES
    // --------------------------------------------

    // Ketika Modal Muncul
    $('#ModalHapus').on('show.bs.modal', function (e) {

        // Tangkap 'medicalPersonelId'
        var medicalPersonelId= $(e.relatedTarget).data('id');

        // Kosongkan Notifikasi
        $('#NotifikasiHapus').html("");

        // Loading Form
        $('#FormHapus').html("Loading...");

        // Disable Button
        $('#ButtonHapus').prop('disabled', true);

        // Ambil Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Nakes/FormHapus.php',
            data        : {medicalPersonelId: medicalPersonelId},
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

    // Proses Hapus Nakes
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
            url 	    : '_Page/Nakes/ProsesHapus.php',
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
                    ShowNakes();

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

    // --------------------------------------------
    // AKSES NAKES
    // --------------------------------------------
    // Ketika Modal Muncul
    $('#ModalAksesNakes').on('show.bs.modal', function (e) {

        // Tangkap 'medicalPersonelId'
        var medicalPersonelId= $(e.relatedTarget).data('id');

        // Kosongkan Notifikasi
        $('#NotifikasiAksesNakes').html("");

        // Loading Form
        $('#FormAksesNakes').html("Loading...");

        // Disable Button
        $('#ButtonAksesNakes').prop('disabled', true);

        // Ambil Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Nakes/FormAksesNakes.php',
            data        : {medicalPersonelId: medicalPersonelId},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;

                // Apabila status success
                if(status=='success'){

                    // Tangkap HTML dan Tempelkan Ke Form
                    var html = response.html;
                    $('#FormAksesNakes').html(html);

                    // Enamble Tombol
                    $('#ButtonAksesNakes').prop('disabled', false);
                }else{
                    $('#FormAksesNakes').html(`
                        <div class="alert alert-danger text-center">
                            <small>
                                <b>Ops!!</b><br>
                                Terjadi Kesalahan : ${message}
                            </small>
                        </div>
                    `);

                    // Disable Tombol
                    $('#ButtonAksesNakes').prop('disabled', true);
                }
            },
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormAksesNakes').html(`
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Ops!!</b><br>
                            Terjadi Kesalahan Pada Sistem.
                        </small>
                    </div>
                `);
                // Disable Tombol
                $('#ButtonAksesNakes').prop('disabled', true);
            },

        });
    });

    // Proses Akses Nakes
    $('#ProsesAksesNakes').submit(function(){
        
        // Tangkap Data
        var ProsesAksesNakes = $('#ProsesAksesNakes').serialize();

        // Tombol
        var ButtonAksesNakes = $('#ButtonAksesNakes').html();

        // Loading Tombol
        $('#ButtonAksesNakes').html('...');

        // Clear Notifikasi Text
        $('#NotifikasiHapus').html("");

        // Disable tombol
        $('#ButtonAksesNakes').prop('disabled', true);

        // Insert Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Nakes/ProsesAksesNakes.php',
            dataType    : 'JSON',
            data 	    :  ProsesAksesNakes,
            success     : function(response){

                // Status & message
                let status = response.status;
                let message = response.message;

                // Jika Berhasil
                if(status=='success'){
                    //Tutup modal
                    $('#ModalAksesNakes').modal('hide');

                    // Kosongkan Notifikasi
                    $('#NotifikasiAksesNakes').html('');

                    //Tampilkan Data
                    ShowNakes();

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        ''+message+''
                    );
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiAksesNakes').html('<div class="alert alert-danger mt-3 mb-3"><small>'+message+'</small></div>');
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
                $('#NotifikasiAksesNakes').html(`<div class="alert alert-danger mt-3 mb-3">Terjadi kesalahan server.</div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#ButtonAksesNakes').prop('disabled', false);
                $('#ButtonAksesNakes').html(ButtonAksesNakes);
            }
        });
    });

});

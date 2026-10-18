//Fungsi Menampilkan Data
function ShowCredentialTable() {
    $.ajax({
        type    : 'POST',
        url     : '_Page/GoogleCredential/TableGoogleCredential.php',
        success: function(data) {
            $('#tabel_credential').html(data);
            
            // 🔁 Re-inisialisasi tooltip setelah data dimuat
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });
}

// Untuk Menampilkan Toast
function copyText(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;

    const text = el.innerText.trim();

    navigator.clipboard.writeText(text).then(function () {
        const toastEl = document.getElementById('toastCopy');
        const toast = new bootstrap.Toast(toastEl, {
            delay: 2000
        });
        toast.show();
    });
}

//Menampilkan Data Pertama Kali
$(document).ready(function() {
    ShowCredentialTable();

    /*  
    ---------------------------------------------------
    TAMBAH KONEKSI
    --------------------------------------------------- 
    */

    /* Ketika 'modal_tambah' di click */
    $(document).on('click', '.modal_tambah', function(){
        $('#ModalTambah').modal('show');
    });

    /* Ketika 'ProsesTambah' disubmit */
    $('#ProsesTambah').submit(function(){
       
        /* Menangkap data dari form  */
        var ProsesTambah=$('#ProsesTambah').serialize();

        /* Loading Notification */
        $('#NotifikasiTambah').html('loading..');

        /* Kirim data dengan AJAX  */
        $.ajax({
            type    : 'POST',
            url     : '_Page/GoogleCredential/ProsesTambah.php',
            dataType: 'json',
            data    : ProsesTambah,
            success: function(response) {
                var status  = response.status;
                var message = response.message;

                // Apabila berhasil
                if(status=='success'){
                    //Bersihkan notifikasi
                    $('#NotifikasiTambah').html('');

                    //reset form
                    $('#ProsesTambah')[0].reset();

                    //Tutup modal
                    $('#ModalTambah').modal('hide');

                    //Menampilkan Swal
                    Swal.fire(
                        'Success!',
                        'Tambah Koneksi Google Credential Berhasil!',
                        'success'
                    )

                    //reload tabel
                    ShowCredentialTable();
                }else{
                    $('#NotifikasiTambah').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
                }
                
            }
        });
    });

    /* MODAL DETAIL */
    $(document).on('click', '.modal_detail', function () {

        //tangkap data 'id_google_credential' dan buat variabel
        var id_google_credential   = $(this).data('id');

        //tampilkan modal
        $('#ModalDetail').modal('show');

        //Form Loading
        $('#FormDetail').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/GoogleCredential/FormDetail.php',
            data        : {id_google_credential: id_google_credential},
            success     : function(data){
                $('#FormDetail').html(data);
            }
        });
    });

    /* MODAL UJI KONEKSI */
    $(document).on('click', '.modal_uji_koneksi', function () {

        //tangkap data 'id_google_credential' dan buat variabel
        var id_google_credential   = $(this).data('id');

        //tampilkan modal
        $('#ModalUjiKoneksi').modal('show');

        //Form Loading
        $('#FormUjiKoneksi').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/GoogleCredential/FormUjiKoneksi.php',
            data        : {id_google_credential: id_google_credential},
            success     : function(data){
                $('#FormUjiKoneksi').html(data);
            }
        });
    });

    /* MODAL EDIT */
    $(document).on('click', '.modal_edit', function () {

        //tangkap data 'id_google_credential' dan buat variabel
        var id_google_credential   = $(this).data('id');

        //tampilkan modal
        $('#ModalEdit').modal('show');

        // Kosongkan Notifikasi
        $('#NotifikasiEdit').html('');

        //Form Loading
        $('#FormEdit').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/GoogleCredential/FormEdit.php',
            data        : {id_google_credential: id_google_credential},
            success     : function(data){
                $('#FormEdit').html(data);
            }
        });
    });

    /* Ketika 'ProsesEdit' disubmit */
    $('#ProsesEdit').submit(function(){
       
        /* Menangkap data dari form  */
        var ProsesEdit=$('#ProsesEdit').serialize();

        /* Loading Notification */
        $('#NotifikasiEdit').html('loading..');

        /* Kirim data dengan AJAX  */
        $.ajax({
            type    : 'POST',
            url     : '_Page/GoogleCredential/ProsesEdit.php',
            dataType: 'json',
            data    : ProsesEdit,
            success: function(response) {
                var status  = response.status;
                var message = response.message;

                // Apabila berhasil
                if(status=='success'){
                    //Bersihkan notifikasi
                    $('#NotifikasiEdit').html('');

                    //Tutup modal
                    $('#ModalEdit').modal('hide');

                    //reload tabel
                    ShowCredentialTable();

                    // Menampilkan Swal
                    Swal.fire(
                        'Success!',
                        'Edit Koneksi Google Credential Berhasil!',
                        'success'
                    )
                }else{
                    $('#NotifikasiEdit').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
                }
                
            }
        });
    });

    /* MODAL DELETE */
    $(document).on('click', '.modal_delete', function () {

        //tangkap data 'id_google_credential' dan buat variabel
        var id_google_credential   = $(this).data('id');

        //tampilkan modal
        $('#ModalDelete').modal('show');

        // Kosongkan Notifikasi
        $('#NotifikasiDelete').html('');

        //Form Loading
        $('#FormDelete').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/GoogleCredential/FormDelete.php',
            data        : {id_google_credential: id_google_credential},
            success     : function(data){
                $('#FormDelete').html(data);
            }
        });
    });

    /* Ketika 'ProsesDelete' disubmit */
    $('#ProsesDelete').submit(function(){
       
        /* Menangkap data dari form  */
        var ProsesDelete=$('#ProsesDelete').serialize();

        /* Loading Notification */
        $('#NotifikasiDelete').html('loading..');

        /* Kirim data dengan AJAX  */
        $.ajax({
            type    : 'POST',
            url     : '_Page/GoogleCredential/ProsesDelete.php',
            dataType: 'json',
            data    : ProsesDelete,
            success: function(response) {
                var status  = response.status;
                var message = response.message;

                // Apabila berhasil
                if(status=='success'){
                    //Bersihkan notifikasi
                    $('#NotifikasiDelete').html('');

                    //Tutup modal
                    $('#ModalDelete').modal('hide');

                    //reload tabel
                    ShowCredentialTable();

                    // Menampilkan Swal
                    Swal.fire(
                        'Success!',
                        'Hapus Koneksi Google Credential Berhasil!',
                        'success'
                    )
                }else{
                    $('#NotifikasiDelete').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
                }
                
            }
        });
    });

});
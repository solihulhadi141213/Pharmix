//Fungsi Menampilkan Data
function ShowConnectionTable() {
    $.ajax({
        type    : 'POST',
        url     : '_Page/SettingSatuSehat/TabelSettingSatuSehat.php',
        success: function(data) {
            $('#tabel_koneksi').html(data);
            
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
    ShowConnectionTable();

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
            url     : '_Page/SettingSatuSehat/ProsesTambah.php',
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
                        'Tambah Koneksi Satu Sehat Berhasil!',
                        'success'
                    )

                    //reload tabel
                    ShowConnectionTable();
                }else{
                    $('#NotifikasiTambah').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
                }
                
            }
        });
    });

    /* MODAL DETAIL */
    $(document).on('click', '.modal_detail', function () {

        //tangkap data 'id_connection_satu_sehat' dan buat variabel
        var id_connection_satu_sehat   = $(this).data('id');

        //tampilkan modal
        $('#ModalDetail').modal('show');

        //Form Loading
        $('#FormDetail').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/SettingSatuSehat/FormDetail.php',
            data        : {id_connection_satu_sehat: id_connection_satu_sehat},
            success     : function(data){
                $('#FormDetail').html(data);
            }
        });
    });

    /* MODAL UJI KONEKSI */
    $(document).on('click', '.modal_uji_koneksi', function () {

        //tangkap data 'id_connection_satu_sehat' dan buat variabel
        var id_connection_satu_sehat   = $(this).data('id');

        //tampilkan modal
        $('#ModalUjiKoneksi').modal('show');

        //Form Loading
        $('#FormUjiKoneksi').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/SettingSatuSehat/FormUjiKoneksi.php',
            data        : {id_connection_satu_sehat: id_connection_satu_sehat},
            success     : function(data){
                $('#FormUjiKoneksi').html(data);
            }
        });
    });

    /* MODAL EDIT */
    $(document).on('click', '.modal_edit', function () {

        //tangkap data 'id_connection_satu_sehat' dan buat variabel
        var id_connection_satu_sehat   = $(this).data('id');

        //tampilkan modal
        $('#ModalEdit').modal('show');

        // Kosongkan Notifikasi
        $('#NotifikasiEdit').html('');

        //Form Loading
        $('#FormEdit').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/SettingSatuSehat/FormEdit.php',
            data        : {id_connection_satu_sehat: id_connection_satu_sehat},
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
            url     : '_Page/SettingSatuSehat/ProsesEdit.php',
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
                    ShowConnectionTable();

                    // Menampilkan Swal
                    Swal.fire(
                        'Success!',
                        'Edit Koneksi Satu Sehat Berhasil!',
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

        //tangkap data 'id_connection_satu_sehat' dan buat variabel
        var id_connection_satu_sehat   = $(this).data('id');

        //tampilkan modal
        $('#ModalDelete').modal('show');

        // Kosongkan Notifikasi
        $('#NotifikasiDelete').html('');

        //Form Loading
        $('#FormDelete').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/SettingSatuSehat/FormDelete.php',
            data        : {id_connection_satu_sehat: id_connection_satu_sehat},
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
            url     : '_Page/SettingSatuSehat/ProsesDelete.php',
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
                    ShowConnectionTable();

                    // Menampilkan Swal
                    Swal.fire(
                        'Success!',
                        'Hapus Koneksi Satu Sehat Berhasil!',
                        'success'
                    )
                }else{
                    $('#NotifikasiDelete').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
                }
                
            }
        });
    });

});
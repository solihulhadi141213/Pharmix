// ===============================================
// FUNCTION
// ===============================================
function ShowData(){
    // Tangkap Isi Form Filter
    var ProsesFilter = $('#ProsesFilter').serialize();
    
    // Tampilkan Data Dengan AJAX
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/NeracaSaldo/TabelNeraca.php',
        data 	    :  ProsesFilter,
        dataType    :  'JSON',
        success     : function(response){

            // Status & Message
            var status = response.status;
            var message = response.message;
            var html = response.html;

            // Apabila Berhasil
            if(status=='success'){

                // Menetapkan title
                var title      = response.title;
                var data_count = response.data_count;

                // Tampilkan Title
                $('#title_report').html(title);

                // Tampilkan Pada Baris
                $('#table_neraca').html(html);

                // Tampilkan Pada Data Count
                $('#data_count').html(`Data Count : ${data_count} Record`);
                
                // Menutup Modal
                $('#ModalFilter').modal('hide');

            }else{

                // Tampilkan Pesan Kesalahan Pada Baris
                $('#NotifikasiFilter').html(`
                    <div class="alert alert-danger text-center">
                        <h1 class="bi bi-exclamation-triangle"></h1>
                        <b>Opss!</b> Terjadi Kesalahan<br>
                        ${message}
                    </tr>
                `);
            }
        },
        error: function () {
            $('#NotifikasiFilter').html(`
                <div class="alert alert-danger text-center">
                    <h1 class="bi bi-exclamation-triangle"></h1>
                    <b>Opss!</b> Terjadi Kesalahan Pada Sistem<br>
                </tr>
            `);
        },
    });
}
// ===============================================
// EVENT LISTENER
// ===============================================
$(document).ready(function() {

    // Pertama Kali Halaman Load, Akan Menampilkan Modal Filter
    $('#ModalFilter').modal('show');

    // Event Ketika Filter Di Submit
    $('#ProsesFilter').submit(function(){
        ShowData();
    });

    // Modal Export
    $('#ModalExport').on('show.bs.modal', function (e) {

        // Tanngkap id_perkiraan, periode1 dan periode2
        var id_perkiraan = $('#id_perkiraan').val();
        var periode1     = $('#periode1').val();
        var periode2     = $('#periode2').val();

        // Disable 'TombolExport'
        $('#TombolExport').prop("disabled", true);

        // Kosongkan Notifikasi
        $('#NotifikasiExport').html("");

        // Kosongkan Form
        $('#FormExport').html('');

        // Bentuk Form Export
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/NeracaSaldo/FormExport.php',
            data        : {periode1: periode1, periode2: periode2},
            dataType    :  'JSON',
            success     : function(response){

                // Status & message
                var status = response.status;
                var message = response.message;

                // Apabila Berhasil
                if(status=='success'){
                    var html = response.html;
                    $('#FormExport').html(html);

                    // Enable 'TombolExport'
                    $('#TombolExport').prop("disabled", false);
                }else{
                    $('#NotifikasiExport').html(`
                        <div class="alert alert-danger text-center">
                            <h1 class="bi bi-exclamation-triangle"></h1>
                            <b>Opss!</b> <br> ${message}<br>
                        </tr>
                    `);
                }
            },
            error: function () {
                $('#NotifikasiExport').html(`
                    <div class="alert alert-danger text-center">
                        <h1 class="bi bi-exclamation-triangle"></h1>
                        <b>Opss!</b> Terjadi Kesalahan Pada Sistem<br>
                    </tr>
                `);
            },
        });
    });
});



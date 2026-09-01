//Fungsi Menampilkan Data
function ShowData() {
    var ProsesFilter = $('#ProsesFilter').serialize();
    var $tabel       = $('#TabelSatuanNumerator');

    // Tambahkan efek visual loading (opacity menurun)
    $tabel.css({
        'opacity': '0.5',
        'pointer-events': 'none',
        'transition': 'opacity 0.3s ease'
    });

    $.ajax({
        type   : 'POST',
        url    : '_Page/SatuanNumerator/TabelSatuanNumerator.php',
        data   : ProsesFilter,
        success: function(data) {
            // Ganti isi tabel tanpa mengganti elemen induk
            $tabel.html(data);

            // Kembalikan efek normal
            $tabel.css({
                'opacity': '1',
                'pointer-events': 'auto'
            });
            
            // 🔁 Re-inisialisasi tooltip setelah data dimuat
            $('[data-bs-toggle="tooltip"]').tooltip();
        },
        error: function() {
            $tabel.html('<tr><td class="text-center" colspan="5"><small class="text-danger">Gagal Memuat, Silahkan Coba Lagi!</small></td></tr>');
            $tabel.css({
                'opacity': '1',
                'pointer-events': 'auto'
            });
        }
    });
}


//Menampilkan Data Pertama Kali
$(document).ready(function() {

    //Menampilkan Data Pertama Kali
    ShowData();

    //Ketika keyword_by diubah
    $('#KeywordBy').change(function(){
        var keyword_by =$('#KeywordBy').val();
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/SatuanNumerator/FormFilter.php',
            data        : {keyword_by: keyword_by},
            success     : function(data){
                $('#FormFilter').html(data);
            }
        });
    });

    //Ketika Data Di Filter Kembalikan Ke Halaman Awal
    $('#ProsesFilter').submit(function(){
        $('#page').val("1");
        $('#ModalFilter').modal('hide');
        ShowData();
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

    // Modal Export
    $(document).on('click', '.modal_export', function () {

        //tampilkan modal
        $('#ModalExport').modal('show');

        //Form Loading
        $('#FormExport').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/SatuanNumerator/FormExport.php',
            success     : function(data){
                $('#FormExport').html(data);
            }
        });
    });

    // Modal Import
    $(document).on('click', '.modal_import', function () {

        //tampilkan modal
        $('#ModalImport').modal('show');

        // Kosongkan Notifikasi
        $('#NotifikasiImport').html('');

        //Form Loading
        $('#FormImport').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/SatuanNumerator/FormImport.php',
            success     : function(data){
                $('#FormImport').html(data);
            }
        });
    });


    // Proses Import
    $('#ProsesImport').on('submit', function (e) {
        e.preventDefault();

        let fileInput = $('#file_import')[0].files[0];

        if (!fileInput) {
            $('#NotifikasiImport').html(
                '<div class="alert alert-danger">File belum dipilih</div>'
            );
            return;
        }

        let formData = new FormData();
        formData.append('file_import', fileInput);

        // Tampilkan progress bar awal
        $('#NotifikasiImport').html(`
            <div class="progress mb-2">
                <div class="progress-bar progress-bar-striped progress-bar-animated"
                     role="progressbar"
                     style="width:0%" id="ProgressImport">
                     0%
                </div>
            </div>
            <div id="LogImport" style="max-height:250px; overflow:auto;"></div>
        `);

        $.ajax({
            url: '_Page/SatuanNumerator/ProsesImport.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (res) {

                if (res.status === 'error') {
                    $('#NotifikasiImport').html(
                        `<div class="alert alert-danger">${res.message}</div>`
                    );
                    return;
                }

                // Update progress
                $('#ProgressImport')
                    .css('width', '100%')
                    .text('100%')
                    .removeClass('progress-bar-animated');

                // Tampilkan log
                let logHtml = '<ul class="list-group">';
                res.log.forEach(function (item) {
                    let badge = item.status === 'success'
                        ? 'success'
                        : 'danger';

                    logHtml += `
                        <li class="list-group-item d-flex justify-content-between">
                            <small>${item.message}</small>
                            <span class="badge bg-${badge}">
                                ${item.status.toUpperCase()}
                            </span>
                        </li>
                    `;
                });
                logHtml += '</ul>';

                $('#LogImport').html(logHtml);
                $("#ProsesFilter")[0].reset();
                $('#page').val(1);
                ShowData();
            },
            error: function () {
                $('#NotifikasiImport').html(
                    '<div class="alert alert-danger">Terjadi kesalahan server</div>'
                );
            }
        });
    });


    //Ketika Modal Tambah Muncul
    $('#ModalTambah').on('show.bs.modal', function (e) {
        // Menampilkan Datalist Category Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/SatuanNumerator/list_system.php',
            success     : function(data){
                $('#list_system').html(data);
            }
        });
    });

    //Proses Tambah
    $('#ProsesTambah').submit(function(e){
        e.preventDefault();
        
        // Ambil Data Dari form
        var ProsesTambah = $(this).serialize();

        //Loading Notifikasi
        $('#NotifikasiTambah').html('<small class="text-muted">Menyimpan data...</small>');

        // Ajax Request
        $.ajax({
            type     : 'POST',
            url      : '_Page/SatuanNumerator/ProsesTambah.php',
            dataType : 'json',
            data     : ProsesTambah,

            success: function(response){

                var payload  = response.payload;
                var status  = response.status;
                var message = response.message || 'Proses berhasil';

                if(status === 'success'){

                    // Bersihkan notifikasi
                    $('#NotifikasiTambah').html('');

                    // Tutup modal jika ada
                    $('#ModalTambah').modal('hide');

                    // Reset Form
                    $("#ProsesFilter")[0].reset();
                    $("#ProsesTambah")[0].reset();

                    // Reload detail pemeriksaan
                    $("#ProsesFilter")[0].reset();
                    $('#page').val(1);
                    ShowData();

                    // Toast Proses Berhasil
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );

                } else {
                    // Tampilkan Pesan Kesalahan
                    $('#NotifikasiTambah').html(
                        '<div class="alert alert-danger"><small>'+message+'</small></div>'
                    );
                }
            },

            error: function(xhr){
                console.log(xhr.responseText);

                $('#NotifikasiTambah').html(
                    '<div class="alert alert-danger"><small>Terjadi kesalahan sistem</small></div>'
                );
            }
        });
    });

    // Modal Edit
    $(document).on('click', '.modal_edit', function () {

        //tangkap data 'kfa_code' dan buat variabel
        var id_referensi_numerator   = $(this).data('id');

        //tampilkan modal
        $('#ModalEdit').modal('show');

        //Form Loading
        $('#FormEdit').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/SatuanNumerator/FormEdit.php',
            data        : {id_referensi_numerator: id_referensi_numerator},
            success     : function(data){
                $('#FormEdit').html(data);

                // 🔁 Re-inisialisasi tooltip setelah data dimuat
                $('[data-bs-toggle="tooltip"]').tooltip();

                // Menampilkan Datalist Category Dengan AJAX
                $.ajax({
                    type 	    : 'POST',
                    url 	    : '_Page/SatuanNumerator/list_system.php',
                    success     : function(data){
                        $('#list_system_edit').html(data);
                    }
                });
            }
        });
    });

    //Proses Edit
    $('#ProsesEdit').submit(function(e){
        e.preventDefault();
        
        // Ambil Data Dari form
        var ProsesEdit = $(this).serialize();

        //Loading Notifikasi
        $('#NotifikasiEdit').html('<small class="text-muted">Menyimpan data...</small>');

        // Ajax Request
        $.ajax({
            type     : 'POST',
            url      : '_Page/SatuanNumerator/ProsesEdit.php',
            dataType : 'json',
            data     : ProsesEdit,

            success: function(response){

                var status  = response.status;
                var message = response.message;

                if(status === 'success'){

                    // Bersihkan notifikasi
                    $('#NotifikasiEdit').html('');

                    // Tutup modal jika ada
                    $('#ModalEdit').modal('hide');

                    // Reload detail pemeriksaan
                    ShowData();

                    // Toast Proses Berhasil
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );

                } else {
                    // Tampilkan Pesan Kesalahan
                    $('#NotifikasiEdit').html(
                        '<div class="alert alert-danger"><small>'+message+'</small></div>'
                    );
                }
            },

            error: function(xhr){
                console.log(xhr.responseText);

                $('#NotifikasiEdit').html(
                    '<div class="alert alert-danger"><small>Terjadi kesalahan sistem</small></div>'
                );
            }
        });
    });

    // Modal Hapus
    $(document).on('click', '.modal_hapus', function () {

        //tangkap data 'kfa_code' dan buat variabel
        var id_referensi_numerator   = $(this).data('id');

        //tampilkan modal
        $('#ModalHapus').modal('show');

        //Form Loading
        $('#FormHapus').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/SatuanNumerator/FormHapus.php',
            data        : {id_referensi_numerator: id_referensi_numerator},
            success     : function(data){
                $('#FormHapus').html(data);
            }
        });
    });

    //Proses Hapus
    $('#ProsesHapus').submit(function(e){
        e.preventDefault();
        
        // Ambil Data Dari form
        var ProsesHapus = $(this).serialize();

        //Loading Notifikasi
        $('#NotifikasiHapus').html('<small class="text-muted">Loading...</small>');

        // Ajax Request
        $.ajax({
            type     : 'POST',
            url      : '_Page/SatuanNumerator/ProsesHapus.php',
            dataType : 'json',
            data     : ProsesHapus,

            success: function(response){

                var status  = response.status;
                var message = response.message;

                if(status === 'success'){

                    // Bersihkan notifikasi
                    $('#NotifikasiHapus').html('');

                    // Tutup modal jika ada
                    $('#ModalHapus').modal('hide');

                    // Reload detail pemeriksaan
                    ShowData();

                    // Toast Proses Berhasil
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil dihapus.'
                    );

                } else {
                    // Tampilkan Pesan Kesalahan
                    $('#NotifikasiHapus').html(
                        '<div class="alert alert-danger"><small>'+message+'</small></div>'
                    );
                }
            },

            error: function(xhr){
                console.log(xhr.responseText);

                $('#NotifikasiHapus').html(
                    '<div class="alert alert-danger"><small>Terjadi kesalahan sistem</small></div>'
                );
            }
        });
    });

    

});






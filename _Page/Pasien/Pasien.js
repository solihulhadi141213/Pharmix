//Fungsi Untuk Menampilkan Data Anggota
function filterAndLoadTable() {
    var ProsesFilter = $('#ProsesFilter').serialize();
    $.ajax({
        type: 'POST',
        url: '_Page/Pasien/TabelPasien.php',
        data: ProsesFilter,
        success: function(data) {
            $('#TabelPasien').html(data);
        }
    });
}

// Fungsi untuk memformat angka ke Rupiah tanpa desimal
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { 
        style: 'currency', 
        currency: 'IDR', 
        minimumFractionDigits: 0, 
        maximumFractionDigits: 0 
    }).format(angka);
}

$(document).ready(function() {

    // Menampilkan Data Pertama Kali
    filterAndLoadTable();

    // Form Filter
    $('#keyword_by').change(function(){
        var keyword_by = $('#keyword_by').val();
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Pasien/FormFilter.php',
            data 	    :  {keyword_by: keyword_by},
            success     : function(data){
                $('#FormFilter').html(data);
            }
        });
    });
    $('#ProsesFilter').submit(function(){
        $('#page').val("1");
        filterAndLoadTable();
        $('#ModalFilter').modal('hide');
    });

    //Pagging
    $(document).on('click', '#next_button', function() {
        var page_now = parseInt($('#page').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page = page_now + 1;
        $('#page').val(next_page);
        filterAndLoadTable(0);
    });
    $(document).on('click', '#prev_button', function() {
        var page_now = parseInt($('#page').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page = page_now - 1;
        $('#page').val(next_page);
        filterAndLoadTable(0);
    });


    //Validasi Kontak Hanya Boleh Angka
    $('#kontak').keypress(function(event) {
        // Hanya mengizinkan angka (0-9) dan tombol kontrol seperti backspace
        var charCode = (event.which) ? event.which : event.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    });
    
    //Proses Tambah
    $('#ProsesTambahPasien').submit(function(e){
        e.preventDefault();
        
        // Ambil Data Dari form
        var ProsesTambahPasien = $(this).serialize();

        //Loading Notifikasi
        $('#TombolTambahPasien').html('Meyimpan data...');

        // Kosongkan Notifikasi
        $('#NotifikasiTambahPasien').html('');

        // Ajax Request
        $.ajax({
            type     : 'POST',
            url      : '_Page/Pasien/ProsesTambahPasien.php',
            dataType : 'json',
            data     : ProsesTambahPasien,

            success: function(response){

                var status  = response.status;
                var message = response.message;

                if(status === 'success'){

                    // Bersihkan notifikasi
                    $('#NotifikasiTambahPasien').html('');

                    // Tutup modal jika ada
                    $('#ModalTambahPasien').modal('hide');
                    
                    // Reset Parameter
                    $('#page').val("1");
                    $("#ProsesFilter")[0].reset();
                    $("#ProsesTambahPasien")[0].reset();

                    // Reload detail pemeriksaan
                    filterAndLoadTable();

                    // Tampilkan Swal
                    Swal.fire(
                        'Success!',
                        'Tambah Pasien Berhasil!',
                        'success'
                    )

                } else {
                    // Tampilkan Pesan Kesalahan
                    $('#NotifikasiTambahPasien').html(
                        '<div class="alert alert-danger"><small>'+message+'</small></div></div>'
                    );
                }
            },

            error: function(xhr){
                console.log(xhr.responseText);

                $('#NotifikasiTambahPasien').html(
                    '<div class="alert alert-danger"><small>Terjadi kesalahan sistem</small></div>'
                );
            }
        });

        $('#TombolTambahPasien').html('<i class="bi bi-save"></i> Simpan');

    });

    // Modal Detail
    $('#ModalDetail').on('show.bs.modal', function (e) {
        var id_anggota= $(e.relatedTarget).data('id');
        $('#FormDetail').html("Loading...");
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Pasien/FormDetail.php',
            data        : {id_anggota: id_anggota},
            success     : function(data){
                $('#FormDetail').html(data);
            }
        });
    });

     // Modal Edit
    $('#ModalEdit').on('show.bs.modal', function (e) {
        var id_anggota= $(e.relatedTarget).data('id');
        $('#FormEdit').html("Loading...");
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Pasien/FormEdit.php',
            data        : {id_anggota: id_anggota},
            success     : function(data){
                $('#FormEdit').html(data);

                // Kosongkan Notifikasi
                $('#NotifikasiEdit').html('');
            }
        });
    });

    // PROSES EDIT PASIEN
    $('#ProsesEdit').submit(function(e){
        e.preventDefault();

        // Ambil data form
        var ProsesEdit = $(this).serialize();

        // Tombol submit
       var TombolEdit = $('#TombolEdit');

        // Loading
        TombolEdit.prop('disabled', true);
        TombolEdit.html('<i class="bi bi-hourglass-split"></i> Menyimpan...');

        // Kosongkan notifikasi
        $('#NotifikasiEdit').html('');

        // AJAX
        $.ajax({
            type     : 'POST',
            url      : '_Page/Pasien/ProsesEdit.php',
            dataType : 'json',
            data     : ProsesEdit,

            success: function(response){

                if(response.status === 'success'){

                    // Tutup modal
                    $('#ModalEdit').modal('hide');

                    // Reset halaman
                    $('#page').val('1');

                    // Reload tabel
                    filterAndLoadTable();

                    // Notifikasi berhasil
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });

                } else {

                    // Tampilkan pesan error
                    $('#NotifikasiEdit').html(
                        '<div class="alert alert-danger">' +
                            '<small>' + response.message + '</small>' +
                        '</div>'
                    );
                }
            },

            error: function(xhr){

                console.log(xhr.responseText);

                $('#NotifikasiEdit').html(
                    '<div class="alert alert-danger">' +
                        '<small>Terjadi kesalahan sistem. Silahkan coba lagi.</small>' +
                    '</div>'
                );
            },

            complete: function(){

                // Aktifkan kembali tombol
                TombolEdit.prop('disabled', false);

                TombolEdit.html(
                    '<i class="bi bi-save"></i> Simpan'
                );
            }
        });
    });

     // Modal Delete
    $('#ModalDelete').on('show.bs.modal', function (e) {
        var id_anggota= $(e.relatedTarget).data('id');
        $('#FormDelete').html("Loading...");
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Pasien/FormDelete.php',
            data        : {id_anggota: id_anggota},
            success     : function(data){
                $('#FormDelete').html(data);

                // Kosongkan Notifikasi
                $('#NotifikasiDelete').html('');
            }
        });
    });

        // =========================================================
    // PROSES DELETE PASIEN
    // =========================================================
    $('#ProsesDelete').submit(function(e){
        e.preventDefault();

        // Ambil data form
        var ProsesDelete = $(this).serialize();

        // Tombol submit
        var TombolDelete = $('#TombolDelete');

        // Loading
        TombolDelete.prop('disabled', true);
        TombolDelete.html(
            '<i class="bi bi-hourglass-split"></i> Menghapus...'
        );

        // Kosongkan notifikasi
        $('#NotifikasiDelete').html('');

        // AJAX
        $.ajax({
            type     : 'POST',
            url      : '_Page/Pasien/ProsesDelete.php',
            dataType : 'json',
            data     : ProsesDelete,

            success: function(response){

                if(response.status === 'success'){

                    // Tutup modal
                    $('#ModalDelete').modal('hide');

                    // Kembali ke halaman pertama
                    $('#page').val('1');

                    // Reload tabel
                    filterAndLoadTable();

                    // Notifikasi
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });

                } else {

                    // Tampilkan pesan error
                    $('#NotifikasiDelete').html(
                        '<div class="alert alert-danger">' +
                            '<small>' + response.message + '</small>' +
                        '</div>'
                    );
                }
            },

            error: function(xhr){

                console.log(xhr.responseText);

                $('#NotifikasiDelete').html(
                    '<div class="alert alert-danger">' +
                        '<small>Terjadi kesalahan sistem. Silahkan coba lagi.</small>' +
                    '</div>'
                );
            },

            complete: function(){

                // Aktifkan kembali tombol
                TombolDelete.prop('disabled', false);

                TombolDelete.html(
                    '<i class="bi bi-check"></i> Ya, Hapus'
                );
            }
        });
    });


});



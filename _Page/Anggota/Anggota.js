//Fungsi Untuk Menampilkan Data Anggota
function filterAndLoadTable() {
    var ProsesFilter = $('#ProsesFilter').serialize();
    $.ajax({
        type: 'POST',
        url: '_Page/Anggota/TabelAnggota.php',
        data: ProsesFilter,
        success: function(data) {
            $('#MenampilkanTabelAnggota').html(data);
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
    // filterAndLoadTable();
});
$('#keyword_by').change(function(){
    var keyword_by = $('#keyword_by').val();
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Anggota/FormFilter.php',
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
//Validasi Kontak Hanya Boleh Angka
$('#kontak').keypress(function(event) {
    // Hanya mengizinkan angka (0-9) dan tombol kontrol seperti backspace
    var charCode = (event.which) ? event.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
});
//Menampilkan Password
$('#tampilkan_password_anggota').click(function(){
    if($(this).is(':checked')){
        $('#password').attr('type','text');
    }else{
        $('#password').attr('type','password');
    }
});
//Menampilkan Form Password Saat akses_anggota bernilai Ya
$('#akses_anggota').click(function(){
    if($(this).is(':checked')){
        $('#form_password').show();
    }else{
        $('#form_password').hide();
    }
});
//Kondisi Ketika Dipilih Status Anggota
$('#status').change(function(){
    var status = $('#status').val();
    if(status=="Keluar"){
        $('#form_tanggal_keluar').show();
        $('#form_alasan_keluar').show();
    }else{
        $('#form_tanggal_keluar').hide();
        $('#form_alasan_keluar').hide();
    }
});
//Proses Tambah Anggota
$('#ProsesTambahAnggota').submit(function(){
    $('#NotifikasiTambahAnggota').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');
    var form = $('#ProsesTambahAnggota')[0];
    var data = new FormData(form);
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Anggota/ProsesTambahAnggota.php',
        data 	    :  data,
        cache       : false,
        processData : false,
        contentType : false,
        enctype     : 'multipart/form-data',
        success     : function(data){
            $('#NotifikasiTambahAnggota').html(data);
            var NotifikasiTambahAnggotaBerhasil=$('#NotifikasiTambahAnggotaBerhasil').html();
            if(NotifikasiTambahAnggotaBerhasil=="Success"){
                $('#NotifikasiTambahAnggota').html('');
                $('#page').val("1");
                $("#ProsesFilter")[0].reset();
                $("#ProsesTambahAnggota")[0].reset();
                $('#ModalTambahAnggota').modal('hide');
                Swal.fire(
                    'Success!',
                    'Tambah Anggota Berhasil!',
                    'success'
                )
                //Menampilkan Data
                filterAndLoadTable();
            }
        }
    });
});

//Detail Anggota
$('#ModalDetailAnggota').on('show.bs.modal', function (e) {
    var id_anggota= $(e.relatedTarget).data('id');
    $('#FormDetailAnggota').html("Loading...");
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Anggota/FormDetailAnggota.php',
        data        : {id_anggota: id_anggota},
        success     : function(data){
            $('#FormDetailAnggota').html(data);
        }
    });
});

//Modal Edit Anggota
$('#ModalEditAnggota').on('show.bs.modal', function (e) {
    var id_anggota= $(e.relatedTarget).data('id');
    $('#FormEditAnggota').html("Loading...");
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Anggota/FormEditAnggota.php',
        data        : {id_anggota: id_anggota},
        success     : function(data){
            $('#FormEditAnggota').html(data);
            $('#NotifikasiEditAnggota').html('');
        }
    });
});

//Proses Edit Anggota
$('#ProsesEditAnggota').submit(function(){
    $('#NotifikasiEditAnggota').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');
    var form = $('#ProsesEditAnggota')[0];
    var data = new FormData(form);
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Anggota/ProsesEditAnggota.php',
        data 	    :  data,
        cache       : false,
        processData : false,
        contentType : false,
        enctype     : 'multipart/form-data',
        success     : function(data){
            $('#NotifikasiEditAnggota').html(data);
            var NotifikasiEditAnggotaBerhasil=$('#NotifikasiEditAnggotaBerhasil').html();
            if(NotifikasiEditAnggotaBerhasil=="Success"){
                if ($("#GetIdAnggota").length) {
                    $('#NotifikasiEditAnggota').html('');
                    $("#ProsesEditAnggota")[0].reset();
                    $('#ModalEditAnggota').modal('hide');
                    Swal.fire(
                        'Success!',
                        'Edit Anggota Berhasil!',
                        'success'
                    )
                    //Menampilkan Data
                    var id_anggota=$("#GetIdAnggota").html();
                    ShowDetailInline(id_anggota);
                }else{
                    $('#NotifikasiEditAnggota').html('');
                    $("#ProsesEditAnggota")[0].reset();
                    $('#ModalEditAnggota').modal('hide');
                    Swal.fire(
                        'Success!',
                        'Edit Anggota Berhasil!',
                        'success'
                    )
                    //Menampilkan Data
                    filterAndLoadTable();
                }
            }
        }
    });
});

//Modal Ubah Foto Anggota
$('#ModalUbahFotoAnggota').on('show.bs.modal', function (e) {
    var id_anggota= $(e.relatedTarget).data('id');
    $('#FormUbahFotoAnggota').html("Loading...");
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Anggota/FormUbahFotoAnggota.php',
        data        : {id_anggota: id_anggota},
        success     : function(data){
            $('#FormUbahFotoAnggota').html(data);
            $('#NotifikasiUbahFotoAnggota').html('');
        }
    });
});

//Proses Ubah Foto Anggota
$('#ProsesUbahFotoAnggota').submit(function(){
    $('#NotifikasiUbahFotoAnggota').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');
    var form = $('#ProsesUbahFotoAnggota')[0];
    var data = new FormData(form);
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Anggota/ProsesUbahFotoAnggota.php',
        data 	    :  data,
        cache       : false,
        processData : false,
        contentType : false,
        enctype     : 'multipart/form-data',
        success     : function(data){
            $('#NotifikasiUbahFotoAnggota').html(data);
            var NotifikasiUbahFotoAnggotaBerhasil=$('#NotifikasiUbahFotoAnggotaBerhasil').html();
            if(NotifikasiUbahFotoAnggotaBerhasil=="Success"){

                if ($("#GetIdAnggota").length) {
                    $('#NotifikasiUbahFotoAnggota').html('');
                    $('#ModalUbahFotoAnggota').modal('hide');
                    Swal.fire(
                        'Success!',
                        'Ubah Foto Anggota Berhasil!',
                        'success'
                    )
                    var id_anggota=$("#GetIdAnggota").html();
                    ShowDetailInline(id_anggota);
                }else{
                    $('#NotifikasiUbahFotoAnggota').html('');
                    $('#ModalUbahFotoAnggota').modal('hide');
                    Swal.fire(
                        'Success!',
                        'Ubah Foto Anggota Berhasil!',
                        'success'
                    );
                    //Menampilkan Data
                    filterAndLoadTable();
                }
                
            }
        }
    });
});

//Modal Hapus Anggota
$('#ModalHapusAnggota').on('show.bs.modal', function (e) {
    var id_anggota= $(e.relatedTarget).data('id');
    $('#FormHapusAnggota').html("Loading...");
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Anggota/FormHapusAnggota.php',
        data        : {id_anggota: id_anggota},
        success     : function(data){
            $('#FormHapusAnggota').html(data);
            $('#NotifikasiHapusAnggota').html('');
        }
    });
});

//Proses Hapus Anggota
$('#ProsesHapusAnggota').submit(function(){
    $('#NotifikasiHapusAnggota').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');
    var form = $('#ProsesHapusAnggota')[0];
    var data = new FormData(form);
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Anggota/ProsesHapusAnggota.php',
        data 	    :  data,
        cache       : false,
        processData : false,
        contentType : false,
        enctype     : 'multipart/form-data',
        success     : function(data){
            $('#NotifikasiHapusAnggota').html(data);
            var NotifikasiHapusAnggotaBerhasil=$('#NotifikasiHapusAnggotaBerhasil').html();
            if(NotifikasiHapusAnggotaBerhasil=="Success"){
                if ($("#GetIdAnggota").length) {
                    window.location.href = "index.php?Page=Anggota";
                }else{
                    $('#NotifikasiHapusAnggota').html('');
                    $('#ModalHapusAnggota').modal('hide');
                    Swal.fire(
                        'Success!',
                        'Hapus Anggota Berhasil!',
                        'success'
                    )
                    //Menampilkan Data
                    filterAndLoadTable();
                }
            }
        }
    });
});

//Edit Anggota 2
$('#ModalEditAnggota2').on('show.bs.modal', function (e) {
    var id_anggota = $(e.relatedTarget).data('id');
    $('#FormEditAnggota2').html("Loading...");
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Anggota/FormEditAnggota.php',
        data        : {id_anggota: id_anggota},
        success     : function(data){
            $('#FormEditAnggota2').html(data);
            //Proses Edit Anggota
            $('#ProsesEditAnggota2').submit(function(){
                $('#NotifikasiEditAnggota').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');
                var form = $('#ProsesEditAnggota2')[0];
                var data = new FormData(form);
                $.ajax({
                    type 	    : 'POST',
                    url 	    : '_Page/Anggota/ProsesEditAnggota.php',
                    data 	    :  data,
                    cache       : false,
                    processData : false,
                    contentType : false,
                    enctype     : 'multipart/form-data',
                    success     : function(data){
                        $('#NotifikasiEditAnggota').html(data);
                        var NotifikasiEditAnggotaBerhasil=$('#NotifikasiEditAnggotaBerhasil').html();
                        if(NotifikasiEditAnggotaBerhasil=="Success"){
                            location.reload();
                        }
                    }
                });
            });
        }
    });
});

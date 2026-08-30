function filterAndLoadTable() {

    // Target And Filter
    let target = $('#tabel_jurnal');
    let data   = $('#ProsesFilter').serialize();

    target.addClass('blur-loading');

    $.ajax({
        type    : 'POST',
        url     : '_Page/Jurnal/TabelJurnal.php',
        data    : data,
        dataType: 'json',
        success : function(res) {

            if(res.status === "success"){

                target.fadeOut(150, function () {
                    target.html(res.html).fadeIn(150);
                });

                // Update info page
                $('#page').val(res.page);
                $('#page_info').html('Page ' + res.page + ' Of ' + res.total_page);

                // Handle tombol
                $('#prev_button').prop('disabled', res.page <= 1);
                $('#next_button').prop('disabled', res.page >= res.total_page);

            }else{
                target.html(res.html);
                $('#page').val(res.page || 1);
                $('#page_info').html('Page ' + (res.page || 1) + ' Of ' + (res.total_page || 1));
            }

            target.removeClass('blur-loading');
        },
        error: function() {
            target.html('<tr><td colspan="8" class="text-center"><span class="text-danger">Gagal memuat data jurnal</span></td></tr>');
            target.removeClass('blur-loading');
        }
    });
}
// Fungsi untuk validasi periode
function validatePeriode() {
    var periode1 = $('#periode_1').val();
    var periode2 = $('#periode_2').val();

    // Cek jika periode1 dan periode2 memiliki nilai
    if (periode1 && periode2) {
        // Jika Periode Awal lebih besar dari Periode Akhir, tampilkan pesan error
        if (new Date(periode1) > new Date(periode2)) {
            $('#NotifikasiFormExport').html('<small class="text-danger">Periode Awal tidak boleh lebih besar dari Periode Akhir</small>');
            $('#periode_1').val(''); // Reset Periode Awal
        } else {
            // Jika periode benar, ganti notifikasi menjadi pesan sukses
            $('#NotifikasiFormExport').html('<small class="text-success">Data Jurnal Siap Di Export</small>');
        }
    } else {
        // Jika salah satu periode belum diisi, kosongkan notifikasi
        $('#NotifikasiFormExport').html('');
    }
}



//Modal Export
$('#ModalExport').on('show.bs.modal', function (e) {
    // Event listener untuk perubahan pada kedua input
    $('#periode_1, #periode_2').on('change', function() {
        validatePeriode();
    });
});



$(document).ready(function() {
    filterAndLoadTable();

    //Ketika Keyword By Dipilih
    $('#KeywordBy').change(function(){
        var KeywordBy = $('#KeywordBy').val();
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Jurnal/FormFilter.php',
            data 	    :  {KeywordBy: KeywordBy},
            success     : function(data){
                $('#FormFilter').html(data);
            }
        });
    });

    //Ketika Filter Di Submit
    $('#ProsesFilter').submit(function(){
        $('#page').val('1');
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
});

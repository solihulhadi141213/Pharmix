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
});
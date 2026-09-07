// ========================================================================
// FUNCTION
// Semua Function akan dimuat di paling atas
// ========================================================================

// Tampilkan Data Jenis Transaksi
var bantuanRequest = null;
function ShowData(scrollToList = false) {
    if (bantuanRequest) bantuanRequest.abort();
    $('#bantuan_pagination').removeClass('d-none');
    
    // Target And Filter
    let target = $('#data_view');
    let data   = $('#ProsesFilter').serialize();

    // Loading or Blur
    target.addClass('blur-loading');

    // Tampilkan Dtaa Dengan AJAX
    bantuanRequest = $.ajax({
        type    : 'POST',
        url     : '_Page/Bantuan/TabelBantuan.php',
        data    : data,
        dataType: 'JSON',
        success : function(res) {

            if(res.status === "success"){

                target.stop(true, true).html(res.html).show();

                // Update info page
                $('#page').val(res.page);
                $('#page_info').html('Page ' + res.page + ' Of ' + res.total_page);

                // Handle tombol
                $('#prev_button').prop('disabled', res.page <= 1);
                $('#next_button').prop('disabled', res.page >= res.total_page);

            }else{
                target.html(res.html);
            }

            target.removeClass('blur-loading');
            if (scrollToList === true) {
                var header = $('#header');
                var headerPosition = header.css('position');
                var headerHeight = (headerPosition === 'fixed' || headerPosition === 'sticky')
                    ? header.outerHeight() || 0 : 0;
                $('html, body').stop(true).animate({
                    scrollTop: Math.max(0, target.offset().top - headerHeight - 16)
                }, window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 300);
            }
        }
    });
}

// Tampilkan Data Jenis Transaksi
function ShowTags() {
    
    // Target And Filter
    let target = $('#tags_list');
    let data   = $('#ProsesFilter').serialize();

    // Loading or Blur
    target.addClass('blur-loading');

    // Tampilkan Dtaa Dengan AJAX
    $.ajax({
        type    : 'POST',
        url     : '_Page/Bantuan/TagsList.php',
        data    : data,
        dataType: 'JSON',
        success : function(res) {

            if(res.status === "success"){

                target.fadeOut(150, function () {
                    target.html(res.html).fadeIn(150);
                });

            }else{
                target.html(res.html);
            }

            target.removeClass('blur-loading');
        }
    });
}

// ========================================================================
// EVENT LISTENER
// ========================================================================

$(document).ready(function() {
    $(document).on('click', '.kembali_daftar_bantuan', function() {
        ShowData();
        ShowTags();
    });

    $(document).on('keydown', '.lihat_detail_bantuan', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).trigger('click');
        }
    });

    $(document).on('click', '.lihat_detail_bantuan', function() {
        if (bantuanRequest) bantuanRequest.abort();
        var target = $('#data_view');
        var kembali = '<button type="button" class="btn btn-secondary mb-3 kembali_daftar_bantuan"><i class="bi bi-arrow-left me-1"></i> Kembali ke daftar bantuan</button>';
        $('#bantuan_pagination').addClass('d-none');
        target.stop(true, true).removeClass('blur-loading').html(kembali + '<p class="text-muted">Memuat bantuan...</p>').show();
        bantuanRequest = $.ajax({
            type: 'POST',
            url: '_Page/Bantuan/DetailBantuan.php',
            data: {id_dokumentasi: $(this).attr('data-id')},
            dataType: 'json',
            success: function(res) {
                target.html(kembali);
                if (res.status === 'success') {
                    target.append(res.html);
                } else {
                    target.append($('<div class="alert alert-warning"></div>').text(res.message || 'Bantuan tidak dapat dimuat.'));
                }
            },
            error: function(xhr, status) {
                if (status !== 'abort') {
                    target.html(kembali).append('<div class="alert alert-danger">Gagal memuat bantuan. Silakan coba lagi.</div>');
                }
            }
        });
    });

    // ------------------------------------
    // Menampilkan Data Pertama Kali
    
    // Tampilkan Tabel
    ShowData();
    ShowTags();

    $(document).on('click', '.pilih_tags', function() {
        $('#tags').val($(this).attr('data-id'));
        $('#page').val(1);
        ShowData();
        ShowTags();
    });

    // Submit Filter
    $('#ProsesFilter').on('submit', function (e) {
        e.preventDefault();

        // Reset ke halaman pertama
        $('#page').val(1);

        // Muat hasil terlebih dahulu, kemudian scroll ke awal daftar.
        ShowData(true);
        ShowTags();

    });

    //Pagging
    $(document).on('click', '#next_button', function() {
        var page_now = parseInt($('#page').val(), 10) || 1;
        var next_page = page_now + 1;
        $('#page').val(next_page);
        ShowData(0);
    });
    $(document).on('click', '#prev_button', function() {
        var page_now = parseInt($('#page').val(), 10) || 1;
        var next_page = Math.max(1, page_now - 1);
        $('#page').val(next_page);
        ShowData(0);
    });
});

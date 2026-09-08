
// -------------------------------------------------
// FUNCTION
// -------------------------------------------------

//Fungsi Untuk Menampilkan Data Pasien
function filterAndLoadTable() {
    const ProsesFilter = $('#ProsesFilter').serialize();

    $.ajax({
        type: 'POST',
        url: '_Page/Pasien/TabelPasien.php',
        data: ProsesFilter,

        beforeSend: function() {
            tableLoading('#tabel_pasien', true);
        },

        success: function(data) {
            $('#TabelPasien').html(data);
            initResponsiveTable('#tabel_pasien');
        },

        error: function() {
            $('#TabelPasien').html(`
                <tr class="table-empty">
                    <td colspan="9" class="text-center text-danger">
                        Gagal memuat data pasien
                    </td>
                </tr>
            `);
        },

        complete: function() {
            tableLoading('#tabel_pasien', false);
        }
    });
}

// Fungsi ShowDetailPasien
function ShowDetailPasien(id_anggota){
    $('#detail_view').html('Loading...');

    $.ajax({
        type    : 'POST',
        url     : '_Page/Pasien/_DetailPasien.php',
        data    : {id_anggota: id_anggota},
        success : function(data){
            $('#detail_view').html(data);
            RiwayatKunjungan();
            RiwayatResep();
            RiwayatTransaksi();
        },
        error : function(xhr){
            console.log(xhr.responseText);
            $('#detail_view').html(
                '<div class="alert alert-danger">' +
                    '<small>Terjadi kesalahan saat membuka detail pasien.</small>' +
                '</div>'
            );
        }
    });

}
// Riwayat Kunjungan pasien
function RiwayatKunjungan() {
    
    if (!$('#FilterKunjungan').length) return;

    // Target And Filter
    let target = $('#riwayat_kunjungan');
    let data   = $('#FilterKunjungan').serialize();

    target.addClass('blur-loading');

    $.ajax({
        type    : 'POST',
        url     : '_Page/Pasien/TabelKunjungan.php',
        data    : data,
        dataType: 'JSON',
        success : function(res) {

            if(res.status === "success"){

                target.fadeOut(150, function () {
                    target.html(res.html).fadeIn(150);
                });

                // Handle tombol
                $('#page_kunjungan').val(res.page);
                $('#page_info_kunjungan').html('Page ' + res.page + ' Of ' + res.total_page);
                $('#prev_button_kunjungan').prop('disabled', res.page <= 1);
                $('#next_button_kunjungan').prop('disabled', res.page >= res.total_page);

            }else{
                target.html(res.html);
                $('#page_info_kunjungan').text('Page 1 Of 1');
                $('#prev_button_kunjungan, #next_button_kunjungan').prop('disabled', true);
            }
        },
        error: function() {
            target.html('<div class="alert alert-danger"><small>Gagal memuat riwayat kunjungan. Silakan coba lagi.</small></div>');
        },
        complete: function() {
            target.removeClass('blur-loading');
        }
    });
}

function RiwayatResep() {
    loadRiwayatPasien('Resep');
}

function RiwayatTransaksi() {
    loadRiwayatPasien('Transaksi');
}

function loadRiwayatPasien(jenis) {
    var key = jenis.toLowerCase();
    var form = $('#Filter' + jenis);
    if (!form.length) return;
    var target = $('#riwayat_' + key);
    if (target.data('loading')) return;
    var prev = $('#prev_button_' + key);
    var next = $('#next_button_' + key);
    var pageInput = $('#page_' + key);
    var pageInfo = $('#page_info_' + key);
    target.data('loading', true).addClass('blur-loading');
    prev.add(next).prop('disabled', true);
    $.ajax({
        type: 'POST',
        url: '_Page/Pasien/Tabel' + jenis + '.php',
        data: form.serialize(),
        dataType: 'json',
        success: function(res) {
            target.html(res.html);
            pageInput.val(res.page);
            pageInfo.text('Page ' + res.page + ' Of ' + res.total_page);
            if (res.status === 'success') {
                prev.prop('disabled', res.page <= 1);
                next.prop('disabled', res.page >= res.total_page);
            }
        },
        error: function() {
            target.html('<div class="alert alert-danger"><small>Gagal memuat riwayat ' + key + '. Silakan coba lagi.</small></div>');
            pageInfo.text('Page 1 Of 1');
            pageInput.val(1);
        },
        complete: function() {
            target.data('loading', false).removeClass('blur-loading');
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



// -------------------------------------------------
// EVENT LISTENER
// -------------------------------------------------
$(document).ready(function() {

    // Tampilkan 'table_view'
    $('#table_view').show();
    $('#detail_view').hide();

    // Menampilkan Data Pertama Kali
    initResponsiveTable('#tabel_pasien');
    filterAndLoadTable();

    $(document).on('click', '#prev_button_kunjungan, #next_button_kunjungan', function () {
        var page = parseInt($('#page_kunjungan').val(), 10) || 1;
        $('#page_kunjungan').val(Math.max(1, page + (this.id === 'next_button_kunjungan' ? 1 : -1)));
        RiwayatKunjungan();
    });

    // Form Filter
    $(document).on('click', '#prev_button_resep, #next_button_resep, #prev_button_transaksi, #next_button_transaksi', function () {
        var key = this.id.endsWith('_resep') ? 'resep' : 'transaksi';
        if ($('#riwayat_' + key).data('loading')) return;
        var input = $('#page_' + key);
        var page = parseInt(input.val(), 10) || 1;
        input.val(Math.max(1, page + (this.id.startsWith('next_') ? 1 : -1)));
        loadRiwayatPasien(key === 'resep' ? 'Resep' : 'Transaksi');
    });

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
        scrollToTop();
    });
    $(document).on('click', '#prev_button', function() {
        var page_now = parseInt($('#page').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page = page_now - 1;
        $('#page').val(next_page);
        filterAndLoadTable(0);
        scrollToTop();
    });

    // --------------------------------------------------------------
    // TAMBAH PASIEN
    // --------------------------------------------------------------
    //Validasi Kontak Hanya Boleh Angka
    $('#kontak').keypress(function(event) {
        // Hanya mengizinkan angka (0-9) dan tombol kontrol seperti backspace
        var charCode = (event.which) ? event.which : event.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    });

    // Auto Focus ModalTambahPasien
    $('#ModalTambahPasien').on('shown.bs.modal', function () {
        $('#id_pasien').trigger('focus');
    });

    // Generate id_pasien
    $(document).on('click', '#generate_rm', function () {
        // Generate angka random 8 digit
        const randomNumber = Math.floor(10000000 + Math.random() * 90000000);

        // Format ID Pasien
        const idPasien = 'P-' + randomNumber;

        // Masukkan ke input
        $('#id_pasien').val(idPasien);
    });

    // Cari IHS pasien
    $(document).on('click', '#cari_ihs', function () {
       var nik = $('#nik').val();

       // Loading Button
       $('#cari_ihs').html('...');

       // Kosongkan Notifikasi
       $('#notifikasi_pencarian_ihs').html('');

       // Kirim data ke PHP dengan AJAX
       $.ajax({
            type     : 'POST',
            url      : '_Page/Pasien/ProsesCariIhs.php',
            dataType : 'JSON',
            data     : {nik: nik},

            success: function(response){

                var status  = response.status;
                var message = response.message;

                if(status === 'success'){

                    // Tangkap IHS
                    var id = response.metadata.id;

                    // Bersihkan notifikasi
                    $('#notifikasi_pencarian_ihs').html('');

                    // Tempelkan ke form
                    $('#id_ihs').val(id);

                    // Tampilkan Swal
                    showToast(
                        'success',
                        'Berhasil',
                        'IHS Pasien Ditemukan.'
                    );

                } else {
                    // Tampilkan Pesan Kesalahan
                    $('#notifikasi_pencarian_ihs').html(
                        '<div class="alert alert-danger mt-3 mb-3"><small>'+message+'</small></div></div>'
                    );
                }
            },

            error: function(xhr){
                console.log(xhr.responseText);

                $('#notifikasi_pencarian_ihs').html(
                    '<div class="alert alert-danger mt-3 mb-3"><small>Terjadi kesalahan sistem</small></div>'
                );
            }
        });
        $('#cari_ihs').html('<i class="bi bi-cloud"></i> Cari');
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

    // --------------------------------------------------------------
    // EDIT PASIEN
    // --------------------------------------------------------------

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

    // Cari IHS pasien
    $(document).on('click', '#cari_ihs_edit', function (e) {
        e.preventDefault();

        const $btn =$(this);
        const nik = $('#nik_edit').val()?.trim() || '';
        const $notif = $('#notifikasi_pencarian_ihs_edit').empty();

        if (!nik) {
            return $notif.html('<div class="alert alert-danger mt-3 mb-3"><small>NIK pasien tidak boleh kosong.</small></div>');
        }

        $.ajax({
            type: 'POST',         
            url: '_Page/Pasien/ProsesCariIhs.php',         
            dataType: 'json',         
            data: { nik },         
            beforeSend: () => $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mencari...'),
            success: (res) => {
                if (res.status === 'success') {
                    $('#id_ihs_edit').val(res.metadata?.id || '');
                    showToast('success', 'Berhasil', 'IHS Pasien Ditemukan.');
                } else {
                    $notif.html(`<div class="alert alert-danger mt-3 mb-3"><small>${res.message}</small></div>`);
                }
            },
            error: (xhr) => {
                console.error(xhr.responseText);
                $notif.html('<div class="alert alert-danger mt-3 mb-3"><small>Terjadi kesalahan sistem.</small></div>');
            },
            complete: () => $btn.prop('disabled', false).html('<i class="bi bi-cloud"></i> Cari')
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

                // Jika Berhasil
                if(response.status === 'success'){

                    // Tutup modal
                    $('#ModalEdit').modal('hide');

                    // Reset halaman
                    $('#page').val('1');

                    // Reload tabel
                    filterAndLoadTable();
                    ShowDetailPasien(response.id_anggota);

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

                    // Pindah ke detail
                    $('#table_view').show();
                    $('#detail_view').hide();

                    // Reload tabel
                    filterAndLoadTable();

                    // Notifikasi
                    showToast(
                        'success',
                        'Berhasil',
                        'Data Berhasil Dihapus.'
                    );

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

    // =========================================================
    // HANDLE DETAIL PASIEN
    // =========================================================

    
    // Modal Detail
    $('#ModalDetail').on('show.bs.modal', function (e) {
        const id_anggota = $(e.relatedTarget).data('id');

        // Simpan ID pada form
        $('#ProsesDetail').data('id', id_anggota);

        // Loading
        $('#FormDetail').html(
            '<div class="text-center p-3">' +
                '<span class="spinner-border spinner-border-sm"></span> Loading...' +
            '</div>'
        );

        $.ajax({
            type: 'POST',
            url: '_Page/Pasien/FormDetail.php',
            data: {
                id_anggota: id_anggota
            },
            success: function (data) {
                $('#FormDetail').html(data);
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                $('#FormDetail').html(
                    '<div class="alert alert-danger">' +
                        '<small>Terjadi kesalahan saat membuka data pasien.</small>' +
                    '</div>'
                );
            }
        });
    });

    // Event 'ProsesDetail' 
    $(document).on('submit', '#ProsesDetail', function (e) {
        e.preventDefault();
        const id_anggota = $(this).data('id');
        if (!id_anggota) {
            console.error('ID Anggota tidak ditemukan');
            return;
        }
        // Tutup Modal
        const modalElement = document.getElementById('ModalDetail');
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        
        if (modalInstance) {
            modalInstance.hide();
        }

        // Pindah ke detail
        $('#table_view').hide();
        $('#detail_view').show();

        // Tampilkan Detail
        ShowDetailPasien(id_anggota);
    });

    // =========================================================
    // BACK TO DATA
    // =========================================================
    $(document).on('click', '.back_to_data', function (e) {
        e.preventDefault();

        // Kembali ke tabel
        $('#table_view').show();
        $('#detail_view').hide();

        // Scroll ke atas
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});



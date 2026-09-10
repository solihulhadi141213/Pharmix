// ===============================================
// Function
// ===============================================

// MENAMPILKAN DATA JENIS TRANSAKSI
function ShowData() {
    const table  = '#tabel_jenis_transaksi';
    const target = $('#TabelJenisTransaksi');
    const data   = $('#ProsesFilter').serialize();
    tableLoading('#TabelJenisTransaksi', true);
    $.ajax({
        type    : 'POST',
        url     : '_Page/JenisTransaksi/TabelJenisTransaksi.php',
        data    : data,
        dataType: 'json',

        beforeSend: function() {
            tableLoading(table, true);
        },

        success: function(res) {
            if (res.status === 'success') {
                target.html(res.html);

                // Jalankan setelah HTML baru dimasukkan
                initResponsiveTable(table);

                // Sinkronkan page dengan response
                $('#page').val(res.page);

                // Informasi pagination
                $('#page_info').text(
                    'Page ' + res.page + ' Of ' + res.total_page
                );

                // Kontrol tombol pagination
                $('#prev_button').prop(
                    'disabled',
                    res.page <= 1
                );

                $('#next_button').prop(
                    'disabled',
                    res.total_page <= 0 ||
                    res.page >= res.total_page
                );

                return;
            }

            target.html(res.html || `
                <tr class="table-empty">
                    <td colspan="8" class="text-center text-danger">
                        <small>
                            Data jenis transaksi tidak dapat ditampilkan.
                        </small>
                    </td>
                </tr>
            `);

            initResponsiveTable(table);

            $('#page').val(1);
            $('#page_info').text('Page 1 Of 1');
            $('#prev_button, #next_button').prop('disabled', true);
        },

        error: function(xhr, status) {
            // Tidak menampilkan error jika request sengaja dibatalkan
            if (status === 'abort') {
                return;
            }

            target.html(`
                <tr class="table-empty">
                    <td colspan="8" class="text-center text-danger">
                        <small>
                            Terjadi kesalahan saat memuat jenis transaksi.
                        </small>
                    </td>
                </tr>
            `);

            $('#page').val(1);
            $('#page_info').text('Page 1 Of 1');
            $('#prev_button, #next_button').prop('disabled', true);

            console.error(xhr.responseText);
        },

        complete: function() {
            tableLoading(table, false);
        }
    });
}

// SELECT2 AKUN PERKIRAAN
function initSelectAkunPerkiraan(selector, parentId, placeholder) {
    $(selector).select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: placeholder,
        allowClear: true,
        dropdownParent: $(parentId),
        minimumInputLength: 0,
        ajax: {
            url: '_Page/JenisTransaksi/CariAkunPerkiraan.php',
            type: 'POST',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    keyword: params.term || '',
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        },
        language: {
            inputTooShort: function () { return 'Ketik untuk mencari akun'; },
            searching: function () { return 'Mencari akun...'; },
            noResults: function () { return 'Akun tidak ditemukan'; },
            loadingMore: function () { return 'Memuat akun berikutnya...'; }
        }
    });
}

function initSelect2Edit() {
    // KATEGORI
    $('#kategori_edit').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Pilih atau ketik kategori',
        allowClear: true,
        tags: true,
        dropdownParent: $('#ModalEdit'),
        ajax: {
            url: '_Page/JenisTransaksi/CariKategori.php',
            type: 'POST',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    keyword: params.term || '',
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: {
                        more: data.more
                    }
                };
            },
            cache: true
        }
    });

    // AKUN DEBET & KREDIT
    initSelectAkunEdit('#id_akun_debet_edit', 'Pilih akun debet');
    initSelectAkunEdit('#id_akun_kredit_edit', 'Pilih akun kredit');
    initSelectAkunEdit('#id_utang_piutang_edit', 'Pilih akun Utang Piutang');
}

function initSelectAkunEdit(selector, placeholder) {
    $(selector).select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: placeholder,
        allowClear: true,
        dropdownParent: $('#ModalEdit'),
        minimumInputLength: 0,
        ajax: {
            url: '_Page/JenisTransaksi/CariAkunPerkiraan.php',
            type: 'POST',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    keyword: params.term || '',
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        },
        language: {
            searching: function () { return 'Mencari akun...'; },
            noResults: function () { return 'Akun tidak ditemukan'; },
            loadingMore: function () { return 'Memuat akun berikutnya...'; }
        }
    });
}

// ===============================================
// Event Handler
// ===============================================
$(document).ready(function() {

    // Menampilkan Data Pertama Kali
    ShowData();

    // Modal Filter Muncul
    $('#ModalFilter').on('shown.bs.modal', function () {
        $('#keyword').trigger('focus');
    });

    // Ketika keyword Diubah
    $('#keyword_by').change(function(){
        var keyword_by = $('#keyword_by').val();
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/JenisTransaksi/FormFilter.php',
            data 	    :  {keyword_by: keyword_by},
            success     : function(data){
                $('#FormFilter').html(data);
            }
        });
    });

    // Filter Submit
    $('#ProsesFilter').submit(function(){
        $('#page').val("1");
        ShowData();
        $('#ModalFilter').modal('hide');
    });

    //Pagging
    $(document).on('click', '#next_button', function() {
        var page_now = parseInt($('#page').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page = page_now + 1;
        $('#page').val(next_page);
        ShowData();
        scrollToTop();
    });
    $(document).on('click', '#prev_button', function() {
        var page_now = parseInt($('#page').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page = page_now - 1;
        $('#page').val(next_page);
        ShowData();
        scrollToTop();
    });


    //------------------------------------------
    // TAMBAH JENIS TRANSAKSI

    // Modal 'ModalTambahJenisTransaksi' Muncul
    $('#ModalTambahJenisTransaksi').on('shown.bs.modal', function () {
        $('#nama').trigger('focus');
    });

    // Saat kategori_transaksi diubah
    $(document).on('change', '#kategori_transaksi', function () {
        var kategori = $(this).val();
        if (kategori === 'Pengeluaran') {
            $('#label_utang_piutang').html(
                '<small>Akun Utang</small>'
            );
        } else if (kategori === 'Pemasukan') {
            $('#label_utang_piutang').html(
                '<small>Akun Piutang</small>'
            );
        } else {
            $('#label_utang_piutang').html(
                '<small>Akun Utang/Piutang</small>'
            );
        }
    });

    // AKUN DEBET
    initSelectAkunPerkiraan('#id_akun_debet', '#form_debet', 'Pilih akun debet');

    // AKUN KREDIT
    initSelectAkunPerkiraan('#id_akun_kredit', '#form_kredit', 'Pilih akun kredit');

    // AKUN UTANG PIUTANG
    initSelectAkunPerkiraan('#id_utang_piutang', '#form_utang_piutang', 'Pilih akun Utang/Piutang');

    // Select 2 'kategori'
    $('#kategori').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Pilih atau ketik kategori',
        allowClear: true,
        tags: true,

        dropdownParent: $('#ModalTambahJenisTransaksi'),

        ajax: {
            url: '_Page/JenisTransaksi/CariKategori.php',
            type    : 'POST',
            dataType: 'json',
            delay   : 300,

            data: function (params) {
                return {
                    keyword: params.term || '',
                    page   : params.page || 1
                };
            },

            processResults: function (data, params) {

                params.page = params.page || 1;

                return {
                    results   : data.results,
                    pagination: {
                        more: data.more
                    }
                };
            },

            cache: true
        }
    });
    
    //Proses Tambah Jenis Transaksi
    $('#ProsesTambahJenisTransaksi').submit(function(){
        $('#NotifikasiTambahJenisTransaksi').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');
        var form = $('#ProsesTambahJenisTransaksi')[0];
        var data = new FormData(form);
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/JenisTransaksi/ProsesTambahJenisTransaksi.php',
            data 	    :  data,
            cache       : false,
            processData : false,
            contentType : false,
            enctype     : 'multipart/form-data',
            success     : function(data){
                $('#NotifikasiTambahJenisTransaksi').html(data);
                var NotifikasiTambahJenisTransaksiBerhasil=$('#NotifikasiTambahJenisTransaksiBerhasil').html();
                if(NotifikasiTambahJenisTransaksiBerhasil=="Success"){
                    $('#NotifikasiTambahJenisTransaksi').html('');
                    $('#page').val("1");
                    $("#ProsesFilter")[0].reset();
                    $("#ProsesTambahJenisTransaksi")[0].reset();
                    $('#ModalTambahJenisTransaksi').modal('hide');
                    
                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );
                    //Menampilkan Data
                    ShowData();

                    // RESET SELECT2
                    $('#kategori').val(null).trigger('change');
                    $('#id_akun_debet').val(null).trigger('change');
                    $('#id_akun_kredit').val(null).trigger('change');

                }
            }
        });   
    });

    //------------------------------------------
    // DETAIL JENIS TRANSAKSI

    //Detail Jenis Transaksi
    $('#ModalDetail').on('show.bs.modal', function (e) {
        var id_transaksi_jenis= $(e.relatedTarget).data('id');
        $('#FormDetail').html("Loading...");
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/JenisTransaksi/FormDetail.php',
            data        : {id_transaksi_jenis: id_transaksi_jenis},
            success     : function(data){
                $('#FormDetail').html(data);
            }
        });
    });
    
    //------------------------------------------
    // EDIT JENIS TRANSAKSI
    
    // Modal Edit
    $('#ModalEdit').on('show.bs.modal', function (e) {
        var id_transaksi_jenis = $(e.relatedTarget).data('id');

        $('#FormEdit').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-secondary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        
        $('#NotifikasiEdit').html('');

        $.ajax({
            type: 'POST',
            url: '_Page/JenisTransaksi/FormEdit.php',
            data: { id_transaksi_jenis: id_transaksi_jenis },
            success: function(data) {
                $('#FormEdit').html(data);
                initSelect2Edit();
                $('#nama_edit').trigger('focus');
            },
            error: function(xhr) {
                $('#FormEdit').html(`
                    <div class="text-center text-danger">
                        <small>Gagal memuat form edit.</small>
                    </div>
                `);
                console.error(xhr.responseText);
            }
        });
    });

    //Proses Edit Anggota
    $('#ProsesEdit').submit(function(){
        $('#NotifiasiEdit').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');
        var form = $('#ProsesEdit')[0];
        var data = new FormData(form);
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/JenisTransaksi/ProsesEdit.php',
            data 	    :  data,
            cache       : false,
            processData : false,
            contentType : false,
            enctype     : 'multipart/form-data',
            success     : function(data){
                $('#NotifiasiEdit').html(data);
                var NotifiasiEditBerhasil=$('#NotifiasiEditBerhasil').html();
                if(NotifiasiEditBerhasil=="Success"){
                    $('#NotifiasiEdit').html('');
                    $('#ModalEdit').modal('hide');
                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );
                    //Menampilkan Data
                    ShowData();
                }
            }
        });
    });
    
    //------------------------------------------
    // HAPUS JENIS TRANSAKSI
    
    //Modal Hapus Anggota
    $('#ModalHapus').on('show.bs.modal', function (e) {
        var id_transaksi_jenis= $(e.relatedTarget).data('id');
        $('#FormHapus').html("Loading...");
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/JenisTransaksi/FormHapus.php',
            data        : {id_transaksi_jenis: id_transaksi_jenis},
            success     : function(data){
                $('#FormHapus').html(data);
                $('#NotifikasiHapus').html('');
            }
        });
    });
    //Proses Hapus Jenis Transaksi
    $('#ProsesHapus').submit(function(){
        $('#NotifikasiHapus').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');
        var form = $('#ProsesHapus')[0];
        var data = new FormData(form);
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/JenisTransaksi/ProsesHapus.php',
            data 	    :  data,
            cache       : false,
            processData : false,
            contentType : false,
            enctype     : 'multipart/form-data',
            success     : function(data){
                $('#NotifikasiHapus').html(data);
                var NotifikasiHapusBerhasil=$('#NotifikasiHapusBerhasil').html();
                if(NotifikasiHapusBerhasil=="Success"){
                    $('#NotifikasiHapus').html('');
                    $('#ModalHapus').modal('hide');
                    Swal.fire(
                        'Success!',
                        'Hapus Jenis Transaksi Berhasil!',
                        'success'
                    )
                    //Menampilkan Data
                    ShowData();
                }
            }
        });
    });

});









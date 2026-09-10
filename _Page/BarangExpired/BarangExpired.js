// ================================================
// FUNCTION
// ================================================

//Fungsi Untuk Menampilkan Data Barang Batch & Expired
function ShowData() {
    const ProsesFilter = $('#ProsesFilter').serialize();

    $.ajax({
        type: 'POST',
        url : '_Page/BarangExpired/TabelBarangExpired.php',
        data: ProsesFilter,

        beforeSend: function() {
            tableLoading('#tabel_barang_expired', true);
        },

        success: function(data) {
            $('#TabelBarangExpired').html(data);
            initResponsiveTable('#tabel_barang_expired');
        },

        error: function() {
            $('#TabelBarangExpired').html(`
                <tr class="table-empty">
                    <td colspan="9" class="text-center text-danger">
                        Gagal memuat data pasien
                    </td>
                </tr>
            `);
        },

        complete: function() {
            tableLoading('#tabel_barang_expired', false);
        }
    });
}

// Format tampilan item di dalam dropdown (Kode Barang Bold, Nama & Kategori Small)
function formatBarang(barang) {
    if (barang.loading) {
        return barang.text;
    }
    var $container = $(
        "<div class='select2-result-barang clearfix'>" +
            "<div class='fw-bold'></div>" +
            "<div><small class='text-muted'></small></div>" +
        "</div>"
    );
    $container.find(".fw-bold").text(barang.kode_barang);
    $container.find("small").text(barang.nama_barang + " - (" + barang.kategori_barang + ")");
    return $container;
}

// Format tampilan setelah barang dipilih di kotak utama
function formatBarangSelection(barang) {
    return barang.text || barang.kode_barang ? (barang.kode_barang + " - " + barang.nama_barang) : barang.text;
}

//Fungsi Menampilkan Data Barang
function ShowDataBarang() {
    var ProsesCariBarang = $('#ProsesCariBarang').serialize();
    $.ajax({
        type    : 'POST',
        url     : '_Page/BarangExpired/TabelBarang.php',
        data    : ProsesCariBarang,
        success: function(data) {
            $('#TabelBarang').html(data);
        }
    });
}


// ================================================
// EVENT LISTENER
// ================================================
$(document).ready(function() {

    // ---------------------------------------
    // Menampilkan Data Pertama Kali
    ShowData();

    //Ketika Batas Diubah
    $('#batas').change(function(){
        ShowData();
    });
    
    // Event ketika 'ModalFilter' ditampilkan
    $('#ModalFilter').on('shown.bs.modal', function () {
        $('#keyword').trigger('focus');
    });

    //Ketika keyword By Diubah
    $('#keyword_by').change(function(){
        var keyword_by = $('#keyword_by').val();
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/BarangExpired/FormFilterKeyword.php',
            data 	    :  {keyword_by: keyword_by},
            success     : function(data){
                $('#FormFilterKeyword').html(data);
            }
        });
    });

    //Ketika Submit Filter
    $('#ProsesFilter').submit(function(){
        //Kembalikan ke halaman 1
        $('#page_exp').val(1);
        ShowData();
        //Tutup Modal
        $('#ModalFilter').modal('hide');
    });

    //Pagging
    $(document).on('click', '#next_button', function() {
        var page_now_exp = parseInt($('#page_exp').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page_exp = page_now_exp + 1;
        $('#page_exp').val(next_page_exp);
        ShowData();
        scrollToTop();
    });
    $(document).on('click', '#prev_button', function() {
        var page_now_exp = parseInt($('#page_exp').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page_exp = page_now_exp - 1;
        $('#page_exp').val(next_page_exp);
        ShowData();
        scrollToTop();
    });

    // ---------------------------------------
    // TAMBAH BATCH DAN EXPIRED
    
    // Inisialisasi Select2 dengan Infinite Scroll & Template
    $(function () {
        const $modal  = $('#ModalTambahBarangExpired');
        const $barang = $modal.find('[name="id_barang"]');
        const $satuan = $modal.find('[name="id_barang_satuan"]');
        let requestSatuan = null;
        let urutan = 0;

        // ---------------------------------------
        // FORMAT HASIL PENCARIAN
        // ---------------------------------------
        function formatBarang(item) {
            if (!item.id || item.loading) return item.text;
            const $hasil = $('<div>');
            $('<strong>').text(item.kode_barang).appendTo($hasil);
            $('<div>').text(item.nama_barang).appendTo($hasil);
            $('<small>').text(item.kategori_barang).appendTo($hasil);
            return $hasil;
        }

        // ---------------------------------------
        // SELECT2 BARANG
        $barang.select2({
            theme: 'bootstrap-5',
            dropdownParent: $modal,
            width: '100%',
            placeholder: 'Pilih kode/nama barang...',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: '_Page/BarangExpired/ListBarang.php',
                dataType: 'json',
                delay: 300,
                data: function (params) {
                    return { keyword: params.term || '', page: params.page || 1 };
                },
                processResults: function (data) {
                    return { results: data.results, pagination: data.pagination };
                }
            },
            templateResult: formatBarang,
            templateSelection: function (item) {
                return item.text;
            },
            language: {
                searching: function () { return 'Mencari barang...'; },
                loadingMore: function () { return 'Memuat barang berikutnya...'; },
                noResults: function () { return 'Barang tidak ditemukan'; },
                errorLoading: function () { return 'Gagal memuat barang. Silakan coba lagi.'; }
            }
        });

        // ---------------------------------------
        // SATUAN UTAMA DAN SATUAN MULTI
        function muatSatuan() {
            const versi = ++urutan;
            if (requestSatuan) requestSatuan.abort();
            const idBarang = $barang.val();

            $satuan.empty().append(new Option('Pilih barang terlebih dahulu', ''))
                .prop('disabled', true);
            // Mencegah submit saat satuan belum berhasil dimuat.
            $barang[0].setCustomValidity('');

            if (!idBarang) return;
            $barang[0].setCustomValidity('Tunggu sampai satuan barang selesai dimuat.');
            $satuan.empty().append(new Option('Memuat satuan...', ''));

            requestSatuan = $.ajax({
                url: '_Page/BarangExpired/ListSatuan.php',
                type: 'GET',
                dataType: 'json',
                data: { id_barang: idBarang }
            }).done(function (data) {
                if (versi !== urutan) return;
                $satuan.empty();
                $satuan.append(new Option(data.satuan_barang, '', true, true));
                data.results.forEach(function (item) {
                    $satuan.append(new Option(item.text, item.id));
                });
                $satuan.prop('disabled', false);
                $barang[0].setCustomValidity('');
            }).fail(function (xhr, status) {
                if (status === 'abort' || versi !== urutan) return;
                const pesan = xhr.responseJSON?.message || 'Gagal memuat satuan. Pilih ulang barang.';
                $satuan.empty().append(new Option(pesan, ''));
                $barang[0].setCustomValidity(pesan);
            });
        }

        $satuan.prop('required', false);
        $barang.on('change.barangExpired', muatSatuan);
        muatSatuan();
    });

    // Generate Kode Batch
    $(document).on('click', '#ModalTambahBarangExpired .generate_batch', function () {
        const angka = Math.floor(10000000 + Math.random() * 90000000);
        $('#ModalTambahBarangExpired #no_batch').val('BTCH' + angka).trigger('change');
    });

    // PROSES TAMBAH BARANG EXPIRED
    $(document).off('submit.tambahBarangExpired', '#ProsesTambahBarangExpired').on('submit.tambahBarangExpired', '#ProsesTambahBarangExpired', function (event) {
        event.preventDefault();

        const form         = this;
        const $form        = $(form);
        const $button      = $form.find('#ButtonTambahBarangExpired');
        const $notifikasi  = $form.find('#NotifikasiTambahBarangExpired');
        const $satuan      = $form.find('[name="id_barang_satuan"]');

        // ---------------------------------------
        // VALIDASI DAN CEGAH SUBMIT GANDA
        // ---------------------------------------
        if ($form.data('submitting')) return;
        if (!form.reportValidity()) return;

        function tampilkanError(message) {
            $notifikasi.empty().append(
                $('<div>', { class: 'alert alert-danger text-center' }).append(
                    $('<small>').text(message)
                )
            );
        }

        if ($satuan.prop('disabled')) {
            tampilkanError('Satuan belum tersedia. Silakan pilih ulang barang.');
            return;
        }

        const data       = new FormData(form);
        const tombolAwal = $button.html();

        $form.data('submitting', true);
        $notifikasi.empty();
        $button.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Menyimpan...'
        );

        // KIRIM DATA
        $.ajax({
            type       : 'POST',
            url        : '_Page/BarangExpired/ProsesTambahBarangExpired.php',
            data       : data,
            processData: false,
            contentType: false,
            dataType   : 'json'
        }).done(function (response) {
            if (!response || response.status !== 'success') {
                tampilkanError(response?.message || 'Data gagal disimpan.');
                return;
            }

            // Reset filter sebelum mengatur halaman.
            const formFilter = document.getElementById('ProsesFilter');
            if (formFilter) formFilter.reset();
            $('#page_exp').val('1');

            // Reset form, Select2, dan satuan terkait.
            form.reset();
            $form.find('[name="id_barang"]').val(null).trigger('change');

            $satuan.empty()
                .append(new Option('Pilih barang terlebih dahulu', ''))
                .prop('disabled', true);

            // Tutup modal menggunakan Bootstrap 5.
            bootstrap.Modal.getOrCreateInstance(
                document.getElementById('ModalTambahBarangExpired')
            ).hide();

            showToast('success', 'Berhasil', response.message || 'Data berhasil disimpan.');
            ShowData();
        }).fail(function (xhr, textStatus) {
            let message = xhr.responseJSON?.message;

            if (!message) {
                if (textStatus === 'parsererror') {
                    message = 'Respons server bukan JSON yang valid. Periksa data sebelum mencoba kembali.';
                } else if (xhr.status === 0) {
                    message = 'Koneksi terputus. Periksa apakah data sudah tersimpan sebelum mencoba kembali.';
                } else {
                    message = 'Terjadi kesalahan server. Periksa data sebelum mencoba kembali.';
                }
            }

            tampilkanError(message);
        }).always(function () {
            $form.removeData('submitting');
            $button.prop('disabled', false).html(tombolAwal);
        });
    });
    
    // ---------------------------------------
    // DETAIL
    // ---------------------------------------

    //Modal Detail Barang Expired
    $('#ModalDetail').on('show.bs.modal', function (e) {
        var id_barang_bacth = $(e.relatedTarget).data('id');
        $('#FormDetail').html("Loading...");
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/BarangExpired/FormDetail.php',
            data        : {id_barang_bacth: id_barang_bacth},
            success     : function(data){
                $('#FormDetail').html(data);
            }
        });
    });

    //Modal Detail Barang
    $('#ModalDetailBarang').on('show.bs.modal', function (e) {
        var id_barang = $(e.relatedTarget).data('id');
        $('#FormDetailBarang').html("Loading...");
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/BarangExpired/FormDetailBarang.php',
            data        : {id_barang: id_barang},
            success     : function(data){
                $('#FormDetailBarang').html(data);
            }
        });
    });

    // ---------------------------------------
    // EDIT
    // ---------------------------------------
    //Modal Edit Barang Expired
    $('#ModalEdit').on('show.bs.modal', function (e) {
        var id_barang_bacth = $(e.relatedTarget).data('id');
        $('#FormEdit').html("Loading...");
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/BarangExpired/FormEditBarangExpired.php',
            data        : {id_barang_bacth: id_barang_bacth},
            success     : function(data){
                $('#FormEdit').html(data);
                //Kosongkan Notifikasi
                $('#NotifikasiEdit').html("");
            }
        });
    });
    
    //Proses Barang Expired
    $('#ProsesEdit').submit(function(){
        $('#NotifikasiEdit').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');
        var form = $('#ProsesEdit')[0];
        var data = new FormData(form);
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/BarangExpired/ProsesEditBarangExpired.php',
            data 	    :  data,
            cache       : false,
            processData : false,
            contentType : false,
            enctype     : 'multipart/form-data',
            success     : function(data){
                $('#NotifikasiEdit').html(data);
                var NotifikasiEditExpiredDateBerhasil=$('#NotifikasiEditExpiredDateBerhasil').html();
                if(NotifikasiEditExpiredDateBerhasil=="Success"){
                    // Tampilkan swal notifikasi
                    showToast('success', 'Berhasil', 'Data Berhasil Disimpan');

                    //Tampilkan Data
                    ShowData();

                    //Tutup Modal
                    $('#ModalEdit').modal('hide');
                }
            }
        });
    });

    // ---------------------------------------
    // HAPUS BARANG
    // ---------------------------------------

    //Hapus Barang Expired
    $('#ModalHapus').on('show.bs.modal', function (e) {
        var id_barang_bacth = $(e.relatedTarget).data('id');
        $('#FormHapus').html("Loading...");
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/BarangExpired/FormHapusBarangExpired.php',
            data        : {id_barang_bacth: id_barang_bacth},
            success     : function(data){
                $('#FormHapus').html(data);
            }
        });
    });

    //Proses Hapus
    $('#ProsesHapus').submit(function(){
        $('#NotifikasiHapus').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');
        var form = $('#ProsesHapus')[0];
        var data = new FormData(form);
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/BarangExpired/ProsesHapus.php',
            data 	    :  data,
            cache       : false,
            processData : false,
            contentType : false,
            enctype     : 'multipart/form-data',
            success     : function(data){
                $('#NotifikasiHapus').html(data);
                var NotifikasiHapusBerhasil=$('#NotifikasiHapusBerhasil').html();
                if(NotifikasiHapusBerhasil=="Success"){
                    // Tampilkan swal notifikasi
                    showToast('success', 'Berhasil', 'Data Berhasil Dihapus');

                    //Tampilkan Data
                    ShowData();

                    //Tutup Modal
                    $('#ModalHapus').modal('hide');
                }
            }
        });
    });

    // --------------------------------------------------
    // EXPORT

    // Menampilkan Modal Export
    $('#ModalExport').on('show.bs.modal', function () {
        const modal = $(this);
        const form = modal.find('form');
        const container = modal.find('#FormExport');
        const tombol = form.find('button[type="submit"]');

        tombol.prop('disabled', true);
        container.html(`
            <div class="text-center py-3">
                <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                Memuat data...
            </div>
        `);

        container.load('_Page/BarangExpired/FormExport.php', function (response, status) {
            if (status === 'error') {
                container.html(`
                    <div class="alert alert-danger mb-0">
                        Gagal memuat form export. Silakan tutup dan buka kembali modal.
                    </div>
                `);
            }
        });
    });

   // Konfirmasi Export
    $('#ModalExport').on('change', '#konfirmasi_export', function () {
        $('#ModalExport button[type="submit"]').prop('disabled', !this.checked);
    });

    // Validasi Sebelum Submit
    $('#ModalExport form').on('submit', function (event) {
        if (!$('#ModalExport #konfirmasi_export').is(':checked')) {
            event.preventDefault();
        }
    });

    // --------------------------------------------------
    // IMPORT

    //Modal Import
    $('#ModalImport').on('show.bs.modal', function (e) {
        //Bersihkan Notifikasi
        $('#NotifikasiImport').html("");
        
        //Reset Form
        $('#ProsesImport')[0].reset();
    });

    //Proses Import Data batch
    $('#ProsesImport').submit(function(){
        $('#NotifikasiImport').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');
        var form = $('#ProsesImport')[0];
        var data = new FormData(form);
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/BarangExpired/ProsesImport.php',
            data 	    :  data,
            cache       : false,
            processData : false,
            contentType : false,
            enctype     : 'multipart/form-data',
            success     : function(data){
                $('#NotifikasiImport').html(data);
                
                //Kembalikan Ke Halaman 1 
                $('#ProsesFilter')[0].reset();

                //Tampilkan Data
                ShowData();
            }
        });
    });
});







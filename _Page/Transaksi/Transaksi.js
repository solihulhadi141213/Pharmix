// ===============================================
// FUNCTION
// ===============================================

//Tabel Transaksi
function ShowData() {
    // Target And Filter
    let target = $('#tabel_transaksi');
    let data   = $('#ProsesFilter').serialize();

    target.addClass('blur-loading');

    $.ajax({
        type    : 'POST',
        url     : '_Page/Transaksi/TabelTransaksi.php',
        data    : data,
        dataType: 'json',
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

// Detail Transaksi
function DetailTransaksi(Data) {
    $.ajax({
        type   : 'POST',
        url    : '_Page/Transaksi/PutDetailTransaksi.php',
        data   : Data,
        success: function(response) {

            var status  = response.status;
            var message = response.message;
            var html    = response.html;
            $('#put_detail_transaksi').html(html);
        }
    });
}

// Rincian Transaksi
function RincianTransaksi(Data) {
    $.ajax({
        type   : 'POST',
        url    : '_Page/Transaksi/PutRincianTransaksi.php',
        data   : Data,
        success: function(response) {

            var status  = response.status;
            var message = response.message;
            var html    = response.html;
            $('#put_rincian_transaksi').html(html);
        }
    });
}

// Jurnal Transaksi
function JurnalTransaksi(Data) {
    $.ajax({
        type   : 'POST',
        url    : '_Page/Transaksi/PutJurnalTransaksi.php',
        data   : Data,
        success: function(response) {

            var status  = response.status;
            var message = response.message;
            var html    = response.html;
            $('#tabel_jurnal_transaksi').html(html);
        }
    });
}


function resetFormTambahTransaksi() {

    // Reset nilai form standar
    $('#ProsesTambahTransaksi')[0].reset();

    // Reset Select2 Jenis Transaksi
    $('#id_transaksi_jenis').val(null).trigger('change');

    // Reset tanggal dan jam
     const sekarang = new Date();

    // Tanggal YYYY-MM-DD
    const tahun = sekarang.getFullYear();
    const bulan = String(sekarang.getMonth() + 1).padStart(2, '0');
    const tanggal = String(sekarang.getDate()).padStart(2, '0');

    const tanggalSekarang =
        tahun + '-' + bulan + '-' + tanggal;

    // Jam HH:mm
    const jam = String(sekarang.getHours()).padStart(2, '0');
    const menit = String(sekarang.getMinutes()).padStart(2, '0');

    const jamSekarang = jam + ':' + menit;

    $('#tanggal').val(tanggalSekarang);
    $('#jam').val(jamSekarang);

    // Reset jumlah dan pembayaran
    $('#JumlahTotal').val('0');
    $('#JumlahTotal2').text('0');
    $('#JumlahPembayaran').val('0');

    // Reset status
    $('#status').val('Lunas');

    // Reset keterangan
    $('#keterangan').val('');

    // Hapus seluruh baris rincian dinamis
    $('#UraianTransaksi').html(`
        <tr>
            <td align="right" colspan="4">
                <b>SUBTOTAL</b>
            </td>
            <td align="right" id="JumlahTotal2">0</td>
            <td></td>
        </tr>
    `);

    // Reset notifikasi
    $('#NotifikasiTambahTransaksi').html('');

    // Reset counter jika digunakan
    counter = 1;
}

// Function to calculate and update the total amount
function calculateTotal() {

    let total = 0;

    $('#UraianTransaksi .jumlah').each(function() {

        total += unformatUang(
            $(this).val()
        );

    });

    // Tampilkan subtotal
    $('#JumlahTotal2').text(
        formatUang(total)
    );

    // Masukkan ke JumlahTotal
    $('#JumlahTotal').val(
        formatUang(total)
    );

    // Update status
    updateStatusTransaksi();
}

// Format angka ke format Rupiah/Indonesia
// Contoh:
// 1500000  -> 1.500.000
// 018000   -> 18.000
function formatUang(value) {

    value = String(value).replace(/\D/g, '');

    if (value === '') {
        return '';
    }

    value = value.replace(/^0+(?=\d)/, '');

    return value.replace(
        /\B(?=(\d{3})+(?!\d))/g,
        '.'
    );
}


// Konversi format uang menjadi angka
// 1.500.000 -> 1500000
function unformatUang(value) {

    return parseInt(
        String(value).replace(/\./g, ''),
        10
    ) || 0;

}

//Semua class nominal hanya angka
$('.nominal_angka').on('keypress', function(e) {
    // Hanya mengizinkan angka (0-9)
    if (e.which < 48 || e.which > 57) {
        e.preventDefault();
    }
});

// Hitung jumlah per baris
// Harga x QTY
function calculateRowTotal(row) {

    let harga = unformatUang(
        row.find('.harga').val()
    );

    let qty = unformatUang(
        row.find('.qty').val()
    );

    let jumlah = harga * qty;

    row.find('.jumlah').val(
        formatUang(jumlah)
    );
}

// Update Status Transaksi
function updateStatusTransaksi() {

    const jumlah = unformatUang(
        $('#JumlahTotal').val()
    );

    const pembayaran = unformatUang(
        $('#JumlahPembayaran').val()
    );

    let status = 'Lunas';

    // Jika jumlah dan pembayaran sama
    if (jumlah === pembayaran) {

        status = 'Lunas';

    }

    // PENGELUARAN
    else if (kategoriTransaksi === 'Pengeluaran') {

        if (jumlah > pembayaran) {

            status = 'Utang';

        } else if (jumlah < pembayaran) {

            status = 'Piutang';

        }

    }

    // PEMASUKAN
    else if (kategoriTransaksi === 'Pemasukan') {

        if (jumlah > pembayaran) {

            status = 'Piutang';

        } else if (jumlah < pembayaran) {

            status = 'Utang';

        }

    }

    $('#status').val(status);
}

// Fungsi mengambil angka dari format uang
function getNominalEdit(value) {
    if (value === undefined || value === null) return 0;
    value = String(value).replace(/\D/g, '');
    return value === '' ? 0 : parseInt(value, 10) || 0;
}

// Fungsi format uang
function formatNominalEdit(value) {
    return getNominalEdit(value).toLocaleString('id-ID');
}

// Fungsi menentukan status transaksi
function updateStatusEdit(kategori = null) {
    let jumlah = getNominalEdit($('#JumlahTotalEdit').val());
    let pembayaran = getNominalEdit($('#JumlahPembayaranEdit').val());

    if (!kategori) {
        let selectedOption = $('#id_transaksi_jenis_edit option:selected');
        kategori = selectedOption.data('kategori') || '';
    }

    kategori = String(kategori).trim().toLowerCase();
    let status = 'Lunas';

    if (kategori === 'pengeluaran') {
        if (jumlah > pembayaran) status = 'Utang';
        else if (jumlah < pembayaran) status = 'Piutang';
        else status = 'Lunas';
    } else if (kategori === 'pemasukan') {
        if (jumlah > pembayaran) status = 'Piutang';
        else if (jumlah < pembayaran) status = 'Utang';
        else status = 'Lunas';
    } else {
        status = 'Lunas';
    }

    $('#status_edit').val(status);
}

function initSelect2TransaksiJenisEdit() {
    $('#id_transaksi_jenis_edit').select2({
        dropdownParent    : $('#ModalEdit'),
        width             : '100%',
        theme             : 'bootstrap-5',
        placeholder       : 'Pilih Kategori Operasional',
        allowClear        : true,
        minimumInputLength: 0,
        ajax: {
            url: '_Page/Transaksi/ListTransaksiJenis.php',
            type: 'GET',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    search: params.term || '',
                    page: params.page || 1
                };
            },
            processResults: function(data, params) {
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
        templateResult: function(data) {
            if (data.loading) {
                return data.text;
            }
            let $result = $(`
                <div>
                    <div><strong></strong></div>
                    <small class="text-muted"></small>
                </div>
            `);
            $result.find('strong').text(data.nama || data.text);
            $result.find('small').text(data.kategori || '');
            return $result;
        },
        templateSelection: function(data) {
            return data.text || data.nama || '';
        }
    });

    // Ketika jenis transaksi berubah
    $('#id_transaksi_jenis_edit').on('select2:select', function(e) {
        let data = e.params.data;
        let kategori = data.kategori || '';
        // Simpan kategori pada option
        $(this).find('option:selected').attr('data-kategori', kategori);
        // Hitung ulang status
        updateStatusEdit(kategori);
    });

    // Ketika pilihan dihapus
    $('#id_transaksi_jenis_edit').on('select2:clear', function() {
        $('#status_edit').val('Lunas');
    });
}

//-------------------------------------------------
// Format angka uang & hitung jumlah rincian
//-------------------------------------------------

// Fungsi mengubah angka menjadi format ribuan
function formatUang(value) {
    value = String(value).replace(/\D/g, '').replace(/^0+(?=\d)/, '');
    return value === '' ? '' : value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Fungsi mengambil angka murni dari format uang
function angkaMurni(value) {
    return parseInt(String(value).replace(/\./g, '').replace(/\D/g, ''), 10) || 0;
}

// Fungsi menghitung jumlah
function hitungJumlahRincian() {
    let harga = angkaMurni($('#uraian_harga').val());
    let qty = angkaMurni($('#uraian_qty').val());
    let jumlah = harga * qty;
    
    $('#uraian_jumlah').val(jumlah > 0 ? formatUang(jumlah) : '');
}

// ===============================================
// EVENT LISTENER
// ===============================================
$(document).ready(function() {

    // Tampilkan 'data_view'
    $('#data_view').show();

    // Sembunyikan 'detail_view'
    $('#detail_view').hide();

    // Sembunyikan 'tambah_transaksi'
    $('#tambah_transaksi_view').hide();

    // Menampilkan Ddata Pertama Kali
    ShowData();

    // Pagging
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

    // Change keyword_by
    $('#keyword_by').change(function(){
        var keyword_by = $('#keyword_by').val();
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Transaksi/FormFilter.php',
            data 	    :  {keyword_by: keyword_by},
            success     : function(data){
                $('#FormFilter').html(data);
            }
        });
    });

    // Submit Filter
    $('#ProsesFilter').submit(function(){

        // Reset Page
        $('#page').val("1");

        // Close Modal
        $('#ModalFilter').modal('hide');

        // Reload Data
        ShowData();
        
    });

    //-------------------------------------------------
    //Detail Jenis Transaksi
    //-------------------------------------------------
    $('#ModalDetail').on('show.bs.modal', function (e) {

        // Tangkap 'id_transaksi'
        var id_transaksi= $(e.relatedTarget).data('id');

        // Disable 'TombolSelengkapnya'
        $('#TombolSelengkapnya').prop('disabled', true);

        // Loading Form
        $('#FormDetail').html("Loading...");

        // Tampilkan Data Dengan AJAX
        $.ajax({
            type    : 'POST',
            url     : '_Page/Transaksi/FormDetail.php',
            data    : {id_transaksi: id_transaksi},
            dataType: 'JSON',
            success : function(response){

                // Status, Message & html
                var status  = response.status;
                var message = response.message;
                var html    = response.html;

                // Jika Success
                if(status=='success'){

                    // Tampilkan Data
                    $('#FormDetail').html(html);

                    // Enable Tombol
                    $('#TombolSelengkapnya').prop('disabled', false);
                }else{
                    $('#FormDetail').html(html);
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormDetail').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            },
        });
    });

    // Proses Detail
    $('#ProsesDetailTransaksi').submit(function(){

        // Tangkap Data Dari Form
        var ProsesDetailTransaksi = $('#ProsesDetailTransaksi').serialize();
        
        // Tutup Modal Detail
        $('#ModalDetail').modal('hide');

        // Sembunyikan 'data_view'
        $('#data_view').hide();

        // Tampilkan 'detail_view'
        $('#detail_view').show();

        // Sembunyikan 'tambah_transaksi'
        $('#tambah_transaksi_view').hide();

        // Menampilkan Detail Transaksi
        DetailTransaksi(ProsesDetailTransaksi);

        // Menampilkan rincian Transaksi
        RincianTransaksi(ProsesDetailTransaksi);

        // Menampilkan Jurnal
        JurnalTransaksi(ProsesDetailTransaksi);
    });

    // Kembali Ke data_view
    $('.back_to_data').click(function(){

        // Tampilkan 'data_view'
        $('#data_view').show();

        // Sembunyikan 'detail_view'
        $('#detail_view').hide();

        // Sembunyikan 'tambah_transaksi'
        $('#tambah_transaksi_view').hide();
    });

    //-------------------------------------------------
    //Tambah Transaksi
    //-------------------------------------------------
     $('.tambah_transaksi').click(function(){

        // Sembunyikan 'data_view'
        $('#data_view').hide();

        // Sembunyikan 'detail_view'
        $('#detail_view').hide();

        // Tampilkan 'tambah_transaksi'
        $('#tambah_transaksi_view').show();
    });

    // Select2 Transaksi Jenis
    $('#id_transaksi_jenis').select2({
        theme      : 'bootstrap-5',
        width      : '100%',
        placeholder: 'Pilih Kategori Operasional',
        allowClear : true,

        ajax       : {
            url     : '_Page/Transaksi/GetTransaksiJenis.php',
            type    : 'GET',
            dataType: 'json',
            delay   : 250,
            data    : function(params) {

                return {
                    search: params.term || '',
                    page: params.page || 1
                };
            },

            processResults: function(data, params) {

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

        minimumInputLength: 0
    });

    // Ketika Jenis Transaksi Dipilih
    $('#id_transaksi_jenis').on('select2:select', function(e) {

        const data = e.params.data;

        // Simpan kategori transaksi
        kategoriTransaksi = data.kategori || '';

        // Hitung ulang status berdasarkan
        // jumlah dan pembayaran yang sudah ada
        updateStatusTransaksi();

    });

    // Ketika Jenis Transaksi Dihapus
    $('#id_transaksi_jenis').on('select2:clear', function() {

        kategoriTransaksi = '';

        // Karena tidak ada kategori transaksi,
        // kembalikan status ke default
        $('#status').val('Lunas');

    });

    // Ketika User Menambahkan Uraian
    $('#TambahUraian').click(function() {

        // Cek Jenis Transaksi
        const idTransaksiJenis = $('#id_transaksi_jenis').val();

        if (!idTransaksiJenis) {

            Swal.fire({
                icon: 'warning',
                title: 'Jenis Transaksi Belum Dipilih',
                text: 'Silakan pilih Jenis Transaksi terlebih dahulu.',
                confirmButtonText: 'OK'
            });

            // Fokus ke Select2
            $('#id_transaksi_jenis').select2('open');

            return;
        }

        // Cek kategori
        if (!kategoriTransaksi) {

            Swal.fire({
                icon: 'warning',
                title: 'Kategori Tidak Ditemukan',
                text: 'Kategori transaksi tidak dapat ditentukan.',
                confirmButtonText: 'OK'
            });

            return;
        }

        // Hapus notifikasi awal
        $('#NotifikasiAwalUraian').remove();

        // Tambahkan baris
        $('#UraianTransaksi').append(`
            <tr>

                <td>
                    <input
                        type="text"
                        name="uraian[]"
                        class="form-control"
                        placeholder="Uraian">
                </td>

                <td>
                    <input
                        type="text"
                        name="harga[]"
                        class="form-control harga"
                        placeholder="Rp 0"
                        inputmode="numeric">
                </td>

                <td>
                    <input
                        type="text"
                        name="qty[]"
                        class="form-control qty"
                        placeholder="QTY"
                        inputmode="numeric">
                </td>

                <td>
                    <input
                        type="text"
                        name="satuan[]"
                        class="form-control"
                        placeholder="Satuan">
                </td>

                <td>
                    <input
                        type="text"
                        name="jumlah[]"
                        class="form-control jumlah"
                        placeholder="Jumlah"
                        readonly>
                </td>

                <td class="text-center">
                    <button
                        type="button"
                        class="btn btn-danger btn-sm remove-row"
                        title="Hapus">
                        <i class="bi bi-x"></i>
                    </button>
                </td>

            </tr>
        `);

    });


    // Format Harga dan QTY ketika diketik
    $(document).on('input', '.harga, .qty', function() {

        // Format input
        $(this).val(
            formatUang($(this).val())
        );

        // Ambil baris
        const row = $(this).closest('tr');

        // Harga
        const harga = unformatUang(
            row.find('.harga').val()
        );

        // Qty
        const qty = unformatUang(
            row.find('.qty').val()
        );

        // Hitung
        const jumlah = harga * qty;

        // Masukkan jumlah
        row.find('.jumlah').val(
            formatUang(jumlah)
        );

        // Hitung total
        calculateTotal();

    });

    // Hapus baris
    $(document).on('click', '.remove-row', function() {

        $(this).closest('tr').remove();

        calculateTotal();

    });

    // Format Jumlah Pembayaran
    $(document).on('input', '#JumlahPembayaran', function() {

        // Format angka
        $(this).val(
            formatUang($(this).val())
        );

        // Update status
        updateStatusTransaksi();

    });

    //Proses Tambah Transaksi
    $('#ProsesTambahTransaksi').submit(function(){

        // Menangkap Element Tombol
        var TombolTambahTransaksi = $('#TombolTambahTransaksi').html();

        // Disable Button
        $('#TombolTambahTransaksi').prop('disabled', true);

        // Loading Tombol
        $('#TombolTambahTransaksi').html('Loading...');

        // Kosongkan Notifikasi
        $('#NotifikasiTambahTransaksi').html('');

        // Tangkap Data Dari Form
        var form = $('#ProsesTambahTransaksi')[0];
        var data = new FormData(form);

        // Kirim Dengan AJAX
        $.ajax({
            type       : 'POST',
            url        : '_Page/Transaksi/ProsesTambahTransaksi.php',
            data       : data,
            cache      : false,
            processData: false,
            contentType: false,
            dataType   : 'JSON',
            enctype    : 'multipart/form-data',
            success    : function(response){

                // Tangkap status & message
                var status = response.status;
                var message = response.message;

                // Apabila Berhasil
                if(status=='success'){
                    
                    // RESET SELURUH FORM
                    resetFormTambahTransaksi();

                    // =============================================
                    // KEMBALI KE DATA
                    // =============================================

                    // Tampilkan data_view
                    $('#data_view').show();

                    // Sembunyikan detail_view
                    $('#detail_view').hide();

                    // Sembunyikan tambah_transaksi_view
                    $('#tambah_transaksi_view').hide();

                    // Reload Data
                    ShowData();

                    // Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );

                }else{
                    $('#NotifikasiTambahTransaksi').html('<div class="alert alert-danger"><small><b>Opss!</b> '+message+'</small></div>');
                }
            },
            
            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#NotifikasiTambahTransaksi').html(`<div class="alert alert-danger"><small>Terjadi kesalahan server.</small></div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#TombolTambahTransaksi').prop('disabled', false);
                $('#TombolTambahTransaksi').html(TombolTambahTransaksi);
            }
        });
    });

    //-------------------------------------------------
    //Edit Transaksi
    //-------------------------------------------------
    $('#ModalEdit').on('show.bs.modal', function (e) {

        // Tangkap 'id_transaksi'
        var id_transaksi= $(e.relatedTarget).data('id');

        // Disable 'TombolEdit'
        $('#TombolEdit').prop('disabled', true);

        // Kosongkan 'NotifikasiEdit'
        $('#NotifikasiEdit').html("");

        // Loading 'FormEdit'
        $('#FormEdit').html("Loading...");

        // Tampilkan Data Dengan AJAX
        $.ajax({
            type    : 'POST',
            url     : '_Page/Transaksi/FormEdit.php',
            data    : {id_transaksi: id_transaksi},
            dataType: 'JSON',
            success : function(response){

                // Status, Message & html
                var status  = response.status;
                var message = response.message;
                var html    = response.html;

                // Jika Success
                if(status=='success'){

                    // Tampilkan Form Edit
                    $('#FormEdit').html(html);

                    // Inisialisasi Select2
                    initSelect2TransaksiJenisEdit();

                    // Enable tombol
                    $('#TombolEdit').prop('disabled', false);
                }else{
                    $('#FormEdit').html(html);
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormEdit').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            },
        });
    });

    // Format pembayaran ketika user mengetik
    $(document).on('click', '.edit_transaksi', function () {

        // Tampilkan Modal
        $('#ModalEdit').modal("show");
        
        // Tangkap Data Dari Form
        var ProsesDetailTransaksi = $('#ProsesDetailTransaksi').serialize();

        // Disable 'TombolEdit'
        $('#TombolEdit').prop('disabled', true);

        // Kosongkan 'NotifikasiEdit'
        $('#NotifikasiEdit').html("");

        // Loading 'FormEdit'
        $('#FormEdit').html("Loading...");

        // Tampilkan Data Dengan AJAX
        $.ajax({
            type    : 'POST',
            url     : '_Page/Transaksi/FormEdit.php',
            data    : ProsesDetailTransaksi,
            dataType: 'JSON',
            success : function(response){

                // Status, Message & html
                var status  = response.status;
                var message = response.message;
                var html    = response.html;

                // Jika Success
                if(status=='success'){

                    // Tampilkan Form Edit
                    $('#FormEdit').html(html);

                    // Inisialisasi Select2
                    initSelect2TransaksiJenisEdit();

                    // Enable tombol
                    $('#TombolEdit').prop('disabled', false);
                }else{
                    $('#FormEdit').html(html);
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormEdit').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            },
        });

    });

    // Format pembayaran ketika user mengetik
    $(document).on('input', '#JumlahPembayaranEdit', function () {
        let value = getNominalEdit($(this).val());
        $(this).val(formatNominalEdit(value));
        updateStatusEdit();
    });

    // Cegah karakter selain angka pada pembayaran
    $(document).on('keypress', '#JumlahPembayaranEdit', function (e) {
        if (e.ctrlKey || e.metaKey || [8, 9, 13, 27, 37, 38, 39, 40].includes(e.which)) {
            return;
        }
        if (e.which < 48 || e.which > 57) {
            e.preventDefault();
        }
    });

    // Ketika pembayaran kehilangan focus
    $(document).on('blur', '#JumlahPembayaranEdit', function () {
        let value = getNominalEdit($(this).val());
        $(this).val(formatNominalEdit(value));
        updateStatusEdit();
    });

    // Ketika jenis transaksi berubah
    $(document).on('change', '#id_transaksi_jenis_edit', function () {
        let id_transaksi_jenis = $(this).val();

        if (!id_transaksi_jenis) {
            $('#status_edit').val('Lunas');
            return;
        }

        $('#status_edit').val('Memuat...');

        $.ajax({
            type: 'POST',
            url: '_Page/Transaksi/GetKategoriTransaksi.php',
            data: { id_transaksi_jenis: id_transaksi_jenis },
            dataType: 'JSON',
            success: function (response) {
                if (response.status === 'success') {
                    let kategori = response.kategori;
                    $('#id_transaksi_jenis_edit option:selected').attr('data-kategori', kategori);
                    updateStatusEdit(kategori);
                } else {
                    $('#status_edit').val('Lunas');
                    console.log(response.message);
                }
            },
            error: function (xhr, status, error) {
                console.log('XHR:', xhr);
                console.log('STATUS:', status);
                console.log('ERROR:', error);
                console.log('RESPONSE:', xhr.responseText);
                $('#status_edit').val('Lunas');
            }
        });
    });

    //Proses Edit Transaksi
    $('#ProsesEdit').submit(function(){

        // Menangkap Element Tombol 'TombolEdit'
        var TombolEdit = $('#TombolEdit').html();

        // Disable Tombol 'TombolEdit'
        $('#TombolEdit').prop('disabled', true);

        // Loading 'TombolEdit'
        $('#TombolEdit').html('Loading..');

        // Kosongkan Notifikasi
        $('#NotifikasiEdit').html('');

         // Tangkap Data
        var form = $('#ProsesEdit')[0];
        var data = new FormData(form);

        // Kirim Data Dengan Ajax
        $.ajax({
            type       : 'POST',
            url        : '_Page/Transaksi/ProsesEdit.php',
            data       : data,
            cache      : false,
            processData: false,
            contentType: false,
            dataType   : 'JSON',
            enctype    : 'multipart/form-data',
            success    : function(response){

                // Tangkap status dan message
                var status = response.status;
                var message = response.message;

                // Jika Berhasil
                // Apabila Berhasil
                if(status=='success'){

                    var ProsesDetailTransaksi = $('#ProsesDetailTransaksi').serialize();
                    
                    // Tutup 'ModalEdit'
                    $('#ModalEdit').modal('hide');

                    // Reload Data
                    ShowData();

                    // Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );

                    // Reload Detail
                    DetailTransaksi(ProsesDetailTransaksi);

                }else{
                    $('#NotifikasiEdit').html('<div class="alert alert-danger"><small><b>Opss!</b> '+message+'</small></div>');
                }
                
            },

             // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#NotifikasiEdit').html(`<div class="alert alert-danger"><small>Terjadi kesalahan server.</small></div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#TombolEdit').prop('disabled', false);
                $('#TombolEdit').html(TombolEdit);
            }
        });
    });


    //-------------------------------------------------
    //Hapus Transaksi
    //-------------------------------------------------
    $('#ModalHapus').on('show.bs.modal', function (e) {

        // Tangkap 'id_transaksi'
        var id_transaksi= $(e.relatedTarget).data('id');

        // Disable 'TombolHapus'
        $('#TombolHapus').prop('disabled', true);

        // Kosongkan 'NotifikasiHapus'
        $('#NotifikasiHapus').html("");

        // Loading 'FormHapus'
        $('#FormHapus').html("Loading...");

        // Tampilkan Data Dengan AJAX
        $.ajax({
            type    : 'POST',
            url     : '_Page/Transaksi/FormHapus.php',
            data    : {id_transaksi: id_transaksi},
            dataType: 'JSON',
            success : function(response){

                // Status, Message & html
                var status  = response.status;
                var message = response.message;
                var html    = response.html;

                // Jika Success
                if(status=='success'){

                    // Tampilkan 'FormHapus'
                    $('#FormHapus').html(html);

                    // Enable 'TombolHapus'
                    $('#TombolHapus').prop('disabled', false);
                }else{
                    $('#FormHapus').html(html);
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormHapus').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            },
        });
    });

    
    //Proses Hapus Jenis Transaksi
    $('#ProsesHapus').submit(function(){

        // Menangkap Element Tombol 'TombolHapus'
        var TombolHapus = $('#TombolHapus').html();

        // Disable Tombol 'TombolHapus'
        $('#TombolHapus').prop('disabled', true);

        // Loading 'TombolHapus'
        $('#TombolHapus').html('Loading..');

        // Kosongkan Notifikasi
         $('#NotifikasiHapus').html('');

         // Tangkap Data
        var form = $('#ProsesHapus')[0];
        var data = new FormData(form);

        // Kirim Data Dengan Ajax
        $.ajax({
            type       : 'POST',
            url        : '_Page/Transaksi/ProsesHapus.php',
            data       : data,
            cache      : false,
            processData: false,
            contentType: false,
            dataType   : 'JSON',
            enctype    : 'multipart/form-data',
            success    : function(response){

                // Tangkap status dan message
                var status = response.status;
                var message = response.message;

                // Jika Berhasil
                // Apabila Berhasil
                if(status=='success'){
                    
                    // Tutup Modal
                    $('#ModalHapus').modal('hide');

                    // Reload Data
                    ShowData();

                    // Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil dihapus.'
                    );

                }else{
                    $('#NotifikasiHapus').html('<div class="alert alert-danger"><small><b>Opss!</b> '+message+'</small></div>');
                }
                
            },

             // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#NotifikasiHapus').html(`<div class="alert alert-danger"><small>Terjadi kesalahan server.</small></div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#TombolHapus').prop('disabled', false);
                $('#TombolHapus').html(TombolHapus);
            }
        });
    });

    //-------------------------------------------------
    //Tambah Rincian Transaksi
    //-------------------------------------------------
    $('#ModalTambahRincian').on('show.bs.modal', function (e) {

        // Tangkap 'ProsesDetailTransaksi'
        var ProsesDetailTransaksi = $('#ProsesDetailTransaksi').serialize();

        // Disable 'TombolTambahRincian'
        $('#TombolTambahRincian').prop('disabled', true);

        // Kosongkan 'NotifikasiTambahRincian'
        $('#NotifikasiTambahRincian').html("");

        // Loading 'FormTambahRincian'
        $('#FormTambahRincian').html("Loading...");

        // Tampilkan Data Dengan AJAX
        $.ajax({
            type    : 'POST',
            url     : '_Page/Transaksi/FormTambahRincian.php',
            data    : ProsesDetailTransaksi,
            dataType: 'JSON',
            success : function(response){

                // Status, Message & html
                var status  = response.status;
                var message = response.message;
                var html    = response.html;

                // Jika Success
                if(status=='success'){

                    // Tampilkan 'FormTambahRincian'
                    $('#FormTambahRincian').html(html);

                    // Enable 'TombolTambahRincian'
                    $('#TombolTambahRincian').prop('disabled', false);
                }else{
                    $('#FormTambahRincian').html(html);
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormTambahRincian').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            },
        });
    });

    // Event Handler Input Harga & QTY
    $(document).on('input', '#uraian_harga', function () {
        let cursorPosition = this.selectionStart;
        let oldLength = this.value.length;
        
        this.value = formatUang(this.value);
        
        cursorPosition += this.value.length - oldLength;
        this.setSelectionRange(cursorPosition, cursorPosition);
        hitungJumlahRincian();
    });

    $(document).on('input', '#uraian_qty', function () {
        this.value = formatUang(this.value.replace(/\D/g, ''));
        hitungJumlahRincian();
    });

    //Proses Tambah Rincian
    $('#ProsesTambahRincian').submit(function(){

        // Menangkap Element Tombol 'TombolTambahRincian'
        var TombolTambahRincian = $('#TombolTambahRincian').html();

        // Disable Tombol 'TombolTambahRincian'
        $('#TombolTambahRincian').prop('disabled', true);

        // Loading 'TombolTambahRincian'
        $('#TombolTambahRincian').html('Loading..');

        // Kosongkan 'NotifikasiTambahRincian'
        $('#NotifikasiTambahRincian').html('');

         // Tangkap Data
        var form = $('#ProsesTambahRincian')[0];
        var data = new FormData(form);

        // Kirim Data Dengan Ajax
        $.ajax({
            type       : 'POST',
            url        : '_Page/Transaksi/ProsesTambahRincian.php',
            data       : data,
            cache      : false,
            processData: false,
            contentType: false,
            dataType   : 'JSON',
            enctype    : 'multipart/form-data',
            success    : function(response){

                // Tangkap status dan message
                var status = response.status;
                var message = response.message;

                // Jika Berhasil
                // Apabila Berhasil
                if(status=='success'){

                    var ProsesDetailTransaksi = $('#ProsesDetailTransaksi').serialize();
                    
                    // Tutup 'ModalTambahRincian'
                    $('#ModalTambahRincian').modal('hide');

                    // Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );

                    // Reload Detail
                    RincianTransaksi(ProsesDetailTransaksi);
                    DetailTransaksi(ProsesDetailTransaksi);

                }else{
                    $('#NotifikasiTambahRincian').html('<div class="alert alert-danger"><small><b>Opss!</b> '+message+'</small></div>');
                }
                
            },

             // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#NotifikasiTambahRincian').html(`<div class="alert alert-danger"><small>Terjadi kesalahan server.</small></div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#TombolTambahRincian').prop('disabled', false);
                $('#TombolTambahRincian').html(TombolTambahRincian);
            }
        });
    });

    //-------------------------------------------------
    // Edit Rincian Transaksi
    //-------------------------------------------------
    $('#ModalEditRincian').on('show.bs.modal', function (e) {
        var id_transaksi_rincian = $(e.relatedTarget).data('id');
        $('#TombolEditRincian').prop('disabled', true);
        $('#NotifikasiEditRincian').html('');
        $('#FormEditRincian').html('Loading...');

        $.ajax({
            type: 'POST',
            url: '_Page/Transaksi/FormEditRincian.php',
            data: { id_transaksi_rincian: id_transaksi_rincian },
            dataType: 'JSON',
            success: function(response) {
                var status  = response.status;
                var message = response.message;
                var html    = response.html;

                if (status === 'success') {
                    $('#FormEditRincian').html(html);
                    $('#TombolEditRincian').prop('disabled', false);
                } else {
                    $('#FormEditRincian').html(html);
                }
            },
            error: function(xhr, status, error) {
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);
                $('#FormEditRincian').html(`
                    <div class="alert alert-danger">
                        <small>Terjadi kesalahan server.</small>
                    </div>
                `);
            }
        });
    });

    // Format angka uang
    function formatUangEditRincian(value) {
        value = String(value).replace(/\D/g, '');
        if (value === '') { return ''; }
        value = value.replace(/^0+(?=\d)/, '');
        return value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Ambil angka murni
    function angkaMurniEditRincian(value) {
        return parseInt(
            String(value).replace(/\./g, '').replace(/\D/g, ''),
            10
        ) || 0;
    }

    // Hitung jumlah rincian
    function hitungJumlahEditRincian() {
        var harga = angkaMurniEditRincian($('#uraian_harga_edit').val());
        var qty = angkaMurniEditRincian($('#uraian_qty_edit').val());
        var jumlah = harga * qty;

        if (jumlah > 0) {
            $('#uraian_jumlah_edit').val(formatUangEditRincian(jumlah));
        } else {
            $('#uraian_jumlah_edit').val('');
        }
    }

    // Input Harga
    $(document).on('input', '#uraian_harga_edit', function() {
        this.value = formatUangEditRincian(this.value);
        hitungJumlahEditRincian();
    });

    // Input QTY
    $(document).on('input', '#uraian_qty_edit', function() {
        this.value = formatUangEditRincian(this.value);
        hitungJumlahEditRincian();
    });

    // Proses Edit Rincian
    $('#ProsesEditRincian').submit(function(e) {
        e.preventDefault();
        var TombolEditRincian = $('#TombolEditRincian').html();
        $('#TombolEditRincian').prop('disabled', true);
        $('#TombolEditRincian').html('Loading..');
        $('#NotifikasiEditRincian').html('');

        var form = $('#ProsesEditRincian')[0];
        var data = new FormData(form);

        $.ajax({
            type: 'POST',
            url: '_Page/Transaksi/ProsesEditRincianTransaksi.php',
            data: data,
            cache: false,
            processData: false,
            contentType: false,
            dataType: 'JSON',
            success: function(response) {
                var status  = response.status;
                var message = response.message;

                if (status === 'success') {
                    $('#ModalEditRincian').modal('hide');
                    var id_transaksi = response.id_transaksi;
                    var ProsesDetailTransaksi = $('#ProsesDetailTransaksi').serialize();

                    RincianTransaksi(ProsesDetailTransaksi);
                    DetailTransaksi(ProsesDetailTransaksi);

                    if (typeof ShowData === 'function') {
                        ShowData();
                    }

                    showToast(
                        'success',
                        'Berhasil',
                        'Rincian transaksi berhasil diubah.'
                    );
                } else {
                    $('#NotifikasiEditRincian').html(`
                        <div class="alert alert-danger">
                            <small>
                                <b>Opss!</b> ${message}
                            </small>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                $('#NotifikasiEditRincian').html(`
                    <div class="alert alert-danger">
                        <small>
                            Terjadi kesalahan server.
                        </small>
                    </div>
                `);
            },
            complete: function() {
                $('#TombolEditRincian').prop('disabled', false);
                $('#TombolEditRincian').html(TombolEditRincian);
            }
        });
    });

    //-------------------------------------------------
    // Hapus Rincian Transaksi
    //-------------------------------------------------
    $('#ModalHapusRincian').on('show.bs.modal', function (e) {
        var id_transaksi_rincian = $(e.relatedTarget).data('id');
        $('#TombolHapusRincian').prop('disabled', true);
        $('#NotifikasiHapusRincian').html('');
        $('#FormHapusRincian').html(`
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                <span class="ms-2">Memuat data...</span>
            </div>
        `);

        if (!id_transaksi_rincian) {
            $('#FormHapusRincian').html(`
                <div class="alert alert-danger">
                    <small>ID rincian transaksi tidak valid.</small>
                </div>
            `);
            return;
        }

        $.ajax({
            type: 'POST',
            url: '_Page/Transaksi/FormHapusRincian.php',
            data: { id_transaksi_rincian: id_transaksi_rincian },
            dataType: 'JSON',
            success: function(response) {
                if (response.status === 'success') {
                    $('#FormHapusRincian').html(response.html);
                    $('#TombolHapusRincian').prop('disabled', false);
                } else {
                    $('#FormHapusRincian').html(response.html);
                }
            },
            error: function(xhr, status, error) {
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);
                $('#FormHapusRincian').html(`
                    <div class="alert alert-danger">
                        <small>Terjadi kesalahan saat mengambil data rincian.</small>
                    </div>
                `);
            }
        });
    });

    // Proses Hapus Rincian
    $('#ProsesHapusRincian').submit(function(e) {
        e.preventDefault();
        var TombolHapusRincian = $('#TombolHapusRincian').html();
        $('#TombolHapusRincian').prop('disabled', true);
        $('#TombolHapusRincian').html(`
            <span class="spinner-border spinner-border-sm me-1"></span>
            Menghapus...
        `);
        $('#NotifikasiHapusRincian').html('');

        var form = $('#ProsesHapusRincian')[0];
        var data = new FormData(form);

        $.ajax({
            type: 'POST',
            url: '_Page/Transaksi/ProsesHapusRincian.php',
            data: data,
            cache: false,
            processData: false,
            contentType: false,
            dataType: 'JSON',
            success: function(response) {
                var status  = response.status;
                var message = response.message;

                if (status === 'success') {
                    var ProsesDetailTransaksi = $('#ProsesDetailTransaksi').serialize();
                    $('#ModalHapusRincian').modal('hide');
                    showToast('success', 'Berhasil', message);
                    RincianTransaksi(ProsesDetailTransaksi);
                    DetailTransaksi(ProsesDetailTransaksi);
                } else {
                    $('#NotifikasiHapusRincian').html(`
                        <div class="alert alert-danger">
                            <small><b>Opss!</b> ${message}</small>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);
                $('#NotifikasiHapusRincian').html(`
                    <div class="alert alert-danger">
                        <small>Terjadi kesalahan server.</small>
                    </div>
                `);
            },
            complete: function() {
                $('#TombolHapusRincian').prop('disabled', false);
                $('#TombolHapusRincian').html(TombolHapusRincian);
            }
        });
    });
    
    //-------------------------------------------------
    // TAMBAH JURNAL
    //-------------------------------------------------

    //Modal Tambah Jurnal
    $('#ModalTambahJurnal').on('show.bs.modal', function (e) {
        
        // Tangkap 'ProsesDetailTransaksi'
        var ProsesDetailTransaksi = $('#ProsesDetailTransaksi').serialize();

        // Disable Tombol
        $('#TombolTambahJurnal').prop('disabled', true);

        // Kosongkan Notifikasi
        $('#NotifikasiTambahJurnal').html('');

        // Loading Form
        $('#FormTambahJurnal').html(`
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                <span class="ms-2">Memuat data...</span>
            </div>
        `);
        
        // Buka Form Dengan AJAX
        $.ajax({
            type    : 'POST',
            url     : '_Page/Transaksi/FormTambahJurnal.php',
            data    : ProsesDetailTransaksi,
            dataType: 'JSON',
            success : function(response) {
                if (response.status === 'success') {
                    $('#FormTambahJurnal').html(response.html);
                    $('#TombolTambahJurnal').prop('disabled', false);

                    $('#kode_perkiraan').select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#ModalTambahJurnal'),
                        width: '100%',
                        placeholder: 'Pilih Akun Perkiraan',
                        allowClear: true,
                        minimumInputLength: 0,
                        ajax: {
                            url: '_Page/Transaksi/ListAkunPerkiraan.php',
                            type: 'GET',
                            dataType: 'json',
                            delay: 300,
                            data: function(params) {
                                return {
                                    search: params.term || '',
                                    page: params.page || 1
                                };
                            },
                            processResults: function(data, params) {
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
                        templateResult: function(data) {
                            if (data.loading) {
                                return data.text;
                            }
                            var $result = $(`
                                <div class="py-1">
                                    <div class="fw-semibold akun-nama"></div>
                                    <small class="text-muted akun-saldo"></small>
                                </div>
                            `);
                            $result.find('.akun-nama').text(data.kode + ' - ' + data.nama);
                            $result.find('.akun-saldo').text('Saldo Normal: ' + (data.saldo_normal || '-'));
                            return $result;
                        },
                        templateSelection: function(data) {
                            if (!data.id) {
                                return data.text;
                            }
                            return data.kode + ' - ' + data.nama;
                        }
                    });
                } else {
                    $('#FormTambahJurnal').html(response.html);
                }
            },
            error: function(xhr, status, error) {
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);
                $('#NotifikasiTambahJurnal').html(`
                    <div class="alert alert-danger">
                        <small>Terjadi kesalahan saat menampilkan form.</small>
                    </div>
                `);
            }
        });
    });

    // Format Nilai Jurnal
    $(document).on('input', '#nilai', function () {
        let value = $(this).val();

        // Hanya angka
        value = value.replace(/\D/g, '');

        // Hilangkan leading zero
        value = value.replace(/^0+(?=\d)/, '');

        // Jika kosong
        if (value === '') {
            $(this).val('');
            return;
        }

        // Format ribuan
        value = value.replace(
            /\B(?=(\d{3})+(?!\d))/g,
            '.'
        );

        $(this).val(value);
    });

    //Proses Tambah Jurnal
    $('#ProsesTambahJurnal').submit(function(e) {
        e.preventDefault();
        var TombolTambahJurnal = $('#TombolTambahJurnal').html();
        $('#TombolTambahJurnal').prop('disabled', true);
        $('#TombolTambahJurnal').html(`
            <span class="spinner-border spinner-border-sm me-1"></span>
            Loading...
        `);
        $('#NotifikasiTambahJurnal').html('');

        var form = $('#ProsesTambahJurnal')[0];
        var data = new FormData(form);

        $.ajax({
            type: 'POST',
            url: '_Page/Transaksi/ProsesTambahJurnal.php',
            data: data,
            cache: false,
            processData: false,
            contentType: false,
            dataType: 'JSON',
            success: function(response) {
                var status  = response.status;
                var message = response.message;

                if (status === 'success') {
                    $('#ModalTambahJurnal').modal('hide');
                    $('#ProsesTambahJurnal')[0].reset();

                    if ($('#kode_perkiraan').hasClass('select2-hidden-accessible')) {
                        $('#kode_perkiraan').val(null).trigger('change');
                    }

                    showToast('success', 'Berhasil', message || 'Jurnal berhasil disimpan.');

                    var ProsesDetailTransaksi = $('#ProsesDetailTransaksi').serialize();
                    JurnalTransaksi(ProsesDetailTransaksi);
                } else {
                    $('#NotifikasiTambahJurnal').html(`
                        <div class="alert alert-danger">
                            <small>
                                <b>Opss!</b>
                                ${message}
                            </small>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.log('XHR:', xhr);
                console.log('STATUS:', status);
                console.log('ERROR:', error);
                console.log('RESPONSE:', xhr.responseText);

                $('#NotifikasiTambahJurnal').html(`
                    <div class="alert alert-danger">
                        <small>
                            Terjadi kesalahan server.
                        </small>
                    </div>
                `);
            },
            complete: function() {
                $('#TombolTambahJurnal').prop('disabled', false);
                $('#TombolTambahJurnal').html(TombolTambahJurnal);
            }
        });
    });

    // =====================================================
    // EDIT JURNAL
    // =====================================================
    $('#ModalEditJurnal').on('show.bs.modal', function (e) {
        var id_jurnal = $(e.relatedTarget).data('id');
        $('#TombolEditJurnal').prop('disabled', true);
        $('#NotifikasiEditJurnal').html('');
        $('#FormEditJurnal').html(`
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                <span class="ms-2">Memuat data jurnal...</span>
            </div>
        `);

        $.ajax({
            type: 'POST',
            url: '_Page/Transaksi/FormEditJurnal.php',
            data: { id_jurnal: id_jurnal },
            dataType: 'JSON',
            success: function(response) {
                if (response.status === 'success') {
                    $('#FormEditJurnal').html(response.html);
                    initSelect2EditJurnal();
                    formatNilaiEditJurnal();
                    $('#TombolEditJurnal').prop('disabled', false);
                } else {
                    $('#FormEditJurnal').html(response.html);
                }
            },
            error: function(xhr, status, error) {
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);
                $('#FormEditJurnal').html(`
                    <div class="alert alert-danger">
                        <small>Terjadi kesalahan saat mengambil data jurnal.</small>
                    </div>
                `);
            }
        });
    });

    function initSelect2EditJurnal() {
        var $select = $('#kode_perkiraan_edit');
        if (!$select.length) {
            return;
        }
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        $select.select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#ModalEditJurnal'),
            width: '100%',
            placeholder: 'Pilih Akun Perkiraan',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: '_Page/Transaksi/ListAkunPerkiraan.php',
                type: 'GET',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        search: params.term || '',
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination ? data.pagination.more : false
                        }
                    };
                },
                cache: true
            },
            templateResult: function(data) {
                if (data.loading) {
                    return data.text;
                }
                var $result = $(`
                    <div>
                        <div><strong></strong></div>
                        <small class="text-muted"></small>
                    </div>
                `);
                $result.find('strong').text(data.nama || data.text || '');
                $result.find('small').text(data.kode ? data.kode : '');
                return $result;
            },
            templateSelection: function(data) {
                return data.text || data.nama || '';
            }
        });
    }

    // FORMAT NILAI EDIT JURNAL
    function formatNilaiEditJurnal() {
        $('#nilai_edit').on('input', function() {
            var value = $(this).val();
            value = value.replace(/\D/g, '');
            if (value !== '') {
                value = parseInt(value, 10).toLocaleString('id-ID');
            }
            $(this).val(value);
        });
    }

    // PROSES EDIT JURNAL
    $('#ProsesEditJurnal').on('submit', function(e) {
        e.preventDefault();
        var TombolEditJurnal = $('#TombolEditJurnal').html();
        $('#TombolEditJurnal').prop('disabled', true);
        $('#TombolEditJurnal').html(`
            <span class="spinner-border spinner-border-sm me-1"></span>
            Loading...
        `);
        $('#NotifikasiEditJurnal').html('');

        var form = this;
        var data = new FormData(form);

        $.ajax({
            type: 'POST',
            url: '_Page/Transaksi/ProsesEditJurnal.php',
            data: data,
            cache: false,
            processData: false,
            contentType: false,
            dataType: 'JSON',
            success: function(response) {
                var status = response.status;
                var message = response.message;

                if (status === 'success') {
                    $('#ModalEditJurnal').modal('hide');
                    var ProsesDetailTransaksi = $('#ProsesDetailTransaksi').serialize();
                    JurnalTransaksi(ProsesDetailTransaksi);
                    if (typeof RincianTransaksi === 'function') {
                        RincianTransaksi(ProsesDetailTransaksi);
                    }
                    showToast('success', 'Berhasil', message || 'Data jurnal berhasil diperbarui.');
                } else {
                    $('#NotifikasiEditJurnal').html(`
                        <div class="alert alert-danger">
                            <small><b>Opss!</b> ${message}</small>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);
                $('#NotifikasiEditJurnal').html(`
                    <div class="alert alert-danger">
                        <small>Terjadi kesalahan server.</small>
                    </div>
                `);
            },
            complete: function() {
                $('#TombolEditJurnal').prop('disabled', false);
                $('#TombolEditJurnal').html(TombolEditJurnal);
            }
        });
    });

    // =====================================================
    // HAPUS JURNAL
    // =====================================================

    // Modal Hapus Jurnal
    $('#ModalHapusJurnal').on('show.bs.modal', function(e) {
        var id_jurnal = $(e.relatedTarget).data('id');

        $('#TombolHapusJurnal').prop('disabled', true);
        $('#NotifikasiHapusJurnal').html('');

        $('#FormHapusJurnal').html(`
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                <span class="ms-2">Memuat data jurnal...</span>
            </div>
        `);

        $.ajax({
            type: 'POST',
            url: '_Page/Transaksi/FormHapusJurnal.php',
            data: {
                id_jurnal: id_jurnal
            },
            dataType: 'JSON',
            success: function(response) {
                if (response.status === 'success') {
                    $('#FormHapusJurnal').html(response.html);
                    $('#TombolHapusJurnal').prop('disabled', false);
                } else {
                    $('#FormHapusJurnal').html(response.html);
                }
            },
            error: function(xhr, status, error) {
                console.log('XHR:', xhr);
                console.log('STATUS:', status);
                console.log('ERROR:', error);
                console.log('RESPONSE:', xhr.responseText);

                $('#FormHapusJurnal').html(`
                    <div class="alert alert-danger">
                        <small>Terjadi kesalahan saat mengambil data jurnal.</small>
                    </div>
                `);
            }
        });
    });

    // Proses Hapus Jurnal
    $('#ProsesHapusJurnal').on('submit', function(e) {
        e.preventDefault();

        var TombolHapusJurnal = $('#TombolHapusJurnal').html();

        $('#TombolHapusJurnal').prop('disabled', true);
        $('#TombolHapusJurnal').html(`
            <span class="spinner-border spinner-border-sm me-1"></span>
            Loading...
        `);

        $('#NotifikasiHapusJurnal').html('');

        var form = this;
        var data = new FormData(form);

        $.ajax({
            type: 'POST',
            url: '_Page/Transaksi/ProsesHapusJurnal.php',
            data: data,
            cache: false,
            processData: false,
            contentType: false,
            dataType: 'JSON',
            success: function(response) {
                var status = response.status;
                var message = response.message;

                if (status === 'success') {
                    $('#ModalHapusJurnal').modal('hide');

                    var ProsesDetailTransaksi = $('#ProsesDetailTransaksi').serialize();

                    JurnalTransaksi(ProsesDetailTransaksi);

                    if (typeof RincianTransaksi === 'function') {
                        RincianTransaksi(ProsesDetailTransaksi);
                    }

                    showToast(
                        'success',
                        'Berhasil',
                        message || 'Data jurnal berhasil dihapus.'
                    );
                } else {
                    $('#NotifikasiHapusJurnal').html(`
                        <div class="alert alert-danger">
                            <small>
                                <b>Opss!</b> ${message}
                            </small>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.log('XHR:', xhr);
                console.log('STATUS:', status);
                console.log('ERROR:', error);
                console.log('RESPONSE:', xhr.responseText);

                $('#NotifikasiHapusJurnal').html(`
                    <div class="alert alert-danger">
                        <small>Terjadi kesalahan server.</small>
                    </div>
                `);
            },
            complete: function() {
                $('#TombolHapusJurnal').prop('disabled', false);
                $('#TombolHapusJurnal').html(TombolHapusJurnal);
            }
        });
    });

    // Reset Modal Hapus Jurnal
    $('#ModalHapusJurnal').on('hidden.bs.modal', function() {
        $('#FormHapusJurnal').html('');
        $('#NotifikasiHapusJurnal').html('');
        $('#TombolHapusJurnal').prop('disabled', true);
    });

    //-------------------------------------------------
    // EXPORT TRANSAKSI
    //-------------------------------------------------

    $('#periode_data').on('change', function() {

        var periode_data = $(this).val();

        $('.form_periode_data').html('');

        if (periode_data === 'Tahunan') {

            $('.form_periode_data').html(`
                <div class="row mb-3">
                    <div class="col col-md-4">
                        <label for="tahun">Tahun</label>
                    </div>
                    <div class="col-md-8">
                        <select name="tahun" id="tahun" class="form-control" required>
                            <option value="">Memuat tahun...</option>
                        </select>
                    </div>
                </div>
            `);

            ListTahunTransaksi();

        } else if (periode_data === 'Bulanan') {

            $('.form_periode_data').html(`

                <div class="row mb-3">
                    <div class="col col-md-4">
                        <label for="tahun">Tahun</label>
                    </div>
                    <div class="col-md-8">
                        <select name="tahun" id="tahun" class="form-control" required>
                            <option value="">Memuat tahun...</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col col-md-4">
                        <label for="bulan">Bulan</label>
                    </div>
                    <div class="col-md-8">
                        <select name="bulan" id="bulan" class="form-control" required>
                            <option value="">Pilih Bulan</option>
                            <option value="01">Januari</option>
                            <option value="02">Februari</option>
                            <option value="03">Maret</option>
                            <option value="04">April</option>
                            <option value="05">Mei</option>
                            <option value="06">Juni</option>
                            <option value="07">Juli</option>
                            <option value="08">Agustus</option>
                            <option value="09">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                    </div>
                </div>

                
            `);

            ListTahunTransaksi();
        }
    });


    //-------------------------------------------------
    // LOAD DAFTAR TAHUN TRANSAKSI
    //-------------------------------------------------

    function ListTahunTransaksi() {

        $('#tahun').html(`
            <option value="">Memuat tahun...</option>
        `);

        $.ajax({
            type: 'GET',
            url: '_Page/Transaksi/ListTahunTransaksi.php',
            dataType: 'JSON',
            cache: true,
            success: function(response) {

                if (response.status === 'success') {

                    var html = '<option value="">Pilih Tahun</option>';

                    $.each(response.data, function(index, tahun) {
                        html += `
                            <option value="${tahun}">${tahun}</option>
                        `;
                    });

                    $('#tahun').html(html);

                } else {

                    $('#tahun').html(`
                        <option value="">${response.message}</option>
                    `);

                }
            },

            error: function(xhr, status, error) {

                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                $('#tahun').html(`
                    <option value="">Gagal memuat tahun</option>
                `);
            }
        });
    }


    //-------------------------------------------------
    // RESET MODAL EXPORT
    //-------------------------------------------------

    $('#ModalExport').on('hidden.bs.modal', function() {

        $(this).find('form')[0].reset();

        $('.form_periode_data').html('');
    });
    
});


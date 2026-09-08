// ===============================================
// FUNCTION
// ===============================================

function ShowData() {
    const ProsesFilter = $('#ProsesFilter').serialize();

    $.ajax({
        type: 'POST',
        url: '_Page/Medication/TabelMedication.php',
        data: ProsesFilter,

        beforeSend: function() {
            tableLoading('#tabel_medication', true);
        },

        success: function(data) {
            $('#TabelMedication').html(data);
            initResponsiveTable('#tabel_medication');
        },

        error: function() {
            $('#TabelMedication').html(`
                <tr class="table-empty">
                    <td colspan="10" class="text-center text-danger">
                        Gagal memuat data pasien
                    </td>
                </tr>
            `);
        },

        complete: function() {
            tableLoading('#tabel_medication', false);
        }
    });
}

//Fungsi Menampilkan Data KFA
function ShowDataKfa() {
    const ProsesCariKfa = $('#ProsesCariKfa').serialize();

    $.ajax({
        type: 'POST',
       url    : '_Page/Medication/TabelKfa.php',
        data: ProsesCariKfa,

        beforeSend: function() {
            tableLoading('#TableKfa', true);
        },

        success: function(data) {
            $('#tabel_kfa').html(data);
            initResponsiveTable('#TableKfa');
        },

        error: function() {
            $('#tabel_kfa').html(`
                <tr class="table-empty">
                    <td colspan="10" class="text-center text-danger">
                        Gagal memuat data pasien
                    </td>
                </tr>
            `);
        },

        complete: function() {
            tableLoading('#TableKfa', false);
        }
    });
}

// Select2 Sediaan
function initSelect2Sediaan(FormElemtn,ParentForm) {
    $(FormElemtn).select2({
        theme             : 'bootstrap-5',
        dropdownParent    : $(ParentForm),
        placeholder       : 'Cari sediaan...',
        allowClear        : true,
        minimumInputLength: 0,
        ajax: {
            url     : '_Page/Medication/ListSediaan.php',
            dataType: 'json',
            delay   : 300,
            data    : function (params) {
                return {
                    keyword            : params.term,
                    medication_category: $('#medication_category_manual').val()
                };
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            },
            cache: true
        }
    });
}

// KFA Manual
function initSelect2Kfa(FormElement,ParentElement,medication_category) {
    $(FormElement).select2({
        theme              : 'bootstrap-5',
        dropdownParent     : $(ParentElement),
        placeholder        : 'Cari KFA',
        allowClear         : true,
        minimumInputLength : 0,
        
        // Render Tampilan Custom
        templateResult     : formatKfaResult,
        templateSelection  : formatKfaSelection,
        escapeMarkup       : function (markup) { return markup; },

        ajax: {
            url        : '_Page/Medication/ListKfa.php',
            dataType   : 'json',
            delay      : 300,
            data       : function (params) {
                return {
                    keyword: params.term,
                    medication_category: medication_category,
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
        }
    });
}

// Format Tampilan Saat Dropdown Terbuka (Dua Baris)
function formatKfaResult(item) {
    if (item.loading) {
        return item.text;
    }

    if (!item.kfa_code) {
        return item.text;
    }

    return `
        <div style="padding: 4px 0;">
            <div style="font-weight: bold; font-size: 13px; color: #111;">
                ${item.kfa_code}
            </div>
            <div style="font-size: 11px; color: #555; line-height: 1.2; margin-top: 2px;">
                ${item.name}
            </div>
        </div>
    `;
}

// Format Tampilan Saat Sudah Dipilih (Biasanya tetap ringkas di kotak input)
function formatKfaSelection(item) {
    if (!item.kfa_code && !item.name) {
        return item.text;
    }
    // Tampilan ringkas saat terpilih
    return item.kfa_code ? `${item.kfa_code} - ${item.name}` : item.text;
}

// Select Manufacture Manual
function initSelect2Manufaktur() {
    $('#manufaktur_manual').select2({
        theme             : 'bootstrap-5',
        dropdownParent    : $('#ModalTambahManual'),
        placeholder       : 'Cari Manufacturer...',
        allowClear        : true,
        minimumInputLength: 3,
        ajax: {
            url     : '_Page/Medication/ListManufacturer.php',
            dataType: 'json',
            delay   : 300,
            data    : function (params) {
                return {
                    keyword : params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            },
            cache: true
        }
    });
}


// Numerator
function initSelect2SatuanNumerator(FormElement,ParentElement) {
    $(FormElement).select2({
        theme             : 'bootstrap-5',
        dropdownParent    : $(ParentElement),
        placeholder       : 'Satuan Numerator...',
        allowClear        : true,
        minimumInputLength: 0,
        ajax              : {
            url     : '_Page/Medication/ListNumerator.php',
            dataType: 'json',
            delay   : 300,
            data    : function (params) {
                return {
                    keyword: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            },
            cache: true
        }
    });
}

// Denumerator
function initSelect2SatuanDenominator(FormElement,ParentElement) {
    $(FormElement).select2({
        theme             : 'bootstrap-5',
        dropdownParent    : $(ParentElement),
        placeholder       : 'Satuan Denominator...',
        allowClear        : true,
        minimumInputLength: 0,
        ajax              : {
            url     : '_Page/Medication/ListDenominator.php',
            dataType: 'json',
            delay   : 300,
            data    : function (params) {
                return {
                    keyword: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            },
            cache: true
        }
    });
}

// Fungsi untuk menyalin error ke clipboard
function copyErrorToClipboard() {
    var errorText = $('#NotifikasiTambahManual').text();
    
    // Buat textarea sementara
    var tempTextArea = document.createElement("textarea");
    tempTextArea.value = errorText;
    document.body.appendChild(tempTextArea);
    tempTextArea.select();
    tempTextArea.setSelectionRange(0, 99999); // Untuk mobile
    
    // Salin teks
    try {
        document.execCommand("copy");
        alert("Error berhasil disalin ke clipboard!");
    } catch (err) {
        console.error("Gagal menyalin: ", err);
        alert("Gagal menyalin error ke clipboard");
    }
    
    // Hapus textarea
    document.body.removeChild(tempTextArea);
}

// Fungsi untuk escape HTML, agar tidak broken ketika menampilkan string JSON
function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

function kontrolTombolIngredientEdit(kategori, racikan) {

    const tombol = $('#modal_tambah_ingridient_edit');

    // Hanya Obat racikan SD / EP yang boleh tambah ingredient
    if (
        kategori === 'Obat' &&
        (racikan === 'SD' || racikan === 'EP')
    ) {
        tombol
            .prop('disabled', false)
            .removeClass('btn-secondary')
            .addClass('btn-primary');

    } else {
        tombol
            .prop('disabled', true)
            .removeClass('btn-primary')
            .addClass('btn-secondary');
    }
}
// ===============================================
// EVENT LISTENER
// ===============================================

//Menampilkan Data Pertama Kali
$(document).ready(function() {

    //-----------------------------------------------
    //Menampilkan Data Pertama Kali
    initResponsiveTable('#TabelMedication');
    ShowData();
    ShowDataKfa();

    //Ketika keyword_by diubah
    $('#KeywordBy').change(function(){
        var keyword_by =$('#KeywordBy').val();
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Medication/FormFilter.php',
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
        scrollToTop();
    });
    $(document).on('click', '#prev_button', function() {
        var page_now = parseInt($('#page').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page = page_now - 1;
        $('#page').val(next_page);
        ShowData(0);
        scrollToTop();
    });

    // Ketika Pencarian KFA
    $('#ProsesCariKfa').submit(function(){
        $('#page_kfa').val("1");
        ShowDataKfa();
    });

    //Pagging KFA
    $(document).on('click', '#next_button_kfa', function() {
        var page_now = parseInt($('#page_kfa').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page = page_now + 1;
        $('#page_kfa').val(next_page);
        ShowDataKfa(0);
        scrollToTop();
    });
    $(document).on('click', '#prev_button_kfa', function() {
        var page_now = parseInt($('#page_kfa').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page = page_now - 1;
        $('#page_kfa').val(next_page);
        ShowDataKfa(0);
        scrollToTop();
    });

    // Ketika Generate Kode Lokal
    $(document).on('click', '.generate_kode_lokal', function () {
        const length = 12;
        const chars = '0123456789';
        let result = '';

        for (let i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }

        $('#medication_code_manual').val(result);
        $('#medication_code').val(result);
    });

    //-----------------------------------------------
    // EXPORT
    
    // Ketika Modal Export Ditampilkan
    $('#ModalExport').on('shown.bs.modal', function () {

        // Reset
        $('#NotifikasiExport').html('');
        $('#FormExport').html('Loading...');
        $('#TombolExport').prop('disabled', true);

        // Ambil Form Export
        $.ajax({
            type    : 'POST',
            url     : '_Page/Medication/FormExport.php',
            dataType: 'JSON',

            success: function (response) {

                if (response.status === 'success') {

                    $('#FormExport').html(response.html);

                    // Enable tombol jika data tersedia
                    $('#TombolExport').prop(
                        'disabled',
                        parseInt(response.jumlah_data || 0) < 1
                    );

                } else {

                    $('#FormExport').html(`
                        <div class="alert alert-danger text-center mb-0">
                            <small>${response.message}</small>
                        </div>
                    `);

                    $('#TombolExport').prop('disabled', true);
                }
            },

            error: function (xhr, status, error) {

                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                $('#FormExport').html(`
                    <div class="alert alert-danger text-center mb-0">
                        <small>Terjadi kesalahan saat menyiapkan export.</small>
                    </div>
                `);

                $('#TombolExport').prop('disabled', true);
            }
        });
    });

    // Proses Export Medication
    $('#ProsesExport').submit(function (e) {

        e.preventDefault();

        // Ambil Data
        const type_file = $('#type_file_export').val();

        if (!type_file) {

            $('#NotifikasiExport').html(`
                <div class="alert alert-danger">
                    <small>Pilih format file terlebih dahulu.</small>
                </div>
            `);

            return;
        }

        // Simpan Tombol
        const TombolExport = $('#TombolExport').html();

        // Loading
        $('#TombolExport')
            .prop('disabled', true)
            .html(`
                <span class="spinner-border spinner-border-sm"></span>
                Export...
            `);

        $('#NotifikasiExport').html('');

        // Buat Form Temporary
        const form = $('<form>', {
            method: 'POST',
            action: '_Page/Medication/ProsesExport.php',
            target: '_blank'
        });

        form.append(
            $('<input>', {
                type : 'hidden',
                name : 'type_file',
                value: type_file
            })
        );

        $('body').append(form);

        // Submit
        form.trigger('submit');

        // Hapus Temporary Form
        form.remove();

        // Kembalikan Tombol
        setTimeout(function () {

            $('#TombolExport')
                .prop('disabled', false)
                .html(TombolExport);

        }, 1000);
    });

    //-----------------------------------------------
    // PROSES IMPORT

    // Reset Ketika Modal Dibuka
    $('#ModalImport').on('shown.bs.modal', function () {
        $('#ProsesImport')[0].reset();
        $('#NotifikasiImport').html('');
        $('#TombolImport').prop('disabled', false);
    });

    // Proses Import
    $('#ProsesImport').submit(function (e) {

        e.preventDefault();

        // File
        const fileInput = $('#file_medication_excel')[0];

        if (!fileInput.files.length) {
            $('#NotifikasiImport').html(`
                <div class="alert alert-danger mt-3">
                    <small>File medication tidak boleh kosong.</small>
                </div>
            `);
            return;
        }

        // FormData
        const formData = new FormData(this);

        // Simpan Tombol
        const TombolImport = $('#TombolImport').html();

        // Loading
        $('#TombolImport')
            .prop('disabled', true)
            .html(`
                <span class="spinner-border spinner-border-sm"></span>
                Import...
            `);

        $('#NotifikasiImport').html(`
            <div class="alert alert-info mt-3">
                <small>
                    <span class="spinner-border spinner-border-sm"></span>
                    Sedang memproses data medication...
                </small>
            </div>
        `);

        // AJAX
        $.ajax({
            type       : 'POST',
            url        : '_Page/Medication/ProsesImport.php',
            data       : formData,
            dataType   : 'JSON',
            contentType: false,
            processData: false,
            cache      : false,

            success: function (response) {

                if (response.status === 'success') {

                    $('#NotifikasiImport').html(response.html);

                    // Reload Tabel Medication
                    ShowData();

                    showToast(
                        'success',
                        'Import Selesai',
                        response.message
                    );

                } else {

                    $('#NotifikasiImport').html(`
                        <div class="alert alert-danger mt-3">
                            <small>${response.message}</small>
                        </div>

                        ${response.html || ''}
                    `);
                }
            },

            error: function (xhr, status, error) {

                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                $('#NotifikasiImport').html(`
                    <div class="alert alert-danger mt-3">
                        <small>Terjadi kesalahan server pada proses import.</small>
                    </div>
                `);
            },

            complete: function () {

                $('#TombolImport')
                    .prop('disabled', false)
                    .html(TombolImport);
            }
        });
    });

    //-----------------------------------------------
    // TAMBAH MEDICATION KFA

    // Modal Tambah Medication KFA
    $(document).on('click', '.modal_tambah_medication_kfa', function () {

        //tangkap data 'kfa_code' dan buat variabel
        var kfa_code   = $(this).data('id');

        //tampilkan modal
        $('#ModalTambahMedicationKfa').modal('show');

        //Form Loading
        $('#FormTambahMedicationKfa').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Medication/FormTambahMedicationKfa.php',
            data        : {kfa_code: kfa_code},
            success     : function(data){
                $('#FormTambahMedicationKfa').html(data);

                // 🔁 Re-inisialisasi tooltip setelah data dimuat
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });
    });

    //Proses Tambah Medication KFA
    $('#ProsesTambahMedicationKfa').submit(function(e){
        e.preventDefault();
        
        // Ambil Data Dari form
        var ProsesTambahMedicationKfa = $(this).serialize();

        //Loading Notifikasi
        $('#NotifikasiEditTagihan').html('<small class="text-muted">Menyimpan data...</small>');

        // Ajax Request
        $.ajax({
            type     : 'POST',
            url      : '_Page/Medication/ProsesTambahMedicationKfa.php',
            dataType : 'json',
            data     : ProsesTambahMedicationKfa,

            success: function(response){

                var payload  = response.payload;
                var status  = response.status;
                var message = response.message || 'Proses berhasil';

                if(status === 'success'){

                    // Bersihkan notifikasi
                    $('#NotifikasiTambahMedicationKfa').html('');

                    // Tutup modal jika ada
                    $('#ModalTambahMedicationKfa').modal('hide');
                    $('#ModalCariKfa').modal('hide');

                    // Reload detail pemeriksaan
                    ShowData();

                    // Toast Proses Berhasil
                    $('#put_message').html('<i class="bi bi-check-circle me-2"></i> ' + message);

                    // Tampilkan Toast
                    var toastEl = document.getElementById('toast_proses');
                    var toast   = new bootstrap.Toast(toastEl, {delay: 3000});
                    toast.show();

                } else {
                    // Tampilkan Pesan Kesalahan
                    $('#NotifikasiTambahMedicationKfa').html(
                        '<div class="alert alert-danger"><small>'+message+'</small></div><div class="alert alert-danger"><code>'+payload+'</code></div>'
                    );
                }
            },

            error: function(xhr){
                console.log(xhr.responseText);

                $('#NotifikasiTambahMedicationKfa').html(
                    '<div class="alert alert-danger"><small>Terjadi kesalahan sistem</small></div>'
                );
            }
        });
    });

    //-----------------------------------------------
    // TAMBAH MANUAL

    // Modal Tambah Medication Manual
    $(document).on('click', '.modal_tambah_manual', function () {

        //tampilkan modal
        $('#ModalTambahManual').modal('show');

        // Kosongkan Notifikasi
        $('#NotifikasiTambahManual').html('');

        //Form Loading
        $('#FormTambahManual').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Medication/FormTambahManual.php',
            success     : function(data){
                $('#FormTambahManual').html(data);

                // Select2 Untuk 'sediaan_manual'
                initSelect2Sediaan('#sediaan_manual','#FormTambahManual');
                initSelect2Manufaktur();

            }
        });
    });

    // Ketika medication_category_manual diubah
    $(document).on('change', '#medication_category_manual', function() {
        var medication_category= $(this).val();
        initSelect2Kfa('#kfa_manual','#FormTambahManual',medication_category);
        
        if(medication_category=="Obat"){
            $('#medication_code_manual').removeAttr('disabled');
            $('#generate_kode_lokal').removeAttr('disabled');
            $('#insert_medication').removeAttr('disabled');
            $('#id_medication_manual').removeAttr('disabled');
            $('#medication_name_manual').removeAttr('disabled');
            $('#racikan_code').removeAttr('disabled');
            $('#kfa_manual').removeAttr('disabled');
            $('#sediaan_manual').removeAttr('disabled');
            $('#manufaktur_manual').removeAttr('disabled');
            $('#modal_tambah_ingridient').removeAttr('disabled');
        }
        if(medication_category=="Alkes"){
            $('#medication_code_manual').removeAttr('disabled');
            $('#generate_kode_lokal').removeAttr('disabled');
            $('#insert_medication').removeAttr('disabled');
            $('#id_medication_manual').removeAttr('disabled');
            $('#medication_name_manual').removeAttr('disabled');
            $('#racikan_code').attr('disabled', 'disabled');
            $('#racikan_code').val('');
            $('#kfa_manual').removeAttr('disabled');
            $('#sediaan_manual').removeAttr('disabled');
            $('#manufaktur_manual').removeAttr('disabled');
            $('#modal_tambah_ingridient').attr('disabled', 'disabled');

            $('#table_list_ingridient').html('<tr><td colspan="6" class="text-center"><small>Konten Belum Ada</small></td></tr>');
        }
        if (medication_category == "") {

            $('#medication_code_manual').val('').prop('disabled', true);
            $('#generate_kode_lokal').prop('disabled', true);
            $('#insert_medication').prop('disabled', true);
            $('#id_medication_manual').val('').prop('disabled', true);
            $('#medication_name_manual').val('').prop('disabled', true);

            $('#racikan_code').val('').prop('disabled', true);
            $('#kfa_manual').val(null).trigger('change').prop('disabled', true);

            $('#sediaan_manual').val(null).trigger('change').prop('disabled', true);
            $('#manufaktur_manual').val(null).trigger('change').prop('disabled', true);

            $('#modal_tambah_ingridient').prop('disabled', true);
            $('#table_list_ingridient').html('<tr><td colspan="6" class="text-center"><small>Konten Belum Ada</small></td></tr>');
        }
    });

    // Modal Tambah Ingridient
    $(document).on('click', '#modal_tambah_ingridient', function () {

        // Menangkap 'medication_category'
        var medication_category = $('#medication_category_manual').val();

        //tampilkan modal
        $('#ModalTambahIngridient').modal('show');

        // Kosongkan Notifikasi
        $('#NotifikasiTambahIngridient').html('');

        //Form Loading
        $('#FormTambahIngridient').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Medication/FormTambahIngridient.php',
            success     : function(data){
                $('#FormTambahIngridient').html(data);

                // 🔁 Re-inisialisasi tooltip setelah data dimuat
                $('[data-bs-toggle="tooltip"]').tooltip();

                // Inisialisasi Select2
                initSelect2Kfa('#ingridient_kfa','#FormTambahIngridient',medication_category);
                initSelect2SatuanNumerator('#satuan_numerator','#FormTambahIngridient');
                initSelect2SatuanDenominator('#satuan_denominator','#FormTambahIngridient');

            }
        });
    });

    // Proses Tambah Ingridient
    $('#ProsesTambahIngridient').submit(function(e){
        e.preventDefault();
        
        // Ambil Data Dari form
        var ProsesTambahIngridient = $(this).serialize();

        // Ajax Request
        $.ajax({
            type     : 'POST',
            url      : '_Page/Medication/ProsesTambahIngridient.php',
            dataType : 'json',
            data     : ProsesTambahIngridient,

            success: function(response){
                var status  = response.status;
                var payload = response.payload;
                var message = response.message || 'Proses berhasil';

                if(status === 'success'){

                    // Tutup Modal
                    $('#ModalTambahIngridient').modal('hide');

                    // Hitung nomor baris (berdasarkan jumlah card yang sudah ada + 1)
                    var no = $('#list_ingridient .card').length + 1;

                    // Format numerator
                    var numerator = '';
                    if(payload.jumlah_numerator !== ''){
                        numerator = payload.jumlah_numerator + ' ' + payload.nama_numerator;
                    }

                    // Format denominator
                    var denominator = '';
                    if(payload.jumlah_denominator !== ''){
                        denominator = payload.jumlah_denominator + ' ' + payload.nama_denominator;
                    }

                    // Buat row card yang rapi, border secondary, tanpa shadow, tombol hapus di pojok kanan atas
                    var content_row = `
                        <div class="card border border-secondary border-opacity-50 rounded-3 mt-3 position-relative shadow-none">
                            <div class="card-body p-3 pe-5">
                                
                                <!-- Tombol Hapus di Pojok Kanan Atas -->
                                <a href="javascript:void(0);" class="text-danger position-absolute top-0 end-0 m-2 p-1 lh-1 btn-hapus-ingridient" title="Hapus Ingridient" style="width: 28px; height: 28px;">
                                    <i class="bi bi-x-lg" style="font-size: 12px;"></i>
                                </a>

                                <!-- Konten Data -->
                                <div class="fw-bold text-dark mb-1">
                                    <span class="badge bg-secondary me-1">${no}</span> 
                                    ${payload.kode_kfa}
                                </div>
                                <br><i>${payload.nama_kfa}</i>
                                <div class="small text-muted d-flex gap-3 mt-2">
                                    <div><span class="fw-semibold">Numerator:</span> ${numerator || '-'}</div>
                                    <div><span class="fw-semibold">Denominator:</span> ${denominator || '-'}</div>
                                </div>

                                <input type="hidden" name="payload_ingridient[]" value='${JSON.stringify(payload)}'>
                            </div>
                        </div>
                    `;

                    // Append ke penampung list
                    $('#list_ingridient').append(content_row);

                    // Reset form
                    $('#ProsesTambahIngridient')[0].reset();

                    // Reset Select2
                    $('#ingridient_kfa, #satuan_numerator, #satuan_denominator').val(null).trigger('change');

                } else {
                    $('#NotifikasiTambahIngridient').html(
                        '<div class="alert alert-danger py-2 mb-2"><small>'+message+'</small></div>'
                    );
                }
            },

            error: function(xhr){
                console.log(xhr.responseText);
                $('#NotifikasiTambahIngridient').html(
                    '<div class="alert alert-danger py-2 mb-2"><small>Terjadi kesalahan sistem</small></div>'
                );
            }
        });
    });

    // Hapus Ingrident List
    $(document).on('click', '.btn-hapus-ingridient', function () {
        // 1. Hapus card (item) yang bersangkutan saja
        $(this).closest('.card').remove();

        // 2. Perbarui nomor urut (badge) secara otomatis agar tetap berurutan
        $('#list_ingridient .card').each(function (index) {
            $(this).find('.badge').text(index + 1);
        });
    });

    

    //Proses Tambah Medication Manual
    $('#ProsesTambahManual').submit(function(e){
        e.preventDefault();
        
        // Ambil Data Dari form
        var ProsesTambahManual = $(this).serialize();

        //Loading Tombol
        $('#TombolTambahManual').html('<small class="text-muted">Menyimpan data...</small>');

        // Ajax Request
        $.ajax({
            type     : 'POST',
            url      : '_Page/Medication/ProsesTambahManual.php',
            dataType : 'JSON',
            data     : ProsesTambahManual,

            success: function(response){
                var status  = response.status;
                var message = response.message;

                // Jika Berhasil
                if(status === 'success'){

                    // Bersihkan Notifikasi
                    $('#NotifikasiTambahManual').html('');

                    // Tutup Modal
                    $('#ModalTambahManual').modal('hide');

                    // Kembalikan Tombol
                    $('#TombolTambahManual').html('<i class="bi bi-save"></i> Simpan');

                    // Reload Data
                    ShowData();

                    // Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil dihapus.'
                    );
                    
                } else {
                    $('#NotifikasiTambahManual').html(message);
                }
            },

            error: function(xhr, status, error){
                $('#NotifikasiTambahManual').html(`
                    <div class="alert alert-danger text-center">
                        <small><b>Opss!</b> <br>Terjasi Kesalahan Pada Sistem</small>
                    </div>
                `);
            }
        });
    });

    

    // Modal Detail Medication KFA
    $(document).on('click', '.modal_detail', function () {

        //tangkap data 'kfa_code' dan buat variabel
        var id   = $(this).data('id');

        //tampilkan modal
        $('#ModalDetail').modal('show');

        //Form Loading
        $('#FormDetail').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Medication/FormDetail.php',
            data        : {id: id},
            success     : function(data){
                $('#FormDetail').html(data);

                // 🔁 Re-inisialisasi tooltip setelah data dimuat
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });
    });

    // Detail Medicationn
    $(document).on('click', '.modal_detail_medication', function () {

        // Tangkap 'id_medication_request_group'
        var id_medication= $(this).data('id');

        // Buka Modal
        $('#ModalDetailMedication').modal("show");

        // Loading Form
        $('#FormDetailMedication').html("Loading...");

        // Ambil Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Medication/FormDetailMedication.php',
            data        : {id_medication: id_medication},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;

                // Apabila status success
                if(status=='success'){

                    // Tangkap HTML dan Tempelkan Ke Form
                    var html = response.html;
                    $('#FormDetailMedication').html(html);

                }else{
                    $('#FormDetailMedication').html(`
                        <div class="alert alert-danger text-center">
                            <small>
                                <b>Ops!!</b><br>
                                Terjadi Kesalahan : ${message}
                            </small>
                        </div>
                    `);

                }
            },
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormDetailMedication').html(`
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Ops!!</b><br>
                            Terjadi Kesalahan Pada Sistem.
                        </small>
                    </div>
                `);
            },

        });
    });

    //-----------------------------------------------
    // EDIT MEDICATION
    //-----------------------------------------------

    // Ketika Modal 'ModalEdit' Ditampilkan
    $('#ModalEdit').on('shown.bs.modal', function (e) {

        // Menangkap 'id_index_medication'
        var id_index_medication = $(e.relatedTarget).data('id');

        // Kosongkan Notifikasi dan Form
        $('#FormEdit').html('Loading...');
        $('#NotifikasiEdit').html('');

        // Disable Tombol
        $('#TombolEdit').prop('disabled', true);

        // Ambil Form dengan AJAX
        $.ajax({
            type    : 'POST',
            url     : '_Page/Medication/FormEdit.php',
            data    : {id_index_medication: id_index_medication},
            dataType: 'JSON',
            success: function (response) {

                if (response.status === 'success') {

                    $('#FormEdit').html(response.html);

                    // Enable tombol
                    $('#TombolEdit').prop('disabled',false);

                     // Kontrol tombol ingredient berdasarkan data dari database
                    kontrolTombolIngredientEdit(
                        $('#medication_category_edit').val(),
                        $('#racikan_code_edit').val()
                    );

                } else {

                    $('#FormEdit').html(`
                        <div class="alert alert-danger text-center mb-0">
                            <small>${response.message}</small>
                        </div>
                    `);

                    $('#TombolEdit').prop('disabled', true);
                }
            },

            error: function (xhr, status, error) {

                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                $('#FormEdit').html(`
                    <div class="alert alert-danger text-center mb-0">
                        <small>Terjadi kesalahan saat menyiapkan data medication.</small>
                    </div>
                `);

                $('#TombolEdit').prop('disabled', true);
            }
        });
    });

    // Ketika kategori medication edit berubah
    $(document).on('change', '#medication_category_edit', function () {

        const kategori = $(this).val();
        const racikan  = $('#racikan_code_edit').val();

        kontrolTombolIngredientEdit(kategori, racikan);
    });

    // Ketika racikan edit berubah
    $(document).on('change', '#racikan_code_edit', function () {

        const kategori = $('#medication_category_edit').val();
        const racikan  = $(this).val();

        kontrolTombolIngredientEdit(kategori, racikan);
    });

    // Modal Tambah Ingredient (Edit)
    $(document).on('click', '#modal_tambah_ingridient_edit', function () {

        // Ambil kategori dari FormEdit.php yang sudah dimuat
        const medication_category_edit = $('#medication_category_edit').val();

        // Validasi
        if (!medication_category_edit) {
            $('#NotifikasiEdit').html(`
                <div class="alert alert-danger">
                    <small>Kategori medication belum dipilih.</small>
                </div>
            `);
            return;
        }

        // Tampilkan modal
        $('#ModalTambahIngridientEdit').modal('show');

        // Reset
        $('#NotifikasiTambahIngridientEdit').html('');
        $('#FormTambahIngridientEdit').html('Loading...');

        // Ambil Form Ingredient
        $.ajax({
            type: 'POST',
            url: '_Page/Medication/FormTambahIngridientEdit.php',

            success: function (data) {

                $('#FormTambahIngridientEdit').html(data);

                // Tooltip
                $('[data-bs-toggle="tooltip"]').tooltip();

                // Select2
                initSelect2Kfa(
                    '#ingridient_kfa_edit',
                    '#FormTambahIngridientEdit',
                    medication_category_edit
                );

                initSelect2SatuanNumerator(
                    '#satuan_numerator_edit',
                    '#FormTambahIngridientEdit'
                );

                initSelect2SatuanDenominator(
                    '#satuan_denominator_edit',
                    '#FormTambahIngridientEdit'
                );
            },

            error: function (xhr, status, error) {

                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                $('#FormTambahIngridientEdit').html(`
                    <div class="alert alert-danger text-center">
                        <small>Terjadi kesalahan saat menyiapkan form ingredient.</small>
                    </div>
                `);
            }
        });
    });

    // Proses Tambah Ingridient (edit)
    $('#ProsesTambahIngridientEdit').submit(function(e){
        e.preventDefault();
        
        // Ambil Data Dari form
        var ProsesTambahIngridientEdit = $(this).serialize();

        // Ajax Request
        $.ajax({
            type     : 'POST',
            url      : '_Page/Medication/ProsesTambahIngridient.php',
            dataType : 'json',
            data     : ProsesTambahIngridientEdit,

            success: function(response){
                var status  = response.status;
                var payload = response.payload;
                var message = response.message || 'Proses berhasil';

                if(status === 'success'){

                    // Tutup Modal
                    $('#ModalTambahIngridientEdit').modal('hide');

                    // Hitung nomor baris (berdasarkan jumlah card yang sudah ada + 1)
                    var no = $('#list_ingridient_edit .card').length + 1;

                    // Format numerator
                    var numerator = '';
                    if(payload.jumlah_numerator !== ''){
                        numerator = payload.jumlah_numerator + ' ' + payload.nama_numerator;
                    }

                    // Format denominator
                    var denominator = '';
                    if(payload.jumlah_denominator !== ''){
                        denominator = payload.jumlah_denominator + ' ' + payload.nama_denominator;
                    }

                    // Buat row card yang rapi, border secondary, tanpa shadow, tombol hapus di pojok kanan atas
                    var content_row = `
                        <div class="card border border-secondary border-opacity-50 rounded-3 mt-3 position-relative shadow-none">
                            <div class="card-body p-3 pe-5">
                                
                                <!-- Tombol Hapus di Pojok Kanan Atas -->
                                <a href="javascript:void(0);" class="text-danger position-absolute top-0 end-0 m-2 p-1 lh-1 btn-hapus-ingridient" title="Hapus Ingridient" style="width: 28px; height: 28px;">
                                    <i class="bi bi-x-lg" style="font-size: 12px;"></i>
                                </a>

                                <!-- Konten Data -->
                                <div class="fw-bold text-dark mb-1">
                                    <span class="badge bg-secondary me-1">${no}</span> 
                                    ${payload.kode_kfa}
                                </div>
                                <br><i>${payload.nama_kfa}</i>
                                <div class="small text-muted d-flex gap-3 mt-2">
                                    <div><span class="fw-semibold">Numerator:</span> ${numerator || '-'}</div>
                                    <div><span class="fw-semibold">Denominator:</span> ${denominator || '-'}</div>
                                </div>

                                <input type="hidden" name="payload_ingridient_edit[]" value='${JSON.stringify(payload)}'>
                            </div>
                        </div>
                    `;

                    // Append ke penampung list
                    $('#list_ingridient_edit').append(content_row);

                    // Reset form
                    $('#ProsesTambahIngridientEdit')[0].reset();

                    // Reset Select2
                    $('#ingridient_kfa, #satuan_numerator, #satuan_denominator').val(null).trigger('change');

                } else {
                    $('#NotifikasiTambahIngridientEdit').html(
                        '<div class="alert alert-danger py-2 mb-2"><small>'+message+'</small></div>'
                    );
                }
            },

            error: function(xhr){
                console.log(xhr.responseText);
                $('#NotifikasiTambahIngridientEdit').html(
                    '<div class="alert alert-danger py-2 mb-2"><small>Terjadi kesalahan sistem</small></div>'
                );
            }
        });
    });

    // Hapus Ingrident List
    $(document).on('click', '.hapus_ingridient_edit', function () {
        // 1. Hapus card (item) yang bersangkutan saja
        $(this).closest('.card').remove();

        // 2. Perbarui nomor urut (badge) secara otomatis agar tetap berurutan
        $('#list_ingridient_edit .card').each(function (index) {
            $(this).find('.badge').text(index + 1);
        });
    });

    //Proses Edit Medication
    $('#ProsesEdit').submit(function(e){

        e.preventDefault();

        // Data Form
        var ProsesEdit = $(this).serialize();

        // Simpan Tombol
        var TombolEdit = $('#TombolEdit').html();

        // Reset Notifikasi
        $('#NotifikasiEdit').html('');

        // Loading
        $('#TombolEdit').html('Loading...').prop('disabled', true);

        // AJAX
        $.ajax({
            type     : 'POST',
            url      : '_Page/Medication/ProsesEdit.php',
            dataType : 'JSON',
            data     : ProsesEdit,

            success: function(response){

                if(response.status === 'success'){

                    // Tutup Modal
                    $('#ModalEdit').modal('hide');

                    // Reload Data
                    ShowData();

                    // Toast
                    showToast(
                        'success',
                        'Berhasil',
                        response.message || 'Data berhasil disimpan.'
                    );

                }else{

                    $('#NotifikasiEdit').html(`
                        <div class="alert alert-danger text-center">
                            <small>
                                <b>Opss!</b><br>
                                ${response.message}
                            </small>
                        </div>
                    `);
                }
            },

            error: function(xhr, status, error){

                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                $('#NotifikasiEdit').html(`
                    <div class="alert alert-danger text-center">
                        <small>Terjadi kesalahan sistem.</small>
                    </div>
                `);
            },

            complete: function(){
                $('#TombolEdit').prop('disabled', false).html(TombolEdit);
            }
        });
    });

    
    //-----------------------------------------------
    // HAPUS MEDICATION
    //-----------------------------------------------

    // Ketika Modal 'ModalHapusMedication' Ditampilkan
    $('#ModalHapusMedication').on('shown.bs.modal', function (e) {

        // Menangkap 'id_index_medication'
        var id_index_medication = $(e.relatedTarget).data('id');

        // Kosongkan Notifikasi dan Form
        $('#FormHapusMedication').html('Loading...');
        $('#NotifikasiHapusMedication').html('');

        // Disable Tombol
        $('#TombolHapusMedication').prop('disabled', true);

        // Ambil Form dengan AJAX
        $.ajax({
            type    : 'POST',
            url     : '_Page/Medication/FormHapusMedication.php',
            data    : {id_index_medication: id_index_medication},
            dataType: 'JSON',
            success: function (response) {

                if (response.status === 'success') {

                    $('#FormHapusMedication').html(response.html);

                    // Enable tombol
                    $('#TombolHapusMedication').prop('disabled',false);

                } else {

                    $('#FormHapusMedication').html(`
                        <div class="alert alert-danger text-center mb-0">
                            <small>${response.message}</small>
                        </div>
                    `);

                    $('#TombolHapusMedication').prop('disabled', true);
                }
            },

            error: function (xhr, status, error) {

                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                $('#FormHapusMedication').html(`
                    <div class="alert alert-danger text-center mb-0">
                        <small>Terjadi kesalahan saat menyiapkan data medication.</small>
                    </div>
                `);

                $('#TombolHapusMedication').prop('disabled', true);
            }
        });
    });

    //Proses Hapus Medication
    $('#ProsesHapusMedication').submit(function(e){

        e.preventDefault();

        // Data Form
        var ProsesHapusMedication = $(this).serialize();

        // Simpan Tombol
        var TombolHapusMedication = $('#TombolHapusMedication').html();

        // Reset Notifikasi
        $('#NotifikasiHapusMedication').html('');

        // Loading
        $('#TombolHapusMedication').html('Loading...').prop('disabled', true);

        // AJAX
        $.ajax({
            type     : 'POST',
            url      : '_Page/Medication/ProsesHapusMedication.php',
            dataType : 'JSON',
            data     : ProsesHapusMedication,

            success: function(response){

                if(response.status === 'success'){

                    // Tutup Modal
                    $('#ModalHapusMedication').modal('hide');

                    // Reload Data
                    ShowData();

                    // Toast
                    showToast(
                        'success',
                        'Berhasil',
                        response.message || 'Data berhasil dihapus.'
                    );

                }else{

                    $('#NotifikasiHapusMedication').html(`
                        <div class="alert alert-danger text-center">
                            <small>
                                <b>Opss!</b><br>
                                ${response.message}
                            </small>
                        </div>
                    `);
                }
            },

            error: function(xhr, status, error){

                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                $('#NotifikasiHapusMedication').html(`
                    <div class="alert alert-danger text-center">
                        <small>Terjadi kesalahan sistem.</small>
                    </div>
                `);
            },

            complete: function(){

                $('#TombolHapusMedication')
                    .prop('disabled', false)
                    .html(TombolHapusMedication);
            }
        });
    });
    

    

});






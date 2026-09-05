
// =======================================
// FUNCTION
// =======================================

//---------------------------------------
//Fungsi Untuk Menampilkan Data Nakes
function ShowResepObat() {
    // Target And Filter
    let target = $('#list_resep_obat');
    let data   = $('#ProsesFilter').serialize();

    target.addClass('blur-loading');

    $.ajax({
        type    : 'POST',
        url     : '_Page/Resep/TabelResep.php',
        data    : data,
        dataType: 'JSON',
        success : function(res) {

            if(res.status === "success"){

                target.fadeOut(150, function () {
                    target.html(res.html).fadeIn(150);
                });

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

//---------------------------------------
// Menampilkan Notif pada form tambah
function showNotification(message, type = 'danger') {
    $('#NotifikasiTambah').html(`
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <small>${message}</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
}

//---------------------------------------
// Menghapus notif saat form tambah
function clearNotification() {
    $('#NotifikasiTambah').html('');
}

//---------------------------------------
// Fungsi Reset Form Tambah Resep
function ResetFormTambahResep() {

    // RESET FORM
    $('#ProsesTambah')[0].reset();

    // RESET SELECT2
    $('#id_anggota').val(null).trigger('change');
    $('#dokter_id').val(null).trigger('change');
    $('#reason_code').val(null).trigger('change');
    $('#apoteker_id').val(null).trigger('change');

    // RESET KUNJUNGAN
    $('#id_kunjungan')
        .empty()
        .append('<option value="">Pilih</option>')
        .prop('disabled', true);

    // RESET HIDDEN DOKTER
    $('input[name="dokter_code"]').val('');
    $('input[name="dokter_ihs"]').val('');
    $('input[name="dokter_nama"]').val('');

    // RESET REASON
    $('input[name="reason_display"]').val('');
    $('input[name="reason_system"]').val(
        'http://hl7.org/fhir/sid/icd-10'
    );

    // RESET HIDDEN APOTEKER
    $('input[name="apoteker_code"]').val('');
    $('input[name="apoteker_nama"]').val('');
    $('input[name="apoteker_ihs"]').val('');

    // RESET NOTIFIKASI
    $('#NotifikasiTambah').html('');
}

//---------------------------------------
// Fungsi Menampilkan Detail Resep
function ShowDetailResep() {

    // Tangkap Data Dari Form Detail
    let ProsesDetail = $('#ProsesDetail').serialize();

    // Menampilkan Detail Resep Dengan AJAX
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Resep/_DetailResep.php',
        data 	    :  ProsesDetail,
        success     : function(data){
            $('#detail_view').html(data);
            
            // Panggil ShowItemResep DI SINI agar elemen #list_item_resep sudah pasti ada di DOM
            ShowItemResep();
        }
    });
}

//---------------------------------------
// Fungsi Menampilkan List Item Resep
function ShowItemResep() {

    // Tangkap Data Dari Form Detail
    let ProsesDetail = $('#ProsesDetail').serialize();

    // Menampilkan Detail Resep Dengan AJAX
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/Resep/TabelItemResep.php',
        data 	    :  ProsesDetail,
        success     : function(data){
            $('#list_item_resep').html(data);
        }
    });
}

//---------------------------------------
// Fungsi kontrol ingredient berdasarkan racikan_code
//---------------------------------------
// Kontrol Ingredient Berdasarkan Racikan
function kontrolIngredient() {

    const racikanCode = $('#racikan_code').val();

    //---------------------------------------
    // Jika Non Compound / Belum Dipilih
    if (
        racikanCode === '' ||
        racikanCode === null ||
        racikanCode === 'NC'
    ) {

        $('#modal_tambah_ingridient')
            .prop('disabled', true)
            .removeClass('btn-primary')
            .addClass('btn-secondary');

    } else {

        //---------------------------------------
        // SD / EP
        $('#modal_tambah_ingridient')
            .prop('disabled', false)
            .removeClass('btn-secondary')
            .addClass('btn-primary');
    }
}

//---------------------------------------
// Menampilkan Select2 pada KFA Ingridient
function initSelect2KfaIngridient(parentModal) {
    var medication_category = 'Obat';
    $('#ingridient_kfa').select2({
        theme             : 'bootstrap-5',
        dropdownParent    : $(parentModal),
        placeholder       : 'Cari Zat/Kandungan...',
        allowClear        : true,
        minimumInputLength: 3,
        ajax              : {
            url     : '_Page/Resep/ListKfa.php',
            dataType: 'json',
            delay   : 300,
            data    : function (params) {
                return {
                    keyword            : params.term,
                    medication_category: medication_category,
                    page               : params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;

                return {
                    results   : data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        }
    });
}

//---------------------------------------
// Menampilkan Satuan Numerator
function initSelect2SatuanNumerator(parentModal) {

    $('#satuan_numerator').select2({
        theme: 'bootstrap-5',
        dropdownParent: $(parentModal),
        placeholder: 'Satuan Numerator...',
        allowClear: true,
        minimumInputLength: 0,

        ajax: {
            url: '_Page/Resep/ListNumerator.php',
            dataType: 'json',
            delay: 300,

            data: function (params) {
                return {
                    keyword: params.term || '',
                    page: params.page || 1,
                    limit: 10
                };
            },

            processResults: function (data) {
                return {
                    results: data.results || [],
                    pagination: {
                        more: data.pagination?.more || false
                    }
                };
            }
        }
    });
}

//---------------------------------------
// Menampilkan Satuan Denominator
function initSelect2SatuanDenominator(parentModal) {

    $('#satuan_denominator').select2({
        theme: 'bootstrap-5',
        dropdownParent: $(parentModal),
        placeholder: 'Satuan Denominator...',
        allowClear: true,
        minimumInputLength: 0,
        width: '100%',

        ajax: {
            url: '_Page/Resep/ListDenominator.php',
            dataType: 'json',
            delay: 300,

            data: function (params) {
                return {
                    keyword: params.term || '',
                    page: params.page || 1,
                    limit: 10
                };
            },

            processResults: function (data, params) {

                params.page = params.page || 1;

                return {
                    results: data.results || [],
                    pagination: {
                        more: data.pagination?.more || false
                    }
                };
            },

            cache: true
        }
    });
}

//---------------------------------------
// Kontrol Ingredient Edit
function kontrolIngredientEdit() {
    const racikanCode = $('#racikan_code_edit').val();
    const isDisabled = !racikanCode || racikanCode === 'NC';

    $('#modal_tambah_ingridient_edit')
        .prop('disabled', isDisabled)
        .toggleClass('btn-primary', !isDisabled)
        .toggleClass('btn-secondary', isDisabled);
}

//---------------------------------------
// Render Ingredient Edit
function renderIngredientEdit(ingredient) {
    if (typeof ingredient === 'string') {
        try {
            ingredient = JSON.parse(ingredient);
        } catch (e) {
            ingredient = [];
        }
    }

    if (!Array.isArray(ingredient)) {
        ingredient = [];
    }

    // Jika Kosong
    if (ingredient.length === 0) {
        $('#table_list_ingridient_edit').html(`
            <tr>
                <td colspan="6" class="text-center"><small>No Data</small></td>
            </tr>
        `);
        return;
    }

    // Isi Table
    let html = '';

    $.each(ingredient, function (index, item) {
        const numerator = item.jumlah_numerator ? `${item.jumlah_numerator} ${item.nama_numerator ?? ''}` : '-';
        const denominator = item.jumlah_denominator ? `${item.jumlah_denominator} ${item.nama_denominator ?? ''}` : '-';

        html += `
            <tr>
                <td class="text-center"><small>${index + 1}</small></td>
                <td><small>${item.kode_kfa ?? '-'}</small></td>
                <td><small>${item.nama_kfa ?? '-'}</small></td>
                <td class="text-center"><small>${numerator}</small></td>
                <td class="text-center"><small>${denominator}</small></td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm btn-hapus-ingridient-edit">
                        <i class="bi bi-trash"></i>
                    </button>
                    <input type="hidden" name="payload_ingridient[]" value='${JSON.stringify(item)}'>
                </td>
            </tr>
        `;
    });

    $('#table_list_ingridient_edit').html(html);
}

//---------------------------------------
// Init Medication Edit
function initMedicationEdit() {

    $('#id_index_medication_edit').select2({
        theme: 'bootstrap-5',
        placeholder: 'Pilih Obat',
        allowClear: true,
        width: '100%',
        minimumInputLength: 0,
        dropdownParent: $('#ModalEditItemResep'),

        ajax: {
            url: '_Page/Resep/OptionMedication.php',
            dataType: 'json',
            delay: 300,

            data: function (params) {
                return {
                    keyword: params.term || '',
                    page: params.page || 1,
                    limit: 10
                };
            },

            processResults: function (data) {
                return {
                    results: data.results || [],
                    pagination: {
                        more: data.pagination?.more || false
                    }
                };
            },

            cache: true
        }
    });
}

//---------------------------------------
// Init Satuan Edit
function initSatuanEdit() {

    $('.select_satuan_edit').select2({
        theme: 'bootstrap-5',
        placeholder: 'Pilih Satuan',
        allowClear: true,
        width: '100%',
        minimumInputLength: 0,
        dropdownParent: $('#ModalEditItemResep'),

        ajax: {
            url: '_Page/Resep/OptionSatuan.php',
            dataType: 'json',
            delay: 300,

            data: function (params) {
                return {
                    q: params.term || '',
                    page: params.page || 1
                };
            },

            processResults: function (data) {
                return {
                    results: data.results || [],
                    pagination: {
                        more: data.pagination?.more || false
                    }
                };
            },

            cache: true
        }
    });
}

//---------------------------------------
// Init Route Edit
function initRouteEdit() {

    $('#route_code_edit').select2({
        theme: 'bootstrap-5',
        placeholder: 'Pilih Route',
        allowClear: true,
        width: '100%',
        minimumInputLength: 0,
        dropdownParent: $('#ModalEditItemResep'),

        ajax: {
            url: '_Page/Resep/OptionRoute.php',
            dataType: 'json',
            delay: 300,

            data: function (params) {
                return {
                    q: params.term || '',
                    page: params.page || 1
                };
            },

            processResults: function (data) {
                return {
                    results: data.results || [],
                    pagination: {
                        more: data.pagination?.more || false
                    }
                };
            },

            cache: true
        }
    });
}

// =======================================
// EVENT LISTENER
// =======================================

$(document).ready(function() {
    
    //---------------------------------------
    // Menampilkan Data Pertama Kali
    // Menyembunyikan detail_view dan menampilkan data_view
    $('#data_view').show();
    $('#detail_view').hide();

    // Load data
    ShowResepObat();

    // Auto Focus ModalFilter
    $('#ModalFilter').on('shown.bs.modal', function () {
        $('#keyword').trigger('focus');
    });

    // Pagging
    // Pagination - Next
    $(document).on('click', '#next_button', function() {
        let page_now  = parseInt($('#page').val(), 10) || 1;
        let next_page = page_now + 1;
        $('#page').val(next_page);
        ShowResepObat(0);
        
        // Scroll ke atas
        window.scrollTo({top: 0,behavior: 'smooth'});
    });


    // Pagination - Previous
    $(document).on('click', '#prev_button', function() {

        let page_now  = parseInt($('#page').val(), 10) || 1;
        let prev_page = page_now - 1;

        // Mencegah halaman kurang dari 1
        if (prev_page < 1) {
            prev_page = 1;
        }

        $('#page').val(prev_page);

        ShowResepObat(0);

        // Scroll ke atas
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });

    });

    // Change keyword_by
    $('#keyword_by').change(function(){
        var keyword_by = $('#keyword_by').val();
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Resep/FormFilter.php',
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
        ShowResepObat();
        
    });

    // --------------------------------------------
    // TAMBAH RESEP
    // --------------------------------------------
    
    // Auto Focus ModalTambah
    $('#ModalTambah').on('shown.bs.modal', function () {
        $('#kategori_resep').trigger('focus');
    });

    // KONFIGURASI
    const modalTambah             = $('#ModalTambah');
    const urlGetMyCompany         = '_Page/Resep/GetMyCompany.php';
    const urlGetPatient           = '_Page/Resep/GetPatient.php';
    const urlGetKunjungan         = '_Page/Resep/GetKunjungan.php';
    const urlGetMedicalPersonel = '_Page/Resep/GetMedicalPersonel.php';
    const urlGetIcds              = '_Page/Resep/GetIcds.php';

    // 1. KATEGORI RESEP
    // --------------------------------------------
    // Jika kategori = Keluar:
    // Ambil sumber resep dari faskes/company sendiri.
    //
    // Jika kategori = Masuk:
    // sumber_resep dikosongkan dan dapat diisi manual.
    // --------------------------------------------
    $('#kategori_resep').on('change', function () {
        const kategoriResep = $(this).val();
        clearNotification();
        if (kategoriResep === 'Keluar') {
            $('#sumber_resep')
                .val('')
                .prop('readonly', true)
                .attr('placeholder', 'Mengambil data faskes...');
            $.ajax({
                type      : 'GET',
                url       : urlGetMyCompany,
                dataType  : 'json',
                beforeSend: function () {
                    $('#sumber_resep').val('Loading...');
                },
                success: function (response) {
                    if (response.status === 'success') {
                        $('#sumber_resep')
                            .val(response.data.sumber_resep ?? '')
                            .prop('readonly', true)
                            .attr('placeholder', '');
                    } else {
                        $('#sumber_resep')
                            .val('')
                            .prop('readonly', false);
                        showNotification(
                            response.message ?? 'Gagal mengambil data sumber resep.'
                        );
                    }
                },
                error: function (xhr) {
                    $('#sumber_resep')
                        .val('')
                        .prop('readonly', false);
                    showNotification(
                        'Terjadi kesalahan saat mengambil data faskes.'
                    );
                    console.error(xhr.responseText);
                }
            });
        } else if (kategoriResep === 'Masuk') {
            // Resep dari luar faskes
            $('#sumber_resep')
                .val('')
                .prop('readonly', false)
                .attr('placeholder', 'Nama faskes / sumber resep');
        } else {
            $('#sumber_resep')
                .val('')
                .prop('readonly', false)
                .attr('placeholder', '');
        }
    });

    // ============================================================
    // 2. SELECT2 PASIEN / ANGGOTA
    // ============================================================
    // Search:
    // - nama
    // - id_pasien / No.RM
    //
    // Paging:
    // limit = 10
    // ============================================================
    $('#id_anggota').select2({
        theme: 'bootstrap-5',
        dropdownParent: modalTambah,
        width: '100%',
        placeholder: 'Cari No.RM / Nama Pasien',
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: urlGetPatient,
            type: 'GET',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    keyword: params.term || '',
                    page: params.page || 1,
                    limit: 10
                };
            },
            processResults: function (response, params) {
                params.page = params.page || 1;
                return {
                    results: response.results || [],
                    pagination: {
                        more: response.pagination?.more || false
                    }
                };
            },
            cache: true
        },
        templateResult: function (data) {
            if (data.loading) {
                return data.text;
            }
            return $(`
                <div>
                    <div>
                        <strong>${data.nama ?? '-'}</strong>
                    </div>
                    <small class="text-muted">
                        No.RM : ${data.id_pasien ?? '-'}
                    </small>
                </div>
            `);
        },
        templateSelection: function (data) {
            if (!data.id) {
                return data.text;
            }
            if (data.id_pasien && data.nama) {
                return `${data.id_pasien} - ${data.nama}`;
            }
            return data.text;
        }
    });

    // ============================================================
    // 3. KETIKA PASIEN DIPILIH
    // LOAD DATA KUNJUNGAN
    // ============================================================
    $('#id_anggota').on('change', function () {
        const idAnggota = $(this).val();
        // Reset kunjungan
        $('#id_kunjungan')
            .empty()
            .append('<option value="">Pilih</option>')
            .prop('disabled', true);
        if (!idAnggota) {
            return;
        }
        $.ajax({
            type: 'GET',
            url: urlGetKunjungan,
            dataType: 'json',
            data: {
                id_anggota: idAnggota
            },
            beforeSend: function () {
                $('#id_kunjungan')
                    .empty()
                    .append('<option value="">Loading...</option>')
                    .prop('disabled', true);
            },
            success: function (response) {
                $('#id_kunjungan')
                    .empty()
                    .append('<option value="">Pilih</option>');
                if (response.status === 'success') {
                    if (
                        Array.isArray(response.data) &&
                        response.data.length > 0
                    ) {
                        $.each(response.data, function (index, item) {
                            $('#id_kunjungan').append(
                                $('<option>', {
                                    value: item.id_kunjungan,
                                    text: item.display
                                })
                            );
                        });
                        $('#id_kunjungan').prop('disabled', false);
                    } else {
                        $('#id_kunjungan')
                            .append(
                                '<option value="" disabled>Tidak ada kunjungan</option>'
                            )
                            .prop('disabled', true);
                    }
                } else {
                    $('#id_kunjungan').prop('disabled', true);
                    showNotification(
                        response.message ?? 'Data kunjungan tidak ditemukan.'
                    );
                }
            },
            error: function (xhr) {
                $('#id_kunjungan')
                    .empty()
                    .append('<option value="">Gagal memuat data</option>')
                    .prop('disabled', true);
                showNotification(
                    'Terjadi kesalahan saat mengambil data kunjungan.'
                );
                console.error(xhr.responseText);
            }
        });
    });

    // ============================================================
    // 4. SELECT2 DOKTER PEMBERI RESEP
    // ============================================================
    $('#dokter_id').select2({
        theme: 'bootstrap-5',
        dropdownParent: modalTambah,
        width: '100%',
        placeholder: 'Cari Dokter',
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: urlGetMedicalPersonel,
            type: 'GET',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    keyword: params.term || '',
                    page: params.page || 1,
                    limit: 10,
                    // Filter backend
                    role: 'dokter'
                };
            },
            processResults: function (response, params) {
                params.page = params.page || 1;
                return {
                    results: response.results || [],
                    pagination: {
                        more: response.pagination?.more || false
                    }
                };
            },
            cache: true
        },
        templateResult: function (data) {
            if (data.loading) {
                return data.text;
            }
            return $(`
                <div>
                    <strong>
                        ${data.medicalPersonelName ?? '-'}
                    </strong>
                    <br>
                    <small class="text-muted">
                        ${data.medicalPersonelCategory ?? '-'}
                    </small>
                </div>
            `);
        },
        templateSelection: function (data) {
            if (!data.id) {
                return data.text;
            }
            return data.medicalPersonelName ?? data.text;
        }
    });

    // ============================================================
    // SIMPAN DATA DOKTER KE HIDDEN INPUT
    // ============================================================
    $('#dokter_id').on('select2:select', function (e) {
        const data = e.params.data;
        $('input[name="dokter_code"]').val(
            data.medicalPersonelCode ?? ''
        );
        $('input[name="dokter_ihs"]').val(
            data.id_practitioner ?? ''
        );
        $('input[name="dokter_nama"]').val(
            data.medicalPersonelName ?? ''
        );
    });

    $('#dokter_id').on('select2:clear', function () {
        $('input[name="dokter_code"]').val('');
        $('input[name="dokter_ihs"]').val('');
        $('input[name="dokter_nama"]').val('');
    });

    // ============================================================
    // 5. SELECT2 REASON CODE / ICD-10
    // ============================================================
    $('#reason_code').select2({
        theme: 'bootstrap-5',
        dropdownParent: modalTambah,
        width: '100%',
        placeholder: 'Cari Diagnosis ICD-10',
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: urlGetIcds,
            type: 'GET',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    keyword: params.term || '',
                    page: params.page || 1,
                    limit: 10,
                    // Untuk membatasi ICD-10
                    icd: 'ICD10'
                };
            },
            processResults: function (response, params) {
                params.page = params.page || 1;
                return {
                    results: response.results || [],
                    pagination: {
                        more: response.pagination?.more || false
                    }
                };
            },
            cache: true
        },
        templateResult: function (data) {
            if (data.loading) {
                return data.text;
            }
            return $(`
                <div>
                    <strong>
                        ${data.kode ?? '-'}
                    </strong>
                    <br>
                    <small class="text-muted">
                        ${data.long_des ?? '-'}
                    </small>
                </div>
            `);
        },
        templateSelection: function (data) {
            if (!data.id) {
                return data.text;
            }
            if (data.kode && data.long_des) {
                return `${data.kode} - ${data.long_des}`;
            }
            return data.text;
        }
    });

    // ============================================================
    // SIMPAN REASON DISPLAY
    // ============================================================
    $('#reason_code').on('select2:select', function (e) {
        const data = e.params.data;
        $('input[name="reason_display"]').val(
            data.long_des ?? ''
        );
        // Tetap ICD-10
        $('input[name="reason_system"]').val(
            'http://hl7.org/fhir/sid/icd-10'
        );
    });

    $('#reason_code').on('select2:clear', function () {
        $('input[name="reason_display"]').val('');
        $('input[name="reason_system"]').val(
            'http://hl7.org/fhir/sid/icd-10'
        );
    });

    // ============================================================
    // 6. SELECT2 APOTEKER
    // ============================================================
    $('#apoteker_id').select2({
        theme: 'bootstrap-5',
        dropdownParent: modalTambah,
        width: '100%',
        placeholder: 'Cari Apoteker',
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: urlGetMedicalPersonel,
            type: 'GET',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    keyword: params.term || '',
                    page: params.page || 1,
                    limit: 10,
                    // Filter backend
                    role: 'apoteker'
                };
            },
            processResults: function (response, params) {
                params.page = params.page || 1;
                return {
                    results: response.results || [],
                    pagination: {
                        more: response.pagination?.more || false
                    }
                };
            },
            cache: true
        },
        templateResult: function (data) {
            if (data.loading) {
                return data.text;
            }
            return $(`
                <div>
                    <strong>
                        ${data.medicalPersonelName ?? '-'}
                    </strong>
                    <br>
                    <small class="text-muted">
                        ${data.medicalPersonelCategory ?? '-'}
                    </small>
                </div>
            `);
        },
        templateSelection: function (data) {
            if (!data.id) {
                return data.text;
            }
            return data.medicalPersonelName ?? data.text;
        }
    });

    // ============================================================
    // SIMPAN DATA APOTEKER KE HIDDEN INPUT
    // ============================================================
    $('#apoteker_id').on('select2:select', function (e) {
        const data = e.params.data;
        $('input[name="apoteker_code"]').val(
            data.medicalPersonelCode ?? ''
        );
        $('input[name="apoteker_nama"]').val(
            data.medicalPersonelName ?? ''
        );
        $('input[name="apoteker_ihs"]').val(
            data.id_practitioner ?? ''
        );
    });

    $('#apoteker_id').on('select2:clear', function () {
        $('input[name="apoteker_code"]').val('');
        $('input[name="apoteker_nama"]').val('');
        $('input[name="apoteker_ihs"]').val('');
    });

    // ============================================================
    // RESET FORM KETIKA MODAL DITUTUP
    // ============================================================
    modalTambah.on('hidden.bs.modal', function () {
        const form = $('#ProsesTambah')[0];
        if (form) {
            form.reset();
        }
        clearNotification();
        // Reset Select2
        $('#id_anggota')
            .val(null)
            .trigger('change');
        $('#dokter_id')
            .val(null)
            .trigger('change');
        $('#reason_code')
            .val(null)
            .trigger('change');
        $('#apoteker_id')
            .val(null)
            .trigger('change');
        // Reset kunjungan
        $('#id_kunjungan')
            .empty()
            .append('<option value="">Pilih</option>')
            .prop('disabled', true);
        // Reset hidden dokter
        $('input[name="dokter_code"]').val('');
        $('input[name="dokter_ihs"]').val('');
        $('input[name="dokter_nama"]').val('');
        // Reset reason
        $('input[name="reason_display"]').val('');
        $('input[name="reason_system"]').val(
            'http://hl7.org/fhir/sid/icd-10'
        );
        // Reset apoteker
        $('input[name="apoteker_code"]').val('');
        $('input[name="apoteker_nama"]').val('');
        $('input[name="apoteker_ihs"]').val('');
    });

    // Proses Tambah Resep
    $('#ProsesTambah').submit(function(){
        
        // Tangkap Data
        var ProsesTambah = $('#ProsesTambah').serialize();

        // Tombol
        var ButtonTambah = $('#ButtonTambah').html();

        // Loading Tombol
        $('#ButtonTambah').html('...');

        // Clear Notifikasi Text
        $('#NotifikasiTambah').html("");

        // Disable tombol
        $('#ButtonTambah').prop('disabled', true);

        // Insert Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Resep/ProsesTambah.php',
            dataType    : 'JSON',
            data 	    :  ProsesTambah,
            success     : function(response){

                // Status & message
                let status = response.status;
                let message = response.message;

                // Jika Berhasil
                if(status=='success'){
                    //tutup modal
                    $('#ModalTambah').modal('hide');

                    //Reset halaman
                    $('#page').val(1);

                    //Reset Form
                    $('#ProsesTambah')[0].reset();
                    ResetFormTambahResep();

                    //Tampilkan Data
                    ShowResepObat();

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiTambah').html('<div class="alert alert-danger mt-3 mb-3"><small>'+message+'</small></div>');
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
                $('#NotifikasiTambah').html(`<div class="alert alert-danger mt-3 mb-3">Terjadi kesalahan server.</div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#ButtonTambah').prop('disabled', false);
                $('#ButtonTambah').html(ButtonTambah);
            }
        });
    });

    // --------------------------------------------
    // DETAIL RESEP
    // --------------------------------------------

    // Ketika Modal Muncul
    $('#ModalDetail').on('show.bs.modal', function (e) {

        // Tangkap 'id_medication_request_group'
        var id_medication_request_group= $(e.relatedTarget).data('id');

        // Loading Form
        $('#FormDetail').html("Loading...");

        // Ambil Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Resep/FormDetail.php',
            data        : {id_medication_request_group: id_medication_request_group},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;

                // Apabila status success
                if(status=='success'){
                    var html = response.html;
                    $('#FormDetail').html(html);
                }else{
                    $('#FormDetail').html(`
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
                $('#FormDetail').html(`
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

    // Submit Detail Resep
    $('#ProsesDetail').submit(function(){
        
        // Menampilkan detail_view dan Menyembunyikan data_view
        $('#data_view').hide();
        $('#detail_view').show();

        // Sembunyikan Modal
        $('#ModalDetail').modal('hide');

        // Panggil Fungsi Detail Resep
        ShowDetailResep();

        // Scroll ke atas
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Kembali Ke Data View
   $(document).on('click', '.tombol_kembali', function () {
        // Menyembunyikan detail_view dan Menampilkan data_view
        $('#data_view').show();
        $('#detail_view').hide();

        // Reload Data
        ShowResepObat(0);

        // Scroll ke atas
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });


    // --------------------------------------------
    // EDIT RESEP
    // --------------------------------------------

    // Ketika Modal Muncul
    $(document).on('click', '.edit_resep', function () {

        // Buka Modal
        $('#ModalEdit').modal("show");

        // Tangkap 'id_medication_request_group'
        var id_medication_request_group= $(this).data('id');

        // Kosongkan Notifikasi
        $('#NotifikasiEdit').html("");

        // Loading Form
        $('#FormEdit').html("Loading...");

        // Disable Button
        $('#TombolEdit').prop('disabled', true);

        // Ambil Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Resep/FormEdit.php',
            data        : {id_medication_request_group: id_medication_request_group},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;

                // Apabila status success
                if(status=='success'){

                    // Tangkap HTML dan Tempelkan Ke Form
                    var html = response.html;
                    $('#FormEdit').html(html);

                    // Enamble Tombol
                    $('#TombolEdit').prop('disabled', false);
                }else{
                    $('#FormEdit').html(`
                        <div class="alert alert-danger text-center">
                            <small>
                                <b>Ops!!</b><br>
                                Terjadi Kesalahan : ${message}
                            </small>
                        </div>
                    `);

                    // Disable Tombol
                    $('#TombolEdit').prop('disabled', true);
                }
            },
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormEdit').html(`
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Ops!!</b><br>
                            Terjadi Kesalahan Pada Sistem.
                        </small>
                    </div>
                `);
                // Disable Tombol
                $('#TombolEdit').prop('disabled', true);
            },

        });
    });
    

    // Proses Edit Nakes
    $('#ProsesEdit').submit(function(){
        
        // Tangkap Data
        var ProsesEdit = $('#ProsesEdit').serialize();

        // Tombol
        var TombolEdit = $('#TombolEdit').html();

        // Loading Tombol
        $('#TombolEdit').html('...');

        // Clear Notifikasi Text
        $('#NotifikasiEdit').html("");

        // Disable tombol
        $('#TombolEdit').prop('disabled', true);

        // Insert Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Resep/ProsesEdit.php',
            dataType    : 'JSON',
            data 	    :  ProsesEdit,
            success     : function(response){

                // Status & message
                let status = response.status;
                let message = response.message;

                // Jika Berhasil
                if(status=='success'){
                    //Tutup modal
                    $('#ModalEdit').modal('hide');

                    // Kosongkan Notifikasi
                    $('#NotifikasiEdit').html('');

                    //Reload Data
                    ShowResepObat();
                    ShowDetailResep();

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiEdit').html('<div class="alert alert-danger mt-3 mb-3"><small>'+message+'</small></div>');
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
                $('#NotifikasiEdit').html(`<div class="alert alert-danger mt-3 mb-3">Terjadi kesalahan server.</div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#TombolEdit').prop('disabled', false);
                $('#TombolEdit').html(TombolEdit);
            }
        });
    });

    // --------------------------------------------
    // HAPUS RESEP
    // --------------------------------------------

    // Ketika Modal Muncul
    $(document).on('click', '.hapus_resep', function () {

        // Tangkap 'id_medication_request_group'
        var id_medication_request_group= $(this).data('id');

        // Buka Modal
        $('#ModalHapus').modal("show");


        // Kosongkan Notifikasi
        $('#NotifikasiHapus').html("");

        // Loading Form
        $('#FormHapus').html("Loading...");

        // Disable Button
        $('#TombolHapus').prop('disabled', true);

        // Ambil Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Resep/FormHapus.php',
            data        : {id_medication_request_group: id_medication_request_group},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;

                // Apabila status success
                if(status=='success'){

                    // Tangkap HTML dan Tempelkan Ke Form
                    var html = response.html;
                    $('#FormHapus').html(html);

                    // Enamble Tombol
                    $('#TombolHapus').prop('disabled', false);
                }else{
                    $('#FormHapus').html(`
                        <div class="alert alert-danger text-center">
                            <small>
                                <b>Ops!!</b><br>
                                Terjadi Kesalahan : ${message}
                            </small>
                        </div>
                    `);

                    // Disable Tombol
                    $('#TombolHapus').prop('disabled', true);
                }
            },
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormHapus').html(`
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Ops!!</b><br>
                            Terjadi Kesalahan Pada Sistem.
                        </small>
                    </div>
                `);
                // Disable Tombol
                $('#TombolHapus').prop('disabled', true);
            },

        });
    });

    // Proses Hapus Nakes
    $('#ProsesHapus').submit(function(){
        
        // Tangkap Data
        var ProsesHapus = $('#ProsesHapus').serialize();

        // Tombol
        var TombolHapus = $('#TombolHapus').html();

        // Loading Tombol
        $('#TombolHapus').html('...');

        // Clear Notifikasi Text
        $('#NotifikasiHapus').html("");

        // Disable tombol
        $('#TombolHapus').prop('disabled', true);

        // Insert Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Resep/ProsesHapus.php',
            dataType    : 'JSON',
            data 	    :  ProsesHapus,
            success     : function(response){

                // Status & message
                let status = response.status;
                let message = response.message;

                // Jika Berhasil
                if(status=='success'){
                    //Tutup modal
                    $('#ModalHapus').modal('hide');

                    // Kosongkan Notifikasi
                    $('#NotifikasiHapus').html('');

                    //Tampilkan Data
                    $('#data_view').show();
                    $('#detail_view').hide();

                    // Reload Data
                    ShowResepObat(0);

                    // Scroll ke atas
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil dihapus.'
                    );
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiHapus').html('<div class="alert alert-danger mt-3 mb-3"><small>'+message+'</small></div>');
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
                $('#NotifikasiHapus').html(`<div class="alert alert-danger mt-3 mb-3">Terjadi kesalahan server.</div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#TombolHapus').prop('disabled', false);
                $('#TombolHapus').html(TombolHapus);
            }
        });
    });

    // --------------------------------------------
    // CETAK  RESEP
    // --------------------------------------------
    
    // Ketika Tombol Cetak Resep Diklik
    $(document).on('click', '.cetak_resep', function () {

        //---------------------------------------
        // Tangkap ID Resep
        var id_medication_request_group = $(this).data('id');

        //---------------------------------------
        // Buka Modal
        $('#ModalCetak').modal('show');

        //---------------------------------------
        // Loading
        $('#FormCetak').html(`
            <div class="text-center p-5">
                <div class="spinner-border text-primary"></div>
                <br>
                <small class="text-muted">Menyiapkan lembar resep...</small>
            </div>
        `);

        //---------------------------------------
        // Disable Tombol Cetak
        $('#TombolCetak').prop('disabled', true);

        //---------------------------------------
        // Ambil Preview
        $.ajax({
            type: 'POST',
            url: '_Page/Resep/FormCetak.php',
            dataType: 'JSON',

            data: {
                id_medication_request_group: id_medication_request_group
            },

            success: function (response) {

                //---------------------------------------
                // Jika Berhasil
                if (response.status === 'success') {

                    $('#FormCetak').html(response.html);

                    //---------------------------------------
                    // Simpan Nama File
                    $('#ProsesCetak').data(
                        'filename',
                        response.filename || 'Resep'
                    );

                    //---------------------------------------
                    // Enable Tombol
                    $('#TombolCetak').prop('disabled', false);

                } else {

                    $('#FormCetak').html(`
                        <div class="alert alert-danger text-center">
                            <small>${response.message}</small>
                        </div>
                    `);

                    $('#TombolCetak').prop('disabled', true);
                }
            },

            error: function (xhr, status, error) {

                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                $('#FormCetak').html(`
                    <div class="alert alert-danger text-center">
                        <small>
                            Terjadi kesalahan saat menyiapkan lembar resep.
                        </small>
                    </div>
                `);

                $('#TombolCetak').prop('disabled', true);
            }
        });
    });


    //---------------------------------------
    // Proses Cetak Resep
    $('#ProsesCetak').submit(function (e) {

        e.preventDefault();

        //---------------------------------------
        // Ambil ID Resep Dari Form Preview
        const id_medication_request_group =
            $('#lembar_resep').data('id');

        if (!id_medication_request_group) {
            return;
        }

        //---------------------------------------
        // Buka Halaman Cetak
        const url =
            '_Page/Resep/CetakResep.php?id_medication_request_group=' +
            encodeURIComponent(id_medication_request_group);

        window.open(url, '_blank');
    });

    // --------------------------------------------
    // TAMBAH ITEM RESEP
    // --------------------------------------------

    // Ketika Modal Muncul
    $(document).on('click', '.tambah_item_resep', function () {

        // Tangkap 'id_medication_request_group'
        var id_medication_request_group= $(this).data('id');

        // Buka Modal
        $('#ModalTambahItemResep').modal("show");


        // Kosongkan Notifikasi
        $('#NotifikasiTambahItemResep').html("");

        // Loading Form
        $('#FormTambahItemResep').html("Loading...");

        // Disable Button
        $('#TombolTambahItemResep').prop('disabled', true);

        // Ambil Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Resep/FormTambahItemResep.php',
            data        : {id_medication_request_group: id_medication_request_group},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;

                // Apabila status success
                if(status=='success'){

                    // Tangkap HTML dan Tempelkan Ke Form
                    var html = response.html;
                    $('#FormTambahItemResep').html(html);

                    // Enamble Tombol
                    $('#TombolTambahItemResep').prop('disabled', false);

                    // ------------------------------------------------
                    // Menampilkan select2 Index Medication
                    $('#id_index_medication').select2({
                        theme             : 'bootstrap-5',
                        placeholder       : 'Pilih Obat',
                        allowClear        : true,
                        width             : '100%',
                        minimumInputLength: 0,
                        dropdownParent    : $('#FormTambahItemResep'),
                        ajax: {
                            url     : '_Page/Resep/OptionMedication.php',
                            dataType: 'json',
                            delay   : 300,
                            data    : function (params) {
                                return {
                                    keyword: params.term || '',
                                    page: params.page || 1,
                                    limit: 10
                                };
                            },

                            processResults: function (data, params) {
                                params.page = params.page || 1;
                                return {
                                    results: data.results || [],
                                    pagination: {
                                        more: data.pagination?.more || false
                                    }
                                };
                            },
                            cache: true
                        },

                        templateResult: function (data) {
                            if (data.loading) {
                                return data.text;
                            }
                            return $(`
                                <div>
                                    <div>
                                        <strong>${data.medication_name ?? '-'}</strong>
                                    </div>
                                    <small class="text-muted">
                                        Kode Lokal : ${data.medication_code ?? '-'}
                                        ${data.kfa_code ? ' | KFA : ' + data.kfa_code : ''}
                                    </small>
                                </div>
                            `);
                        },

                        templateSelection: function (data) {
                            if (!data.id) {
                                return data.text;
                            }
                            return data.medication_name ?? data.text;
                        }
                    });

                    //---------------------------------------
                    // Ketika Medication Dipilih
                    $('#id_index_medication').on('select2:select', function (e) {

                        const data = e.params.data;

                        //---------------------------------------
                        // Isi Nama Medication
                        $('#name_medication')
                            .val(data.medication_name ?? '')
                            .prop('readonly', true);

                        //---------------------------------------
                        // Set Racikan Code
                        $('#racikan_code')
                            .val(data.racikan_code ?? '')
                            .trigger('change');

                        //---------------------------------------
                        // Kosongkan Table Ingredient
                        $('#table_list_ingridient').html('');

                        //---------------------------------------
                        // Ambil Ingredient Dari Medication
                        let ingredient = data.ingredient ?? [];

                        //---------------------------------------
                        // Jika Ingredient Masih String JSON
                        if (typeof ingredient === 'string') {
                            try {
                                ingredient = JSON.parse(ingredient);
                            } catch (error) {
                                ingredient = [];
                                console.error('Ingredient JSON tidak valid:', error);
                            }
                        }

                        //---------------------------------------
                        // Pastikan Array
                        if (!Array.isArray(ingredient)) {
                            ingredient = [];
                        }

                        //---------------------------------------
                        // Tampilkan Ingredient
                        if (ingredient.length > 0) {

                            $.each(ingredient, function (index, item) {

                                //---------------------------------------
                                // Format Numerator
                                let numerator = '-';

                                if (
                                    item.jumlah_numerator !== undefined &&
                                    item.jumlah_numerator !== null &&
                                    item.jumlah_numerator !== ''
                                ) {
                                    numerator =
                                        item.jumlah_numerator + ' ' +
                                        (item.nama_numerator ?? '');
                                }

                                //---------------------------------------
                                // Format Denominator
                                let denominator = '-';

                                if (
                                    item.jumlah_denominator !== undefined &&
                                    item.jumlah_denominator !== null &&
                                    item.jumlah_denominator !== ''
                                ) {
                                    denominator =
                                        item.jumlah_denominator + ' ' +
                                        (item.nama_denominator ?? '');
                                }

                                //---------------------------------------
                                // Buat Row
                                let row = `
                                    <tr>
                                        <td class="text-center">
                                            <small>${index + 1}</small>
                                        </td>

                                        <td>
                                            <small>${item.kode_kfa ?? '-'}</small>
                                        </td>

                                        <td>
                                            <small>${item.nama_kfa ?? '-'}</small>
                                        </td>

                                        <td class="text-center">
                                            <small>${numerator}</small>
                                        </td>

                                        <td class="text-center">
                                            <small>${denominator}</small>
                                        </td>

                                        <td class="text-center">
                                            <button
                                                type="button"
                                                class="btn btn-danger btn-sm btn-hapus-ingridient"
                                                title="Hapus Ingredient"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>

                                            <input
                                                type="hidden"
                                                name="payload_ingridient[]"
                                                value='${JSON.stringify(item)}'
                                            >
                                        </td>
                                    </tr>
                                `;

                                $('#table_list_ingridient').append(row);
                            });

                        } else {

                            //---------------------------------------
                            // Tidak Ada Ingredient
                            $('#table_list_ingridient').html(`
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <small>No Data</small>
                                    </td>
                                </tr>
                            `);
                        }

                        //---------------------------------------
                        // Kontrol Ingredient
                        kontrolIngredient();
                    });


                    //---------------------------------------
                    // Ketika Medication Dihapus
                    $('#id_index_medication').on('select2:clear', function () {

                        //---------------------------------------
                        // Reset Nama Medication
                        $('#name_medication')
                            .val('')
                            .prop('readonly', false);

                        //---------------------------------------
                        // Reset Racikan
                        $('#racikan_code')
                            .val('')
                            .trigger('change');

                        //---------------------------------------
                        // Reset Table Ingredient
                        $('#table_list_ingridient').html(`
                            <tr>
                                <td colspan="6" class="text-center">
                                    <small>No Data</small>
                                </td>
                            </tr>
                        `);

                        //---------------------------------------
                        // Kontrol Ingredient
                        kontrolIngredient();
                    });

                    // ------------------------------------------------
                    // Menampilkan select2 satuan
                    $('.select_satuan').select2({
                        theme             : 'bootstrap-5',
                        placeholder       : 'Pilih Satuan',
                        tags              : false,
                        width             : '100%',
                        minimumInputLength: 0,
                        dropdownParent    : $('#FormTambahItemResep'),
                        ajax: {
                            url     : '_Page/Resep/OptionSatuan.php',
                            dataType: 'json',
                            delay   : 300,
                            data    : function (params) {
                                return {
                                    q: params.term,
                                    page: params.page || 1
                                };
                            },
                            processResults: function (data, params) {
                                return {
                                    results: data.results,
                                    pagination: {
                                        more: data.pagination?.more || false
                                    }
                                };
                            },
                            cache: true
                        }
                    });
                    // ------------------------------------------------
                    // Menampilkan select2 route
                     $('#route_code').select2({
                        theme             : 'bootstrap-5',
                        placeholder       : 'Pilih Satuan',
                        tags              : false,
                        width             : '100%',
                        minimumInputLength: 0,
                        dropdownParent    : $('#FormTambahItemResep'),
                        ajax: {
                            url     : '_Page/Resep/OptionRoute.php',
                            dataType: 'json',
                            delay   : 300,
                            data    : function (params) {
                                return {
                                    q: params.term,
                                    page: params.page || 1
                                };
                            },
                            processResults: function (data, params) {
                                return {
                                    results: data.results,
                                    pagination: {
                                        more: data.pagination?.more || false
                                    }
                                };
                            },
                            cache: true
                        }
                    });

                }else{
                    $('#FormTambahItemResep').html(`
                        <div class="alert alert-danger text-center">
                            <small>
                                <b>Ops!!</b><br>
                                Terjadi Kesalahan : ${message}
                            </small>
                        </div>
                    `);

                    // Disable Tombol
                    $('#TombolTambahItemResep').prop('disabled', true);
                }
            },
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormTambahItemResep').html(`
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Ops!!</b><br>
                            Terjadi Kesalahan Pada Sistem.
                        </small>
                    </div>
                `);
                // Disable Tombol
                $('#TombolTambahItemResep').prop('disabled', true);
            },

        });
    });

    // Saat racikan_code diubah
    $(document).on('change', '#racikan_code', function () {
        kontrolIngredient();
    });

    // Modal Tambah Ingridient
    $(document).on('click', '#modal_tambah_ingridient', function () {

        //tampilkan modal
        $('#ModalTambahIngridient').modal('show');

        // Kosongkan Notifikasi
        $('#NotifikasiTambahIngridient').html('');

        //Form Loading
        $('#FormTambahIngridient').html('Loading...');

        //Tampilkan Form Dengan Ajax
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Resep/FormTambahIngridient.php',
            success     : function(data){
                $('#FormTambahIngridient').html(data);

                // Inisialisasi Select2
                initSelect2KfaIngridient('#ModalTambahIngridient');
                initSelect2SatuanNumerator('#ModalTambahIngridient');
                initSelect2SatuanDenominator('#ModalTambahIngridient');

            }
        });
    });

    //Proses Tambah Ingridient
    $('#ProsesTambahIngridient').submit(function(e){
        e.preventDefault();
        
        // Ambil Data Dari form
        var ProsesTambahIngridient = $(this).serialize();


        // Ajax Request
        $.ajax({
            type     : 'POST',
            url      : '_Page/Resep/ProsesTambahIngridient.php',
            dataType : 'json',
            data     : ProsesTambahIngridient,

            success: function(response){
                // Buat Variabel
                var status   = response.status;
                var payload  = response.payload;
                var message  = response.message || 'Proses berhasil';

                if(status === 'success'){

                    // Hapus row "Konten Belum Ada" jika ada
                    if($('#table_list_ingridient tr td').length === 1){
                        $('#table_list_ingridient').empty();
                    }

                    // Hitung nomor baris
                    var no = $('#table_list_ingridient tr').length + 1;

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

                    // Buat row
                    var content_row = `
                        <tr>
                            <td class="text-center"><small>${no}</small></td>
                            <td><small>${payload.kode_kfa}</small></td>
                            <td><small>${payload.nama_kfa}</small></td>
                            <td class="text-center"><small>${numerator}</small></td>
                            <td class="text-center"><small>${denominator}</small></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm btn-hapus-ingridient" title="Hapus Ingridient">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <input type="hidden" name="payload_ingridient[]" value='${JSON.stringify(payload)}'>
                            </td>
                        </tr>
                    `;

                    // Append ke tabel
                    $('#table_list_ingridient').append(content_row);

                    // Reset form
                    $('#ProsesTambahIngridient')[0].reset();

                    // Optional: reset Select2
                    $('#ingridient_kfa, #satuan_numerator, #satuan_denominator').val(null).trigger('change');

                    // tutup modal
                    $('#ModalTambahIngridient').modal('hide');

                } else {

                    $('#NotifikasiTambahIngridient').html(
                        '<div class="alert alert-danger"><small>'+message+'</small></div>'
                    );

                }
            },

            error: function(xhr){
                console.log(xhr.responseText);

                $('#NotifikasiTambahIngridient').html(
                    '<div class="alert alert-danger"><small>Terjadi kesalahan sistem</small></div>'
                );
            }
        });
    });

    // Hapus Ingrident List
    $(document).on('click', '.btn-hapus-ingridient', function(){
        $(this).closest('tr').remove();

        // Update ulang nomor
        $('#table_list_ingridient tr').each(function(index){
            $(this).find('td:first small').text(index + 1);
        });

        // Jika kosong, tampilkan placeholder
        if($('#table_list_ingridient tr').length === 0){
            $('#table_list_ingridient').html(`
                <tr>
                    <td colspan="6" class="text-center">
                        <small>Konten Belum Ada</small>
                    </td>
                </tr>
            `);
        }
    });

    // Proses Tambah Item Resep
    $('#ProsesTambahItemResep').submit(function(){
        
        // Tangkap Data
        var ProsesTambahItemResep = $('#ProsesTambahItemResep').serialize();

        // Tombol
        var TombolTambahItemResep = $('#TombolTambahItemResep').html();

        // Loading Tombol
        $('#TombolTambahItemResep').html('...');

        // Clear Notifikasi Text
        $('#NotifikasiTambahItemResep').html("");

        // Disable tombol
        $('#TombolTambahItemResep').prop('disabled', true);

        // Insert Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Resep/ProsesTambahItemResep.php',
            dataType    : 'JSON',
            data 	    :  ProsesTambahItemResep,
            success     : function(response){

                // Status & message
                let status = response.status;
                let message = response.message;

                // Jika Berhasil
                if(status=='success'){
                    //Tutup modal
                    $('#ModalTambahItemResep').modal('hide');

                    // Kosongkan Notifikasi
                    $('#NotifikasiTambahItemResep').html('');

                    // Reload Data
                    ShowDetailResep();

                    // Scroll ke atas
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiTambahItemResep').html('<div class="alert alert-danger mt-3 mb-3"><small>'+message+'</small></div>');
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
                $('#NotifikasiTambahItemResep').html(`<div class="alert alert-danger mt-3 mb-3">Terjadi kesalahan server.</div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#TombolTambahItemResep').prop('disabled', false);
                $('#TombolTambahItemResep').html(TombolTambahItemResep);
            }
        });
    });

    // --------------------------------------------
    // DETAIL ITEM RESEP
    // --------------------------------------------
    $(document).on('click', '.modal_detail_item_resep', function () {

        //---------------------------------------
        // Tangkap ID
        var MedicationRequestId = $(this).data('id');

        //---------------------------------------
        // Tampilkan Modal
        $('#ModalDetailItemResep').modal('show');

        //---------------------------------------
        // Loading
        $('#FormDetailItemResep').html(`
            <div class="text-center p-4">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <br>
                <small class="text-muted">Loading...</small>
            </div>
        `);

        //---------------------------------------
        // AJAX
        $.ajax({
            type: 'POST',
            url: '_Page/Resep/FormDetailItemResep.php',
            data: {
                MedicationRequestId: MedicationRequestId
            },
            dataType: 'JSON',

            success: function (response) {

                if (response.status === 'success') {

                    $('#FormDetailItemResep').html(
                        response.html
                    );

                } else {

                    $('#FormDetailItemResep').html(`
                        <div class="alert alert-danger text-center">
                            <small>${response.message}</small>
                        </div>
                    `);
                }
            },

            error: function (xhr, status, error) {

                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                $('#FormDetailItemResep').html(`
                    <div class="alert alert-danger text-center">
                        <small>
                            Terjadi kesalahan pada sistem.
                        </small>
                    </div>
                `);
            }
        });
    });

    // --------------------------------------------
    // EDIT ITEM RESEP
    // --------------------------------------------

    // Ketika Modal Edit Item Resep Muncul
    $(document).on('click', '.modal_edit_item_resep', function () {
        var MedicationRequestId = $(this).data('id');

        $('#ModalEditItemResep').modal('show');
        $('#NotifikasiEditItemResep').html('');
        $('#FormEditItemResep').html(`
            <div class="text-center p-4">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <br>
                <small class="text-muted">Loading...</small>
            </div>
        `);
        $('#TombolEditItemResep').prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: '_Page/Resep/FormEditItemResep.php',
            data: { MedicationRequestId: MedicationRequestId },
            dataType: 'JSON',
            success: function (response) {
                if (response.status === 'success') {
                    $('#FormEditItemResep').html(response.html);

                    initMedicationEdit();
                    initSatuanEdit();
                    initRouteEdit();
                    kontrolIngredientEdit();

                    $('#TombolEditItemResep').prop('disabled', false);
                } else {
                    $('#FormEditItemResep').html(`
                        <div class="alert alert-danger text-center">
                            <small>
                                <b>Ops!!</b><br>
                                ${response.message}
                            </small>
                        </div>
                    `);
                }
            },
            error: function (xhr, status, error) {
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                $('#FormEditItemResep').html(`
                    <div class="alert alert-danger text-center">
                        <small>
                            Terjadi kesalahan pada sistem.
                        </small>
                    </div>
                `);
            }
        });
    });

    // Medication Edit Dipilih
    $(document).on('select2:select', '#id_index_medication_edit', function (e) {
        const data = e.params.data;

        $('#name_medication_edit')
            .val(data.medication_name ?? '')
            .prop('readonly', true);

        $('#racikan_code_edit')
            .val(data.racikan_code ?? 'NC')
            .trigger('change');

        renderIngredientEdit(data.ingredient ?? []);
    });

    // Medication Edit Dihapus
    $(document).on('select2:clear', '#id_index_medication_edit', function () {
        $('#name_medication_edit')
            .val('')
            .prop('readonly', false);

        $('#racikan_code_edit')
            .val('NC')
            .trigger('change');

        renderIngredientEdit([]);
    });

    // Racikan Edit Diubah
    $(document).on('change', '#racikan_code_edit', function () {
        const value = $(this).val();

        if (value === 'NC') {
            renderIngredientEdit([]);
        }

        kontrolIngredientEdit();
    });

    // Buka Modal Tambah Ingredient Edit
    $(document).on('click', '#modal_tambah_ingridient_edit', function () {
        $('#ModalTambahIngridientEdit').modal('show');
        $('#NotifikasiTambahIngridientEdit').html('');
        $('#FormTambahIngridientEdit').html('Loading...');

        $.ajax({
            type: 'POST',
            url: '_Page/Resep/FormTambahIngridient.php',
            success: function (data) {
                $('#FormTambahIngridientEdit').html(data);

                initSelect2KfaIngridient('#FormTambahIngridientEdit');
                initSelect2SatuanNumerator('#FormTambahIngridientEdit');
                initSelect2SatuanDenominator('#FormTambahIngridientEdit');
            }
        });
    });

    // Tambah Ingredient Ke Item Edit
    $('#ProsesTambahIngridientEdit').submit(function (e) {
        e.preventDefault();

        const dataForm = $('#ProsesTambahIngridientEdit').serialize();

        $.ajax({
            type: 'POST',
            url: '_Page/Resep/ProsesTambahIngridient.php',
            dataType: 'JSON',
            data: dataForm,
            success: function (response) {
                if (response.status !== 'success') {
                    $('#NotifikasiTambahIngridientEdit').html(
                        '<div class="alert alert-danger">' +
                        '<small>' +
                        response.message +
                        '</small>' +
                        '</div>'
                    );
                    return;
                }

                const payload = response.payload;

                if ($('#table_list_ingridient_edit tr td').length === 1) {
                    $('#table_list_ingridient_edit').empty();
                }

                const no = $('#table_list_ingridient_edit tr').length + 1;

                let numerator = '-';
                if (payload.jumlah_numerator !== '') {
                    numerator = payload.jumlah_numerator + ' ' + payload.nama_numerator;
                }

                let denominator = '-';
                if (payload.jumlah_denominator !== '') {
                    denominator = payload.jumlah_denominator + ' ' + payload.nama_denominator;
                }

                $('#table_list_ingridient_edit').append(`
                    <tr>
                        <td class="text-center">
                            <small>${no}</small>
                        </td>
                        <td>
                            <small>${payload.kode_kfa}</small>
                        </td>
                        <td>
                            <small>${payload.nama_kfa}</small>
                        </td>
                        <td class="text-center">
                            <small>${numerator}</small>
                        </td>
                        <td class="text-center">
                            <small>${denominator}</small>
                        </td>
                        <td class="text-center">
                            <button
                                type="button"
                                class="btn btn-danger btn-sm btn-hapus-ingridient-edit"
                            >
                                <i class="bi bi-trash"></i>
                            </button>
                            <input
                                type="hidden"
                                name="payload_ingridient[]"
                                value='${JSON.stringify(payload)}'
                            >
                        </td>
                    </tr>
                `);

                $('#ProsesTambahIngridientEdit')[0].reset();

                $('#ingridient_kfa, #satuan_numerator, #satuan_denominator')
                    .val(null)
                    .trigger('change');

                $('#ModalTambahIngridientEdit').modal('hide');
            }
        });
    });

    // Hapus Ingredient Edit
    $(document).on('click', '.btn-hapus-ingridient-edit', function () {
        $(this).closest('tr').remove();

        $('#table_list_ingridient_edit tr').each(function (index) {
            $(this).find('td:first small').text(index + 1);
        });

        if ($('#table_list_ingridient_edit tr').length === 0) {
            $('#table_list_ingridient_edit').html(`
                <tr>
                    <td colspan="6" class="text-center">
                        <small>No Data</small>
                    </td>
                </tr>
            `);
        }
    });

    // Proses Edit Item Resep
    $('#ProsesEditItemResep').submit(function (e) {
        e.preventDefault();

        var ProsesEditItemResep = $('#ProsesEditItemResep').serialize();
        var TombolEditItemResep = $('#TombolEditItemResep').html();

        $('#TombolEditItemResep')
            .html('...')
            .prop('disabled', true);

        $('#NotifikasiEditItemResep').html('');

        $.ajax({
            type: 'POST',
            url: '_Page/Resep/ProsesEditItemResep.php',
            dataType: 'JSON',
            data: ProsesEditItemResep,
            success: function (response) {
                if (response.status === 'success') {
                    $('#ModalEditItemResep').modal('hide');

                    ShowDetailResep();
                    ShowResepObat(0);

                    showToast(
                        'success',
                        'Berhasil',
                        response.message || 'Data berhasil diperbarui.'
                    );
                } else {
                    $('#NotifikasiEditItemResep').html(`
                        <div class="alert alert-danger mt-3 mb-3">
                            <small>
                                ${response.message}
                            </small>
                        </div>
                    `);
                }
            },
            error: function (xhr, status, error) {
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                $('#NotifikasiEditItemResep').html(`
                    <div class="alert alert-danger mt-3 mb-3">
                        <small>
                            Terjadi kesalahan server.
                        </small>
                    </div>
                `);
            },
            complete: function () {
                $('#TombolEditItemResep')
                    .prop('disabled', false)
                    .html(TombolEditItemResep);
            }
        });
    });

    // --------------------------------------------
    // CETAK ETIKET
    // --------------------------------------------

    // Buka Modal Cetak Etiket
    $(document).on('click', '.modal_cetak_item_resep', function () {

        //---------------------------------------
        // Tangkap MedicationRequestId
        const MedicationRequestId = $(this).data('id');

        //---------------------------------------
        // Buka Modal
        $('#ModalCetakItemResep').modal('show');

        //---------------------------------------
        // Reset
        $('#NotifikasiCetakItemResep').html('');

        $('#FormCetakItemResep').html(`
            <div class="text-center p-4">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <br>
                <small class="text-muted">
                    Menyiapkan preview etiket...
                </small>
            </div>
        `);

        //---------------------------------------
        // Disable Button
        $('#TombolCetakItemResep').prop('disabled', true);

        //---------------------------------------
        // Ambil Preview
        $.ajax({
            type    : 'POST',
            url     : '_Page/Resep/FormCetakItemResep.php',
            dataType: 'JSON',
            data    : {MedicationRequestId: MedicationRequestId},
            success : function (response) {

                if (response.status === 'success') {

                    //---------------------------------------
                    // Tampilkan Preview
                    $('#FormCetakItemResep').html(
                        response.html
                    );

                    //---------------------------------------
                    // Simpan ID Pada Form
                    $('#ProsesCetakItemResep').data(
                        'id',
                        MedicationRequestId
                    );

                    //---------------------------------------
                    // Enable Button
                    $('#TombolCetakItemResep')
                        .prop('disabled', false);

                } else {

                    $('#FormCetakItemResep').html(`
                        <div class="alert alert-danger text-center">
                            <small>${response.message}</small>
                        </div>
                    `);
                }
            },

            error: function (xhr, status, error) {

                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                $('#FormCetakItemResep').html(`
                    <div class="alert alert-danger text-center">
                        <small>
                            Terjadi kesalahan saat menyiapkan etiket.
                        </small>
                    </div>
                `);
            }
        });
    });


    // --------------------------------------------
    // PROSES PRINT ETIKET
    $('#ProsesCetakItemResep').submit(function (e) {

        e.preventDefault();

        //---------------------------------------
        // Ambil ID
        const MedicationRequestId =$('#ProsesCetakItemResep').data('id');
        if (!MedicationRequestId) {return;}

        //---------------------------------------
        // Buat Form Temporary
        const form = $('<form>', {
            method: 'POST',
            action: '_Page/Resep/ProsesCetakItemResep.php',
            target: '_blank'
        });

        //---------------------------------------
        // Masukkan ID
        form.append(
            $('<input>', {
                type: 'hidden',
                name: 'MedicationRequestId',
                value: MedicationRequestId
            })
        );

        //---------------------------------------
        // Tempel Ke Body
        $('body').append(form);

        //---------------------------------------
        // Submit
        form.trigger('submit');

        //---------------------------------------
        // Hapus Form Temporary
        form.remove();
    });

    // --------------------------------------------
    // HAPUS ITEM RESEP
    // --------------------------------------------

    // Ketika Modal Muncul
    $(document).on('click', '.modal_hapus_item_resep', function () {

        // Tangkap 'id_medication_request_group'
        var MedicationRequestId= $(this).data('id');

        // Buka Modal
        $('#ModalHapusItemResep').modal("show");


        // Kosongkan Notifikasi
        $('#NotifikasiHapusItemResep').html("");

        // Loading Form
        $('#FormHapusItemResep').html("Loading...");

        // Disable Button
        $('#TombolHapusItemResep').prop('disabled', true);

        // Ambil Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Resep/FormHapusItemResep.php',
            data        : {MedicationRequestId: MedicationRequestId},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;

                // Apabila status success
                if(status=='success'){

                    // Tangkap HTML dan Tempelkan Ke Form
                    var html = response.html;
                    $('#FormHapusItemResep').html(html);

                    // Enamble Tombol
                    $('#TombolHapusItemResep').prop('disabled', false);
                }else{
                    $('#FormHapusItemResep').html(`
                        <div class="alert alert-danger text-center">
                            <small>
                                <b>Ops!!</b><br>
                                Terjadi Kesalahan : ${message}
                            </small>
                        </div>
                    `);

                    // Disable Tombol
                    $('#TombolHapusItemResep').prop('disabled', true);
                }
            },
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#FormHapusItemResep').html(`
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Ops!!</b><br>
                            Terjadi Kesalahan Pada Sistem.
                        </small>
                    </div>
                `);
                // Disable Tombol
                $('#TombolHapusItemResep').prop('disabled', true);
            },

        });
    });

    // Proses Hapus Item Resep
    $('#ProsesHapusItemResep').submit(function(){
        
        // Tangkap Data
        var ProsesHapusItemResep = $('#ProsesHapusItemResep').serialize();

        // Tombol
        var TombolHapusItemResep = $('#TombolHapusItemResep').html();

        // Loading Tombol
        $('#TombolHapusItemResep').html('...');

        // Clear Notifikasi Text
        $('#NotifikasiHapusItemResep').html("");

        // Disable tombol
        $('#TombolHapusItemResep').prop('disabled', true);

        // Insert Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Resep/ProsesHapusItemResep.php',
            dataType    : 'JSON',
            data 	    :  ProsesHapusItemResep,
            success     : function(response){

                // Status & message
                let status = response.status;
                let message = response.message;

                // Jika Berhasil
                if(status=='success'){
                    //Tutup modal
                    $('#ModalHapusItemResep').modal('hide');

                    // Kosongkan Notifikasi
                    $('#NotifikasiHapusItemResep').html('');

                    // Reload Data
                    ShowDetailResep();
                    ShowResepObat(0);

                    // Scroll ke atas
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil dihapus.'
                    );
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiHapusItemResep').html('<div class="alert alert-danger mt-3 mb-3"><small>'+message+'</small></div>');
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
                $('#NotifikasiHapusItemResep').html(`<div class="alert alert-danger mt-3 mb-3">Terjadi kesalahan server.</div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#TombolHapusItemResep').prop('disabled', false);
                $('#TombolHapusItemResep').html(TombolHapusItemResep);
            }
        });
    });

    // --------------------------------------------
    // HAPUS ITEM RESEP
    // --------------------------------------------

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
            url 	    : '_Page/Resep/FormDetailMedication.php',
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



});

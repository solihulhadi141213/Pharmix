// =====================================
// FUNCTION
// =====================================

// MENAMPILKAN TABEL KUNJUNGAN KOSONG
function showEmptyKunjungan(message) {
    $('#tabel_kunjungan').html(`
        <tr class="table-empty">
            <td colspan="9" class="text-center">
                ${message}
            </td>
        </tr>
    `);

    $('#page').val(1);
    $('#page_info').text('Page 1 Of 1');
    $('#prev_button, #next_button').prop('disabled', true);
}

// MENAMPILKAN DATA KUNJUNGAN
function ShowData() {
    const target = $('#tabel_kunjungan');
    const data   = $('#ProsesFilter').serializeArray();

    data.push({
        name : 'page',
        value: parseInt($('#page').val(), 10) || 1
    });

    $.ajax({
        type    : 'POST',
        url     : '_Page/Kunjungan/TabelKunjungan.php',
        data    : data,
        dataType: 'json',

        beforeSend: function() {
            tableLoading('#TabelKunjunganHeader', true);
        },

        success: function(res) {
            if (res.status === 'success') {
                target.html(res.html);

                // Inisialisasi responsive card pada table
                initResponsiveTable('#TabelKunjunganHeader');

                // Sinkronkan halaman dari response
                $('#page').val(res.page);

                // Informasi pagination
                $('#page_info').text(
                    'Page ' + res.page + ' Of ' + res.total_page
                );

                // Tombol pagination
                $('#prev_button').prop('disabled', res.page <= 1);

                $('#next_button').prop(
                    'disabled',
                    res.total_page <= 0 || res.page >= res.total_page
                );

                return;
            }

            showEmptyKunjungan(
                res.html || 'Tidak ada data kunjungan.'
            );
        },

        error: function(xhr) {
            showEmptyKunjungan(
                '<small class="text-danger">' +
                    'Terjadi kesalahan pada sistem atau data tidak valid.' +
                '</small>'
            );

            console.error(xhr.responseText);
        },

        complete: function() {
            tableLoading('#TabelKunjunganHeader', false);
        }
    });
}

// MNEMAPILKAN DETAIL KUNJUNGAN
function ShowDetailKunjungan(id_kunjungan) {
    $('#detail_view').html('Loading...');
    $.ajax({
        type: 'POST',
        url: '_Page/Kunjungan/_DetailKunjungan.php',
        data: {id_kunjungan: id_kunjungan},
        success: function(data) {
            $('#detail_view').html(data);
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            $('#detail_view').html('<div class="alert alert-danger"><small>Terjadi kesalahan saat membuka detail pasien.</small></div>');
        }
    });
}

// MNEMAPILKAN ATTACHMENT
function ShowAttachment(document,id) {
    // Loading Page
    $('#attchment_view').html(`
        <div class="alert alert-info text-center">
            <small>
                Loading...
            </small>
        </div>
    `);

    // Kirim Parameter Melalui AJAX
    $.ajax({
        type: 'POST',
        url: '_Page/Kunjungan/AttachmentView.php',
        data: { document: document, id: id },
        success: function (response) {
            $('#attchment_view').html(response);
        }
    });
}

// =====================================
// EVENT
// =====================================
$(document).ready(function() {

    // Menampilkan Data Pertama kali
    $('#table_view').show();
    $('#detail_view').hide();
    ShowData();

    $('#ModalFilter').on('shown.bs.modal', function () {
        $('#keyword').trigger('focus');
    });

    $('#keyword_by').change(function() {
        var keyword_by = $('#keyword_by').val();
        $.ajax({
            type: 'POST',
            url: '_Page/Kunjungan/FormFilter.php',
            data: {keyword_by: keyword_by},
            success: function(data) {
                $('#FormFilter').html(data);
            }
        });
    });

    $('#ProsesFilter').submit(function() {
        $('#page').val(1);
        ShowData();
        $('#ModalFilter').modal('hide');
    });

    $(document).on('click', '#next_button', function() {
        var page_now = parseInt($('#page').val(), 10);
        $('#page').val(page_now + 1);
        ShowData(0);
        scrollToTop();
    });

    $(document).on('click', '#prev_button', function() {
        var page_now = parseInt($('#page').val(), 10);
        $('#page').val(page_now - 1);
        ShowData(0);
        scrollToTop();
    });

    // --------------------------------
    // TAMBAH
    // --------------------------------
    $(document).ready(function () {
        function initPasienSelect() {
            const $select = $('#id_anggota');
            if ($select.hasClass('select2-hidden-accessible')) {
                return;
            }
            $select.select2({
                dropdownParent: $('#ModalTambah'),
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Cari Pasien (Nama atau RM)...',
                allowClear: true,
                ajax: {
                    url: '_Page/Kunjungan/ProsesSelectPasien.php',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            keyword: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results || [],
                            pagination: {
                                more: data.pagination ? data.pagination.more : false
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0
            });
        }

        function initMedicalPersonelSelect(dropdownParent, selectorId, hiddenCodeId, hiddenNameId) {
            const $select = $(selectorId);
            if ($select.hasClass('select2-hidden-accessible')) {
                return;
            }
            $select.select2({
                dropdownParent: $(dropdownParent),
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Cari Dokter (Kode atau Nama)...',
                allowClear: true,
                ajax: {
                    url: '_Page/Kunjungan/ProsesSelectMedicalPersonel.php',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            keyword: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results || [],
                            pagination: {
                                more: data.pagination ? data.pagination.more : false
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                templateResult: formatMedicalPersonel,
                templateSelection: formatMedicalPersonelSelection
            });

            $select.on('select2:select', function (e) {
                const data = e.params.data;
                $(hiddenCodeId).val(data.code || '');
                $(hiddenNameId).val(data.name || '');
            });

            $select.on('select2:clear', function () {
                $(hiddenCodeId).val('');
                $(hiddenNameId).val('');
            });
        }

        function formatMedicalPersonel(personel) {
            if (personel.loading) {
                return personel.text;
            }
            const $container = $(
                "<div class='select2-result-personel'>" +
                    "<div class='fw-bold personel-code'></div>" +
                    "<div class='personel-name'></div>" +
                    "<div><small class='text-muted personel-category'></small></div>" +
                "</div>"
            );
            $container.find('.personel-code').text(personel.code || '');
            $container.find('.personel-name').text(personel.name || '');
            $container.find('.personel-category').text(personel.category || '');
            return $container;
        }

        function formatMedicalPersonelSelection(personel) {
            if (!personel.id) {
                return personel.text || '';
            }
            if (personel.code && personel.name) {
                return personel.code + ' - ' + personel.name;
            }
            return personel.text || '';
        }

        function initPolyclinicSelect() {
            const $select = $('#id_poli');
            if ($select.hasClass('select2-hidden-accessible')) {
                return;
            }
            $select.select2({
                dropdownParent: $('#FormPoli'),
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Cari Poliklinik (Kode atau Nama)...',
                allowClear: true,
                ajax: {
                    url: '_Page/Kunjungan/ProsesSelectPolyclinic.php',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            keyword: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results || [],
                            pagination: {
                                more: data.pagination ? data.pagination.more : false
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                templateResult: formatPolyclinic,
                templateSelection: formatPolyclinicSelection
            });

            $select.on('select2:select', function (e) {
                const data = e.params.data;
                $('#kode_poli').val(data.code || '');
                $('#nama_poli').val(data.name || '');
            });

            $select.on('select2:clear', function () {
                $('#kode_poli').val('');
                $('#nama_poli').val('');
            });
        }

        function formatPolyclinic(poli) {
            if (poli.loading) {
                return poli.text;
            }
            const $container = $(
                "<div class='select2-result-poli'>" +
                    "<div class='fw-bold poli-code'></div>" +
                    "<div class='poli-name'></div>" +
                "</div>"
            );
            $container.find('.poli-code').text(poli.code || '');
            $container.find('.poli-name').text(poli.name || '');
            return $container;
        }

        function formatPolyclinicSelection(poli) {
            if (!poli.id) {
                return poli.text || '';
            }
            if (poli.code && poli.name) {
                return poli.code + ' - ' + poli.name;
            }
            return poli.text || '';
        }

        initPasienSelect();
        initMedicalPersonelSelect('#DokterPenerima', '#id_dokter_penerima', '#kode_dokter_penerima', '#nama_dokter_penerima');
        initMedicalPersonelSelect('#DokterDpjp', '#id_dpjp', '#kode_dpjp', '#nama_dpjp');
        initPolyclinicSelect();

        function resetFormTambah() {
            const form = $('#ProsesTambah')[0];
            if (form) {
                form.reset();
            }
            $('#id_anggota').val(null).trigger('change');
            $('#id_dokter_penerima').val(null).trigger('change');
            $('#id_dpjp').val(null).trigger('change');
            $('#id_poli').val(null).trigger('change');
            $('#kode_dokter_penerima').val('');
            $('#nama_dokter_penerima').val('');
            $('#kode_dpjp').val('');
            $('#nama_dpjp').val('');
            $('#kode_poli').val('');
            $('#nama_poli').val('');
            $('#NotifikasiTambah').html('');
        }

        $('#ProsesTambah').on('submit', function (e) {
            e.preventDefault();
            const $form = $(this);
            const $button = $('#TombolTambah');

            $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');
            $('#NotifikasiTambah').html('');

            $.ajax({
                type: 'POST',
                url: '_Page/Kunjungan/ProsesTambah.php',
                dataType: 'json',
                data: $form.serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        resetFormTambah();
                        $('#ModalTambah').modal('hide');
                        $('#page').val('1');
                        if ($('#ProsesFilter').length) {
                            const filterForm = $('#ProsesFilter')[0];
                            if (filterForm) {
                                filterForm.reset();
                            }
                        }
                        if (typeof ShowData === 'function') {
                            ShowData();
                        }
                        if (typeof showToast === 'function') {
                            showToast('success', 'Berhasil', response.message || 'Data berhasil disimpan.');
                        }
                    } else {
                        $('#NotifikasiTambah').html('<div class="alert alert-danger"><small>' + (response.message || 'Terjadi kesalahan.') + '</small></div>');
                    }
                },
                error: function (xhr, status, error) {
                    console.log('Status:', status);
                    console.log('Error:', error);
                    console.log('Response:', xhr.responseText);
                    $('#NotifikasiTambah').html('<div class="alert alert-danger"><small>Terjadi kesalahan sistem. Silakan coba kembali.</small></div>');
                },
                complete: function () {
                    $button.prop('disabled', false).html('<i class="bi bi-save"></i> Simpan');
                }
            });
        });

        $('#ModalTambah').on('hidden.bs.modal', function () {
            $('#NotifikasiTambah').html('');
        });
    });

    // --------------------------------
    // DETAIL
    // --------------------------------
    $('#ModalDetail').on('show.bs.modal', function (e) {
        var id_kunjungan = $(e.relatedTarget).data('id');
        $('#FormDetail').html("Loading...");
        $('#TombolSelengkapnya').prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: '_Page/Kunjungan/FormDetail.php',
            data: {id_kunjungan: id_kunjungan},
            dataType: 'JSON',
            success: function(response) {
                var status = response.status;
                var message = response.message;
                if (status == 'success') {
                    $('#FormDetail').html(response.html);
                    $('#TombolSelengkapnya').prop('disabled', false);
                } else {
                    $('#FormDetail').html('<div class="alert alert-danger text-center"><small><b>Opss!</b><br>' + message + '</small></div>');
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                $('#FormDetail').html('<div class="alert alert-danger text-center"><small><b>Opss!</b><br>Terjadi kesalahan sistem</small></div>');
            }
        });
    });

    // --------------------------------
    // EXPORT
    // --------------------------------
    $('#ModalExport').on('show.bs.modal', function (e) {

        // Form Export Loading
        $('#FormExport').html("Loading...");
        $('#TombolExport').prop('disabled', true);

        $.ajax({
            type    : 'POST',
            url     : '_Page/Kunjungan/FormExport.php',
            dataType: 'JSON',
            success : function(response) {
                var status = response.status;
                var message = response.message;
                if (status == 'success') {
                    $('#FormExport').html(response.html);
                    $('#TombolExport').prop('disabled', false);
                } else {
                    $('#FormExport').html('<div class="alert alert-danger text-center"><small><b>Opss!</b><br>' + message + '</small></div>');
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                $('#FormExport').html('<div class="alert alert-danger text-center"><small><b>Opss!</b><br>Terjadi kesalahan sistem</small></div>');
            }
        });
    });

    $(document).on('submit', '#ProsesDetail', function (e) {
        e.preventDefault();
        var id_kunjungan = $('#id_kunjungan').val();
        const modalElement = document.getElementById('ModalDetail');
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        if (modalInstance) {
            modalInstance.hide();
        }
        $('#table_view').hide();
        $('#detail_view').show();
        ShowDetailKunjungan(id_kunjungan);
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
            top     : 0,
            behavior: 'smooth'
        });
    });

    // --------------------------------
    // EDIT
    // --------------------------------
    $(document).ready(function () {
        function formatMedicalPersonel(data) {
            if (data.loading) {
                return data.text;
            }
            const code = data.code || '';
            const name = data.name || data.text || '';
            if (code && name) {
                return $('<span><b>' + code + '</b> - ' + name + '</span>');
            }
            return $('<span>' + name + '</span>');
        }

        function formatMedicalPersonelSelection(data) {
            if (!data) {
                return '';
            }
            let code = data.code || '';
            let name = data.name || '';
            if (data.element) {
                code = code || $(data.element).data('code') || '';
                name = name || $(data.element).data('name') || '';
            }
            if (code && name) {
                return code + ' - ' + name;
            }
            return data.text || '';
        }

        function formatPolyclinic(data) {
            if (data.loading) {
                return data.text;
            }
            const code = data.code || '';
            const name = data.name || data.text || '';
            if (code && name) {
                return $('<span><b>' + code + '</b> - ' + name + '</span>');
            }
            return $('<span>' + name + '</span>');
        }

        function formatPolyclinicSelection(data) {
            if (!data) {
                return '';
            }
            let code = data.code || '';
            let name = data.name || '';
            if (data.element) {
                code = code || $(data.element).data('code') || '';
                name = name || $(data.element).data('name') || '';
            }
            if (code && name) {
                return code + ' - ' + name;
            }
            return data.text || '';
        }

        function initPatientSelectForEdit() {
            const $select = $('#edit_id_anggota');
            if (!$select.length) {
                return;
            }
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.select2({
                dropdownParent: $('#ModalEdit'),
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Cari Pasien (Nama atau RM)...',
                allowClear: true,
                ajax: {
                    url: '_Page/Kunjungan/ProsesSelectPasien.php',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            keyword: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results || [],
                            pagination: {
                                more: data.pagination ? Boolean(data.pagination.more) : false
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0
            });
        }

        function initMedicalPersonelSelectForEdit(dropdownParent, selectorId, hiddenCodeId, hiddenNameId) {
            const $select = $(selectorId);
            if (!$select.length) {
                return;
            }
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.select2({
                dropdownParent: $(dropdownParent),
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Cari Dokter (Kode atau Nama)...',
                allowClear: true,
                ajax: {
                    url: '_Page/Kunjungan/ProsesSelectMedicalPersonel.php',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            keyword: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results || [],
                            pagination: {
                                more: data.pagination ? Boolean(data.pagination.more) : false
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                templateResult: formatMedicalPersonel,
                templateSelection: formatMedicalPersonelSelection
            });

            $select.off('select2:select.edit').on('select2:select.edit', function (e) {
                const data = e.params.data || {};
                $(hiddenCodeId).val(data.code || '');
                $(hiddenNameId).val(data.name || data.text || '');
            });

            $select.off('select2:clear.edit').on('select2:clear.edit', function () {
                $(hiddenCodeId).val('');
                $(hiddenNameId).val('');
            });
        }

        function initPolyclinicSelectForEdit() {
            const $select = $('#edit_id_poli');
            if (!$select.length) {
                return;
            }
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.select2({
                dropdownParent: $('#EditFormPoli'),
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Cari Poliklinik (Kode atau Nama)...',
                allowClear: true,
                ajax: {
                    url: '_Page/Kunjungan/ProsesSelectPolyclinic.php',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            keyword: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results || [],
                            pagination: {
                                more: data.pagination ? Boolean(data.pagination.more) : false
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                templateResult: formatPolyclinic,
                templateSelection: formatPolyclinicSelection
            });

            $select.off('select2:select.edit').on('select2:select.edit', function (e) {
                const data = e.params.data || {};
                $('#edit_kode_poli').val(data.code || '');
                $('#edit_nama_poli').val(data.name || data.text || '');
            });

            $select.off('select2:clear.edit').on('select2:clear.edit', function () {
                $('#edit_kode_poli').val('');
                $('#edit_nama_poli').val('');
            });
        }
        
        $('#ModalEdit').on('show.bs.modal', function (e) {
            const button = $(e.relatedTarget);
            const id_kunjungan = button.data('id');
            $('#FormEdit').html(`
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2">Memuat data...</span>
                </div>
            `);
            $('#NotifikasiEdit').html('');
            $('#TombolEdit').prop('disabled', true).html('<i class="bi bi-save"></i> Simpan');

            if (!id_kunjungan) {
                $('#FormEdit').html('<div class="alert alert-danger text-center"><small>ID Kunjungan tidak valid.</small></div>');
                return;
            }

            $.ajax({
                type: 'POST',
                url: '_Page/Kunjungan/FormEdit.php',
                data: {id_kunjungan: id_kunjungan},
                dataType: 'json',
                success: function (response) {
                    if (response.status !== 'success') {
                        $('#FormEdit').html('<div class="alert alert-danger text-center"><small><b>Opss!</b><br>' + (response.message || 'Gagal memuat data.') + '</small></div>');
                        return;
                    }
                    $('#FormEdit').html(response.html || '');
                    initPatientSelectForEdit();
                    initMedicalPersonelSelectForEdit('#EditDokterPenerima', '#edit_id_dokter_penerima', '#edit_kode_dokter_penerima', '#edit_nama_dokter_penerima');
                    initMedicalPersonelSelectForEdit('#EditDokterDpjp', '#edit_id_dpjp', '#edit_kode_dpjp', '#edit_nama_dpjp');
                    initPolyclinicSelectForEdit();
                    $('#TombolEdit').prop('disabled', false);
                },
                error: function (xhr, status, error) {
                    console.log('AJAX ERROR FormEdit', xhr.responseText, status, error);
                    $('#FormEdit').html('<div class="alert alert-danger text-center"><small><b>Opss!</b><br>Terjadi kesalahan saat memuat data.</small></div>');
                }
            });
        });

        $('#ProsesEdit').on('submit', function (e) {
            e.preventDefault();
            const $form = $(this);
            const $button = $('#TombolEdit');
            if ($button.prop('disabled')) {
                return;
            }
            const formData = $form.serialize();
            $('#NotifikasiEdit').html('');
            $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span>Menyimpan...');

            $.ajax({
                type: 'POST',
                url: '_Page/Kunjungan/ProsesEdit.php',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $('#ModalEdit').modal('hide');
                        if (typeof ShowData === 'function') {
                            ShowData();
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message || 'Data kunjungan berhasil diperbarui.',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        $('#NotifikasiEdit').html('<div class="alert alert-danger"><small>' + (response.message || 'Data gagal disimpan.') + '</small></div>');
                    }
                },
                error: function (xhr, status, error) {
                    console.log('AJAX ERROR ProsesEdit', xhr.responseText, status, error);
                    let message = 'Terjadi kesalahan sistem.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            message = response.message;
                        }
                    } catch (e) {}
                    $('#NotifikasiEdit').html('<div class="alert alert-danger"><small>' + message + '</small></div>');
                },
                complete: function () {
                    $button.prop('disabled', false).html('<i class="bi bi-save"></i> Simpan');
                }
            });
        });

        $('#ModalEdit').on('hidden.bs.modal', function () {
            [
                '#edit_id_anggota',
                '#edit_id_dokter_penerima',
                '#edit_id_dpjp',
                '#edit_id_poli'
            ].forEach(function (selector) {
                const $element = $(selector);
                if ($element.length && $element.hasClass('select2-hidden-accessible')) {
                    $element.select2('destroy');
                }
            });
            $('#FormEdit').html('');
            $('#NotifikasiEdit').html('');
            $('#TombolEdit').prop('disabled', false).html('<i class="bi bi-save"></i> Simpan');
        });
    });

    // --------------------------------
    // DELETE
    // --------------------------------
    $('#ModalHapus').on('show.bs.modal', function (e) {
        const button = $(e.relatedTarget);
        const id_kunjungan = button.data('id');

        $('#FormHapus').html(`
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-danger" role="status"></div>
                <span class="ms-2">Memuat data...</span>
            </div>
        `);
        $('#NotifikasiHapus').html('');
        $('#TombolHapus').prop('disabled', true);

        if (!id_kunjungan) {
            $('#FormHapus').html('<div class="alert alert-danger text-center"><small>ID Kunjungan tidak valid.</small></div>');
            return;
        }

        $.ajax({
            type: 'POST',
            url: '_Page/Kunjungan/FormHapus.php',
            data: { id_kunjungan: id_kunjungan },
            dataType: 'json',
            success: function (response) {
                if (response.status !== 'success') {
                    $('#FormHapus').html('<div class="alert alert-danger text-center"><small><b>Opss!</b><br>' + (response.message || 'Gagal memuat data.') + '</small></div>');
                    return;
                }
                $('#FormHapus').html(response.html || '');
                $('#TombolHapus').prop('disabled', false);
            },
            error: function (xhr, status, error) {
                console.log('AJAX ERROR FormHapus', xhr.responseText, status, error);
                $('#FormHapus').html('<div class="alert alert-danger text-center"><small><b>Opss!</b><br>Terjadi kesalahan saat memuat data.</small></div>');
            }
        });
    });

    // PROSES SUBMIT HAPUS KUNJUNGAN
    $('#ProsesHapus').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $button = $('#TombolHapus');

        if ($button.prop('disabled')) {
            return;
        }

        const formData = $form.serialize();
        $('#NotifikasiHapus').html('');
        
        $button.prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
            Menghapus...
        `);

        $.ajax({
            type: 'POST',
            url: '_Page/Kunjungan/ProsesHapus.php',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#ModalHapus').modal('hide');

                    if (typeof ShowData === 'function') {
                        ShowData();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message || 'Data kunjungan berhasil dihapus.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    $('#NotifikasiHapus').html(`
                        <div class="alert alert-danger">
                            <small>${response.message || 'Data gagal dihapus.'}</small>
                        </div>
                    `);
                }
            },
            error: function (xhr, status, error) {
                console.log('AJAX ERROR ProsesHapus', xhr.responseText, status, error);
                let message = 'Terjadi kesalahan sistem.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        message = response.message;
                    }
                } catch (e) {}

                $('#NotifikasiHapus').html(`
                    <div class="alert alert-danger">
                        <small>${message}</small>
                    </div>
                `);
            },
            complete: function () {
                $button.prop('disabled', false).html('<i class="bi bi-check"></i> Ya, Hapus');
            }
        });
    });

    // SAAT MODAL HAPUS DITUTUP
    $('#ModalHapus').on('hidden.bs.modal', function () {
        $('#FormHapus').html('');
        $('#NotifikasiHapus').html('');
        $('#TombolHapus').prop('disabled', false).html('<i class="bi bi-check"></i> Ya, Hapus');
    });

    // ----------------------------------------
    // MODAL KIRIM ENCOUNTER: TAMPILKAN FORM
    // ----------------------------------------
    $('#ModalKirimEncounter').on('show.bs.modal', function (e) {
        const button = $(e.relatedTarget);
        const id_kunjungan = button.data('id');

        $('#FormKirimEncounter').html(`
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <span class="ms-2">Memuat data encounter...</span>
            </div>
        `);
        $('#NotifikasiKirimEncounter').html('');
        $('#TombolKirimEncounter').prop('disabled', true);

        if (!id_kunjungan) {
            $('#FormKirimEncounter').html('<div class="alert alert-danger text-center"><small>ID Kunjungan tidak valid.</small></div>');
            return;
        }

        $.ajax({
            type: 'POST',
            url: '_Page/Kunjungan/FormKirimEncounter.php',
            data: { id_kunjungan: id_kunjungan },
            dataType: 'json',
            success: function (response) {

                if (response.status !== 'success') {
                    $('#FormKirimEncounter').html(`
                        <div class="alert alert-danger text-center">
                            <small>
                                <b>Opss!</b><br>
                                ${response.message || 'Gagal memuat data.'}
                            </small>
                        </div>
                    `);
                    $('#TombolKirimEncounter').prop('disabled', true);
                    return;
                }

                // Tampilkan form
                $('#FormKirimEncounter').html(
                    response.html || ''
                );

                // Tombol kirim hanya aktif jika data valid
                $('#TombolKirimEncounter').prop(
                    'disabled',
                    response.valid !== true
                );
            },
            error: function (xhr, status, error) {
                console.log('AJAX ERROR FormKirimEncounter', xhr.responseText, status, error);
                $('#FormKirimEncounter').html('<div class="alert alert-danger text-center"><small><b>Opss!</b><br>Terjadi kesalahan saat memuat data.</small></div>');
            }
        });
    });

    // PROSES SUBMIT KIRIM ENCOUNTER
    $('#ProsesKirimEncounter').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $button = $('#TombolKirimEncounter');

        if ($button.prop('disabled')) {
            return;
        }

        const formData = $form.serialize();
        $('#NotifikasiKirimEncounter').html('');

        $button.prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
            Mengirim...
        `);

        $.ajax({
            type: 'POST',
            url: '_Page/Kunjungan/ProsesKirimEncounter.php',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#ModalKirimEncounter').modal('hide');

                    if (typeof ShowData === 'function') {
                        ShowData();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message || 'Encounter berhasil dikirim.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    $('#NotifikasiKirimEncounter').html(`
                        <div class="alert alert-danger">
                            <small>${response.message || 'Gagal mengirim encounter.'}</small>
                        </div>
                    `);
                }
            },
            error: function (xhr, status, error) {
                console.log('AJAX ERROR ProsesKirimEncounter', xhr.responseText, status, error);
                let message = 'Terjadi kesalahan sistem.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        message = response.message;
                    }
                } catch (e) {}

                $('#NotifikasiKirimEncounter').html(`
                    <div class="alert alert-danger">
                        <small>${message}</small>
                    </div>
                `);
            },
            complete: function () {
                $button.prop('disabled', false).html('<i class="bi bi-send"></i> Kirim');
            }
        });
    });

    // SAAT MODAL KIRIM ENCOUNTER DITUTUP
    $('#ModalKirimEncounter').on('hidden.bs.modal', function () {
        $('#FormKirimEncounter').html('');
        $('#NotifikasiKirimEncounter').html('');
        $('#TombolKirimEncounter').prop('disabled', false).html('<i class="bi bi-send"></i> Kirim');
    });
    
    // ----------------------------------------
    // MODAL DETAIL ENCOUNTER: TAMPILKAN FORM
    // ----------------------------------------
    // Modal Detail Encounter
    $('#ModalDetailEncounter').on('show.bs.modal', function (e) {
        const id_encounter = $(e.relatedTarget).data('id');

        $('#FormDetailEncounter').html(`
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <span class="ms-2">Memuat detail Encounter...</span>
            </div>
        `);

        if (!id_encounter) {
            $('#FormDetailEncounter').html(`
                <div class="alert alert-danger text-center">
                    <small>ID Encounter tidak valid.</small>
                </div>
            `);
            return;
        }

        $.ajax({
            type: 'POST',
            url: '_Page/Kunjungan/FormDetailEncounter.php',
            data: { id_encounter: id_encounter },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#FormDetailEncounter').html(response.html);
                } else {
                    $('#FormDetailEncounter').html(`
                        <div class="alert alert-danger text-center">
                            <small>${response.message || 'Gagal memuat Encounter.'}</small>
                        </div>
                    `);
                }
            },
            error: function (xhr) {
                console.log('AJAX ERROR DetailEncounter:', xhr.responseText);

                $('#FormDetailEncounter').html(`
                    <div class="alert alert-danger text-center">
                        <small>Terjadi kesalahan saat memuat data Encounter.</small>
                    </div>
                `);
            }
        });
    });

    // Reset Modal
    $('#ModalDetailEncounter').on('hidden.bs.modal', function () {
        $('#FormDetailEncounter').html('');
    });

    // ----------------------------------------
    // HANDDLE ATTACHMENT
    // ----------------------------------------
     $(document).on('click', '.sub_feature', function() {
        var document = $(this).data('doc');
        var id       = $(this).data('id');

        ShowAttachment(document,id);
        
    });

    // ----------------------------------------
    // CONDITION
    // ----------------------------------------

    // Modal Tambah Condition
    let conditionFormRequest = null;
    let conditionFormGeneration = 0;

    function initConditionSelect(selector, endpoint, placeholder, detailSelector, detailKey, modalSelector = '#ModalTambahCondition') {
        const $select = $(selector);
        $select.select2({
            dropdownParent: $(modalSelector),
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: placeholder,
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: '_Page/Kunjungan/' + endpoint,
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { keyword: params.term || '', page: params.page || 1 };
                },
                processResults: function (data) {
                    return { results: data.results || [], pagination: data.pagination || { more: false } };
                }
            }
        }).on('select2:select', function (event) {
            $(detailSelector).val(event.params.data[detailKey] || '');
        }).on('select2:clear', function () {
            $(detailSelector).val('');
        });
    }

    $('#ModalTambahCondition').on('hide.bs.modal', function () {
        conditionFormGeneration++;
        if (conditionFormRequest) {
            conditionFormRequest.abort();
            conditionFormRequest = null;
        }
        $(this).find('.select2-hidden-accessible').select2('destroy');
    });

    $('#ModalTambahCondition').on('show.bs.modal', function (e) {
        const generation = ++conditionFormGeneration;

        // Tangkap id_kunjungan dan category
        var id_kunjungan = $(e.relatedTarget).data('id');
        var category     = $(e.relatedTarget).data('category');

        // Kosongkan Notifikasi
        $('#NotifikasiTambahCondition').html('');

        // Loading Form
        $('#FormTambahCondition').html('Loading...');

        // Disable Button
        $('#TombolTambahCondition').prop('disabled', true);

        // Tampilkan Form Dengan AJAX
        conditionFormRequest = $.ajax({
            type    : 'POST',
            url     : '_Page/Condition/FormTambahCondition.php',
            data    : { id_kunjungan: id_kunjungan, category: category },
            dataType: 'json',
            success: function (response) {
                if (generation !== conditionFormGeneration) return;
                if (response.status === 'success') {

                    // Tampilkan Form
                    $('#FormTambahCondition').html(response.html);
                    initConditionSelect('#condition_medical_personel', 'ProsesSelectConditionMedicalPersonel.php', 'Cari tenaga medis...', '#condition_medical_name', 'name');
                    initConditionSelect('#condition_icd_code', 'ProsesSelectConditionIcd.php', 'Cari kode atau deskripsi ICD10...', '#condition_icd_description', 'description');

                    // Enable Button
                    $('#TombolTambahCondition').prop('disabled', false);
                } else {
                    $('#FormTambahCondition').html(`
                        <div class="alert alert-danger text-center">
                            <small>${$('<div>').text(response.message || 'Gagal memuat form.').html()}</small>
                        </div>
                    `);
                }
            },
            error: function (xhr, status) {
                if (status === 'abort' || generation !== conditionFormGeneration) return;
                console.log('AJAX ERROR :', xhr.responseText);

                $('#FormTambahCondition').html(`
                    <div class="alert alert-danger text-center">
                        <small>Terjadi kesalahan saat memuat Form.</small>
                    </div>
                `);
            }
        });
    });

    // HAPUS CONDITION
    let hapusConditionRequest = null;
    let hapusConditionGeneration = 0;
    let hapusConditionBusy = false;
    let hapusConditionReady = false;
    const $hapusConditionModal = $('#ModalHapusCondition');
    const $hapusConditionButton = $('#TombolHapusCondition');
    const hapusConditionButtonHtml = $hapusConditionButton.html();

    function hapusConditionNotice(message) {
        $('#NotifikasiHapusCondition').empty().append($('<div>').addClass('alert alert-danger').text(message));
    }
    $hapusConditionModal.on('show.bs.modal', function (event) {
        const generation = ++hapusConditionGeneration;
        hapusConditionReady = false;
        $hapusConditionButton.prop('disabled', true).html(hapusConditionButtonHtml);
        $('#NotifikasiHapusCondition').empty();
        $('#FormHapusCondition').text('Memuat konfirmasi hapus diagnosis...');
        if (hapusConditionRequest) hapusConditionRequest.abort();
        hapusConditionRequest = $.ajax({
            type: 'POST',
            url: '_Page/Condition/FormHapusCondition.php',
            data: { id_diagnosis: $(event.relatedTarget).data('id') },
            dataType: 'json',
            success: function (response) {
                if (generation !== hapusConditionGeneration) return;
                if (response.status !== 'success') {
                    $('#FormHapusCondition').empty();
                    hapusConditionNotice(response.message || 'Gagal memuat konfirmasi hapus.');
                    return;
                }
                $('#FormHapusCondition').html(response.html);
                hapusConditionReady = true;
                $hapusConditionButton.prop('disabled', false);
            },
            error: function (xhr, status) {
                if (status === 'abort' || generation !== hapusConditionGeneration) return;
                $('#FormHapusCondition').empty();
                hapusConditionNotice('Gagal memuat konfirmasi hapus. Silakan buka kembali modal.');
            }
        });
    }).on('hide.bs.modal', function (event) {
        if (hapusConditionBusy) { event.preventDefault(); return; }
        hapusConditionGeneration++;
        hapusConditionReady = false;
        $hapusConditionButton.prop('disabled', true);
        if (hapusConditionRequest) { hapusConditionRequest.abort(); hapusConditionRequest = null; }
    });

    $('#ProsesHapusCondition').on('submit', function (event) {
        event.preventDefault();
        if (hapusConditionBusy || !hapusConditionReady || $hapusConditionButton.prop('disabled')) return;
        const formData = $(this).serialize();
        hapusConditionBusy = true;
        $hapusConditionButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menghapus...');
        $hapusConditionModal.find('[data-bs-dismiss="modal"]').prop('disabled', true);
        $('#NotifikasiHapusCondition').empty();
        $.ajax({
            type: 'POST',
            url: '_Page/Condition/ProsesHapusCondition.php',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    hapusConditionBusy = false;
                    $hapusConditionModal.modal('hide');
                    showToast('success', 'Diagnosis Dihapus', response.message || 'Diagnosis berhasil dihapus.');
                    ShowAttachment('Condition', response.id_kunjungan);
                } else {
                    hapusConditionNotice(response.message || 'Diagnosis gagal dihapus.');
                }
            },
            error: function (xhr) {
                hapusConditionNotice((xhr.responseJSON && xhr.responseJSON.message) || 'Respons penghapusan tidak dapat dibaca. Periksa daftar diagnosis sebelum mencoba kembali.');
            },
            complete: function () {
                hapusConditionBusy = false;
                $hapusConditionModal.find('[data-bs-dismiss="modal"]').prop('disabled', false);
                $hapusConditionButton.prop('disabled', !hapusConditionReady).html(hapusConditionButtonHtml);
            }
        });
    });

    // EDIT CONDITION
    let editConditionRequest = null;
    let editConditionGeneration = 0;
    let editConditionBusy = false;
    $('#ModalEditCondition').on('show.bs.modal', function (event) {
        const generation = ++editConditionGeneration;
        const idDiagnosis = $(event.relatedTarget).data('id');
        $('#NotifikasiEditCondition').empty();
        $('#FormEditCondition').text('Memuat form edit diagnosis...');
        $('#TombolEditCondition').prop('disabled', true);
        if (editConditionRequest) editConditionRequest.abort();
        editConditionRequest = $.ajax({
            type: 'POST',
            url: '_Page/Condition/FormEditCondition.php',
            data: { id_diagnosis: idDiagnosis },
            dataType: 'json',
            success: function (response) {
                if (generation !== editConditionGeneration) return;
                if (response.status !== 'success') {
                    $('#FormEditCondition').empty().append($('<div>').addClass('alert alert-danger').text(response.message || 'Gagal memuat form edit diagnosis.'));
                    return;
                }
                $('#FormEditCondition').html(response.html);
                initConditionSelect('#edit_condition_medical_personel', 'ProsesSelectConditionMedicalPersonel.php', 'Cari tenaga medis...', '#edit_condition_medical_name', 'name', '#ModalEditCondition');
                initConditionSelect('#edit_condition_icd_code', 'ProsesSelectConditionIcd.php', 'Cari kode atau deskripsi ICD10...', '#edit_condition_icd_description', 'description', '#ModalEditCondition');
                $('#TombolEditCondition').prop('disabled', false);
            },
            error: function (xhr, status) {
                if (status === 'abort' || generation !== editConditionGeneration) return;
                $('#FormEditCondition').empty().append($('<div>').addClass('alert alert-danger').text('Gagal memuat form edit diagnosis. Silakan buka kembali modal.'));
            }
        });
    }).on('hide.bs.modal', function (event) {
        if (editConditionBusy) {
            event.preventDefault();
            return;
        }
        editConditionGeneration++;
        if (editConditionRequest) {
            editConditionRequest.abort();
            editConditionRequest = null;
        }
        $(this).find('.select2-hidden-accessible').select2('destroy');
        $('#TombolEditCondition').prop('disabled', true);
    });

    // PROSES EDIT CONDITION
    $('#ProsesEditCondition').on('submit', function (event) {
        event.preventDefault();
        const $form = $(this);
        const $button = $('#TombolEditCondition');
        if (editConditionBusy || $button.prop('disabled')) return;
        const formData = $form.serialize();
        const buttonHtml = $button.html();
        const $controls = $form.find(':input:enabled');
        let saved = false;
        editConditionBusy = true;
        $controls.prop('disabled', true);
        $button.html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyimpan...');
        $('#NotifikasiEditCondition').empty();
        $.ajax({
            type: 'POST',
            url: '_Page/Condition/ProsesEditCondition.php',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    saved = true;
                    editConditionBusy = false;
                    $('#ModalEditCondition').modal('hide');
                    const syncFailed = response.satusehat && response.satusehat.status === 'error';
                    showToast(syncFailed ? 'warning' : 'success', 'Diagnosis Diperbarui', response.message || 'Diagnosis berhasil diperbarui.');
                    ShowAttachment('Condition', response.id_kunjungan);
                } else {
                    $('#NotifikasiEditCondition').empty().append($('<div>').addClass('alert alert-danger').text(response.message || 'Diagnosis gagal diperbarui.'));
                }
            },
            error: function (xhr) {
                const message = (xhr.responseJSON && xhr.responseJSON.message) || 'Respons penyimpanan tidak dapat dibaca. Periksa data diagnosis sebelum mencoba kembali.';
                $('#NotifikasiEditCondition').empty().append($('<div>').addClass('alert alert-danger').text(message));
            },
            complete: function () {
                editConditionBusy = false;
                $controls.prop('disabled', false);
                $button.prop('disabled', saved).html(buttonHtml);
            }
        });
    });

    // PROSES SUBMIT CONDITION
    $('#ProsesTambahCondition').on('submit', function (e) {
        e.preventDefault();

        // Form & Button
        const $form = $(this);
        const $button = $('#TombolTambahCondition');
        if ($button.prop('disabled')) {
            return;
        }

        // Catch Data Form
        const formData = $form.serialize();
        $('#NotifikasiTambahCondition').html('');

        // Loading Button
        $button.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1" role="status"></span> Mengirim...`);

        // Submit Data Via AJAX
        $.ajax({
            type    : 'POST',
            url     : '_Page/Condition/ProsesTambahCondition.php',
            data    : formData,
            dataType: 'json',
            success : function (response) {
                if (response.status === 'success') {

                    // Jika Berhasil Tutup Modal
                    $('#ModalTambahCondition').modal('hide');

                    // Penyimpanan lokal berhasil meskipun pengiriman SATUSEHAT tertunda/gagal.
                    const syncStatus = response.satusehat ? response.satusehat.status : 'success';
                    showToast(
                        syncStatus === 'success' ? 'success' : (syncStatus === 'skipped' ? 'info' : 'warning'),
                        'Diagnosis Tersimpan',
                        response.message || 'Data berhasil disimpan.'
                    );

                    // Reload Data
                    ShowAttachment('Condition',response.id_kunjungan);

                } else {
                    $('#NotifikasiTambahCondition').html(`
                        <div class="alert alert-danger">
                            <small>${$('<div>').text(response.message || 'Gagal menyimpan diagnosis.').html()}</small>
                        </div>
                    `);
                }
            },
            error: function (xhr, status, error) {
                console.log('AJAX ERROR ProsesTambahCondition', xhr.responseText, status, error);
                let message = 'Terjadi kesalahan sistem.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        message = response.message;
                    }
                } catch (e) {}

                $('#NotifikasiTambahCondition').html(`
                    <div class="alert alert-danger">
                        <small>${$('<div>').text(message).html()}</small>
                    </div>
                `);
            },
            complete: function () {
                $button.prop('disabled', false).html('<i class="bi bi-save"></i> Simpan');
            }
        });
    });

    // Preview dan pengiriman diagnosis yang sudah tersimpan.
    let kirimConditionRequest = null;
    let kirimConditionGeneration = 0;
    let kirimConditionEligible = false;
    let kirimConditionBusy = false;
    const $kirimConditionModal = $('#ModalKirimCondition');
    const $kirimConditionButton = $('#TombolKirimCondition');
    const kirimConditionButtonHtml = $kirimConditionButton.html();

    function conditionSendNotice(message, type) {
        $('#NotifikasiKirimCondition').empty().append(
            $('<div>').addClass('alert alert-' + (type || 'danger')).text(message)
        );
    }

    $kirimConditionModal.on('show.bs.modal', function (event) {
        const generation = ++kirimConditionGeneration;
        const code = $(event.relatedTarget).attr('data-id') || '';
        kirimConditionEligible = false;
        $kirimConditionButton.prop('disabled', true).html(kirimConditionButtonHtml);
        $('#NotifikasiKirimCondition').empty();
        $('#FormKirimCondition').text('Memuat preview Condition...');
        if (kirimConditionRequest) kirimConditionRequest.abort();
        kirimConditionRequest = $.ajax({
            type: 'POST',
            url: '_Page/Condition/FormKirimCondition.php',
            data: { diagnosis_code: code },
            dataType: 'json',
            success: function (response) {
                if (generation !== kirimConditionGeneration) return;
                if (response.status !== 'success') {
                    $('#FormKirimCondition').empty();
                    conditionSendNotice(response.message || 'Gagal memuat preview Condition.');
                    return;
                }
                $('#FormKirimCondition').html(response.html);
                kirimConditionEligible = response.eligible === true;
                $kirimConditionButton.prop('disabled', !kirimConditionEligible);
            },
            error: function (xhr, status) {
                if (status === 'abort' || generation !== kirimConditionGeneration) return;
                $('#FormKirimCondition').empty();
                conditionSendNotice('Gagal memuat preview Condition. Silakan buka kembali modal.');
            }
        });
    });

    $kirimConditionModal.on('hide.bs.modal', function (event) {
        if (kirimConditionBusy) {
            event.preventDefault();
            return;
        }
        kirimConditionGeneration++;
        kirimConditionEligible = false;
        $kirimConditionButton.prop('disabled', true);
        if (kirimConditionRequest) {
            kirimConditionRequest.abort();
            kirimConditionRequest = null;
        }
    });

    $('#ProsesKirimCondition').on('submit', function (event) {
        event.preventDefault();
        if (kirimConditionBusy || !kirimConditionEligible || $kirimConditionButton.prop('disabled')) return;
        kirimConditionBusy = true;
        kirimConditionEligible = false;
        $kirimConditionButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Mengirim...');
        $kirimConditionModal.find('[data-bs-dismiss="modal"]').prop('disabled', true);
        $('#NotifikasiKirimCondition').empty();
        $.ajax({
            type: 'POST',
            url: '_Page/Condition/ProsesKirimCondition.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    kirimConditionBusy = false;
                    $kirimConditionModal.modal('hide');
                    showToast('success', 'Condition Terkirim', response.message || 'Condition berhasil dikirim ke SATUSEHAT.');
                    ShowAttachment('Condition', response.id_kunjungan);
                } else {
                    conditionSendNotice(response.message || 'Condition belum berhasil dikirim.', response.status === 'warning' ? 'warning' : 'danger');
                }
            },
            error: function (xhr) {
                conditionSendNotice((xhr.responseJSON && xhr.responseJSON.message) || 'Respons pengiriman tidak dapat dibaca. Periksa status Condition sebelum mengirim ulang.');
            },
            complete: function () {
                kirimConditionBusy = false;
                $kirimConditionModal.find('[data-bs-dismiss="modal"]').prop('disabled', false);
                // A new preview is required after a failed or ambiguous send.
                $kirimConditionButton.prop('disabled', true).html(kirimConditionButtonHtml);
            }
        });
    });

    // DETAIL CONDITION
    $('#ModalDetailCondition').on('show.bs.modal', function (e) {
        
        // Tangkap id_diagnosis 
        var id_diagnosis  = $(e.relatedTarget).data('id');

        // Loading Form
        $('#FormDetailCondition').html('Loading...');

        // Tampilkan Form Dengan AJAX
        $.ajax({
            type    : 'POST',
            url     : '_Page/Condition/FormDetailCondition.php',
            data    : { id_diagnosis: id_diagnosis },
            success: function (response) {
                $('#FormDetailCondition').html(response);
            }
        });
    });

    // DETAIL ID CONDITION
    $('#ModalDetailIdCondition').on('show.bs.modal', function (e) {
        const id_condition = $(e.relatedTarget).attr('data-id');
        const target = $('#FormDetailIdCondition');

        if (!id_condition) {
            target.html('<div class="alert alert-danger mb-0">ID Condition tidak tersedia.</div>');
            return;
        }

        target.html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Memuat...</span>
                </div>
                <div class="text-muted mt-2">Memuat data Condition...</div>
            </div>
        `);

        $.ajax({
            type: 'POST',
            url: '_Page/Condition/FormDetailIdCondition.php',
            data: { id_condition: id_condition },
            dataType: 'html',
            success: function (response) {
                target.html(response);
            },
            error: function () {
                target.html(`
                    <div class="alert alert-danger mb-0">
                        Gagal memuat detail Condition. Silakan coba kembali.
                    </div>
                `);
            }
        });
    });

    // DELETE CONDITION

});

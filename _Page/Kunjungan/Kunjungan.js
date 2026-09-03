// =====================================
// FUNCTION
// =====================================
function ShowData() {
    let target = $('#tabel_kunjungan');
    let data = $('#ProsesFilter').serialize() + '&page=' + $('#page').val();
    target.addClass('blur-loading');
    $.ajax({
        type: 'POST',
        url: '_Page/Kunjungan/TabelKunjungan.php',
        data: data,
        dataType: 'JSON',
        success: function(res) {
            if (res.status === "success") {
                target.fadeOut(150, function () {
                    target.html(res.html).fadeIn(150);
                });
                $('#page_info').html('Page ' + res.page + ' Of ' + res.total_page);
                $('#prev_button').prop('disabled', res.page <= 1);
                $('#next_button').prop('disabled', res.page >= res.total_page);
            } else {
                target.html(res.html);
                $('#prev_button').prop('disabled', true);
                $('#next_button').prop('disabled', true);
                $('#page_info').html('Page 1 Of 1');
            }
            target.removeClass('blur-loading');
        },
        error: function(xhr, status, error) {
            target.html('<tr><td colspan="9" class="text-center text-danger"><small>Terjadi kesalahan pada sistem atau data tidak valid.</small></td></tr>');
            $('#prev_button').prop('disabled', true);
            $('#next_button').prop('disabled', true);
            $('#page_info').html('Page 1 Of 1');
            target.removeClass('blur-loading');
        }
    });
}

function ShowDetailKunjungan(id_anggota) {
    $('#detail_view').html('Loading...');
    $.ajax({
        type: 'POST',
        url: '_Page/Pasien/_DetailPasien.php',
        data: {id_anggota: id_anggota},
        success: function(data) {
            $('#detail_view').html(data);
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            $('#detail_view').html('<div class="alert alert-danger"><small>Terjadi kesalahan saat membuka detail pasien.</small></div>');
        }
    });
}

// =====================================
// EVENT
// =====================================
$(document).ready(function() {
    $('#table_view').show();
    $('#detail_view').hide();
    ShowData();

    // --------------------------------
    // TABEL
    // --------------------------------
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
    });

    $(document).on('click', '#prev_button', function() {
        var page_now = parseInt($('#page').val(), 10);
        $('#page').val(page_now - 1);
        ShowData(0);
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
});
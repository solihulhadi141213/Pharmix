let resepKeyword = '';

function ShowResep(page = 1, keyword = resepKeyword) {
    resepKeyword = keyword;

    $('#list_resep').html(
        '<div class="col-xl-3 col-lg-4 col-md-6">' +
            '<div class="card h-100 resep-state-card">' +
                '<div class="card-body d-flex justify-content-center align-items-center text-center">' +
                    '<div><span class="spinner-border spinner-border-sm mb-2"></span><br>' +
                    '<small class="text-muted">Memuat data resep...</small></div>' +
                '</div>' +
            '</div>' +
        '</div>'
    );

    $.ajax({
        type: 'POST',
        url: '_Page/Resep/TabelResep.php',
        data: { page: page, keyword: resepKeyword },
        success: function(data) {
            $('#list_resep').html(data);
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            $('#list_resep').html(
                '<div class="col-xl-3 col-lg-4 col-md-6">' +
                    '<div class="card h-100 border-danger resep-state-card">' +
                        '<div class="card-body d-flex justify-content-center align-items-center text-center">' +
                            '<div><i class="bi bi-exclamation-triangle fs-2 text-danger mb-2"></i><br>' +
                            '<small class="text-danger">Gagal menampilkan data resep.</small></div>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            );
        }
    });
}

function bukaModalResep(idModal) {
    bootstrap.Modal.getOrCreateInstance(document.getElementById(idModal)).show();
}

function tampilkanLoadingForm(selector) {
    $(selector).html(
        '<div class="text-center p-4"><span class="spinner-border spinner-border-sm"></span>' +
        '<small class="ms-2 text-muted">Memuat form...</small></div>'
    );
}

function submitResep(form, url, button, notification, modalId, successMessage) {
    const $button = $(button);
    const originalText = $button.html();
    $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
    $(notification).html('');

    $.ajax({
        type: 'POST',
        url: url,
        data: $(form).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                bootstrap.Modal.getOrCreateInstance(document.getElementById(modalId)).hide();
                showToast('success', 'Berhasil', response.message || successMessage);
                ShowResep();
            } else {
                $(notification).html('<div class="alert alert-danger py-2 mb-0">' + response.message + '</div>');
            }
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            $(notification).html('<div class="alert alert-danger py-2 mb-0">Terjadi kesalahan saat memproses data.</div>');
        },
        complete: function() {
            $button.prop('disabled', false).html(originalText);
        }
    });
}

$(function() {
    ShowResep();

    $(document).on('click', '#tombol_tambah', function() {
        $('#FormTambahResep').html('');
        $('#NotifikasiTambahResep').html('');
        bukaModalResep('ModalTambahResep');
        tampilkanLoadingForm('#FormTambahResep');
        $('#FormTambahResep').load('_Page/Resep/FormTambah.php');
    });

    $(document).on('click', '.edit_resep', function() {
        const id = $(this).data('id');
        $('#FormEditResep').html('');
        $('#NotifikasiEditResep').html('');
        bukaModalResep('ModalEditResep');
        tampilkanLoadingForm('#FormEditResep');
        $('#FormEditResep').load('_Page/Resep/FormEdit.php', { id_medication_request_group: id });
    });

    $(document).on('click', '.detail_resep', function() {
        const id = $(this).data('id');
        bukaModalResep('ModalDetailResep');
        tampilkanLoadingForm('#FormDetailResep');
        $('#FormDetailResep').load('_Page/Resep/FormDetail.php', { id_medication_request_group: id });
    });

    $(document).on('click', '.hapus_resep', function() {
        const id = $(this).data('id');
        $('#id_medication_request_group_hapus').val(id);
        $('#kode_resep_hapus').text('RSP-' + String(id).padStart(6, '0'));
        $('#NotifikasiHapusResep').html('');
        bukaModalResep('ModalHapusResep');
    });

    $(document).on('submit', '#ProsesTambahResep', function(event) {
        event.preventDefault();
        submitResep(this, '_Page/Resep/ProsesTambah.php', '#TombolTambahResep', '#NotifikasiTambahResep', 'ModalTambahResep', 'Resep berhasil ditambahkan.');
    });

    $(document).on('submit', '#ProsesEditResep', function(event) {
        event.preventDefault();
        submitResep(this, '_Page/Resep/ProsesEdit.php', '#TombolEditResep', '#NotifikasiEditResep', 'ModalEditResep', 'Resep berhasil diperbarui.');
    });

    $(document).on('submit', '#ProsesHapusResep', function(event) {
        event.preventDefault();
        submitResep(this, '_Page/Resep/ProsesHapus.php', '#TombolHapusResep', '#NotifikasiHapusResep', 'ModalHapusResep', 'Resep berhasil dihapus.');
    });

    $(document).on('click', '#list_resep .pagination-resep a[data-page]', function(event) {
        event.preventDefault();
        ShowResep($(this).data('page'));
    });

    $('#ModalTambahResep, #ModalEditResep, #ModalDetailResep, #ModalHapusResep').on('hidden.bs.modal', function() {
        const form = $(this).find('form')[0];
        if (form) form.reset();
        $(this).find('[id^="Form"], [id^="Notifikasi"]').html('');
        $('#id_medication_request_group_hapus').val('');
        $('#kode_resep_hapus').text('');
    });
});

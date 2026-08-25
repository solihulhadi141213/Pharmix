// ========================================================================
// FUNCTION
// Semua Function akan dimuat di paling atas
// ========================================================================

// Tampilkan Data Jenis Transaksi
function ShowData() {
    
    // Target And Filter
    let target = $('#tabel_dokumentasi');
    let data   = $('#ProsesFilter').serialize();

    // Loading or Blur
    target.addClass('blur-loading');

    // Tampilkan Dtaa Dengan AJAX
    $.ajax({
        type    : 'POST',
        url     : '_Page/Dokumentasi/TabelDokumentasi.php',
        data    : data,
        dataType: 'JSON',
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

// Parse List Konten
function ParseListKonten(value) {
    if (!value) {
        return [];
    }
    if (Array.isArray(value)) {
        return value;
    }
    return [];
}

//Fungsi Untuk Menampilkan Halaman Detail Dokumentasi
function ShowDetail(id_dokumentasi) {
    //------ Persiapan tampilan awal loading
    $('#put_id_dokumentasi').val(id_dokumentasi);
    $('#put_judul').html(`
        <span class="placeholder-glow">
            <span class="placeholder col-8"></span>
        </span>
    `);
    $('#put_deskripsi').html('Memuat dokumentasi...');
    $('#put_author').html('-');
    $('#put_status').html('');
    $('#put_tags').html(`
        <div class="text-muted">
            <i class="bi bi-hourglass-split"></i>
            Memuat tags...
        </div>
    `);
    $('.put_list_konten').html(`
        <div class="text-center text-muted py-5">
            <div class="spinner-border spinner-border-sm"></div>
            <div class="mt-2">Memuat konten...</div>
        </div>
    `);

    // ==== AJAX REQUEST
    $.ajax({
        type     : 'POST',
        url      : '_Page/Dokumentasi/_detail_dokumentasi.php',
        data     : { id_dokumentasi: id_dokumentasi },
        dataType : 'JSON',
        success  : function (response) {
            //------ Validasi response
            if (response.status !== 'success') {
                Swal.fire(
                    'Oops!',
                    response.message || 'Data dokumentasi tidak ditemukan.',
                    'error'
                );
                return;
            }

            let detail  = response.detail || {};
            let tags    = response.tags || [];
            let content = response.content || [];

            // ==== RENDER DETAIL DOKUMENTASI
            $('#put_id_dokumentasi').val(detail.id_dokumentasi || id_dokumentasi);
            $('#put_judul').html(escapeHtml(detail.judul || '-'));
            $('#put_deskripsi').html(escapeHtml(detail.deskripsi || '-'));
            $('#put_author').html(escapeHtml(detail.author_name || '-'));
            // Status dari PHP sudah berupa HTML badge, jangan gunakan escapeHtml()
            $('#put_status').html(detail.status || '');

            // ==== RENDER TAGS
            let htmlTags = '';
            if (tags.length === 0) {
                htmlTags = `
                    <span class="text-muted">
                        <i class="bi bi-tags"></i>
                        Tidak ada tag.
                    </span>
                `;
            } else {
                $.each(tags, function (index, item) {
                    htmlTags += `
                        <span class="badge bg-success-subtle text-success border border-success rounded-pill me-1 mb-1">
                            <i class="bi bi-tag"></i>
                            ${escapeHtml(item.tags)}
                        </span>
                    `;
                });
            }
            $('#put_tags').html(htmlTags);

            // ==== RENDER CONTENT
            let htmlContent = '';
            if (content.length === 0) {
                htmlContent = `
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-file-earmark-x fs-1"></i>
                        <div class="mt-2">
                            Belum ada konten dokumentasi.
                        </div>
                    </div>
                `;
            } else {
                $.each(content, function (index, item) {
                    htmlContent += RenderKontenDokumentasi(item);
                    //------ Tambahkan separator antar konten
                    if (index < content.length - 1) {
                        htmlContent += `
                            <hr style="border-top: 1px dashed #adb5bd; opacity: 1;">
                        `;
                    }
                });
            }
            $('.put_list_konten').html(htmlContent);
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error);
            console.error('Response:', xhr.responseText);
            Swal.fire(
                'Oops!',
                'Terjadi kesalahan pada saat menampilkan detail dokumentasi.',
                'error'
            );
            $('.put_list_konten').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Gagal memuat konten dokumentasi.
                </div>
            `);
        }
    });
}

// ==== RENDER KONTEN DOKUMENTASI
function RenderKontenDokumentasi(item) {
    let id_dokumentasi_konten = item.id_dokumentasi_konten || '';
    let tipe                  = item.tipe_konten || '';
    let html                  = '';

    //------ TEXT
    if (tipe === 'Text') {
        html = `
            <div class="row mb-3 mt-3 hover-shadow edit_dokumentasi_konten" data-id="${id_dokumentasi_konten}" style="cursor: pointer;">
                <div class="col-12 mt-3 mb-3">
                    ${item.text_konten || ''}
                </div>
            </div>
        `;
    }
    //------ LIST NUMBERING
    else if (tipe === 'List Numbering') {
        let list = ParseListKonten(item.list_konten);
        let htmlList = '<ol>';
        $.each(list, function (index, value) {
            htmlList += `
                <li>
                    ${escapeHtml(value)}
                </li>
            `;
        });
        htmlList += '</ol>';
        html = `
            <div class="row mb-3 mt-3 hover-shadow edit_dokumentasi_konten" data-id="${id_dokumentasi_konten}" style="cursor: pointer;">
                <div class="col-12 mt-3 mb-3">
                    ${htmlList}
                </div>
            </div>
        `;
    }
    //------ LIST BULLET
    else if (tipe === 'List Bullet') {
        let list = ParseListKonten(item.list_konten);
        let htmlList = '<ul>';
        $.each(list, function (index, value) {
            htmlList += `
                <li>
                    ${escapeHtml(value)}
                </li>
            `;
        });
        htmlList += '</ul>';
        html = `
            <div class="row mb-3 mt-3 hover-shadow edit_dokumentasi_konten" data-id="${id_dokumentasi_konten}" style="cursor: pointer;">
                <div class="col-12 mt-3 mb-3">
                    ${htmlList}
                </div>
            </div>
        `;
    }
    //------ LOCAL IMAGE
    else if (tipe === 'Local Image') {
        if (item.local_image_konten) {
            html = `
                <div class="row mb-3 mt-3 hover-shadow edit_dokumentasi_konten" data-id="${id_dokumentasi_konten}" style="cursor: pointer;">
                    <div class="col-12 mt-3 mb-3 text-center">
                        <img
                            src="assets/img/dokumentasi/${encodeURIComponent(item.local_image_konten)}"
                            class="img-fluid rounded"
                            alt="Dokumentasi"
                            loading="lazy"
                        >
                    </div>
                </div>
            `;
        }
    }
    //------ URL IMAGE
    else if (tipe === 'Url Image') {
        if (item.url_image_konten) {
            html = `
                <div class="row mb-3 mt-3 hover-shadow edit_dokumentasi_konten" data-id="${id_dokumentasi_konten}" style="cursor: pointer;">
                    <div class="col-12 mt-3 mb-3 text-center">
                        <img
                            src="${escapeAttribute(item.url_image_konten)}"
                            class="img-fluid rounded"
                            alt="Dokumentasi"
                            loading="lazy"
                        >
                    </div>
                </div>
            `;
        }
    }
    //------ TIPE TIDAK DIKENAL
    else {
        html = `
            <div class="row mb-3 mt-3">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Tipe konten <strong>${escapeHtml(tipe)}</strong> belum didukung.
                    </div>
                </div>
            </div>
        `;
    }

    return html;
}

// ESCAPE HTML
function escapeHtml(value) {
    return $('<div>')
        .text(value ?? '')
        .html();
}

// ESCAPE ATTRIBUTE
function escapeAttribute(value) {
    return $('<div>')
        .text(value ?? '')
        .html()
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}


// ========================================================================
// EVENT LISTENER
// ========================================================================

$(document).ready(function() {
    
    // ------------------------------------
    // Menampilkan Data Pertama Kali
    // ------------------------------------
    
    // Tampilkan 'tabel_view'
    $('#tabel_view').show();

    // Sembunyikan 'tabel_view'
    $('#detail_view').hide();

    // Tampilkan Tabel
    ShowData();

    // Ketika modal 'ModalFilter' muncul langsung autofocus ke 'judul_dokumentasi'
    $('#ModalFilter').on('shown.bs.modal', function (e) {
        $('#keyword').trigger('focus');
    });

    //Ketika keyword By Diubah
    $('#keyword_by').change(function(){
        var keyword_by = $('#keyword_by').val();
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Dokumentasi/FormFilter.php',
            data 	    :  {keyword_by: keyword_by},
            success     : function(data){
                $('#FormFilter').html(data);
            }
        });
    });

    // Submit Filter
    $('#ProsesFilter').on('submit', function (e) {
        e.preventDefault();

        // Reset ke halaman pertama
        $('#PutPage').val(1);

        // Tampilkan data
        ShowData();

        // Tutup modal
        $('#ModalFilter').modal('hide');

    });

    //Pagging
    $(document).on('click', '#next_button', function() {
        var page_now = parseInt($('#PutPage').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page = page_now + 1;
        $('#PutPage').val(next_page);
        ShowData(0);
    });
    $(document).on('click', '#prev_button', function() {
        var page_now = parseInt($('#PutPage').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page = page_now - 1;
        $('#PutPage').val(next_page);
        ShowData(0);
    });

    // ------------------------------------
    // TAMBAH DOKUMENTASI
    // ------------------------------------

    // Ketika modal muncul langsung autofocus ke 'judul_dokumentasi'
    $('#ModalTambah').on('shown.bs.modal', function (e) {
        $('#judul_dokumentasi').trigger('focus');
    });

    // Atur tags_dokumentasi menggunakan select2
    $('#tags_dokumentasi').select2({
        theme         : 'bootstrap-5',
        width         : '100%',
        multiple      : true,
        tags          : true,
        tokenSeparators: [','],
        placeholder   : 'Ketik atau pilih tag...',
        allowClear    : true,
        dropdownParent: $('#ModalTambah'),
        ajax: {
            url     : '_Page/Dokumentasi/GetTags.php',
            type    : 'POST',
            dataType: 'json',
            delay   : 250,
            data    : function (params) {
                return {
                    keyword: params.term || ''
                };
            },
            processResults: function (response) {
                return {
                    results: response.status ? response.results : []
                };
            },
            cache: true
        },
        createTag: function (params) {
            let term = $.trim(params.term);
            if (term === '') {
                return null;
            }
            return {
                id: term,
                text: term,
                newTag: true
            };
        }
    });

    // PROSES TAMBAH DOKUMENTASI
    $('#ProsesTambah').on('submit', function (e) {
        e.preventDefault();
        let form = this;

        //------ Ambil & validasi tag
        let tags = $('#tags_dokumentasi').val();
        if (!tags || tags.length === 0) {
            $('#NotifikasiTambah').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Minimal satu tag harus dipilih.
                </div>
            `);
            $('#tags_dokumentasi').select2('open');
            return;
        }

        //------ Validasi judul
        let judul = $.trim($('#judul_dokumentasi').val());
        if (judul === '') {
            $('#NotifikasiTambah').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Judul dokumentasi wajib diisi.
                </div>
            `);
            $('#judul_dokumentasi').focus();
            return;
        }

        //------ Bersihkan notifikasi & siapkan data
        $('#NotifikasiTambah').html('');
        let formData = new FormData(form);

        //------ Disable tombol submit & tampilkan spinner
        $('#TombolTambah').prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Menyimpan...
        `);

        //------ Kirim AJAX
        $.ajax({
            type        : 'POST',
            url         : '_Page/Dokumentasi/ProsesTambah.php',
            data        : formData,
            processData : false,
            contentType : false,
            dataType    : 'JSON',
            success     : function (res) {
                if (res.status === true) {
                    $('#NotifikasiTambah').html(`
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            ${res.message}
                        </div>
                    `);
                    form.reset();
                    $('#tags_dokumentasi').val(null).trigger('change');
                    $('#PutPage').val(1);
                    ShowData();
                    $('#ModalTambah').modal('hide');
                    $('#NotifikasiTambah').html('');
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );
                } else {
                    $('#NotifikasiTambah').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            ${res.message || 'Gagal menyimpan dokumentasi.'}
                        </div>
                    `);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response Server:', xhr.responseText);
                $('#NotifikasiTambah').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Terjadi kesalahan pada server.
                    </div>
                `);
            },
            complete: function () {
                $('#TombolTambah').prop('disabled', false).html(`
                    <i class="bi bi-save"></i>
                    Simpan
                `);
            }
        });
    });

    // ------------------------------------
    // DETAIL DOKUMENTASI
    // ------------------------------------
    $(document).on('click', '.show_detail', function() {

        // Tangkap id_dokumentasi
        var id_dokumentasi = $(this).data('id');
        // Sembunyikan 'tabel_view'
        $('#tabel_view').hide();

        // Tampilkan 'tabel_view'
        $('#detail_view').show();

        // Scroll ke atas
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });

        ShowDetail(id_dokumentasi);
    });
    
    // Kembali Ke Data Tabel
    $(document).on('click', '.back_to_table_view', function() {
        // Tampilkan 'tabel_view'
        $('#tabel_view').show();

        // Sembunyikan 'tabel_view'
        $('#detail_view').hide();

        // Scroll ke atas
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // ------------------------------------
    // TAMBAH KONTEN DOKUMENTASI
    // ------------------------------------
    $(document).on('click', '.tambah_dokumentasi_konten', function() {

        // Tangkap id_dokumentasi
        var id_dokumentasi = $('#put_id_dokumentasi').val();

        // Tampilkan Modal
        $('#ModalTambahKonten').modal('show');

        // Tempelkan 'id_dokumentasi' ke 'put_id_dokumentasi_konten'
        $('#put_id_dokumentasi_konten').val(id_dokumentasi);

    });

    // ==== VARIABEL QUILL
    let quillKonten = null;

    // ==== TAMBAH KONTEN DOKUMENTASI
    $(document).on('click', '.tambah_dokumentasi_konten', function () {
        //------ Ambil & validasi ID Dokumentasi
        let id_dokumentasi = $('#put_id_dokumentasi').val();
        if (!id_dokumentasi || id_dokumentasi === '0') {
            Swal.fire(
                'Oops!',
                'ID dokumentasi tidak ditemukan.',
                'error'
            );
            return;
        }

        //------ Reset modal & tampilkan
        $('#ProsesTambahKonten')[0].reset();
        $('#put_id_dokumentasi_konten').val(id_dokumentasi);
        $('#FormTambahKonten').html('');
        $('#NotifikasiTambahKonten').html('');
        $('#ModalTambahKonten').modal('show');
    });

    // ==== KETIKA TIPE KONTEN BERUBAH
    $(document).on('change', '#tipe_konten', function () {
        let tipe = $(this).val();
        RenderFormKonten(tipe);
    });

    // ==== RENDER FORM BERDASARKAN TIPE
    function RenderFormKonten(tipe) {
        quillKonten = null;
        $('#FormTambahKonten').html('');

        if (!tipe) {
            return;
        }

        // ==== TEXT
        if (tipe === 'Text') {
            $('#FormTambahKonten').html(`
                <div class="row mb-3">
                    <div class="col-12 mb-3">
                        <label><small>* Konten</small></label>
                        <div id="editor_konten" style="min-height: 250px; background-color: #fff;"></div>
                        <input type="hidden" name="text_konten" id="text_konten">
                    </div>
                </div>
            `);

            //------ Inisialisasi Quill
            quillKonten = new Quill('#editor_konten', {
                theme: 'snow',
                placeholder: 'Tulis konten dokumentasi...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        ['blockquote', 'code-block'],
                        ['link'],
                        [{ 'align': [] }],
                        [{ 'indent': '-1' }, { 'indent': '+1' }],
                        ['clean']
                    ]
                }
            });
            return;
        }

        // ==== LIST NUMBERING
        if (tipe === 'List Numbering') {
            $('#FormTambahKonten').html(`
                <div class="row mb-3">
                    <div class="col-12">
                        <label><small>* Daftar</small></label>
                        <div id="list_konten_container"></div>
                        <button type="button" class="btn btn-md btn-secondary mt-3 btn-block" id="TambahItemList">
                            <i class="bi bi-plus"></i> Tambah Item
                        </button>
                    </div>
                </div>
            `);
            TambahItemList();
            return;
        }

        // ==== LIST BULLET
        if (tipe === 'List Bullet') {
            $('#FormTambahKonten').html(`
                <div class="row mb-3">
                    <div class="col-12">
                        <label><small>* Daftar</small></label>
                        <div id="list_konten_container"></div>
                        <button type="button" class="btn btn-md btn-secondary mt-3 btn-block" id="TambahItemList">
                            <i class="bi bi-plus"></i> Tambah Item
                        </button>
                    </div>
                </div>
            `);
            TambahItemList();
            return;
        }

        // ==== LOCAL IMAGE
        if (tipe === 'Local Image') {
            $('#FormTambahKonten').html(`
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="local_image_konten"><small>* Upload Image</small></label>
                        <input type="file" name="local_image_konten" id="local_image_konten" class="form-control" accept="image/jpeg,image/png,image/webp" required>
                        <small class="text-muted">Format: JPG, JPEG, PNG atau WEBP.</small>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <img src="" id="preview_local_image" class="img-fluid rounded d-none" style="max-height: 300px;">
                    </div>
                </div>
            `);
            return;
        }

        // ==== URL IMAGE
        if (tipe === 'Url Image') {
            $('#FormTambahKonten').html(`
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="url_image_konten"><small>* URL Image</small></label>
                        <input type="url" name="url_image_konten" id="url_image_konten" class="form-control" placeholder="https://example.com/image.jpg" required>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <img src="" id="preview_url_image" class="img-fluid rounded d-none" style="max-height: 300px;">
                    </div>
                </div>
            `);
            return;
        }
    }

    // ==== TAMBAH ITEM LIST
    function TambahItemList() {
        let container = $('#list_konten_container');
        if (container.length === 0) {
            return;
        }

        let jumlah = container.find('.item-list-konten').length;
        let nomor = jumlah + 1;

        let html = `
            <div class="input-group mb-2 item-list-konten">
                <span class="input-group-text">
                    ${nomor}
                </span>
                <input
                    type="text"
                    name="list_konten[]"
                    class="form-control"
                    placeholder="Masukkan item..."
                    required
                >
                <button
                    type="button"
                    class="btn btn-danger HapusItemList"
                    title="Hapus">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;

        container.append(html);

        //------ Fokus ke input baru
        container
            .find('.item-list-konten:last input')
            .focus();

        UpdateNomorList();
    }

   // TOMBOL TAMBAH ITEM
    $(document).on('click', '#TambahItemList', function () {
        TambahItemList();
    });

   // HAPUS ITEM LIST
    $(document).on('click', '.HapusItemList', function () {
        let container = $('#list_konten_container');
        $(this)
            .closest('.item-list-konten')
            .remove();
        // Minimal harus ada 1 item
        if (container.find('.item-list-konten').length === 0) {
            TambahItemList();
        }
        UpdateNomorList();
    });

    // UPDATE NOMOR LIST
    function UpdateNomorList() {
        $('#list_konten_container .item-list-konten').each(function (index) {
            $(this)
                .find('.input-group-text')
                .text(index + 1);
        });
    }

    // ==== PREVIEW LOCAL IMAGE
    $(document).on('change', '#local_image_konten', function () {
        let file = this.files[0];
        if (!file) {
            $('#preview_local_image').attr('src', '').addClass('d-none');
            return;
        }

        //------ Validasi tipe file
        let allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) {
            Swal.fire(
                'Format Tidak Valid',
                'File harus berupa JPG, JPEG, PNG atau WEBP.',
                'warning'
            );
            $(this).val('');
            return;
        }

        //------ Tampilkan preview
        let reader = new FileReader();
        reader.onload = function (e) {
            $('#preview_local_image')
                .attr('src', e.target.result)
                .removeClass('d-none');
        };
        reader.readAsDataURL(file);
    });

    // PREVIEW URL IMAGE
    $(document).on('input', '#url_image_konten', function () {
        let url = $.trim($(this).val());
        if (url === '') {
            $('#preview_url_image')
                .attr('src', '')
                .addClass('d-none');
            return;
        }
        $('#preview_url_image')
            .attr('src', url)
            .removeClass('d-none');
    });

    // ==== SUBMIT TAMBAH KONTEN
    $('#ProsesTambahKonten').on('submit', function (e) {
        e.preventDefault();

        let form = this;
        let id_dokumentasi = $('#put_id_dokumentasi_konten').val();
        let tipe = $('#tipe_konten').val();

        //------ Validasi ID
        if (!id_dokumentasi || id_dokumentasi === '0') {
            $('#NotifikasiTambahKonten').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    ID dokumentasi tidak ditemukan.
                </div>
            `);
            return;
        }

        //------ Validasi tipe
        if (!tipe) {
            $('#NotifikasiTambahKonten').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Silakan pilih tipe konten.
                </div>
            `);
            $('#tipe_konten').focus();
            return;
        }

        //------ Jika Text → ambil HTML dari Quill
        if (tipe === 'Text') {
            if (!quillKonten) {
                $('#NotifikasiTambahKonten').html(`
                    <div class="alert alert-danger">
                        Editor belum siap.
                    </div>
                `);
                return;
            }

            let html = quillKonten.root.innerHTML;
            if (html === '<p><br></p>' || $.trim(quillKonten.getText()) === '') {
                $('#NotifikasiTambahKonten').html(`
                    <div class="alert alert-danger">
                        Konten tidak boleh kosong.
                    </div>
                `);
                return;
            }

            $('#text_konten').val(html);
        }

        //------ Bersihkan notifikasi & disable tombol
        $('#NotifikasiTambahKonten').html('');
        $('#TombolTambahKonten').prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm" role="status"></span>
            Menyimpan...
        `);

        let formData = new FormData(form);

        // ==== AJAX REQUEST
        $.ajax({
            type        : 'POST',
            url         : '_Page/Dokumentasi/ProsesTambahKonten.php',
            data        : formData,
            processData : false,
            contentType : false,
            dataType    : 'JSON',
            success     : function (response) {
                if (response.status === true) {
                    $('#NotifikasiTambahKonten').html(`
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            ${response.message}
                        </div>
                    `);
                    ShowDetail(id_dokumentasi);
                    setTimeout(function () {
                        $('#ModalTambahKonten').modal('hide');
                    }, 800);
                } else {
                    $('#NotifikasiTambahKonten').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            ${response.message || 'Gagal menyimpan konten.'}
                        </div>
                    `);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
                $('#NotifikasiTambahKonten').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Terjadi kesalahan pada server.
                    </div>
                `);
            },
            complete: function () {
                $('#TombolTambahKonten').prop('disabled', false).html(`
                    <i class="bi bi-save"></i>
                    Simpan
                `);
            }
        });
    });

    // ------------------------------------
    // EDIT KONTEN DOKUMENTASI
    // ------------------------------------
    
    // SHOW EDIT KONTEN
    function ShowEditKonten(id_dokumentasi_konten) {

        // Reset
        $('#edit_id_dokumentasi_konten').val(id_dokumentasi_konten);
        $('#edit_tipe_konten').val('').trigger('change');
        $('#edit_tipe_konten_hidden').val('');

        $('#FormEditKonten').html(`
            <div class="text-center text-muted py-5">
                <div
                    class="spinner-border spinner-border-sm"
                    role="status">
                </div>
                <div class="mt-2">
                    Memuat konten...
                </div>
            </div>
        `);

        $('#NotifikasiEditKonten').html('');

        // Tampilkan modal
        $('#ModalEditKonten').modal('show');

        // AJAX
        $.ajax({
            type: 'POST',
            url: '_Page/Dokumentasi/_get_konten.php',
            data: {
                id_dokumentasi_konten: id_dokumentasi_konten
            },
            dataType: 'JSON',
            success: function (response) {
                if (response.status !== 'success') {
                    $('#FormEditKonten').html('');
                    Swal.fire(
                        'Oops!',
                        response.message || 'Konten tidak ditemukan.',
                        'error'
                    );
                    return;
                }
                let data = response.data || {};
                let tipe = data.tipe_konten || '';

                // Set tipe
                $('#edit_tipe_konten')
                    .val(tipe);

                $('#edit_tipe_konten_hidden')
                    .val(tipe);

                // Render form
                RenderFormEditKonten(data);

            },

            error: function (xhr, status, error) {

                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);

                $('#FormEditKonten').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Gagal mengambil data konten.
                    </div>
                `);

            }

        });

    }

    // ==== RENDER FORM EDIT KONTEN
    function RenderFormEditKonten(data) {
        let tipe = data.tipe_konten || '';
        let html = '';

        // ==== TEXT
        if (tipe === 'Text') {
            html = `
                <div class="mb-3">
                    <label><small>* Isi Konten</small></label>
                    <div id="EditQuillEditor" style="min-height: 250px;"></div>
                    <input type="hidden" name="text_konten" id="edit_text_konten">
                </div>
            `;
            $('#FormEditKonten').html(html);

            //------ Inisialisasi Quill
            let editQuill = new Quill('#EditQuillEditor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'header': 1 }, { 'header': 2 }],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        [{ 'indent': '-1' }, { 'indent': '+1' }],
                        ['link', 'image'],
                        ['clean']
                    ]
                }
            });

            //------ Masukkan konten lama & simpan instance Quill
            editQuill.root.innerHTML = data.text_konten || '';
            $('#ModalEditKonten').data('quill', editQuill);
        }
        // ==== LIST NUMBERING
        else if (tipe === 'List Numbering') {
            html = RenderEditListForm(data.list_konten, 'List Numbering');
            $('#FormEditKonten').html(html);
        }
        // ==== LIST BULLET
        else if (tipe === 'List Bullet') {
            html = RenderEditListForm(data.list_konten, 'List Bullet');
            $('#FormEditKonten').html(html);
        }
        // ==== LOCAL IMAGE
        else if (tipe === 'Local Image') {
            html = `
                <div class="mb-3">
                    <label class="form-label"><small>Ganti Gambar</small></label>
                    <input type="file" name="local_image_konten" id="edit_local_image_konten" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">Kosongkan jika tidak ingin mengganti gambar.</div>
                </div>
                ${data.local_image_konten ? `
                    <div class="text-center mt-3">
                        <p class="text-muted">Gambar saat ini:</p>
                        <img src="assets/img/dokumentasi/${encodeURIComponent(data.local_image_konten)}" class="img-fluid rounded" style="max-height:300px;" alt="Gambar Dokumentasi">
                    </div>
                ` : ''}
            `;
            $('#FormEditKonten').html(html);
        }
        // ==== URL IMAGE
        else if (tipe === 'Url Image') {
            html = `
                <div class="mb-3">
                    <label for="edit_url_image_konten"><small>* URL Gambar</small></label>
                    <input type="url" name="url_image_konten" id="edit_url_image_konten" class="form-control" value="${escapeAttribute(data.url_image_konten || '')}" placeholder="https://example.com/image.jpg" required>
                </div>
                <div class="text-center mt-3">
                    <img id="preview_edit_url_image" src="${escapeAttribute(data.url_image_konten || '')}" class="img-fluid rounded" style="max-height:300px;" alt="Preview" onerror="this.style.display='none';" onload="this.style.display='inline-block';">
                </div>
            `;
            $('#FormEditKonten').html(html);
        }
        // ==== TIDAK DIKENAL
        else {
            $('#FormEditKonten').html(`
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    Tipe konten tidak dikenali.
                </div>
            `);
        }
    }

    // ==== FORM EDIT LIST
    function RenderEditListForm(list, tipe) {
        if (!Array.isArray(list)) {
            list = [];
        }

        //------ Minimal satu input
        if (list.length === 0) {
            list = [''];
        }

        let html = `
            <div class="mb-3">
                <label><small>* Isi ${escapeHtml(tipe)}</small></label>
                <div id="EditListContainer">
        `;

        $.each(list, function(index, value) {
            html += `
                <div class="input-group mb-2 edit-list-item">
                    <span class="input-group-text">${index + 1}</span>
                    <input
                        type="text"
                        name="list_konten[]"
                        class="form-control edit-list-input"
                        value="${escapeAttribute(value || '')}"
                        placeholder="Masukkan isi list">
                    <button
                        type="button"
                        class="btn btn-outline-danger btn-remove-edit-list"
                        title="Hapus">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
        });

        html += `
                </div>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary mt-2"
                    id="btnTambahEditList">
                    <i class="bi bi-plus"></i> Tambah Item
                </button>
            </div>
        `;

        return html;
    }

    // ==== TAMBAH ITEM EDIT LIST
    $(document).on('click', '#btnTambahEditList', function () {
        $('#EditListContainer').append(`
            <div class="input-group mb-2 edit-list-item">
                <span class="input-group-text">#</span>
                <input
                    type="text"
                    name="list_konten[]"
                    class="form-control edit-list-input"
                    placeholder="Masukkan isi list">
                <button
                    type="button"
                    class="btn btn-outline-danger btn-remove-edit-list"
                    title="Hapus">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `);

        UpdateEditListNumber();

        //------ Fokus ke input baru
        $('#EditListContainer .edit-list-input').last().focus();
    });

    // ==== HAPUS ITEM EDIT LIST
    $(document).on('click', '.btn-remove-edit-list', function () {
        let total = $('#EditListContainer .edit-list-item').length;

        //------ Jangan sampai semua item dihapus
        if (total <= 1) {
            $('#NotifikasiEditKonten').html(`
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    Minimal satu item harus tersedia.
                </div>
            `);
            return;
        }

        $(this).closest('.edit-list-item').remove();
        UpdateEditListNumber();
    });

    // fungsi numbering:
    function UpdateEditListNumber() {
        $('#EditListContainer .edit-list-item').each(function(index) {
            $(this).find('.input-group-text').text(index + 1);
        });
    }

    //Preview URL Image
    $(document).on('input','#edit_url_image_konten',function () {
            let url = $.trim($(this).val());
            let preview = $('#preview_edit_url_image');
            if (url === '') {
                preview.hide();
                return;
            }
            preview.attr('src', url).show();
        }
    );

    // Ketika Click 'edit_dokumentasi_konten'
    $(document).on('click', '.edit_dokumentasi_konten', function () {
        let id_dokumentasi_konten = $(this).data('id');
        if (!id_dokumentasi_konten) {
            Swal.fire(
                'Oops!',
                'ID konten dokumentasi tidak ditemukan.',
                'error'
            );
            return;
        }
        ShowEditKonten(id_dokumentasi_konten);

    });

    // ==== PROSES EDIT KONTEN
    $('#ProsesEditKonten').on('submit', function(e) {
        e.preventDefault();

        let form = this;
        let id = $('#edit_id_dokumentasi_konten').val();
        let tipe = $('#edit_tipe_konten_hidden').val();

        console.group('=== DEBUG EDIT KONTEN ===');
        console.log('ID Konten    :', id);
        console.log('Tipe Konten  :', tipe);

        //------ Validasi ID
        if (!id || id === '0') {
            console.error('VALIDASI GAGAL: ID konten tidak valid.');
            console.groupEnd();
            $('#NotifikasiEditKonten').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    ID konten tidak valid.
                </div>
            `);
            return;
        }

        //------ Validasi Tipe
        if (!tipe) {
            console.error('VALIDASI GAGAL: Tipe konten kosong.');
            console.groupEnd();
            $('#NotifikasiEditKonten').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Tipe konten tidak ditemukan.
                </div>
            `);
            return;
        }

        //------ Validasi Text
        if (tipe === 'Text') {
            let quill = $('#ModalEditKonten').data('quill');
            console.log('Quill Instance:', quill);

            if (!quill) {
                console.error('QUILL ERROR: Instance Quill tidak ditemukan.');
                console.groupEnd();
                $('#NotifikasiEditKonten').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Editor belum siap.
                    </div>
                `);
                return;
            }

            let htmlContent = quill.root.innerHTML;
            let textContent = quill.getText().trim();
            console.log('Quill HTML:', htmlContent);
            console.log('Quill Text:', textContent);

            if (textContent === '') {
                console.error('VALIDASI GAGAL: Konten Text kosong.');
                console.groupEnd();
                $('#NotifikasiEditKonten').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Isi konten tidak boleh kosong.
                    </div>
                `);
                return;
            }

            $('#edit_text_konten').val(htmlContent);
            console.log('edit_text_konten berhasil diisi.');
        }

        //------ Validasi List
        if (tipe === 'List Numbering' || tipe === 'List Bullet') {
            let valid = true;
            let jumlah = 0;

            $('#EditListContainer .edit-list-input').each(function(index) {
                let value = $.trim($(this).val());
                console.log('List Item ' + (index + 1) + ':', value);

                if (value === '') {
                    valid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                    jumlah++;
                }
            });

            console.log('Jumlah List Valid:', jumlah);

            if (!valid) {
                console.error('VALIDASI GAGAL: Ada item list yang kosong.');
                console.groupEnd();
                $('#NotifikasiEditKonten').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Semua item list harus diisi.
                    </div>
                `);
                return;
            }

            if (jumlah === 0) {
                console.error('VALIDASI GAGAL: Tidak ada item list.');
                console.groupEnd();
                $('#NotifikasiEditKonten').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Minimal satu item list harus diisi.
                    </div>
                `);
                return;
            }
        }

        //------ Reset Notifikasi & Siapkan FormData
        $('#NotifikasiEditKonten').html('');
        let formData = new FormData(form);

        console.log('----- DATA YANG DIKIRIM -----');
        for (let pair of formData.entries()) {
            let key = pair[0];
            let value = pair[1];
            if (value instanceof File) {
                console.log(key + ':', '[FILE]', value.name, value.type, value.size + ' bytes');
            } else {
                console.log(key + ':', value);
            }
        }
        console.log('----------------------------');

        //------ Button Loading
        $('#TombolEditKonten').prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Menyimpan...
        `);

        // ==== AJAX REQUEST
        $.ajax({
            type        : 'POST',
            url         : '_Page/Dokumentasi/ProsesEditKonten.php',
            data        : formData,
            processData : false,
            contentType : false,
            dataType    : 'text',
            success     : function(response, textStatus, xhr) {
                console.log('================================');
                console.log('AJAX SUCCESS');
                console.log('================================');
                console.log('HTTP Status:', xhr.status);
                console.log('Status Text:', textStatus);
                console.log('Response Length:', response.length);
                console.log('----- RESPONSE RAW -----');
                console.log(response);
                console.log('------------------------');

                if (!response || response.trim() === '') {
                    console.error('SERVER ERROR: Response dari PHP kosong.');
                    $('#NotifikasiEditKonten').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            Server tidak mengembalikan response.<br>
                            <small>Silakan buka Console untuk melihat detail debug.</small>
                        </div>
                    `);
                    return;
                }

                let result;
                try {
                    result = JSON.parse(response);
                    console.log('----- JSON PARSED -----');
                    console.log(result);
                    console.log('-----------------------');
                } catch (jsonError) {
                    console.error('JSON PARSE ERROR:', jsonError);
                    console.error('Response bukan JSON valid.');
                    $('#NotifikasiEditKonten').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            Server tidak mengembalikan JSON yang valid.<br>
                            <small>Buka Console Browser untuk melihat response dari server.</small>
                        </div>
                    `);
                    return;
                }

                if (result.status === true) {
                    console.log('EDIT BERHASIL:', result.message);
                    $('#NotifikasiEditKonten').html(`
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            ${escapeHtml(result.message || 'Konten berhasil diperbarui.')}
                        </div>
                    `);

                    let idDokumentasi = $('#put_id_dokumentasi').val();
                    console.log('Refresh Dokumentasi:', idDokumentasi);

                    ShowDetail(idDokumentasi);

                    setTimeout(function() {
                        $('#ModalEditKonten').modal('hide');
                    }, 700);
                } else {
                    console.error('PHP mengembalikan status FALSE.');
                    console.error('Message:', result.message);
                    $('#NotifikasiEditKonten').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            ${escapeHtml(result.message || 'Gagal memperbarui konten.')}
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.group('=== AJAX ERROR EDIT KONTEN ===');
                console.error('AJAX Error:', error);
                console.error('Status:', status);
                console.error('HTTP Status:', xhr.status);
                console.error('HTTP Status Text:', xhr.statusText);
                console.error('Response Headers:', xhr.getAllResponseHeaders());
                console.error('Response Length:', xhr.responseText ? xhr.responseText.length : 0);
                console.error('Response Server RAW:');
                console.error(xhr.responseText);
                console.groupEnd();

                let serverMessage = '';
                if (xhr.responseText && xhr.responseText.trim() !== '') {
                    serverMessage = `
                        <hr>
                        <small><strong>Response Server:</strong></small>
                        <pre style="white-space: pre-wrap; max-height: 300px; overflow: auto; font-size: 11px;">${escapeHtml(xhr.responseText)}</pre>
                    `;
                } else {
                    serverMessage = `
                        <hr>
                        <small>Server tidak mengembalikan response.</small>
                    `;
                }

                $('#NotifikasiEditKonten').html(`
                    <div class="alert alert-danger">
                        <div>
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Terjadi kesalahan AJAX.</strong>
                        </div>
                        <small>HTTP Status: <strong>${xhr.status}</strong></small>
                        ${serverMessage}
                    </div>
                `);
            },
            complete: function(xhr, status) {
                console.log('AJAX COMPLETE:', status);
                $('#TombolEditKonten').prop('disabled', false).html(`
                    <i class="bi bi-save"></i>
                    Simpan Perubahan
                `);
                console.groupEnd();
            }
        });
    });

    // ==== PINDAH KONTEN KE ATAS
    $(document).on('click', '.pindah_ke_atas', function () {
        let id_dokumentasi_konten = $('#edit_id_dokumentasi_konten').val();

        //------ Validasi ID
        if (!id_dokumentasi_konten || id_dokumentasi_konten === '0') {
            $('#NotifikasiEditKonten').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    ID konten tidak valid.
                </div>
            `);
            return;
        }

        //------ Konfirmasi
        Swal.fire({
            title: 'Pindahkan ke atas?',
            text: 'Konten akan dipindahkan satu posisi ke atas.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, pindahkan',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }
            ProsesPindahSequence(id_dokumentasi_konten, 'atas');
        });
    });

    // ==== PINDAH KONTEN KE BAWAH
    $(document).on('click', '.pindah_ke_bawah', function () {
        let id_dokumentasi_konten = $('#edit_id_dokumentasi_konten').val();

        //------ Validasi ID
        if (!id_dokumentasi_konten || id_dokumentasi_konten === '0') {
            $('#NotifikasiEditKonten').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    ID konten tidak valid.
                </div>
            `);
            return;
        }

        //------ Konfirmasi
        Swal.fire({
            title: 'Pindahkan ke bawah?',
            text: 'Konten akan dipindahkan satu posisi ke bawah.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, pindahkan',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }
            ProsesPindahSequence(id_dokumentasi_konten, 'bawah');
        });
    });

    // ==== FUNGSI PROSES PINDAH SEQUENCE
    function ProsesPindahSequence(id_dokumentasi_konten, arah) {
        //------ Disable tombol & tampilkan loading
        $('.pindah_ke_atas, .pindah_ke_bawah').prop('disabled', true);
        $('#NotifikasiEditKonten').html(`
            <div class="alert alert-info">
                <span class="spinner-border spinner-border-sm"></span>
                Memproses perubahan urutan...
            </div>
        `);

        // ==== AJAX REQUEST
        $.ajax({
            type     : 'POST',
            url      : '_Page/Dokumentasi/ProsesPindahSequence.php',
            data     : {
                id_dokumentasi_konten: id_dokumentasi_konten,
                arah: arah
            },
            dataType : 'json',
            success  : function (response) {
                console.log('Response Pindah Sequence:', response);

                if (response.status === true) {
                    $('#NotifikasiEditKonten').html(`
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            ${escapeHtml(response.message)}
                        </div>
                    `);

                    let id_dokumentasi = $('#put_id_dokumentasi').val();
                    ShowDetail(id_dokumentasi);

                    setTimeout(function () {
                        $('#ModalEditKonten').modal('hide');
                    }, 500);
                } else {
                    $('#NotifikasiEditKonten').html(`
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            ${escapeHtml(response.message || 'Konten tidak dapat dipindahkan.')}
                        </div>
                    `);
                }
            },
            error: function (xhr, status, error) {
                console.error('=== ERROR PINDAH SEQUENCE ===');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('HTTP Status:', xhr.status);
                console.error('Response:', xhr.responseText);

                $('#NotifikasiEditKonten').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Terjadi kesalahan pada server.
                        <hr>
                        <small>HTTP ${xhr.status}</small>
                    </div>
                `);
            },
            complete: function () {
                $('.pindah_ke_atas, .pindah_ke_bawah').prop('disabled', false);
            }
        });
    }

    // ==== HAPUS KONTEN
    $(document).on('click', '.hapus_konten', function () {
        let id_dokumentasi_konten = $('#edit_id_dokumentasi_konten').val();

        //------ Validasi ID
        if (!id_dokumentasi_konten || id_dokumentasi_konten === '0') {
            $('#NotifikasiEditKonten').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    ID konten tidak valid.
                </div>
            `);
            return;
        }

        //------ Konfirmasi hapus
        Swal.fire({
            title: 'Hapus Konten?',
            html: `
                <div class="text-muted">
                    Konten dokumentasi yang dihapus tidak dapat dikembalikan.
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }
            ProsesHapusKonten(id_dokumentasi_konten);
        });
    });

    // ==== FUNGSI PROSES HAPUS
    function ProsesHapusKonten(id_dokumentasi_konten) {
        //------ Disable tombol & tampilkan loading
        $('.hapus_konten, .pindah_ke_atas, .pindah_ke_bawah').prop('disabled', true);
        $('#NotifikasiEditKonten').html(`
            <div class="alert alert-info">
                <span class="spinner-border spinner-border-sm"></span>
                Menghapus konten...
            </div>
        `);

        // ==== AJAX REQUEST
        $.ajax({
            type     : 'POST',
            url      : '_Page/Dokumentasi/ProsesHapusKonten.php',
            data     : { id_dokumentasi_konten: id_dokumentasi_konten },
            dataType : 'json',
            success  : function (response) {
                console.log('Response Hapus Konten:', response);

                if (response.status === true) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message || 'Konten berhasil dihapus.',
                        timer: 1000,
                        showConfirmButton: false
                    });

                    let id_dokumentasi = $('#put_id_dokumentasi').val();
                    $('#ModalEditKonten').modal('hide');
                    ShowDetail(id_dokumentasi);
                } else {
                    $('#NotifikasiEditKonten').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            ${escapeHtml(response.message || 'Konten gagal dihapus.')}
                        </div>
                    `);
                }
            },
            error: function (xhr, status, error) {
                console.error('=== ERROR HAPUS KONTEN ===');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('HTTP:', xhr.status);
                console.error('Response:', xhr.responseText);

                $('#NotifikasiEditKonten').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Terjadi kesalahan pada server.
                        <hr>
                        <small>HTTP ${xhr.status}</small>
                    </div>
                `);
            },
            complete: function () {
                $('.hapus_konten, .pindah_ke_atas, .pindah_ke_bawah').prop('disabled', false);
            }
        });
    }

    // ------------------------------------
    // EDIT DOKUMENTASI
    // ------------------------------------
    
    // ==== Inisialisasi Select2 Edit Dokumentasi
    function InitSelect2EditDokumentasi() {
        let $select = $('#tags_dokumentasi_edit');

        //------ Hancurkan Select2 jika sebelumnya sudah aktif
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        $select.select2({
            theme         : 'bootstrap-5',
            width         : '100%',
            placeholder   : 'Pilih Tags / Category',
            allowClear    : true,
            multiple      : true,
            tags          : true,
            dropdownParent: $('#ModalEditDokumentasi'),
            createTag     : function (params) {
                let term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            },
            ajax: {
                url     : '_Page/Dokumentasi/GetTags.php',
                type    : 'POST',
                dataType: 'json',
                delay   : 250,
                data    : function (params) {
                    return {
                        search: params.term || '',
                        page: params.page || 1
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
                if (data.newTag) {
                    return $(
                        '<span>' +
                            '<i class="bi bi-plus-circle me-1"></i>' +
                            'Tambah tag: <strong>' +
                            $('<div>').text(data.text).html() +
                            '</strong>' +
                        '</span>'
                    );
                }
                return data.text;
            },
            templateSelection: function (data) {
                return data.text;
            }
        });
    }
    // =========================================================
    // EDIT DOKUMENTASI
    // Support:
    // 1. .edit_dokumentasi  -> ID dari data-id
    // 2. .edit_dokumentasi2 -> ID dari #put_id_dokumentasi
    // =========================================================
    $(document).on('click', '.edit_dokumentasi, .edit_dokumentasi2', function (e) {
        e.preventDefault();

        //------ Ambil ID Dokumentasi
        let id_dokumentasi = $(this).data('id');
        if (!id_dokumentasi) {
            id_dokumentasi = $('#put_id_dokumentasi').val();
        }
        id_dokumentasi = $.trim(String(id_dokumentasi || ''));

        console.log('====================================');
        console.log('EDIT DOKUMENTASI');
        console.log('ID Dokumentasi:', id_dokumentasi);
        console.log('====================================');

        //------ Validasi ID
        if (id_dokumentasi === '' || id_dokumentasi === '0') {
            Swal.fire(
                'Oops!',
                'ID dokumentasi tidak ditemukan.',
                'error'
            );
            return;
        }

        //------ Reset & Tampilkan Loading Form
        $('#NotifikasiEditDokumentasi').html('');
        $('#FormEditDokumentasi').html(`
            <div class="text-center text-muted py-4">
                <div class="spinner-border spinner-border-sm" role="status"></div>
                <div class="mt-2">Memuat data dokumentasi...</div>
            </div>
        `);
        $('#edit_id_dokumentasi').val(id_dokumentasi);
        $('#TombolEditDokumentasi').prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Memuat...
        `);
        $('#ModalEditDokumentasi').modal('show');

        //------ Initialize Select2
        InitSelect2EditDokumentasi();

        // ==== AJAX DETAIL DOKUMENTASI
        $.ajax({
            type     : 'POST',
            url      : '_Page/Dokumentasi/_detail_dokumentasi.php',
            data     : { id_dokumentasi: id_dokumentasi },
            dataType : 'json',
            success  : function (response) {
                console.log('Response Detail Edit:', response);

                if (!response || response.status !== 'success') {
                    $('#FormEditDokumentasi').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            ${escapeHtml(response?.message || 'Data dokumentasi tidak ditemukan.')}
                        </div>
                    `);
                    return;
                }

                let detail = response.detail || {};
                let tags   = response.tags || [];

                $('#edit_id_dokumentasi').val(detail.id_dokumentasi || id_dokumentasi);
                $('#judul_dokumentasi_edit').val(detail.judul || '');
                $('#deskripsi_dokumentasi_edit').val(detail.deskripsi || '');

                //------ Select2 Tags
                let $tags = $('#tags_dokumentasi_edit');
                $tags.empty();

                $.each(tags, function (index, item) {
                    let tag = $.trim(String(item.tags || ''));
                    if (tag === '') {
                        return;
                    }

                    let option = new Option(tag, tag, true, true);
                    $tags.append(option);
                });

                $tags.trigger('change');

                //------ Enable Tombol Simpan
                $('#TombolEditDokumentasi').prop('disabled', false).html(`
                    <i class="bi bi-save"></i>
                    Simpan
                `);
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error Edit Dokumentasi:', error);
                console.error('AJAX Status:', status);
                console.error('HTTP Status:', xhr.status);
                console.error('Response:', xhr.responseText);

                let message = 'Gagal mengambil data dokumentasi.';
                if (xhr.responseText) {
                    try {
                        let response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            message = response.message;
                        }
                    } catch (parseError) {
                        console.error('Response bukan JSON valid:', parseError);
                    }
                }

                $('#FormEditDokumentasi').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        ${escapeHtml(message)}
                        <hr>
                        <small>
                            <strong>Debug Information:</strong><br>
                            HTTP Status: ${xhr.status}<br>
                            AJAX Status: ${escapeHtml(status)}<br>
                            Error: ${escapeHtml(error || '-')}
                        </small>
                    </div>
                `);
            },
            complete: function () {
                // Tombol tetap disabled jika terjadi error, hanya aktif pada success
            }
        });
    });

    // ==== PROSES EDIT DOKUMENTASI
    $(document).on('submit', '#ProsesEditDokumentasi', function (e) {
        e.preventDefault();

        let form = this;
        let id_dokumentasi = $('#edit_id_dokumentasi').val();

        //------ Validasi ID
        if (!id_dokumentasi || id_dokumentasi === '0') {
            $('#NotifikasiEditDokumentasi').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    ID dokumentasi tidak valid.
                </div>
            `);
            return;
        }

        //------ Reset notifikasi & siapkan data
        $('#NotifikasiEditDokumentasi').html('');
        let formData = new FormData(form);

        //------ Loading button
        $('#TombolEditDokumentasi').prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Menyimpan...
        `);

        // ==== AJAX REQUEST
        $.ajax({
            type        : 'POST',
            url         : '_Page/Dokumentasi/ProsesEditDokumentasi.php',
            data        : formData,
            processData : false,
            contentType : false,
            dataType    : 'json',
            success     : function (response) {
                console.log('Response Edit Dokumentasi:', response);

                if (response && response.status === true) {
                    $('#NotifikasiEditDokumentasi').html(``);

                    //------ Refresh tabel jika ada
                    ShowData();

                    //------ Refresh detail jika sedang terbuka
                    if (typeof ShowDetail === 'function' && $('#put_id_dokumentasi').val() == id_dokumentasi) {
                        ShowDetail(id_dokumentasi);
                    }

                    //------ Tutup modal
                    $('#ModalEditDokumentasi').modal('hide');

                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );
                } else {
                    $('#NotifikasiEditDokumentasi').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            ${escapeHtml(response?.message || 'Dokumentasi gagal diperbarui.')}
                        </div>
                    `);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error Edit Dokumentasi:', error);
                console.error('Status:', status);
                console.error('HTTP Status:', xhr.status);
                console.error('Response:', xhr.responseText);

                let message = 'Terjadi kesalahan pada server.';
                if (xhr.responseText) {
                    try {
                        let response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            message = response.message;
                        }
                    } catch (e) {
                        console.error('Response bukan JSON valid:', e);
                    }
                }

                $('#NotifikasiEditDokumentasi').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        ${escapeHtml(message)}
                        <hr>
                        <small>
                            HTTP Status: ${xhr.status}<br>
                            AJAX Status: ${escapeHtml(status)}<br>
                            Error: ${escapeHtml(error || '-')}
                        </small>
                    </div>
                `);
            },
            complete: function () {
                $('#TombolEditDokumentasi').prop('disabled', false).html(`
                    <i class="bi bi-save"></i>
                    Simpan
                `);
            }
        });
    });

    // =========================================================
    // HAPUS DOKUMENTASI - BUKA MODAL
    // =========================================================
    
    $(document).on('click', '.hapus_dokumentasi, .hapus_dokumentasi2', function (e) {
        e.preventDefault();
        let id_dokumentasi = '';
        if ($(this).hasClass('hapus_dokumentasi2')) {

            // Dipanggil dari halaman/detail dokumentasi
            id_dokumentasi = $('#put_id_dokumentasi').val();

        } else {

            // Dipanggil dari tabel dokumentasi
            id_dokumentasi = $(this).data('id');
        }

        // Pastikan ID menjadi string bersih
        id_dokumentasi = $.trim(String(id_dokumentasi || ''));

        console.log('Hapus Dokumentasi');
        console.log('ID Dokumentasi:', id_dokumentasi);
        
        //------ Validasi ID
        if (!id_dokumentasi) {
            Swal.fire(
                'Oops!',
                'ID dokumentasi tidak ditemukan.',
                'error'
            );
            return;
        }

        //------ Reset & tampilkan loading modal
        $('#NotifikasiHapusDokumentasi').html('');
        $('#FormHapusDokumentasi').html(`
            <div class="text-center text-muted py-4">
                <div class="spinner-border spinner-border-sm"></div>
                <div class="mt-2">Memuat data dokumentasi...</div>
            </div>
        `);
        $('#hapus_id_dokumentasi').val(id_dokumentasi);
        $('#ButtonHapusDokumentasi').prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Memuat...
        `);
        $('#ModalHapusDokumentasi').modal('show');

        // ==== AJAX DETAIL DOKUMENTASI
        $.ajax({
            type     : 'POST',
            url      : '_Page/Dokumentasi/_detail_dokumentasi.php',
            data     : { id_dokumentasi: id_dokumentasi },
            dataType : 'json',
            success  : function (response) {
                console.log('Response Detail Hapus:', response);

                if (!response || response.status !== 'success') {
                    $('#FormHapusDokumentasi').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            ${escapeHtml(response?.message || 'Data dokumentasi tidak ditemukan.')}
                        </div>
                    `);
                    return;
                }

                let detail = response.detail || {};
                let tags   = response.tags || [];

                $('#hapus_id_dokumentasi').val(detail.id_dokumentasi || id_dokumentasi);

                //------ Buat daftar tag
                let htmlTags = '';
                $.each(tags, function (index, item) {
                    htmlTags += `
                        <span class="badge bg-success-subtle text-success border border-success rounded-pill me-1">
                            <i class="bi bi-tag"></i>
                            ${escapeHtml(item.tags)}
                        </span>
                    `;
                });

                //------ Tampilkan informasi detail
                $('#FormHapusDokumentasi').html(`
                    <div class="row mb-2">
                        <div class="col-5"><small>Judul</small></div>
                        <div class="col-7"><small class="text text-muted">${detail.judul}</small></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5"><small>Author</small></div>
                        <div class="col-7"><small class="text text-muted">${detail.author_name}</small></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5"><small>Tanggal</small></div>
                        <div class="col-7"><small class="text text-muted">${detail.creat_at}</small></div>
                    </div>
                    <div class="alert alert-warning mt-3">
                        <small>
                            Data yang sudah dihapus tidak dapat dikembalikan.<br>
                            <b>Apakah Anda yakin ingin menghapus dokumentasi ini?</b>
                        </small>
                    </div>
                `);

                //------ Enable tombol hapus
                $('#ButtonHapusDokumentasi').prop('disabled', false).html(`
                    <i class="bi bi-check"></i>
                    Ya, Hapus
                `);
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error Hapus Dokumentasi:', error);
                console.error('HTTP Status:', xhr.status);
                console.error('Response:', xhr.responseText);

                $('#FormHapusDokumentasi').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Gagal mengambil data dokumentasi.
                        <hr>
                        <small>
                            HTTP ${xhr.status}<br>
                            Status: ${escapeHtml(status)}<br>
                            Error: ${escapeHtml(error || '-')}
                        </small>
                    </div>
                `);
            },
            complete: function () {
                $('#ButtonHapusDokumentasi').prop('disabled', false).html(`
                    <i class="bi bi-check"></i>
                    Ya, Hapus
                `);
            }
        });
    });


    // ==== PROSES HAPUS DOKUMENTASI
    $(document).on('submit', '#ProsesHapusDokumentasi', function (e) {
        e.preventDefault();

        let form = this;
        let id_dokumentasi = $('#hapus_id_dokumentasi').val();

        //------ Validasi ID
        if (!id_dokumentasi || id_dokumentasi === '0') {
            $('#NotifikasiHapusDokumentasi').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    ID dokumentasi tidak valid.
                </div>
            `);
            return;
        }

        //------ Reset notifikasi & siapkan data
        $('#NotifikasiHapusDokumentasi').html('');
        let formData = new FormData(form);

        //------ Loading button
        $('#ButtonHapusDokumentasi').prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Menghapus...
        `);

        // ==== AJAX REQUEST
        $.ajax({
            type        : 'POST',
            url         : '_Page/Dokumentasi/ProsesHapusDokumentasi.php',
            data        : formData,
            processData : false,
            contentType : false,
            dataType    : 'json',
            success     : function (response) {
                console.log('Response Hapus Dokumentasi:', response);

                if (response && response.status === true) {
                    $('#NotifikasiHapusDokumentasi').html(``);
                    $('#ModalHapusDokumentasi').modal('hide');
                    // Tampilkan 'tabel_view'
                    $('#tabel_view').show();

                    // Sembunyikan 'tabel_view'
                    $('#detail_view').hide();

                    // Tampilkan Tabel
                    ShowData();
                    //------ Kosongkan detail jika yang dihapus sedang terbuka
                    if ($('#put_id_dokumentasi').val() == id_dokumentasi) {
                        $('#put_id_dokumentasi').val('');
                        $('#put_judul').html('-');
                        $('#put_deskripsi').html('-');
                        $('#put_author').html('-');
                        $('#put_status').html('');
                        $('#put_tags').html('');
                        $('.put_list_konten').html(`
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-file-earmark-x fs-1"></i>
                                <div class="mt-2">Dokumentasi sudah dihapus.</div>
                            </div>
                        `);
                    }

                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil dihapus.'
                    );
                } else {
                    $('#NotifikasiHapusDokumentasi').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            ${escapeHtml(response?.message || 'Dokumentasi gagal dihapus.')}
                        </div>
                    `);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error Hapus Dokumentasi:', error);
                console.error('HTTP Status:', xhr.status);
                console.error('Response:', xhr.responseText);

                let message = 'Terjadi kesalahan pada server.';
                if (xhr.responseText) {
                    try {
                        let response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            message = response.message;
                        }
                    } catch (e) {
                        console.error('Response bukan JSON valid:', e);
                    }
                }

                $('#NotifikasiHapusDokumentasi').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        ${escapeHtml(message)}
                        <hr>
                        <small>
                            HTTP Status: ${xhr.status}<br>
                            AJAX Status: ${escapeHtml(status)}<br>
                            Error: ${escapeHtml(error || '-')}
                        </small>
                    </div>
                `);
            },
            complete: function () {
                $('#ButtonHapusDokumentasi').prop('disabled', false).html(`
                    <i class="bi bi-check"></i>
                    Ya, Hapus
                `);
            }
        });
    });
});
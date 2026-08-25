// Load Tahun
function LoadTahunGrafik() {

    // Variabel Tahun
    const $tahun = $('#tahun');
    $tahun.html(`<option value="">Tahun Sekarang</option>`);

    // Ambil Data Dari Database Dengan AJAX
    $.ajax({
        url     : '_Page/RekapTransaksi/OptionTahun.php',
        type    : 'GET',
        dataType: 'json',
        success: function(response) {

            if (!Array.isArray(response)) {
                return;
            }
            $.each(response, function(index, tahun) {
                $tahun.append(
                    $('<option>', {
                        value: tahun,
                        text: tahun
                    })
                );

            });
        },
        error: function(xhr) {
            console.error(
                'Gagal mengambil tahun:',
                xhr.responseText
            );
        }
    });
}

// Fungsi Format Rupiah
function FormatRupiah(value) {
    value = Number(value) || 0;
    return 'Rp ' + value.toLocaleString('id-ID');
}

// ==== MENAMPILKAN GRAFIK
// ==== MENAMPILKAN GRAFIK
function ShowGrafik() {

    //------ Tangkap Filter
    const tahun              = $('#tahun').val();
    const id_transaksi_jenis = $('#id_transaksi_jenis').val();

    //------ Tampilkan Loading
    $('#GrafikTransaksi').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2 text-muted">Memuat grafik...</div>
        </div>
    `);

    // ==== AJAX REQUEST
    $.ajax({
        url     : '_Page/RekapTransaksi/ShowGrafik.php',
        type    : 'POST',
        dataType: 'json',
        data    : {
            tahun: tahun,
            id_transaksi_jenis: id_transaksi_jenis
        },
        success : function(response) {

            //------ Validasi Response
            if (!response.status) {
                $('#GrafikTransaksi').html(`
                    <div class="alert alert-danger">
                        ${response.message ?? 'Gagal mengambil data grafik.'}
                    </div>
                `);
                return;
            }

            //------ Hapus Grafik Lama
            if (window.ChartGrafikTransaksi) {
                window.ChartGrafikTransaksi.destroy();
                window.ChartGrafikTransaksi = null;
            }

            //------ Validasi Data
            if (!Array.isArray(response.data) || response.data.length === 0) {
                $('#GrafikTransaksi').html(`
                    <div class="alert alert-info text-center">
                        Tidak ada data transaksi untuk ditampilkan.
                    </div>
                `);
                return;
            }

            //------ Inisialisasi Data Grafik
            const categories     = [];
            const series_subtotal  = [];
            const series_pembayaran = [];
            const series_lunas      = [];
            const series_utang      = [];
            const series_piutang    = [];

            //------ Mapping Data
            $.each(response.data, function(index, data) {

                categories.push(data.bulan);

                series_subtotal.push(Number(data.subtotal) || 0);
                series_pembayaran.push(Number(data.pembayaran) || 0);
                series_lunas.push(Number(data.lunas) || 0);
                series_utang.push(Number(data.utang) || 0);
                series_piutang.push(Number(data.piutang) || 0);

            });

            //------ Judul Periode
            const tahunGrafik = response.tahun;

            const judulPeriode = 'Tahun ' + tahunGrafik;

            // ==== KONFIGURASI APEXCHARTS
            const options = {
                chart: {
                    type: 'bar',
                    height: 450,
                    toolbar: {
                        show: true
                    },
                    animations: {
                        enabled: true
                    }
                },

                title: {
                    text: 'Grafik Transaksi - ' + judulPeriode,
                    align: 'left',
                    style: {
                        fontSize: '16px',
                        fontWeight: '600'
                    }
                },

                series: [
                    {
                        name: 'Subtotal',
                        data: series_subtotal
                    },
                    {
                        name: 'Pembayaran',
                        data: series_pembayaran
                    },
                    {
                        name: 'Lunas',
                        data: series_lunas
                    },
                    {
                        name: 'Utang',
                        data: series_utang
                    },
                    {
                        name: 'Piutang',
                        data: series_piutang
                    }
                ],

                xaxis: {
                    categories: categories,
                    title: {
                        text: 'Bulan'
                    }
                },

                yaxis: {
                    title: {
                        text: 'Nominal'
                    },
                    labels: {
                        formatter: function(value) {
                            return FormatRupiah(value);
                        }
                    }
                },

                dataLabels: {
                    enabled: false
                },

                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '65%',
                        borderRadius: 4
                    }
                },

                tooltip: {
                    shared: true,
                    intersect: false,
                    y: {
                        formatter: function(value) {
                            return FormatRupiah(value);
                        }
                    }
                },

                legend: {
                    position: 'top',
                    horizontalAlign: 'center'
                },

                stroke: {
                    show: true,
                    width: 1,
                    colors: ['transparent']
                },

                grid: {
                    strokeDashArray: 4
                },

                noData: {
                    text: 'Tidak ada data transaksi'
                }
            };

            //------ Render Grafik
            $('#GrafikTransaksi').html('');

            window.ChartGrafikTransaksi = new ApexCharts(
                document.querySelector('#GrafikTransaksi'),
                options
            );

            window.ChartGrafikTransaksi.render();
        },

        error: function(xhr) {

            console.error(xhr.responseText);

            $('#GrafikTransaksi').html(`
                <div class="alert alert-danger">
                    Terjadi kesalahan saat mengambil data grafik.
                </div>
            `);
        }
    });
}

// ==== MENAMPILKAN TABEL
function ShowTable() {

    //------ Tangkap Filter
    const tahun              = $('#tahun').val();
    const id_transaksi_jenis = $('#id_transaksi_jenis').val();

    //------ Tampilkan Loading
    $('#tabel_transaksi').html(`
        <tr>
            <td colspan="8" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2 text-muted">
                    Memuat data...
                </div>
            </td>
        </tr>
    `);

    // ==== AJAX REQUEST
    $.ajax({
        url     : '_Page/RekapTransaksi/ShowTable.php',
        type    : 'POST',
        dataType: 'json',
        data    : {
            tahun: tahun,
            id_transaksi_jenis: id_transaksi_jenis
        },
        success : function(response) {

            //------ Validasi Response
            if (!response.status) {
                $('#tabel_transaksi').html(`
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="alert alert-danger mb-0">
                                ${response.message ?? 'Gagal mengambil data transaksi.'}
                            </div>
                        </td>
                    </tr>
                `);
                return;
            }

            //------ Validasi Data Kosong
            if (!response.data || response.data.length === 0) {
                $('#tabel_transaksi').html(`
                    <tr>
                        <td colspan="8" class="text-center">
                            <small class="text-muted">
                                Tidak ada data transaksi
                            </small>
                        </td>
                    </tr>
                `);
                return;
            }

            //------ Tampilkan Data
            let html = '';

            $.each(response.data, function(index, data) {

                const isTotal = data.bulan === 'TOTAL';

                if (isTotal) {

                    html += `
                        <tr class="fw-bold table-secondary">
                            <td></td>
                            <td></td>
                            <td class="text-center">
                                TOTAL
                            </td>
                            <td>${FormatRupiah(data.subtotal)}</td>
                            <td>${FormatRupiah(data.pembayaran)}</td>
                            <td>${FormatRupiah(data.lunas)}</td>
                            <td>${FormatRupiah(data.utang)}</td>
                            <td>${FormatRupiah(data.piutang)}</td>
                        </tr>
                    `;

                } else {

                    html += `
                        <tr
                            class="transaksi_rincian"
                            data-id_transaksi_jenis="${id_transaksi_jenis ?? ''}"
                            data-tahun="${data.tahun}"
                            data-bulan="${data.bulan_index}"
                            style="cursor: pointer;"
                        >
                            <td>
                                ${index + 1}
                            </td>
                            <td>
                                ${data.tahun}
                            </td>
                            <td>
                                ${data.bulan}
                            </td>
                            <td>${FormatRupiah(data.subtotal)}</td>
                            <td>${FormatRupiah(data.pembayaran)}</td>
                            <td>${FormatRupiah(data.lunas)}</td>
                            <td>${FormatRupiah(data.utang)}</td>
                            <td>${FormatRupiah(data.piutang)}</td>
                        </tr>
                    `;

                }

            });

            $('#tabel_transaksi').html(html);
        },
        error: function(xhr) {

            console.error(xhr.responseText);

            $('#tabel_transaksi').html(`
                <tr>
                    <td colspan="8" class="text-center">
                        <div class="alert alert-danger mb-0">
                            Terjadi kesalahan saat mengambil data transaksi.
                        </div>
                    </td>
                </tr>
            `);

        }
    });
}

// Menampilkan Form Informasi Export
function ShowFormExportTransaksi() {

    const tahun              = $('#tahun').val();
    const id_transaksi_jenis = $('#id_transaksi_jenis').val();
    const nama_transaksi     = $('#id_transaksi_jenis option:selected').text();

    const tahun_export = tahun !== ''
        ? tahun
        : 'Tahun ' + new Date().getFullYear();

    const jenis_export = (
        id_transaksi_jenis !== '' &&
        nama_transaksi !== '' &&
        nama_transaksi !== 'Semua'
    )
        ? nama_transaksi
        : 'Semua Jenis Transaksi';

    //------ Disable Tombol Selama Proses Render
    $('#TombolExportTransaksi')
        .prop('disabled', true)
        .html(`
            <span class="spinner-border spinner-border-sm me-1"></span>
            Menyiapkan...
        `);

    //------ Tampilkan Loading
    $('#FormExportTransaksi').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2 text-muted">
                Menyiapkan informasi export...
            </div>
        </div>
    `);

    //------ Render Informasi
    setTimeout(function() {

        $('#FormExportTransaksi').html(`
            <input type="hidden" name="tahun" value="${tahun}">
            <input type="hidden" name="id_transaksi_jenis" value="${id_transaksi_jenis}">

            <div class="row mb-2">
                <div class="col-5"><small>Jenis Transaksi</small></div>
                <div class="col-7">
                    <small class="text text-muted">${jenis_export}</small>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>Periode Tahun</small></div>
                <div class="col-7">
                    <small class="text text-muted">${tahun_export}</small>
                </div>
            </div>
            <div class="alert alert-warning">
                <small>
                     <i class="bi bi-shield-check"></i>
                    Pastikan filter yang digunakan sudah sesuai sebelum melakukan export.
                </small>
            </div>
        `);

        //------ Aktifkan Tombol
        $('#TombolExportTransaksi')
            .prop('disabled', false)
            .html(`
                <i class="bi bi-download"></i> Export
            `);

    }, 150);

}

//=====================================
// EVENT LISTENER
//=====================================
$(document).ready(function() {

    // Pertama Kali Halaman Di Load
    LoadTahunGrafik();

    // Select2 Jenis Transaksi
    $('#id_transaksi_jenis').select2({
        theme         : 'bootstrap-5',
        dropdownParent: $('#ModalFilterGrafik'),
        width         : '100%',
        placeholder   : 'Semua jenis transaksi',
        allowClear    : true,
        ajax          : {
            url     : '_Page/RekapTransaksi/SelectTransaksiJenis.php',
            type    : 'GET',
            dataType: 'json',
            delay   : 300,

            data: function(params) {
                return {q: params.term || '', page: params.page || 1};
            },

            processResults: function(data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: data.pagination
                };
            },
            cache: true
        }
    });

    // Proses Submit Filter 'ProsesFilterGrafik'
    $('#ProsesFilterGrafik').on('submit', function(e) {
        e.preventDefault();
        ShowGrafik();
        ShowTable();
        const modalElement = document.getElementById('ModalFilterGrafik');
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }

    });

    ShowGrafik();
    ShowTable();


    // Export Transaksi
    $('#ModalExportTabelTransaksi').on('show.bs.modal', function() {
        ShowFormExportTransaksi();
    });

    // =========================================
    // VARIABEL TRANSAKSI RINCIAN
    // =========================================
    let PageTransaksiRincian = 1;
    let TotalPageTransaksiRincian = 1;

    // =========================================
    // MENAMPILKAN DATA RINCIAN
    // =========================================
    function LoadTransaksiRincian() {
        const id_transaksi_jenis = $('#put_id_transaksi_jenis').val();
        const tahun              = $('#put_tahun').val();
        const bulan              = $('#put_bulan').val();
        const page               = PageTransaksiRincian;

        //------ Disable Tombol Export
        $('#TombolExportTransaksiRincian').prop('disabled', true);

        //------ Tampilkan Loading
        $('#tabel_transaksi_rincian').html(`
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2 text-muted">
                        Memuat data rincian transaksi...
                    </div>
                </td>
            </tr>
        `);

        //------ AJAX REQUEST
        $.ajax({
            url     : '_Page/RekapTransaksi/ShowTransaksiRincian.php',
            type    : 'POST',
            dataType: 'json',
            data    : {
                id_transaksi_jenis: id_transaksi_jenis,
                tahun             : tahun,
                bulan             : bulan,
                page              : page
            },
            success : function(response) {
                //------ Validasi Response
                if (!response.status) {
                    $('#tabel_transaksi_rincian').html(`
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="alert alert-danger mb-0">
                                    ${response.message ?? 'Gagal mengambil data rincian transaksi.'}
                                </div>
                            </td>
                        </tr>
                    `);

                    $('#page_info_rincian').text('Page 0 Of 0');
                    $('#prev_button_rincian, #next_button_rincian').prop('disabled', true);
                    return;
                }

                //------ Simpan & Update Informasi Pagination
                PageTransaksiRincian       = response.pagination.page;
                TotalPageTransaksiRincian  = response.pagination.total_page;

                $('#page_info_rincian').text(
                    'Page ' + PageTransaksiRincian + ' Of ' + TotalPageTransaksiRincian
                );

                $('#prev_button_rincian').prop('disabled', PageTransaksiRincian <= 1);
                $('#next_button_rincian').prop('disabled', PageTransaksiRincian >= TotalPageTransaksiRincian);

                //------ Data Kosong
                if (!response.data || response.data.length === 0) {
                    $('#tabel_transaksi_rincian').html(`
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <small class="text-muted">
                                    Tidak ada data transaksi pada periode ini.
                                </small>
                            </td>
                        </tr>
                    `);
                    return;
                }

                //------ Render Data Trik Tabel
                let html = '';
                $.each(response.data, function(index, data) {
                    const nomor = ((PageTransaksiRincian - 1) * response.pagination.limit) + index + 1;

                    html += `
                        <tr>
                            <td class="text-center">${nomor}</td>
                            <td>${data.tanggal}</td>
                            <td>${data.nama_transaksi}</td>
                            <td>${data.rincian_transaksi ?? '-'}</td>
                            <td class="text-end">${FormatRupiah(data.harga)}</td>
                            <td class="text-center">${data.qty}</td>
                            <td>${data.satuan ?? '-'}</td>
                            <td class="text-end">${FormatRupiah(data.jumlah)}</td>
                        </tr>
                    `;
                });

                $('#tabel_transaksi_rincian').html(html);

                //------ Aktifkan Export
                $('#TombolExportTransaksiRincian').prop('disabled', false);
            },
            error: function(xhr) {
                console.error(xhr.responseText);

                $('#tabel_transaksi_rincian').html(`
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="alert alert-danger mb-0">
                                Terjadi kesalahan saat mengambil data rincian transaksi.
                            </div>
                        </td>
                    </tr>
                `);

                $('#page_info_rincian').text('Page 0 Of 0');
                $('#prev_button_rincian, #next_button_rincian').prop('disabled', true);
            }
        });
    }

    // =========================================
    // CLICK BARIS TRANSAKSI
    // =========================================
    $(document).on('click', '.transaksi_rincian', function() {
        const id_transaksi_jenis = $(this).data('id_transaksi_jenis');
        const tahun              = $(this).data('tahun');
        const bulan              = $(this).data('bulan');

        //------ Simpan Filter ke Input Hidden
        $('#put_id_transaksi_jenis').val(id_transaksi_jenis ?? '');
        $('#put_tahun').val(tahun ?? '');
        $('#put_bulan').val(bulan ?? '');

        //------ Reset Halaman & Input Pagination
        PageTransaksiRincian = 1;
        $('#page_rincian').val(1);
        $('#page_info_rincian').text('Page 1 Of 1');

        $('#prev_button_rincian, #next_button_rincian').prop('disabled', true);

        //------ Tampilkan Modal & Ambil Data
        $('#ModalTransaksiRincian').modal('show');
        LoadTransaksiRincian();
    });

    // =========================================
    // PAGINATION PREVIOUS
    // =========================================
    $(document).on('click', '#prev_button_rincian', function() {
        if (PageTransaksiRincian <= 1) {
            return;
        }

        PageTransaksiRincian--;
        $('#page_rincian').val(PageTransaksiRincian);
        LoadTransaksiRincian();
    });

    // =========================================
    // PAGINATION NEXT
    // =========================================
    $(document).on('click', '#next_button_rincian', function() {
        if (PageTransaksiRincian >= TotalPageTransaksiRincian) {
            return;
        }

        PageTransaksiRincian++;
        $('#page_rincian').val(PageTransaksiRincian);
        LoadTransaksiRincian();
    });

});
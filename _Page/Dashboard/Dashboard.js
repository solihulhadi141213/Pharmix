// Fungsi Untuk Menampilkan Jumlah Medication
function CountMedication() {
    $.ajax({
        type: 'POST',
        url: '_Page/Dashboard/CountMedication.php',
        dataType: "json",
        success: function(response) {
            if (response.status == "Success") {
                $('#put_count_medication').hide().html(response.count_medication).fadeIn(500);
            } else {
                $('#notifikasi_proses').hide().html('<div class="alert alert-danger"><small>' + response.message + '</small></div>').fadeIn(500);
            }
        },
        error: function() {
            $('#notifikasi_proses').hide().html('<div class="alert alert-danger"><small>Terjadi Kesalahan Pada Sistem Saat Menghitung Barang!</small></div>').fadeIn(500);
        },
    });
}

// Fungsi Untuk Menampilkan Jumlah Pasien
function CountPasien() {
    $.ajax({
        type: 'POST',
        url: '_Page/Dashboard/CountPasien.php',
        dataType: "json",
        success: function(response) {
            if (response.status == "Success") {
                $('#put_count_pasien').hide().html(response.count_pasien).fadeIn(500);
            } else {
                $('#notifikasi_proses').hide().html('<div class="alert alert-danger"><small>' + response.message + '</small></div>').fadeIn(500);
            }
        },
        error: function() {
            $('#notifikasi_proses').hide().html('<div class="alert alert-danger"><small>Terjadi Kesalahan Pada Sistem Saat Menghitung Barang!</small></div>').fadeIn(500);
        },
    });
}


// Fungsi Untuk Menampilkan Jumlah Kunjungan
function CountKunjungan() {
    $.ajax({
        type: 'POST',
        url: '_Page/Dashboard/CountKunjungan.php',
        dataType: "json",
        success: function(response) {
            if (response.status == "Success") {
                $('#put_count_kunjungan').hide().html(response.count_kunjungan).fadeIn(500);
            } else {
                $('#notifikasi_proses').hide().html('<div class="alert alert-danger"><small>' + response.message + '</small></div>').fadeIn(500);
            }
        },
        error: function() {
            $('#notifikasi_proses').hide().html('<div class="alert alert-danger"><small>Terjadi Kesalahan Pada Sistem Saat Menghitung Barang!</small></div>').fadeIn(500);
        },
    });
}

// Fungsi Untuk Menampilkan Jumlah Resep
function CountResep() {
    $.ajax({
        type: 'POST',
        url: '_Page/Dashboard/CountResep.php',
        dataType: "json",
        success: function(response) {
            if (response.status == "Success") {
                $('#put_count_resep').hide().html(response.count_resep).fadeIn(500);
            } else {
                $('#notifikasi_proses').hide().html('<div class="alert alert-danger"><small>' + response.message + '</small></div>').fadeIn(500);
            }
        },
        error: function() {
            $('#notifikasi_proses').hide().html('<div class="alert alert-danger"><small>Terjadi Kesalahan Pada Sistem Saat Menghitung Barang!</small></div>').fadeIn(500);
        },
    });
}

// Fungsi Untuk Menampilkan Data Barang
function CountOfBarang() {
    $.ajax({
        type: 'POST',
        url: '_Page/Dashboard/CountOfBarang.php',
        dataType: "json",
        success: function(response) {
            if (response.status == "Success") {
                $('#put_count_rp_barang').hide().html(response.rp_barang).fadeIn(500);
                $('#put_count_item_barang').hide().html(response.item_barang).fadeIn(500);
            } else {
                $('#notifikasi_proses').hide().html('<div class="alert alert-danger"><small>' + response.message + '</small></div>').fadeIn(500);
            }
        },
        error: function() {
            $('#notifikasi_proses').hide().html('<div class="alert alert-danger"><small>Terjadi Kesalahan Pada Sistem Saat Menghitung Barang!</small></div>').fadeIn(500);
        },
    });
}


// Fungsi Untuk Menampilkan Data Penjualan
function CountOfPenjualan() {
    $.ajax({
        type: 'POST',
        url: '_Page/Dashboard/CountOfPenjualan.php',
        dataType: "json",
        success: function(response) {
            if (response.status == "Success") {
                $('#put_nominal_penjualan').hide().html(''+response.put_nominal_penjualan+'').fadeIn(500);
                $('#put_record_penjualan').hide().html(''+response.put_record_penjualan+' Record').fadeIn(500);
                CountOfPembelian();
            } else {
                $('#notifikasi_proses').hide().html('<div class="alert alert-danger"><small>' + response.message + '</small></div>').fadeIn(500);
            }
        },
        error: function() {
            $('#notifikasi_proses').hide().html('<div class="alert alert-danger"><small>Terjadi Kesalahan Pada Sistem Saat Menghitung Penjualan!</small></div>').fadeIn(500);
        },
    });
}

// Fungsi Untuk Menampilkan Data Pembelan
function CountOfPembelian() {
    $.ajax({
        type: 'POST',
        url: '_Page/Dashboard/CountOfPembelian.php',
        dataType: "json",
        success: function(response) {
            if (response.status == "Success") {
                $('#put_nominal_pembelian').hide().html(''+response.put_nominal_pembelian+'').fadeIn(500);
                $('#put_record_pembelian').hide().html(''+response.put_record_pembelian+' Record').fadeIn(500);
            } else {
                $('#notifikasi_proses').hide().html('<div class="alert alert-danger"><small>' + response.message + '</small></div>').fadeIn(500);
            }
        },
        error: function() {
            $('#notifikasi_proses').hide().html('<div class="alert alert-danger"><small>Terjadi Kesalahan Pada Sistem Saat Menghitung Penjualan!</small></div>').fadeIn(500);
        },
    });
}

// Fungsi Untuk Menampilkan Data Transaksi Operasional
function CountOfTransaksiOperasional() {
    $.ajax({
        type: 'POST',
        url: '_Page/Dashboard/CountOfTransaksiOperasional.php',
        dataType: "json",
        success: function(response) {
            if (response.status == "Success") {
                $('#put_nominal_transaksi').hide().html(''+response.put_nominal_transaksi+'').fadeIn(500);
                $('#put_record_transaksi').hide().html(''+response.put_record_transaksi+' Record').fadeIn(500);
                ShowPemberitahuanSistem();
            } else {
                $('#notifikasi_proses').hide().html('<div class="alert alert-danger"><small>' + response.message + '</small></div>').fadeIn(500);
            }
        },
        error: function() {
            $('#notifikasi_proses').hide().html('<div class="alert alert-danger"><small>Terjadi Kesalahan Pada Sistem Saat Menghitung Penjualan!</small></div>').fadeIn(500);
        },
    });
}

// Fungsi Untuk Menampilkan Pemberitahuan Sistem
function ShowPemberitahuanSistem() {
    $.ajax({
        type: 'POST',
        url: '_Page/Dashboard/ShowPemberitahuanSistem.php',
        success: function(response) {
            $('#ShowPemberitahuanSistem').hide().html(response).fadeIn(500);
            
        }
    });
}


// Fungsi Untuk Menampilkan Grafik
function ShowGrafikSiimpanPinjam() {
    // Fungsi untuk mengambil data dari file JSON
    $.getJSON("_Page/Dashboard/GrafikTransaksi.php", function (data) {
        // Mengolah data untuk ApexCharts
        const categories = data.map(item => item.x);
        const PenjualanSeries = data.map(item => parseFloat(item.ySimpanan));
        const PembelianSeries = data.map(item => parseFloat(item.yPinjaman));

        // Konfigurasi grafik
        var options = {
            chart: {
                type: 'area',
                height: 400,
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: false,
                    allowMouseWheelZoom: false
                },
                selection: {
                    enabled: false
                }
            },
            series: [
                {
                    name: 'Penjualan',
                    data: PenjualanSeries
                },
                {
                    name: 'Pembelian',
                    data: PembelianSeries
                }
            ],
            xaxis: {
                categories: categories,
                labels: {
                    formatter: function (value) {
                        return String(value).slice(0, 3);
                    }
                }
            },
            yaxis: {
                labels: {
                    show: false
                }
            },
            tooltip: {
                y: {
                    formatter: function (value) {
                        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
                    }
                }
            },
            dataLabels: {
                enabled: false // Menonaktifkan label nilai pada bar
            }
        };

        // Inisialisasi grafik
        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
    });
}

// Fungsi untuk menampilkan jam digital
function tampilkanJam() {
    const waktu = new Date();
    let jam = waktu.getHours().toString().padStart(2, '0');
    let menit = waktu.getMinutes().toString().padStart(2, '0');
    let detik = waktu.getSeconds().toString().padStart(2, '0');

    $('#jam_menarik').text(`${jam}:${menit}:${detik}`);
}

// Fungsi untuk menampilkan tanggal
function tampilkanTanggal() {
    const waktu = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const tanggal = waktu.toLocaleDateString('id-ID', options);
    
    $('#tanggal_menarik').text(tanggal);
}

// Fungsi Untuk Menampilkan Transaksi Terbaru
function TransaksiTerbaru() {

    // Loading Data
    $('#transaksi_terbaru').html('Loading...');
    $.ajax({
        type: 'POST',
        url: '_Page/Dashboard/TransaksiTerbaru.php',
        success: function(response) {
            $('#transaksi_terbaru').html(response);
        }
    });
}

function escapeDashboardHtml(value) {
    return $('<div>').text(value == null ? '' : value).html();
}

function formatDashboardDate(value) {
    if (!value) return '-';
    const parts = String(value).split('-');
    return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : value;
}

function formatDashboardNumber(value) {
    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(value) || 0);
}

function renderDashboardList(selector, items, renderer, emptyMessage) {
    const $target = $(selector);
    if (!items || items.length === 0) {
        $target.html(`<div class="text-center text-muted py-3"><i class="bi bi-inbox"></i><br>${emptyMessage}</div>`);
        return;
    }
    $target.html(`<div class="list-group list-group-flush">${items.map(renderer).join('')}</div>`);
}

function LoadDashboardPeringatan() {
    $('#barang_expire, #barang_limit, #jatuh_tempo').html('<div class="text-center text-muted py-3">Loading...</div>');

    $.ajax({
        type: 'POST',
        url: '_Page/Dashboard/DataPeringatan.php',
        dataType: 'json',
        success: function (response) {
            if (response.status !== 'Success') {
                const message = escapeDashboardHtml(response.message || 'Gagal memuat data.');
                $('#barang_expire, #barang_limit, #jatuh_tempo').html(`<div class="text-center text-danger py-3">${message}</div>`);
                return;
            }

            renderDashboardList('#barang_expire', response.barang_expire, function (item) {
                return `<div class="list-group-item px-0">
                    <div class="d-flex justify-content-between gap-2">
                        <div><div class="fw-bold text-dark"><small>${escapeDashboardHtml(item.nama_barang)}</small></div>
                        <small class="text-muted">${escapeDashboardHtml(item.kode_barang)} | Batch ${escapeDashboardHtml(item.no_batch)}</small></div>
                        <div class="text-end"><small class="text-danger fw-bold"><i class="bi bi-calendar"></i> ${formatDashboardDate(item.expired_date)}</small>
                        <br><small class="text-muted">${formatDashboardNumber(item.qty_batch)} ${escapeDashboardHtml(item.satuan_barang)}</small></div>
                    </div>
                </div>`;
            }, 'Tidak ada barang yang segera expire.');

            renderDashboardList('#barang_limit', response.barang_limit, function (item) {
                return `<div class="list-group-item px-0">
                    <div class="d-flex justify-content-between gap-2">
                        <div><div class="fw-bold text-dark"><small>${escapeDashboardHtml(item.nama_barang)}</small></div>
                        <small class="text-muted">${escapeDashboardHtml(item.kode_barang)}</small></div>
                        <div class="text-end"><small class="text-warning fw-bold">${formatDashboardNumber(item.stok_barang)} ${escapeDashboardHtml(item.satuan_barang)}</small>
                        <br><small class="text-muted">Min ${formatDashboardNumber(item.stok_minimum)}</small></div>
                    </div>
                </div>`;
            }, 'Tidak ada barang yang hampir habis.');

            renderDashboardList('#jatuh_tempo', response.jatuh_tempo, function (item) {
                return `<div class="list-group-item px-0">
                    <div class="d-flex justify-content-between gap-2">
                        <div><div class="fw-bold text-dark">${escapeDashboardHtml(item.id_transaksi)}</div>
                        <small class="text-muted">${escapeDashboardHtml(item.kategori)}</small></div>
                        <div class="text-end"><small class="text-danger fw-bold">${formatDashboardDate(item.tanggal_tempo)}</small>
                        <br><small class="text-muted">Sisa Rp ${formatDashboardNumber(item.sisa_tagihan)}</small></div>
                    </div>
                </div>`;
            }, 'Tidak ada transaksi yang hampir jatuh tempo.');
        },
        error: function () {
            $('#barang_expire, #barang_limit, #jatuh_tempo').html('<div class="text-center text-danger py-3">Terjadi kesalahan saat memuat data.</div>');
        }
    });
}

function initDashboardSummary() {
    const list = document.getElementById('dashboard-summary-list');
    if (!list) return;

    const previous = document.getElementById('dashboard-summary-prev');
    const next = document.getElementById('dashboard-summary-next');
    const controls = document.querySelector('.dashboard-summary-controls');
    const hint = document.getElementById('dashboard-summary-hint');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    function updateControls() {
        const maxScroll = list.scrollWidth - list.clientWidth;
        controls.hidden = maxScroll <= 2;
        hint.hidden = maxScroll <= 2;
        previous.disabled = list.scrollLeft <= 2;
        next.disabled = list.scrollLeft >= maxScroll - 2;
    }

    function scrollCards(direction) {
        const card = list.querySelector('.dashboard-summary-item');
        if (!card) return;
        const step = card.getBoundingClientRect().width + parseFloat(getComputedStyle(list).columnGap || 0);
        const count = Math.max(1, Math.floor(list.clientWidth / step));
        list.scrollBy({ left: direction * step * count, behavior: reducedMotion.matches ? 'auto' : 'smooth' });
    }

    previous.addEventListener('click', function () { scrollCards(-1); });
    next.addEventListener('click', function () { scrollCards(1); });
    list.addEventListener('scroll', updateControls, { passive: true });
    list.addEventListener('keydown', function (event) {
        if (event.target !== list) return;
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            scrollCards(event.key === 'ArrowLeft' ? -1 : 1);
        }
    });
    if ('ResizeObserver' in window) {
        new ResizeObserver(updateControls).observe(list);
    } else {
        window.addEventListener('resize', updateControls);
    }
    updateControls();
}

$(document).ready(function () {
    initDashboardSummary();
    CountMedication();
    CountPasien();
    CountKunjungan();
    CountResep();
    //Menampilkan Data Pertama Kali
    CountOfBarang();
    CountOfPenjualan();
    CountOfPembelian();
    CountOfTransaksiOperasional();

    // Show grafik
    ShowGrafikSiimpanPinjam();
    TransaksiTerbaru();
    LoadDashboardPeringatan();

    //Jam Menarik
    tampilkanTanggal(); // Tampilkan tanggal saat halaman dimuat
    tampilkanJam();     // Tampilkan jam pertama kali
    setInterval(tampilkanJam, 1000); // Perbarui jam setiap detik

    
});

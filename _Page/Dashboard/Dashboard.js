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
                type: 'bar',
                height: 400
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
                categories: categories
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
                    }
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
                        <div><div class="fw-bold text-dark">${escapeDashboardHtml(item.nama_barang)}</div>
                        <small class="text-muted">${escapeDashboardHtml(item.kode_barang)} | Batch ${escapeDashboardHtml(item.no_batch)}</small></div>
                        <div class="text-end"><small class="text-danger fw-bold">Expire ${formatDashboardDate(item.expired_date)}</small>
                        <br><small class="text-muted">Stok ${formatDashboardNumber(item.qty_batch)} ${escapeDashboardHtml(item.satuan_barang)}</small></div>
                    </div>
                </div>`;
            }, 'Tidak ada barang yang segera expire.');

            renderDashboardList('#barang_limit', response.barang_limit, function (item) {
                return `<div class="list-group-item px-0">
                    <div class="d-flex justify-content-between gap-2">
                        <div><div class="fw-bold text-dark">${escapeDashboardHtml(item.nama_barang)}</div>
                        <small class="text-muted">${escapeDashboardHtml(item.kode_barang)}</small></div>
                        <div class="text-end"><small class="text-warning fw-bold">Stok ${formatDashboardNumber(item.stok_barang)} ${escapeDashboardHtml(item.satuan_barang)}</small>
                        <br><small class="text-muted">Minimum ${formatDashboardNumber(item.stok_minimum)}</small></div>
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

$(document).ready(function () {
    //Menampilkan Data Pertama Kali
    CountOfBarang();
    ShowGrafikSiimpanPinjam();
    TransaksiTerbaru();
    LoadDashboardPeringatan();
    //Jam Menarik
    tampilkanTanggal(); // Tampilkan tanggal saat halaman dimuat
    tampilkanJam();     // Tampilkan jam pertama kali
    setInterval(tampilkanJam, 1000); // Perbarui jam setiap detik

    CountOfPenjualan();
    CountOfPembelian();
    CountOfTransaksiOperasional();
});

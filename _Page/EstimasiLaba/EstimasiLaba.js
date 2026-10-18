// Fungsi Menampilkan Data Laba Penjualan
function ShowDataLaba() {
    var data = $('#ProsesFilterLaba').serialize();
    var target = $('#TabelLabaPenjualan');
    tableLoading('#TableLabaPenjualan', true);
    $.ajax({
        type: 'POST',
        url: '_Page/EstimasiLaba/TabelEstimasiLaba.php',
        data: data,
        success: function(data) {
            target.html(data);
            initResponsiveTable('#TableLabaPenjualan');
        },
        error: function() {
            target.html('<tr class="table-empty"><td colspan="12" class="text-center text-danger"><small>Gagal memuat data.</small></td></tr>');
        },
        complete: function() {
            tableLoading('#TableLabaPenjualan', false);
        }
    });
}

$(document).ready(function() {
    if ($("#TabelLabaPenjualan").length) {
        ShowDataLaba();

        //Ketika Filter Di Submit
        $("#ProsesFilterLaba").on("submit", function (e) {
            //Reset Halaman
            $('#page_laba').val(1);
            
            //Tampilkan Data
            ShowDataLaba();

            //Tutup Modal
            $('#ModalFilterLaba').modal('hide');
        });

        //Event Listener Ketika keyword_by diubah
        $('#keyword_by_laba').change(function(){
            var keyword_by = $('#keyword_by_laba').val();
            $.ajax({
                type 	    : 'POST',
                url 	    : '_Page/Penjualan/FormFilterKeywordLaba.php',
                data 	    :  {keyword_by: keyword_by},
                success     : function(data){
                    $('#FormFilterKeywordLaba').html(data);
                }
            });
        });

        //Event listener ketika proses export
        $("#ProsesExportLaba").on("submit", function (e) {
        
            var periode_1 = $('#periode_1_laba').val();
            var periode_2 = $('#periode_2_laba').val();
            var type_data = $('#type_data_laba').val();
            // Bangun URL dengan parameter
            var url = '_Page/EstimasiLaba/ProsesExportLaba.php?' + 
            'periode_1=' + encodeURIComponent(periode_1) + 
            '&periode_2=' + encodeURIComponent(periode_2) + 
            '&type_data=' + encodeURIComponent(type_data);

            // Buka tab baru dengan URL tersebut
            window.open(url, '_blank');
        });
    }

    
    //Modal Detail
    $('#ModalDetail').on('show.bs.modal', function (e) {
        //Tangkap id_transaksi_jual_beli dari modal detail
        var id_transaksi_jual_beli = $(e.relatedTarget).data('id');
        
        //Buka Detail Barang
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Penjualan/detail_penjualan.php',
            data        : {id_transaksi_jual_beli: id_transaksi_jual_beli},
            dataType    : "json",
            success     : function(response){
                if(response.status=="Success"){

                    var data = response.dataset;
                    var list_rincian = response.list_rincian;
                    
                    //Tempelkan Ke Element
                    $('#FormDetail').html(`
                        <input type="hidden" name="id" value="${id_transaksi_jual_beli}">
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <div class="row mb-2">
                                    <div class="col-4"><small>ID/Kode</small></div>
                                    <div class="col-8">
                                        <small class="text text-muted">${id_transaksi_jual_beli}</small>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><small>Tanggal & Jam</small></div>
                                    <div class="col-8">
                                        <small class="text text-muted">${data.tanggal}</small>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><small>Pasien</small></div>
                                    <div class="col-8">
                                        <small class="text text-muted">${data.nama_anggota}</small>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><small>Kategori</small></div>
                                    <div class="col-8">
                                        <small class="text text-muted">${data.kategori}</small>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><small>Status</small></div>
                                    <div class="col-8">
                                        <small class="text text-muted">${data.status}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row mb-2">
                                    <div class="col-4"><small>Creat At</small></div>
                                    <div class="col-8">
                                        <small class="text text-muted">${data.creat_at}</small>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><small>Update At</small></div>
                                    <div class="col-8">
                                        <small class="text text-muted">${data.update_at}</small>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><small>Creat By</small></div>
                                    <div class="col-8">
                                        <small class="text text-muted">${data.Creator}</small>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><small>Update By</small></div>
                                    <div class="col-8">
                                        <small class="text text-muted">${data.Updater}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                    var rincianList = response.list_rincian;
                    var html = "";

                    // Inisialisasi total
                    var totalPpn = 0;
                    var totalDiskon = 0;
                    var totalSubtotal = 0;

                    if (rincianList.length > 0) {
                        $.each(rincianList, function(index, item) {
                            html += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.nama_barang}</td>
                                    <td>${item.qty}</td>
                                    <td class="text-end">${item.harga_rp}</td>
                                    <td class="text-end">${item.ppn_rp}</td>
                                    <td class="text-end">${item.diskon_rp}</td>
                                    <td class="text-end">${item.subtotal_rp}</td>
                                </tr>
                            `;

                            // Hitung total
                            totalPpn += parseFloat(item.ppn);
                            totalDiskon += parseFloat(item.diskon);
                            totalSubtotal += parseFloat(item.subtotal);
                        });

                        // Tambahkan baris total di akhir tabel
                        html += `
                            <tr class="fw-bold bg-light">
                                <td colspan="4" class="text-left">TOTAL/TAGIHAN</td>
                                <td class="text-end">Rp ${totalPpn.toLocaleString("id-ID")}</td>
                                <td class="text-end">Rp ${totalDiskon.toLocaleString("id-ID")}</td>
                                <td class="text-end">Rp ${totalSubtotal.toLocaleString("id-ID")}</td>
                            </tr>
                        `;
                        html += `
                            <tr class="fw-bold bg-light">
                                <td colspan="4" class="text-left">UANG/CASH</td>
                                <td class="text-end"></td>
                                <td class="text-end"></td>
                                <td class="text-end">${data.cash_rp.toLocaleString("id-ID")}</td>
                            </tr>
                        `;
                        html += `
                            <tr class="fw-bold bg-light">
                                <td colspan="4" class="text-left">KEMBALIAN</td>
                                <td class="text-end"></td>
                                <td class="text-end"></td>
                                <td class="text-end">${data.kembalian_rp.toLocaleString("id-ID")}</td>
                            </tr>
                        `;
                    } else {
                        html = '<tr><td colspan="7" class="text-center">Tidak ada rincian transaksi</td></tr>';
                    }

                    // Masukkan ke dalam tabel
                    $("#ListDetail").html(html);

                    //Enable tombol
                    $('#ButtonSelengkapnya').prop("disabled", false);
                }else{
                    //Tempelkan Notifikasi
                    $('#FormDetail').html(
                        `<div class="alert alert-danger" role="alert">${response.message}</div>`
                    );
                    //Disable tombol
                    $('#ButtonSelengkapnya').prop("disabled", true);
                }
            },
            error: function () {
                //Tempelkan Notifikasi
                $('#FormDetail').html(
                    '<div class="alert alert-danger" role="alert">Terjadi kesalahan pada sistem. Silakan coba lagi.</div>'
                );
                //Disable tombol
                $('#ButtonSelengkapnya').prop("disabled", true);
            },
        });
    });
});
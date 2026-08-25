<div class="modal fade" id="ModalFilterGrafik" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <form action="javascript:void(0);" id="ProsesFilterGrafik">

                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-funnel"></i> Filter Grafik
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <!-- Jenis Transaksi -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="id_transaksi_jenis">Jenis Transaksi</label>
                            <select name="id_transaksi_jenis" id="id_transaksi_jenis" class="form-control" style="width: 100%;">
                                <option value="">Semua</option>
                            </select>
                            <small class="text-grayish">
                                Pilih jenis transaksi yang ingin ditampilkan
                            </small>
                        </div>
                    </div>


                    <!-- Tahun -->
                    <div class="row mb-3" id="FormTahun">
                        <div class="col-12">
                            <label for="tahun">Tahun Data</label>
                            <select name="tahun" id="tahun" class="form-control"> 
                                <option value="">Semua Tahun</option>
                            </select>
                            <small class="text-grayish">
                                Periode tahun yang ditampilkan
                            </small>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-filter"></i> Tampilkan Grafik
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<div class="modal fade" id="ModalExportTabelTransaksi" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="_Page/RekapTransaksi/ProsesExportTransaksi.php" method="POST" target="_blank" id="FormProsesExportTransaksi">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-download"></i> Export Rekap Transaksi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12" id="FormExportTransaksi">
                            <!-- Form Export Transaksi -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolExportTransaksi" disabled>
                        <i class="bi bi-download"></i> Export
                    </button>

                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Transaksi Rincian -->
<div class="modal fade" id="ModalTransaksiRincian" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <form action="_Page/RekapTransaksi/ProsesExportTransaksiRincian.php" method="POST" target="_blank" class="d-flex flex-column h-100">

                <input type="hidden" name="id_transaksi_jenis" id="put_id_transaksi_jenis" value="">
                <input type="hidden" name="tahun" id="put_tahun" value="">
                <input type="hidden" name="bulan" id="put_bulan" value="">
                <input type="hidden" name="page" id="page_rincian" value="1">

                <!-- HEADER MODAL -->
                <div class="modal-header bg-primary flex-shrink-0">
                    <h5 class="modal-title text-light">
                        <i class="bi bi-list-ul"></i>
                        Rincian Transaksi Operasional
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- BODY MODAL -->
                <div class="modal-body d-flex flex-column overflow-hidden">
                    <!-- AREA TABEL -->
                    <div class="flex-grow-1 overflow-auto">
                        <table class="table table-hover table-striped table-md mb-0">
                            <thead class="table-primary sticky-top">
                                <tr>
                                    <th class="text-center"><b>No</b></th>
                                    <th><b>Tanggal</b></th>
                                    <th><b>Jenis Transaksi</b></th>
                                    <th><b>Uraian/Rincian</b></th>
                                    <th class="text-end"><b>Harga</b></th>
                                    <th class="text-center"><b>QTY</b></th>
                                    <th><b>Satuan</b></th>
                                    <th class="text-end"><b>Subtotal</b></th>
                                </tr>
                            </thead>
                            <tbody id="tabel_transaksi_rincian">
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <small class="text-muted">No Data</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <div class="row align-items-center mt-3 flex-shrink-0">
                        <div class="col-6">
                            <small id="page_info_rincian" class="text-muted">
                                Page 1 Of 1
                            </small>
                        </div>
                        <div class="col-6 text-end">
                            <button type="button" class="btn btn-md btn-outline-info btn-floating" id="prev_button_rincian" disabled>
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button type="button" class="btn btn-md btn-outline-info btn-floating" id="next_button_rincian" disabled>
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- FOOTER MODAL -->
                <div class="modal-footer bg-primary flex-shrink-0">
                    <button type="submit" class="btn btn-success btn-rounded" id="TombolExportTransaksiRincian" disabled>
                        <i class="bi bi-download"></i>
                        Export
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Tutup
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
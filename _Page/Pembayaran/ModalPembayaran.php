<!-- Modal Filter -->
<div class="modal fade" id="ModalFilter" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesFilter">
                <input type="hidden" name="page" id="page" value="1">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-funnel"></i> Filter Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="batas_operasional">
                                <small>Limit/Batas</small>
                            </label>
                            <select name="batas" id="batas_operasional" class="form-control">
                                <option value="5">5</option>
                                <option selected value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="OrderByOperasional">
                                <small>Order By</small>
                            </label>
                            <select name="OrderBy" id="OrderByOperasional" class="form-control">
                                <option value="">Pilih</option>
                                <option value="tanggal">Tanggal</option>
                                <option value="id_transaksi">Ref Operasional</option>
                                <option value="id_transaksi_jual_beli">Ref Jual/Beli</option>
                                <option value="kategori_pembayaran">Pembayaran</option>
                                <option value="kategori_transaksi">Transaksi</option>
                                <option value="jumlah">Nominal</option>
                                <option value="creat_by_name">Petugas</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="ShortBy">
                                <small>Short By</small>
                            </label>
                            <select name="ShortBy" id="ShortBy" class="form-control">
                                <option value="DESC">Z To A</option>
                                <option value="ASC">A To Z</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="keyword_by">
                                <small>Keyword By</small>
                            </label>
                            <select name="keyword_by" id="keyword_by" class="form-control">
                                <option value="">Pilih</option>
                                <option value="tanggal">Tanggal</option>
                                <option value="id_transaksi">Ref Operasional</option>
                                <option value="id_transaksi_jual_beli">Ref Jual/Beli</option>
                                <option value="kategori_pembayaran">Pembayaran</option>
                                <option value="kategori_transaksi">Transaksi</option>
                                <option value="creat_by_name">Petugas</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12" id="FormFilter">
                            <label for="keyword">
                                <small>Keyword</small>
                            </label>
                            <input type="text" name="keyword" id="keyword" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
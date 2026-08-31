<!-- Modal Filter Buku Besar -->
<div class="modal fade" id="ModalFilter" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesFilter">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-funnel"></i> Filter Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="id_perkiraan">Akun Perkiraan</label>
                            <select name="id_perkiraan" id="id_perkiraan" class="form-control">
                                <?php
                                    echo '<option value="">Pilih</option>';
                                    // Query untuk mengambil akun level 1 (group utama)
                                    $QryGroupUtama = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE level='1' ORDER BY nama");
                                    while ($GroupUtama = mysqli_fetch_array($QryGroupUtama)) {
                                        $id_perkiraan_utama = $GroupUtama['id_perkiraan'];
                                        $kode_utama = $GroupUtama['kode'];
                                        $nama_utama = $GroupUtama['nama'];
                                        $saldo_normal_utama = $GroupUtama['saldo_normal'];
                                        // Tampilkan group utama
                                        echo '<optgroup label="'.$nama_utama.' ('.$saldo_normal_utama.')">';
                                        // Query untuk mengambil anak group dari group utama berdasarkan kode
                                        $QryAnakGroup = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE kode LIKE '$kode_utama%' AND level != '1' ORDER BY nama");
                                        while ($AnakGroup = mysqli_fetch_array($QryAnakGroup)) {
                                            $id_perkiraan_anak = $AnakGroup['id_perkiraan'];
                                            $nama_anak = $AnakGroup['nama'];
                                            $saldo_normal_anak = $AnakGroup['saldo_normal'];
                                            $kode = $AnakGroup['kode'];
                                            $level = $AnakGroup['level'];
                                            $LevelTerbawah = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akun_perkiraan WHERE kd$level='$kode'"));
                                            // Tampilkan anak group
                                            if($LevelTerbawah=="1"){
                                                echo '<option value="'.$id_perkiraan_anak.'">'.$nama_anak.' ('.$saldo_normal_anak.')</option>';
                                            }
                                        }
                                        echo '</optgroup>';
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="periode1">Periode Awal</label>
                            <input type="date" name="periode1" id="periode1" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="periode2">Periode Akhir</label>
                            <input type="date" name="periode2" id="periode2" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiFilter">
                            <!-- Notifikasi Filter Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-check"></i> Tampilkan
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Export -->
<div class="modal fade" id="ModalExport" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="_Page/BukuBesar/ProsesExport.php" method="POST" target="_blank">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-download"></i> Export Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormExport">
                            <!-- Notifikasi Filter Disini -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiExport">
                            <!-- Notifikasi Filter Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolExport">
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

<div class="modal fade" id="ModalDetailTransaksi" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-light"><i class="bi bi-box"></i> Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="FormDetailTransaksi">
                
            </div>
        </div>
    </div>
</div>
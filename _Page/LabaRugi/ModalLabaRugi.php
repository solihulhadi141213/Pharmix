<?php
    $esc = static function ($value): string {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };

    $html_akun = '<option value="">Pilih</option>';

    $QryAkun = mysqli_query($Conn, "
        SELECT 
            id_perkiraan,
            kode,
            nama,
            saldo_normal
        FROM akun_perkiraan
        WHERE level = 1
        ORDER BY kode ASC, nama ASC
    ");

    if ($QryAkun) {
        while ($DataAkun = mysqli_fetch_assoc($QryAkun)) {

            $id_perkiraan = $DataAkun['id_perkiraan'] ?? '';
            $kode         = $DataAkun['kode'] ?? '';
            $nama         = $DataAkun['nama'] ?? '';
            $saldo_normal = $DataAkun['saldo_normal'] ?? '';

            $html_akun .= '
                <option value="' . $esc($id_perkiraan) . '">
                    ' . $esc($kode) . ' - ' .
                    $esc($nama) . ' (' .
                    $esc($saldo_normal) . ')
                </option>
            ';
        }
    }
?>

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
                            <label for="akun_pemasukan">Akun Pemasukan</label>
                            <select name="akun_pemasukan" id="akun_pemasukan" class="form-control">
                                <?= $html_akun ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="akun_pengeluaran">Akun Pengeluaran</label>
                            <select name="akun_pengeluaran" id="akun_pengeluaran" class="form-control">
                                <?= $html_akun ?>
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
            <form action="_Page/LabaRugi/ProsesExport.php" method="POST" target="_blank">
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

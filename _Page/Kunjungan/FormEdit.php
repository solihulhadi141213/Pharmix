<?php
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

header('Content-Type: application/json');

if (empty($SessionIdAkses)) {
    echo json_encode(["status" => "error", "message" => "Sesi akses berakhir."]);
    exit;
}

$id_kunjungan = $_POST['id_kunjungan'] ?? '';
if (empty($id_kunjungan)) {
    echo json_encode(["status" => "error", "message" => "ID Kunjungan tidak valid."]);
    exit;
}

$query = "SELECT kunjungan.*, 
                 anggota.id_pasien as rm_pasien, 
                 anggota.nama as nama_pasien 
          FROM kunjungan 
          LEFT JOIN anggota ON kunjungan.id_anggota = anggota.id_anggota 
          WHERE kunjungan.id_kunjungan = ?";

$stmt = mysqli_prepare($Conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_kunjungan);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Data tidak ditemukan."]);
    exit;
}

$rawDateTime       = $data['tanggal_kunjungan'];
$tanggal_kunjungan = $rawDateTime ? date('Y-m-d', strtotime($rawDateTime)) : date('Y-m-d');
$jam_kunjungan     = $rawDateTime ? date('H:i', strtotime($rawDateTime)) : date('H:i');

$id_anggota           = $data['id_anggota'];
$label_pasien         = ($data['rm_pasien'] && $data['nama_pasien']) ? $data['rm_pasien'] . ' - ' . $data['nama_pasien'] : 'Pilih Pasien';

$priority             = $data['priority'];
$keluhan              = $data['keluhan'];
$jenis_kunjungan      = $data['jenis_kunjungan'];

$id_dokter_penerima   = $data['id_dokter_penerima'] ?? '';
$kode_dokter_penerima = $data['kode_dokter_penerima'] ?? '';
$nama_dokter_penerima = $data['nama_dokter_penerima'] ?? '';
$label_dokter_penerima= ($kode_dokter_penerima && $nama_dokter_penerima) ? $kode_dokter_penerima . ' - ' . $nama_dokter_penerima : 'Pilih';

$id_dpjp              = $data['id_dpjp'] ?? '';
$kode_dpjp            = $data['kode_dpjp'] ?? '';
$nama_dpjp            = $data['nama_dpjp'] ?? '';
$label_dpjp           = ($kode_dpjp && $nama_dpjp) ? $kode_dpjp . ' - ' . $nama_dpjp : 'Pilih';

$id_poli              = $data['id_poli'] ?? '';
$kode_poli            = $data['kode_poli'] ?? '';
$nama_poli            = $data['nama_poli'] ?? '';
$label_poli           = ($kode_poli && $nama_poli) ? $kode_poli . ' - ' . $nama_poli : 'Pilih';

$kelas_inap           = $data['kelas_inap'];
$ruang_inap           = $data['ruang_inap'];
$status               = $data['status'];

$html = '
    <input type="hidden" name="id_kunjungan" value="'.$id_kunjungan.'">

    <div class="row mb-3">
        <div class="col-md-12">
            <label for="edit_id_anggota"><span class="text-danger">*</span> Nama Pasien</label>
            <select name="id_anggota" id="edit_id_anggota" class="form-control" required>
                <option value="'.$id_anggota.'" selected>'.$label_pasien.'</option>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12">
            <label for="edit_tanggal_kunjungan"><span class="text-danger">*</span>Tanggal & Jam Kunjungan</label>
            <div class="input-group">
                <input type="date" name="tanggal_kunjungan" id="edit_tanggal_kunjungan" class="form-control" value="'.$tanggal_kunjungan.'" required>
                <input type="time" name="jam_kunjungan" id="edit_jam_kunjungan" class="form-control" value="'.$jam_kunjungan.'" required>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12">
            <label for="edit_priority"><span class="text-danger">*</span> <i>Priority</i></label>
            <select name="priority" id="edit_priority" class="form-control" required>
                <option value="Normal" '.($priority == 'Normal' ? 'selected' : '').'>Normal</option>
                <option value="Urgent" '.($priority == 'Urgent' ? 'selected' : '').'>Urgent</option>
                <option value="Emergency" '.($priority == 'Emergency' ? 'selected' : '').'>Emergency</option>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12">
            <label for="edit_keluhan">Keluhan Pasien</label>
            <textarea name="keluhan" id="edit_keluhan" class="form-control">'.$keluhan.'</textarea>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12">
            <label for="edit_jenis_kunjungan"><span class="text-danger">*</span> <i>Jenis Kunjungan</i></label>
            <select name="jenis_kunjungan" id="edit_jenis_kunjungan" class="form-control" required>
                <option value="AMB" '.($jenis_kunjungan == 'AMB' ? 'selected' : '').'>Rawat Jalan</option>
                <option value="IMP" '.($jenis_kunjungan == 'IMP' ? 'selected' : '').'>Rawat Inap</option>
                <option value="EMER" '.($jenis_kunjungan == 'EMER' ? 'selected' : '').'>Emergency / Gawat Darurat</option>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12" id="EditDokterPenerima">
            <label for="edit_id_dokter_penerima">Dokter Penerima</label>
            <input type="hidden" name="kode_dokter_penerima" id="edit_kode_dokter_penerima" value="'.$kode_dokter_penerima.'">
            <input type="hidden" name="nama_dokter_penerima" id="edit_nama_dokter_penerima" value="'.$nama_dokter_penerima.'">
            <select name="id_dokter_penerima" id="edit_id_dokter_penerima" class="form-control">
                <option value="'.$id_dokter_penerima.'" selected>'.$label_dokter_penerima.'</option>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12" id="EditDokterDpjp">
            <label for="edit_id_dpjp">Dokter DPJP</label>
            <input type="hidden" name="kode_dpjp" id="edit_kode_dpjp" value="'.$kode_dpjp.'">
            <input type="hidden" name="nama_dpjp" id="edit_nama_dpjp" value="'.$nama_dpjp.'">
            <select name="id_dpjp" id="edit_id_dpjp" class="form-control">
                <option value="'.$id_dpjp.'" selected>'.$label_dpjp.'</option>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12" id="EditFormPoli">
            <label for="edit_id_poli">Poliklinik</label>
            <input type="hidden" name="kode_poli" id="edit_kode_poli" value="'.$kode_poli.'">
            <input type="hidden" name="nama_poli" id="edit_nama_poli" value="'.$nama_poli.'">
            <select name="id_poli" id="edit_id_poli" class="form-control">
                <option value="'.$id_poli.'" selected>'.$label_poli.'</option>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12">
            <label for="edit_kelas_inap">Kelas Inap</label>
            <input type="text" name="kelas_inap" id="edit_kelas_inap" class="form-control" value="'.$kelas_inap.'">
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12">
            <label for="edit_ruang_inap">Ruang Inap</label>
            <input type="text" name="ruang_inap" id="edit_ruang_inap" class="form-control" value="'.$ruang_inap.'">
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12">
            <label for="edit_status">Status Kunjungan</label>
            <select name="status" id="edit_status" class="form-control" required>
                <option value="planned" '.($status == 'planned' ? 'selected' : '').'>Planned</option>
                <option value="arrived" '.($status == 'arrived' ? 'selected' : '').'>Arrived</option>
                <option value="triaged" '.($status == 'triaged' ? 'selected' : '').'>Triaged</option>
                <option value="in-progress" '.($status == 'in-progress' ? 'selected' : '').'>In-Progress</option>
                <option value="onleave" '.($status == 'onleave' ? 'selected' : '').'>Onleave</option>
                <option value="finished" '.($status == 'finished' ? 'selected' : '').'>Finished</option>
                <option value="cancelled" '.($status == 'cancelled' ? 'selected' : '').'>Cancelled</option>
                <option value="entered-in-error" '.($status == 'entered-in-error' ? 'selected' : '').'>Entered in error</option>
                <option value="unknown" '.($status == 'unknown' ? 'selected' : '').'>Unknown</option>
            </select>
        </div>
    </div>
';

echo json_encode(["status" => "success", "html" => $html]);
?>
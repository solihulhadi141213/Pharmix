 <div class="row mb-3">
    <div class="col-md-12">
        <label for="medication_category_manual">
            Kategori Index
        </label>
        <select name="medication_category" id="medication_category_manual" class="form-control" required>
            <option value="">Pilih Kategori</option>
            <option value="Obat">Obat</option>
            <option value="Alkes">Alkes</option>
        </select>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <label for="medication_name_manual">Nama <i>Medication</i></label>
        <input type="text" disabled name="medication_name" id="medication_name_manual" class="form-control" required>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <label for="kfa_manual">Kamus Farmasi/Alkes (KFA)</label>
        <select disabled name="kfa" id="kfa_manual" class="form-control">
            <option value="">Pilih</option>
        </select>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="sediaan_manual">Sediaan</label>
        <select disabled name="sediaan" id="sediaan_manual" class="form-control" required>
            <option value="">Pilih</option>
        </select>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="manufaktur_manual"><i>Manufacturer</i></label>
        <select disabled name="manufaktur" id="manufaktur_manual" class="form-control">
            <option value="">Pilih</option>
        </select>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="racikan_code">Racikan / Non Racikan</label>
        <select name="racikan_code" disabled id="racikan_code" class="form-control">
            <option value="">Pilih</option>
            <option value="NC">Non-compound</option>
            <option value="SD">Gives of such doses</option>
            <option value="EP">Divide into equal parts</option>
        </select>
    </div>
</div>
<div class="row mb-2 mt-3">
    <div class="col-md-12">
        <button type="button" disabled class="btn btn-md btn-block btn-secondary" id="modal_tambah_ingridient">
            <i class="bi bi-plus"></i> Tambah Ingredient
        </button>
    </div>
</div>
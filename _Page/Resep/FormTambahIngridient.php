<div class="row mb-3">
    <div class="col-md-12">
        <label for="ingridient_kfa">
            <small>Nama Obat/Zat</small>
        </label>
        <select name="ingridient_kfa" id="ingridient_kfa" class="form-control"></select>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="jumlah_numerator">
            <small>Jumlah Kandungan Numerator (Pembilang, Contoh : gram, mL, dll)</small>
        </label>
    </div>
    <div class="col-4 col-md-4">
        <input type="number" min="0" step="0.01" class="form-control" name="jumlah_numerator" id="jumlah_numerator">
    </div>
    <div class="col-8 col-md-8">
        <select name="satuan_numerator" id="satuan_numerator" class="form-control"></select>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="jumlah_denominator">
            <small>Jumlah Kandungan Denominator (Penyebut, Contoh : Tablet Sehari, Jam, Tablet)</small>
        </label>
    </div>
    <div class="col-4 col-md-4">
        <input type="number" min="0" step="0.01" class="form-control" name="jumlah_denominator" id="jumlah_denominator">
    </div>
    <div class="col-8 col-md-8">
        <select name="satuan_denominator" id="satuan_denominator" class="form-control"></select>
    </div>
</div>
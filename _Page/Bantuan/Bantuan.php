<?php
    // Menangkap Keyword dengan GET
    $keyword = isset($_GET['keyword']) && is_string($_GET['keyword'])
        ? trim($_GET['keyword'])
        : '';
?>
<div class="pagetitle">
    <h1>
        <a href=""><i class="bi bi-question-circle"></i> Bantuan</a>
    </h1>
    <nav aria-label="Breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Bantuan</li>
        </ol>
    </nav>
</div>
<style>
    .bantuan-page .bantuan-filter {
        border: 1px dashed #b8c7dc;
        border-radius: 18px;
        background: #f8faff;
        box-shadow: none;
    }
    .bantuan-page .bantuan-item {
        border: 1px solid #e4eaf2;
        border-radius: 16px;
        box-shadow: 0 3px 12px rgba(32, 56, 85, .04);
    }
    .bantuan-page .bantuan-description { line-height: 1.7; }
</style>
<section class="section bantuan-page">
    <div class="row">
        <div class="col-12">
            <div class="card bantuan-filter mb-4">
                <div class="card-body p-3 p-md-4">
                    <h2 class="h6 fw-bold mb-2"><i class="bi bi-search me-1"></i> Temukan bantuan</h2>
                    <p class="small text-muted mb-3">Cari panduan atau pilih topik yang ingin Anda pelajari.</p>
                    <form action="javascript:void(0);" id="ProsesFilter">
                        <input type="hidden" name="page" id="page" value="1">
                        <input type="hidden" name="tags" id="tags" value="">
                        <label for="keyword" class="visually-hidden">Pencarian bantuan</label>
                        <div class="input-group mb-4">
                            <input type="text" name="keyword" id="keyword" class="form-control" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" placeholder="Cari judul atau deskripsi bantuan...">
                            <button type="submit" class="btn btn-primary px-3 px-md-4"><i class="bi bi-search me-1"></i> Cari</button>
                        </div>
                    </form>
                    <div class="small fw-bold text-muted mb-2">Jelajahi topik</div>
                    <div id="tags_list" aria-live="polite"><small class="text-muted">Memuat tags...</small></div>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h6 fw-bold mb-0">Daftar Bantuan</h2>
                <span class="small text-muted">Panduan penggunaan aplikasi</span>
            </div>
            <div id="data_view" aria-live="polite">
                <div class="text-center text-muted py-4">Memuat daftar bantuan...</div>
            </div>
            <nav id="bantuan_pagination" class="d-flex align-items-center justify-content-center gap-3 py-3" aria-label="Halaman bantuan">
                <button type="button" class="btn btn-outline-primary btn-floating" id="prev_button" aria-label="Halaman sebelumnya" disabled>
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                </button>
                <span class="small text-muted" id="page_info" aria-live="polite">Page 1 Of 1</span>
                <button type="button" class="btn btn-outline-primary btn-floating" id="next_button" aria-label="Halaman berikutnya" disabled>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </button>
            </nav>
        </div>
    </div>
</section>

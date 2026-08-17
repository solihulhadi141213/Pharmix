<!-- ======= Footer ======= -->

<!-- Back to top -->
<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<!-- Toast -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="appToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i id="appToastIcon" class="bi"></i> <strong id="appToastTitle" class="me-auto"></strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div id="appToastMessage" class="toast-body"></div>
    </div>
</div>

<!-- Vendor JS Files -->
<script src="node_modules/signature_pad/dist/signature_pad.umd.min.js"></script>
<script src="node_modules/apexcharts/dist/apexcharts.min.js"></script>
<script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="node_modules/quill/quill.js"></script>
<script src="node_modules/jquery/dist/jquery.min.js" type="text/javascript"></script>
<script src="node_modules/jQuery-Mask-Plugin/dist/jquery.mask.min.js"></script>
<script src="node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
<script src="assets/js/jquery.session.js" type="text/javascript"></script>
<script src="node_modules/html2canvas/dist/html2canvas.min.js"></script>
<script src="node_modules/jspdf/dist/jspdf.umd.min.js"></script>

<!-- Custome JS -->
<script src="assets/js/main.js?v=<?php echo date('YmdHis'); ?>"></script>

<script type="text/javascript">
    $(document).ready(function(){
        // Format mata uang.
        $( '#kembalian' ).mask('000.000.000.000', {reverse: true});
        $( '#pembayaran' ).mask('000.000.000.000', {reverse: true});
        $( '#jumlah_transaksi' ).mask('000.000.000.000', {reverse: true});
        $( '#jumlah_transaksi_edit' ).mask('000.000.000.000', {reverse: true});
        $( '#pembayaran_edit' ).mask('000.000.000.000', {reverse: true});
        $( '#kembalian_edit' ).mask('000.000.000.000', {reverse: true});
        $( '.format_uang' ).mask('000.000.000.000', {reverse: true});
    })
</script>

<!-- Scan QR -->
<script src="node_modules/jsqr/dist/jsQR.js"></script>

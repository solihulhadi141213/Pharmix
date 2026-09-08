//Notification First Time
$('#MenampilkanBelNotifikasi').load('_Partial/ReloadBelNotification.php');
$('#MenampilkanBelNotifikasiPesan').load('_Partial/ReloadBelNotificationPesan.php');

//Reload Notification
$(document).ready(function() {
    function ReloadBelNotification() {
        $('#MenampilkanBelNotifikasi').load('_Partial/ReloadBelNotification.php');
    }
    function ReloadBelNotificationPesan() {
        $('#MenampilkanBelNotifikasiPesan').load('_Partial/ReloadBelNotificationPesan.php');
    }
    // setInterval(ReloadBelNotification, 5000);
    setInterval(ReloadBelNotificationPesan, 5000);
});

//Kondisi Ketika Uraian Notifikasi Di Klik
$('#MenampilkanBelNotifikasi').click(function(){
    $('#MenampilkanNotificationList').html('<li class="dropdown-header">Loading...</li>');
    $('#MenampilkanNotificationList').load('_Partial/NotificationList.php');
});

//Kondisi Ketika Uraian Notifikasi Pesan Di Klik
$('#MenampilkanBelNotifikasiPesan').click(function(){
    $('#MenampilkanListNotifikasiPesan').html('<li class="dropdown-header">Loading...</li>');
    $('#MenampilkanListNotifikasiPesan').load('_Partial/NotificationListPesan.php');
});

// Fungsi Menampilkan Toast
function showToast(type, title, message) {

    const toast = $('#appToast');
    const icon = $('#appToastIcon');

    // Reset
    toast.removeClass(
        'toast-success toast-error toast-warning toast-info'
    );

    icon.removeClass();

    // Tipe toast
    switch (type) {

        case 'success':
            toast.addClass('toast-success');
            icon.addClass('bi bi-check-circle-fill');
            break;

        case 'error':
            toast.addClass('toast-error');
            icon.addClass('bi bi-x-circle-fill');
            break;

        case 'warning':
            toast.addClass('toast-warning');
            icon.addClass('bi bi-exclamation-triangle-fill');
            break;

        case 'info':
            toast.addClass('toast-info');
            icon.addClass('bi bi-info-circle-fill');
            break;
    }

    $('#appToastTitle').text(title);
    $('#appToastMessage').text(message);

    const toastElement = document.getElementById('appToast');

    const bsToast = bootstrap.Toast.getOrCreateInstance(
        toastElement,
        {
            delay: 1500
        }
    );

    bsToast.show();
}
// ============================================================
// RESPONSIVE TABLE
// ============================================================
function initResponsiveTable(selector = '.table-responsive-card') {
    $(selector).each(function() {
        const table  = $(this);
        const labels = [];

        table.find('thead th').each(function() {
            labels.push($(this).text().trim());
        });

        table.find('tbody tr').each(function() {
            const row   = $(this);
            const cells = row.find('td');

            // Baris kosong atau menggunakan colspan
            if (cells.length === 1 && cells.first().is('[colspan]')) {
                row.addClass('table-empty');
                cells.first().removeAttr('data-label');
                return;
            }

            row.removeClass('table-empty');

            cells.each(function(index) {
                $(this).attr('data-label', labels[index] || '');
            });
        });
    });
}
// ============================================================
// GLOBAL TABLE LOADING
// ============================================================
function tableLoading(tableSelector, status = true, message = 'Memuat data...') {
    const table     = $(tableSelector);
    const container = table.closest('.table-load-container');

    if (!table.length || !container.length) {
        return;
    }

    if (!container.find('.table-loading-overlay').length) {
        container.append(`
            <div class="table-loading-overlay">
                <div class="table-loading-content">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                    <span class="ms-2 table-loading-message"></span>
                </div>
            </div>
        `);
    }

    container
        .find('.table-loading-message')
        .text(message);

    container
        .toggleClass('table-loading', status)
        .attr('aria-busy', status ? 'true' : 'false');
}


// ============================================================
// SCROLL TOP
// ============================================================
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}
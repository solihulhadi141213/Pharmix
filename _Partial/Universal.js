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
            delay: 4000
        }
    );

    bsToast.show();
}
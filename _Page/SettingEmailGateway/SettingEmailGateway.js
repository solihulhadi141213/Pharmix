// Handle Submit Setting Email Gateway
$('#ProsesSettingEmail').submit(function(e){
    e.preventDefault();

    // Bersihkan notifikasi sebelumnya
    $('#NotifikasiSimpanSettingEmail').html('');

    // Tombol submit
    let tombol = $('#ButtonSimpanSettingEmail');
    let tombol_asli = tombol.html();

    // Disable tombol dan tampilkan loading
    tombol.prop('disabled', true);
    tombol.html('<i class="bi bi-hourglass-split"></i> Menyimpan...');

    $.ajax({
        url: '_Page/SettingEmailGateway/ProsesSettingEmail.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',

        success: function(response){

            // Kembalikan tombol
            tombol.prop('disabled', false);
            tombol.html(tombol_asli);

            if(response.status === 'success'){

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    confirmButtonText: 'OK'
                });

            }else{

                $('#NotifikasiSimpanSettingEmail').html(
                    '<div class="alert alert-danger alert-dismissible fade show" role="alert">'+
                        response.message+
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'+
                    '</div>'
                );

            }
        },

        error: function(xhr, status, error){

            // Kembalikan tombol
            tombol.prop('disabled', false);
            tombol.html(tombol_asli);

            $('#NotifikasiSimpanSettingEmail').html(
                '<div class="alert alert-danger alert-dismissible fade show" role="alert">'+
                    'Terjadi kesalahan pada server. (' + error + ')' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'+
                '</div>'
            );
        }
    });
});

// Handle Test Kirim Email
$('#ProsesTestKirimEmail').submit(function(e){
    e.preventDefault();

    // Kosongkan log sebelumnya
    $('#NotifikasiTestKirimEmail').html('');

    // Tombol submit
    let tombol = $('#ButtonTestKirimEmail');
    let tombol_asli = tombol.html();

    // Loading
    tombol.prop('disabled', true);
    tombol.html(
        '<span class="spinner-border spinner-border-sm me-2"></span> Mengirim...'
    );

    $.ajax({
        url: '_Page/SettingEmailGateway/ProsesKirimEmail.php',
        type: 'POST',
        data: $(this).serialize(),

        success: function(response){

            $('#NotifikasiTestKirimEmail').html(
                '<div class="alert alert-info mb-0" style="white-space: pre-wrap;">' +
                    response +
                '</div>'
            );

            // Scroll otomatis ke bawah jika log panjang
            $('#NotifikasiTestKirimEmail').scrollTop(
                $('#NotifikasiTestKirimEmail')[0].scrollHeight
            );

            // Kembalikan tombol
            tombol.prop('disabled', false);
            tombol.html(tombol_asli);
        },

        error: function(xhr, status, error){

            $('#NotifikasiTestKirimEmail').html(
                '<div class="alert alert-danger mb-0">' +
                    'Gagal terhubung ke server.<br>' +
                    error +
                '</div>'
            );

            // Kembalikan tombol
            tombol.prop('disabled', false);
            tombol.html(tombol_asli);
        }
    });
});
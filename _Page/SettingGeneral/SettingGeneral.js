// Informasi Umum
$('#ProsesSettingGeneral').submit(function(e){
    e.preventDefault();

    $('#NotifikasiSimpanSettingGeneral').html('');

    let tombol = $('#ButtonSimpanSettingGeneral');
    let tombol_asli = tombol.html();

    tombol.prop('disabled', true);
    tombol.html('<i class="bi bi-hourglass-split"></i> Menyimpan...');

    $.ajax({
        url: '_Page/SettingGeneral/ProsesSettingGeneral.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',

        success: function(response){

            tombol.prop('disabled', false);
            tombol.html(tombol_asli);

            if(response.success){

                Swal.fire({
                    icon             : 'success',
                    title            : 'Berhasil',
                    text             : response.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });

            }else{

                $('#NotifikasiSimpanSettingGeneral').html(
                    '<div class="alert alert-danger">'+
                        response.message+
                    '</div>'
                );

            }
        },

        error: function(){

            tombol.prop('disabled', false);
            tombol.html(tombol_asli);

            $('#NotifikasiSimpanSettingGeneral').html(
                '<div class="alert alert-danger">'+
                    'Terjadi kesalahan pada server.'+
                '</div>'
            );

        }
    });
});

// Favicon
$('#ProsesUpdateFavicon').submit(function(e){
    e.preventDefault();

    $('#NotifikasiUpdateFavicon').html('');

    let tombol      = $('#ButtonUpdateFavicon');
    let tombol_asli = tombol.html();

    tombol.prop('disabled', true);
    tombol.html('<i class="bi bi-hourglass-split"></i> Uploading...');

    let formData = new FormData(this);

    $.ajax({
        url        : '_Page/SettingGeneral/ProsesUpdateFavicon.php',
        type       : 'POST',
        data       : formData,
        processData: false,
        contentType: false,
        dataType   : 'json',

        success: function(response){

            tombol.prop('disabled', false);
            tombol.html(tombol_asli);

            if(response.success){

                Swal.fire({
                    icon             : 'success',
                    title            : 'Berhasil',
                    text             : response.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });

                $('#FaviconPreview').load(
                    location.href + ' #FaviconPreview>*'
                );

            }else{

                $('#NotifikasiUpdateFavicon').html(
                    '<div class="alert alert-danger">'+
                        response.message+
                    '</div>'
                );

            }
        },

        error: function(){

            tombol.prop('disabled', false);
            tombol.html(tombol_asli);

            $('#NotifikasiUpdateFavicon').html(
                '<div class="alert alert-danger">'+
                    'Terjadi kesalahan pada server.'+
                '</div>'
            );

        }
    });
});

// Logo
$('#ProsesUpdateLogo').submit(function(e){
    e.preventDefault();

    $('#NotifikasiUpdateLogo').html('');

    let tombol      = $('#ButtonUpdateLogo');
    let tombol_asli = tombol.html();

    tombol.prop('disabled', true);
    tombol.html('<i class="bi bi-hourglass-split"></i> Uploading...');

    let formData = new FormData(this);

    $.ajax({
        url        : '_Page/SettingGeneral/ProsesUpdateLogo.php',
        type       : 'POST',
        data       : formData,
        processData: false,
        contentType: false,
        dataType   : 'json',

        success: function(response){

            tombol.prop('disabled', false);
            tombol.html(tombol_asli);

            if(response.success){

                Swal.fire({
                    icon             : 'success',
                    title            : 'Berhasil',
                    text             : response.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });

                $('#LogoPreview').load(
                    location.href + ' #LogoPreview>*'
                );

            }else{

                $('#NotifikasiUpdateLogo').html(
                    '<div class="alert alert-danger">'+
                        response.message+
                    '</div>'
                );

            }
        },

        error: function(){

            tombol.prop('disabled', false);
            tombol.html(tombol_asli);

            $('#NotifikasiUpdateLogo').html(
                '<div class="alert alert-danger">'+
                    'Terjadi kesalahan pada server.'+
                '</div>'
            );

        }
    });
});
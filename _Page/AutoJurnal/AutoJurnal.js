// =======================================
// FUNCTION
// =======================================

//Menampilkan Setting Autojurnal
function ShowPembayaran() {
    // Target And Filter
    let target = $('#list_auto_jurnal');

    target.addClass('blur-loading');

    $.ajax({
        type    : 'POST',
        url     : '_Page/AutoJurnal/TabelAutoJurnal.php',
        dataType: 'JSON',
        success : function(res) {

            if(res.status === "success"){

                target.fadeOut(150, function () {
                    target.html(res.html).fadeIn(150);
                });

            }else{
                target.html(res.html);
            }

            target.removeClass('blur-loading');
        }
    });
}

// =======================================
// EVENT LISTENER
// =======================================
$(document).ready(function() {

    // Pada Pertama Kali Muncul
    ShowPembayaran();

    // Modal Edit Auto Jurnal
    $('#ModalEdit').on('show.bs.modal', function (e) {
        
        //Tangkap 'id_autojurnal_jual_beli' 
        var id_autojurnal_jual_beli = $(e.relatedTarget).data('id');

        // Load Form
        $('#FormEdit').html("Loading...");

        //Disable tombol
        $('#ButtonEdit').prop("disabled", true);

        //Buka Detail Barang
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/AutoJurnal/FormEdit.php',
            data        : {id_autojurnal_jual_beli: id_autojurnal_jual_beli},
            dataType    : "JSON",
            success     : function(response){

                // Jika Berhasil
                if(response.status=="success"){
                    var html = response.html;

                    //Tempelkan Detail
                    $('#FormEdit').html(html);

                    // Enable Tombol
                    $('#ButtonEdit').prop("disabled", false);
                }else{
                    //Tempelkan ke 'FormDetailTransaksiJualBeli'
                    $('#FormEdit').html(
                        `
                            <div class="alert alert-danger text-center" role="alert">
                                <small>
                                    <b>Opsss!</b><br>
                                    Terjadi kesalahan pada sistem. <br>
                                    ${response.message}
                                </small>
                            </div>
                        `
                    );
                    
                    //Disable tombol
                    $('#ButtonEdit').prop("disabled", true);
                }
            },
            error: function () {
                //Tempelkan ke 'FormDetailTransaksiJualBeli'
                $('#FormEdit').html(
                    `
                        <div class="alert alert-danger text-center" role="alert">
                            <small>
                                <b>Opsss!</b><br>
                                Terjadi kesalahan pada sistem. Silahkan Coba Lagi<br>
                            </small>
                        </div>
                    `
                );

                //Disable tombol
                $('#ButtonEdit').prop("disabled", true);
            },
        });
    });

    // Proses Submit Edit Auto Jurnal
    $("#ProsesEdit").on("submit", function (e) {
        e.preventDefault();
        
        // Ubah tombol jadi loading
        let $btn = $("#ButtonEdit");
        let originalBtnHtml = $btn.html();
        $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...').prop("disabled", true);
        
        // Kosongkan area notifikasi sebelumnya
        $("#NotifikasiEdit").html('');

        // Ambil data dari form
        let formData = new FormData(this);

        // Kirim data via AJAX
        $.ajax({
            url         : "_Page/AutoJurnal/ProsesEdit.php",
            type        : "POST",
            data        : formData,
            contentType : false,
            processData : false,
            dataType    : "json",
            success     : function (response) {
                if (response.status === "success") {

                    // Tutup Modal Edit
                    $('#ModalEdit').modal('hide');
                    
                    // Tampilkan Toast Sukses (Opsional, sesuaikan dengan fungsi toast aplikasi Anda)
                    if (typeof showToast === 'function') {
                        showToast('success', 'Berhasil', response.message);
                    } else {
                        alert(response.message);
                    }

                    // Reload ulang daftar auto jurnal
                    ShowPembayaran(); // Sesuaikan dengan fungsi load tabel Anda (misal: ShowAutoJurnal())
                    
                } else {
                    // Tampilkan pesan error di dalam modal
                    $("#NotifikasiEdit").html(`
                        <div class="alert alert-danger text-center py-2" role="alert">
                            <small><b>Opsss!</b><br>${response.message}</small>
                        </div>
                    `);
                }
                // Kembalikan tombol ke semula
                $btn.html(originalBtnHtml).prop("disabled", false);
            },
            error: function () {
                $("#NotifikasiEdit").html(`
                    <div class="alert alert-danger text-center py-2" role="alert">
                        <small><b>Opsss!</b><br>Terjadi kesalahan pada sistem. Silakan coba lagi.</small>
                    </div>
                `);
                $btn.html(originalBtnHtml).prop("disabled", false);
            }
        });
    });
});


//Proses Auto Jurnal Simpan, Pinjam
$('#ProsesAutoJurnal').submit(function(){
    $('#NotifikasiSimpanAutoJurnal').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');
    var form = $('#ProsesAutoJurnal')[0];
    var data = new FormData(form);
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/AutoJurnal/ProsesAutoJurnal.php',
        data 	    :  data,
        cache       : false,
        processData : false,
        contentType : false,
        enctype     : 'multipart/form-data',
        success     : function(data){
            $('#NotifikasiSimpanAutoJurnal').html(data);
            var NotifikasiSimpanAutoJurnalBerhasil=$('#NotifikasiSimpanAutoJurnalBerhasil').html();
            if(NotifikasiSimpanAutoJurnalBerhasil=="Success"){
                location.reload();
            }
        }
    });
});

//Proses Auto Jurnal Jual/Beli
$('#ProssesSimpanAutoJurnalJualBeli').submit(function(){
    $('#NotifikasiSimpanAutoJurnalJualBeli').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');
    var form = $('#ProssesSimpanAutoJurnalJualBeli')[0];
    var data = new FormData(form);
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/AutoJurnal/ProssesSimpanAutoJurnalJualBeli.php',
        data 	    :  data,
        cache       : false,
        processData : false,
        contentType : false,
        enctype     : 'multipart/form-data',
        success     : function(data){
            $('#NotifikasiSimpanAutoJurnalJualBeli').html(data);
            var NotifikasiSimpanAutoJurnalJualBeliBerhasil=$('#NotifikasiSimpanAutoJurnalJualBeliBerhasil').html();
            if(NotifikasiSimpanAutoJurnalJualBeliBerhasil=="Berhasil"){
                location.reload();
            }
        }
    });
});

//Proses Auto Jurnal SHU
$('#ProssesSimpanAutoJurnalShu').submit(function(){
    $('#NotifikasiSimpanAutoJurnalShu').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');
    var form = $('#ProssesSimpanAutoJurnalShu')[0];
    var data = new FormData(form);
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/AutoJurnal/ProssesSimpanAutoJurnalShu.php',
        data 	    :  data,
        cache       : false,
        processData : false,
        contentType : false,
        enctype     : 'multipart/form-data',
        success     : function(data){
            $('#NotifikasiSimpanAutoJurnalShu').html(data);
            var NotifikasiSimpanAutoJurnalShuBerhasil=$('#NotifikasiSimpanAutoJurnalShuBerhasil').html();
            if(NotifikasiSimpanAutoJurnalShuBerhasil=="Berhasil"){
                location.reload();
            }
        }
    });
});
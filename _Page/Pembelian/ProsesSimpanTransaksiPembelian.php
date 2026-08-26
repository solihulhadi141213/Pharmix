<?php
    // Koneksi
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Time Now Tmp
    $now = date('Y-m-d H:i:s');

    // Inisialisasi respons default
    $response = [
        "status" => "Error",
        "message" => "Belum ada proses yang dilakukan pada sistem."
    ];

    // Validasi sesi login
    if (empty($SessionIdAkses)) {
        $response = [
            "status" => "Error",
            "message" => "Sesi Akses Sudah Berakhir, Silahkan Login Ulang"
        ];
    } else {
        // Validasi Data Tidak Boleh Kosong
        $requiredFields = [
            'kategori_transaksi'    => "Kategori Transaksi Tidak Boleh Kosong!",
            'tanggal'               => "Tanggal Transaksi Tidak Boleh Kosong!",
            'jam'                   => "Jam Transaksi Tidak Boleh Kosong!",
            'status'                => "Status Transaksi Tidak Boleh Kosong!",
        ];

        foreach ($requiredFields as $field => $errorMessage) {
            if (empty($_POST[$field])) {
                $response = [
                    "status" => "Error",
                    "message" => $errorMessage
                ];
                echo json_encode($response);
                exit;
            }
        }
        // Buat Variabel
        $kategori_transaksi = validateAndSanitizeInput($_POST['kategori_transaksi']);
        $tanggal            = validateAndSanitizeInput($_POST['tanggal']);
        $jam                = validateAndSanitizeInput($_POST['jam']);
        $tanggal            = "$tanggal $jam";
        $status             = validateAndSanitizeInput($_POST['status']);

        //Variabel Lain Yang Tidak Wajib
        if(empty($_POST['put_id_supplier_for_add_pembelian'])){
            $id_supplier=null;
            $validasi_supplier="Valid";
        }else{
            $id_supplier=$_POST['put_id_supplier_for_add_pembelian'];
            $validasi_id_supplier=mysqli_num_rows(mysqli_query($Conn, "SELECT id_supplier FROM supplier WHERE id_supplier='$id_supplier'"));
            if(empty($validasi_id_supplier)){
                $validasi_supplier="ID Anggota Tidak Ditemukan!";
            }else{
                $validasi_supplier="Valid";
            }
        }
        $total     = empty($_POST['total']) ? 0 : validateAndSanitizeInput($_POST['total']);
        $cash      = empty($_POST['cash']) ? 0 : validateAndSanitizeInput($_POST['cash']);
        $kembalian = empty($_POST['kembalian']) ? 0 : validateAndSanitizeInput($_POST['kembalian']);

        //Hapus Titik Pada Nilai Angka Rupiah
        $total = str_replace('.', '', $total);
        $cash = str_replace('.', '', $cash);
        $kembalian = str_replace('.', '', $kembalian);

        //Validasi Supplier
        if($validasi_supplier !== "Valid"){
            $response = [
                "status" => "Error",
                "message" => "$validasi_supplier"
            ];
        } else {

            //Menghitung Data Dari Bulk
            $jumlah_bulk = mysqli_num_rows(mysqli_query($Conn, "SELECT id_transaksi_bulk FROM transaksi_bulk WHERE id_akses='$SessionIdAkses' AND kategori='$kategori_transaksi'"));
            
            if(empty($jumlah_bulk)){
                $response = [
                    "status" => "Error",
                    "message" => "Belum ada data rincian untuk transaksi ini!"
                ];
            } else {

                // ==========================================
                // MULAI TRANSAKSI DATABASE (START TRANSACTION)
                // ==========================================
                mysqli_begin_transaction($Conn);
                $transaction_success = true;
                $error_message = "";

                // Query untuk menghitung total transaksi, total PPN, dan total diskon
                $query_sum = "
                SELECT 
                    SUM(qty * harga) AS total_transaksi, 
                    SUM(ppn) AS total_ppn, 
                    SUM(diskon) AS total_diskon 
                FROM transaksi_bulk 
                WHERE kategori = ? AND id_akses = ?";

                $stmt_sum = $Conn->prepare($query_sum);
                $stmt_sum->bind_param("si", $kategori_transaksi, $SessionIdAkses);
                $stmt_sum->execute();
                $result_sum = $stmt_sum->get_result();
                $row_sum = $result_sum->fetch_assoc();

                $total_transaksi = $row_sum['total_transaksi'] ?? 0;
                $total_ppn       = $row_sum['total_ppn'] ?? 0;
                $total_diskon    = $row_sum['total_diskon'] ?? 0;
                $stmt_sum->close();

                //Buat ID Transaksi (Perbaikan bug variabel $$kode_trans)
                $kode_trans    = "PMB";
                $randome_code  = GenerateKodeBarang(6);
                $milliseconds  = round(microtime(true) * 1000);
                $id_transaksi_jual_beli = "$kode_trans-$milliseconds-$randome_code";
                $id_anggota    = null;

                //Insert Ke Database transaksi_jual_beli
                $query = "INSERT INTO transaksi_jual_beli (
                    id_transaksi_jual_beli, 
                    id_anggota, 
                    id_supplier, 
                    kategori, 
                    tanggal, 
                    subtotal, 
                    diskon, 
                    ppn, 
                    total, 
                    cash, 
                    kembalian, 
                    status,
                    creat_by_id, 
                    creat_by_name, 
                    creat_at, 
                    update_by_id, 
                    update_by_name, 
                    update_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $Conn->prepare($query);
                if ($stmt) {
                    $stmt->bind_param(
                        "ssssssssssssississ",
                        $id_transaksi_jual_beli, 
                        $id_anggota, 
                        $id_supplier, 
                        $kategori_transaksi, 
                        $tanggal,
                        $total_transaksi, 
                        $total_diskon, 
                        $total_ppn, 
                        $total, 
                        $cash, 
                        $kembalian, 
                        $status,
                        $SessionIdAkses, 
                        $SessionNama, 
                        $now, 
                        $SessionIdAkses, 
                        $SessionNama, 
                        $now
                    );
                    
                    if ($stmt->execute()) {
                        $stmt->close();

                        // Ambil data bulk
                        $query_bulk = mysqli_query($Conn, "SELECT * FROM transaksi_bulk WHERE id_akses='$SessionIdAkses' AND kategori='$kategori_transaksi' ORDER BY id_transaksi_bulk DESC");
                        
                        while ($data = mysqli_fetch_array($query_bulk)) {
                            $id_transaksi_bulk = $data['id_transaksi_bulk'];
                            $id_barang         = $data['id_barang'];
                            $nama_barang       = $data['nama_barang'];
                            $satuan            = $data['satuan'];
                            $qty               = $data['qty'];
                            $harga             = $data['harga'];
                            $ppn               = $data['ppn'];
                            $diskon            = $data['diskon'];
                            $subtotal          = $data['subtotal'];

                            // Simpan Data ke tabel transaksi_jual_beli_rincian
                            $query2 = "INSERT INTO transaksi_jual_beli_rincian (
                                id_transaksi_jual_beli, id_barang, nama_barang, satuan, qty, harga, ppn, diskon, subtotal
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            
                            $stmt2 = mysqli_prepare($Conn, $query2);
                            if (!$stmt2) {
                                $transaction_success = false;
                                $error_message = "Gagal mempersiapkan statement rincian.";
                                break;
                            }

                            mysqli_stmt_bind_param($stmt2, "sisssssss", 
                                $id_transaksi_jual_beli, $id_barang, $nama_barang, $satuan, $qty, $harga, $ppn, $diskon, $subtotal
                            );
                            
                            if (mysqli_stmt_execute($stmt2)) {
                                mysqli_stmt_close($stmt2);

                                // Hapus Bulk
                                $HapusBulk = mysqli_query($Conn, "DELETE FROM transaksi_bulk WHERE id_transaksi_bulk='$id_transaksi_bulk'");
                                if ($HapusBulk) {
                                    // Update Stok Barang
                                    $stok_barang_lama = GetDetailData($Conn, 'barang', 'id_barang', $id_barang, 'stok_barang');
                                    $konversi         = GetDetailData($Conn, 'barang', 'id_barang', $id_barang, 'konversi');
                                    $konversi_multi   = GetDetailData($Conn, 'barang_satuan', 'satuan_multi', $satuan, 'konversi_multi');
                                    
                                    if(!empty($konversi_multi)){
                                        $qty_converted = $qty * ($konversi_multi / $konversi);
                                        $stok_barang   = $stok_barang_lama + $qty_converted;
                                    } else {
                                        $stok_barang   = $stok_barang_lama + $qty;
                                    }

                                    $update_barang = mysqli_query($Conn, "UPDATE barang SET stok_barang='$stok_barang' WHERE id_barang='$id_barang'"); 
                                    if(!$update_barang){
                                        $transaction_success = false;
                                        $error_message = "Gagal memperbarui stok barang.";
                                        break;
                                    }
                                } else {
                                    $transaction_success = false;
                                    $error_message = "Gagal menghapus data dari tabel bulk.";
                                    break;
                                }
                            } else {
                                $transaction_success = false;
                                $error_message = "Gagal menyimpan data rincian transaksi.";
                                break;
                            }
                        }

                        // Jika semua rincian & stok aman, lanjut ke Auto Jurnal dan Log
                        if ($transaction_success) {
                            $tanggal_jurnal = date('Y-m-d', strtotime($tanggal));
                            $auto_jurnal    = AutoJurnalJualBeli($Conn, $kategori_transaksi, $tanggal_jurnal, $id_transaksi_jual_beli, $total, $cash, $status);
                            
                            if ($auto_jurnal !== "Success") {
                                $transaction_success = false;
                                $error_message = $auto_jurnal;
                            } else {
                                $kategori_log  = "Transaksi Pembelian";
                                $deskripsi_log = "Tambah Transaksi Pembelian";
                                $InputLog      = addLog($Conn, $SessionIdAkses, $now, $kategori_log, $deskripsi_log);
                                
                                if ($InputLog !== "Success") {
                                    $transaction_success = false;
                                    $error_message = "Terjadi kesalahan pada saat menyimpan log aktivitas.";
                                }
                            }
                        }

                    } else {
                        $transaction_success = false;
                        $error_message = "Terjadi kesalahan pada saat input ke database: " . $stmt->error;
                        $stmt->close();
                    }
                } else {
                    $transaction_success = false;
                    $error_message = "Terjadi kesalahan pada saat mempersiapkan statement database.";
                }

                // ==========================================
                // KEPUTUSAN COMMIT / ROLLBACK TRANSAKSI
                // ==========================================
                if ($transaction_success) {
                    mysqli_commit($Conn);
                    $response = [
                        "status" => "Success",
                        "message" => "Tambah Transaksi Pembelian Berhasil!",
                        "id_transaksi_jual_beli" => $id_transaksi_jual_beli,
                    ];
                } else {
                    mysqli_rollback($Conn);
                    $response = [
                        "status" => "Error",
                        "message" => $error_message
                    ];
                }
            }
        }
    }

    // Output response
    echo json_encode($response);
?>
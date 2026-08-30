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

        if (!in_array($kategori_transaksi, ['Penjualan', 'Retur Penjualan'], true)) {
            $response = [
                "status" => "Error",
                "message" => "Kategori transaksi tidak valid."
            ];
            echo json_encode($response);
            exit;
        }

        //Variabel Lain Yang Tidak Wajib
        if(empty($_POST['put_id_anggota_for_add_penjualan'])){
            $id_anggota=null;
            $validasi_anggota="Valid";
        }else{
            $id_anggota=$_POST['put_id_anggota_for_add_penjualan'];
            if($id_anggota=="1"){
                $validasi_anggota="ID Anggota 1 Terus";
            }else{
                $validasi_id_anggota=mysqli_num_rows(mysqli_query($Conn, "SELECT id_anggota FROM anggota WHERE id_anggota='$id_anggota'"));
                if(empty($validasi_id_anggota)){
                    $validasi_anggota="ID Anggota Tidak Ditemukan!";
                }else{
                    $validasi_anggota="Valid";
                }
            }
        }
        $total     = empty($_POST['total']) ? 0 : validateAndSanitizeInput($_POST['total']);
        $cash      = empty($_POST['cash']) ? 0 : validateAndSanitizeInput($_POST['cash']);
        $kembalian = 0;
        
        //Hapus Titik Pada Nilai Angka Rupiah
        $total = (int) preg_replace('/[^0-9]/', '', (string) $total);
        $cash = (int) preg_replace('/[^0-9]/', '', (string) $cash);

        //Validasi Anggota
        if($validasi_anggota!=="Valid"){
            $response = [
                "status" => "Error",
                "message" => "$validasi_anggota"
            ];
        }else{

            //Menghitung Data Dari Bulk
            $jumlah_bulk=mysqli_num_rows(mysqli_query($Conn, "SELECT id_transaksi_bulk FROM transaksi_bulk WHERE id_akses='$SessionIdAkses' AND kategori='$kategori_transaksi'"));
            
            if(empty($jumlah_bulk)){
                $response = [
                    "status" => "Error",
                    "message" => "Belum ada data rincian untuk transaksi ini!"
                ];
            }else{

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
                    SUM(diskon) AS total_diskon,
                    SUM(subtotal) AS total_tagihan
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
                $total = (int) round((float) ($row_sum['total_tagihan'] ?? 0));
                if ($total <= 0) {
                    mysqli_rollback($Conn);
                    $response = [
                        "status" => "Error",
                        "message" => "Total transaksi tidak valid."
                    ];
                    echo json_encode($response);
                    exit;
                }
                $cash_input = max(0, $cash);
                $cash = min($cash_input, $total);
                $kembalian = max(0, $cash_input - $total);
                if ($cash === $total) {
                    $status = "Lunas";
                } elseif ($kategori_transaksi === "Penjualan") {
                    $status = "Piutang";
                } else {
                    $status = "Utang";
                }
                $stmt_sum->close();

                //Buat ID Transaksi
                $kode_trans             = "PNJ";
                $time_sekarang          = date('ymdHis');
                $randome_code           = GenerateKodeTransaksi();
                $milliseconds           = round(microtime(true) * 1000);
                $id_transaksi_jual_beli = "$kode_trans-$randome_code";
                $id_supplier            = null;

                //Insert Ke Database transaksi_jual_beli
                $query = "INSERT INTO transaksi_jual_beli (
                    id_transaksi_jual_beli, id_anggota, id_supplier, kategori, tanggal, 
                    subtotal, diskon, ppn, total, cash, kembalian, status,
                    creat_by_id, creat_by_name, creat_at, update_by_id, update_by_name, update_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $Conn->prepare($query);
                if ($stmt) {
                    $stmt->bind_param(
                        "ssssssssssssississ",
                        $id_transaksi_jual_beli, $id_anggota, $id_supplier, $kategori_transaksi, $tanggal,
                        $total_transaksi, $total_diskon, $total_ppn, $total, $cash, $kembalian, $status,
                        $SessionIdAkses, $SessionNama, $now, $SessionIdAkses, $SessionNama, $now
                    );
                    
                    if ($stmt->execute()) {
                        $stmt->close();

                        // Jika Berhasil Input transaksi bulk ke transaksi rincian
                        $error_item = [];
                        $item_no = 1;
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

                            //Buka Harga Beli Barang
                            $harga_beli = GetDetailData($Conn, 'barang', 'id_barang', $id_barang, 'harga_beli');
                            if(empty($harga_beli)){
                                $harga_beli = 0;
                            }

                            //Simpan Data ke tabel transaksi_jual_beli_rincian
                            $query2 = "INSERT INTO transaksi_jual_beli_rincian (
                                id_transaksi_jual_beli, id_barang, nama_barang, satuan, qty, hpp, harga, ppn, diskon, subtotal
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            
                            $stmt2 = mysqli_prepare($Conn, $query2);
                            if (!$stmt2) {
                                $transaction_success = false;
                                $error_item[] = "Error Statment in item $item_no";
                                break;
                            } else {
                                mysqli_stmt_bind_param($stmt2, "sissssssss", 
                                    $id_transaksi_jual_beli, $id_barang, $nama_barang, $satuan, $qty, $harga_beli, $harga, $ppn, $diskon, $subtotal
                                );
                            
                                $Input2 = mysqli_stmt_execute($stmt2);
                                mysqli_stmt_close($stmt2);

                                if ($Input2) {
                                    //Jika Berhasil Hapus Bulk
                                    $HapusBulk = mysqli_query($Conn, "DELETE FROM transaksi_bulk WHERE id_transaksi_bulk='$id_transaksi_bulk'");
                                    if ($HapusBulk) {
                                        //Jika Hapus Berhasil Lakukan Update Data Stok Barang
                                        $stok_barang_lama = GetDetailData($Conn, 'barang', 'id_barang', $id_barang, 'stok_barang');
                                        $konversi         = GetDetailData($Conn, 'barang', 'id_barang', $id_barang, 'konversi');
                                        $konversi_multi   = GetDetailData($Conn, 'barang_satuan', 'satuan_multi', $satuan, 'konversi_multi');
                                        
                                        if(!empty($konversi_multi)){
                                            $qty_converted = $qty * ($konversi_multi / $konversi);
                                            if($kategori_transaksi == "Penjualan"){
                                                $stok_barang = $stok_barang_lama - $qty_converted;
                                            } else {
                                                $stok_barang = $stok_barang_lama + $qty_converted;
                                            }
                                        } else {
                                            if($kategori_transaksi == "Penjualan"){
                                                $stok_barang = $stok_barang_lama - $qty;
                                            } else {
                                                $stok_barang = $stok_barang_lama + $qty;
                                            }
                                        }

                                        //Proses Update Stok
                                        $update_barang = mysqli_query($Conn, "UPDATE barang SET stok_barang='$stok_barang' WHERE id_barang='$id_barang'"); 
                                        if(!$update_barang){
                                            $transaction_success = false;
                                            $error_item[] = "Error Update in item $item_no";
                                            break;
                                        }
                                    } else {
                                        $transaction_success = false;
                                        $error_item[] = "Error Delete in item $item_no";
                                        break;
                                    }
                                } else {
                                    $transaction_success = false;
                                    $error_item[] = "Error Input in item $item_no";
                                    break;
                                }
                            }
                            $item_no++;
                        }

                        // Jika rincian & stok sukses, lanjut hitung HPP dan Auto Jurnal
                        if ($transaction_success) {
                            $tanggal_jurnal = date('Y-m-d', strtotime($tanggal));
                            
                            // Simpan Auto Jurnal Berdasarkan Kategori Transaksi
                            $auto_jurnal = AutoJurnalJualBeli($Conn, $kategori_transaksi, $tanggal_jurnal, $id_transaksi_jual_beli, $total, $cash, $status);
                            
                            if($auto_jurnal !== "Success"){
                                $transaction_success = false;
                                $error_message = $auto_jurnal;
                            } else {
                                $kategori_log  = "Transaksi Penjualan";
                                $deskripsi_log = "Tambah Transaksi Penjualan";
                                $InputLog      = addLog($Conn, $SessionIdAkses, $now, $kategori_log, $deskripsi_log);
                                
                                if($InputLog !== "Success"){
                                    $transaction_success = false;
                                    $error_message = "Terjadi kesalahan pada saat menyimpan log aktivitas";
                                }
                            }
                        } else {
                            $error_item_list = implode(',', $error_item);
                            $error_message = "Ada Beberapa Item Barang Yang Gagal Ditangani. Data: $error_item_list";
                        }

                    } else {
                        $transaction_success = false;
                        $error_message = "Terjadi kesalahan pada saat input ke database <br>" . $stmt->error;
                        $stmt->close();
                    }
                } else {
                    $transaction_success = false;
                    $error_message = "Terjadi kesalahan pada saat mempersiapkan statement database";
                }

                // ==========================================
                // KEPUTUSAN COMMIT / ROLLBACK TRANSAKSI
                // ==========================================
                if ($transaction_success) {
                    mysqli_commit($Conn);
                    $response = [
                        "status" => "Success",
                        "message" => "Tambah Transaksi Penjualan Berhasil!",
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

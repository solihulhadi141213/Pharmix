<?php
    use PhpOffice\PhpSpreadsheet\IOFactory;
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    
    require "../../_Config/Connection.php"; // Koneksi database
    require "../../vendor/autoload.php"; // Autoload composer
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!isset($_FILES['file_barang'])) {
            echo '<div class="alert alert-danger">File tidak ditemukan.</div>';
            exit;
        }
    
        $file = $_FILES['file_barang'];
        $allowedTypes = ['xls', 'xlsx'];
        $maxSize = 10 * 1024 * 1024; // 10 MB
    
        // Validasi ekstensi file
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!in_array($ext, $allowedTypes)) {
            echo '<div class="alert alert-danger">Format file tidak valid. Hanya diperbolehkan file Excel (.xls, .xlsx).</div>';
            exit;
        }
    
        // Validasi ukuran file
        if ($file['size'] > $maxSize) {
            echo '<div class="alert alert-danger">Ukuran file terlalu besar. Maksimal 10 MB.</div>';
            exit;
        }
    
        $filePath = $file['tmp_name'];
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        // Baca nilai asli, bukan tampilan angka/mata uang dari format Excel.
        $rows = $sheet->toArray(null, true, false);

        $parseHarga = static function ($nilai) {
            if (is_int($nilai) || is_float($nilai)) {
                return is_int($nilai) ? (string) $nilai : number_format($nilai, 2, '.', '');
            }

            // Harga teks menggunakan format Indonesia: Rp 10.000,00.
            $nilai = preg_replace('/(?:Rp\.?|IDR|\s|\x{00A0})/iu', '', (string) ($nilai ?? ''));
            if (strpos($nilai, ',') !== false && strpos($nilai, '.') !== false) {
                if (strrpos($nilai, ',') > strrpos($nilai, '.')) {
                    $nilai = str_replace(',', '.', str_replace('.', '', $nilai));
                } else {
                    $nilai = str_replace(',', '', $nilai);
                }
            } elseif (preg_match('/^-?\d{1,3}(?:,\d{3})+$/', $nilai)) {
                $nilai = str_replace(',', '', $nilai);
            } elseif (strpos($nilai, ',') !== false) {
                $nilai = str_replace(',', '.', $nilai);
            } elseif (preg_match('/^-?\d{1,3}(?:\.\d{3})+$/', $nilai)) {
                $nilai = str_replace('.', '', $nilai);
            }

            // Bedakan harga nol yang valid dari sel kosong/nilai tidak valid.
            // Kirim DECIMAL sebagai string agar tidak kehilangan presisi melalui float.
            if (preg_match('/^\+?\d+(?:\.\d+)?$/', $nilai)) {
                return ltrim($nilai, '+');
            }
            return null;
        };
    
        if (count($rows) <= 1) {
            echo '<div class="alert alert-danger">File Excel kosong atau tidak sesuai format.</div>';
            exit;
        }
    
        echo '<table class="table table-bordered table-sm">';
        echo '<thead><tr><th>No</th><th>Kode Barang</th><th>Nama Barang</th><th>Kategori</th><th>Satuan</th><th>Stock</th><th>Harga Beli</th><th>Status</th></tr></thead>';
        echo '<tbody>';
    
        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Lewati baris pertama (judul kolom)
    
            $kode_barang     = trim($row[1] ?? '');
            $nama_barang     = trim($row[2] ?? '');
            $kategori_barang = trim($row[3] ?? '');
            $stok_barang     = is_numeric($row[4]) ? intval($row[4]) : 0;
            $satuan_barang   = trim($row[5] ?? '');
            $harga_beli      = $parseHarga($row[6] ?? null);
    
            // Validasi kelengkapan data
            if (empty($kode_barang) || empty($nama_barang) || empty($kategori_barang) || empty($satuan_barang)) {
                echo '<tr class="table-warning"><td colspan="8">Data pada baris '.($index+1).' tidak valid: Kode Barang, Nama Barang, Kategori, dan Satuan wajib diisi.</td></tr>';
                continue;
            }
    
            // Cek apakah kode barang sudah ada di database
            $stmt = $Conn->prepare("SELECT id_barang FROM barang WHERE kode_barang = ? LIMIT 1");
            $stmt->bind_param("s", $kode_barang);
            $stmt->execute();
            $stmt->bind_result($id_barang);
            $barang_sudah_ada = $stmt->fetch() === true;
            $stmt->close();

            if ($harga_beli === null) {
                if ($barang_sudah_ada) {
                    echo '<tr class="table-warning"><td colspan="8">Baris '.($index+1).': Harga Beli kosong atau tidak valid. Pembaruan dilewati agar harga lama tidak tertimpa.</td></tr>';
                    continue;
                }
                if (trim((string) ($row[6] ?? '')) !== '') {
                    echo '<tr class="table-warning"><td colspan="8">Baris '.($index+1).': Format Harga Beli tidak valid. Data tidak diimpor.</td></tr>';
                    continue;
                }
                $harga_beli = '0';
            }
    
            if ($barang_sudah_ada) {
                // Barang lama hanya diperbarui harganya.
                $stmt = $Conn->prepare("UPDATE barang SET harga_beli = ? WHERE id_barang = ?");
                $stmt->bind_param("si", $harga_beli, $id_barang);
            } else {
                // Insert data ke tabel barang
                $konversi = 1;
                $stok_minimum = 0;
                $stmt = $Conn->prepare("INSERT INTO barang (kode_barang, nama_barang, kategori_barang, satuan_barang, konversi, harga_beli, stok_barang, stok_minimum) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssisii", $kode_barang, $nama_barang, $kategori_barang, $satuan_barang, $konversi, $harga_beli, $stok_barang, $stok_minimum);
            }
    
            if ($stmt->execute()) {
                if (!$barang_sudah_ada) {
                    $id_barang = $stmt->insert_id;
                }
                $stmt->close();
                $harga_berhasil = true;
                $hasil_harga = [];
    
                // Simpan harga multi berdasarkan kategori
                for ($i = 7; $i < count($row); $i++) {
                    $kategori_harga = trim((string) ($sheet->getCell([$i + 1, 1])->getValue() ?? '')); // Ambil nama kategori dari header
                    if ($kategori_harga === '') {
                        continue; // Lewati kolom harga tanpa kategori pada header
                    }
                    $harga = $parseHarga($row[$i] ?? null);
                    if ($harga === null) {
                        $harga_berhasil = false;
                        $hasil_harga[] = $kategori_harga.': nilai tidak valid/kosong (dilewati)';
                        continue;
                    }
    
                    // Cari id_barang_kategori_harga berdasarkan kategori_harga
                    $stmt2 = $Conn->prepare("SELECT id_barang_kategori_harga FROM barang_kategori_harga WHERE kategori_harga = ?");
                    $stmt2->bind_param("s", $kategori_harga);
                    $stmt2->execute();
                    $result2 = $stmt2->get_result();
                    $row2 = $result2->fetch_assoc();
                    $stmt2->close();
    
                    if ($row2) {
                        $id_barang_kategori_harga = $row2['id_barang_kategori_harga'];
    
                        // Perbarui harga kategori yang ada, atau tambahkan jika belum ada.
                        $stmt3 = $Conn->prepare("SELECT COUNT(*) FROM barang_harga WHERE id_barang = ? AND id_barang_kategori_harga = ?");
                        $stmt3->bind_param("ii", $id_barang, $id_barang_kategori_harga);
                        $stmt3->execute();
                        $stmt3->bind_result($jumlah_harga);
                        $stmt3->fetch();
                        $stmt3->close();

                        if ($jumlah_harga > 0) {
                            $stmt3 = $Conn->prepare("UPDATE barang_harga SET harga = ? WHERE id_barang = ? AND id_barang_kategori_harga = ?");
                            $stmt3->bind_param("sii", $harga, $id_barang, $id_barang_kategori_harga);
                        } else {
                            $stmt3 = $Conn->prepare("INSERT INTO barang_harga (id_barang, id_barang_kategori_harga, harga) VALUES (?, ?, ?)");
                            $stmt3->bind_param("iis", $id_barang, $id_barang_kategori_harga, $harga);
                        }
                        if (!$stmt3->execute()) {
                            $harga_berhasil = false;
                            $hasil_harga[] = $kategori_harga.': gagal disimpan';
                        } else {
                            $hasil_harga[] = $kategori_harga.': '.$harga;
                        }
                        $stmt3->close();
                    } else {
                        $harga_berhasil = false;
                        $hasil_harga[] = $kategori_harga.': kategori tidak ditemukan (dilewati)';
                    }
                }
    
                $status = $barang_sudah_ada ? 'Harga berhasil diperbarui' : 'Berhasil diimport';
                if (!$harga_berhasil) {
                    $status = 'Data barang tersimpan, tetapi sebagian harga kategori kosong, tidak valid, atau gagal disimpan';
                }
                if ($hasil_harga) {
                    $status .= '<br>'.htmlspecialchars(implode('; ', $hasil_harga), ENT_QUOTES, 'UTF-8');
                }
                echo '<tr class="'.($harga_berhasil ? 'table-success' : 'table-warning').'">';
                echo "<td>{$row[0]}</td><td>{$kode_barang}</td><td>{$nama_barang}</td><td>{$kategori_barang}</td><td>{$satuan_barang}</td><td>{$stok_barang}</td><td>{$harga_beli}</td><td>{$status}</td>";
                echo '</tr>';
            } else {
                $stmt->close();
                echo '<tr class="table-danger"><td colspan="8">Gagal mengimport data pada baris '.($index+1).'</td></tr>';
            }
        }
    
        echo '</tbody></table>';
    } else {
        echo '<div class="alert alert-danger">Metode request tidak valid.</div>';
    }    

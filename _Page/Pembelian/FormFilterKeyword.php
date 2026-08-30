<?php
    // ============================================================
    // KONEKSI
    // ============================================================
    include "../../_Config/Connection.php";

    // ============================================================
    // TANGKAP PARAMETER
    // ============================================================
    $KeywordBy = $_POST['keyword_by'] ?? '';

    // ============================================================
    // TAMPILKAN FORM KEYWORD
    // ============================================================
    switch ($KeywordBy) {

        // --------------------------------------------------------
        // TANGGAL
        // --------------------------------------------------------
        case 'tanggal':
            echo '
                <input 
                    type="date" 
                    name="keyword" 
                    id="keyword" 
                    class="form-control"
                >
            ';
            break;

        // --------------------------------------------------------
        // KATEGORI
        // --------------------------------------------------------
        case 'kategori':
            echo '
                <select name="keyword" id="keyword" class="form-control">
                    <option value="">Pilih</option>
                    <option value="Pembelian">Pembelian</option>
                    <option value="Retur Pembelian">Retur Pembelian</option>
                </select>
            ';
            break;

        // --------------------------------------------------------
        // STATUS
        // --------------------------------------------------------
        case 'status':
            echo '
                <select name="keyword" id="keyword" class="form-control">
                    <option value="">Pilih</option>
                    <option value="Lunas">Lunas</option>
                    <option value="Kredit">Kredit</option>
                </select>
            ';
            break;

        // --------------------------------------------------------
        // SUPPLIER
        // --------------------------------------------------------
        case 'id_supplier':

            echo '
                <select name="keyword" id="keyword" class="form-control">
                    <option value="">Pilih Supplier</option>
            ';

            $QrySupplier = $Conn->prepare("
                SELECT DISTINCT
                    s.id_supplier,
                    s.nama_supplier
                FROM transaksi_jual_beli AS tjb
                INNER JOIN supplier AS s
                    ON tjb.id_supplier = s.id_supplier
                WHERE tjb.id_supplier IS NOT NULL
                ORDER BY s.nama_supplier ASC
            ");

            if ($QrySupplier) {

                if ($QrySupplier->execute()) {

                    $ResultSupplier = $QrySupplier->get_result();

                    while ($DataSupplier = $ResultSupplier->fetch_assoc()) {

                        $id_supplier = htmlspecialchars(
                            $DataSupplier['id_supplier'],
                            ENT_QUOTES,
                            'UTF-8'
                        );

                        $nama_supplier = htmlspecialchars(
                            $DataSupplier['nama_supplier'],
                            ENT_QUOTES,
                            'UTF-8'
                        );

                        echo '
                            <option value="'.$id_supplier.'">
                                '.$nama_supplier.'
                            </option>
                        ';
                    }
                }

                $QrySupplier->close();
            }

            echo '</select>';

            break;

        // --------------------------------------------------------
        // DEFAULT
        // --------------------------------------------------------
        default:
            echo '
                <input 
                    type="text" 
                    name="keyword" 
                    id="keyword" 
                    class="form-control"
                >
            ';
            break;
    }
?>
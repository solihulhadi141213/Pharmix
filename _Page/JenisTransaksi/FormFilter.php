<?php
    // ============================================================
    // KONFIGURASI
    // ============================================================
    include "../../_Config/Connection.php";


    // ============================================================
    // AMBIL PARAMETER
    // ============================================================
    $keyword_by = trim($_POST['keyword_by'] ?? '');


    // ============================================================
    // DAFTAR FILTER YANG DIIZINKAN
    // ============================================================
    $allowedKeywordBy = [
        'kategori',
        'id_akun_debet',
        'id_akun_kredit',
        'id_utang_piutang'
    ];


    // ============================================================
    // HELPER INPUT TEXT DEFAULT
    // ============================================================
    function renderInputText()
    {
        echo '
            <label for="keyword">
                Keyword
            </label>

            <input
                type="text"
                name="keyword"
                id="keyword"
                class="form-control"
                autocomplete="off"
            >
        ';

        exit;
    }


    // ============================================================
    // VALIDASI KEYWORD BY
    // ============================================================
    if (
        $keyword_by === '' ||
        !in_array(
            $keyword_by,
            $allowedKeywordBy,
            true
        )
    ) {
        renderInputText();
    }


    // ============================================================
    // TAMPILKAN LABEL
    // ============================================================
    echo '
        <label for="keyword">
            Keyword
        </label>
    ';


    // ============================================================
    // FILTER KATEGORI
    // ============================================================
    if ($keyword_by === 'kategori') {

        $sql = "
            SELECT DISTINCT
                kategori
            FROM transaksi_jenis
            WHERE
                kategori IS NOT NULL
                AND kategori <> ''
            ORDER BY kategori ASC
        ";

        $errorMsg = 'Gagal memuat kategori';
    }


    // ============================================================
    // FILTER AKUN
    // ============================================================
    else {

        /*
         * Kolom hanya dapat berasal dari $allowedKeywordBy.
         * Karena sudah divalidasi sebelumnya, aman digunakan
         * sebagai nama kolom pada query SQL.
         */
        $kolom = $keyword_by;


        // Label berdasarkan jenis akun
        $labelAkun = '';

        if ($keyword_by === 'id_akun_debet') {

            $labelAkun = 'akun debet';

        } elseif ($keyword_by === 'id_akun_kredit') {

            $labelAkun = 'akun kredit';

        } elseif ($keyword_by === 'id_utang_piutang') {

            $labelAkun = 'akun utang/piutang';
        }


        // ========================================================
        // QUERY AKUN
        // ========================================================
        $sql = "
            SELECT DISTINCT
                tj.$kolom AS id_akun,
                ap.kode,
                ap.nama,
                ap.saldo_normal

            FROM transaksi_jenis AS tj

            INNER JOIN akun_perkiraan AS ap
                ON ap.id_perkiraan = tj.$kolom

            WHERE
                tj.$kolom IS NOT NULL

            ORDER BY
                ap.kode ASC,
                ap.nama ASC
        ";

        $errorMsg = 'Gagal memuat ' . $labelAkun;
    }


    // ============================================================
    // PREPARE QUERY
    // ============================================================
    $stmt = $Conn->prepare($sql);


    // ============================================================
    // VALIDASI PREPARE
    // ============================================================
    if (!$stmt) {

        echo '
            <select
                name="keyword"
                id="keyword"
                class="form-select"
            >
                <option value="">
                    ' . htmlspecialchars(
                        $errorMsg,
                        ENT_QUOTES,
                        'UTF-8'
                    ) . '
                </option>
            </select>
        ';

        exit;
    }


    // ============================================================
    // EXECUTE QUERY
    // ============================================================
    if (!$stmt->execute()) {

        $stmt->close();

        echo '
            <select
                name="keyword"
                id="keyword"
                class="form-select"
            >
                <option value="">
                    ' . htmlspecialchars(
                        $errorMsg,
                        ENT_QUOTES,
                        'UTF-8'
                    ) . '
                </option>
            </select>
        ';

        exit;
    }


    // ============================================================
    // AMBIL HASIL QUERY
    // ============================================================
    $result = $stmt->get_result();


    // ============================================================
    // TAMPILKAN SELECT
    // ============================================================
    echo '
        <select
            name="keyword"
            id="keyword"
            class="form-select"
        >
            <option value="">
                Pilih
            </option>
    ';


    // ============================================================
    // LOOP DATA
    // ============================================================
    while ($data = $result->fetch_assoc()) {

        // --------------------------------------------------------
        // KATEGORI
        // --------------------------------------------------------
        if ($keyword_by === 'kategori') {

            $val = htmlspecialchars(
                $data['kategori'] ?? '',
                ENT_QUOTES,
                'UTF-8'
            );

            $text = $val;
        }


        // --------------------------------------------------------
        // AKUN
        // --------------------------------------------------------
        else {

            $val = (int) (
                $data['id_akun'] ?? 0
            );

            $kode = htmlspecialchars(
                $data['kode'] ?? '',
                ENT_QUOTES,
                'UTF-8'
            );

            $nama = htmlspecialchars(
                $data['nama'] ?? '',
                ENT_QUOTES,
                'UTF-8'
            );

            $saldo = htmlspecialchars(
                $data['saldo_normal'] ?? '',
                ENT_QUOTES,
                'UTF-8'
            );


            // Format nama akun
            $text = '';

            if ($kode !== '') {
                $text .= $kode;
            }

            if ($nama !== '') {

                if ($text !== '') {
                    $text .= ' - ';
                }

                $text .= $nama;
            }

            if ($saldo !== '') {
                $text .= ' (' . $saldo . ')';
            }


            // Jika data akun kosong
            if ($text === '') {
                $text = '-';
            }
        }


        // --------------------------------------------------------
        // OUTPUT OPTION
        // --------------------------------------------------------
        echo '
            <option value="' . $val . '">
                ' . $text . '
            </option>
        ';
    }


    // ============================================================
    // TUTUP SELECT
    // ============================================================
    echo '
        </select>
    ';


    // ============================================================
    // TUTUP STATEMENT
    // ============================================================
    $stmt->close();
?>
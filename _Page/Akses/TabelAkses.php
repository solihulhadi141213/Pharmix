<?php

    header('Content-Type: application/json; charset=utf-8');

    // =========================================================
    // KONEKSI DAN SESSION
    // =========================================================
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // =========================================================
    // FUNGSI RESPONSE ERROR
    // =========================================================
    function responseError(
        string $message,
        int $page = 1,
        int $total_page = 1,
        int $total_data = 0
    ): void {

        echo json_encode([
            "status"     => "error",
            "html"       => '
                <tr>
                    <td colspan="7" class="text-center text-danger">
                        <small>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</small>
                    </td>
                </tr>
            ',
            "page"       => $page,
            "total_page" => $total_page,
            "total_data" => $total_data
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // =========================================================
    // VALIDASI SESSION
    // =========================================================
    if (empty($SessionIdAkses)) {

        responseError(
            "Sesi akses sudah berakhir. Silakan login ulang.",
            1,
            1,
            0
        );
    }

    // =========================================================
    // AMBIL PARAMETER
    // =========================================================
    $page       = $_POST['page'] ?? 1;
    $batas      = $_POST['batas'] ?? 10;
    $OrderBy    = $_POST['OrderBy'] ?? 'id_akses';
    $ShortBy    = $_POST['ShortBy'] ?? 'ASC';
    $keyword_by = $_POST['keyword_by'] ?? '';
    $keyword    = trim($_POST['keyword'] ?? '');

    // =========================================================
    // VALIDASI PAGE
    // =========================================================
    $page = filter_var($page, FILTER_VALIDATE_INT);

    if ($page === false || $page < 1) {
        $page = 1;
    }

    // =========================================================
    // VALIDASI BATAS
    // =========================================================
    $batas = filter_var($batas, FILTER_VALIDATE_INT);

    if ($batas === false || $batas < 1) {
        $batas = 10;
    }

    // Batasi maksimal data per halaman
    if ($batas > 100) {
        $batas = 100;
    }

    // =========================================================
    // VALIDASI ORDER BY
    // =========================================================
    $allowedOrder = [
        'id_akses',
        'nama_akses',
        'email_akses',
        'kontak_akses',
        'akses'
    ];

    if (!in_array($OrderBy, $allowedOrder, true)) {
        $OrderBy = 'id_akses';
    }

    // =========================================================
    // VALIDASI SORT
    // =========================================================
    $ShortBy = strtoupper($ShortBy);

    if (!in_array($ShortBy, ['ASC', 'DESC'], true)) {
        $ShortBy = 'ASC';
    }

    // =========================================================
    // ORDER SQL
    // =========================================================
    // Karena $OrderBy sudah divalidasi menggunakan whitelist,
    // aman untuk dimasukkan langsung ke query.
    $OrderBySql = "a." . $OrderBy;

    // =========================================================
    // VALIDASI FILTER
    // =========================================================
    $allowedKeywordBy = [
        'nama_akses',
        'email_akses',
        'kontak_akses',
        'akses'
    ];

    if (!empty($keyword_by) && !in_array($keyword_by, $allowedKeywordBy, true)) {
        $keyword_by = '';
    }

    // =========================================================
    // BUILD WHERE
    // =========================================================
    $where      = "";
    $bindTypes  = "";
    $bindValues = [];

    if ($keyword !== '') {

        $keywordLike = "%" . $keyword . "%";

        // ---------------------------------------------
        // Pencarian berdasarkan kolom tertentu
        // ---------------------------------------------
        if ($keyword_by !== '') {

            $where = " WHERE a.$keyword_by LIKE ? ";

            $bindTypes   = "s";
            $bindValues[] = $keywordLike;

        } else {

            // ---------------------------------------------
            // Pencarian semua kolom
            // ---------------------------------------------
            $where = "
                WHERE (
                    a.nama_akses LIKE ?
                    OR a.email_akses LIKE ?
                    OR a.kontak_akses LIKE ?
                    OR a.akses LIKE ?
                )
            ";

            $bindTypes = "ssss";

            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
        }
    }

    // =========================================================
    // QUERY TOTAL DATA
    // =========================================================
    $sql_count = "
        SELECT COUNT(*) AS total
        FROM akses AS a
        $where
    ";

    $stmt_count = $Conn->prepare($sql_count);

    if (!$stmt_count) {

        responseError(
            "Gagal mempersiapkan query jumlah data.",
            $page,
            1,
            0
        );
    }

    // =========================================================
    // BIND COUNT PARAMETER
    // =========================================================
    if (!empty($bindValues)) {

        $stmt_count->bind_param(
            $bindTypes,
            ...$bindValues
        );
    }

    // =========================================================
    // EXECUTE COUNT
    // =========================================================
    if (!$stmt_count->execute()) {

        $stmt_count->close();

        responseError(
            "Gagal menghitung jumlah data.",
            $page,
            1,
            0
        );
    }

    // =========================================================
    // AMBIL TOTAL
    // =========================================================
    $result_count = $stmt_count->get_result();

    if (!$result_count) {

        $stmt_count->close();

        responseError(
            "Gagal membaca jumlah data.",
            $page,
            1,
            0
        );
    }

    $data_count = $result_count->fetch_assoc();

    $total_data = (int)($data_count['total'] ?? 0);

    $stmt_count->close();

    // =========================================================
    // TOTAL HALAMAN
    // =========================================================
    $total_page = ($total_data > 0)
        ? (int)ceil($total_data / $batas)
        : 1;

    // =========================================================
    // VALIDASI PAGE TERHADAP TOTAL PAGE
    // =========================================================
    if ($page > $total_page) {
        $page = $total_page;
    }

    // =========================================================
    // POSISI DATA
    // =========================================================
    $posisi = ($page - 1) * $batas;

    // =========================================================
    // QUERY DATA
    // =========================================================
    $sql = "
        SELECT
            a.id_akses,
            a.nama_akses,
            a.email_akses,
            a.kontak_akses,
            a.akses,
            COALESCE(ai.jumlah_item, 0) AS jumlah_item

        FROM akses AS a

        LEFT JOIN (
            SELECT
                id_akses,
                COUNT(id_akses_ijin) AS jumlah_item
            FROM akses_ijin
            GROUP BY id_akses
        ) AS ai
            ON ai.id_akses = a.id_akses

        $where

        ORDER BY $OrderBySql $ShortBy

        LIMIT ?, ?
    ";

    // =========================================================
    // PREPARE
    // =========================================================
    $stmt = $Conn->prepare($sql);

    if (!$stmt) {

        responseError(
            "Gagal mempersiapkan query data.",
            $page,
            $total_page,
            $total_data
        );
    }

    // =========================================================
    // BIND PARAMETER DATA
    // =========================================================
    $bindTypesData  = $bindTypes . "ii";
    $bindValuesData = $bindValues;

    $bindValuesData[] = $posisi;
    $bindValuesData[] = $batas;

    $stmt->bind_param(
        $bindTypesData,
        ...$bindValuesData
    );

    // =========================================================
    // EXECUTE
    // =========================================================
    if (!$stmt->execute()) {

        $stmt->close();

        responseError(
            "Terjadi kesalahan saat mengambil data.",
            $page,
            $total_page,
            $total_data
        );
    }

    // =========================================================
    // GET RESULT
    // =========================================================
    $query = $stmt->get_result();

    if (!$query) {

        $stmt->close();

        responseError(
            "Gagal membaca hasil data.",
            $page,
            $total_page,
            $total_data
        );
    }

    // =========================================================
    // BUILD HTML
    // =========================================================
    $html = '';

    $no = $posisi + 1;

    // =========================================================
    // JIKA DATA KOSONG
    // =========================================================
    if ($query->num_rows === 0) {

        $html .= '
            <tr>
                <td colspan="7" class="text-center text-muted">
                    <small>Tidak ada data yang ditampilkan.</small>
                </td>
            </tr>
        ';

    } else {

        // =====================================================
        // LOOP DATA
        // =====================================================
        while ($data = $query->fetch_assoc()) {

            // ---------------------------------------------
            // DATA
            // ---------------------------------------------
            $id_akses = (int)$data['id_akses'];

            $nama_akses = htmlspecialchars(
                $data['nama_akses'] ?? '',
                ENT_QUOTES,
                'UTF-8'
            );

            $email_akses = htmlspecialchars(
                $data['email_akses'] ?? '',
                ENT_QUOTES,
                'UTF-8'
            );

            $kontak_akses = htmlspecialchars(
                $data['kontak_akses'] ?? '',
                ENT_QUOTES,
                'UTF-8'
            );

            $akses = htmlspecialchars(
                $data['akses'] ?? '',
                ENT_QUOTES,
                'UTF-8'
            );

            $jumlah_item = (int)($data['jumlah_item'] ?? 0);

            // ---------------------------------------------
            // HTML
            // ---------------------------------------------
            $html .= '
                <tr>

                    <!-- NO -->
                    <td>
                        <small class="text-muted">
                            ' . $no . '
                        </small>
                    </td>

                    <!-- NAMA -->
                    <td>
                        <a
                            class="modal_detail"
                            href="javascript:void(0)"
                            data-bs-toggle="modal"
                            data-bs-target="#ModalDetail"
                            data-id="' . $id_akses . '"
                        >
                            <small>
                                ' . $nama_akses . '
                            </small>
                        </a>
                    </td>

                    <!-- EMAIL -->
                    <td>
                        <small class="text-muted">
                            ' . $email_akses . '
                        </small>
                    </td>

                    <!-- KONTAK -->
                    <td>
                        <small class="text-muted">
                            ' . $kontak_akses . '
                        </small>
                    </td>

                    <!-- AKSES -->
                    <td>
                        <small class="text-muted">
                            ' . $akses . '
                        </small>
                    </td>

                    <!-- JUMLAH FITUR -->
                    <td>
                        <small class="text-muted">
                            ' . $jumlah_item . ' Fitur
                        </small>
                    </td>

                    <!-- ACTION -->
                    <td class="text-center">

                        <button
                            type="button"
                            class="btn btn-sm btn-floating btn-secondary"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end">

                            <li class="dropdown-header text-start">
                                <h6>Option</h6>
                            </li>

                            <!-- DETAIL -->
                            <li>
                                <a
                                    class="dropdown-item modal_detail_sesi"
                                    href="javascript:void(0)"
                                    data-bs-toggle="modal"
                                    data-bs-target="#ModalDetail"
                                    data-id="' . $id_akses . '"
                                >
                                    <i class="bi bi-info-circle"></i>
                                    Detail
                                </a>
                            </li>

                            <!-- EDIT -->
                            <li>
                                <a
                                    class="dropdown-item"
                                    href="javascript:void(0)"
                                    data-bs-toggle="modal"
                                    data-bs-target="#ModalEdit"
                                    data-id="' . $id_akses . '"
                                >
                                    <i class="bi bi-pencil"></i>
                                    Edit
                                </a>
                            </li>

                            <li>
                                <a
                                    class="dropdown-item"
                                    href="javascript:void(0)"
                                    data-bs-toggle="modal"
                                    data-bs-target="#ModalEditLevelAkses"
                                    data-id="' . $id_akses . '"
                                >
                                    <i class="bi bi-layers"></i>
                                    Entitas/Level
                                </a>
                            </li>

                            <li>
                                <a
                                    class="dropdown-item"
                                    href="javascript:void(0)"
                                    data-bs-toggle="modal"
                                    data-bs-target="#ModalUbahFotoAkses"
                                    data-id="' . $id_akses . '"
                                >
                                    <i class="bi bi-image"></i>
                                    Foto
                                </a>
                            </li>

                            <li>
                                <a
                                    class="dropdown-item"
                                    href="javascript:void(0)"
                                    data-bs-toggle="modal"
                                    data-bs-target="#ModalUbahPassword"
                                    data-id="' . $id_akses . '"
                                >
                                    <i class="bi bi-key"></i>
                                    Password
                                </a>
                            </li>

                            <li>
                                <a
                                    class="dropdown-item"
                                    href="javascript:void(0)"
                                    data-bs-toggle="modal"
                                    data-bs-target="#ModalUbahIzinAkses"
                                    data-id="' . $id_akses . '"
                                >
                                    <i class="bi bi-file"></i>
                                    Role
                                </a>
                            </li>

                            <!-- HAPUS -->
                            <li>
                                <a
                                    class="dropdown-item"
                                    href="javascript:void(0)"
                                    data-bs-toggle="modal"
                                    data-bs-target="#ModalHapus"
                                    data-id="' . $id_akses . '"
                                >
                                    <i class="bi bi-trash"></i>
                                    Hapus
                                </a>
                            </li>

                        </ul>

                    </td>

                </tr>
            ';

            $no++;
        }
    }

    // =========================================================
    // CLOSE STATEMENT
    // =========================================================
    $stmt->close();

    // =========================================================
    // RESPONSE JSON
    // =========================================================
    echo json_encode([
        "status"     => "success",
        "html"       => $html,
        "page"       => $page,
        "total_page" => $total_page,
        "total_data" => $total_data
    ], JSON_UNESCAPED_UNICODE);

    exit;
?>
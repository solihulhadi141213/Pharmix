<?php
    //Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    //Hitung data barang
    $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT id_barang_kategori_harga FROM barang_kategori_harga"));
    if(empty($jml_data)){
        echo '
            <tr>
                <td colspan="4" class="text-center text-danger">
                    Tidak Ada Data Kategori Multi Harga Yang Ditampilkan
                </td>
            </tr>
        ';
    }else{
        $no = 1;
        $query = mysqli_query($Conn, "SELECT*FROM barang_kategori_harga");
        while ($data = mysqli_fetch_array($query)) {
            $id_barang_kategori_harga= $data['id_barang_kategori_harga'];
            $kategori_harga= $data['kategori_harga'];
            $jumlah_barang = mysqli_num_rows(mysqli_query($Conn, "SELECT id_barang_harga FROM barang_harga WHERE id_barang_kategori_harga='$id_barang_kategori_harga'"));
            echo '
                <tr>
                    <td><span class="text-muted">'.$no.'</span></td>
                    <td><span class="text-muted">'.$kategori_harga.'</span></td>
                    <td><span class="text-muted">'.$jumlah_barang.' Item</span></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-floating btn-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" style="">
                            <li class="dropdown-header text-start">
                                <h6>Option</h6>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalEditKategoriHarga" data-id="'.$id_barang_kategori_harga.'">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalHapusKategoriHarga" data-id="'.$id_barang_kategori_harga.'">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </li>
                        </ul>
                    </td>
                </tr>
            ';
            $no++;
        }
    }
?>

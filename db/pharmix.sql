-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 17, 2026 at 12:42 PM
-- Server version: 8.4.7
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pharmix`
--

-- --------------------------------------------------------

--
-- Table structure for table `akses`
--

DROP TABLE IF EXISTS `akses`;
CREATE TABLE IF NOT EXISTS `akses` (
  `id_akses` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid_akses_entitas` varchar(32) NOT NULL COMMENT 'Dari tabel akses_entitas',
  `nama_akses` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Nama pengguna (user)',
  `kontak_akses` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Kontak pengguna (user)',
  `email_akses` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Alamat email pengguna (user)',
  `password` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Password terenkripsi',
  `image_akses` char(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Nama File Foto Profil',
  `akses` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Level akses',
  `datetime_daftar` datetime NOT NULL COMMENT 'Tanggal jam dibuat',
  `datetime_update` datetime NOT NULL COMMENT 'Tanggal jam update terakhir',
  PRIMARY KEY (`id_akses`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `akses_entitas`
--

DROP TABLE IF EXISTS `akses_entitas`;
CREATE TABLE IF NOT EXISTS `akses_entitas` (
  `uuid_akses_entitas` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `akses` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`uuid_akses_entitas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `akses_fitur`
--

DROP TABLE IF EXISTS `akses_fitur`;
CREATE TABLE IF NOT EXISTS `akses_fitur` (
  `id_akses_fitur` int NOT NULL AUTO_INCREMENT,
  `kode` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Kode Unik Fitur Aplikasi',
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Nama Fitur Aplikasi',
  `kategori` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Kategori Fitur',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Keterangan atau deskripsi fitur',
  PRIMARY KEY (`id_akses_fitur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `akses_ijin`
--

DROP TABLE IF EXISTS `akses_ijin`;
CREATE TABLE IF NOT EXISTS `akses_ijin` (
  `id_akses_ijin` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_akses` int UNSIGNED NOT NULL,
  `id_akses_fitur` int NOT NULL,
  `kode` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `kategori` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id_akses_ijin`),
  KEY `id_akses` (`id_akses`),
  KEY `id_akses_fitur` (`id_akses_fitur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `akses_login`
--

DROP TABLE IF EXISTS `akses_login`;
CREATE TABLE IF NOT EXISTS `akses_login` (
  `akses_login` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_akses` int UNSIGNED NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `date_creat` datetime NOT NULL,
  `date_expired` datetime NOT NULL,
  PRIMARY KEY (`akses_login`),
  KEY `id_akses` (`id_akses`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `akses_referensi`
--

DROP TABLE IF EXISTS `akses_referensi`;
CREATE TABLE IF NOT EXISTS `akses_referensi` (
  `id_akses_referensi` int NOT NULL AUTO_INCREMENT,
  `uuid_akses_entitas` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `id_akses_fitur` int NOT NULL,
  PRIMARY KEY (`id_akses_referensi`),
  KEY `uuid_akses_entitas` (`uuid_akses_entitas`),
  KEY `id_akses_fitur` (`id_akses_fitur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `akun_perkiraan`
--

DROP TABLE IF EXISTS `akun_perkiraan`;
CREATE TABLE IF NOT EXISTS `akun_perkiraan` (
  `id_perkiraan` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `nama` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `level` int NOT NULL,
  `saldo_normal` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Debet, Kredit',
  `kd1` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `kd2` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `kd3` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `kd4` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `kd5` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id_perkiraan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anggota`
--

DROP TABLE IF EXISTS `anggota`;
CREATE TABLE IF NOT EXISTS `anggota` (
  `id_anggota` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_pasien` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'RM Pasien Lokal',
  `id_ihs` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'ID Satusehat',
  `nik` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Nomor KTP',
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `kontak` char(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `gender` enum('Male','Female') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `tempat_lahir` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `creat_at` datetime NOT NULL COMMENT 'Tanggal & Jam Pembuatan',
  `creat_by_id` int UNSIGNED NOT NULL COMMENT 'ID Akses Pembuat',
  `creat_by_name` varchar(255) NOT NULL COMMENT 'Nama Akses Pembuat',
  `update_at` datetime NOT NULL COMMENT 'Tanggal & Jam Update',
  `update_by_id` int UNSIGNED NOT NULL COMMENT 'ID Akses Updater',
  `update_by_name` varchar(255) NOT NULL COMMENT 'Nama Akses Updater',
  PRIMARY KEY (`id_anggota`),
  UNIQUE KEY `uq_anggota_id_pasien` (`id_pasien`),
  KEY `id_pasien` (`id_pasien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

DROP TABLE IF EXISTS `barang`;
CREATE TABLE IF NOT EXISTS `barang` (
  `id_barang` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_barang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `nama_barang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `kategori_barang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `satuan_barang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `konversi` decimal(10,2) NOT NULL,
  `harga_beli` decimal(10,2) DEFAULT NULL,
  `stok_barang` decimal(10,2) DEFAULT NULL,
  `stok_minimum` decimal(10,2) NOT NULL,
  `id_index_medication` int UNSIGNED DEFAULT NULL COMMENT 'Dari tabel medication',
  PRIMARY KEY (`id_barang`),
  KEY `kode_barang` (`kode_barang`),
  KEY `barang_to_medication` (`id_index_medication`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barang_bacth`
--

DROP TABLE IF EXISTS `barang_bacth`;
CREATE TABLE IF NOT EXISTS `barang_bacth` (
  `id_barang_bacth` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_barang` int UNSIGNED NOT NULL COMMENT 'Dari tabel barang',
  `no_batch` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Nomor Batch Lokal',
  `expired_date` date NOT NULL COMMENT 'Tanggal expired',
  `qty_batch` decimal(15,2) NOT NULL COMMENT 'Jumlah Per Batch',
  `reminder_date` date NOT NULL COMMENT 'Tanggal pengingat',
  `status` enum('Terdaftar','Terjual') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Terdaftar, Terjual, None',
  PRIMARY KEY (`id_barang_bacth`),
  KEY `id_barang` (`id_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barang_diskon`
--

DROP TABLE IF EXISTS `barang_diskon`;
CREATE TABLE IF NOT EXISTS `barang_diskon` (
  `id_barang_diskon` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_barang` int UNSIGNED NOT NULL,
  `diskon` decimal(15,2) UNSIGNED NOT NULL COMMENT 'Persen',
  `datetime_start` datetime NOT NULL,
  `datetime_end` datetime NOT NULL,
  PRIMARY KEY (`id_barang_diskon`),
  KEY `id_barang` (`id_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barang_harga`
--

DROP TABLE IF EXISTS `barang_harga`;
CREATE TABLE IF NOT EXISTS `barang_harga` (
  `id_barang_harga` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_barang` int UNSIGNED NOT NULL COMMENT 'Dari tabel barang',
  `id_barang_kategori_harga` int UNSIGNED NOT NULL COMMENT 'dati tabel barang_kategori_harga',
  `harga` decimal(15,2) UNSIGNED DEFAULT NULL COMMENT 'Harga barang',
  PRIMARY KEY (`id_barang_harga`),
  KEY `id_barang` (`id_barang`),
  KEY `id_barang_kategori_harga` (`id_barang_kategori_harga`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barang_kategori_harga`
--

DROP TABLE IF EXISTS `barang_kategori_harga`;
CREATE TABLE IF NOT EXISTS `barang_kategori_harga` (
  `id_barang_kategori_harga` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `kategori_harga` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id_barang_kategori_harga`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barang_satuan`
--

DROP TABLE IF EXISTS `barang_satuan`;
CREATE TABLE IF NOT EXISTS `barang_satuan` (
  `id_barang_satuan` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_barang` int UNSIGNED NOT NULL,
  `satuan_multi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `konversi_multi` int DEFAULT NULL,
  PRIMARY KEY (`id_barang_satuan`),
  KEY `id_barang` (`id_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `captcha`
--

DROP TABLE IF EXISTS `captcha`;
CREATE TABLE IF NOT EXISTS `captcha` (
  `id_captcha` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `unique_code` char(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `timestamp_creat` timestamp NOT NULL,
  `timestamp_expired` timestamp NOT NULL,
  PRIMARY KEY (`id_captcha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `connection_satu_sehat`
--

DROP TABLE IF EXISTS `connection_satu_sehat`;
CREATE TABLE IF NOT EXISTS `connection_satu_sehat` (
  `id_connection_satu_sehat` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_connection_satu_sehat` varchar(255) NOT NULL COMMENT 'Ex: Development, Staging, Production',
  `url_connection_satu_sehat` varchar(255) NOT NULL COMMENT 'Dari Satu Sehat',
  `organization_id` varchar(255) NOT NULL COMMENT 'Dari Satu Sehat',
  `client_key` varchar(255) NOT NULL COMMENT 'Dari Satu Sehat',
  `secret_key` varchar(255) NOT NULL COMMENT 'Dari Satu Sehat',
  `token` varchar(255) NOT NULL,
  `datetime_expired` datetime DEFAULT NULL,
  `status_connection_satu_sehat` tinyint(1) NOT NULL COMMENT 'True Or False',
  PRIMARY KEY (`id_connection_satu_sehat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diagnosis`
--

DROP TABLE IF EXISTS `diagnosis`;
CREATE TABLE IF NOT EXISTS `diagnosis` (
  `id_diagnosis` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `diagnosis_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Kode Local Diagnosis',
  `id_kunjungan` int UNSIGNED NOT NULL COMMENT 'Dari tabel kunjungan',
  `id_pasien` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'NO RM Pasien',
  `id_condition` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'ID Condition Satusehat',
  `medicalPersonelId` int UNSIGNED DEFAULT NULL COMMENT 'Dari tabel medical_personel',
  `medicalPersonelName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Nama dokter',
  `category` enum('Admission','Provisional','Primary','Secondary','Working','Differential','Final') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Kategori Diagnosis',
  `icd_version` enum('ICD9','ICD10','ICD11') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Versi ICD yang digunakan',
  `icd_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Kode ICD',
  `icd_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Deskripsi ICD',
  `diagnosis_text` text COMMENT 'Diagnosis text bebas dari dokter',
  `case_status` enum('Baru','Lama','Kambuh','Kronis') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `certainty_status` enum('Provisional','Final') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `creat_at` datetime NOT NULL COMMENT 'Tanggal dibuat',
  `creat_by_id` int UNSIGNED DEFAULT NULL COMMENT 'Akses Pembuat',
  `creat_by_name` varchar(255) NOT NULL COMMENT 'Nama Pembuat',
  `update_at` datetime NOT NULL COMMENT 'Tanggal Diupdate',
  `update_by_id` int UNSIGNED DEFAULT NULL COMMENT 'Akses Pengupdate',
  `update_by_name` varchar(255) NOT NULL COMMENT 'Nama Pengupdate',
  PRIMARY KEY (`id_diagnosis`),
  KEY `diagnosis_code` (`diagnosis_code`),
  KEY `diagnosis_to_kunjungan` (`id_kunjungan`),
  KEY `diagnosis_to_anggota` (`id_pasien`),
  KEY `diagnosis_to_medical_personel` (`medicalPersonelId`),
  KEY `diagnosis_to_akses_1` (`creat_by_id`),
  KEY `diagnosis_to_akses_2` (`update_by_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dokumentasi`
--

DROP TABLE IF EXISTS `dokumentasi`;
CREATE TABLE IF NOT EXISTS `dokumentasi` (
  `id_dokumentasi` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL COMMENT 'Judul Dokumentasi',
  `deskripsi` tinytext NOT NULL COMMENT 'Gambaran singkat',
  `status` enum('Publish','Draft') NOT NULL COMMENT 'Publish Of Draft',
  `id_akses` int UNSIGNED DEFAULT NULL COMMENT 'User yang membuat konten',
  `author_name` varchar(255) NOT NULL COMMENT 'Nama Author',
  `creat_at` datetime NOT NULL COMMENT 'Tanggal & Jam Dibuat',
  `update_at` datetime NOT NULL COMMENT 'Tanggal & Jam Update',
  PRIMARY KEY (`id_dokumentasi`),
  KEY `dokumentasi_to_akses` (`id_akses`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dokumentasi_konten`
--

DROP TABLE IF EXISTS `dokumentasi_konten`;
CREATE TABLE IF NOT EXISTS `dokumentasi_konten` (
  `id_dokumentasi_konten` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_dokumentasi` int UNSIGNED NOT NULL COMMENT 'Dari tabel dokuemntasi',
  `sequence` int UNSIGNED NOT NULL COMMENT 'Urutan konten',
  `tipe_konten` enum('Text','List Numbering','List Bullet','Local Image','Url Image') NOT NULL COMMENT 'Tipe konten',
  `text_konten` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'konten text',
  `list_konten` json DEFAULT NULL COMMENT 'Numbering Or Bullet',
  `local_image_konten` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'File Name Image',
  `url_image_konten` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Image dari URL',
  PRIMARY KEY (`id_dokumentasi_konten`),
  KEY `konten_to_dokumentasi` (`id_dokumentasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dokumentasi_tags`
--

DROP TABLE IF EXISTS `dokumentasi_tags`;
CREATE TABLE IF NOT EXISTS `dokumentasi_tags` (
  `id_dokumentasi_tags` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_dokumentasi` int UNSIGNED NOT NULL,
  `tags` varchar(255) NOT NULL,
  PRIMARY KEY (`id_dokumentasi_tags`),
  KEY `tags_to_dokumentasi` (`id_dokumentasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `google_credential`
--

DROP TABLE IF EXISTS `google_credential`;
CREATE TABLE IF NOT EXISTS `google_credential` (
  `id_google_credential` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `credential_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_secret` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Active','Inactive','','') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_google_credential`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `icd`
--

DROP TABLE IF EXISTS `icd`;
CREATE TABLE IF NOT EXISTS `icd` (
  `id_icd` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `long_des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `short_des` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `icd` enum('ICD9','ICD10','ICD11') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id_icd`),
  UNIQUE KEY `kode_2` (`kode`),
  KEY `kode` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jurnal`
--

DROP TABLE IF EXISTS `jurnal`;
CREATE TABLE IF NOT EXISTS `jurnal` (
  `id_jurnal` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `kategori` enum('Pembayaran','Penjualan','Pembelian','Retur Penjualan','Retur Pembelian','Pemasukan','Pengeluaran') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Transaksi, Penjualan, Pembelian, Pembayaran',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `id_transaksi` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `id_transaksi_jual_beli` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `id_transaksi_pembayaran` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `tanggal` date NOT NULL COMMENT 'tanggal transaksi',
  `kode_perkiraan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama_perkiraan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `d_k` enum('D','K') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'D/K',
  `nilai` int DEFAULT NULL,
  PRIMARY KEY (`id_jurnal`),
  KEY `id_transaksi_jual_beli` (`id_transaksi_jual_beli`),
  KEY `id_transaksi` (`id_transaksi`),
  KEY `id_transaksi_pembayaran` (`id_transaksi_pembayaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kunjungan`
--

DROP TABLE IF EXISTS `kunjungan`;
CREATE TABLE IF NOT EXISTS `kunjungan` (
  `id_kunjungan` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_anggota` int UNSIGNED DEFAULT NULL COMMENT 'dari Tabel Anggota',
  `id_encounter` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'ID Satusehat',
  `tanggal_kunjungan` datetime NOT NULL,
  `priority` enum('Normal','Urgent','Emergency') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `keluhan` text,
  `jenis_kunjungan` enum('AMB','IMP','EMER') NOT NULL,
  `id_dokter_penerima` int UNSIGNED DEFAULT NULL COMMENT 'dari medical_personel',
  `kode_dokter_penerima` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `nama_dokter_penerima` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `id_dpjp` int UNSIGNED DEFAULT NULL COMMENT 'dari medical_personel',
  `kode_dpjp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `nama_dpjp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `id_poli` int UNSIGNED DEFAULT NULL COMMENT 'dari polyclinic',
  `kode_poli` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `nama_poli` varchar(255) DEFAULT NULL,
  `kelas_inap` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `ruang_inap` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `status` enum('planned','arrived','triaged','in-progress','onleave','finished','cancelled','entered-in-error','unknown') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `creat_at` datetime NOT NULL COMMENT 'Tanggal & Jam Dibuat',
  `creat_by_id` int UNSIGNED DEFAULT NULL COMMENT 'ID Akses Creator',
  `creat_by_name` varchar(255) NOT NULL COMMENT 'Nama Creator',
  `update_at` datetime NOT NULL COMMENT 'Tanggal & Jam diubah',
  `update_by_id` int UNSIGNED DEFAULT NULL COMMENT 'ID Akses Updater',
  `update_by_name` varchar(255) NOT NULL COMMENT 'Nama Updater',
  PRIMARY KEY (`id_kunjungan`),
  KEY `kunjungan_anggota` (`id_anggota`),
  KEY `kunjungan_to_dokter_1` (`id_dokter_penerima`),
  KEY `kunjungan_to_dokter_2` (`id_dpjp`),
  KEY `kunjungan_to_poliklinik` (`id_poli`),
  KEY `kunjungan_to_akses_1` (`creat_by_id`),
  KEY `kunjungan_to_akses_2` (`update_by_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
CREATE TABLE IF NOT EXISTS `log` (
  `id_log` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_akses` int UNSIGNED NOT NULL,
  `datetime_log` datetime NOT NULL,
  `kategori_log` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `deskripsi_log` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id_log`),
  KEY `id_akses` (`id_akses`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_personel`
--

DROP TABLE IF EXISTS `medical_personel`;
CREATE TABLE IF NOT EXISTS `medical_personel` (
  `medicalPersonelId` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `medicalPersonelCode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Local code frome company',
  `id_practitioner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'id_practitioner from satusehat patform',
  `medicalPersonelCategory` enum('Dokter Umum','Dokter Spesialis','Perawat','Bidan','Rekam Medis','Administrasi','Apoteker','Analis Laboratorium','Radiografer','Terapis','Gizi','Penata Anestesi','Elektromedis','Sanitarian','Epidemiolog','Kesehatan Lingkungan','Kesehatan Masyarakat') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Kategori tenaga medis',
  `medicalPersonelNik` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Nomor KTP',
  `medicalPersonelName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Nama lengkap & gelar',
  `medicalPersonelGender` enum('Male','Female') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Jenis kelamin (male OR Female)',
  `medicalPersonelEmail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Alamat email',
  `medicalPersonelPhone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Kontak/telepon',
  `medicalPersonelAddress` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Alamat selengkapnya, seperti nama jalan, gang, nomor rumah dll',
  `medicalPersonelStatus` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Active Or Inactive',
  `creat_by_id` int UNSIGNED DEFAULT NULL COMMENT 'ID Account yang melakukan insert',
  `creat_by_name` varchar(255) NOT NULL,
  `creat_at` datetime DEFAULT NULL COMMENT 'Timezone UTC',
  `update_by_id` int UNSIGNED DEFAULT NULL COMMENT 'ID Account yang melakukan Update',
  `update_by_name` varchar(255) NOT NULL,
  `update_at` datetime DEFAULT NULL COMMENT 'Timezone UTC',
  `id_akses` int UNSIGNED DEFAULT NULL COMMENT 'Akun Akses Tenaga Medis',
  PRIMARY KEY (`medicalPersonelId`),
  UNIQUE KEY `email` (`medicalPersonelEmail`),
  KEY `personel_to_createdBy` (`creat_by_id`),
  KEY `personel_to_updateBy` (`update_by_id`),
  KEY `medical_personel_to_akses_3` (`id_akses`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Data Master Tenaga Kesehatan';

-- --------------------------------------------------------

--
-- Table structure for table `medication`
--

DROP TABLE IF EXISTS `medication`;
CREATE TABLE IF NOT EXISTS `medication` (
  `id_index_medication` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_medication` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Dari Satu sehat',
  `medication_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Kode Lokal',
  `medication_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'nama obat',
  `medication_category` enum('Obat','Alkes','Lainnya','') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Obat, Alkes, Lainnya',
  `kfa_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Kode KFA',
  `kfa_display` varchar(255) DEFAULT NULL COMMENT 'Nama KFA',
  `sediaan_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'medication-form',
  `sediaan_display` varchar(255) DEFAULT NULL COMMENT 'medication-form',
  `racikan_code` enum('NC','SD','EP') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'medication-type code',
  `racikan_display` enum('Non-compound','Gives of such doses','Divide into equal parts') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'medication-type',
  `manufacturer_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'ID Organization Manufacturer Dari Satusehat',
  `manufacturer_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Nama Organization Manufacturer Dari Satusehat',
  `ingredient` json DEFAULT NULL COMMENT 'JSON LIST',
  PRIMARY KEY (`id_index_medication`),
  UNIQUE KEY `code_medication` (`medication_code`),
  UNIQUE KEY `id_medication_2` (`id_medication`),
  KEY `id_medication` (`id_medication`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_dispense`
--

DROP TABLE IF EXISTS `medication_dispense`;
CREATE TABLE IF NOT EXISTS `medication_dispense` (
  `kode_medication_dispense` varchar(255) NOT NULL COMMENT 'ID Unik medication_dispense',
  `id_medication_dispense` varchar(255) DEFAULT NULL COMMENT 'ID medication request (satu Sehat)',
  `MedicationRequestId` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Dari tabel medication_request',
  `id_medication_request_group` int UNSIGNED NOT NULL COMMENT 'dari tabel medication_request_group',
  `apoteker_id_ihs` varchar(255) DEFAULT NULL COMMENT 'ID practitioner Apoteker',
  `apoteker_nama` varchar(255) NOT NULL COMMENT 'Nama Apoteker',
  `quantity_value` decimal(10,2) NOT NULL COMMENT 'Jumlah obat yang diserahkan',
  `quantity_unit` varchar(255) NOT NULL COMMENT 'Satuan obat yang diserahkan',
  `quantity_code` varchar(255) NOT NULL COMMENT 'Kode satuan obat yang diserahkan',
  `quantity_system` varchar(255) NOT NULL COMMENT 'Sistem satuan obat yang diserahkan',
  `status` enum('preparation','in-progress','completed','stopped','cancelled') NOT NULL,
  KEY `dispense_to_request` (`MedicationRequestId`),
  KEY `dispense_to_group` (`id_medication_request_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Informasi penyerahan obat';

-- --------------------------------------------------------

--
-- Table structure for table `medication_request`
--

DROP TABLE IF EXISTS `medication_request`;
CREATE TABLE IF NOT EXISTS `medication_request` (
  `MedicationRequestId` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Kode Item Resep Lokal',
  `id_medication_request_group` int UNSIGNED NOT NULL,
  `id_medication_request` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Dari Satu Sehat',
  `intent` enum('order','plan','proposal') NOT NULL COMMENT 'Tujuan Permintaan',
  `id_index_medication` int UNSIGNED DEFAULT NULL COMMENT 'Dari Tabel Medication',
  `name_medication` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'nama Obat Yang Diresepkan',
  `status` enum('active','on-hold','completed','stopped','cancelled','entered-in-error') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `dosage_inst_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Ex : Diminum Setelah Makan',
  `dosage_inst_frequency` int UNSIGNED NOT NULL COMMENT 'Berapa kali dalam 1 hari (Ex: 3)',
  `dosage_inst_period` int UNSIGNED NOT NULL COMMENT 'Interval (1)',
  `dosage_inst_period_unit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'd (Day)',
  `dose_value` decimal(15,2) NOT NULL COMMENT 'Dosis per sekali minum (ex: 1 Or 0.5)',
  `dose_unit` varchar(255) NOT NULL COMMENT 'Satuan dosis (ex: tablet)',
  `dose_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Kode Satuan (Ex: TAB)',
  `dose_system` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Code System (http://unitsofmeasure.org)',
  `route_display` varchar(255) NOT NULL COMMENT 'Cara obat masuk (ex:Oral) ',
  `route_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Kode cara obat masuk (Ex: O)',
  `route_system` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Code System (Ex: http://www.whocc.no/atc)',
  `dispense_value` decimal(10,2) NOT NULL COMMENT 'Jumlah obat yang harus diserahkan apotek (ex: 10)',
  `dispense_unit` varchar(255) NOT NULL COMMENT 'Satuan obat yang diserahkan (Ex: tablet)',
  `dispense_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Kode satuan obat yang diserahkan (Ex: TAB)',
  `dispense_sys` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Code System satuan obat yang diserahkan (EX: http://unitsofmeasure.org)',
  `supply_duration_value` int NOT NULL COMMENT 'Durasi waktu obat dikonsumsi (Ex: 3)',
  `supply_duration_unit` varchar(255) NOT NULL COMMENT 'Unit Durasi waktu obat dikonsumsi (Ex: days)',
  `supply_duration_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'code unit durasi waktu obat dikonsumsi (Ex: d)',
  `supply_duration_sys` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'System unit durasi waktu obat dikonsumsi (Ex: http://unitsofmeasure.org)',
  `racikan_code` enum('NC','SD','EP') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'C, NC',
  `racikan_display` enum('Non-compound','Gives of such doses','Divide into equal parts') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Non-compound, Compound',
  `ingredient` json DEFAULT NULL COMMENT '[\r\n    {\r\n        "kode_kfa": "93015422",\r\n        "nama_kfa": "Paracetamol 500 mg Tablet (AFIFARMA, STRIP)",\r\n        "kode_numerator": "mg",\r\n        "nama_numerator": "milligram",\r\n        "jumlah_numerator": "500",\r\n        "kode_denominator": "TAB",\r\n        "nama_denominator": "Tablet",\r\n        "jumlah_denominator": "1"\r\n    },\r\n    {\r\n        "kode_kfa": "93023512",\r\n        "nama_kfa": "Caffeine 50 mg / Ekstrak Eleutherococcus senticosus Radix 25 mg / Calcium Ascorbate 90 mg / Cyanocobalamin  2,4 ug Tablet Kunyah (CAFETAB)",\r\n        "kode_numerator": "mg",\r\n        "nama_numerator": "milligram",\r\n        "jumlah_numerator": "50",\r\n        "kode_denominator": "TAB",\r\n        "nama_denominator": "Tablet",\r\n        "jumlah_denominator": "1"\r\n    }\r\n]',
  PRIMARY KEY (`MedicationRequestId`),
  KEY `medication_request_to_medication` (`id_index_medication`),
  KEY `medication_request_to_group` (`id_medication_request_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_request_group`
--

DROP TABLE IF EXISTS `medication_request_group`;
CREATE TABLE IF NOT EXISTS `medication_request_group` (
  `id_medication_request_group` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_anggota` int UNSIGNED DEFAULT NULL COMMENT 'dari tabel anggota',
  `id_kunjungan` int UNSIGNED DEFAULT NULL COMMENT 'ID Kunjungan pasien',
  `nama_pasien` varchar(255) NOT NULL,
  `priority` enum('routine','urgent','asap','stat') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `datetime_creat` datetime NOT NULL COMMENT 'Tanggal/Waktu Resep Dibuat',
  `datetime_verified` datetime DEFAULT NULL COMMENT 'Tanggal/Waktu Resep Diterima Apoteker',
  `datetime_completed` datetime DEFAULT NULL COMMENT 'Tanggal/Waktu Resep Selesai',
  `dokter_id` int UNSIGNED DEFAULT NULL COMMENT 'medicalPersonelId',
  `dokter_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'medicalPersonelCode',
  `dokter_ihs` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'id_practitioner',
  `dokter_nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'medicalPersonelName',
  `reason_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'http://hl7.org/fhir/sid/icd-10',
  `reason_display` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Nama Diagnosa',
  `reason_system` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Kode Diagnosa',
  `apoteker_id` int UNSIGNED DEFAULT NULL COMMENT 'medicalPersonelId',
  `apoteker_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'medicalPersonelCode',
  `apoteker_nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Nama Lengkap Apoteker',
  `apoteker_ihs` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'id_practitioner Apoteker',
  `kategori_resep` enum('Masuk','Keluar') NOT NULL COMMENT 'Masuk Or Keluar',
  `sumber_resep` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Faskes Yang Mengirim Resep',
  `status_resep` enum('Draft','Verified','Partially','Completed','Cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `id_document_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Dari Resource DocumentReference',
  `no_resep_nasional` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Dicari Setelah Mendapatkan DocumentReference',
  `creat_at` datetime NOT NULL,
  `creat_by_id` int UNSIGNED DEFAULT NULL,
  `creat_by_name` varchar(255) NOT NULL,
  `update_at` datetime NOT NULL,
  `update_by_id` int UNSIGNED DEFAULT NULL,
  `update_by_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id_medication_request_group`),
  KEY `medication_request_group_to_anggota` (`id_anggota`),
  KEY `medication_request_group_to_kunjungan` (`id_kunjungan`),
  KEY `medication_request_group_to_medical_personel_1` (`dokter_id`),
  KEY `medication_request_group_to_medical_personel_2` (`apoteker_id`),
  KEY `medication_request_group_to_akses_1` (`creat_by_id`),
  KEY `medication_request_group_to_akses_2` (`update_by_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `polyclinic`
--

DROP TABLE IF EXISTS `polyclinic`;
CREATE TABLE IF NOT EXISTS `polyclinic` (
  `polyclinicId` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `satuSehatCode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'id_location SATUSEHAT',
  `polyclinicCode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Kode poliklinik (lokal)',
  `polyclinicName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Nama poliklinik',
  `polyclinicStatus` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Active Or Inactive',
  `creat_at` datetime NOT NULL,
  `creat_by_id` int UNSIGNED DEFAULT NULL,
  `creat_by_name` varchar(255) NOT NULL,
  `update_at` datetime NOT NULL,
  `update_by_id` int UNSIGNED DEFAULT NULL,
  `update_by_name` varchar(255) NOT NULL,
  PRIMARY KEY (`polyclinicId`),
  KEY `polyclinic_to_akses_1` (`creat_by_id`),
  KEY `polyclinic_to_akses_2` (`update_by_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Master Poliklinik';

-- --------------------------------------------------------

--
-- Table structure for table `referensi_denominator`
--

DROP TABLE IF EXISTS `referensi_denominator`;
CREATE TABLE IF NOT EXISTS `referensi_denominator` (
  `id_referensi_denominator` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `code_denominator` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `display_denominator` varchar(255) NOT NULL,
  `system_denominator` text NOT NULL,
  PRIMARY KEY (`id_referensi_denominator`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referensi_numerator`
--

DROP TABLE IF EXISTS `referensi_numerator`;
CREATE TABLE IF NOT EXISTS `referensi_numerator` (
  `id_referensi_numerator` int NOT NULL AUTO_INCREMENT,
  `unit` varchar(255) NOT NULL,
  `code_numerator` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `system_numerator` text NOT NULL,
  PRIMARY KEY (`id_referensi_numerator`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referensi_route`
--

DROP TABLE IF EXISTS `referensi_route`;
CREATE TABLE IF NOT EXISTS `referensi_route` (
  `id_referensi_route` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_route` varchar(255) NOT NULL COMMENT 'Dalam Bahasa Indonesia',
  `display_route` varchar(255) NOT NULL COMMENT 'Nama sesuai FHIR',
  `code_route` varchar(255) NOT NULL COMMENT 'Kode Route Sesuai FHIR',
  `system_route` text NOT NULL COMMENT 'Kode System yang Digunakan',
  PRIMARY KEY (`id_referensi_route`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referensi_satuan_dosis`
--

DROP TABLE IF EXISTS `referensi_satuan_dosis`;
CREATE TABLE IF NOT EXISTS `referensi_satuan_dosis` (
  `id_referensi_satuan_dosis` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_satuan_dosis` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Bahas indonesia / Yang Ditampilkan',
  `unit_satuan_dosis` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Nama sesuai FHIR',
  `code_satuan_dosis` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Kode sesuai FHIR',
  `system_satuan_dosis` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Sistem yang digunakan',
  PRIMARY KEY (`id_referensi_satuan_dosis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Referensi satuan untuk dosis resep';

-- --------------------------------------------------------

--
-- Table structure for table `referensi_sediaan`
--

DROP TABLE IF EXISTS `referensi_sediaan`;
CREATE TABLE IF NOT EXISTS `referensi_sediaan` (
  `id_referensi_sediaan` int NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `display` varchar(255) NOT NULL,
  `system_referensi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `category` varchar(255) NOT NULL,
  `group_name` enum('Alkes','Obat') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Alkes, Obat',
  PRIMARY KEY (`id_referensi_sediaan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `setting_autojurnal_jual_beli`
--

DROP TABLE IF EXISTS `setting_autojurnal_jual_beli`;
CREATE TABLE IF NOT EXISTS `setting_autojurnal_jual_beli` (
  `id_autojurnal_jual_beli` int NOT NULL AUTO_INCREMENT,
  `kategori` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `debet` int UNSIGNED DEFAULT NULL COMMENT 'Dari id_perkiraan',
  `kredit` int UNSIGNED DEFAULT NULL COMMENT 'Dari id_perkiraan',
  `utang_piutang` int UNSIGNED DEFAULT NULL COMMENT 'Dari id_perkiraan',
  PRIMARY KEY (`id_autojurnal_jual_beli`),
  KEY `auto_jurnal_to_akun_perkiraan_1` (`debet`),
  KEY `auto_jurnal_to_akun_perkiraan_2` (`kredit`),
  KEY `auto_jurnal_to_akun_perkiraan_3` (`utang_piutang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Untuk menentukan auto jurnal saat transaksi dan pembayaran';

-- --------------------------------------------------------

--
-- Table structure for table `setting_email_gateway`
--

DROP TABLE IF EXISTS `setting_email_gateway`;
CREATE TABLE IF NOT EXISTS `setting_email_gateway` (
  `id_setting_email_gateway` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `email_gateway` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password_gateway` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `url_provider` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `port_gateway` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama_pengirim` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `url_service` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id_setting_email_gateway`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `setting_general`
--

DROP TABLE IF EXISTS `setting_general`;
CREATE TABLE IF NOT EXISTS `setting_general` (
  `id_setting_general` int NOT NULL AUTO_INCREMENT,
  `title_page` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kata_kunci` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `deskripsi` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `alamat_bisnis` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `email_bisnis` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `telepon_bisnis` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `favicon` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `logo` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `base_url` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `author` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id_setting_general`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_opname`
--

DROP TABLE IF EXISTS `stock_opname`;
CREATE TABLE IF NOT EXISTS `stock_opname` (
  `id_stock_opname` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `start_at` datetime NOT NULL COMMENT 'Mulai pada',
  `finish_at` datetime DEFAULT NULL COMMENT 'Selesai Pada',
  `creatAt` datetime NOT NULL COMMENT 'Waktu Dibuat',
  `creatBy` int UNSIGNED DEFAULT NULL COMMENT 'User Pembuat',
  `updateAt` datetime NOT NULL COMMENT 'Waktu Update',
  `updateBy` int UNSIGNED DEFAULT NULL COMMENT 'User Update',
  `status` enum('On-Progress','Finished') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'On-Progress' COMMENT '''On-Progress'' OR ''Finished''',
  PRIMARY KEY (`id_stock_opname`),
  KEY `so_to_akses_1` (`creatBy`),
  KEY `so_to_akses_2` (`updateBy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_opname_barang`
--

DROP TABLE IF EXISTS `stock_opname_barang`;
CREATE TABLE IF NOT EXISTS `stock_opname_barang` (
  `id_stock_opname_barang` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_stock_opname` int UNSIGNED NOT NULL COMMENT 'dari tabel stock_opname',
  `id_barang` int UNSIGNED NOT NULL COMMENT 'dari tabel barang',
  `stok_awal` decimal(10,2) DEFAULT NULL COMMENT 'Stock pada sistem',
  `stok_akhir` decimal(10,2) DEFAULT NULL COMMENT 'Stock hasil perbaikan',
  `stok_gap` decimal(10,2) DEFAULT NULL COMMENT 'Selisih Stock',
  `harga_beli` decimal(10,2) DEFAULT NULL COMMENT 'Harga beli pada saat pencatatan',
  `jumlah` decimal(10,2) DEFAULT NULL COMMENT 'Jumlah uang dari margin',
  `keterangan` varchar(255) DEFAULT NULL COMMENT 'Keterangan SO',
  `creatAt` datetime NOT NULL COMMENT 'Waktu dibuat',
  `creatBy` int UNSIGNED DEFAULT NULL COMMENT 'User Pembuat',
  `updateAt` datetime DEFAULT NULL COMMENT 'Waktu Update',
  `updateBy` int UNSIGNED DEFAULT NULL COMMENT 'User Update',
  PRIMARY KEY (`id_stock_opname_barang`),
  KEY `to_so` (`id_stock_opname`),
  KEY `to_barang` (`id_barang`),
  KEY `so_barang_to_akses_1` (`creatBy`),
  KEY `so_barang_to_akses_2` (`updateBy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='menjelaskan rincian SO barang';

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

DROP TABLE IF EXISTS `supplier`;
CREATE TABLE IF NOT EXISTS `supplier` (
  `id_supplier` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_supplier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Nama Perusahaan',
  `alamat_supplier` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Alamat perusahaan',
  `email_supplier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Email perusahaan',
  `kontak_supplier` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'kontak perusahaan',
  `pic` varchar(255) DEFAULT NULL COMMENT 'Person in Charge',
  `npwp` varchar(255) DEFAULT NULL COMMENT 'Nomor Wajib Pajak',
  PRIMARY KEY (`id_supplier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

DROP TABLE IF EXISTS `transaksi`;
CREATE TABLE IF NOT EXISTS `transaksi` (
  `id_transaksi` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `id_transaksi_jenis` int UNSIGNED DEFAULT NULL,
  `tanggal` datetime NOT NULL COMMENT 'Tanggal beralngsungnnya transaksi',
  `jumlah` int DEFAULT NULL COMMENT 'Jumlah tagihan/rincian',
  `pembayaran` int DEFAULT NULL COMMENT 'pembayaran uang cash',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'keterangan transaksi',
  `status` enum('Lunas','Utang','Piutang') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Lunas' COMMENT 'Status transaksi',
  `creat_at` datetime NOT NULL COMMENT 'Tanggal & Jam Dibuat',
  `creat_by_id` int UNSIGNED DEFAULT NULL COMMENT 'User/Id akses pembuat',
  `creat_by_name` varchar(255) NOT NULL COMMENT 'Nama pembuat',
  `update_at` datetime NOT NULL COMMENT 'Tanggal & Jam Diubah',
  `update_by_id` int UNSIGNED DEFAULT NULL COMMENT 'User/Id akses pengubah',
  `update_by_name` varchar(255) NOT NULL COMMENT 'Nama pengubah',
  PRIMARY KEY (`id_transaksi`),
  KEY `id_transaksi_jenis` (`id_transaksi_jenis`),
  KEY `tanggal` (`tanggal`),
  KEY `transaksi_to_akun_1` (`creat_by_id`),
  KEY `transaksi_to_akun_2` (`update_by_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_bulk`
--

DROP TABLE IF EXISTS `transaksi_bulk`;
CREATE TABLE IF NOT EXISTS `transaksi_bulk` (
  `id_transaksi_bulk` int NOT NULL AUTO_INCREMENT,
  `id_akses` int UNSIGNED NOT NULL,
  `kategori` enum('Penjualan','Pembelian','Retur Penjualan','Retur Pembelian') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Penjualan, Pembelian, Retur Penjualan, Retur Pembelian',
  `id_barang` int UNSIGNED NOT NULL,
  `nama_barang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `satuan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `qty` decimal(10,2) DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `ppn` decimal(10,2) DEFAULT NULL,
  `diskon` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_transaksi_bulk`),
  KEY `id_akses` (`id_akses`),
  KEY `id_barang` (`id_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Datara rincian transaksi sementara';

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_jenis`
--

DROP TABLE IF EXISTS `transaksi_jenis`;
CREATE TABLE IF NOT EXISTS `transaksi_jenis` (
  `id_transaksi_jenis` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Nama transaksi',
  `kategori` enum('Pengeluaran','Pemasukan') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Pengeluaran, Pemasukan',
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Penjelasan tentan transaksi',
  `id_akun_debet` int UNSIGNED DEFAULT NULL COMMENT 'Akun perkiraan di lajur debet',
  `id_akun_kredit` int UNSIGNED DEFAULT NULL COMMENT 'Akun perkiraan di lajur kredit',
  `id_utang_piutang` int UNSIGNED DEFAULT NULL COMMENT 'Akun saat utang/piutang',
  PRIMARY KEY (`id_transaksi_jenis`),
  KEY `kredit_to_akun_perkiraan` (`id_akun_kredit`),
  KEY `debet_to_akun_perkiraan` (`id_akun_debet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_jual_beli`
--

DROP TABLE IF EXISTS `transaksi_jual_beli`;
CREATE TABLE IF NOT EXISTS `transaksi_jual_beli` (
  `id_transaksi_jual_beli` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `id_anggota` int UNSIGNED DEFAULT NULL COMMENT 'Koneksi ke tabel anggota (Pasien)',
  `id_supplier` int UNSIGNED DEFAULT NULL,
  `kategori` enum('Penjualan','Pembelian','Retur Penjualan','Retur Pembelian') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Penjualan, Pembelian, Retur Penjualan, Retur Pembelian',
  `tanggal` datetime NOT NULL COMMENT 'Tanggal & Jam Transaksi',
  `subtotal` decimal(15,2) DEFAULT NULL COMMENT 'RP',
  `diskon` decimal(15,2) DEFAULT NULL COMMENT 'RP',
  `ppn` decimal(15,2) DEFAULT NULL COMMENT 'RP',
  `total` decimal(15,2) DEFAULT NULL COMMENT 'RP',
  `cash` decimal(15,2) DEFAULT NULL COMMENT 'RP',
  `kembalian` decimal(15,2) DEFAULT NULL COMMENT 'RP',
  `status` enum('Lunas','Utang','Piutang') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Lunas, Kredit',
  `creat_by_id` int UNSIGNED DEFAULT NULL COMMENT 'ID Akses Yang Membuat',
  `creat_by_name` varchar(255) NOT NULL COMMENT 'Nama User Yang Membuat',
  `creat_at` datetime NOT NULL COMMENT 'Tanggal & Jam Dibuat',
  `update_by_id` int UNSIGNED DEFAULT NULL COMMENT 'ID Akses Yang Melakukan UPDATE',
  `update_by_name` varchar(255) NOT NULL COMMENT 'Nama User Yang Melakukan Update',
  `update_at` datetime NOT NULL COMMENT 'Tanggal & Jam Update',
  PRIMARY KEY (`id_transaksi_jual_beli`),
  KEY `id_anggota` (`id_anggota`),
  KEY `id_supplier` (`id_supplier`),
  KEY `transaksi_to_akses_1` (`creat_by_id`),
  KEY `transaksi_to_akses_2` (`update_by_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_jual_beli_rincian`
--

DROP TABLE IF EXISTS `transaksi_jual_beli_rincian`;
CREATE TABLE IF NOT EXISTS `transaksi_jual_beli_rincian` (
  `id_transaksi_jual_beli_rincian` int NOT NULL AUTO_INCREMENT,
  `id_transaksi_jual_beli` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `id_barang` int UNSIGNED DEFAULT NULL,
  `nama_barang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `satuan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `qty` decimal(15,2) NOT NULL,
  `hpp` decimal(15,2) DEFAULT NULL,
  `harga` decimal(15,2) DEFAULT NULL,
  `ppn` decimal(15,2) DEFAULT NULL,
  `diskon` decimal(15,2) DEFAULT NULL,
  `subtotal` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id_transaksi_jual_beli_rincian`),
  KEY `id_transaksi_jual_beli` (`id_transaksi_jual_beli`),
  KEY `id_barang` (`id_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_pembayaran`
--

DROP TABLE IF EXISTS `transaksi_pembayaran`;
CREATE TABLE IF NOT EXISTS `transaksi_pembayaran` (
  `id_transaksi_pembayaran` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `id_transaksi` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `id_transaksi_jual_beli` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `kategori_pembayaran` enum('Cash','Termin','Pelunasan') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Cash. Termin, Pelunasan',
  `kategori_transaksi` enum('Pengeluaran','Pemasukan','Penjualan','Pembelian','Retur Penjualan','Retur Pembelian') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Pengeluaran, Pemasukan, Pembelian, Penjualan',
  `tanggal` datetime NOT NULL COMMENT 'Tanggal-Jam Bayar',
  `jumlah` int DEFAULT NULL COMMENT 'Jumlah Nominnal Pembayaran',
  `creat_at` datetime NOT NULL COMMENT 'Waktu data dibuat',
  `creat_by_id` int UNSIGNED DEFAULT NULL COMMENT 'Petugas yang membuat',
  `creat_by_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Nama Creator Data',
  `update_at` datetime NOT NULL COMMENT 'Tanggl & Jam Update',
  `update_by_id` int UNSIGNED DEFAULT NULL COMMENT 'User Akses Yang Update',
  `update_by_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Nama Updater Data',
  PRIMARY KEY (`id_transaksi_pembayaran`),
  KEY `id_transaksi_jual_beli` (`id_transaksi_jual_beli`),
  KEY `pembayaran_to_akses_1` (`creat_by_id`),
  KEY `pembayaran_to_akses_2` (`update_by_id`),
  KEY `pembayaran_to_transaksi` (`id_transaksi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_rincian`
--

DROP TABLE IF EXISTS `transaksi_rincian`;
CREATE TABLE IF NOT EXISTS `transaksi_rincian` (
  `id_transaksi_rincian` int NOT NULL AUTO_INCREMENT,
  `id_transaksi` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `rincian_transaksi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `harga` int DEFAULT NULL,
  `qty` int DEFAULT NULL,
  `satuan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  PRIMARY KEY (`id_transaksi_rincian`),
  KEY `id_transaksi` (`id_transaksi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_tempo`
--

DROP TABLE IF EXISTS `transaksi_tempo`;
CREATE TABLE IF NOT EXISTS `transaksi_tempo` (
  `id_transaksi_tempo` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_transaksi` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'ID dari transaksi operasional',
  `id_transaksi_jual_beli` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'ID dari transaksi_jual_beli',
  `kategori` enum('Pengeluaran','Pemasukan','Penjualan','Pembelian','Retur Penjualan','Retur Pembelian') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'kategori transaksi',
  `tanggal_tempo` date NOT NULL COMMENT 'Tanggal jatuh tempo',
  PRIMARY KEY (`id_transaksi_tempo`),
  KEY `tempo_to_jual_beli` (`id_transaksi_jual_beli`),
  KEY `tempo_to_transaksi` (`id_transaksi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Mencatat tanggal jatuh tempo';

--
-- Constraints for dumped tables
--

--
-- Constraints for table `akses_ijin`
--
ALTER TABLE `akses_ijin`
  ADD CONSTRAINT `ijin_to_akses` FOREIGN KEY (`id_akses`) REFERENCES `akses` (`id_akses`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ijin_to_fitur` FOREIGN KEY (`id_akses_fitur`) REFERENCES `akses_fitur` (`id_akses_fitur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `akses_login`
--
ALTER TABLE `akses_login`
  ADD CONSTRAINT `login_to_akses` FOREIGN KEY (`id_akses`) REFERENCES `akses` (`id_akses`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `akses_referensi`
--
ALTER TABLE `akses_referensi`
  ADD CONSTRAINT `referensi_to_entitas` FOREIGN KEY (`uuid_akses_entitas`) REFERENCES `akses_entitas` (`uuid_akses_entitas`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `referensi_to_fitur` FOREIGN KEY (`id_akses_fitur`) REFERENCES `akses_fitur` (`id_akses_fitur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `barang_to_medication` FOREIGN KEY (`id_index_medication`) REFERENCES `medication` (`id_index_medication`) ON DELETE SET NULL ON UPDATE RESTRICT;

--
-- Constraints for table `barang_bacth`
--
ALTER TABLE `barang_bacth`
  ADD CONSTRAINT `batc_to_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `barang_diskon`
--
ALTER TABLE `barang_diskon`
  ADD CONSTRAINT `diskon_to_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `barang_harga`
--
ALTER TABLE `barang_harga`
  ADD CONSTRAINT `harga_to_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `harga_to_kategori` FOREIGN KEY (`id_barang_kategori_harga`) REFERENCES `barang_kategori_harga` (`id_barang_kategori_harga`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `barang_satuan`
--
ALTER TABLE `barang_satuan`
  ADD CONSTRAINT `satuan_to_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `diagnosis`
--
ALTER TABLE `diagnosis`
  ADD CONSTRAINT `diagnosis_to_akses_1` FOREIGN KEY (`creat_by_id`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `diagnosis_to_akses_2` FOREIGN KEY (`update_by_id`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `diagnosis_to_anggota` FOREIGN KEY (`id_pasien`) REFERENCES `anggota` (`id_pasien`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `diagnosis_to_kunjungan` FOREIGN KEY (`id_kunjungan`) REFERENCES `kunjungan` (`id_kunjungan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `diagnosis_to_medical_personel` FOREIGN KEY (`medicalPersonelId`) REFERENCES `medical_personel` (`medicalPersonelId`) ON DELETE SET NULL ON UPDATE RESTRICT;

--
-- Constraints for table `dokumentasi`
--
ALTER TABLE `dokumentasi`
  ADD CONSTRAINT `dokumentasi_to_akses` FOREIGN KEY (`id_akses`) REFERENCES `akses` (`id_akses`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dokumentasi_konten`
--
ALTER TABLE `dokumentasi_konten`
  ADD CONSTRAINT `konten_to_dokumentasi` FOREIGN KEY (`id_dokumentasi`) REFERENCES `dokumentasi` (`id_dokumentasi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dokumentasi_tags`
--
ALTER TABLE `dokumentasi_tags`
  ADD CONSTRAINT `tags_to_dokumentasi` FOREIGN KEY (`id_dokumentasi`) REFERENCES `dokumentasi` (`id_dokumentasi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `jurnal`
--
ALTER TABLE `jurnal`
  ADD CONSTRAINT `jurnal_to_jual_beli` FOREIGN KEY (`id_transaksi_jual_beli`) REFERENCES `transaksi_jual_beli` (`id_transaksi_jual_beli`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jurnal_to_pembayaran` FOREIGN KEY (`id_transaksi_pembayaran`) REFERENCES `transaksi_pembayaran` (`id_transaksi_pembayaran`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jurnal_to_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kunjungan`
--
ALTER TABLE `kunjungan`
  ADD CONSTRAINT `kunjungan_anggota` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `kunjungan_to_akses_1` FOREIGN KEY (`creat_by_id`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `kunjungan_to_akses_2` FOREIGN KEY (`update_by_id`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `kunjungan_to_dokter_1` FOREIGN KEY (`id_dokter_penerima`) REFERENCES `medical_personel` (`medicalPersonelId`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `kunjungan_to_dokter_2` FOREIGN KEY (`id_dpjp`) REFERENCES `medical_personel` (`medicalPersonelId`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `kunjungan_to_poliklinik` FOREIGN KEY (`id_poli`) REFERENCES `polyclinic` (`polyclinicId`) ON DELETE SET NULL ON UPDATE RESTRICT;

--
-- Constraints for table `log`
--
ALTER TABLE `log`
  ADD CONSTRAINT `log_to_akses` FOREIGN KEY (`id_akses`) REFERENCES `akses` (`id_akses`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `medical_personel`
--
ALTER TABLE `medical_personel`
  ADD CONSTRAINT `medical_personel_to_akses_1` FOREIGN KEY (`creat_by_id`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `medical_personel_to_akses_2` FOREIGN KEY (`update_by_id`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `medical_personel_to_akses_3` FOREIGN KEY (`id_akses`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT;

--
-- Constraints for table `medication_dispense`
--
ALTER TABLE `medication_dispense`
  ADD CONSTRAINT `dispense_to_request` FOREIGN KEY (`MedicationRequestId`) REFERENCES `medication_request` (`MedicationRequestId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `medication_request`
--
ALTER TABLE `medication_request`
  ADD CONSTRAINT `medication_request_to_group` FOREIGN KEY (`id_medication_request_group`) REFERENCES `medication_request_group` (`id_medication_request_group`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `medication_request_to_medication` FOREIGN KEY (`id_index_medication`) REFERENCES `medication` (`id_index_medication`) ON DELETE SET NULL ON UPDATE RESTRICT;

--
-- Constraints for table `medication_request_group`
--
ALTER TABLE `medication_request_group`
  ADD CONSTRAINT `medication_request_group_to_akses_1` FOREIGN KEY (`creat_by_id`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `medication_request_group_to_akses_2` FOREIGN KEY (`update_by_id`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `medication_request_group_to_anggota` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `medication_request_group_to_kunjungan` FOREIGN KEY (`id_kunjungan`) REFERENCES `kunjungan` (`id_kunjungan`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `medication_request_group_to_medical_personel_1` FOREIGN KEY (`dokter_id`) REFERENCES `medical_personel` (`medicalPersonelId`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `medication_request_group_to_medical_personel_2` FOREIGN KEY (`apoteker_id`) REFERENCES `medical_personel` (`medicalPersonelId`) ON DELETE SET NULL ON UPDATE RESTRICT;

--
-- Constraints for table `polyclinic`
--
ALTER TABLE `polyclinic`
  ADD CONSTRAINT `polyclinic_to_akses_1` FOREIGN KEY (`creat_by_id`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `polyclinic_to_akses_2` FOREIGN KEY (`update_by_id`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT;

--
-- Constraints for table `setting_autojurnal_jual_beli`
--
ALTER TABLE `setting_autojurnal_jual_beli`
  ADD CONSTRAINT `auto_jurnal_to_akun_perkiraan_1` FOREIGN KEY (`debet`) REFERENCES `akun_perkiraan` (`id_perkiraan`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `auto_jurnal_to_akun_perkiraan_2` FOREIGN KEY (`kredit`) REFERENCES `akun_perkiraan` (`id_perkiraan`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `auto_jurnal_to_akun_perkiraan_3` FOREIGN KEY (`utang_piutang`) REFERENCES `akun_perkiraan` (`id_perkiraan`) ON DELETE SET NULL ON UPDATE RESTRICT;

--
-- Constraints for table `stock_opname`
--
ALTER TABLE `stock_opname`
  ADD CONSTRAINT `so_to_akses_1` FOREIGN KEY (`creatBy`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `so_to_akses_2` FOREIGN KEY (`updateBy`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT;

--
-- Constraints for table `stock_opname_barang`
--
ALTER TABLE `stock_opname_barang`
  ADD CONSTRAINT `so_barang_to_akses_1` FOREIGN KEY (`creatBy`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `so_barang_to_akses_2` FOREIGN KEY (`updateBy`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `so_barang_to_so` FOREIGN KEY (`id_stock_opname`) REFERENCES `stock_opname` (`id_stock_opname`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `so_to_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_to_akun_1` FOREIGN KEY (`creat_by_id`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `transaksi_to_akun_2` FOREIGN KEY (`update_by_id`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `transaksi_to_jenis` FOREIGN KEY (`id_transaksi_jenis`) REFERENCES `transaksi_jenis` (`id_transaksi_jenis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaksi_bulk`
--
ALTER TABLE `transaksi_bulk`
  ADD CONSTRAINT `bulk_to_akses` FOREIGN KEY (`id_akses`) REFERENCES `akses` (`id_akses`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bulk_to_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaksi_jenis`
--
ALTER TABLE `transaksi_jenis`
  ADD CONSTRAINT `debet_to_akun_perkiraan` FOREIGN KEY (`id_akun_debet`) REFERENCES `akun_perkiraan` (`id_perkiraan`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `kredit_to_akun_perkiraan` FOREIGN KEY (`id_akun_kredit`) REFERENCES `akun_perkiraan` (`id_perkiraan`) ON DELETE SET NULL ON UPDATE RESTRICT;

--
-- Constraints for table `transaksi_jual_beli`
--
ALTER TABLE `transaksi_jual_beli`
  ADD CONSTRAINT `transaksi_anggota` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_to_akses_1` FOREIGN KEY (`creat_by_id`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `transaksi_to_akses_2` FOREIGN KEY (`update_by_id`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `transaksi_to_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`) ON DELETE SET NULL ON UPDATE RESTRICT;

--
-- Constraints for table `transaksi_jual_beli_rincian`
--
ALTER TABLE `transaksi_jual_beli_rincian`
  ADD CONSTRAINT `rincian_to_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `rincian_to_jual_beli` FOREIGN KEY (`id_transaksi_jual_beli`) REFERENCES `transaksi_jual_beli` (`id_transaksi_jual_beli`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaksi_pembayaran`
--
ALTER TABLE `transaksi_pembayaran`
  ADD CONSTRAINT `pembayaran_to_akses_1` FOREIGN KEY (`creat_by_id`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `pembayaran_to_akses_2` FOREIGN KEY (`update_by_id`) REFERENCES `akses` (`id_akses`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `pembayaran_to_jual_beli` FOREIGN KEY (`id_transaksi_jual_beli`) REFERENCES `transaksi_jual_beli` (`id_transaksi_jual_beli`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pembayaran_to_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaksi_rincian`
--
ALTER TABLE `transaksi_rincian`
  ADD CONSTRAINT `rincian_to_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaksi_tempo`
--
ALTER TABLE `transaksi_tempo`
  ADD CONSTRAINT `tempo_to_jual_beli` FOREIGN KEY (`id_transaksi_jual_beli`) REFERENCES `transaksi_jual_beli` (`id_transaksi_jual_beli`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tempo_to_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

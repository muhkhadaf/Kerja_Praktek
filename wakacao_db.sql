-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2025 at 06:17 PM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 7.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wakacao_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL,
  `id_karyawan` varchar(10) NOT NULL,
  `tanggal` date NOT NULL,
  `id_shift` int(11) DEFAULT NULL,
  `check_in` datetime DEFAULT NULL,
  `foto_check_in` varchar(255) DEFAULT NULL,
  `check_out` datetime DEFAULT NULL,
  `foto_check_out` varchar(255) DEFAULT NULL,
  `status_check_in` enum('tepat waktu','terlambat','tidak absen') DEFAULT 'tidak absen',
  `latitude_in` decimal(10,8) DEFAULT NULL,
  `longitude_in` decimal(11,8) DEFAULT NULL,
  `status_check_out` enum('tepat waktu','lebih awal','tidak absen') DEFAULT 'tidak absen',
  `latitude_out` decimal(10,8) DEFAULT NULL,
  `longitude_out` decimal(11,8) DEFAULT NULL,
  `location_status_in` varchar(10) DEFAULT NULL,
  `location_info_in` varchar(255) DEFAULT NULL,
  `location_status_out` varchar(10) DEFAULT NULL,
  `location_info_out` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id`, `id_karyawan`, `tanggal`, `id_shift`, `check_in`, `foto_check_in`, `check_out`, `foto_check_out`, `status_check_in`, `latitude_in`, `longitude_in`, `status_check_out`, `latitude_out`, `longitude_out`, `location_status_in`, `location_info_in`, `location_status_out`, `location_info_out`) VALUES
(1, '001', '2025-04-26', 1, '2025-04-26 07:15:00', 'uploads/absensi/001_20240601_check_in.jpg', '2025-04-26 15:05:00', 'uploads/absensi/001_20240601_check_out.jpg', 'tepat waktu', NULL, NULL, 'tepat waktu', NULL, NULL, NULL, NULL, NULL, NULL),
(2, '001', '2025-04-27', 3, '2025-04-26 01:57:17', 'uploads/absensi/001_2025-04-27_check_in_1745607437.jpg', '2025-04-26 21:09:23', 'uploads/absensi/001_2025-04-27_check_out_1745676563.jpg', 'tepat waktu', NULL, NULL, 'tepat waktu', '-6.13031480', '106.90930000', NULL, NULL, 'valid', 'Wakacao Bintaro'),
(3, '001', '2025-04-28', 1, '2025-04-26 02:44:19', 'uploads/absensi/001_2025-04-28_check_in_1745610259.jpg', '2025-04-26 02:43:33', 'uploads/absensi/001_2025-04-28_check_out_1745610213.jpg', '', '-6.13031480', '106.90930000', '', '-6.13031480', '106.90930000', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(4, '002', '2025-04-28', 2, '2025-04-26 16:35:35', 'uploads/absensi/002_2025-04-28_check_in_1745660135.jpg', '2025-04-26 16:36:00', 'uploads/absensi/002_2025-04-28_check_out_1745660160.jpg', '', '-6.13031480', '106.90930000', '', '-6.13031480', '106.90930000', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(5, '002', '2025-04-29', 2, '2025-04-26 16:42:54', 'uploads/absensi/002_2025-04-29_check_in_1745660574.jpg', '2025-04-26 16:43:01', 'uploads/absensi/002_2025-04-29_check_out_1745660581.jpg', '', '-6.13031480', '106.90930000', '', '-6.13031480', '106.90930000', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(6, '002', '2025-04-27', 1, '2025-04-26 16:43:44', 'uploads/absensi/002_2025-04-27_check_in_1745660624.jpg', '2025-04-26 16:55:34', 'uploads/absensi/002_2025-04-27_check_out_1745661334.jpg', '', '-6.13031480', '106.90930000', '', '-6.13031480', '106.90930000', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(7, '002', '2025-04-26', 1, '2025-04-26 16:55:46', 'uploads/absensi/002_2025-04-26_check_in_1745661346.jpg', '2025-04-26 16:58:19', 'uploads/absensi/002_2025-04-26_check_out_1745661499.jpg', '', '-6.13031480', '106.90930000', '', '-6.13031480', '106.90930000', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(8, '002', '2025-04-30', 1, '2025-04-26 17:02:57', 'uploads/absensi/002_2025-04-30_check_in_1745661777.jpg', '2025-04-26 17:16:09', 'uploads/absensi/002_2025-04-30_check-out_1745662569.jpg', '', '-6.13031480', '106.90930000', '', '-6.13031480', '106.90930000', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(9, '002', '2025-05-01', 2, '2025-04-26 17:21:18', 'uploads/absensi/002_2025-05-01_check_in_1745662878.jpg', '2025-04-26 17:18:49', 'uploads/absensi/002_2025-05-01_check-in_1745662729.jpg', '', '-6.13031480', '106.90930000', '', '-6.36316420', '106.73359160', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (8490m dari lokasi, melebihi radius 100m)'),
(10, '002', '2025-05-02', 2, '2025-04-26 17:21:30', 'uploads/absensi/002_2025-05-02_check_in_1745662890.jpg', '2025-04-26 17:16:54', 'uploads/absensi/002_2025-05-02_check-in_1745662614.jpg', '', '-6.13031480', '106.90930000', '', '-6.13031480', '106.90930000', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(11, '001', '2025-04-29', 1, '2025-04-26 17:25:33', 'uploads/absensi/001_2025-04-29_check_in_1745663133.jpg', '2025-04-26 17:25:43', 'uploads/absensi/001_2025-04-29_check_out_1745663143.jpg', '', '-6.13031480', '106.90930000', '', '-6.13031480', '106.90930000', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(12, '001', '2025-04-30', 1, '2025-04-26 17:32:54', 'uploads/absensi/001_2025-04-30_check_in_1745663574.jpg', '2025-04-26 20:09:23', 'uploads/absensi/001_2025-04-30_check_out_1745672963.jpg', '', '-6.13031480', '106.90930000', '', '-6.13031480', '106.90930000', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(13, '001', '2025-05-02', 1, '2025-05-02 07:15:00', 'uploads/absensi/001_20240601_check_in.jpg', '2025-05-02 15:05:00', 'uploads/absensi/001_20240601_check_out.jpg', 'tepat waktu', '-6.13031480', '106.90930000', 'tepat waktu', '-6.38976000', '106.78763520', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', NULL, 'Latitude: -6.38976, Longitude: 106.7876352'),
(14, '001', '2025-05-01', 1, '2025-04-26 20:09:44', 'uploads/absensi/001_2025-05-01_check_in_1745672984.jpg', NULL, NULL, '', '-6.13031480', '106.90930000', 'tidak absen', NULL, NULL, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', NULL, NULL),
(15, '090903', '2025-04-26', 2, '2025-04-26 20:29:28', 'uploads/absensi/090903_2025-04-26_check_in_1745674168.jpg', '2025-04-26 20:47:07', 'uploads/absensi/090903_2025-04-26_check_out_1745675227.jpg', '', '-6.13031480', '106.90930000', '', '-6.13031480', '106.90930000', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(16, '230901', '2025-04-26', 1, '2025-04-26 20:43:56', 'uploads/absensi/230901_2025-04-26_check_in_1745675036.jpg', '2025-04-26 20:45:47', 'uploads/absensi/230901_2025-04-26_check_out_1745675147.jpg', '', '-6.13031480', '106.90930000', '', '-6.13031480', '106.90930000', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(17, '230901', '2025-04-27', 1, '2025-04-26 20:45:34', 'uploads/absensi/230901_2025-04-27_check_in_1745675134.jpg', NULL, NULL, '', '-6.13031480', '106.90930000', 'tidak absen', NULL, NULL, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', NULL, NULL),
(18, '090903', '2025-04-30', 1, '2025-04-30 15:13:53', 'uploads/absensi/090903_2025-04-30_check_in_1746000833.jpg', '2025-04-30 15:14:07', 'uploads/absensi/090903_2025-04-30_check_out_1746000847.jpg', 'terlambat', '-6.13031480', '106.90930000', 'tepat waktu', '-6.13031480', '106.90930000', 'valid', 'Wakacao Bintaro', 'valid', 'Wakacao Bintaro'),
(19, '090903', '2025-05-01', 1, '2025-04-30 15:14:18', 'uploads/absensi/090903_2025-05-01_check_in_1746000858.jpg', '2025-04-30 15:15:44', 'uploads/absensi/090903_2025-05-01_check_out_1746000944.jpg', 'terlambat', '-6.13031480', '106.90930000', 'tepat waktu', '-6.13031480', '106.90930000', 'valid', 'Wakacao Bintaro', 'valid', 'Wakacao Bintaro'),
(20, '090903', '2025-05-02', 1, '2025-04-30 15:16:07', 'uploads/absensi/090903_2025-05-02_check_in_1746000967.jpg', '2025-04-30 15:17:23', 'uploads/absensi/090903_2025-05-02_check_out_1746001043.jpg', 'terlambat', '-6.13031480', '106.90930000', 'tepat waktu', '-6.13031480', '106.90930000', 'valid', 'Wakacao Bintaro', 'valid', 'Wakacao Bintaro'),
(21, '090903', '2025-05-03', 1, '2025-04-30 15:20:10', 'uploads/absensi/090903_2025-05-03_check_in_1746001210.jpg', '2025-04-30 15:21:34', 'uploads/absensi/090903_2025-05-03_check_out_1746001294.jpg', 'tepat waktu', '-6.13031480', '106.90930000', 'tepat waktu', '-6.13031480', '106.90930000', 'valid', 'Wakacao Bintaro', 'valid', 'Wakacao Bintaro'),
(22, '090903', '2025-05-04', 1, '2025-04-30 15:22:07', 'uploads/absensi/090903_2025-05-04_check_in_1746001327.jpg', '2025-04-30 15:23:06', 'uploads/absensi/090903_2025-05-04_check_out_1746001386.jpg', '', '-6.13031480', '106.90930000', '', '-6.13031480', '106.90930000', 'valid', 'Wakacao Bintaro', 'valid', 'Wakacao Bintaro'),
(23, '230901', '2025-05-02', 1, '2025-05-02 22:27:08', 'uploads/absensi/230901_2025-05-02_check_in_1746199628.jpg', '2025-05-02 22:27:16', 'uploads/absensi/230901_2025-05-02_check_out_1746199636.jpg', 'terlambat', '-6.38976000', '106.78763520', 'tepat waktu', '-6.38976000', '106.78763520', NULL, 'Latitude: -6.38976, Longitude: 106.7876352', NULL, 'Latitude: -6.38976, Longitude: 106.7876352'),
(24, '230901', '2025-05-03', 1, '2025-05-02 22:38:38', 'uploads/absensi/230901_2025-05-03_check_in_1746200318.jpg', NULL, NULL, 'terlambat', '-6.38976000', '106.78763520', 'tidak absen', NULL, NULL, NULL, 'Latitude: -6.38976, Longitude: 106.7876352', NULL, NULL),
(25, '1211111', '2025-05-02', 3, '2025-05-02 22:40:43', 'uploads/absensi/1211111_2025-05-02_check_in_1746200443.jpg', '2025-05-02 22:41:39', 'uploads/absensi/1211111_2025-05-02_check_out_1746200499.jpg', 'tepat waktu', '-6.38976000', '106.78763520', 'tepat waktu', '-6.38976000', '106.78763520', NULL, 'Latitude: -6.38976, Longitude: 106.7876352', NULL, 'Latitude: -6.38976, Longitude: 106.7876352'),
(26, '1211111', '2025-05-03', 3, '2025-05-02 22:42:59', 'uploads/absensi/1211111_2025-05-03_check_in_1746200579.jpg', '2025-05-02 22:50:56', 'uploads/absensi/1211111_2025-05-03_check_out_1746201056.jpg', 'tepat waktu', '-6.38976000', '106.78763520', 'tepat waktu', '-6.38976000', '106.78763520', NULL, 'Latitude: -6.38976, Longitude: 106.7876352', NULL, 'Latitude: -6.38976, Longitude: 106.7876352'),
(27, '1211111', '2025-05-04', 3, '2025-05-02 22:52:26', 'uploads/absensi/1211111_2025-05-04_check_in_1746201146.jpg', '2025-05-02 22:53:17', 'uploads/absensi/1211111_2025-05-04_check_out_1746201197.jpg', 'tepat waktu', '-6.38976000', '106.78763520', 'tepat waktu', '-6.36318350', '106.73361440', NULL, 'Latitude: -6.38976, Longitude: 106.7876352', NULL, 'Latitude: -6.3631835, Longitude: 106.7336144'),
(28, '090903', '2025-05-05', 1, '2025-05-05 21:42:53', 'uploads/absensi/090903_2025-05-05_check_in_1746456173.jpg', NULL, NULL, 'terlambat', '-6.35397920', '106.70799600', 'tidak absen', NULL, NULL, NULL, 'Latitude: -6.3539792, Longitude: 106.707996', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cuti_tahunan`
--

CREATE TABLE `cuti_tahunan` (
  `id` int(11) NOT NULL,
  `id_karyawan` varchar(10) NOT NULL,
  `outlet` varchar(50) NOT NULL,
  `nama_karyawan` varchar(100) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `durasi` int(11) NOT NULL,
  `alasan` text NOT NULL,
  `status` enum('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending',
  `keterangan_admin` text DEFAULT NULL,
  `approved_by` varchar(50) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cuti_tahunan`
--

INSERT INTO `cuti_tahunan` (`id`, `id_karyawan`, `outlet`, `nama_karyawan`, `tanggal_mulai`, `tanggal_selesai`, `durasi`, `alasan`, `status`, `keterangan_admin`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, '090903', 'Wakacao BSD', 'MUJI NUR FADILAH', '2025-09-23', '2025-10-01', 9, 'mau pulang', 'disetujui', '', 'Admin User', '2025-04-30 12:38:06', '2025-04-30 05:16:35', '2025-04-30 05:38:06'),
(2, '090903', 'Wakacao BSD', 'MUJI NUR FADILAH', '2025-09-23', '2025-10-01', 9, 'mau pulang', 'disetujui', 'oke gas keun', 'Admin User', '2025-04-30 12:22:30', '2025-04-30 05:21:39', '2025-04-30 05:22:30'),
(3, '090903', 'Wakacao BSD', 'MUJI NUR FADILAH', '2025-09-23', '2025-10-01', 9, 'mau pulang', 'ditolak', 'jangan ya dek ya', 'Admin User', '2025-04-30 12:22:19', '2025-04-30 05:21:51', '2025-04-30 05:22:19'),
(4, '090903', 'Wakacao BSD', 'MUJI NUR FADILAH', '2025-09-23', '2025-10-01', 9, 'mau pulang', 'disetujui', '', 'Admin User', '2025-04-30 12:38:02', '2025-04-30 05:25:03', '2025-04-30 05:38:02'),
(5, '090903', 'Wakacao BSD', 'MUJI NUR FADILAH', '2025-09-09', '2025-10-09', 31, 'ya', 'disetujui', '', 'Admin User', '2025-04-30 12:37:57', '2025-04-30 05:25:54', '2025-04-30 05:37:57'),
(6, '090903', 'Wakacao BSD', 'MUJI NUR FADILAH', '2026-02-12', '2026-03-12', 29, 'mau pulkam sek', 'ditolak', 'jangan dek ya', 'AKU ADMIN', '2025-05-02 19:06:01', '2025-05-01 10:31:58', '2025-05-02 12:06:01');

-- --------------------------------------------------------

--
-- Table structure for table `izin`
--

CREATE TABLE `izin` (
  `id` int(11) NOT NULL,
  `id_karyawan` varchar(10) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `jenis_izin` enum('sakit','izin','cuti','lainnya') NOT NULL,
  `keterangan` text DEFAULT NULL,
  `bukti_file` varchar(255) DEFAULT NULL,
  `solusi_pengganti` enum('shift','libur','cuti','gaji') NOT NULL,
  `status` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `izin`
--

INSERT INTO `izin` (`id`, `id_karyawan`, `tanggal_mulai`, `tanggal_selesai`, `jenis_izin`, `keterangan`, `bukti_file`, `solusi_pengganti`, `status`, `created_at`) VALUES
(1, '001', '2025-04-26', '0000-00-00', 'sakit', 'sss', '', 'shift', 'disetujui', '2025-04-25 18:46:24'),
(2, '090903', '2025-05-05', '2025-05-05', 'sakit', 'Batuk', 'uploads/izin/090903_2025-05-05_1746456448.jpeg', 'libur', 'pending', '2025-05-05 14:47:28');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `id` int(11) NOT NULL,
  `id_karyawan` varchar(10) NOT NULL,
  `tanggal` date NOT NULL,
  `id_shift` int(11) DEFAULT NULL,
  `status` enum('masuk','libur','izin','sakit','cuti') DEFAULT 'masuk'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `jadwal`
--

INSERT INTO `jadwal` (`id`, `id_karyawan`, `tanggal`, `id_shift`, `status`) VALUES
(374, '002', '2025-04-21', 1, 'masuk'),
(375, '002', '2025-04-22', 1, 'masuk'),
(376, '002', '2025-04-23', 1, 'masuk'),
(377, '002', '2025-04-24', 1, 'masuk'),
(378, '002', '2025-04-25', 1, 'masuk'),
(379, '002', '2025-04-26', 1, 'masuk'),
(380, '002', '2025-04-27', 1, 'masuk'),
(381, '001', '2025-04-21', 2, 'masuk'),
(382, '001', '2025-04-22', 1, 'masuk'),
(383, '001', '2025-04-23', NULL, 'libur'),
(384, '001', '2025-04-24', 1, 'masuk'),
(385, '001', '2025-04-25', 2, 'masuk'),
(386, '001', '2025-04-26', 3, 'masuk'),
(387, '001', '2025-04-27', 1, 'masuk'),
(388, '230901', '2025-04-21', 1, 'masuk'),
(389, '230901', '2025-04-22', 1, 'masuk'),
(390, '230901', '2025-04-23', 1, 'masuk'),
(391, '230901', '2025-04-24', 1, 'masuk'),
(392, '230901', '2025-04-25', 1, 'masuk'),
(393, '230901', '2025-04-26', 1, 'masuk'),
(394, '230901', '2025-04-27', 1, 'masuk'),
(395, '003', '2025-04-21', 2, 'masuk'),
(396, '003', '2025-04-22', 3, 'masuk'),
(397, '003', '2025-04-23', 1, 'masuk'),
(398, '003', '2025-04-24', 2, 'masuk'),
(399, '003', '2025-04-25', 2, 'masuk'),
(400, '003', '2025-04-26', 2, 'masuk'),
(401, '003', '2025-04-27', 2, 'masuk'),
(444, '090903', '2025-09-23', NULL, 'cuti'),
(445, '090903', '2025-09-24', NULL, 'cuti'),
(446, '090903', '2025-09-25', NULL, 'cuti'),
(447, '090903', '2025-09-26', NULL, 'cuti'),
(448, '090903', '2025-09-27', NULL, 'cuti'),
(449, '090903', '2025-09-28', NULL, 'cuti'),
(450, '090903', '2025-09-29', NULL, 'cuti'),
(451, '090903', '2025-09-30', NULL, 'cuti'),
(452, '090903', '2025-10-01', NULL, 'cuti'),
(453, '090903', '2025-09-09', NULL, 'cuti'),
(454, '090903', '2025-09-10', NULL, 'cuti'),
(455, '090903', '2025-09-11', NULL, 'cuti'),
(456, '090903', '2025-09-12', NULL, 'cuti'),
(457, '090903', '2025-09-13', NULL, 'cuti'),
(458, '090903', '2025-09-14', NULL, 'cuti'),
(459, '090903', '2025-09-15', NULL, 'cuti'),
(460, '090903', '2025-09-16', NULL, 'cuti'),
(461, '090903', '2025-09-17', NULL, 'cuti'),
(462, '090903', '2025-09-18', NULL, 'cuti'),
(463, '090903', '2025-09-19', NULL, 'cuti'),
(464, '090903', '2025-09-20', NULL, 'cuti'),
(465, '090903', '2025-09-21', NULL, 'cuti'),
(466, '090903', '2025-09-22', NULL, 'cuti'),
(467, '090903', '2025-10-02', NULL, 'cuti'),
(468, '090903', '2025-10-03', NULL, 'cuti'),
(469, '090903', '2025-10-04', NULL, 'cuti'),
(470, '090903', '2025-10-05', NULL, 'cuti'),
(471, '090903', '2025-10-06', NULL, 'cuti'),
(472, '090903', '2025-10-07', NULL, 'cuti'),
(473, '090903', '2025-10-08', NULL, 'cuti'),
(474, '090903', '2025-10-09', NULL, 'cuti'),
(517, '002', '2025-04-28', 2, 'masuk'),
(518, '002', '2025-04-29', 2, 'masuk'),
(519, '002', '2025-04-30', 2, 'masuk'),
(520, '002', '2025-05-01', 2, 'masuk'),
(521, '002', '2025-05-02', 2, 'masuk'),
(522, '002', '2025-05-03', 1, 'masuk'),
(523, '002', '2025-05-04', 2, 'masuk'),
(524, '001', '2025-04-28', 2, 'masuk'),
(525, '001', '2025-04-29', 1, 'masuk'),
(526, '001', '2025-04-30', 1, 'masuk'),
(527, '001', '2025-05-01', 1, 'masuk'),
(528, '001', '2025-05-02', 1, 'masuk'),
(529, '001', '2025-05-03', 1, 'masuk'),
(530, '001', '2025-05-04', 1, 'masuk'),
(531, '230901', '2025-04-28', 1, 'masuk'),
(532, '230901', '2025-04-29', 1, 'masuk'),
(533, '230901', '2025-04-30', 1, 'masuk'),
(534, '230901', '2025-05-01', 1, 'masuk'),
(535, '230901', '2025-05-02', 1, 'masuk'),
(536, '230901', '2025-05-03', 1, 'masuk'),
(537, '230901', '2025-05-04', 1, 'masuk'),
(538, '1211111', '2025-04-28', 2, 'masuk'),
(539, '1211111', '2025-04-29', 3, 'masuk'),
(540, '1211111', '2025-04-30', 2, 'masuk'),
(541, '1211111', '2025-05-01', 3, 'masuk'),
(542, '1211111', '2025-05-02', 3, 'masuk'),
(543, '1211111', '2025-05-03', 3, 'masuk'),
(544, '1211111', '2025-05-04', 3, 'masuk'),
(545, '003', '2025-04-28', 2, 'masuk'),
(546, '003', '2025-04-29', 2, 'masuk'),
(547, '003', '2025-04-30', 2, 'masuk'),
(548, '003', '2025-05-01', 2, 'masuk'),
(549, '003', '2025-05-02', 2, 'masuk'),
(550, '003', '2025-05-03', 2, 'masuk'),
(551, '003', '2025-05-04', 2, 'masuk'),
(552, '090903', '2025-04-28', 1, 'masuk'),
(553, '090903', '2025-04-29', 1, 'masuk'),
(554, '090903', '2025-04-30', 1, 'masuk'),
(555, '090903', '2025-05-01', 1, 'masuk'),
(556, '090903', '2025-05-02', 1, 'masuk'),
(557, '090903', '2025-05-03', 1, 'masuk'),
(558, '090903', '2025-05-04', 1, 'masuk'),
(559, '002', '2025-05-05', 2, 'masuk'),
(560, '002', '2025-05-06', 2, 'masuk'),
(561, '002', '2025-05-07', 2, 'masuk'),
(562, '002', '2025-05-08', 2, 'masuk'),
(563, '002', '2025-05-09', 2, 'masuk'),
(564, '002', '2025-05-10', 2, 'masuk'),
(565, '002', '2025-05-11', 2, 'masuk'),
(566, '001', '2025-05-05', 2, 'masuk'),
(567, '001', '2025-05-06', 2, 'masuk'),
(568, '001', '2025-05-07', 2, 'masuk'),
(569, '001', '2025-05-08', 2, 'masuk'),
(570, '001', '2025-05-09', 2, 'masuk'),
(571, '001', '2025-05-10', 2, 'masuk'),
(572, '001', '2025-05-11', 2, 'masuk'),
(573, '230901', '2025-05-05', 2, 'masuk'),
(574, '230901', '2025-05-06', 2, 'masuk'),
(575, '230901', '2025-05-07', 2, 'masuk'),
(576, '230901', '2025-05-08', 2, 'masuk'),
(577, '230901', '2025-05-09', 2, 'masuk'),
(578, '230901', '2025-05-10', 2, 'masuk'),
(579, '230901', '2025-05-11', 2, 'masuk'),
(580, '1211111', '2025-05-05', 2, 'masuk'),
(581, '1211111', '2025-05-06', 2, 'masuk'),
(582, '1211111', '2025-05-07', 2, 'masuk'),
(583, '1211111', '2025-05-08', 2, 'masuk'),
(584, '1211111', '2025-05-09', 2, 'masuk'),
(585, '1211111', '2025-05-10', 2, 'masuk'),
(586, '1211111', '2025-05-11', 2, 'masuk'),
(587, '003', '2025-05-05', 2, 'masuk'),
(588, '003', '2025-05-06', 2, 'masuk'),
(589, '003', '2025-05-07', 2, 'masuk'),
(590, '003', '2025-05-08', 2, 'masuk'),
(591, '003', '2025-05-09', 2, 'masuk'),
(592, '003', '2025-05-10', 2, 'masuk'),
(593, '003', '2025-05-11', 2, 'masuk'),
(594, '090903', '2025-05-05', 1, 'sakit'),
(595, '090903', '2025-05-06', 1, 'masuk'),
(596, '090903', '2025-05-07', 1, 'masuk'),
(597, '090903', '2025-05-08', 1, 'masuk'),
(598, '090903', '2025-05-09', NULL, 'libur'),
(599, '090903', '2025-05-10', 1, 'masuk'),
(600, '090903', '2025-05-11', 1, 'masuk');

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `jenis_laporan` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('Menunggu','Proses','Selesai') DEFAULT 'Menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `laporan`
--

INSERT INTO `laporan` (`id_laporan`, `id_user`, `tanggal`, `jenis_laporan`, `deskripsi`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-05-05 22:49:05', 'Keluhan', 'Contoh keluhan pelanggan', 'Selesai', '2025-05-05 15:49:05', '2025-05-05 15:49:05'),
(2, 1, '2025-05-05 22:49:05', 'Saran', 'Contoh saran pengembangan', 'Proses', '2025-05-05 15:49:05', '2025-05-05 15:49:05'),
(3, 1, '2025-05-05 22:49:05', 'Bug', 'Contoh laporan bug sistem', 'Menunggu', '2025-05-05 15:49:05', '2025-05-05 15:49:05');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `outlet_name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `radius` int(11) NOT NULL DEFAULT 100,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `outlet_name`, `address`, `latitude`, `longitude`, `radius`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Wakacao Bintaro', 'gatau', '-6.13031590', '106.90930000', 100, 1, '2025-04-25 19:26:34', '2025-05-05 15:40:40'),
(2, 'Wakacao BSD', NULL, '-6.28998500', '106.66478000', 80, 1, '2025-04-25 19:26:34', '2025-04-25 19:26:34'),
(3, 'Wakacao Pamulang', NULL, '-6.36313480', '106.33645200', 100, 1, '2025-04-26 13:41:36', '2025-04-26 13:41:36');

-- --------------------------------------------------------

--
-- Table structure for table `outlets`
--

CREATE TABLE `outlets` (
  `id` int(11) NOT NULL,
  `outlet_name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `shift`
--

CREATE TABLE `shift` (
  `id` int(11) NOT NULL,
  `nama_shift` varchar(20) NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `shift`
--

INSERT INTO `shift` (`id`, `nama_shift`, `jam_mulai`, `jam_selesai`) VALUES
(1, 'Pagi', '07:00:00', '15:00:00'),
(2, 'Siang', '15:00:00', '23:00:00'),
(3, 'Malam', '23:00:00', '07:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `id_karyawan` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `outlet` varchar(50) NOT NULL,
  `role` enum('admin','karyawan','supervisor') NOT NULL DEFAULT 'karyawan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `id_karyawan`, `nama`, `email`, `password`, `outlet`, `role`, `created_at`, `updated_at`) VALUES
(1, '001', 'John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bintaro', 'karyawan', '2025-04-25 18:45:39', '2025-04-25 18:45:39'),
(2, '002', 'Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bintaro', 'karyawan', '2025-04-25 18:45:39', '2025-04-25 18:45:39'),
(3, '003', 'Michael Johnson', 'michael@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bintaro', 'karyawan', '2025-04-25 18:45:39', '2025-04-25 18:45:39'),
(4, '004', 'AKU ADMIN', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Wakacao Bintaro', 'admin', '2025-04-25 18:45:39', '2025-04-30 07:50:29'),
(9, '090903', 'MUJI NUR FADILAH', 'muji@gmail.com', '$2y$10$BvQzfCMM9bGwotORYr6Sa.bo7yXAROsQO3v6Yww83YLVLCjKSfGbm', 'Wakacao BSD', 'karyawan', '2025-04-26 13:22:04', '2025-04-26 13:22:04'),
(10, '230901', 'Khadafi', 'khadafi@mail.com', '$2y$10$I.Sizr/Pciur/4SWzuosju5vXCXH8eRL3X2Y2HSB90yCZYg5sk/uy', 'Wakacao Pamulang', 'karyawan', '2025-04-26 13:42:27', '2025-04-26 13:49:42'),
(11, '0929022', 'MUHAMMAD KHADAFI RIYADI', 'adminkhadafi@mail.com', '$2y$10$b9dPJykr83mrEptisQS1K.iVJyk94dSvNZBIrdHrM8HvKST4EsSwi', 'Wakacao Bintaro', 'admin', '2025-05-02 12:07:07', '2025-05-02 12:07:07'),
(12, '1211111', 'KIM MIN JEONG', 'contoh@mail.com', '$2y$10$cKklhu4Kh/DGXGmMRBCAfetUST.oHsvSgqs88FejZJm//DGSQPeIS', 'Wakacao BSD', 'karyawan', '2025-05-02 15:39:42', '2025-05-02 15:42:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_karyawan` (`id_karyawan`),
  ADD KEY `id_shift` (`id_shift`);

--
-- Indexes for table `cuti_tahunan`
--
ALTER TABLE `cuti_tahunan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_karyawan` (`id_karyawan`);

--
-- Indexes for table `izin`
--
ALTER TABLE `izin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_karyawan` (`id_karyawan`);

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_karyawan` (`id_karyawan`),
  ADD KEY `id_shift` (`id_shift`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `outlets`
--
ALTER TABLE `outlets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shift`
--
ALTER TABLE `shift`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_karyawan` (`id_karyawan`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `cuti_tahunan`
--
ALTER TABLE `cuti_tahunan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `izin`
--
ALTER TABLE `izin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=601;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `outlets`
--
ALTER TABLE `outlets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shift`
--
ALTER TABLE `shift`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `users` (`id_karyawan`),
  ADD CONSTRAINT `absensi_ibfk_2` FOREIGN KEY (`id_shift`) REFERENCES `shift` (`id`);

--
-- Constraints for table `izin`
--
ALTER TABLE `izin`
  ADD CONSTRAINT `izin_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `users` (`id_karyawan`);

--
-- Constraints for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `users` (`id_karyawan`),
  ADD CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`id_shift`) REFERENCES `shift` (`id`);

--
-- Constraints for table `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

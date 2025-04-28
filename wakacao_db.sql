-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 26, 2025 at 07:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id`, `id_karyawan`, `tanggal`, `id_shift`, `check_in`, `foto_check_in`, `check_out`, `foto_check_out`, `status_check_in`, `latitude_in`, `longitude_in`, `status_check_out`, `latitude_out`, `longitude_out`, `location_status_in`, `location_info_in`, `location_status_out`, `location_info_out`) VALUES
(1, '001', '2025-04-26', 1, '2025-04-26 07:15:00', 'uploads/absensi/001_20240601_check_in.jpg', '2025-04-26 15:05:00', 'uploads/absensi/001_20240601_check_out.jpg', 'tepat waktu', NULL, NULL, 'tepat waktu', NULL, NULL, NULL, NULL, NULL, NULL),
(2, '001', '2025-04-27', 3, '2025-04-26 01:57:17', 'uploads/absensi/001_2025-04-27_check_in_1745607437.jpg', '2025-04-26 21:09:23', 'uploads/absensi/001_2025-04-27_check_out_1745676563.jpg', 'tepat waktu', NULL, NULL, 'tepat waktu', -6.13031480, 106.90930000, NULL, NULL, 'valid', 'Wakacao Bintaro'),
(3, '001', '2025-04-28', 1, '2025-04-26 02:44:19', 'uploads/absensi/001_2025-04-28_check_in_1745610259.jpg', '2025-04-26 02:43:33', 'uploads/absensi/001_2025-04-28_check_out_1745610213.jpg', '', -6.13031480, 106.90930000, '', -6.13031480, 106.90930000, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(4, '002', '2025-04-28', 2, '2025-04-26 16:35:35', 'uploads/absensi/002_2025-04-28_check_in_1745660135.jpg', '2025-04-26 16:36:00', 'uploads/absensi/002_2025-04-28_check_out_1745660160.jpg', '', -6.13031480, 106.90930000, '', -6.13031480, 106.90930000, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(5, '002', '2025-04-29', 2, '2025-04-26 16:42:54', 'uploads/absensi/002_2025-04-29_check_in_1745660574.jpg', '2025-04-26 16:43:01', 'uploads/absensi/002_2025-04-29_check_out_1745660581.jpg', '', -6.13031480, 106.90930000, '', -6.13031480, 106.90930000, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(6, '002', '2025-04-27', 1, '2025-04-26 16:43:44', 'uploads/absensi/002_2025-04-27_check_in_1745660624.jpg', '2025-04-26 16:55:34', 'uploads/absensi/002_2025-04-27_check_out_1745661334.jpg', '', -6.13031480, 106.90930000, '', -6.13031480, 106.90930000, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(7, '002', '2025-04-26', 1, '2025-04-26 16:55:46', 'uploads/absensi/002_2025-04-26_check_in_1745661346.jpg', '2025-04-26 16:58:19', 'uploads/absensi/002_2025-04-26_check_out_1745661499.jpg', '', -6.13031480, 106.90930000, '', -6.13031480, 106.90930000, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(8, '002', '2025-04-30', 1, '2025-04-26 17:02:57', 'uploads/absensi/002_2025-04-30_check_in_1745661777.jpg', '2025-04-26 17:16:09', 'uploads/absensi/002_2025-04-30_check-out_1745662569.jpg', '', -6.13031480, 106.90930000, '', -6.13031480, 106.90930000, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(9, '002', '2025-05-01', 2, '2025-04-26 17:21:18', 'uploads/absensi/002_2025-05-01_check_in_1745662878.jpg', '2025-04-26 17:18:49', 'uploads/absensi/002_2025-05-01_check-in_1745662729.jpg', '', -6.13031480, 106.90930000, '', -6.36316420, 106.73359160, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (8490m dari lokasi, melebihi radius 100m)'),
(10, '002', '2025-05-02', 2, '2025-04-26 17:21:30', 'uploads/absensi/002_2025-05-02_check_in_1745662890.jpg', '2025-04-26 17:16:54', 'uploads/absensi/002_2025-05-02_check-in_1745662614.jpg', '', -6.13031480, 106.90930000, '', -6.13031480, 106.90930000, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(11, '001', '2025-04-29', 1, '2025-04-26 17:25:33', 'uploads/absensi/001_2025-04-29_check_in_1745663133.jpg', '2025-04-26 17:25:43', 'uploads/absensi/001_2025-04-29_check_out_1745663143.jpg', '', -6.13031480, 106.90930000, '', -6.13031480, 106.90930000, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(12, '001', '2025-04-30', 1, '2025-04-26 17:32:54', 'uploads/absensi/001_2025-04-30_check_in_1745663574.jpg', '2025-04-26 20:09:23', 'uploads/absensi/001_2025-04-30_check_out_1745672963.jpg', '', -6.13031480, 106.90930000, '', -6.13031480, 106.90930000, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(13, '001', '2025-05-02', 2, '2025-04-26 17:31:40', 'uploads/absensi/001_2025-05-02_check_in_1745663500.jpg', NULL, NULL, '', -6.13031480, 106.90930000, 'tidak absen', NULL, NULL, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', NULL, NULL),
(14, '001', '2025-05-01', 1, '2025-04-26 20:09:44', 'uploads/absensi/001_2025-05-01_check_in_1745672984.jpg', NULL, NULL, '', -6.13031480, 106.90930000, 'tidak absen', NULL, NULL, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', NULL, NULL),
(15, '090903', '2025-04-26', 2, '2025-04-26 20:29:28', 'uploads/absensi/090903_2025-04-26_check_in_1745674168.jpg', '2025-04-26 20:47:07', 'uploads/absensi/090903_2025-04-26_check_out_1745675227.jpg', '', -6.13031480, 106.90930000, '', -6.13031480, 106.90930000, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(16, '230901', '2025-04-26', 1, '2025-04-26 20:43:56', 'uploads/absensi/230901_2025-04-26_check_in_1745675036.jpg', '2025-04-26 20:45:47', 'uploads/absensi/230901_2025-04-26_check_out_1745675147.jpg', '', -6.13031480, 106.90930000, '', -6.13031480, 106.90930000, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)'),
(17, '230901', '2025-04-27', 1, '2025-04-26 20:45:34', 'uploads/absensi/230901_2025-04-27_check_in_1745675134.jpg', NULL, NULL, '', -6.13031480, 106.90930000, 'tidak absen', NULL, NULL, 'invalid', 'Wakacao Bintaro (27805m dari lokasi, melebihi radius 100m)', NULL, NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `izin`
--

INSERT INTO `izin` (`id`, `id_karyawan`, `tanggal_mulai`, `tanggal_selesai`, `jenis_izin`, `keterangan`, `bukti_file`, `solusi_pengganti`, `status`, `created_at`) VALUES
(1, '001', '2025-04-26', '0000-00-00', 'sakit', 'sss', '', 'shift', 'pending', '2025-04-25 18:46:24');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(402, '090903', '2025-04-21', 2, 'masuk'),
(403, '090903', '2025-04-22', 2, 'masuk'),
(404, '090903', '2025-04-23', 1, 'masuk'),
(405, '090903', '2025-04-24', 2, 'masuk'),
(406, '090903', '2025-04-25', 2, 'masuk'),
(407, '090903', '2025-04-26', 2, 'masuk'),
(408, '090903', '2025-04-27', 2, 'masuk'),
(409, '002', '2025-04-28', 2, 'masuk'),
(410, '002', '2025-04-29', 2, 'masuk'),
(411, '002', '2025-04-30', 2, 'masuk'),
(412, '002', '2025-05-01', 1, 'masuk'),
(413, '002', '2025-05-02', 2, 'masuk'),
(414, '002', '2025-05-03', 2, 'masuk'),
(415, '002', '2025-05-04', 2, 'masuk'),
(416, '001', '2025-04-28', 1, 'masuk'),
(417, '001', '2025-04-29', 1, 'masuk'),
(418, '001', '2025-04-30', 1, 'masuk'),
(419, '001', '2025-05-01', 1, 'masuk'),
(420, '001', '2025-05-02', 1, 'masuk'),
(421, '001', '2025-05-03', 2, 'masuk'),
(422, '001', '2025-05-04', 1, 'masuk'),
(423, '230901', '2025-04-28', 1, 'masuk'),
(424, '230901', '2025-04-29', 1, 'masuk'),
(425, '230901', '2025-04-30', 1, 'masuk'),
(426, '230901', '2025-05-01', 1, 'masuk'),
(427, '230901', '2025-05-02', 1, 'masuk'),
(428, '230901', '2025-05-03', 1, 'masuk'),
(429, '230901', '2025-05-04', 1, 'masuk'),
(430, '003', '2025-04-28', 2, 'masuk'),
(431, '003', '2025-04-29', 2, 'masuk'),
(432, '003', '2025-04-30', 2, 'masuk'),
(433, '003', '2025-05-01', 2, 'masuk'),
(434, '003', '2025-05-02', 2, 'masuk'),
(435, '003', '2025-05-03', 2, 'masuk'),
(436, '003', '2025-05-04', 2, 'masuk'),
(437, '090903', '2025-04-28', 1, 'masuk'),
(438, '090903', '2025-04-29', 1, 'masuk'),
(439, '090903', '2025-04-30', 1, 'masuk'),
(440, '090903', '2025-05-01', 1, 'masuk'),
(441, '090903', '2025-05-02', 1, 'masuk'),
(442, '090903', '2025-05-03', 1, 'masuk'),
(443, '090903', '2025-05-04', 1, 'masuk');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `outlet_name` varchar(100) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `radius` int(11) NOT NULL DEFAULT 100,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `outlet_name`, `latitude`, `longitude`, `radius`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Wakacao Bintaro', -6.13031590, 106.90930000, 100, 1, '2025-04-25 19:26:34', '2025-04-26 14:06:16'),
(2, 'Wakacao BSD', -6.28998500, 106.66478000, 80, 1, '2025-04-25 19:26:34', '2025-04-25 19:26:34'),
(3, 'Wakacao Pamulang', -6.36313480, 106.33645200, 100, 1, '2025-04-26 13:41:36', '2025-04-26 13:41:36');

-- --------------------------------------------------------

--
-- Table structure for table `shift`
--

CREATE TABLE `shift` (
  `id` int(11) NOT NULL,
  `nama_shift` varchar(20) NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `id_karyawan`, `nama`, `email`, `password`, `outlet`, `role`, `created_at`, `updated_at`) VALUES
(1, '001', 'John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bintaro', 'karyawan', '2025-04-25 18:45:39', '2025-04-25 18:45:39'),
(2, '002', 'Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bintaro', 'karyawan', '2025-04-25 18:45:39', '2025-04-25 18:45:39'),
(3, '003', 'Michael Johnson', 'michael@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bintaro', 'karyawan', '2025-04-25 18:45:39', '2025-04-25 18:45:39'),
(4, '004', 'Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pusat', 'admin', '2025-04-25 18:45:39', '2025-04-25 18:45:39'),
(9, '090903', 'MUJI NUR FADILAH', 'muji@gmail.com', '$2y$10$BvQzfCMM9bGwotORYr6Sa.bo7yXAROsQO3v6Yww83YLVLCjKSfGbm', 'Wakacao BSD', 'karyawan', '2025-04-26 13:22:04', '2025-04-26 13:22:04'),
(10, '230901', 'Khadafi', 'khadafi@mail.com', '$2y$10$I.Sizr/Pciur/4SWzuosju5vXCXH8eRL3X2Y2HSB90yCZYg5sk/uy', 'Wakacao Pamulang', 'karyawan', '2025-04-26 13:42:27', '2025-04-26 13:49:42');

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
-- Indexes for table `locations`
--
ALTER TABLE `locations`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `izin`
--
ALTER TABLE `izin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=444;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `shift`
--
ALTER TABLE `shift`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

<?php
// Include database configuration
require_once 'config.php';

// SQL to create the cuti_tahunan table
$sql = "
CREATE TABLE IF NOT EXISTS `cuti_tahunan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_karyawan` (`id_karyawan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

// Execute the query
if (mysqli_query($koneksi, $sql)) {
    echo "Table 'cuti_tahunan' created successfully or already exists!";
} else {
    echo "Error creating table: " . mysqli_error($koneksi);
}
?> 
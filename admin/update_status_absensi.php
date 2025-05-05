<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!isLoggedIn() || getUserRole() !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

$id_karyawan = isset($_POST['id_karyawan']) ? mysqli_real_escape_string($koneksi, $_POST['id_karyawan']) : '';
$tanggal = isset($_POST['tanggal']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal']) : '';
$jenis = isset($_POST['jenis']) ? $_POST['jenis'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : '';

if (!$id_karyawan || !$tanggal || !$jenis || !$status) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

// Validasi jenis dan status
if ($jenis === 'check_in') {
    $allowed = ['tepat waktu', 'terlambat', 'tidak absen', 'tidak valid'];
    $field = 'status_check_in';
} elseif ($jenis === 'check_out') {
    $allowed = ['tepat waktu', 'lebih awal', 'tidak absen', 'tidak valid'];
    $field = 'status_check_out';
} else {
    echo json_encode(['success' => false, 'message' => 'Jenis status tidak valid']);
    exit;
}

if (!in_array($status, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
    exit;
}

// Update status di database
$query = "UPDATE absensi SET $field = '$status' WHERE id_karyawan = '$id_karyawan' AND tanggal = '$tanggal'";
if (mysqli_query($koneksi, $query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal update status: ' . mysqli_error($koneksi)]);
} 
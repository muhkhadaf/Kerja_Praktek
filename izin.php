<?php
// Include konfigurasi
require_once 'config.php';

// Cek apakah pengguna sudah login
requireLogin();

// Ambil data karyawan yang login
$id_karyawan = getKaryawanId();
$nama = getUserName();

// Menangani pengajuan izin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal_mulai = sanitize($_POST['tanggal_mulai']);
    $tanggal_selesai = sanitize($_POST['tanggal_selesai'] ?? $tanggal_mulai); // Jika tanggal_selesai tidak ada, gunakan tanggal_mulai
    $jenis_izin = sanitize($_POST['jenis_izin']);
    $keterangan = sanitize($_POST['keterangan']);
    $solusi_pengganti = sanitize($_POST['solusi_pengganti']);
    
    // Upload bukti (jika ada)
    $bukti_file = '';
    if (isset($_FILES['bukti_file']) && $_FILES['bukti_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/izin/';
        
        // Membuat direktori jika belum ada
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = $id_karyawan . '_' . $tanggal_mulai . '_' . time() . '.' . pathinfo($_FILES['bukti_file']['name'], PATHINFO_EXTENSION);
        $upload_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['bukti_file']['tmp_name'], $upload_path)) {
            $bukti_file = $upload_path;
        }
    }
    
    // Insert data izin
    $query = "INSERT INTO izin 
        (id_karyawan, tanggal_mulai, tanggal_selesai, jenis_izin, keterangan, bukti_file, solusi_pengganti)
        VALUES 
        ('$id_karyawan', '$tanggal_mulai', '$tanggal_selesai', '$jenis_izin', '$keterangan', '$bukti_file', '$solusi_pengganti')";
    
    if (mysqli_query($koneksi, $query)) {
        // Update status jadwal untuk tanggal-tanggal yang diajukan
        $current_date = $tanggal_mulai;
        while (strtotime($current_date) <= strtotime($tanggal_selesai)) {
            $query_update = "UPDATE jadwal SET status = '$jenis_izin' 
                             WHERE id_karyawan = '$id_karyawan' AND tanggal = '$current_date'";
            mysqli_query($koneksi, $query_update);
            
            // Tambahkan 1 hari
            $current_date = date('Y-m-d', strtotime($current_date . ' + 1 day'));
        }
        
        // Redirect ke halaman utama dengan status sukses
        header("Location: index.php?status=success&jenis=izin");
        exit();
    } else {
        // Jika gagal, kembali ke halaman utama dengan status error
        header("Location: index.php?status=error&jenis=izin");
        exit();
    }
}
?> 
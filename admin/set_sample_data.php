<?php
// Include konfigurasi
require_once '../config.php';

// Fungsi untuk menghasilkan pesan hasil
function showResult($message) {
    echo "<div style='margin: 20px; padding: 10px; background-color: #f8f9fa; border: 1px solid #ddd;'>";
    echo "<h3>Status Operasi</h3>";
    echo "<p>{$message}</p>";
    echo "<a href='attendance_list.php'>Kembali ke Daftar Absensi</a>";
    echo "</div>";
}

// Cek koneksi database
if (!$koneksi) {
    showResult("Error koneksi database: " . mysqli_connect_error());
    exit();
}

// Tambahkan data absensi sampel
$tanggal = date('Y-m-d'); // Tanggal hari ini
$id_karyawan = '001'; // Karyawan John Doe
$id_shift = 1; // Shift pagi

// Cek apakah data absensi sudah ada untuk tanggal ini
$query_check = "SELECT * FROM absensi WHERE tanggal = '$tanggal' AND id_karyawan = '$id_karyawan'";
$result_check = mysqli_query($koneksi, $query_check);

if (mysqli_num_rows($result_check) > 0) {
    $query = "UPDATE absensi SET 
              id_shift = $id_shift,
              check_in = '$tanggal 07:15:00',
              foto_check_in = 'uploads/absensi/001_20240601_check_in.jpg',
              check_out = '$tanggal 15:05:00',
              foto_check_out = 'uploads/absensi/001_20240601_check_out.jpg',
              status_check_in = 'tepat waktu',
              status_check_out = 'tepat waktu'
              WHERE tanggal = '$tanggal' AND id_karyawan = '$id_karyawan'";
    
    if (mysqli_query($koneksi, $query)) {
        showResult("Data absensi sampel berhasil diupdate!");
    } else {
        showResult("Error: " . mysqli_error($koneksi));
    }
} else {
    $query = "INSERT INTO absensi (id_karyawan, tanggal, id_shift, check_in, foto_check_in, check_out, foto_check_out, status_check_in, status_check_out) 
              VALUES ('$id_karyawan', '$tanggal', $id_shift, '$tanggal 07:15:00', 'uploads/absensi/001_20240601_check_in.jpg', 
              '$tanggal 15:05:00', 'uploads/absensi/001_20240601_check_out.jpg', 'tepat waktu', 'tepat waktu')";
    
    if (mysqli_query($koneksi, $query)) {
        showResult("Data absensi sampel berhasil ditambahkan!");
    } else {
        showResult("Error: " . mysqli_error($koneksi));
    }
}

// Tambahkan jadwal untuk tanggal ini jika belum ada
$query_check_jadwal = "SELECT * FROM jadwal WHERE tanggal = '$tanggal' AND id_karyawan = '$id_karyawan'";
$result_check_jadwal = mysqli_query($koneksi, $query_check_jadwal);

if (mysqli_num_rows($result_check_jadwal) == 0) {
    $query_jadwal = "INSERT INTO jadwal (id_karyawan, tanggal, id_shift, status) 
                    VALUES ('$id_karyawan', '$tanggal', $id_shift, 'masuk')";
    
    if (mysqli_query($koneksi, $query_jadwal)) {
        showResult("Data jadwal sampel berhasil ditambahkan!");
    } else {
        showResult("Error pada jadwal: " . mysqli_error($koneksi));
    }
}
?> 
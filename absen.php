<?php
// Include konfigurasi
require_once 'config.php';

// Cek apakah pengguna sudah login
requireLogin();

// Ambil data karyawan yang login
$id_karyawan = getKaryawanId();
$nama = getUserName();
$outlet = getOutlet();

// Aktifkan error reporting untuk debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Buat direktori log jika belum ada
if (!file_exists('logs')) {
    mkdir('logs', 0777, true);
}

// Simpan semua data request untuk debugging
file_put_contents('logs/request_data.txt', date('Y-m-d H:i:s') . "\n" . 
                 "POST: " . print_r($_POST, true) . "\n" .
                 "FILES: " . print_r($_FILES, true) . "\n\n", 
                 FILE_APPEND);

// Fungsi untuk menghitung jarak antara dua koordinat dalam meter (menggunakan formula Haversine)
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // Radius bumi dalam meter
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $earthRadius * $c;
    
    return $distance;
}

// Menangani proses absensi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Buat direktori upload jika belum ada
    $upload_dir = 'uploads/absensi/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Cek dan validasi data yang diperlukan
    if (!isset($_POST['tanggal']) || !isset($_POST['id_shift']) || !isset($_POST['jenis_absensi'])) {
        die("Data tidak lengkap");
    }
    
    $tanggal = sanitize($_POST['tanggal']);
    $id_shift = sanitize($_POST['id_shift']);
    $jenis_absensi = sanitize($_POST['jenis_absensi']); // check_in atau check_out
    
    // Ambil data lokasi dari form
    $latitude = isset($_POST['latitude']) ? sanitize($_POST['latitude']) : 0;
    $longitude = isset($_POST['longitude']) ? sanitize($_POST['longitude']) : 0;
    
    // Catat informasi lokasi saja tanpa validasi
    $location_info = "Latitude: $latitude, Longitude: $longitude";
    
    // Log info lokasi
    file_put_contents('logs/location_log.txt', date('Y-m-d H:i:s') . " - User: $id_karyawan - Info: $location_info\n", FILE_APPEND);
    
    // Simpan foto dari base64
    $foto = '';
    if (isset($_POST['foto_base64']) && !empty($_POST['foto_base64'])) {
        // Decode base64
        $base64_string = $_POST['foto_base64'];
        $data = explode(',', $base64_string);
        
        // Pastikan format base64 valid
        if (count($data) > 1) {
            $image_data = base64_decode($data[1]);
            
            // Buat nama file unik
            $file_name = $id_karyawan . '_' . $tanggal . '_' . $jenis_absensi . '_' . time() . '.jpg';
            $upload_path = $upload_dir . $file_name;
            
            // Simpan file
            if (file_put_contents($upload_path, $image_data)) {
                $foto = $upload_path;
            }
        }
    }
    
    // Tentukan waktu saat ini
    $waktu_sekarang = date('Y-m-d H:i:s');
    
    // Ambil informasi shift
    $query_shift = "SELECT * FROM shift WHERE id = '$id_shift'";
    $result_shift = mysqli_query($koneksi, $query_shift);
    
    if (!$result_shift || mysqli_num_rows($result_shift) == 0) {
        die("Shift tidak ditemukan");
    }
    
    $shift = mysqli_fetch_assoc($result_shift);
    
    // Format waktu shift
    $jam_mulai = date('H:i:s', strtotime($shift['jam_mulai']));
    $jam_selesai = date('H:i:s', strtotime($shift['jam_selesai']));
    
    // Hitung status absensi (tepat waktu, terlambat, lebih awal)
    $waktu_sekarang_format = date('H:i:s');
    
    if ($jenis_absensi === 'check_in') {
        // Toleransi 5 menit
        $batas_terlambat = date('H:i:s', strtotime($jam_mulai . ' + 5 minutes'));
        $status_absensi = ($waktu_sekarang_format <= $batas_terlambat) ? 'tepat waktu' : 'terlambat';
    } else { // check_out
        $status_absensi = ($waktu_sekarang_format >= $jam_selesai) ? 'tepat waktu' : 'lebih awal';
    }
    
    // Cek apakah sudah ada data absensi untuk tanggal ini
    $query_cek = "SELECT * FROM absensi WHERE id_karyawan = '$id_karyawan' AND tanggal = '$tanggal'";
    $result_cek = mysqli_query($koneksi, $query_cek);
    
    // Cek jika ada error pada query
    if (!$result_cek) {
        file_put_contents('logs/db_error.txt', date('Y-m-d H:i:s') . " - Error query cek: " . mysqli_error($koneksi) . "\n", FILE_APPEND);
        die("Error database: " . mysqli_error($koneksi));
    }
    
    // Proses sesuai dengan jenis absensi
    if (mysqli_num_rows($result_cek) > 0) {
        // Update data yang sudah ada
        if ($jenis_absensi === 'check_in') {
            $query = "UPDATE absensi SET 
                check_in = '$waktu_sekarang',
                foto_check_in = '$foto',
                status_check_in = '$status_absensi',
                latitude_in = '$latitude',
                longitude_in = '$longitude',
                location_info_in = '$location_info'
                WHERE id_karyawan = '$id_karyawan' AND tanggal = '$tanggal'";
        } else {
            $query = "UPDATE absensi SET 
                check_out = '$waktu_sekarang',
                foto_check_out = '$foto',
                status_check_out = '$status_absensi',
                latitude_out = '$latitude',
                longitude_out = '$longitude',
                location_info_out = '$location_info'
                WHERE id_karyawan = '$id_karyawan' AND tanggal = '$tanggal'";
        }
    } else {
        // Insert data baru
        if ($jenis_absensi === 'check_in') {
            $query = "INSERT INTO absensi 
                (id_karyawan, tanggal, id_shift, check_in, foto_check_in, status_check_in, 
                latitude_in, longitude_in, location_info_in)
                VALUES 
                ('$id_karyawan', '$tanggal', '$id_shift', '$waktu_sekarang', '$foto', '$status_absensi', 
                '$latitude', '$longitude', '$location_info')";
        } else {
            $query = "INSERT INTO absensi 
                (id_karyawan, tanggal, id_shift, check_out, foto_check_out, status_check_out, 
                latitude_out, longitude_out, location_info_out)
                VALUES 
                ('$id_karyawan', '$tanggal', '$id_shift', '$waktu_sekarang', '$foto', '$status_absensi', 
                '$latitude', '$longitude', '$location_info')";
        }
    }
    
    // Log query untuk debug
    file_put_contents('logs/query_log.txt', date('Y-m-d H:i:s') . " - Query: " . $query . "\n", FILE_APPEND);
    
    // Eksekusi query
    $result = mysqli_query($koneksi, $query);
    
    // Cek hasil query
    if ($result) {
        file_put_contents('logs/success_log.txt', date('Y-m-d H:i:s') . " - Berhasil: " . $jenis_absensi . "\n", FILE_APPEND);
        header("Location: index.php?status=success&jenis=$jenis_absensi");
        exit();
    } else {
        file_put_contents('logs/error_log.txt', date('Y-m-d H:i:s') . " - Error: " . mysqli_error($koneksi) . "\n", FILE_APPEND);
        header("Location: index.php?status=error&jenis=$jenis_absensi&message=" . urlencode("Error database: " . mysqli_error($koneksi)));
        exit();
    }
}
?> 
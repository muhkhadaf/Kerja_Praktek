<?php
// Ensure no output is sent before session_start
ob_start();

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Path dasar
define('BASE_PATH', __DIR__);

// URL dasar (sesuaikan dengan environment)
define('BASE_URL', 'http://localhost/wakacao');

// Default timezone
date_default_timezone_set('Asia/Jakarta');

// Load database connection
require_once 'database.php';

// Fungsi untuk memeriksa apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk redirect ke halaman login jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Fungsi untuk mengamankan input
function sanitize($input) {
    global $koneksi;
    return mysqli_real_escape_string($koneksi, trim($input));
}

// Fungsi untuk mendapatkan nama user dari session
function getUserName() {
    return $_SESSION['user_name'] ?? 'Pengguna';
}

// Fungsi untuk mendapatkan ID karyawan dari session
function getKaryawanId() {
    return $_SESSION['id_karyawan'] ?? '';
}

// Fungsi untuk mendapatkan outlet dari session
function getOutlet() {
    return $_SESSION['outlet'] ?? '-';
}

// Fungsi untuk mendapatkan role dari session
function getUserRole() {
    return $_SESSION['user_role'] ?? '';
}

// Fungsi untuk memformat tanggal ke format Indonesia
function formatTanggal($tanggal) {
    $bulan = [
        '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $tgl = date('j', strtotime($tanggal));
    $bln = (int)date('n', strtotime($tanggal));
    $thn = date('Y', strtotime($tanggal));
    
    return $tgl . ' ' . $bulan[$bln] . ' ' . $thn;
}

// Fungsi untuk memformat waktu 
function formatWaktu($waktu) {
    return date('H:i', strtotime($waktu));
}

// Fungsi untuk memastikan user adalah admin
function requireAdmin() {
    if (!isLoggedIn() || getUserRole() !== 'admin') {
        header("Location: login.php");
        exit;
    }
}
?> 
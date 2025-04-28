<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Wakacao - Setup Database</title>
  <link rel="stylesheet" href="vendors/feather/feather.css">
  <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="css/vertical-layout-light/style.css">
  <link rel="shortcut icon" href="images/favicon.png" />
</head>
<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth px-0">
        <div class="row w-100 mx-0">
          <div class="col-lg-6 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
              <div class="brand-logo text-center">
                <img src="images/logo.svg" alt="logo">
              </div>

<?php
// Konfigurasi koneksi database
$servername = "localhost";
$username = "root";
$password = "";

// Koneksi ke MySQL tanpa memilih database
$conn = mysqli_connect($servername, $username, $password);

// Cek koneksi
if (!$conn) {
    die('<div class="alert alert-danger">Koneksi gagal: ' . mysqli_connect_error() . '</div>');
}

echo "<h2 class='mb-4'>Setup Database Wakacao</h2>";
echo "<div class='setup-results'>";

// Cek apakah database wakacao_db sudah ada
$check_db = mysqli_query($conn, "SHOW DATABASES LIKE 'wakacao_db'");
if (mysqli_num_rows($check_db) > 0) {
    // Hapus database jika sudah ada
    $sql = "DROP DATABASE wakacao_db";
    if (mysqli_query($conn, $sql)) {
        echo "<div class='alert alert-info'>Database lama dihapus...</div>";
    } else {
        echo "<div class='alert alert-danger'>Error menghapus database: " . mysqli_error($conn) . "</div>";
    }
}

// Buat database baru
$sql = "CREATE DATABASE wakacao_db";
if (mysqli_query($conn, $sql)) {
    echo "<div class='alert alert-success'>Database berhasil dibuat...</div>";
} else {
    echo "<div class='alert alert-danger'>Error membuat database: " . mysqli_error($conn) . "</div>";
    exit;
}

// Pilih database yang baru dibuat
mysqli_select_db($conn, "wakacao_db");

// Buat tabel users
$sql = "CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_karyawan VARCHAR(10) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    outlet VARCHAR(50) NOT NULL,
    role ENUM('admin', 'karyawan', 'supervisor') NOT NULL DEFAULT 'karyawan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "<div class='alert alert-success'>Tabel users berhasil dibuat...</div>";
} else {
    echo "<div class='alert alert-danger'>Error membuat tabel users: " . mysqli_error($conn) . "</div>";
}

// Buat tabel shift
$sql = "CREATE TABLE shift (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_shift VARCHAR(20) NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL
)";

if (mysqli_query($conn, $sql)) {
    echo "<div class='alert alert-success'>Tabel shift berhasil dibuat...</div>";
} else {
    echo "<div class='alert alert-danger'>Error membuat tabel shift: " . mysqli_error($conn) . "</div>";
}

// Buat tabel jadwal
$sql = "CREATE TABLE jadwal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_karyawan VARCHAR(10) NOT NULL,
    tanggal DATE NOT NULL,
    id_shift INT,
    status ENUM('masuk', 'libur', 'izin', 'sakit', 'cuti') DEFAULT 'masuk',
    FOREIGN KEY (id_karyawan) REFERENCES users(id_karyawan),
    FOREIGN KEY (id_shift) REFERENCES shift(id)
)";

if (mysqli_query($conn, $sql)) {
    echo "<div class='alert alert-success'>Tabel jadwal berhasil dibuat...</div>";
} else {
    echo "<div class='alert alert-danger'>Error membuat tabel jadwal: " . mysqli_error($conn) . "</div>";
}

// Buat tabel absensi
$sql = "CREATE TABLE absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_karyawan VARCHAR(10) NOT NULL,
    tanggal DATE NOT NULL,
    id_shift INT,
    check_in DATETIME,
    foto_check_in VARCHAR(255),
    check_out DATETIME,
    foto_check_out VARCHAR(255),
    status_check_in ENUM('tepat waktu', 'terlambat', 'tidak absen') DEFAULT 'tidak absen',
    status_check_out ENUM('tepat waktu', 'lebih awal', 'tidak absen') DEFAULT 'tidak absen',
    FOREIGN KEY (id_karyawan) REFERENCES users(id_karyawan),
    FOREIGN KEY (id_shift) REFERENCES shift(id)
)";

if (mysqli_query($conn, $sql)) {
    echo "<div class='alert alert-success'>Tabel absensi berhasil dibuat...</div>";
} else {
    echo "<div class='alert alert-danger'>Error membuat tabel absensi: " . mysqli_error($conn) . "</div>";
}

// Buat tabel izin
$sql = "CREATE TABLE izin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_karyawan VARCHAR(10) NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    jenis_izin ENUM('sakit', 'izin', 'cuti', 'lainnya') NOT NULL,
    keterangan TEXT,
    bukti_file VARCHAR(255),
    solusi_pengganti ENUM('shift', 'libur', 'cuti', 'gaji') NOT NULL,
    status ENUM('pending', 'disetujui', 'ditolak') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_karyawan) REFERENCES users(id_karyawan)
)";

if (mysqli_query($conn, $sql)) {
    echo "<div class='alert alert-success'>Tabel izin berhasil dibuat...</div>";
} else {
    echo "<div class='alert alert-danger'>Error membuat tabel izin: " . mysqli_error($conn) . "</div>";
}

// Masukkan data contoh untuk shift
$sql = "INSERT INTO shift (nama_shift, jam_mulai, jam_selesai) VALUES
    ('Pagi', '07:00:00', '15:00:00'),
    ('Siang', '15:00:00', '23:00:00'),
    ('Malam', '23:00:00', '07:00:00')";

if (mysqli_query($conn, $sql)) {
    echo "<div class='alert alert-success'>Data shift berhasil ditambahkan...</div>";
} else {
    echo "<div class='alert alert-danger'>Error menambahkan data shift: " . mysqli_error($conn) . "</div>";
}

// Masukkan data contoh untuk users
// Password: 'password'
$password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
$sql = "INSERT INTO users (id_karyawan, nama, email, password, outlet, role) VALUES
    ('001', 'John Doe', 'john@example.com', '$password_hash', 'Bintaro', 'karyawan'),
    ('002', 'Jane Smith', 'jane@example.com', '$password_hash', 'Bintaro', 'karyawan'),
    ('003', 'Michael Johnson', 'michael@example.com', '$password_hash', 'Bintaro', 'karyawan'),
    ('004', 'Admin User', 'admin@example.com', '$password_hash', 'Pusat', 'admin')";

if (mysqli_query($conn, $sql)) {
    echo "<div class='alert alert-success'>Data users berhasil ditambahkan...</div>";
} else {
    echo "<div class='alert alert-danger'>Error menambahkan data users: " . mysqli_error($conn) . "</div>";
}

// Data tanggal hari ini dan 2 hari ke depan
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$day_after = date('Y-m-d', strtotime('+2 day'));

// Masukkan data contoh untuk jadwal dengan tanggal terbaru
$sql = "INSERT INTO jadwal (id_karyawan, tanggal, id_shift, status) VALUES
    ('001', '$today', 1, 'masuk'),
    ('001', '$tomorrow', 2, 'masuk'),
    ('001', '$day_after', 3, 'masuk'),
    ('002', '$today', 2, 'masuk'),
    ('002', '$tomorrow', 3, 'masuk'),
    ('002', '$day_after', 1, 'masuk'),
    ('003', '$today', 3, 'masuk'),
    ('003', '$tomorrow', 1, 'masuk'),
    ('003', '$day_after', 2, 'masuk')";

if (mysqli_query($conn, $sql)) {
    echo "<div class='alert alert-success'>Data jadwal berhasil ditambahkan...</div>";
} else {
    echo "<div class='alert alert-danger'>Error menambahkan data jadwal: " . mysqli_error($conn) . "</div>";
}

// Buat direktori untuk uploads
if (!file_exists('uploads/absensi')) {
    mkdir('uploads/absensi', 0777, true);
    echo "<div class='alert alert-success'>Direktori uploads/absensi berhasil dibuat...</div>";
}

if (!file_exists('uploads/izin')) {
    mkdir('uploads/izin', 0777, true);
    echo "<div class='alert alert-success'>Direktori uploads/izin berhasil dibuat...</div>";
}

// Masukkan data contoh untuk izin
$sql = "INSERT INTO izin (id_karyawan, tanggal_mulai, tanggal_selesai, jenis_izin, keterangan, solusi_pengganti, status, created_at) VALUES
    ('001', DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 8 DAY), 'sakit', 'Sakit flu dan demam', 'gaji', 'disetujui', DATE_SUB(NOW(), INTERVAL 12 DAY)),
    ('001', DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 20 DAY), 'izin', 'Urusan keluarga', 'shift', 'disetujui', DATE_SUB(NOW(), INTERVAL 22 DAY)),
    ('001', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'cuti', 'Liburan keluarga', 'cuti', 'pending', DATE_SUB(NOW(), INTERVAL 2 DAY))";

if (mysqli_query($conn, $sql)) {
    echo "<div class='alert alert-success'>Data izin berhasil ditambahkan...</div>";
} else {
    echo "<div class='alert alert-danger'>Error menambahkan data izin: " . mysqli_error($conn) . "</div>";
}

echo "</div>"; // Tutup div setup-results

// Tutup koneksi
mysqli_close($conn);
?>

              <div class="mt-4">
                <h3>Informasi Login:</h3>
                <div class="card p-3 mb-3">
                  <h5>Akun Karyawan</h5>
                  <p>Email: john@example.com<br>Password: password</p>
                </div>
                <div class="card p-3 mb-3">
                  <h5>Akun Admin</h5>
                  <p>Email: admin@example.com<br>Password: password</p>
                </div>
                <div class="text-center mt-4">
                  <a href="login.php" class="btn btn-primary btn-lg">Pergi ke Halaman Login</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="vendors/js/vendor.bundle.base.js"></script>
  <script src="js/off-canvas.js"></script>
  <script src="js/hoverable-collapse.js"></script>
  <script src="js/template.js"></script>
</body>
</html> 
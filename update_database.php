<?php
// Include koneksi database
require_once 'database.php';

// Fungsi untuk menjalankan query dan menangani error
function executeQuery($query, $description) {
    global $koneksi;
    
    echo "<p>Menjalankan: $description...</p>";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<p style='color:green'>Berhasil: $description</p>";
        return true;
    } else {
        echo "<p style='color:red'>Gagal: " . mysqli_error($koneksi) . "</p>";
        return false;
    }
}

// Mulai HTML output
echo "<html><head><title>Update Database</title></head><body>";
echo "<h1>Update Struktur Database</h1>";

// Cek apakah kolom latitude_in sudah ada
$checkColumn = mysqli_query($koneksi, "SHOW COLUMNS FROM absensi LIKE 'latitude_in'");
if (mysqli_num_rows($checkColumn) == 0) {
    // Tambahkan kolom untuk lokasi
    executeQuery(
        "ALTER TABLE absensi 
        ADD COLUMN latitude_in DECIMAL(10,7) DEFAULT NULL,
        ADD COLUMN longitude_in DECIMAL(10,7) DEFAULT NULL, 
        ADD COLUMN latitude_out DECIMAL(10,7) DEFAULT NULL,
        ADD COLUMN longitude_out DECIMAL(10,7) DEFAULT NULL",
        "Menambahkan kolom lokasi"
    );
} else {
    echo "<p>Kolom lokasi sudah ada pada tabel absensi</p>";
}

// Cek apakah kolom status lokasi sudah ada
$checkColumn = mysqli_query($koneksi, "SHOW COLUMNS FROM absensi LIKE 'location_status_in'");
if (mysqli_num_rows($checkColumn) == 0) {
    // Tambahkan kolom untuk status lokasi
    executeQuery(
        "ALTER TABLE absensi 
        ADD COLUMN location_status_in VARCHAR(10) DEFAULT NULL,
        ADD COLUMN location_info_in VARCHAR(255) DEFAULT NULL,
        ADD COLUMN location_status_out VARCHAR(10) DEFAULT NULL,
        ADD COLUMN location_info_out VARCHAR(255) DEFAULT NULL",
        "Menambahkan kolom status lokasi"
    );
} else {
    echo "<p>Kolom status lokasi sudah ada pada tabel absensi</p>";
}

// Update tipe data kolom foto jika diperlukan
executeQuery(
    "ALTER TABLE absensi
    MODIFY COLUMN foto_check_in VARCHAR(255) DEFAULT NULL,
    MODIFY COLUMN foto_check_out VARCHAR(255) DEFAULT NULL",
    "Mengupdate kolom foto"
);

// Tampilkan struktur tabel setelah diupdate
echo "<h2>Struktur Tabel Absensi Setelah Update</h2>";
$result = mysqli_query($koneksi, "DESCRIBE absensi");

if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color:red'>Gagal mendapatkan struktur tabel: " . mysqli_error($koneksi) . "</p>";
}

echo "<p><a href='index.php'>Kembali ke Halaman Utama</a></p>";
echo "</body></html>";
?> 
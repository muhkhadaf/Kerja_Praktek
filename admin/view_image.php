<?php
// File untuk menampilkan gambar secara langsung tanpa CSS atau layout lain
// Berguna untuk debugging gambar yang tidak muncul

// Path gambar yang ingin ditampilkan
$imagePath = isset($_GET['path']) ? $_GET['path'] : '';

// Cek apakah path valid dan tidak kosong
if (empty($imagePath)) {
    echo "Error: Path gambar tidak valid atau kosong";
    exit;
}

// Path lengkap ke file gambar
$fullPath = '../' . $imagePath;

// Cek apakah file exist
if (!file_exists($fullPath)) {
    echo "Error: File tidak ditemukan di path " . htmlspecialchars($fullPath);
    echo "<br><br>Periksa apakah:";
    echo "<ol>";
    echo "<li>Path file benar</li>";
    echo "<li>Direktori memiliki permission yang benar</li>";
    echo "<li>File sudah diupload dengan benar</li>";
    echo "</ol>";
    exit;
}

// Cek ukuran file
$fileSize = filesize($fullPath);
echo "File ditemukan! Ukuran: " . $fileSize . " bytes<br>";

// Tampilkan gambar
echo "<img src='../{$imagePath}' alt='Foto Absensi' style='max-width: 100%;'>";
?> 
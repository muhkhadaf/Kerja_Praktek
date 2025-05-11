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

// Output HTML dengan CSS
echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Gambar</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .header {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 24px;
        }
        .info {
            background-color: #e1f5fe;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .image-container {
            text-align: center;
            padding: 10px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            border: 1px solid #eaeaea;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Viewer Gambar</h1>
        </div>
        <div class="info">
            File ditemukan! Ukuran: ' . $fileSize . ' bytes
        </div>
        <div class="image-container">
            <img src="../' . $imagePath . '" alt="Foto Absensi">
        </div>
        <div class="footer">
            <p>Klik kanan pada gambar untuk menyimpan</p>
        </div>
    </div>
</body>
</html>';
?> 
<?php
require_once 'config.php';

// Query to get table structure
$query = "SHOW COLUMNS FROM laporan";
$result = mysqli_query($koneksi, $query);

if ($result) {
    echo "Columns in 'laporan' table:<br>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
} else {
    echo "Error: " . mysqli_error($koneksi);
}

// Close connection
mysqli_close($koneksi);
?>

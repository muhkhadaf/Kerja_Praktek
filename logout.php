<?php
// Include konfigurasi
require_once 'config.php';

// Menghapus semua data session
$_SESSION = array();

// Menghapus session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Menghancurkan session
session_destroy();

// Redirect ke halaman login
header("Location: login.php");
exit;
?> 
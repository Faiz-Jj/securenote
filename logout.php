<?php
session_start();
session_unset(); // Hapus semua data session
session_destroy(); // Hancurkan session

// Optional: hapus cookie sesi
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect ke halaman login
header("Location: login.php");
exit;


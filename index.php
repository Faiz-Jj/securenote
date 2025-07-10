<?php
session_start();
if (isset($_SESSION['user_logged_in'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureNote - Beranda</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <header class="hero">
        <div class="container">
            <h1 class="hero-title">
                <span class="material-icons">lock</span> SecureNote
            </h1>
            <p class="hero-subtitle">Sistem Pencatatan Terenkripsi antara Dosen dan Mahasiswa</p>
        </div>
    </header>

    <main class="container">
        <div class="card-grid">
            <div class="card">
                <div class="card-header">
                    <span class="material-icons">school</span>
                    <h3>Akses Mahasiswa</h3>
                </div>
                <div class="card-body">
                    <a href="lihat.php" class="card-link">
                        <span class="material-icons">search</span>
                        <span>Cari & Lihat Catatan Mahasiswa</span>
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <span class="material-icons">admin_panel_settings</span>
                    <h3>Akses Admin</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                        <div class="login-status">
                            <span class="material-icons">verified_user</span>
                            <span>Login sebagai Admin</span>
                            <a href="admin_logout.php" class="logout-link">(Logout)</a>
                        </div>
                        <a href="admin.php" class="card-link">
                            <span class="material-icons">note_add</span>
                            <span>Tambah Catatan</span>
                        </a>
                        <a href="admin_list.php" class="card-link">
                            <span class="material-icons">list_alt</span>
                            <span>Kelola Catatan</span>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">
                            <span class="material-icons">login</span>
                            <span>Login Admin</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

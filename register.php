<?php
require_once 'config/db.php';

session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($password) < 8) {
        $error = "Password harus minimal 8 karakter";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hashedPassword]);
            $success = "Admin berhasil dibuat!";
        } catch (PDOException $e) {
            $error = "Username sudah digunakan atau terjadi kesalahan";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Akun Admin - SecureNote</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="header-title">
                    <span class="material-icons">person_add</span>
                    <h1>Buat Akun Admin</h1>
                </div>
                <nav class="user-nav">
                    <div class="user-info">
                        <span class="material-icons">account_circle</span>
                        <?= htmlspecialchars($_SESSION['username']) ?>
                    </div>
                    <a href="dashboard.php" class="btn btn-outline btn-sm">
                        <span class="material-icons">arrow_back</span> Kembali
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="card">
            <?php if ($success): ?>
                <div class="alert success">
                    <span class="material-icons">check_circle</span>
                    <?= $success ?>
                    <a href="dashboard.php" class="btn btn-sm" style="margin-left: auto;">Ke Dashboard</a>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert error">
                    <span class="material-icons">error</span>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="post" class="auth-form">
                <div class="form-group">
                    <label for="username">
                        <span class="material-icons">person</span>
                        Username
                    </label>
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="Masukkan username admin" required>
                </div>

                <div class="form-group">
                    <label for="password">
                        <span class="material-icons">lock</span>
                        Password
                    </label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Minimal 8 karakter" required>
                    <small class="text-muted">Password harus minimal 8 karakter</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <span class="material-icons">person_add</span> Buat Akun Admin
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>

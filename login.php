<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SecureNote</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <span class="material-icons" style="font-size: 3rem; color: #4361ee;">admin_panel_settings</span>
                <h1>Admin Login</h1>
                <p>Masukkan kredensial Anda untuk mengakses panel admin</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert error">
                    <span class="material-icons">error</span>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="auth-form">
                <div class="form-group">
                    <label for="username">
                        <span class="material-icons">person</span>
                        Username
                    </label>
                    <input type="text" id="username" name="username" required placeholder="Masukkan username">
                </div>

                <div class="form-group">
                    <label for="password">
                        <span class="material-icons">lock</span>
                        Password
                    </label>
                    <input type="password" id="password" name="password" required placeholder="Masukkan password">
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <span class="material-icons">login</span>
                    Masuk
                </button>
            </form>

            <div class="auth-footer">
                <a href="index.php" class="text-link">
                    <span class="material-icons">arrow_back</span>
                    Kembali ke Halaman Utama
                </a>
            </div>
        </div>
    </div>
</body>
</html>

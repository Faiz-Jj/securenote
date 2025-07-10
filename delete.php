<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: dashboard.php?error=ID catatan tidak valid");
    exit;
}

// Verify note exists and belongs to user before deleting
$stmt = $pdo->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$note = $stmt->fetch();

if (!$note) {
    header("Location: dashboard.php?error=Catatan tidak ditemukan atau tidak memiliki akses");
    exit;
}

// Perform deletion
$stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);

if ($stmt->rowCount()) {
    $success = "Catatan berhasil dihapus";
} else {
    $error = "Gagal menghapus catatan";
}

header("Location: dashboard.php?" . ($success ? "success=$success" : "error=$error"));
exit;
?>

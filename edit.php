<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: dashboard.php?error=ID catatan tidak ditemukan");
    exit;
}

// Ambil catatan
$stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$note = $stmt->fetch();

if (!$note) {
    header("Location: dashboard.php?error=Catatan tidak ditemukan");
    exit;
}

// Ambil daftar mahasiswa
$mahasiswaList = $pdo->query("SELECT id, nama, kelas FROM mahasiswa ORDER BY nama")->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mahasiswa_id = $_POST['mahasiswa_id'];
    $title = $_POST['title'];
    $message = $_POST['message'];

    // Ambil password mahasiswa
    $stmt = $pdo->prepare("SELECT password FROM mahasiswa WHERE id = ?");
    $stmt->execute([$mahasiswa_id]);
    $mahasiswa = $stmt->fetch();

    if ($mahasiswa) {
        $key = $mahasiswa['password'];
        $ciphertext = encryptXOR($message, $key);

        $stmt = $pdo->prepare("UPDATE notes SET mahasiswa_id = ?, title = ?, ciphertext = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$mahasiswa_id, $title, $ciphertext, $id, $_SESSION['user_id']])) {
            $success = "Catatan berhasil diperbarui";
        } else {
            $error = "Gagal memperbarui catatan";
        }
    } else {
        $error = "Mahasiswa tidak ditemukan";
    }
}

// Decrypt content for editing
$decryptedContent = '';
try {
    $stmt = $pdo->prepare("SELECT password FROM mahasiswa WHERE id = ?");
    $stmt->execute([$note['mahasiswa_id']]);
    $key = $stmt->fetchColumn();
    $decryptedContent = decryptXOR($note['ciphertext'], $key);
} catch (Exception $e) {
    $error = "Gagal mendekripsi konten: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Catatan - SecureNote</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 42px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="header-title">
                    <span class="material-icons">edit</span>
                    <h1>Edit Catatan</h1>
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
                    <a href="dashboard.php" class="btn btn-sm" style="margin-left: auto;">Lihat Dashboard</a>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert error">
                    <span class="material-icons">error</span>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="post" class="form-note">
                <div class="form-group">
                    <label for="mahasiswa_id">
                        <span class="material-icons">person</span>
                        Mahasiswa
                    </label>
                    <select name="mahasiswa_id" id="mahasiswa_id" class="js-example-basic-single" required>
                        <?php foreach ($mahasiswaList as $mhs): ?>
                            <option value="<?= $mhs['id'] ?>" <?= ($mhs['id'] == $note['mahasiswa_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mhs['nama']) ?> - <?= htmlspecialchars($mhs['kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title">
                        <span class="material-icons">title</span>
                        Judul Catatan
                    </label>
                    <input type="text" id="title" name="title" class="form-control" 
                           value="<?= htmlspecialchars($note['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="message">
                        <span class="material-icons">notes</span>
                        Isi Catatan
                    </label>
                    <textarea id="message" name="message" class="form-control" rows="8" required><?= 
                        htmlspecialchars($decryptedContent) 
                    ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <span class="material-icons">save</span> Simpan Perubahan
                    </button>
                    <a href="dashboard.php" class="btn btn-outline">
                        <span class="material-icons">cancel</span> Batal
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.js-example-basic-single').select2({
            placeholder: "Pilih mahasiswa...",
            width: '100%',
            dropdownParent: $('.form-note')
        });
    });
    </script>
</body>
</html>

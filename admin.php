<?php
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/db.php';
require_once 'includes/functions.php';


$stmt = $pdo->query("SELECT * FROM mahasiswa ORDER BY nama ASC");
$mahasiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mahasiswa_id = $_POST['mahasiswa_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Ambil password mahasiswa sebagai key enkripsi
    $stmt = $pdo->prepare("SELECT password FROM mahasiswa WHERE id = ?");
    $stmt->execute([$mahasiswa_id]);
    $row = $stmt->fetch();

    if ($row) {
        $key = $row['password'];
        $ciphertext = encryptXOR($content, $key);

	$user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("INSERT INTO notes (user_id, mahasiswa_id, title, ciphertext) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $mahasiswa_id, $title, $ciphertext])) {
            $success = "Catatan berhasil ditambahkan.";
        } else {
            $error = "Gagal menambahkan catatan.";
        }
    } else {
        $error = "Mahasiswa tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Catatan - SecureNote</title>
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
                    <span class="material-icons">note_add</span>
                    <h1>Tambah Catatan</h1>
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
                </div>
            <?php elseif ($error): ?>
                <div class="alert error">
                    <span class="material-icons">error</span>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="post" class="form-note">
                <div class="form-group">
                    <label for="mahasiswa">
                        <span class="material-icons">person</span>
                        Pilih Mahasiswa
                    </label>
                    <select name="mahasiswa_id" id="mahasiswa" class="js-example-basic-single" required>
                        <option value="">-- Pilih Mahasiswa --</option>
                        <?php foreach ($mahasiswa as $mhs): ?>
                            <option value="<?= $mhs['id'] ?>" data-pass="<?= htmlspecialchars($mhs['password']) ?>">
                                <?= htmlspecialchars($mhs['nama']) ?> (<?= htmlspecialchars($mhs['kelas']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title">
                        <span class="material-icons">title</span>
                        Judul Catatan
                    </label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="content">
                        <span class="material-icons">notes</span>
                        Isi Catatan
                    </label>
                    <textarea id="content" name="content" class="form-control" rows="5" required></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <span class="material-icons">save</span> Simpan Catatan
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.js-example-basic-single').select2({
            placeholder: "Cari mahasiswa...",
            width: '100%',
            dropdownParent: $('.form-note')
        });
    });
    </script>
</body>
</html>

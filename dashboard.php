<?php
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/db.php';

$search_nama = $_GET['search_nama'] ?? '';
$search_kelas = $_GET['search_kelas'] ?? '';

$sql = "SELECT notes.id, notes.title, notes.ciphertext, mahasiswa.nama, mahasiswa.kelas
        FROM notes
        JOIN mahasiswa ON notes.mahasiswa_id = mahasiswa.id";
$params = [];
$conditions = [];

if (!empty($search_nama)) {
    $conditions[] = "mahasiswa.nama LIKE ?";
    $params[] = '%' . $search_nama . '%';
}
if (!empty($search_kelas)) {
    $conditions[] = "mahasiswa.kelas LIKE ?";
    $params[] = '%' . $search_kelas . '%';
}
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY notes.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SecureNote</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="header-title">
                    <span class="material-icons">dashboard</span>
                    <h1>SecureNote</h1>
                </div>
                <nav class="user-nav">
                    <div class="user-info">
                        <span class="material-icons">account_circle</span>
                        <?= htmlspecialchars($_SESSION['username']) ?>
                    </div>
                    <a href="admin.php" class="btn btn-primary btn-sm">
                        <span class="material-icons">add</span> Tambah Catatan
                    </a>
                    <a href="register.php" class="btn btn-outline btn-sm">
                        <span class="material-icons">person_add</span> Tambah User
                    </a>
                    <a href="logout.php" class="btn btn-danger btn-sm">
                        <span class="material-icons">logout</span> Logout
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="card">
            <h3 class="card-title">
                <span class="material-icons">search</span>
                Cari Catatan Mahasiswa
            </h3>
            <form method="get" class="search-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="search_nama">Nama Mahasiswa</label>
                        <input type="text" id="search_nama" name="search_nama" 
                               class="form-control"
                               placeholder="Contoh: Faiz"
                               value="<?= htmlspecialchars($search_nama) ?>">
                    </div>
                    <div class="form-group">
                        <label for="search_kelas">Kelas</label>
                        <input type="text" id="search_kelas" name="search_kelas" 
                               class="form-control"
                               placeholder="Contoh: TMJ CCIT 4B"
                               value="<?= htmlspecialchars($search_kelas) ?>">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="material-icons">search</span> Cari
                        </button>
                        <a href="dashboard.php" class="btn btn-outline">
                            <span class="material-icons">refresh</span> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="material-icons">list_alt</span>
                    Daftar Catatan Mahasiswa
                </h3>
                <span class="badge badge-primary">Total: <?= count($notes) ?></span>
            </div>

            <?php if ($notes): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Nama Mahasiswa</th>
                                <th>Kelas</th>
                                <th>Isi Terenkripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notes as $note): ?>
                                <tr>
                                    <td><?= htmlspecialchars($note['title']) ?></td>
                                    <td><?= htmlspecialchars($note['nama']) ?></td>
                                    <td><?= htmlspecialchars($note['kelas']) ?></td>
                                    <td class="text-muted ciphertext" title="<?= htmlspecialchars($note['ciphertext']) ?>">
                                        <?= strlen($note['ciphertext']) > 30 ? 
                                            substr(htmlspecialchars($note['ciphertext']), 0, 30) . '...' : 
                                            htmlspecialchars($note['ciphertext']) ?>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="edit.php?id=<?= $note['id'] ?>" class="btn-icon" title="Edit">
                                                <span class="material-icons">edit</span>
                                            </a>
                                            <a href="delete.php?id=<?= $note['id'] ?>" 
                                               class="btn-icon danger" 
                                               title="Hapus"
                                               onclick="return confirm('Yakin ingin menghapus catatan ini?')">
                                                <span class="material-icons">delete</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <span class="material-icons empty-state-icon">info</span>
                    <p>Tidak ada catatan ditemukan</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Tooltip for truncated ciphertext
        document.querySelectorAll('.ciphertext').forEach(el => {
            if (el.textContent.endsWith('...')) {
                el.style.cursor = 'help';
            }
        });
    </script>
</body>
</html>

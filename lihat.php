<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$nama = $_GET['nama'] ?? '';
$kelas = $_GET['kelas'] ?? '';
$catatan = [];
$decrypted_notes = []; // Store decrypted notes by ID

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['note_id'])) {
    $note_id = $_POST['note_id'];
    $input_password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT notes.id, notes.title, notes.ciphertext, mahasiswa.password, 
                                  mahasiswa.nama, mahasiswa.kelas
                           FROM notes
                           JOIN mahasiswa ON notes.mahasiswa_id = mahasiswa.id
                           WHERE notes.id = ?");
    $stmt->execute([$note_id]);
    $note = $stmt->fetch();

    if ($note) {
        if ($input_password === $note['password']) {
            $plaintext = decryptXOR($note['ciphertext'], $input_password);
            $decrypted_notes[$note_id] = [
                'title' => $note['title'],
                'content' => nl2br(htmlspecialchars($plaintext)),
		//'content' => nl2br($plaintext),
                'nama' => $note['nama'],
                'kelas' => $note['kelas']
            ];
        } else {
            $decrypted_notes[$note_id] = [
                'error' => 'Kode dekripsi salah'
            ];
        }
    }
}

// Search functionality
if (!empty($nama) || !empty($kelas)) {
    $query = "SELECT notes.id, notes.title, mahasiswa.nama, mahasiswa.kelas
              FROM notes
              JOIN mahasiswa ON notes.mahasiswa_id = mahasiswa.id";
    $params = [];
    $conditions = [];

    if (!empty($nama)) {
        $conditions[] = "mahasiswa.nama LIKE ?";
        $params[] = "%$nama%";
    }
    if (!empty($kelas)) {
        $conditions[] = "mahasiswa.kelas LIKE ?";
        $params[] = "%$kelas%";
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $query .= " ORDER BY notes.id DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $catatan = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Catatan - SecureNote</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="header-title">
                    <span class="material-icons">search</span>
                    <h2>Lihat Catatan Mahasiswa</h2>
                </div>
                <a href="index.php" class="btn btn-outline btn-sm">
                    <span class="material-icons">arrow_back</span> Kembali
                </a>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="card">
            <form method="get" class="search-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nama">
                            <span class="material-icons">person</span>
                            Nama Mahasiswa
                        </label>
                        <input type="text" id="nama" name="nama"
                               class="form-control"
                               value="<?= htmlspecialchars($nama) ?>"
                               placeholder="Cari berdasarkan nama">
                    </div>
                    <div class="form-group">
                        <label for="kelas">
                            <span class="material-icons">groups</span>
                            Kelas
                        </label>
                        <input type="text" id="kelas" name="kelas"
                               class="form-control"
                               value="<?= htmlspecialchars($kelas) ?>"
                               placeholder="Cari berdasarkan kelas">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="material-icons">search</span> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($catatan): ?>
            <div class="card">
                <h3 class="card-title">
                    <span class="material-icons">list_alt</span>
                    Catatan Ditemukan
                </h3>
                <div class="notes-list">
                    <?php foreach ($catatan as $cat): ?>
                        <div class="note-container">
                            <form method="post" class="note-form">
                                <div class="card-body">
                                    <div class="note-header">
                                        <h4><?= htmlspecialchars($cat['title']) ?></h4>
                                        <p class="text-muted">
                                            <?= htmlspecialchars($cat['nama']) ?> (<?= htmlspecialchars($cat['kelas']) ?>)
                                        </p>
                                    </div>
                                    <input type="hidden" name="note_id" value="<?= $cat['id'] ?>">
                                    <div class="form-group">
                                        <label for="password-<?= $cat['id'] ?>">
                                            <span class="material-icons">key</span>
                                            Kode Dekripsi
                                        </label>
                                        <input type="password" id="password-<?= $cat['id'] ?>"
                                               name="password" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <span class="material-icons">visibility</span> Lihat Isi
                                    </button>
                                </div>

                                <?php if (isset($decrypted_notes[$cat['id']])): ?>
                                    <div class="note-result">
                                        <?php if (isset($decrypted_notes[$cat['id']]['error'])): ?>
                                            <div class="note-error">
                                                <span class="material-icons">error</span>
                                                <?= $decrypted_notes[$cat['id']]['error'] ?>
                                            </div>
                                        <?php else: ?>
                                            <h5><?= htmlspecialchars($decrypted_notes[$cat['id']]['title']) ?></h5>
                                            <div class="note-content">
                                                <?= $decrypted_notes[$cat['id']]['content'] ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif ($nama || $kelas): ?>
            <div class="card">
                <div class="empty-state">
                    <span class="material-icons">info</span>
                    <p>Tidak ada catatan ditemukan untuk kriteria ini</p>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>

<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$nama = $_GET['nama'] ?? '';
$kelas = $_GET['kelas'] ?? '';
$catatan = [];
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note_id = $_POST['note_id'];
    $input_password = $_POST['password'];

    // Ambil catatan dan info mahasiswa
    $stmt = $pdo->prepare("SELECT notes.title, notes.ciphertext, mahasiswa.password 
                           FROM notes 
                           JOIN mahasiswa ON notes.mahasiswa_id = mahasiswa.id 
                           WHERE notes.id = ?");
    $stmt->execute([$note_id]);
    $note = $stmt->fetch();

    if ($note) {
        if ($input_password === $note['password']) {
            $plaintext = decryptXOR(base64_decode($note['ciphertext']), $input_password);
            $pesan = "✅ Catatan berhasil didekripsi: <br><strong>{$note['title']}</strong><br>" . nl2br(htmlspecialchars($plaintext));
        } else {
            $pesan = "❌ Kode dekripsi salah!";
        }
    }
}

if (!empty($nama) && !empty($kelas)) {
    $stmt = $pdo->prepare("SELECT notes.id, notes.title 
                           FROM notes 
                           JOIN mahasiswa ON notes.mahasiswa_id = mahasiswa.id 
                           WHERE mahasiswa.nama = ? AND mahasiswa.kelas = ?");
    $stmt->execute([$nama, $kelas]);
    $catatan = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<h2>Lihat Catatan Mahasiswa</h2>
<form method="get">
    <label>Nama:</label>
    <input type="text" name="nama" value="<?= htmlspecialchars($nama) ?>" required>
    <label>Kelas:</label>
    <input type="text" name="kelas" value="<?= htmlspecialchars($kelas) ?>" required>
    <button type="submit">Cari</button>
</form>

<?php if ($catatan): ?>
    <h3>Catatan ditemukan:</h3>
    <ul>
        <?php foreach ($catatan as $cat): ?>
            <li>
                <form method="post">
                    <input type="hidden" name="note_id" value="<?= $cat['id'] ?>">
                    <strong><?= htmlspecialchars($cat['title']) ?></strong><br>
                    <label>Kode Dekripsi:</label>
                    <input type="password" name="password" required>
                    <button type="submit">Lihat Isi</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php elseif ($nama || $kelas): ?>
    <p>❌ Tidak ada catatan untuk nama dan kelas tersebut.</p>
<?php endif; ?>

<?php if (!empty($pesan)): ?>
    <div style="margin-top: 20px;"><?= $pesan ?></div>
<?php endif; ?>

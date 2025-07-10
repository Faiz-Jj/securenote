<?php require_once 'config/db.php'; ?>

<h2>Daftar Semua Catatan Mahasiswa</h2>
<a href="admin.php">+ Tambah Catatan Baru</a><br><br>
<a href="dashboard.php">← Kembali ke Beranda</a>

<?php
$stmt = $pdo->query("SELECT * FROM notes ORDER BY created_at DESC");
$notes = $stmt->fetchAll();

if (count($notes) === 0) {
    echo "<p>Belum ada catatan disimpan.</p>";
} else {
    foreach ($notes as $note) {
        ?>
        <div style="border:1px solid #ccc; padding:10px; margin:10px;">
            <strong><?= htmlspecialchars($note['title']) ?></strong><br>
            Nama: <?= htmlspecialchars($note['nama']) ?><br>
            Kelas: <?= htmlspecialchars($note['kelas']) ?><br>
            Tanggal: <?= $note['created_at'] ?><br>
            Encrypted: <textarea rows="2" cols="50" readonly><?= $note['ciphertext'] ?></textarea><br>
            <a href="edit.php?id=<?= $note['id'] ?>">✏️ Edit</a> |
            <a href="delete.php?id=<?= $note['id'] ?>" onclick="return confirm('Yakin ingin menghapus catatan ini?')">🗑️ Hapus</a>
        </div>
        <?php
    }
}
?>

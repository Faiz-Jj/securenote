<?php require_once 'config/db.php'; ?>

<h2>Cari Catatan Mahasiswa</h2>
<a href="dashboard.php">← Kembali ke Beranda</a>

<form method="get">
    Nama: <input type="text" name="nama" required>
    Kelas: <input type="text" name="kelas" required>
    <button type="submit">Cari</button>
</form>

<?php
if (isset($_GET['nama']) && isset($_GET['kelas'])) {
    $nama = $_GET['nama'];
    $kelas = $_GET['kelas'];

    $stmt = $pdo->prepare("SELECT * FROM notes WHERE nama = ? AND kelas = ? ORDER BY created_at DESC");
    $stmt->execute([$nama, $kelas]);
    $notes = $stmt->fetchAll();

    if (count($notes) === 0) {
        echo "<p>Tidak ada catatan ditemukan untuk $nama di kelas $kelas.</p>";
    } else {
        echo "<h3>Hasil Pencarian:</h3>";
        foreach ($notes as $note) {
            ?>
            <div style="border:1px solid #ccc; padding:10px; margin:10px;">
                <strong><?= htmlspecialchars($note['title']) ?></strong><br>
                Tanggal: <?= $note['created_at'] ?><br>
                Encrypted: <textarea rows="2" cols="50" readonly><?= $note['ciphertext'] ?></textarea><br>
                Passphrase: <input type="password" id="pass<?= $note['id'] ?>">
                <button onclick="decryptXOR('<?= $note['id'] ?>', '<?= htmlspecialchars($note['ciphertext'], ENT_QUOTES) ?>')">Lihat</button><br>
                <strong>Plaintext:</strong>
                <p id="plain<?= $note['id'] ?>"></p>
            </div>
            <?php
        }
    }
}
?>

<script>
function decryptXOR(id, base64) {
    const key = document.getElementById('pass' + id).value;
    const encoded = atob(base64);

    let result = '';
    for (let i = 0; i < encoded.length; i++) {
        const eChar = encoded.charCodeAt(i);
        const kChar = key.charCodeAt(i % key.length);
        const xor = eChar ^ kChar;
        result += String.fromCharCode(xor);
    }

    document.getElementById('plain' + id).innerText = result;
}
</script>

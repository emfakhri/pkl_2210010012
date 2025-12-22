<?php include '../config/database.php'; ?>

<h3>Penentuan Kelulusan</h3>

<table border="1">
    <tr>
        <th>Nama</th>
        <th>Status Verifikasi</th>
        <th>Kelulusan</th>
        <th>Aksi</th>
    </tr>

    <?php
    $q = mysqli_query($koneksi, "SELECT * FROM siswa WHERE status_verifikasi='diterima'");
    while ($s = mysqli_fetch_array($q)) {
    ?>
        <tr>
            <td><?= $s['nama'] ?></td>
            <td><?= $s['status_verifikasi'] ?></td>
            <td><?= $s['status_lulus'] ?></td>
            <td>
                <a href="kelulusan_proses.php?id=<?= $s['id'] ?>&lulus=lulus">Lulus</a> |
                <a href="kelulusan_proses.php?id=<?= $s['id'] ?>&lulus=tidak lulus">Tidak</a>
            </td>
        </tr>
    <?php } ?>
</table>
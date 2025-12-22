<?php include '../config/database.php'; ?>

<h3>Verifikasi Berkas</h3>

<table border="1" width="100%">
    <tr>
        <th>Nama</th>
        <th>NISN</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>

    <?php
    $data = mysqli_query($koneksi, "
    SELECT * FROM siswa
");

    while ($s = mysqli_fetch_array($data)) {
    ?>
        <tr>
            <td><?= $s['nama'] ?></td>
            <td><?= $s['nisn'] ?></td>
            <td><?= $s['status_verifikasi'] ?></td>
            <td>
                <a href="verifikasi_proses.php?id=<?= $s['id'] ?>&status=diterima">Terima</a> |
                <a href="verifikasi_proses.php?id=<?= $s['id'] ?>&status=ditolak">Tolak</a>
            </td>
        </tr>
    <?php } ?>
</table>
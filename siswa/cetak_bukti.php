<?php include '../config/database.php'; ?>

<?php
$id = $_GET['id'];
$data = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id='$id'");
$s = mysqli_fetch_array($data);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Bukti Pendaftaran</title>
</head>

<body onload="window.print()">

    <h2 align="center">BUKTI PENDAFTARAN PPDB</h2>
    <hr>

    <table>
        <tr>
            <td>Nama</td>
            <td>: <?= $s['nama'] ?></td>
        </tr>
        <tr>
            <td>NISN</td>
            <td>: <?= $s['nisn'] ?></td>
        </tr>
        <tr>
            <td>Asal Sekolah</td>
            <td>: <?= $s['asal_sekolah'] ?></td>
        </tr>
        <tr>
            <td>Status</td>
            <td>: <?= $s['status_verifikasi'] ?></td>
        </tr>
    </table>

    <p>Harap simpan bukti ini sebagai tanda pendaftaran resmi.</p>

</body>

</html>
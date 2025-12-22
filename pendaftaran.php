<?php include 'config/database.php'; ?>

<!DOCTYPE html>
<html>

<head>
    <title>Form Pendaftaran PPDB</title>
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">
        <h3>Form Pendaftaran Siswa</h3>

        <form method="post">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required>
            </div>

            <div class="form-group">
                <label>NISN</label>
                <input type="text" name="nisn" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Asal Sekolah</label>
                <input type="text" name="asal_sekolah" class="form-control">
            </div>

            <button type="submit" name="simpan" class="btn btn-success">
                Simpan Pendaftaran
            </button>
        </form>

        <?php
        if (isset($_POST['simpan'])) {
            $nama = $_POST['nama'];
            $nisn = $_POST['nisn'];
            $asal = $_POST['asal_sekolah'];

            mysqli_query(
                $koneksi,
                "INSERT INTO siswa VALUES (NULL,'$nama','$nisn','$asal','menunggu')"
            );

            echo "<div class='alert alert-success mt-3'>
                Pendaftaran berhasil!
              </div>";
        }
        ?>
    </div>

</body>

</html>
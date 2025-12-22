<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'siswa') {
    header("Location: auth/login.php");
    exit;
}

include 'config/db.php';
include 'includes/header.php';

$user_id = $_SESSION['user_id'];

// cek apakah siswa sudah mengisi form
$cek = mysqli_query($conn, "SELECT * FROM students WHERE user_id='$user_id'");
$data = mysqli_fetch_assoc($cek);

// simpan data
if (isset($_POST['simpan'])) {
    $nisn = $_POST['nisn'];
    $nama = $_POST['nama'];
    $jk = $_POST['jk'];
    $tempat = $_POST['tempat_lahir'];
    $tgl = $_POST['tanggal_lahir'];
    $asal = $_POST['asal_sekolah'];
    $alamat = $_POST['alamat'];
    $ortu = $_POST['nama_ortu'];

    if ($data) {
        // update
        mysqli_query($conn, "UPDATE students SET
            nisn='$nisn',
            nama='$nama',
            jk='$jk',
            tempat_lahir='$tempat',
            tanggal_lahir='$tgl',
            asal_sekolah='$asal',
            alamat='$alamat',
            nama_ortu='$ortu'
            WHERE user_id='$user_id'
        ");
    } else {
        // insert
        mysqli_query($conn, "INSERT INTO students
        (user_id, nisn, nama, jk, tempat_lahir, tanggal_lahir, asal_sekolah, alamat, nama_ortu)
        VALUES
        ('$user_id','$nisn','$nama','$jk','$tempat','$tgl','$asal','$alamat','$ortu')
        ");
    }

    echo "<script>alert('Data berhasil disimpan');location='dashboard_siswa.php';</script>";
}
?>

<div class="container mt-4">
    <h3 class="mb-3">Formulir Pendaftaran Siswa</h3>

    <form method="POST">
        <div class="mb-3">
            <label>NISN</label>
            <input type="text" name="nisn" class="form-control" required value="<?= $data['nisn'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" required value="<?= $data['nama'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label>Jenis Kelamin</label>
            <select name="jk" class="form-control" required>
                <option value="">-- Pilih --</option>
                <option value="L" <?= ($data['jk'] ?? '') == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                <option value="P" <?= ($data['jk'] ?? '') == 'P' ? 'selected' : '' ?>>Perempuan</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Tempat Lahir</label>
            <input type="text" name="tempat_lahir" class="form-control" value="<?= $data['tempat_lahir'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label>Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" class="form-control" value="<?= $data['tanggal_lahir'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label>Asal Sekolah</label>
            <input type="text" name="asal_sekolah" class="form-control" value="<?= $data['asal_sekolah'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control"><?= $data['alamat'] ?? '' ?></textarea>
        </div>

        <div class="mb-3">
            <label>Nama Orang Tua / Wali</label>
            <input type="text" name="nama_ortu" class="form-control" value="<?= $data['nama_ortu'] ?? '' ?>">
        </div>

        <button name="simpan" class="btn btn-primary">Simpan Data</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
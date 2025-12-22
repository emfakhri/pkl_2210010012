<?php
session_start();

/* ===============================
   PROTEKSI HALAMAN SISWA
================================ */
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'siswa') {
    header("Location: auth/login.php");
    exit;
}

include 'config/db.php';
include 'includes/header.php';

$user_id = $_SESSION['user_id'];

/* ===============================
   AMBIL ID STUDENT
================================ */
$q = mysqli_query($conn, "SELECT id FROM students WHERE user_id='$user_id'");
$s = mysqli_fetch_assoc($q);

if (!$s) {
    echo "<div class='container mt-4'>
            <div class='alert alert-warning'>
                Silakan isi formulir pendaftaran terlebih dahulu.
            </div>
          </div>";
    include 'includes/footer.php';
    exit;
}

$student_id = $s['id'];

/* ===============================
   PROSES UPLOAD
================================ */
if (isset($_POST['upload'])) {

    $base = "uploads/berkas/";

    // ambil nama file
    $kk    = time() . "_" . $_FILES['kk']['name'];
    $akta  = time() . "_" . $_FILES['akta']['name'];
    $rapor = time() . "_" . $_FILES['rapor']['name'];
    $foto  = time() . "_" . $_FILES['foto']['name'];

    // upload ke folder masing-masing
    move_uploaded_file($_FILES['kk']['tmp_name'],    $base . "kk/" . $kk);
    move_uploaded_file($_FILES['akta']['tmp_name'],  $base . "akta/" . $akta);
    move_uploaded_file($_FILES['rapor']['tmp_name'], $base . "rapor/" . $rapor);
    move_uploaded_file($_FILES['foto']['tmp_name'],  $base . "foto/" . $foto);

    // cek apakah sudah pernah upload
    $cek = mysqli_query($conn, "SELECT * FROM berkas WHERE student_id='$student_id'");

    if (mysqli_num_rows($cek) > 0) {
        // update
        mysqli_query($conn, "UPDATE berkas SET
            kk='$kk',
            akta='$akta',
            rapor='$rapor',
            foto='$foto'
            WHERE student_id='$student_id'
        ");
    } else {
        // insert
        mysqli_query($conn, "INSERT INTO berkas
        (student_id, kk, akta, rapor, foto)
        VALUES
        ('$student_id','$kk','$akta','$rapor','$foto')
        ");
    }

    echo "<script>
        alert('Berkas berhasil diupload');
        window.location='dashboard_siswa.php';
    </script>";
}
?>

<div class="container mt-4">
    <h3 class="mb-3">Upload Berkas Pendaftaran</h3>

    <div class="alert alert-info">
        Upload berkas dalam format JPG / PNG / PDF
    </div>

    <form method="POST" enctype="multipart/form-data">

        <div class="mb-3">
            <label>Kartu Keluarga (KK)</label>
            <input type="file" name="kk" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Akta Kelahiran</label>
            <input type="file" name="akta" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Rapor</label>
            <input type="file" name="rapor" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Pas Foto</label>
            <input type="file" name="foto" class="form-control" required>
        </div>

        <button name="upload" class="btn btn-primary">
            Upload Berkas
        </button>

        <a href="dashboard_siswa.php" class="btn btn-secondary">
            Kembali
        </a>

    </form>
</div>

<?php include 'includes/footer.php'; ?>
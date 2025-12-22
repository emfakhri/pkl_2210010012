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
   AMBIL STATUS PENDAFTARAN
================================ */
$query = mysqli_query($conn, "SELECT status FROM students WHERE user_id='$user_id'");
$data  = mysqli_fetch_assoc($query);
?>

<div class="container mt-4">

    <!-- ================= HEADER DASHBOARD ================= -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Dashboard Siswa</h3>

        <!-- TOMBOL LOGOUT -->
        <a href="logout.php" class="btn btn-danger btn-sm">
            Logout
        </a>
    </div>

    <!-- ================= STATUS PENDAFTARAN ================= -->
    <div class="card mb-4">
        <div class="card-body">
            <h5>Status Pendaftaran</h5>

            <?php if ($data): ?>
                <div class="alert alert-info mt-2 mb-0">
                    <strong><?= htmlspecialchars($data['status']); ?></strong>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mt-2 mb-0">
                    Anda belum mengisi formulir pendaftaran.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ================= MENU SISWA ================= -->
    <div class="card">
        <div class="card-body">
            <h5 class="mb-3">Menu Siswa</h5>

            <div class="d-flex flex-wrap gap-2">

                <!-- ISI / EDIT FORMULIR -->
                <a href="pendaftaran.php" class="btn btn-primary">
                    📝 Isi / Edit Formulir
                </a>

                <!-- UPLOAD BERKAS -->
                <a href="upload_berkas.php" class="btn btn-secondary">
                    📂 Upload Berkas
                </a>

                <!-- CETAK BUKTI -->
                <a href="cetak_bukti.php" target="_blank" class="btn btn-success">
                    📄 Cetak Bukti Pendaftaran
                </a>

            </div>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
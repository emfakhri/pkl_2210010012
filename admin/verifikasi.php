<?php
session_start();

// proteksi admin
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

include '../config/db.php';
include '../includes/header.php';

// ambil id siswa
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id = intval($_GET['id']);

// ambil data siswa
$query = mysqli_query($conn, "SELECT * FROM students WHERE id='$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Data tidak ditemukan.</div></div>";
    include '../includes/footer.php';
    exit;
}

// simpan verifikasi
if (isset($_POST['verifikasi'])) {
    $status = $_POST['status'];

    mysqli_query($conn, "UPDATE students SET status='$status' WHERE id='$id'");

    echo "<script>
        alert('Status pendaftaran berhasil diperbarui');
        window.location='dashboard.php';
    </script>";
}
?>

<div class="container mt-4">

    <h3 class="mb-4">Verifikasi Calon Siswa</h3>

    <div class="card mb-4">
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th width="30%">NISN</th>
                    <td><?= htmlspecialchars($data['nisn']); ?></td>
                </tr>
                <tr>
                    <th>Nama Lengkap</th>
                    <td><?= htmlspecialchars($data['nama']); ?></td>
                </tr>
                <tr>
                    <th>Jenis Kelamin</th>
                    <td><?= $data['jk'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                </tr>
                <tr>
                    <th>Tempat, Tanggal Lahir</th>
                    <td><?= htmlspecialchars($data['tempat_lahir']); ?>, <?= $data['tanggal_lahir']; ?></td>
                </tr>
                <tr>
                    <th>Asal Sekolah</th>
                    <td><?= htmlspecialchars($data['asal_sekolah']); ?></td>
                </tr>
                <tr>
                    <th>Nama Orang Tua</th>
                    <td><?= htmlspecialchars($data['nama_ortu']); ?></td>
                </tr>
                <tr>
                    <th>Alamat</th>
                    <td><?= nl2br(htmlspecialchars($data['alamat'])); ?></td>
                </tr>
                <tr>
                    <th>Status Saat Ini</th>
                    <td>
                        <strong><?= $data['status']; ?></strong>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- FORM VERIFIKASI -->
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Ubah Status Pendaftaran</label>
                    <select name="status" class="form-control" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="Diterima">Diterima</option>
                        <option value="Ditolak">Ditolak</option>
                    </select>
                </div>

                <button name="verifikasi" class="btn btn-success">
                    Simpan Verifikasi
                </button>
                <a href="dashboard.php" class="btn btn-secondary">
                    Kembali
                </a>
            </form>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
<?php
session_start();

// proteksi halaman admin
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

include '../config/db.php';
include '../includes/header.php';

// ambil semua data pendaftar
$query = mysqli_query($conn, "
    SELECT students.*, users.username 
    FROM students 
    JOIN users ON students.user_id = users.id
    ORDER BY students.id DESC
");
?>

<div class="container mt-4">

    <!-- HEADER DASHBOARD -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Dashboard Admin PPDB</h3>

        <!-- LOGOUT -->
        <a href="../logout.php" class="btn btn-danger btn-sm">
            Logout
        </a>
    </div>

    <!-- INFORMASI -->
    <div class="alert alert-info">
        Berikut adalah daftar calon siswa yang telah mendaftar.
    </div>

    <!-- TABEL PENDAFTAR -->
    <div class="card">
        <div class="card-body table-responsive">

            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>NISN</th>
                        <th>Nama</th>
                        <th>Jenis Kelamin</th>
                        <th>Asal Sekolah</th>
                        <th>Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($query) > 0): ?>
                        <?php $no = 1;
                        while ($d = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($d['nisn']); ?></td>
                                <td><?= htmlspecialchars($d['nama']); ?></td>
                                <td class="text-center">
                                    <?= $d['jk'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                                </td>
                                <td><?= htmlspecialchars($d['asal_sekolah']); ?></td>
                                <td class="text-center">
                                    <?php
                                    if ($d['status'] == 'Menunggu Verifikasi') {
                                        echo '<span class="badge bg-warning text-dark">Menunggu</span>';
                                    } elseif ($d['status'] == 'Diterima') {
                                        echo '<span class="badge bg-success">Diterima</span>';
                                    } else {
                                        echo '<span class="badge bg-danger">Ditolak</span>';
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <a href="verifikasi.php?id=<?= $d['id']; ?>" class="btn btn-primary btn-sm">
                                        Verifikasi
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                Belum ada data pendaftar.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
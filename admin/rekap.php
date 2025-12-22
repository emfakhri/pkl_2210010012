<?php
session_start();
if ($_SESSION['role'] != 'admin') exit;

include '../config/db.php';
include '../includes/header.php';

$q = mysqli_query($conn, "SELECT status, COUNT(*) total FROM students GROUP BY status");
?>

<div class="container mt-4">
    <h3>Rekap Pendaftaran</h3>
    <table class="table table-bordered">
        <tr>
            <th>Status</th>
            <th>Jumlah</th>
        </tr>
        <?php while ($d = mysqli_fetch_assoc($q)): ?>
            <tr>
                <td><?= $d['status']; ?></td>
                <td><?= $d['total']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <a href="laporan_pdf.php" class="btn btn-danger">Cetak Laporan PDF</a>
</div>

<?php include '../includes/footer.php'; ?>
<?php
session_start();

if (!isset($_SESSION['login']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../config/database.php';

function e($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

function getRows($conn, $sql) {
    $q = mysqli_query($conn, $sql);
    $rows = [];

    if ($q) {
        while ($r = mysqli_fetch_assoc($q)) {
            $rows[] = $r;
        }
    }

    return $rows;
}

/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/
$status   = $_GET['status'] ?? '';
$jk       = $_GET['jk'] ?? '';
$sekolah  = $_GET['sekolah'] ?? '';
$domisili = $_GET['domisili'] ?? '';

$conditions = [];

if (in_array($status, ['Menunggu Verifikasi', 'Diterima', 'Ditolak'], true)) {
    $conditions[] = "status='" . mysqli_real_escape_string($conn, $status) . "'";
} else {
    $status = '';
}

if ($jk !== '') {
    $conditions[] = "jk='" . mysqli_real_escape_string($conn, $jk) . "'";
}

if ($sekolah !== '') {
    $conditions[] = "nama_sekolah_asal='" . mysqli_real_escape_string($conn, $sekolah) . "'";
}

if ($domisili !== '') {
    $conditions[] = "domisili_murid='" . mysqli_real_escape_string($conn, $domisili) . "'";
}

$where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

/*
|--------------------------------------------------------------------------
| PILIH REPORT
|--------------------------------------------------------------------------
*/
$report = isset($_GET['report']) ? (int) $_GET['report'] : 0;

if ($report < 1 || $report > 5) {
    die('Report tidak ditemukan.');
}

$title = '';

/*
|--------------------------------------------------------------------------
| REPORT 1 - DATA SELURUH PENDAFTAR
|--------------------------------------------------------------------------
*/
if ($report === 1) {

    $title = 'Report 1 - Data Seluruh Pendaftar';

    $students = getRows($conn, "
        SELECT id, nama, nisn, nik, jk, nama_sekolah_asal,
               domisili_murid, status, created_at
        FROM students
        $where
        ORDER BY id DESC
    ");
}

/*
|--------------------------------------------------------------------------
| REPORT 2 - STATUS PENDAFTARAN
|--------------------------------------------------------------------------
*/
elseif ($report === 2) {

    $title = 'Report 2 - Status Pendaftaran';

    $rows = getRows($conn, "
        SELECT
            COALESCE(NULLIF(status, ''), 'Tidak diisi') AS label,
            COUNT(*) AS jumlah
        FROM students
        $where
        GROUP BY status
        ORDER BY jumlah DESC, label ASC
    ");
}

/*
|--------------------------------------------------------------------------
| REPORT 3 - JENIS KELAMIN
|--------------------------------------------------------------------------
*/
elseif ($report === 3) {

    $title = 'Report 3 - Jenis Kelamin';

    $rows = getRows($conn, "
        SELECT
            COALESCE(NULLIF(jk, ''), 'Tidak diisi') AS label,
            COUNT(*) AS jumlah
        FROM students
        $where
        GROUP BY jk
        ORDER BY jumlah DESC, label ASC
    ");
}

/*
|--------------------------------------------------------------------------
| REPORT 4 - ASAL SEKOLAH
|--------------------------------------------------------------------------
*/
elseif ($report === 4) {

    $title = 'Report 4 - Asal Sekolah';

    $rows = getRows($conn, "
        SELECT
            COALESCE(NULLIF(nama_sekolah_asal, ''), 'Tidak diisi') AS label,
            COUNT(*) AS jumlah
        FROM students
        $where
        GROUP BY nama_sekolah_asal
        ORDER BY jumlah DESC, label ASC
    ");
}

/*
|--------------------------------------------------------------------------
| REPORT 5 - DOMISILI
|--------------------------------------------------------------------------
*/
elseif ($report === 5) {

    $title = 'Report 5 - Domisili';

    $rows = getRows($conn, "
        SELECT
            COALESCE(NULLIF(domisili_murid, ''), 'Tidak diisi') AS label,
            COUNT(*) AS jumlah
        FROM students
        $where
        GROUP BY domisili_murid
        ORDER BY jumlah DESC, label ASC
    ");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= e($title) ?></title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            color: #000;
            margin: 0;
            padding: 30px;
            font-size: 12px;
        }

        .toolbar {
            margin-bottom: 25px;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            background: #075e35;
            color: #fff;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0 0 6px;
            font-size: 20px;
        }

        .header p {
            margin: 3px 0;
        }

        .filter-info {
            margin-bottom: 15px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }

        th {
            text-align: center;
            font-weight: bold;
        }

        .center {
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm;
            }

            body {
                padding: 0;
            }

            .toolbar {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="toolbar">
    <button class="btn" onclick="window.print()">
        Cetak / Simpan sebagai PDF
    </button>

    <a href="laporan.php" class="btn">
        Kembali
    </a>
</div>

<div class="header">
    <h2><?= e($title) ?></h2>
    <p>MTs Ulumul Qur'an Al Madani</p>
    <p>Laporan Penerimaan Murid Baru</p>
</div>

<?php if ($status || $jk || $sekolah || $domisili): ?>
<div class="filter-info">
    <strong>Filter:</strong>

    <?php if ($status): ?>
        Status: <?= e($status) ?> |
    <?php endif; ?>

    <?php if ($jk): ?>
        Jenis Kelamin: <?= e($jk) ?> |
    <?php endif; ?>

    <?php if ($sekolah): ?>
        Asal Sekolah: <?= e($sekolah) ?> |
    <?php endif; ?>

    <?php if ($domisili): ?>
        Domisili: <?= e($domisili) ?>
    <?php endif; ?>
</div>
<?php endif; ?>


<?php if ($report === 1): ?>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama</th>
                <th>NISN</th>
                <th>JK</th>
                <th>Asal Sekolah</th>
                <th>Domisili</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>
        </thead>

        <tbody>

        <?php if (empty($students)): ?>

            <tr>
                <td colspan="8" class="center">
                    Belum ada data.
                </td>
            </tr>

        <?php else: ?>

            <?php foreach ($students as $i => $s): ?>

                <tr>
                    <td class="center"><?= $i + 1 ?></td>
                    <td><?= e($s['nama']) ?></td>
                    <td><?= e($s['nisn']) ?></td>
                    <td><?= e($s['jk']) ?></td>
                    <td><?= e($s['nama_sekolah_asal']) ?></td>
                    <td><?= e($s['domisili_murid']) ?></td>
                    <td><?= e($s['status']) ?></td>
                    <td><?= e($s['created_at']) ?></td>
                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>
    </table>


<?php else: ?>

    <table>
        <thead>
            <tr>
                <th width="10%">No</th>
                <th>Kategori</th>
                <th width="20%">Jumlah</th>
            </tr>
        </thead>

        <tbody>

        <?php if (empty($rows)): ?>

            <tr>
                <td colspan="3" class="center">
                    Belum ada data.
                </td>
            </tr>

        <?php else: ?>

            <?php foreach ($rows as $i => $row): ?>

                <tr>
                    <td class="center"><?= $i + 1 ?></td>
                    <td><?= e($row['label']) ?></td>
                    <td class="center"><?= (int) $row['jumlah'] ?></td>
                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>
    </table>

<?php endif; ?>


<div class="footer">
    <p>
        Dicetak pada: <?= date('d-m-Y H:i') ?>
    </p>
</div>

<script>
window.onload = function () {
    window.print();
};
</script>

</body>
</html>
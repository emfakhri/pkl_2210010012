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

    $title = 'Laporan Data Seluruh Pendaftar';

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

    $title = 'Laporan Status Pendaftaran';

    $r2conditions = [];
    if ($status !== '') $r2conditions[] = "s.status='" . mysqli_real_escape_string($conn, $status) . "'";
    if ($jk !== '') $r2conditions[] = "s.jk='" . mysqli_real_escape_string($conn, $jk) . "'";
    if ($sekolah !== '') $r2conditions[] = "s.nama_sekolah_asal='" . mysqli_real_escape_string($conn, $sekolah) . "'";
    if ($domisili !== '') $r2conditions[] = "s.domisili_murid='" . mysqli_real_escape_string($conn, $domisili) . "'";
    $r2where = "WHERE u.role='siswa'" . ($r2conditions ? ' AND ' . implode(' AND ', $r2conditions) : '');
    $required = "s.nama IS NOT NULL AND s.nama<>'' AND s.nisn IS NOT NULL AND s.nisn<>'' AND s.nik IS NOT NULL AND s.nik<>'' AND s.nama_sekolah_asal IS NOT NULL AND s.nama_sekolah_asal<>'' AND s.tanggal_lahir IS NOT NULL AND s.jk IS NOT NULL AND s.jk<>'' AND s.no_kk IS NOT NULL AND s.no_kk<>'' AND s.nama_ayah IS NOT NULL AND s.nama_ayah<>'' AND s.nama_ibu IS NOT NULL AND s.nama_ibu<>'' AND s.domisili_murid IS NOT NULL AND s.domisili_murid<>''";
    $formRows = getRows($conn, "SELECT CASE WHEN s.id IS NULL THEN 'Belum Mengisi' WHEN $required THEN 'Lengkap' ELSE 'Belum Lengkap' END label, COUNT(*) jumlah FROM users u LEFT JOIN students s ON s.user_id=u.id $r2where GROUP BY label");
    $verificationRows = getRows($conn, "SELECT COALESCE(NULLIF(s.status,''),'Belum Verifikasi') label, COUNT(*) jumlah FROM users u LEFT JOIN students s ON s.user_id=u.id $r2where GROUP BY label");
    /*
     * Status Upload Berkas:
     * Jika tabel student_documents belum dibuat, semua siswa ditampilkan
     * sebagai "Belum Upload" agar laporan tetap berjalan tanpa fatal error.
     */
    $cekTabelDokumen = mysqli_query($conn, "SHOW TABLES LIKE 'student_documents'");

    if ($cekTabelDokumen && mysqli_num_rows($cekTabelDokumen) > 0) {
        $uploadRows = getRows($conn, "
            SELECT
                CASE
                    WHEN COUNT(d.id)=0 THEN 'Belum Upload'
                    WHEN COUNT(d.id)<6 THEN 'Belum Lengkap'
                    ELSE 'Lengkap'
                END AS label,
                COUNT(*) AS jumlah
            FROM users u
            LEFT JOIN students s ON s.user_id=u.id
            LEFT JOIN student_documents d ON d.student_id=s.id
            $r2where
            GROUP BY u.id
        ");

        $uploadSummary=['Belum Upload'=>0,'Belum Lengkap'=>0,'Lengkap'=>0];
        foreach($uploadRows as $r) {
            $uploadSummary[$r['label']] += (int)$r['jumlah'];
        }

        $uploadRows=[];
        foreach($uploadSummary as $label=>$jumlah) {
            $uploadRows[]=['label'=>$label,'jumlah'=>$jumlah];
        }
    } else {
        $jumlahSiswa = getRows($conn, "
            SELECT COUNT(*) AS jumlah
            FROM users u
            LEFT JOIN students s ON s.user_id=u.id
            $r2where
        ");

        $totalSiswa = (int)($jumlahSiswa[0]['jumlah'] ?? 0);

        $uploadRows = [
            ['label'=>'Belum Upload', 'jumlah'=>$totalSiswa],
            ['label'=>'Belum Lengkap', 'jumlah'=>0],
            ['label'=>'Lengkap', 'jumlah'=>0]
        ];
    }
}

/*
|--------------------------------------------------------------------------
| REPORT 3 - JENIS KELAMIN
|--------------------------------------------------------------------------
*/
elseif ($report === 3) {

    $title = 'Laporan Jenis Kelamin';

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
    $title = 'Laporan Data Asal Sekolah';
    $rows = getRows($conn, "
        SELECT nama_sekolah_asal, status_sekolah_asal, alamat_sekolah_asal
        FROM students
        $where
        ORDER BY nama_sekolah_asal ASC, id DESC
    ");
}

elseif ($report === 5) {

    $title = 'Laporan Data Tempat Tinggal Siswa';

    $rows = getRows($conn, "
        SELECT
            domisili_murid AS status_tempat_tinggal,

            CASE
                WHEN alamat_ibu_status = 'SAMA_DENGAN_AYAH'
                    OR alamat_ibu_status IS NULL
                    OR alamat_ibu_status = ''
                THEN jalan_ayah
                ELSE jalan_ibu
            END AS alamat,

            CASE
                WHEN alamat_ibu_status = 'SAMA_DENGAN_AYAH'
                    OR alamat_ibu_status IS NULL
                    OR alamat_ibu_status = ''
                THEN kelurahan_ayah
                ELSE kelurahan_ibu
            END AS kelurahan,

            CASE
                WHEN alamat_ibu_status = 'SAMA_DENGAN_AYAH'
                    OR alamat_ibu_status IS NULL
                    OR alamat_ibu_status = ''
                THEN kecamatan_ayah
                ELSE kecamatan_ibu
            END AS kecamatan,

            CASE
                WHEN alamat_ibu_status = 'SAMA_DENGAN_AYAH'
                    OR alamat_ibu_status IS NULL
                    OR alamat_ibu_status = ''
                THEN kabupaten_ayah
                ELSE kabupaten_ibu
            END AS kabupaten_kota

        FROM students

        $where

        ORDER BY id DESC
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
                size: <?= $report === 5 ? 'A4 landscape' : 'A4 portrait' ?>;
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


<?php elseif ($report === 2): ?>
    <?php foreach ([['A. Status Isi Formulir',$formRows],['B. Status Verifikasi',$verificationRows],['C. Status Upload Berkas',$uploadRows]] as $section): ?>
    <h3><?= e($section[0]) ?></h3>
    <table><thead><tr><th width="10%">No</th><th>Kategori</th><th width="20%">Jumlah</th></tr></thead><tbody>
    <?php foreach ($section[1] as $i=>$row): ?><tr><td class="center"><?= $i+1 ?></td><td><?= e($row['label']) ?></td><td class="center"><?= (int)$row['jumlah'] ?></td></tr><?php endforeach; ?>
    </tbody></table>
    <?php endforeach; ?>

<?php elseif ($report === 4): ?>
<table>
<thead><tr><th width="8%">No</th><th width="30%">Nama Sekolah Asal</th><th width="18%">Status Sekolah</th><th>Alamat Sekolah Asal</th></tr></thead>
<tbody>
<?php if (empty($rows)): ?><tr><td colspan="4" class="center">Belum ada data.</td></tr>
<?php else: foreach ($rows as $i => $row):
$statusSekolah=strtoupper(trim($row['status_sekolah_asal'] ?? ''));
$statusTampil=$statusSekolah==='NEGERI'?'Negeri':($statusSekolah==='SWASTA'?'Swasta':($statusSekolah?:'-')); ?>
<tr><td class="center"><?= $i+1 ?></td><td><?= e($row['nama_sekolah_asal']) ?: '-' ?></td><td class="center"><?= e($statusTampil) ?></td><td><?= e($row['alamat_sekolah_asal']) ?: '-' ?></td></tr>
<?php endforeach; endif; ?>
</tbody></table>

<?php elseif ($report === 5): ?>
<table>
<thead><tr><th width="6%">No</th><th width="17%">Status Tempat Tinggal</th><th width="24%">Alamat</th><th width="17%">Kelurahan</th><th width="18%">Kecamatan</th><th width="18%">Kabupaten / Kota</th></tr></thead>
<tbody>
<?php if (empty($rows)): ?><tr><td colspan="6" class="center">Belum ada data.</td></tr>
<?php else: foreach ($rows as $i => $row): ?>
<tr>
    <td class="center"><?= $i + 1 ?></td>

    <td>
        <?= e($row['status_tempat_tinggal'] ?? '-') ?>
    </td>

    <td>
        <?= e($row['alamat'] ?? '-') ?>
    </td>

    <td>
        <?= e($row['kelurahan'] ?? '-') ?>
    </td>

    <td>
        <?= e($row['kecamatan'] ?? '-') ?>
    </td>

    <td>
        <?= e($row['kabupaten_kota'] ?? '-') ?>
    </td>
</tr>
<?php endforeach; endif; ?>
</tbody></table>

<?php else: ?>
<table><thead><tr><th width="10%">No</th><th>Kategori</th><th width="20%">Jumlah</th></tr></thead><tbody>
<?php if (empty($rows)): ?><tr><td colspan="3" class="center">Belum ada data.</td></tr>
<?php else: foreach ($rows as $i => $row): ?><tr><td class="center"><?= $i+1 ?></td><td><?= e($row['label']) ?></td><td class="center"><?= (int)$row['jumlah'] ?></td></tr><?php endforeach; endif; ?>
</tbody></table>
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
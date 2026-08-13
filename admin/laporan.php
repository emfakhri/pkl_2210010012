<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../config/database.php';

function e($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

function countWhere($conn, $where = '') {
    $sql = "SELECT COUNT(*) AS total FROM students";
    if ($where !== '') $sql .= " WHERE " . $where;
    $q = mysqli_query($conn, $sql);
    return $q ? (int) mysqli_fetch_assoc($q)['total'] : 0;
}

function getRows($conn, $sql) {
    $q = mysqli_query($conn, $sql);
    $rows = [];
    if ($q) {
        while ($r = mysqli_fetch_assoc($q)) $rows[] = $r;
    }
    return $rows;
}

$status = $_GET['status'] ?? '';
$jk = $_GET['jk'] ?? '';
$sekolah = $_GET['sekolah'] ?? '';
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

$total = countWhere($conn, $where ? substr($where, 7) : '');
$menunggu = countWhere($conn, ($where ? substr($where, 7) . ' AND ' : '') . "(status='Menunggu Verifikasi')");
$diterima = countWhere($conn, ($where ? substr($where, 7) . ' AND ' : '') . "(status='Diterima')");
$ditolak = countWhere($conn, ($where ? substr($where, 7) . ' AND ' : '') . "(status='Ditolak')");

$students = getRows($conn, "
    SELECT id,nama,nisn,nik,jk,nama_sekolah_asal,domisili_murid,status,created_at
    FROM students $where ORDER BY id DESC
");

$asalSekolah = getRows($conn, "
    SELECT COALESCE(NULLIF(nama_sekolah_asal,''),'Tidak diisi') AS label, COUNT(*) AS jumlah
    FROM students $where GROUP BY nama_sekolah_asal ORDER BY jumlah DESC, label ASC
");

$jenisKelamin = getRows($conn, "
    SELECT COALESCE(NULLIF(jk,''),'Tidak diisi') AS label, COUNT(*) AS jumlah
    FROM students $where GROUP BY jk ORDER BY jumlah DESC, label ASC
");

$domisiliRows = getRows($conn, "
    SELECT COALESCE(NULLIF(domisili_murid,''),'Tidak diisi') AS label, COUNT(*) AS jumlah
    FROM students $where GROUP BY domisili_murid ORDER BY jumlah DESC, label ASC
");

$statusRows = getRows($conn, "
    SELECT COALESCE(NULLIF(status,''),'Tidak diisi') AS label, COUNT(*) AS jumlah
    FROM students $where GROUP BY status ORDER BY jumlah DESC, label ASC
");

$report4Rows = getRows($conn, "
    SELECT nama_sekolah_asal, status_sekolah_asal, alamat_sekolah_asal
    FROM students $where ORDER BY nama_sekolah_asal ASC
");
$report5Rows = getRows($conn, "
    SELECT tinggal_bersama,
           CASE WHEN alamat_ibu_status='SAMA_DENGAN_AYAH' OR alamat_ibu_status IS NULL OR alamat_ibu_status=''
                THEN jalan_ayah ELSE jalan_ibu END AS alamat,
           CASE WHEN alamat_ibu_status='SAMA_DENGAN_AYAH' OR alamat_ibu_status IS NULL OR alamat_ibu_status=''
                THEN kelurahan_ayah ELSE kelurahan_ibu END AS kelurahan,
           CASE WHEN alamat_ibu_status='SAMA_DENGAN_AYAH' OR alamat_ibu_status IS NULL OR alamat_ibu_status=''
                THEN kecamatan_ayah ELSE kecamatan_ibu END AS kecamatan,
           CASE WHEN alamat_ibu_status='SAMA_DENGAN_AYAH' OR alamat_ibu_status IS NULL OR alamat_ibu_status=''
                THEN kabupaten_ayah ELSE kabupaten_ibu END AS kabupaten_kota
    FROM students $where ORDER BY id DESC
");

$sekolahOptions = getRows($conn, "SELECT DISTINCT nama_sekolah_asal AS value FROM students WHERE nama_sekolah_asal IS NOT NULL AND nama_sekolah_asal<>'' ORDER BY nama_sekolah_asal");
$jkOptions = getRows($conn, "SELECT DISTINCT jk AS value FROM students WHERE jk IS NOT NULL AND jk<>'' ORDER BY jk");
$domisiliOptions = getRows($conn, "SELECT DISTINCT domisili_murid AS value FROM students WHERE domisili_murid IS NOT NULL AND domisili_murid<>'' ORDER BY domisili_murid");

function tableReport($title, $rows, $report, $query) {
    ob_start(); ?>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="section-title mb-0"><?= e($title) ?></h5>
                    <a href="laporan_pdf.php?report=<?= (int)$report ?>&<?= e($query) ?>"
                       target="_blank"
                       class="btn btn-sm btn-danger">
                        <i class="fas fa-print mr-1"></i> Cetak
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Kategori</th><th width="90">Jumlah</th></tr></thead>
                        <tbody>
                        <?php if (!$rows): ?>
                            <tr><td colspan="2" class="text-center text-muted">Belum ada data.</td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <tr><td><?= e($r['label']) ?></td><td class="font-weight-bold"><?= (int)$r['jumlah'] ?></td></tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php return ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Laporan PMBM</title>
<link rel="stylesheet" href="../assets/css/sb-admin-2.min.css">
<link rel="stylesheet" href="../assets/vendor/fontawesome-free/css/all.min.css">
<style>
:root{--gd:#075e35;--g:#0b7a45;--y:#f5c400}
body{background:#f5f8f6}.topbar{background:var(--gd);padding:12px 0}
.brand{color:#fff!important;text-decoration:none;font-weight:800}
.brand img{width:44px;height:44px;object-fit:contain;background:#fff;border-radius:50%;padding:3px;margin-right:10px}
.hero{background:linear-gradient(135deg,var(--gd),var(--g));color:#fff;padding:30px 0}
.card{border:0;border-radius:14px;box-shadow:0 5px 20px rgba(0,0,0,.06)}
.stat{border-left:5px solid var(--g)}.table thead{background:#edf8f1;color:var(--gd)}
.section-title{font-weight:900;color:var(--gd);margin-bottom:15px}
.filter-card{background:#fff;border-radius:14px;box-shadow:0 5px 20px rgba(0,0,0,.06)}
.small-note{font-size:12px;color:#6c757d}
</style>
</head>
<body>
<nav class="topbar">
<div class="container d-flex justify-content-between align-items-center">
<a href="../index.php" class="brand d-flex align-items-center"><img src="../assets/img/logo.png" alt="Logo"><span>PMBM - Laporan Admin</span></a>
<div>
<a href="../dashboard_admin.php" class="btn btn-sm btn-outline-light mr-1"><i class="fas fa-home"></i> Dashboard</a>
<a href="../logout.php" class="btn btn-sm btn-outline-light"><i class="fas fa-sign-out-alt"></i> Keluar</a>
</div>
</div>
</nav>

<section class="hero">
<div class="container">
<h1 class="h2 font-weight-bold mb-1">Laporan PMBM</h1>
<p class="mb-0">Statistik, rekap, dan analisis data pendaftar.</p>
</div>
</section>

<div class="container my-4">

<div class="filter-card p-4 mb-4">
<h5 class="section-title"><i class="fas fa-filter mr-2"></i>Filter Laporan</h5>
<form method="get">
<div class="row">
<div class="col-md-3 mb-3">
<label>Status</label>
<select name="status" class="form-control">
<option value="">Semua Status</option>
<option value="Menunggu Verifikasi" <?= $status==='Menunggu Verifikasi'?'selected':'' ?>>Menunggu Verifikasi</option>
<option value="Diterima" <?= $status==='Diterima'?'selected':'' ?>>Diterima</option>
<option value="Ditolak" <?= $status==='Ditolak'?'selected':'' ?>>Ditolak</option>
</select>
</div>
<div class="col-md-3 mb-3">
<label>Jenis Kelamin</label>
<select name="jk" class="form-control">
<option value="">Semua</option>
<?php foreach($jkOptions as $o): ?><option value="<?= e($o['value']) ?>" <?= $jk===$o['value']?'selected':'' ?>><?= e($o['value']) ?></option><?php endforeach; ?>
</select>
</div>
<div class="col-md-3 mb-3">
<label>Asal Sekolah</label>
<select name="sekolah" class="form-control">
<option value="">Semua Sekolah</option>
<?php foreach($sekolahOptions as $o): ?><option value="<?= e($o['value']) ?>" <?= $sekolah===$o['value']?'selected':'' ?>><?= e($o['value']) ?></option><?php endforeach; ?>
</select>
</div>
<div class="col-md-3 mb-3">
<label>Domisili</label>
<select name="domisili" class="form-control">
<option value="">Semua Domisili</option>
<?php foreach($domisiliOptions as $o): ?><option value="<?= e($o['value']) ?>" <?= $domisili===$o['value']?'selected':'' ?>><?= e($o['value']) ?></option><?php endforeach; ?>
</select>
</div>
</div>
<div>
<button class="btn btn-success mr-2"><i class="fas fa-search mr-1"></i> Tampilkan</button>
<a href="laporan.php" class="btn btn-secondary mr-2">Reset</a>
<?php
$query = http_build_query(['status'=>$status,'jk'=>$jk,'sekolah'=>$sekolah,'domisili'=>$domisili]);
?>
<a href="laporan_excel.php?<?= e($query) ?>" class="btn btn-primary"><i class="fas fa-file-excel mr-1"></i> Excel/CSV</a>
</div>
</form>
<p class="small-note mb-0 mt-3">Filter berlaku untuk statistik, tabel pendaftar, dan analisis di halaman ini.</p>
</div>

<h5 class="section-title"><i class="fas fa-chart-pie mr-2"></i>Ringkasan Data</h5>
<div class="row mb-4">
<?php foreach([
['Total Pendaftar',$total,'fa-users','success'],
['Menunggu Verifikasi',$menunggu,'fa-clock','warning'],
['Diterima',$diterima,'fa-check-circle','success'],
['Ditolak',$ditolak,'fa-times-circle','danger']
] as $s): ?>
<div class="col-md-3 mb-3"><div class="card stat h-100"><div class="card-body">
<div class="text-muted small"><?= e($s[0]) ?></div>
<div class="h2 font-weight-bold text-<?= $s[3] ?>"><?= (int)$s[1] ?></div>
<i class="fas <?= $s[2] ?> text-muted"></i>
</div></div></div>
<?php endforeach; ?>
</div>

<div class="card mb-4">
<div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-3">
<h5 class="section-title mb-0"><i class="fas fa-list mr-2"></i>Laporan Data Seluruh Pendaftar</h5>
<div>
    <span class="small-note mr-2"><?= count($students) ?> data sesuai filter</span>
    <a href="laporan_pdf.php?report=1&<?= e($query) ?>" target="_blank" class="btn btn-sm btn-danger">
        <i class="fas fa-print mr-1"></i> Cetak
    </a>
</div>
</div>
<div class="table-responsive">
<table class="table table-hover table-sm">
<thead><tr><th>No</th><th>Nama</th><th>NISN</th><th>JK</th><th>Asal Sekolah</th><th>Domisili</th><th>Status</th><th>Tanggal</th></tr></thead>
<tbody>
<?php if(!$students): ?><tr><td colspan="8" class="text-center text-muted py-4">Belum ada data.</td></tr>
<?php else: foreach($students as $i=>$s):
$sc=stripos($s['status'],'tolak')!==false?'danger':(stripos($s['status'],'diterima')!==false?'success':'warning'); ?>
<tr>
<td><?= $i+1 ?></td><td><?= e($s['nama']) ?></td><td><?= e($s['nisn']) ?></td><td><?= e($s['jk']) ?></td>
<td><?= e($s['nama_sekolah_asal']) ?></td><td><?= e($s['domisili_murid']) ?></td>
<td><span class="badge badge-<?= $sc ?>"><?= e($s['status']) ?></span></td>
<td><?= e($s['created_at']) ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
</div>
</div>

<h5 class="section-title"><i class="fas fa-file-alt mr-2"></i>Laporan</h5>
<div class="row">
<?= tableReport('Laporan Status Pendaftaran', $statusRows, 2, $query) ?>
<?= tableReport('Laporan Jenis Kelamin', $jenisKelamin, 3, $query) ?>
<div class="col-12 mb-4"><div class="card"><div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-3"><h5 class="section-title mb-0">Laporan Data Asal Sekolah</h5><a href="laporan_pdf.php?report=4&<?= e($query) ?>" target="_blank" class="btn btn-sm btn-danger">Cetak</a></div>
<div class="table-responsive"><table class="table table-sm table-hover"><thead><tr><th>No</th><th>Nama Sekolah Asal</th><th>Status Sekolah</th><th>Alamat Sekolah Asal</th></tr></thead><tbody>
<?php if(!$report4Rows): ?><tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr><?php else: foreach($report4Rows as $i=>$r): $st=strtoupper(trim($r['status_sekolah_asal']??'')); $st=$st==='NEGERI'?'Negeri':($st==='SWASTA'?'Swasta':'-'); ?>
<tr><td><?= $i+1 ?></td><td><?= e($r['nama_sekolah_asal']?:'-') ?></td><td><?= e($st) ?></td><td><?= e($r['alamat_sekolah_asal']?:'-') ?></td></tr><?php endforeach; endif; ?>
</tbody></table></div></div></div></div>
<div class="col-12 mb-4"><div class="card"><div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-3"><h5 class="section-title mb-0">Laporan Data Tempat Tinggal Siswa</h5><a href="laporan_pdf.php?report=5&<?= e($query) ?>" target="_blank" class="btn btn-sm btn-danger">Cetak</a></div>
<div class="table-responsive"><table class="table table-sm table-hover"><thead><tr><th>No</th><th>Status Tempat Tinggal</th><th>Alamat</th><th>Kelurahan</th><th>Kecamatan</th><th>Kabupaten / Kota</th></tr></thead><tbody>
<?php if(!$report5Rows): ?><tr><td colspan="6" class="text-center text-muted">Belum ada data.</td></tr><?php else: foreach($report5Rows as $i=>$r): ?>
<tr><td><?= $i+1 ?></td><td><?= e($r['tinggal_bersama']?:'-') ?></td><td><?= e($r['alamat']?:'-') ?></td><td><?= e($r['kelurahan']?:'-') ?></td><td><?= e($r['kecamatan']?:'-') ?></td><td><?= e($r['kabupaten_kota']?:'-') ?></td></tr><?php endforeach; endif; ?>
</tbody></table></div></div></div></div>
</div>

<div class="text-right mb-4">
<a href="laporan_excel.php?<?= e($query) ?>" class="btn btn-primary"><i class="fas fa-file-excel mr-1"></i> Unduh Excel/CSV</a>
</div>

</div>
<footer class="bg-dark text-white text-center py-3 small">© <?= date('Y') ?> MTs Ulumul Qur'an Al Madani</footer>
</body>
</html>
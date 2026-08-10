<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: auth/login.php");
    exit;
}

require_once 'config/database.php';

function e($v){return htmlspecialchars($v ?? '',ENT_QUOTES,'UTF-8');}

$stats = ['total'=>0,'menunggu'=>0,'diterima'=>0,'ditolak'=>0];
$q = mysqli_query($conn, "SELECT COUNT(*) total FROM students");
if($q){$stats['total']=(int)mysqli_fetch_assoc($q)['total'];}
$q = mysqli_query($conn, "SELECT COUNT(*) total FROM students WHERE status LIKE '%Menunggu%' OR status LIKE '%Verifikasi%'");
if($q){$stats['menunggu']=(int)mysqli_fetch_assoc($q)['total'];}
$q = mysqli_query($conn, "SELECT COUNT(*) total FROM students WHERE status LIKE '%Diterima%' OR status LIKE '%Disetujui%'");
if($q){$stats['diterima']=(int)mysqli_fetch_assoc($q)['total'];}
$q = mysqli_query($conn, "SELECT COUNT(*) total FROM students WHERE status LIKE '%Tolak%'");
if($q){$stats['ditolak']=(int)mysqli_fetch_assoc($q)['total'];}

$students = [];
$q = mysqli_query($conn, "SELECT id,nama,nisn,nik,jk,nama_sekolah_asal,status,created_at FROM students ORDER BY id DESC LIMIT 10");
if($q){while($r=mysqli_fetch_assoc($q))$students[]=$r;}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard Admin - PPDB</title>
<link rel="stylesheet" href="assets/css/sb-admin-2.min.css"><link rel="stylesheet" href="assets/vendor/fontawesome-free/css/all.min.css">
<style>
:root{--gd:#075e35;--g:#0b7a45;--y:#f5c400}body{background:#f5f8f6}.topbar{background:var(--gd);padding:12px 0}.brand{color:#fff!important;text-decoration:none;font-weight:800}.brand img{width:44px;height:44px;object-fit:contain;background:#fff;border-radius:50%;padding:3px;margin-right:10px}.hero{background:linear-gradient(135deg,var(--gd),var(--g));color:#fff;padding:34px 0}.card{border:0;border-radius:14px;box-shadow:0 5px 20px rgba(0,0,0,.06)}.stat{border-left:5px solid var(--g)}.table thead{background:#edf8f1;color:var(--gd)}.badge-status{padding:7px 10px;border-radius:15px}.btn-main{background:var(--y);color:#493d00;font-weight:800}
</style>
</head>
<body>
<nav class="topbar"><div class="container d-flex justify-content-between align-items-center"><a href="../index.php" class="brand d-flex align-items-center"><img src="assets/img/logo.png" alt="Logo"><span>PPDB - Panel Admin</span></a><a href="logout.php" class="btn btn-sm btn-outline-light"><i class="fas fa-sign-out-alt mr-1"></i> Keluar</a></div></nav>
<section class="hero"><div class="container"><div class="small text-warning font-weight-bold">ADMINISTRATOR</div><h1 class="h2 font-weight-bold mb-1">Dashboard Admin</h1><p class="mb-0">Kelola pendaftaran dan verifikasi calon murid.</p></div></section>
<div class="container my-4">
<div class="row">
<?php foreach([['total','Total Pendaftar','fa-users','success'],['menunggu','Menunggu Verifikasi','fa-clock','warning'],['diterima','Diterima','fa-check-circle','success'],['ditolak','Ditolak','fa-times-circle','danger']] as $s): ?>
<div class="col-md-3 mb-3"><div class="card stat h-100"><div class="card-body"><div class="text-muted small"><?= $s[1] ?></div><div class="h3 font-weight-bold text-<?= $s[3] ?>"><?= $stats[$s[0]] ?></div><i class="fas <?= $s[2] ?> text-muted"></i></div></div></div>
<?php endforeach; ?>
</div>
<div class="card mb-4"><div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-3"><h5 class="font-weight-bold text-success mb-0">Pendaftar Terbaru</h5><a href="admin/verifikasi.php" class="btn btn-main btn-sm"><i class="fas fa-search mr-1"></i> Kelola Verifikasi</a>
<a href="admin/laporan.php" class="btn btn-success btn-sm ml-2"><i class="fas fa-chart-bar mr-1"></i> Laporan PPDB</a></div>
<div class="table-responsive"><table class="table table-hover">
<thead><tr><th>No</th><th>Nama</th><th>NISN</th><th>Asal Sekolah</th><th>Status</th><th>Aksi</th></tr></thead>
<tbody>
<?php if(!$students): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada pendaftar.</td></tr>
<?php else: foreach($students as $i=>$s): $sc=stripos($s['status'],'tolak')!==false?'danger':(stripos($s['status'],'diterima')!==false||stripos($s['status'],'setujui')!==false?'success':'warning'); ?>
<tr><td><?= $i+1 ?></td><td><?= e($s['nama']) ?></td><td><?= e($s['nisn']) ?></td><td><?= e($s['nama_sekolah_asal']) ?></td><td><span class="badge badge-<?= $sc ?> badge-status"><?= e($s['status']) ?></span></td><td><a class="btn btn-sm btn-outline-success" href="admin/verifikasi.php?id=<?= (int)$s['id'] ?>"><i class="fas fa-eye"></i></a></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div></div></div>
</div>
<footer class="bg-dark text-white text-center py-3 small">© <?= date('Y') ?> MTs Ulumul Qur'an Al Madani</footer>
</body></html>
<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'siswa') {
    header("Location: auth/login.php");
    exit;
}

require_once 'config/database.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
$data = [];

$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result) {
    $data = mysqli_fetch_assoc($result) ?: [];
}
mysqli_stmt_close($stmt);

function e($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$labels = [
    'nama_sekolah_asal'=>'Sekolah Asal','status_sekolah_asal'=>'Status Sekolah Asal','npsn_nsm_asal'=>'NPSN / NSM',
    'nama'=>'Nama Lengkap','nisn'=>'NISN','nik'=>'NIK','tempat_lahir'=>'Tempat Lahir','tanggal_lahir'=>'Tanggal Lahir',
    'jk'=>'Jenis Kelamin','jumlah_saudara'=>'Jumlah Saudara','anak_ke'=>'Anak Ke','cita_cita'=>'Cita-cita','hobi'=>'Hobi',
    'telepon'=>'No. Telepon / WhatsApp','email'=>'Email','pembiaya_sekolah'=>'Pembiaya Sekolah','pra_sekolah'=>'Pra Sekolah',
    'imunisasi'=>'Imunisasi','no_kk'=>'Nomor KK','nama_kepala_keluarga'=>'Kepala Keluarga',
    'nama_ayah'=>'Nama Ayah','status_ayah'=>'Status Ayah','nama_ibu'=>'Nama Ibu','status_ibu'=>'Status Ibu',
    'domisili_murid'=>'Domisili','transportasi'=>'Transportasi','jarak_rumah'=>'Jarak Rumah','waktu_tempuh'=>'Waktu Tempuh',
    'kebutuhan_khusus'=>'Kebutuhan Khusus','kebutuhan_disabilitas'=>'Kebutuhan Disabilitas',
    'status_wali'=>'Status Wali','nama_wali'=>'Nama Wali','hp_wali'=>'HP Wali','tinggal_bersama'=>'Tinggal Bersama'
];

$required = ['nama_sekolah_asal','status_sekolah_asal','nama','nisn','nik','jk','nama_ayah','nama_ibu','domisili_murid','transportasi'];
$filled = 0;
foreach ($required as $f) {
    if (trim((string)($data[$f] ?? '')) !== '') $filled++;
}
$progress = count($required) ? round(($filled / count($required)) * 100) : 0;
$status = $data['status'] ?? 'Belum Mengisi';
$statusClass = 'secondary';
if (stripos($status, 'diterima') !== false || stripos($status, 'disetujui') !== false) $statusClass = 'success';
elseif (stripos($status, 'tolak') !== false) $statusClass = 'danger';
elseif (stripos($status, 'verifikasi') !== false || stripos($status, 'proses') !== false) $statusClass = 'warning';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Siswa - MTs Ulumul Qur'an Al Madani</title>
<link rel="stylesheet" href="assets/css/sb-admin-2.min.css">
<link rel="stylesheet" href="assets/vendor/fontawesome-free/css/all.min.css">
<style>
:root{--gd:#075e35;--g:#0b7a45;--y:#f5c400;--soft:#edf8f1}
body{background:#f5f8f6}.topbar{background:var(--gd);padding:12px 0}.brand{color:#fff!important;font-weight:800;text-decoration:none}.brand img{width:44px;height:44px;object-fit:contain;background:#fff;border-radius:50%;padding:3px;margin-right:10px}.hero{background:linear-gradient(135deg,var(--gd),var(--g));color:#fff;padding:35px 0}.card{border:0;border-radius:14px;box-shadow:0 5px 22px rgba(0,0,0,.06)}.status{font-weight:800;border-radius:20px;padding:7px 13px}.progress{height:9px;border-radius:10px}.btn-main{background:var(--y);color:#493d00;font-weight:800}.btn-main:hover{background:#ffd633;color:#493d00}.section-title{color:var(--gd);font-weight:900;border-bottom:2px solid var(--soft);padding-bottom:10px;margin-bottom:18px}.data-row{padding:9px 0;border-bottom:1px solid #edf0ee}.data-row:last-child{border-bottom:0}.label{color:#6b7280;font-size:13px}.value{font-weight:600;color:#263238;word-break:break-word}
</style>
</head>
<body>
<nav class="topbar"><div class="container d-flex justify-content-between align-items-center">
<a href="index.php" class="brand d-flex align-items-center"><img src="assets/img/logo.png" alt="Logo"><span>MTs Ulumul Qur'an Al Madani</span></a>
<a href="logout.php" class="btn btn-sm btn-outline-light"><i class="fas fa-sign-out-alt mr-1"></i> Keluar</a>
</div></nav>
<section class="hero"><div class="container"><div class="d-flex flex-wrap justify-content-between align-items-center">
<div><div class="small font-weight-bold text-warning">PENERIMAAN PESERTA DIDIK BARU</div><h1 class="h2 font-weight-bold mb-1">Dashboard Calon Murid</h1><p class="mb-0">Pantau data pendaftaran dan status verifikasi kamu.</p></div>
<div class="mt-3 mt-md-0"><span class="badge badge-<?= $statusClass ?> status"><?= e($status) ?></span></div>
</div></div></section>

<div class="container my-4">
<div class="row">
<div class="col-lg-8">
<div class="card mb-4"><div class="card-body">
<div class="d-flex justify-content-between"><div><h5 class="font-weight-bold text-success mb-1">Kelengkapan Data</h5><small class="text-muted"><?= $filled ?> dari <?= count($required) ?> data utama terisi</small></div><strong class="text-success"><?= $progress ?>%</strong></div>
<div class="progress mt-3"><div class="progress-bar bg-success" style="width:<?= $progress ?>%"></div></div>
<div class="mt-3"><a href="pendaftaran.php" class="btn btn-main"><i class="fas fa-edit mr-1"></i> <?= $data ? 'Edit Formulir' : 'Isi Formulir' ?></a>
<a href="upload_berkas.php" class="btn btn-outline-success ml-1"><i class="fas fa-file-upload mr-1"></i> Upload Berkas</a>
</div></div></div>

<div class="card"><div class="card-body"><h5 class="section-title"><i class="fas fa-user mr-2"></i>Ringkasan Biodata</h5>
<div class="row">
<?php foreach(['nama','nisn','nik','tempat_lahir','tanggal_lahir','jk','asal_sekolah','telepon','email','domisili_murid','transportasi','kebutuhan_khusus','kebutuhan_disabilitas'] as $f): ?>
<div class="col-md-6 data-row"><div class="label"><?= e($labels[$f] ?? ucwords(str_replace('_',' ',$f))) ?></div><div class="value"><?= e($data[$f] ?? '-') ?: '-' ?></div></div>
<?php endforeach; ?>
</div></div></div>
</div>

<div class="col-lg-4">
<div class="card mb-4"><div class="card-body"><h5 class="section-title"><i class="fas fa-tasks mr-2"></i>Menu Pendaftaran</h5>
<a href="pendaftaran.php" class="btn btn-block btn-outline-success"><i class="fas fa-edit mr-2"></i>Formulir Pendaftaran</a>
<a href="upload_berkas.php" class="btn btn-block btn-outline-success"><i class="fas fa-upload mr-2"></i>Upload Berkas</a>
<a href="cetak_bukti.php" class="btn btn-block btn-outline-success"><i class="fas fa-print mr-2"></i>Cetak Bukti</a>
</div></div>
<div class="card"><div class="card-body"><h5 class="section-title"><i class="fas fa-info-circle mr-2"></i>Informasi</h5>
<p class="small text-muted mb-0">Pastikan seluruh data sesuai dokumen resmi. Setelah dikirim, data akan diperiksa oleh panitia PPDB.</p>
</div></div>
</div>
</div>
</div>
<footer class="bg-dark text-white text-center py-3 small">© <?= date('Y') ?> MTs Ulumul Qur'an Al Madani</footer>
</body>
</html>
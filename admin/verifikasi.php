<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../config/database.php';

function e($v){return htmlspecialchars($v ?? '',ENT_QUOTES,'UTF-8');}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['aksi'],$_POST['id'])) {
    $id=(int)$_POST['id'];
    $aksi=$_POST['aksi'];
    $allowed=['Menunggu Verifikasi','Diterima','Ditolak'];
    if(in_array($aksi,$allowed,true)){
        $stmt=mysqli_prepare($conn,"UPDATE students SET status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
        mysqli_stmt_bind_param($stmt,"si",$aksi,$id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: verifikasi.php?id=".$id."&saved=1");
    exit;
}

$id=(int)($_GET['id']??0);
$detail=null;
if($id){
    $stmt=mysqli_prepare($conn,"SELECT * FROM students WHERE id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt,"i",$id);mysqli_stmt_execute($stmt);
    $res=mysqli_stmt_get_result($stmt);$detail=$res?mysqli_fetch_assoc($res):null;mysqli_stmt_close($stmt);
}

$filter=$_GET['status']??'';
$list=[];
if($filter && in_array($filter,['Menunggu Verifikasi','Diterima','Ditolak'],true)){
    $stmt=mysqli_prepare($conn,"SELECT id,nama,nisn,nik,jk,nama_sekolah_asal,status,created_at FROM students WHERE status=? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt,"s",$filter);mysqli_stmt_execute($stmt);$res=mysqli_stmt_get_result($stmt);
}else{
    $res=mysqli_query($conn,"SELECT id,nama,nisn,nik,jk,nama_sekolah_asal,status,created_at FROM students ORDER BY id DESC");
}
if($res)while($r=mysqli_fetch_assoc($res))$list[]=$r;

$groups=[
'Informasi Sekolah'=>['nama_sekolah_asal','status_sekolah_asal','npsn_nsm_asal','alamat_sekolah_asal'],
'Biodata Murid'=>['nama','nisn','nik','tempat_lahir','tanggal_lahir','jk','jumlah_saudara','anak_ke','cita_cita','hobi','telepon','email','pembiaya_sekolah','pra_sekolah','imunisasi','no_kk','nama_kepala_keluarga'],
'Ayah'=>['nama_ayah','status_ayah','nik_ayah','tanggal_lahir_ayah','pendidikan_ayah','pekerjaan_ayah','penghasilan_ayah','hp_ayah'],
'Ibu'=>['nama_ibu','status_ibu','nik_ibu','tanggal_lahir_ibu','pendidikan_ibu','pekerjaan_ibu','penghasilan_ibu','hp_ibu'],
'Alamat Ayah'=>['kepemilikan_rumah_ayah','provinsi_ayah','kabupaten_ayah','kecamatan_ayah','kelurahan_ayah','rt_ayah','rw_ayah','jalan_ayah','kode_pos_ayah'],
'Alamat Ibu'=>['alamat_ibu_status','kepemilikan_rumah_ibu','provinsi_ibu','kabupaten_ibu','kecamatan_ibu','kelurahan_ibu','rt_ibu','rw_ibu','jalan_ibu','kode_pos_ibu'],
'Domisili & Kondisi'=>['domisili_murid','transportasi','jarak_rumah','waktu_tempuh','kebutuhan_khusus','kebutuhan_disabilitas'],
'Wali'=>['status_wali','nama_wali','hp_wali','tinggal_bersama','alamat_wali']
];
$label=[];
foreach($groups as $fs)foreach($fs as $f)$label[$f]=ucwords(str_replace('_',' ',$f));

/*
|--------------------------------------------------------------------------
| BERKAS PENDAFTAR
|--------------------------------------------------------------------------
| File disimpan oleh upload_berkas.php dengan pola:
| user_id_jenis_berkas_timestamp.ext
|--------------------------------------------------------------------------
*/
$jenis_berkas_list=[
    'kk'=>'Kartu Keluarga',
    'akta'=>'Akta Kelahiran',
    'ijazah'=>'Ijazah / Surat Keterangan Aktif',
    'foto'=>'Pas Foto',
    'kip'=>'KTP Orang Tua',
    'lainnya'=>'Nomor Induk Siswa Nasional (NISN)'
];

function cariBerkas($user_id,$jenis_berkas){
    $dir=__DIR__.'/../uploads/';
    if(!is_dir($dir)) return null;

    $prefix=(string)$user_id.'_'.$jenis_berkas.'_';
    $files=[];

    $items=scandir($dir);
    if($items===false) return null;

    foreach($items as $item){
        if($item==='.'||$item==='..') continue;
        $full=$dir.$item;
        if(!is_file($full)) continue;
        if(strpos($item,$prefix)!==0) continue;

        $ext=strtolower(pathinfo($item,PATHINFO_EXTENSION));
        if(!in_array($ext,['jpg','jpeg','png','pdf'],true)) continue;

        $files[]=$full;
    }

    if(!$files) return null;

    usort($files,function($a,$b){
        return filemtime($b)<=>filemtime($a);
    });

    return basename($files[0]);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Verifikasi Pendaftar - PPDB</title>
<link rel="stylesheet" href="../assets/css/sb-admin-2.min.css"><link rel="stylesheet" href="../assets/vendor/fontawesome-free/css/all.min.css">
<style>
:root{--gd:#075e35;--g:#0b7a45;--y:#f5c400}body{background:#f5f8f6}.topbar{background:var(--gd);padding:12px 0}.brand{color:#fff!important;text-decoration:none;font-weight:800}.brand img{width:44px;height:44px;object-fit:contain;background:#fff;border-radius:50%;padding:3px;margin-right:10px}.hero{background:linear-gradient(135deg,var(--gd),var(--g));color:#fff;padding:30px 0}.card{border:0;border-radius:14px;box-shadow:0 5px 20px rgba(0,0,0,.06)}.table thead{background:#edf8f1;color:var(--gd)}.section-title{font-weight:900;color:var(--gd);border-bottom:2px solid #edf8f1;padding-bottom:9px;margin-bottom:15px}.field{padding:8px 0;border-bottom:1px solid #eef1ef}.field .lbl{font-size:12px;color:#777}.field .val{font-weight:600;word-break:break-word}.btn-y{background:var(--y);color:#493d00;font-weight:800}.detail-card{position:sticky;top:15px}.doc-row{border:1px solid #e8eee9;border-radius:10px;padding:12px 14px;margin-bottom:10px;background:#fbfdfb}.doc-name{font-weight:700;color:var(--gd)}.doc-file{font-size:12px;color:#777;word-break:break-all}.doc-missing{color:#999;font-size:13px}.btn-view{background:var(--gd);color:#fff;font-weight:700}.btn-view:hover{background:var(--g);color:#fff}
</style>
</head>
<body>
<nav class="topbar"><div class="container d-flex justify-content-between align-items-center"><a href="../index.php" class="brand d-flex align-items-center"><img src="../assets/img/logo.png" alt="Logo"><span>PPDB - Verifikasi</span></a><div><a href="../dashboard_admin.php" class="btn btn-sm btn-outline-light mr-1"><i class="fas fa-home"></i> Dashboard</a><a href="../logout.php" class="btn btn-sm btn-outline-light"><i class="fas fa-sign-out-alt"></i></a></div></div></nav>
<section class="hero"><div class="container"><h1 class="h2 font-weight-bold mb-1">Verifikasi Pendaftar</h1><p class="mb-0">Periksa seluruh data sebelum menentukan status pendaftaran.</p></div></section>

<div class="container my-4">
<?php if(isset($_GET['saved'])): ?><div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i>Status pendaftar berhasil diperbarui.</div><?php endif; ?>
<div class="card mb-4"><div class="card-body">
<form class="form-inline">
<label class="mr-2 font-weight-bold">Filter status:</label>
<select name="status" class="form-control mr-2"><option value="">Semua</option><?php foreach(['Menunggu Verifikasi','Diterima','Ditolak'] as $s): ?><option value="<?= $s ?>" <?= $filter===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select>
<button class="btn btn-success mr-2">Tampilkan</button><a href="verifikasi.php" class="btn btn-light">Reset</a>
</form></div></div>

<div class="row">
<div class="col-lg-7 mb-4"><div class="card"><div class="card-body"><h5 class="font-weight-bold text-success mb-3">Daftar Calon Murid</h5>
<div class="table-responsive"><table class="table table-hover table-sm"><thead><tr><th>No</th><th>Nama</th><th>NISN</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php if(!$list): ?><tr><td colspan="5" class="text-center text-muted py-4">Belum ada data.</td></tr>
<?php else: foreach($list as $i=>$s): $sc=stripos($s['status'],'tolak')!==false?'danger':(stripos($s['status'],'diterima')!==false?'success':'warning'); ?>
<tr><td><?= $i+1 ?></td><td><?= e($s['nama']) ?></td><td><?= e($s['nisn']) ?></td><td><span class="badge badge-<?= $sc ?>"><?= e($s['status']) ?></span></td><td><a href="?id=<?= (int)$s['id'] ?><?= $filter?'&status='.urlencode($filter):'' ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-eye"></i></a></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div></div></div></div>

<div class="col-lg-5">
<?php if($detail): ?>
<div class="card detail-card"><div class="card-body">
<div class="d-flex justify-content-between align-items-start mb-3"><div><h5 class="font-weight-bold text-success mb-1"><?= e($detail['nama']) ?></h5><small class="text-muted">NISN: <?= e($detail['nisn']) ?></small></div><span class="badge badge-<?= stripos($detail['status'],'tolak')!==false?'danger':(stripos($detail['status'],'diterima')!==false?'success':'warning') ?>"><?= e($detail['status']) ?></span></div>
<form method="POST" class="mb-4"><input type="hidden" name="id" value="<?= (int)$detail['id'] ?>"><div class="form-row align-items-center"><div class="col"><select name="aksi" class="form-control"><option value="Menunggu Verifikasi">Menunggu Verifikasi</option><option value="Diterima">Diterima</option><option value="Ditolak">Ditolak</option></select></div><div class="col-auto"><button class="btn btn-y" type="submit"><i class="fas fa-save mr-1"></i> Simpan Status</button></div></div></form>
<?php foreach($groups as $g=>$fields): ?>
<h6 class="section-title mt-3"><?= e($g) ?></h6>
<div class="row">
<?php foreach($fields as $f): if(!array_key_exists($f,$detail)) continue; $v=$detail[$f]; if($v===''||$v===null) $v='-'; ?>
<div class="col-md-6 field"><div class="lbl"><?= e($label[$f]) ?></div><div class="val"><?= nl2br(e($v)) ?></div></div>
<?php endforeach; ?>
</div>
<?php endforeach; ?>

<h6 class="section-title mt-4">Berkas Pendaftaran</h6>
<div class="mb-2">
<?php if(empty($detail['user_id'])): ?>
    <div class="alert alert-warning mb-0">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        Data user_id siswa belum tersedia.
    </div>
<?php else: ?>
    <?php foreach($jenis_berkas_list as $jenis=>$nama): ?>
        <?php $file_berkas=cariBerkas($detail['user_id'],$jenis); ?>
        <div class="doc-row">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mr-2">
                    <div class="doc-name"><i class="fas fa-file-alt mr-1"></i><?= e($nama) ?></div>
                    <?php if($file_berkas): ?>
                        <div class="doc-file"><?= e($file_berkas) ?></div>
                    <?php else: ?>
                        <div class="doc-missing">Belum diupload</div>
                    <?php endif; ?>
                </div>
                <?php if($file_berkas): ?>
                    <a href="lihat_berkas.php?id=<?= (int)$detail['id'] ?>&jenis=<?= urlencode($jenis) ?>" target="_blank" class="btn btn-sm btn-view flex-shrink-0">
                        <i class="fas fa-eye mr-1"></i> Lihat
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>
</div></div>
<?php else: ?>
<div class="card"><div class="card-body text-center py-5"><i class="fas fa-user-check fa-3x text-success mb-3"></i><h5 class="font-weight-bold">Pilih pendaftar</h5><p class="text-muted mb-0">Klik tombol mata pada daftar untuk melihat data lengkap.</p></div></div>
<?php endif; ?>
</div>
</div>
</div>
<footer class="bg-dark text-white text-center py-3 small">© <?= date('Y') ?> MTs Ulumul Qur'an Al Madani</footer>
</body></html>
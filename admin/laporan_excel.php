<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../config/database.php';

$status = $_GET['status'] ?? '';
$jk = $_GET['jk'] ?? '';
$sekolah = $_GET['sekolah'] ?? '';
$domisili = $_GET['domisili'] ?? '';

$conditions = [];
if (in_array($status, ['Menunggu Verifikasi','Diterima','Ditolak'], true)) $conditions[] = "status='".mysqli_real_escape_string($conn,$status)."'";
if ($jk !== '') $conditions[] = "jk='".mysqli_real_escape_string($conn,$jk)."'";
if ($sekolah !== '') $conditions[] = "nama_sekolah_asal='".mysqli_real_escape_string($conn,$sekolah)."'";
if ($domisili !== '') $conditions[] = "domisili_murid='".mysqli_real_escape_string($conn,$domisili)."'";
$where = $conditions ? ' WHERE '.implode(' AND ',$conditions) : '';

$q = mysqli_query($conn, "SELECT nama,nisn,nik,jk,nama_sekolah_asal,domisili_murid,status,created_at FROM students $where ORDER BY id DESC");

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan_pmbm.csv"');
echo "\xEF\xBB\xBF";

$out = fopen('php://output','w');
fputcsv($out,['No','Nama','NISN','NIK','Jenis Kelamin','Asal Sekolah','Domisili','Status','Tanggal Daftar']);

$no=1;
if($q){
    while($r=mysqli_fetch_assoc($q)){
        fputcsv($out,[$no++,$r['nama'],$r['nisn'],$r['nik'],$r['jk'],$r['nama_sekolah_asal'],$r['domisili_murid'],$r['status'],$r['created_at']]);
    }
}
fclose($out);
exit;

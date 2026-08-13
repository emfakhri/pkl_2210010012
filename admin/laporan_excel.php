<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { header("Location: ../auth/login.php"); exit; }
require_once '../config/database.php';

$status=$_GET['status']??''; $jk=$_GET['jk']??''; $sekolah=$_GET['sekolah']??''; $domisili=$_GET['domisili']??'';
$conditions=[];
if(in_array($status,['Menunggu Verifikasi','Diterima','Ditolak'],true)) $conditions[]="status='".mysqli_real_escape_string($conn,$status)."'";
if($jk!=='') $conditions[]="jk='".mysqli_real_escape_string($conn,$jk)."'";
if($sekolah!=='') $conditions[]="nama_sekolah_asal='".mysqli_real_escape_string($conn,$sekolah)."'";
if($domisili!=='') $conditions[]="domisili_murid='".mysqli_real_escape_string($conn,$domisili)."'";
$where=$conditions?' WHERE '.implode(' AND ',$conditions):'';
function sectionRows($conn,$field,$where){
  $q=mysqli_query($conn,"SELECT COALESCE(NULLIF($field,''),'Tidak diisi') label, COUNT(*) jumlah FROM students $where GROUP BY $field ORDER BY jumlah DESC,label ASC");
  $rows=[]; if($q) while($r=mysqli_fetch_assoc($q)) $rows[]=$r; return $rows;
}
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan_pmbm_5_report.csv"');
echo "\xEF\xBB\xBF"; $out=fopen('php://output','w');
function title($out,$text){ fputcsv($out,[$text]); }
function groupSection($out,$conn,$title,$field,$where){ title($out,$title); fputcsv($out,['Kategori','Jumlah']); foreach(sectionRows($conn,$field,$where) as $r) fputcsv($out,[$r['label'],$r['jumlah']]); fputcsv($out,[]); }
title($out,'REPORT 1 - DATA SELURUH PENDAFTAR');
fputcsv($out,['No','Nama','NISN','NIK','Jenis Kelamin','Asal Sekolah','Domisili','Status','Tanggal Daftar']);
$q=mysqli_query($conn,"SELECT nama,nisn,nik,jk,nama_sekolah_asal,domisili_murid,status,created_at FROM students $where ORDER BY id DESC"); $no=1;
if($q) while($r=mysqli_fetch_assoc($q)) fputcsv($out,[$no++,$r['nama'],$r['nisn'],$r['nik'],$r['jk'],$r['nama_sekolah_asal'],$r['domisili_murid'],$r['status'],$r['created_at']]);
fputcsv($out,[]);
groupSection($out,$conn,'REPORT 2 - STATUS PENDAFTARAN','status',$where);
groupSection($out,$conn,'REPORT 3 - JENIS KELAMIN','jk',$where);
groupSection($out,$conn,'REPORT 4 - ASAL SEKOLAH','nama_sekolah_asal',$where);
groupSection($out,$conn,'REPORT 5 - DOMISILI','domisili_murid',$where);
fclose($out); exit;

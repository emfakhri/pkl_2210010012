<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../config/database.php';

function e($v){
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$id=(int)($_GET['id']??0);
$jenis=$_GET['jenis']??'';

$daftar_berkas=[
    'kk'=>'Kartu Keluarga',
    'akta'=>'Akta Kelahiran',
    'ijazah'=>'Ijazah / Surat Keterangan Aktif',
    'foto'=>'Pas Foto',
    'kip'=>'KTP Orang Tua',
    'lainnya'=>'Nomor Induk Siswa Nasional (NISN)'
];

if($id<=0 || !array_key_exists($jenis,$daftar_berkas)){
    http_response_code(400);
    exit('Permintaan berkas tidak valid.');
}

/*
|--------------------------------------------------------------------------
| Ambil user_id berdasarkan students.id
|--------------------------------------------------------------------------
*/
$stmt=mysqli_prepare($conn,"SELECT id,nama,user_id FROM students WHERE id=? LIMIT 1");
mysqli_stmt_bind_param($stmt,"i",$id);
mysqli_stmt_execute($stmt);
$res=mysqli_stmt_get_result($stmt);
$student=$res?mysqli_fetch_assoc($res):null;
mysqli_stmt_close($stmt);

if(!$student){
    http_response_code(404);
    exit('Data siswa tidak ditemukan.');
}

$user_id=(string)$student['user_id'];
$upload_dir=__DIR__.'/../uploads/';

if(!is_dir($upload_dir)){
    http_response_code(404);
    exit('Folder uploads tidak ditemukan.');
}

/*
|--------------------------------------------------------------------------
| Cari file dengan pola yang sama persis dengan upload_berkas.php
|--------------------------------------------------------------------------
| user_id_jenis_berkas_timestamp.ext
|--------------------------------------------------------------------------
*/
$prefix=$user_id.'_'.$jenis.'_';
$files=[];
$items=scandir($upload_dir);

if($items===false){
    http_response_code(500);
    exit('Folder uploads tidak dapat dibaca.');
}

foreach($items as $item){
    if($item==='.'||$item==='..') continue;

    $full=$upload_dir.$item;
    if(!is_file($full)) continue;
    if(strpos($item,$prefix)!==0) continue;

    $ext=strtolower(pathinfo($item,PATHINFO_EXTENSION));
    if(!in_array($ext,['jpg','jpeg','png','pdf'],true)) continue;

    $files[]=$full;
}

if(!$files){
    http_response_code(404);
    exit('Berkas '.$daftar_berkas[$jenis].' belum diupload oleh siswa ini.');
}

/* Jika ada beberapa versi, buka yang terbaru. */
usort($files,function($a,$b){
    return filemtime($b)<=>filemtime($a);
});

$file_path=$files[0];

/*
|--------------------------------------------------------------------------
| Pastikan path tetap di dalam folder uploads
|--------------------------------------------------------------------------
*/
$real_upload=realpath($upload_dir);
$real_file=realpath($file_path);

if($real_upload===false || $real_file===false ||
   strpos($real_file,$real_upload.DIRECTORY_SEPARATOR)!==0){
    http_response_code(403);
    exit('Akses file ditolak.');
}

$allowed_mime=[
    'jpg'=>'image/jpeg',
    'jpeg'=>'image/jpeg',
    'png'=>'image/png',
    'pdf'=>'application/pdf'
];

$ext=strtolower(pathinfo($real_file,PATHINFO_EXTENSION));

if(!isset($allowed_mime[$ext])){
    http_response_code(403);
    exit('Format file tidak diperbolehkan.');
}

$size=filesize($real_file);
if($size===false){
    http_response_code(500);
    exit('File tidak dapat dibaca.');
}

header('Content-Type: '.$allowed_mime[$ext]);
header('Content-Length: '.$size);
header('Content-Disposition: inline; filename="'.basename($real_file).'"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

readfile($real_file);
exit;
?>
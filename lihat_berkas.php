<?php

session_start();

/*
|--------------------------------------------------------------------------
| CEK LOGIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Akses ditolak.');
}

$user_id = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| AMBIL NAMA FILE
|--------------------------------------------------------------------------
*/
$nama_file = basename($_GET['file'] ?? '');

if ($nama_file === '') {
    http_response_code(404);
    exit('File tidak ditemukan.');
}

/*
|--------------------------------------------------------------------------
| VALIDASI NAMA FILE
| File upload project menggunakan pola:
| user_id_jenis_timestamp.ext
|--------------------------------------------------------------------------
*/
$jenis_valid = ['kk', 'akta', 'ijazah', 'foto', 'kip', 'lainnya'];

$pattern = '/^' . preg_quote((string)$user_id, '/') .
           '_(' . implode('|', array_map('preg_quote', $jenis_valid)) .
           ')_[0-9]+\.(jpg|jpeg|png|pdf)$/i';

if (!preg_match($pattern, $nama_file)) {
    http_response_code(403);
    exit('File tidak diizinkan.');
}

$upload_dir = __DIR__ . '/uploads/';
$file_path = realpath($upload_dir . $nama_file);
$upload_real = realpath($upload_dir);

/*
|--------------------------------------------------------------------------
| PASTIKAN FILE BERADA DI FOLDER UPLOAD
|--------------------------------------------------------------------------
*/
if (
    $file_path === false ||
    $upload_real === false ||
    strpos($file_path, $upload_real . DIRECTORY_SEPARATOR) !== 0 ||
    !is_file($file_path)
) {
    http_response_code(404);
    exit('File tidak ditemukan.');
}

/*
|--------------------------------------------------------------------------
| TENTUKAN MIME TYPE
|--------------------------------------------------------------------------
*/
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file_path);
finfo_close($finfo);

$mime_diizinkan = [
    'image/jpeg',
    'image/png',
    'application/pdf'
];

if (!in_array($mime, $mime_diizinkan, true)) {
    http_response_code(403);
    exit('Format file tidak diizinkan.');
}

/*
|--------------------------------------------------------------------------
| TAMPILKAN FILE
|--------------------------------------------------------------------------
*/
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($file_path));
header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
header('X-Content-Type-Options: nosniff');

readfile($file_path);
exit;
?>
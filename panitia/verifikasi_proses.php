<?php
include '../config/database.php';

$id = $_GET['id'];
$status = $_GET['status'];

mysqli_query($koneksi, "
    UPDATE siswa SET status_verifikasi='$status' WHERE id='$id'
");

header("location:verifikasi.php");

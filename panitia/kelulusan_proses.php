<?php
include '../config/database.php';

$id = $_GET['id'];
$lulus = $_GET['lulus'];

mysqli_query($koneksi, "
    UPDATE siswa SET status_lulus='$lulus' WHERE id='$id'
");

header("location:kelulusan.php");

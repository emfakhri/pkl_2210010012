<?php
session_start();
require 'dompdf/autoload.inc.php';
include 'config/db.php';

use Dompdf\Dompdf;

$pdf = new Dompdf();

$user_id = $_SESSION['user_id'];
$q = mysqli_query($conn, "
SELECT * FROM students 
WHERE user_id='$user_id'
");
$d = mysqli_fetch_assoc($q);

$html = "
<h3 style='text-align:center'>BUKTI PENDAFTARAN PPDB</h3>
<hr>
<p>Nama: {$d['nama']}</p>
<p>NISN: {$d['nisn']}</p>
<p>Asal Sekolah: {$d['asal_sekolah']}</p>
<p>Status: {$d['status']}</p>
";

$pdf->loadHtml($html);
$pdf->setPaper('A4', 'portrait');
$pdf->render();
$pdf->stream("bukti_pendaftaran.pdf", ["Attachment" => false]);

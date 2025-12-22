<?php
require '../dompdf/autoload.inc.php';
include '../config/db.php';

use Dompdf\Dompdf;

$pdf = new Dompdf();

$q = mysqli_query($conn, "SELECT * FROM students");

$html = "<h3 style='text-align:center'>LAPORAN PPDB</h3>
<table border='1' width='100%' cellspacing='0' cellpadding='5'>
<tr>
<th>No</th><th>Nama</th><th>NISN</th><th>Status</th>
</tr>";

$no = 1;
while ($d = mysqli_fetch_assoc($q)) {
    $html .= "
<tr>
<td>$no</td>
<td>{$d['nama']}</td>
<td>{$d['nisn']}</td>
<td>{$d['status']}</td>
</tr>";
    $no++;
}
$html .= "</table>";

$pdf->loadHtml($html);
$pdf->setPaper('A4', 'landscape');
$pdf->render();
$pdf->stream("laporan_ppdb.pdf", ["Attachment" => false]);

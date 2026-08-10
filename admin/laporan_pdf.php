<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require '../dompdf/autoload.inc.php';
require '../config/database.php';

use Dompdf\Dompdf;

function e($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$status = $_GET['status'] ?? '';
$jk = $_GET['jk'] ?? '';
$sekolah = $_GET['sekolah'] ?? '';
$domisili = $_GET['domisili'] ?? '';

$conditions = [];

if (in_array($status, ['Menunggu Verifikasi', 'Diterima', 'Ditolak'], true)) {
    $conditions[] = "status='" . mysqli_real_escape_string($conn, $status) . "'";
} else {
    $status = '';
}
if ($jk !== '') $conditions[] = "jk='" . mysqli_real_escape_string($conn, $jk) . "'";
if ($sekolah !== '') $conditions[] = "nama_sekolah_asal='" . mysqli_real_escape_string($conn, $sekolah) . "'";
if ($domisili !== '') $conditions[] = "domisili_murid='" . mysqli_real_escape_string($conn, $domisili) . "'";

$where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

function countReport($conn, $where, $extra = '') {
    $w = $where;
    if ($extra !== '') $w .= ($w ? ' AND ' : ' WHERE ') . $extra;
    $q = mysqli_query($conn, "SELECT COUNT(*) total FROM students $w");
    return $q ? (int)mysqli_fetch_assoc($q)['total'] : 0;
}

function groupRows($conn, $field, $where) {
    $safeField = preg_replace('/[^a-zA-Z0-9_]/', '', $field);
    $q = mysqli_query($conn, "SELECT COALESCE(NULLIF($safeField,''),'Tidak diisi') label, COUNT(*) jumlah FROM students $where GROUP BY $safeField ORDER BY jumlah DESC, label ASC");
    $rows = [];
    if ($q) while ($r = mysqli_fetch_assoc($q)) $rows[] = $r;
    return $rows;
}

$total = countReport($conn, $where);
$menunggu = countReport($conn, $where, "status='Menunggu Verifikasi'");
$diterima = countReport($conn, $where, "status='Diterima'");
$ditolak = countReport($conn, $where, "status='Ditolak'");

$q = mysqli_query($conn, "SELECT nama,nisn,nik,jk,nama_sekolah_asal,domisili_murid,status,created_at FROM students $where ORDER BY id DESC");
$students = [];
if ($q) while ($r = mysqli_fetch_assoc($q)) $students[] = $r;

$reports = [
    'Asal Sekolah' => groupRows($conn,'nama_sekolah_asal',$where),
    'Jenis Kelamin' => groupRows($conn,'jk',$where),
    'Domisili' => groupRows($conn,'domisili_murid',$where),
    'Pendidikan Ayah' => groupRows($conn,'pendidikan_ayah',$where),
    'Pendidikan Ibu' => groupRows($conn,'pendidikan_ibu',$where),
    'Pekerjaan Ayah' => groupRows($conn,'pekerjaan_ayah',$where),
    'Pekerjaan Ibu' => groupRows($conn,'pekerjaan_ibu',$where),
    'Penghasilan Ayah' => groupRows($conn,'penghasilan_ayah',$where),
    'Penghasilan Ibu' => groupRows($conn,'penghasilan_ibu',$where),
    'Kebutuhan Khusus' => groupRows($conn,'kebutuhan_khusus',$where)
];

$filterText = [];
if ($status) $filterText[] = "Status: ".e($status);
if ($jk) $filterText[] = "Jenis Kelamin: ".e($jk);
if ($sekolah) $filterText[] = "Asal Sekolah: ".e($sekolah);
if ($domisili) $filterText[] = "Domisili: ".e($domisili);
$filterLine = $filterText ? implode(' | ', $filterText) : 'Semua data';

$html = '<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
@page{margin:25px 25px 35px 25px}
body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#222}
.header{text-align:center;margin-bottom:12px}.header h2{margin:0;font-size:16px}.header h3{margin:6px 0;font-size:12px}.filter{text-align:center;color:#555;margin-bottom:12px}
.stats{width:100%;border-collapse:collapse;margin-bottom:15px}.stats td{border:1px solid #bbb;padding:7px;text-align:center}.stats b{font-size:14px}
table{width:100%;border-collapse:collapse;margin-bottom:15px}th,td{border:1px solid #777;padding:4px}th{background:#edf8f1}.center{text-align:center}
h4{color:#075e35;margin:10px 0 6px}.small{font-size:8px;color:#666}
</style></head><body>';

$html .= '<div class="header"><h2>MTs ULUMUL QUR\'AN AL MADANI</h2><h3>LAPORAN PPDB</h3><div>'.$filterLine.'</div></div>';

$html .= '<table class="stats"><tr>
<td><b>'.$total.'</b><br>Total Pendaftar</td>
<td><b>'.$menunggu.'</b><br>Menunggu Verifikasi</td>
<td><b>'.$diterima.'</b><br>Diterima</td>
<td><b>'.$ditolak.'</b><br>Ditolak</td>
</tr></table>';

$html .= '<h4>Laporan Pendaftar</h4><table><tr>
<th width="4%">No</th><th>Nama</th><th>NISN</th><th>JK</th><th>Asal Sekolah</th><th>Domisili</th><th>Status</th><th>Tanggal</th></tr>';
$no=1;
foreach($students as $s){
    $html .= '<tr><td class="center">'.$no.'</td><td>'.e($s['nama']).'</td><td>'.e($s['nisn']).'</td><td class="center">'.e($s['jk']).'</td><td>'.e($s['nama_sekolah_asal']).'</td><td>'.e($s['domisili_murid']).'</td><td>'.e($s['status']).'</td><td>'.e($s['created_at']).'</td></tr>';
    $no++;
}
if (!$students) $html .= '<tr><td colspan="8" class="center">Belum ada data.</td></tr>';
$html .= '</table>';

foreach($reports as $title=>$rows){
    $html .= '<h4>'.$title.'</h4><table><tr><th>Kategori</th><th width="100">Jumlah</th></tr>';
    if (!$rows) {
        $html .= '<tr><td colspan="2" class="center">Belum ada data.</td></tr>';
    } else {
        foreach($rows as $r) $html .= '<tr><td>'.e($r['label']).'</td><td class="center">'.(int)$r['jumlah'].'</td></tr>';
    }
    $html .= '</table>';
}

$html .= '<div class="small">Dicetak pada: '.date('d-m-Y H:i:s').'</div></body></html>';

$pdf = new Dompdf();
$pdf->loadHtml($html);
$pdf->setPaper('A4','landscape');
$pdf->render();

$name='laporan_ppdb';
if($status) $name.='_'.strtolower(str_replace(' ','_',$status));
$pdf->stream($name.'.pdf',['Attachment'=>true]);
exit;

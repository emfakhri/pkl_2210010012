<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'siswa') {
    header("Location: auth/login.php");
    exit;
}

require_once 'config/database.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
$data = [];

$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result) {
    $data = mysqli_fetch_assoc($result) ?: [];
}
mysqli_stmt_close($stmt);

$success = '';
$error = '';

function old($key, $data) {
    return htmlspecialchars($data[$key] ?? '', ENT_QUOTES, 'UTF-8');
}
function selected($key, $value, $data) {
    return (($data[$key] ?? '') === $value) ? 'selected' : '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $fields = [
        'nama_sekolah_asal','status_sekolah_asal','npsn_nsm_asal','alamat_sekolah_asal',
        'nama','nisn','nik','tempat_lahir','tanggal_lahir','jk','jumlah_saudara','anak_ke',
        'cita_cita','hobi','telepon','email','pembiaya_sekolah','pra_sekolah','imunisasi',
        'no_kk','nama_kepala_keluarga',
        'nama_ayah','status_ayah','nik_ayah','tanggal_lahir_ayah','pendidikan_ayah',
        'pekerjaan_ayah','penghasilan_ayah','hp_ayah',
        'nama_ibu','status_ibu','nik_ibu','tanggal_lahir_ibu','pendidikan_ibu',
        'pekerjaan_ibu','penghasilan_ibu','hp_ibu',
        'kepemilikan_rumah_ayah','provinsi_ayah','kabupaten_ayah','kecamatan_ayah',
        'kelurahan_ayah','rt_ayah','rw_ayah','jalan_ayah','kode_pos_ayah',
        'alamat_ibu_status','kepemilikan_rumah_ibu','provinsi_ibu','kabupaten_ibu',
        'kecamatan_ibu','kelurahan_ibu','rt_ibu','rw_ibu','jalan_ibu','kode_pos_ibu',
        'domisili_murid','transportasi','jarak_rumah','waktu_tempuh',
        'kebutuhan_khusus','kebutuhan_disabilitas',
        'status_wali','nama_wali','hp_wali','tinggal_bersama','alamat_wali'
    ];

    $post = [];
    foreach ($fields as $field) {
        $post[$field] = trim($_POST[$field] ?? '');
    }

    if ($post['alamat_ibu_status'] !== 'BERBEDA_DENGAN_AYAH') {
        foreach (['kepemilikan_rumah_ibu','provinsi_ibu','kabupaten_ibu','kecamatan_ibu',
                  'kelurahan_ibu','rt_ibu','rw_ibu','jalan_ibu','kode_pos_ibu'] as $f) {
            $post[$f] = '';
        }
    }

    if ($post['domisili_murid'] === 'TINGGAL DENGAN ORANG TUA') {
        foreach (['status_wali','nama_wali','hp_wali','tinggal_bersama','alamat_wali'] as $f) {
            $post[$f] = '';
        }
    }

    if ($post['nama'] === '' || $post['nisn'] === '' || $post['nik'] === '') {
        $error = 'Nama lengkap, NISN, dan NIK wajib diisi.';
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM students WHERE user_id = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "i", $user_id);
        mysqli_stmt_execute($check);
        $checkResult = mysqli_stmt_get_result($check);
        $existing = $checkResult ? mysqli_fetch_assoc($checkResult) : null;
        mysqli_stmt_close($check);

        if ($existing) {
            $setParts = [];
            foreach ($fields as $f) {
                $setParts[] = "`$f` = ?";
            }
            $sql = "UPDATE students SET " . implode(', ', $setParts) .
                   ", updated_at = CURRENT_TIMESTAMP WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $sql);

            $types = str_repeat('s', count($fields)) . 'i';
            $bindValues = array_values($post);
            $bindValues[] = $user_id;
            mysqli_stmt_bind_param($stmt, $types, ...$bindValues);
            $ok = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $columns = array_merge(['user_id'], $fields);
            $colSql = '`' . implode('`,`', $columns) . '`';
            $placeholders = implode(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO students ($colSql, status) VALUES ($placeholders, 'Menunggu Verifikasi')";
            $stmt = mysqli_prepare($conn, $sql);

            $types = 'i' . str_repeat('s', count($fields));
            $values = array_merge([$user_id], array_values($post));
            mysqli_stmt_bind_param($stmt, $types, ...$values);
            $ok = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        if ($ok) {
            $success = 'Data pendaftaran berhasil disimpan.';
            $data = array_merge($data, $post);
        } else {
            $error = 'Data gagal disimpan: ' . mysqli_error($conn);
        }
    }
}

$edu = ['Tidak tamat sekolah','SD / sederajat','SMP / sederajat','SMA / sederajat','D1','D2','D3','D4','S1','S2','S3'];
$jobs = ['Tidak bekerja','Pensiunan','PNS','TNI','Polri','Guru','Dosen','Karyawan swasta','Wiraswasta',
         'Pengacara / Jaksa / Hakim / Notaris','Seniman / Artis','Tenaga kesehatan','Pedagang',
         'Petani / Peternak','Buruh (tani, pabrik, bangunan)','Sopir','Politikus','Lainnya'];
$income = ['DIBAWAH Rp. 800.000','Rp. 800.001 - Rp. 1.200.000','Rp. 1.200.001 - Rp. 1.800.000',
           'Rp. 1.800.001 - Rp. 2.500.000','Rp. 2.500.001 - Rp. 3.500.000','Rp. 3.500.001 - Rp. 4.800.000',
           'Rp. 4.800.001 - Rp. 6.500.000','Rp. 6.500.001 - Rp. 10.000.000',
           'Rp. 10.000.001 - Rp. 20.000.000','DIATAS Rp. 20.000.001','TIDAK ADA'];
$homeOwnership = ['MILIK SENDIRI','RUMAH ORANG TUA','RUMAH SAUDARA / KERABAT','RUMAH DINAS','SEWA / KONTRAK'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Formulir PMBM - MTs Ulumul Qur'an Al Madani</title>
<link rel="stylesheet" href="assets/css/sb-admin-2.min.css">
<link rel="stylesheet" href="assets/vendor/fontawesome-free/css/all.min.css">
<style>
:root{--gdark:#075e35;--green:#0b7a45;--soft:#eaf7ef;--yellow:#f5c400;--ys:#fff8d6;--muted:#6b7280}
body{background:#f5f8f6;color:#263238;font-family:"Segoe UI",Arial,sans-serif}
.topbar{background:var(--gdark);padding:12px 0;box-shadow:0 3px 18px rgba(0,0,0,.12)}
.brand{color:#fff!important;text-decoration:none!important;font-weight:800}
.brand img{width:46px;height:46px;object-fit:contain;background:#fff;border-radius:50%;padding:3px;margin-right:10px}
.back{color:#fff;border:1px solid rgba(255,255,255,.55);padding:8px 14px;border-radius:8px;text-decoration:none!important}
.back:hover{background:var(--yellow);color:#333}
.hero{background:linear-gradient(135deg,var(--gdark),var(--green));color:#fff;padding:42px 0 72px;position:relative;overflow:hidden}
.hero:after{content:"";position:absolute;width:330px;height:330px;border-radius:50%;right:-100px;top:-160px;background:rgba(245,196,0,.14)}
.hero h1,.hero p{position:relative;z-index:1}.hero h1{font-weight:900;font-size:34px}.hero p{opacity:.9}
.wrap{max-width:1050px;margin:-35px auto 60px;position:relative;z-index:2}
.card-main{background:#fff;border-radius:18px;box-shadow:0 10px 35px rgba(0,0,0,.08);overflow:hidden}
.card-head{padding:24px 30px;border-bottom:1px solid #e5e7eb}.card-head h3{margin:0;color:var(--gdark);font-weight:900}.card-head p{margin:6px 0 0;color:var(--muted)}
.form-body{padding:30px}.section{margin-bottom:38px}.section-title{display:flex;align-items:center;gap:12px;color:var(--gdark);font-size:19px;font-weight:900;margin-bottom:22px;padding-bottom:10px;border-bottom:2px solid var(--soft)}
.section-title .icon{width:38px;height:38px;background:var(--ys);color:#8d7000;border-radius:10px;display:flex;align-items:center;justify-content:center}
label{font-weight:700;font-size:14px;color:#374151}.req{color:#dc2626}.form-control,.custom-select{border-radius:8px;min-height:44px;border:1px solid #d1d5db}.form-control:focus,.custom-select:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(11,122,69,.1)}
textarea.form-control{min-height:95px}.info{background:var(--soft);border-left:4px solid var(--green);padding:14px 16px;border-radius:7px;margin-bottom:25px;font-size:14px}
.conditional{display:none;background:#fbfefc;border:1px dashed #b8d9c4;padding:20px;border-radius:12px;margin-top:10px}
.actions{border-top:1px solid #e5e7eb;padding-top:24px;display:flex;justify-content:space-between;gap:12px}
.btn-save{background:var(--yellow);color:#493d00;font-weight:800;border:0;border-radius:8px;padding:12px 25px}.btn-save:hover{background:#ffd633}
.btn-secondary-custom{background:#eef1ef;color:#374151;font-weight:700;border-radius:8px;padding:12px 20px}
footer{background:#043d24;color:#fff;text-align:center;padding:22px}
@media(max-width:768px){.hero h1{font-size:26px}.form-body{padding:20px}.card-head{padding:20px}.wrap{margin:-25px 12px 40px}.actions{flex-direction:column}.actions a,.actions button{width:100%}}
</style>
</head>
<body>

<nav class="topbar">
<div class="container d-flex justify-content-between align-items-center">
<a href="index.php" class="brand d-flex align-items-center">
<img src="assets/img/logo.png" alt="Logo">
<span>MTs Ulumul Qur'an Al Madani</span>
</a>
<a href="dashboard_siswa.php" class="back"><i class="fas fa-arrow-left mr-1"></i> Dashboard</a>
</div>
</nav>

<section class="hero">
<div class="container">
<h1>Formulir Pendaftaran Peserta Didik Baru</h1>
<p>Lengkapi data calon murid dan data keluarga dengan benar sesuai dokumen resmi.</p>
</div>
</section>

<div class="container wrap">
<div class="card-main">
<div class="card-head">
<h3><i class="fas fa-edit mr-2"></i> Data Pendaftaran</h3>
<p>Kolom bertanda <span class="req">*</span> wajib diisi.</p>
</div>

<div class="form-body">
<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="info"><i class="fas fa-info-circle mr-2"></i>Isi formulir secara bertahap. Alamat ibu akan muncul jika memilih <b>berbeda dengan ayah kandung</b>. Data wali diisi jika murid tidak tinggal dengan orangtua kandung.</div>

<form method="POST">

<div class="section">
<div class="section-title"><span class="icon"><i class="fas fa-school"></i></span>Sekolah Asal</div>
<div class="row">
<div class="col-md-6 form-group"><label>Nama sekolah asal <span class="req">*</span></label><input name="nama_sekolah_asal" class="form-control" required value="<?= old('nama_sekolah_asal',$data) ?>"></div>
<div class="col-md-6 form-group"><label>Status sekolah asal <span class="req">*</span></label><select name="status_sekolah_asal" class="custom-select" required><option value="">Pilih</option><option value="NEGERI" <?= selected('status_sekolah_asal','NEGERI',$data) ?>>Negeri</option><option value="SWASTA" <?= selected('status_sekolah_asal','SWASTA',$data) ?>>Swasta</option></select></div>
<div class="col-md-6 form-group"><label>NPSN / NSM sekolah atau madrasah asal</label><input name="npsn_nsm_asal" class="form-control" value="<?= old('npsn_nsm_asal',$data) ?>"></div>
<div class="col-md-6 form-group"><label>Alamat sekolah asal</label><textarea name="alamat_sekolah_asal" class="form-control"><?= old('alamat_sekolah_asal',$data) ?></textarea></div>
</div></div>

<div class="section">
<div class="section-title"><span class="icon"><i class="fas fa-user"></i></span>Biodata Calon Murid Baru</div>
<div class="row">
<div class="col-md-6 form-group"><label>Nama lengkap <span class="req">*</span></label><input name="nama" class="form-control" required value="<?= old('nama',$data) ?>"></div>
<div class="col-md-3 form-group"><label>NISN <span class="req">*</span></label><input name="nisn" maxlength="10" class="form-control" required value="<?= old('nisn',$data) ?>"></div>
<div class="col-md-3 form-group"><label>NIK <span class="req">*</span></label><input name="nik" maxlength="16" class="form-control" required value="<?= old('nik',$data) ?>"></div>
<div class="col-md-6 form-group"><label>Tempat lahir</label><input name="tempat_lahir" class="form-control" value="<?= old('tempat_lahir',$data) ?>"></div>
<div class="col-md-3 form-group"><label>Tanggal lahir</label><input type="date" name="tanggal_lahir" class="form-control" value="<?= old('tanggal_lahir',$data) ?>"></div>
<div class="col-md-3 form-group"><label>Jenis kelamin <span class="req">*</span></label><select name="jk" class="custom-select" required><option value="">Pilih</option><option value="L" <?= selected('jk','L',$data) ?>>Laki-laki</option><option value="P" <?= selected('jk','P',$data) ?>>Perempuan</option></select></div>
<div class="col-md-3 form-group"><label>Jumlah saudara</label><input type="number" min="0" name="jumlah_saudara" class="form-control" value="<?= old('jumlah_saudara',$data) ?>"></div>
<div class="col-md-3 form-group"><label>Anak ke</label><input type="number" min="1" name="anak_ke" class="form-control" value="<?= old('anak_ke',$data) ?>"></div>
<div class="col-md-6 form-group"><label>Cita-cita</label><select name="cita_cita" class="custom-select"><option value="">Pilih cita-cita</option><?php foreach(['PNS','TNI','Polri','Guru','Dosen','Dokter','Politikus','Wiraswasta','Seniman / Artis','Ilmuan','Agamawan','Lainnya'] as $v): ?><option value="<?= $v ?>" <?= selected('cita_cita',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-6 form-group"><label>Hobi</label><select name="hobi" class="custom-select"><option value="">Pilih hobi</option><?php foreach(['Olahraga','Kesenian','Membaca','Menulis','Jalan-jalan','Lainnya'] as $v): ?><option value="<?= $v ?>" <?= selected('hobi',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-6 form-group"><label>Nomor telepon / WhatsApp</label><input name="telepon" class="form-control" value="<?= old('telepon',$data) ?>"></div>
<div class="col-md-6 form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?= old('email',$data) ?>"></div>
<div class="col-md-6 form-group"><label>Yang membiayai sekolah</label><select name="pembiaya_sekolah" class="custom-select"><option value="">Pilih</option><option value="ORANGTUA" <?= selected('pembiaya_sekolah','ORANGTUA',$data) ?>>Orangtua</option><option value="WALI / ORANGTUA ASUH" <?= selected('pembiaya_sekolah','WALI / ORANGTUA ASUH',$data) ?>>Wali / orangtua asuh</option><option value="LAINNYA" <?= selected('pembiaya_sekolah','LAINNYA',$data) ?>>Lainnya</option></select></div>
<div class="col-md-6 form-group"><label>Pra sekolah</label><select name="pra_sekolah" class="custom-select"><option value="">Pilih</option><option value="PERNAH TK / RA" <?= selected('pra_sekolah','PERNAH TK / RA',$data) ?>>Pernah TK / RA</option><option value="PERNAH PAUD" <?= selected('pra_sekolah','PERNAH PAUD',$data) ?>>Pernah PAUD</option></select></div>
<div class="col-md-6 form-group"><label>Imunisasi</label><select name="imunisasi" class="custom-select"><option value="">Pilih</option><?php foreach(['Hepatitis','BCG','DPT','Polio','Campak','COVID','Lengkap'] as $v): ?><option value="<?= $v ?>" <?= selected('imunisasi',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-3 form-group"><label>Nomor kartu keluarga</label><input name="no_kk" maxlength="16" class="form-control" value="<?= old('no_kk',$data) ?>"></div>
<div class="col-md-3 form-group"><label>Nama kepala keluarga</label><input name="nama_kepala_keluarga" class="form-control" value="<?= old('nama_kepala_keluarga',$data) ?>"></div>
</div></div>

<div class="section">
<div class="section-title"><span class="icon"><i class="fas fa-users"></i></span>Data Orangtua Kandung Murid Baru</div>
<h5 class="text-success font-weight-bold mb-3">Data Ayah</h5>
<div class="row">
<div class="col-md-6 form-group"><label>Nama ayah</label><input name="nama_ayah" class="form-control" value="<?= old('nama_ayah',$data) ?>"></div>
<div class="col-md-3 form-group"><label>Status</label><select name="status_ayah" class="custom-select"><option value="">Pilih</option><?php foreach(['MASIH HIDUP','SUDAH MENINGGAL','TIDAK DIKETAHUI'] as $v): ?><option value="<?= $v ?>" <?= selected('status_ayah',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-3 form-group"><label>NIK</label><input name="nik_ayah" maxlength="16" class="form-control" value="<?= old('nik_ayah',$data) ?>"></div>
<div class="col-md-3 form-group"><label>Tanggal lahir</label><input type="date" name="tanggal_lahir_ayah" class="form-control" value="<?= old('tanggal_lahir_ayah',$data) ?>"></div>
<div class="col-md-4 form-group"><label>Pendidikan terakhir</label><select name="pendidikan_ayah" class="custom-select"><option value="">Pilih</option><?php foreach($edu as $v): ?><option value="<?= $v ?>" <?= selected('pendidikan_ayah',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-5 form-group"><label>Pekerjaan utama</label><select name="pekerjaan_ayah" class="custom-select"><option value="">Pilih</option><?php foreach($jobs as $v): ?><option value="<?= $v ?>" <?= selected('pekerjaan_ayah',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-7 form-group"><label>Penghasilan rata-rata</label><select name="penghasilan_ayah" class="custom-select"><option value="">Pilih</option><?php foreach($income as $v): ?><option value="<?= $v ?>" <?= selected('penghasilan_ayah',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-5 form-group"><label>Nomor HP / WhatsApp</label><input name="hp_ayah" class="form-control" value="<?= old('hp_ayah',$data) ?>"></div>
</div>

<h5 class="text-success font-weight-bold mt-4 mb-3">Data Ibu</h5>
<div class="row">
<div class="col-md-6 form-group"><label>Nama ibu murid</label><input name="nama_ibu" class="form-control" value="<?= old('nama_ibu',$data) ?>"></div>
<div class="col-md-3 form-group"><label>Status</label><select name="status_ibu" class="custom-select"><option value="">Pilih</option><?php foreach(['MASIH HIDUP','SUDAH MENINGGAL','TIDAK DIKETAHUI'] as $v): ?><option value="<?= $v ?>" <?= selected('status_ibu',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-3 form-group"><label>NIK</label><input name="nik_ibu" maxlength="16" class="form-control" value="<?= old('nik_ibu',$data) ?>"></div>
<div class="col-md-3 form-group"><label>Tanggal lahir</label><input type="date" name="tanggal_lahir_ibu" class="form-control" value="<?= old('tanggal_lahir_ibu',$data) ?>"></div>
<div class="col-md-4 form-group"><label>Pendidikan terakhir</label><select name="pendidikan_ibu" class="custom-select"><option value="">Pilih</option><?php foreach($edu as $v): ?><option value="<?= $v ?>" <?= selected('pendidikan_ibu',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-5 form-group"><label>Pekerjaan utama</label><select name="pekerjaan_ibu" class="custom-select"><option value="">Pilih</option><?php foreach($jobs as $v): ?><option value="<?= $v ?>" <?= selected('pekerjaan_ibu',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-7 form-group"><label>Penghasilan rata-rata</label><select name="penghasilan_ibu" class="custom-select"><option value="">Pilih</option><?php foreach($income as $v): ?><option value="<?= $v ?>" <?= selected('penghasilan_ibu',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-5 form-group"><label>Nomor HP / WhatsApp</label><input name="hp_ibu" class="form-control" value="<?= old('hp_ibu',$data) ?>"></div>
</div></div>

<div class="section">
<div class="section-title"><span class="icon"><i class="fas fa-home"></i></span>Data Alamat</div>
<h5 class="text-success font-weight-bold mb-3">Alamat Ayah Kandung</h5>
<div class="row">
<div class="col-md-4 form-group"><label>Status kepemilikan rumah</label><select name="kepemilikan_rumah_ayah" class="custom-select"><option value="">Pilih</option><?php foreach($homeOwnership as $v): ?><option value="<?= $v ?>" <?= selected('kepemilikan_rumah_ayah',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-4 form-group"><label>Provinsi</label><input name="provinsi_ayah" class="form-control" value="<?= old('provinsi_ayah',$data) ?>"></div>
<div class="col-md-4 form-group"><label>Kabupaten / Kota</label><input name="kabupaten_ayah" class="form-control" value="<?= old('kabupaten_ayah',$data) ?>"></div>
<div class="col-md-4 form-group"><label>Kecamatan</label><input name="kecamatan_ayah" class="form-control" value="<?= old('kecamatan_ayah',$data) ?>"></div>
<div class="col-md-4 form-group"><label>Kelurahan / Desa</label><input name="kelurahan_ayah" class="form-control" value="<?= old('kelurahan_ayah',$data) ?>"></div>
<div class="col-md-2 form-group"><label>RT</label><input name="rt_ayah" class="form-control" value="<?= old('rt_ayah',$data) ?>"></div>
<div class="col-md-2 form-group"><label>RW</label><input name="rw_ayah" class="form-control" value="<?= old('rw_ayah',$data) ?>"></div>
<div class="col-md-9 form-group"><label>Nama jalan / komplek / no. rumah</label><input name="jalan_ayah" class="form-control" value="<?= old('jalan_ayah',$data) ?>"></div>
<div class="col-md-3 form-group"><label>Kode pos</label><input name="kode_pos_ayah" class="form-control" value="<?= old('kode_pos_ayah',$data) ?>"></div>
</div>

<h5 class="text-success font-weight-bold mt-4 mb-3">Alamat Ibu Kandung</h5>
<div class="form-group">
<label>Alamat ibu kandung</label>
<select name="alamat_ibu_status" id="alamatIbuStatus" class="custom-select">
<option value="">Pilih</option>
<option value="SAMA_DENGAN_AYAH" <?= selected('alamat_ibu_status','SAMA_DENGAN_AYAH',$data) ?>>Sama dengan ayah kandung</option>
<option value="BERBEDA_DENGAN_AYAH" <?= selected('alamat_ibu_status','BERBEDA_DENGAN_AYAH',$data) ?>>Berbeda dengan ayah kandung</option>
</select>
</div>
<div id="alamatIbuBox" class="conditional">
<div class="row">
<div class="col-md-4 form-group"><label>Status kepemilikan rumah</label><select name="kepemilikan_rumah_ibu" class="custom-select"><option value="">Pilih</option><?php foreach($homeOwnership as $v): ?><option value="<?= $v ?>" <?= selected('kepemilikan_rumah_ibu',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-4 form-group"><label>Provinsi</label><input name="provinsi_ibu" class="form-control" value="<?= old('provinsi_ibu',$data) ?>"></div>
<div class="col-md-4 form-group"><label>Kabupaten / Kota</label><input name="kabupaten_ibu" class="form-control" value="<?= old('kabupaten_ibu',$data) ?>"></div>
<div class="col-md-4 form-group"><label>Kecamatan</label><input name="kecamatan_ibu" class="form-control" value="<?= old('kecamatan_ibu',$data) ?>"></div>
<div class="col-md-4 form-group"><label>Kelurahan / Desa</label><input name="kelurahan_ibu" class="form-control" value="<?= old('kelurahan_ibu',$data) ?>"></div>
<div class="col-md-2 form-group"><label>RT</label><input name="rt_ibu" class="form-control" value="<?= old('rt_ibu',$data) ?>"></div>
<div class="col-md-2 form-group"><label>RW</label><input name="rw_ibu" class="form-control" value="<?= old('rw_ibu',$data) ?>"></div>
<div class="col-md-9 form-group"><label>Nama jalan / komplek / no. rumah</label><input name="jalan_ibu" class="form-control" value="<?= old('jalan_ibu',$data) ?>"></div>
<div class="col-md-3 form-group"><label>Kode pos</label><input name="kode_pos_ibu" class="form-control" value="<?= old('kode_pos_ibu',$data) ?>"></div>
</div></div>
</div>

<div class="section">
<div class="section-title"><span class="icon"><i class="fas fa-route"></i></span>Domisili dan Kondisi Murid</div>
<div class="row">
<div class="col-md-6 form-group"><label>Domisili murid</label><select name="domisili_murid" id="domisiliMurid" class="custom-select"><option value="">Pilih</option><?php foreach(['TINGGAL DENGAN ORANG TUA','IKUT SAUDARA / KERABAT','KONTRAK / KOST','PANTI ASUHAN'] as $v): ?><option value="<?= $v ?>" <?= selected('domisili_murid',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-6 form-group"><label>Transportasi ke sekolah</label><select name="transportasi" class="custom-select"><option value="">Pilih</option><?php foreach(['JALAN KAKI','SEPEDA / SEPEDA LISTRIK','SEPEDA MOTOR','MOBIL PRIBADI','ANGKUTAN UMUM','OJEK'] as $v): ?><option value="<?= $v ?>" <?= selected('transportasi',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-4 form-group"><label>Jarak rumah ke sekolah</label><select name="jarak_rumah" class="custom-select"><option value="">Pilih</option><?php foreach(['KURANG 5 KM','ANTARA 5 – 10 KM','ANTARA 11 – 20 KM','ANTARA 21 – 30 KM','LEBIH DARI 30 KM'] as $v): ?><option value="<?= $v ?>" <?= selected('jarak_rumah',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-4 form-group"><label>Waktu tempuh</label><select name="waktu_tempuh" class="custom-select"><option value="">Pilih</option><?php foreach(['1 - 10 Menit','10 - 19 Menit','20 - 29 Menit','30 - 39 Menit','1 - 2 Jam','Lebih dari 2 Jam'] as $v): ?><option value="<?= $v ?>" <?= selected('waktu_tempuh',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-4 form-group"><label>Kebutuhan khusus</label><select name="kebutuhan_khusus" class="custom-select"><option value="">Pilih</option><?php foreach(['TIDAK ADA','LAMBAN BELAJAR','KESULITAN BELAJAR SPESIFIK','GANGGUAN KOMUNIKASI','BERBAKAT / MEMILIKI KEMAMPUAN & KECERDASAN LUAR BIASA','LAINNYA'] as $v): ?><option value="<?= $v ?>" <?= selected('kebutuhan_khusus',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-12 form-group"><label>Kebutuhan disabilitas</label><select name="kebutuhan_disabilitas" class="custom-select"><option value="">Pilih</option><?php foreach(['TIDAK ADA','TUNA NETRA','TUNA RUNGU','TUNA DAKSA','TUNA GRAHITA','TUNA LARAS','TUNA WICARA'] as $v): ?><option value="<?= $v ?>" <?= selected('kebutuhan_disabilitas',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
</div></div>

<div class="section" id="waliSection">
<div class="section-title"><span class="icon"><i class="fas fa-user-shield"></i></span>Wali Siswa</div>
<div class="info">Bagian ini diisi jika murid tidak tinggal dengan orangtua kandung.</div>
<div class="row">
<div class="col-md-6 form-group"><label>Status wali murid</label><select name="status_wali" class="custom-select"><option value="">Pilih</option><?php foreach(['KELUARGA PIHAK AYAH','KELUARGA PIHAK IBU','PENGASUH'] as $v): ?><option value="<?= $v ?>" <?= selected('status_wali',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-6 form-group"><label>Nama wali</label><input name="nama_wali" class="form-control" value="<?= old('nama_wali',$data) ?>"></div>
<div class="col-md-6 form-group"><label>Nomor HP / WhatsApp</label><input name="hp_wali" class="form-control" value="<?= old('hp_wali',$data) ?>"></div>
<div class="col-md-6 form-group"><label>Jika siswa tidak tinggal bersama orangtua kandung</label><select name="tinggal_bersama" class="custom-select"><option value="">Pilih</option><?php foreach(['TINGGAL DENGAN WALI','IKUT SAUDARA / KERABAT','ASRAMA','KONTRAKAN / KOST','PANTI ASUHAN'] as $v): ?><option value="<?= $v ?>" <?= selected('tinggal_bersama',$v,$data) ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-12 form-group"><label>Alamat lengkap</label><textarea name="alamat_wali" class="form-control"><?= old('alamat_wali',$data) ?></textarea></div>
</div></div>

<div class="actions">
<a href="dashboard_siswa.php" class="btn btn-secondary-custom"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
<button type="submit" name="simpan" class="btn btn-save"><i class="fas fa-save mr-1"></i> Simpan Data Pendaftaran</button>
</div>
</form>
</div></div></div>
</div>

<footer>© <?= date('Y') ?> MTs Ulumul Qur'an Al Madani. Semua Hak Dilindungi.</footer>

<script>
function toggleAlamatIbu(){
    const v=document.getElementById('alamatIbuStatus').value;
    document.getElementById('alamatIbuBox').style.display=(v==='BERBEDA_DENGAN_AYAH')?'block':'none';
}
function toggleWali(){
    const v=document.getElementById('domisiliMurid').value;
    document.getElementById('waliSection').style.display=(v && v!=='TINGGAL DENGAN ORANG TUA')?'block':'none';
}
document.getElementById('alamatIbuStatus').addEventListener('change',toggleAlamatIbu);
document.getElementById('domisiliMurid').addEventListener('change',toggleWali);
toggleAlamatIbu(); toggleWali();
</script>
</body>
</html>
<?php
session_start();
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($nama === '' || $email === '' || $username === '' || $password === '' || $password2 === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $password2) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        if (!$stmt) {
            $error = 'Terjadi kesalahan pada database.';
        } else {
            mysqli_stmt_bind_param($stmt, "ss", $username, $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $exists = $result && mysqli_num_rows($result) > 0;
            mysqli_stmt_close($stmt);

            if ($exists) {
                $error = 'Username atau email sudah terdaftar.';
            } else {
                $role = 'siswa';
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, email, username, password, role) VALUES (?, ?, ?, ?, ?)");
                if (!$stmt) {
                    $error = 'Terjadi kesalahan saat membuat akun.';
                } else {
                    mysqli_stmt_bind_param($stmt, "sssss", $nama, $email, $username, $hash, $role);
                    if (mysqli_stmt_execute($stmt)) {
                        $success = 'Akun berhasil dibuat. Silakan login menggunakan akun kamu.';
                    } else {
                        $error = 'Pendaftaran akun gagal. Silakan coba lagi.';
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}

function old($name) {
    return htmlspecialchars($_POST[$name] ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Akun - MTs Ulumul Qur'an Al Madani</title>
<link rel="stylesheet" href="../assets/vendor/fontawesome-free/css/all.min.css">
<style>
:root{--gd:#075e35;--g:#0b7a45;--gl:#edf8f1;--y:#f5c400;--yh:#ffd633}
*{box-sizing:border-box}
body{margin:0;min-height:100vh;font-family:Arial,Helvetica,sans-serif;background:linear-gradient(135deg,#eaf7ef,#fffbea)}
.page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:30px 20px}
.box{width:100%;max-width:1000px;display:grid;grid-template-columns:42% 58%;background:#fff;border-radius:22px;overflow:hidden;box-shadow:0 18px 55px rgba(0,0,0,.12)}
.left{position:relative;overflow:hidden;padding:45px;color:#fff;background:linear-gradient(145deg,var(--gd),var(--g));display:flex;flex-direction:column;justify-content:center}
.left:after{content:"";position:absolute;width:240px;height:240px;right:-110px;bottom:-100px;border-radius:50%;background:rgba(245,196,0,.17)}
.logo{width:88px;height:88px;object-fit:contain;background:#fff;border-radius:50%;padding:7px;margin-bottom:22px;z-index:1}
.left h1{z-index:1;margin:0 0 15px;font-size:29px;line-height:1.2}
.left p{z-index:1;margin:0;line-height:1.7;color:#e8f5ed;font-size:14px}
.badge{z-index:1;display:inline-block;width:max-content;margin-top:25px;padding:8px 14px;border-radius:20px;background:var(--y);color:#4b4000;font-size:11px;font-weight:bold}
.right{padding:40px 48px}
.title{color:var(--gd);margin:0;font-size:25px}.subtitle{margin:7px 0 24px;color:#718078;font-size:13px}
.alert{padding:12px 14px;border-radius:9px;margin-bottom:17px;font-size:13px}.danger{color:#a52b2b;background:#fdecec}.success{color:#176b3e;background:#eaf8ef}
.group{margin-bottom:15px}.group label{display:block;margin-bottom:7px;color:#42534a;font-size:12px;font-weight:bold}
.input{position:relative}.input i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--g)}
.input input{width:100%;height:43px;padding:10px 14px 10px 40px;border:1px solid #d8e2dc;border-radius:9px;outline:none;font-size:13px}
.input input:focus{border-color:var(--g);box-shadow:0 0 0 3px rgba(11,122,69,.1)}
.btn{width:100%;border:0;border-radius:9px;padding:13px;margin-top:3px;background:var(--y);color:#4b4000;font-size:14px;font-weight:bold;cursor:pointer}.btn:hover{background:var(--yh)}
.links{text-align:center;margin-top:19px;font-size:12px;color:#718078}.links a{color:var(--gd);font-weight:bold;text-decoration:none}.links a:hover{text-decoration:underline}
.note{margin-top:17px;padding:11px 13px;border-left:4px solid var(--y);border-radius:7px;background:var(--gl);color:#53645a;font-size:11px;line-height:1.5}
@media(max-width:800px){.box{grid-template-columns:1fr;max-width:550px}.left{padding:30px}.left h1{font-size:24px}.logo{width:70px;height:70px}.right{padding:30px 25px}}
</style>
</head>
<body>
<div class="page">
<div class="box">
<div class="left">
<img src="../assets/img/logo.png" alt="Logo Sekolah" class="logo">
<h1>MTs Ulumul Qur'an<br>Al Madani</h1>
<p>Buat akun untuk melanjutkan proses Penerimaan Murid Baru Madrasah (PMBM). Pastikan data yang digunakan benar.</p>
<span class="badge">PMBM TAHUN AJARAN BARU</span>
</div>
<div class="right">
<h2 class="title"><i class="fas fa-user-plus"></i> Daftar Akun</h2>
<p class="subtitle">Silakan isi data berikut untuk membuat akun calon murid.</p>

<?php if ($error !== ''): ?>
<div class="alert danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($success !== ''): ?>
<div class="alert success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<div class="links"><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login Sekarang</a></div>
<?php else: ?>
<form method="POST" action="" autocomplete="off">
<div class="group"><label>Nama Lengkap</label><div class="input"><i class="fas fa-user"></i><input type="text" name="nama" value="<?= old('nama') ?>" placeholder="Masukkan nama lengkap" required></div></div>
<div class="group"><label>Email</label><div class="input"><i class="fas fa-envelope"></i><input type="email" name="email" value="<?= old('email') ?>" placeholder="contoh@email.com" required></div></div>
<div class="group"><label>Username</label><div class="input"><i class="fas fa-at"></i><input type="text" name="username" value="<?= old('username') ?>" placeholder="Buat username" required></div></div>
<div class="group"><label>Password</label><div class="input"><i class="fas fa-lock"></i><input type="password" name="password" placeholder="Minimal 6 karakter" required></div></div>
<div class="group"><label>Konfirmasi Password</label><div class="input"><i class="fas fa-lock"></i><input type="password" name="password2" placeholder="Ulangi password" required></div></div>
<button type="submit" class="btn"><i class="fas fa-user-plus"></i> Buat Akun</button>
</form>
<?php endif; ?>

<div class="links">
<?php if ($success === ''): ?>Sudah memiliki akun? <a href="login.php">Login di sini</a> &nbsp;•&nbsp;<?php endif; ?>
<a href="../index.php">Kembali ke Beranda</a>
</div>
<div class="note"><i class="fas fa-info-circle"></i> Setelah akun berhasil dibuat, silakan login kemudian lengkapi formulir pendaftaran PMBM.</div>
</div>
</div>
</div>
</body>
</html>

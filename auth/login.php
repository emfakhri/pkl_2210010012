<?php
session_start();
require_once '../config/database.php';

/* ===============================
   JIKA SUDAH LOGIN, REDIRECT
================================ */
if (isset($_SESSION['login'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: ../dashboard_admin.php");
    } else {
        header("Location: ../dashboard_siswa.php");
    }
    exit;
}

$error = '';

if (isset($_POST['login'])) {

    $username = trim($_POST['username']); // NISN / username admin
    $password = $_POST['password'];

    /* ===============================
       CEK USER
    ================================ */
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    $data  = mysqli_fetch_assoc($query);

    if ($data) {

        /* ===============================
           VERIFIKASI PASSWORD (AMAN)
        ================================ */
        if (password_verify($password, $data['password'])) {

            $_SESSION['login']    = true;
            $_SESSION['user_id']  = $data['id'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['role']     = $data['role'];

            /* ===============================
               REDIRECT SESUAI ROLE
            ================================ */
            if ($data['role'] == 'admin') {
                header("Location: ../dashboard_admin.php");
            } else {
                header("Location: ../dashboard_siswa.php");
            }
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username / NISN tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login PMBM - MTs Ulumul Qur'an Al Madani</title>
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <style>
        :root{
            --green-dark:#075e35;
            --green:#0b7a45;
            --green-soft:#eaf7ef;
            --yellow:#f5c400;
            --yellow-light:#fff7c7;
            --text:#263238;
        }
        *{box-sizing:border-box}
        body{
            min-height:100vh;
            margin:0;
            font-family:Arial,Helvetica,sans-serif;
            background:linear-gradient(135deg,var(--green-dark),var(--green));
            display:flex;
            align-items:center;
            justify-content:center;
            padding:25px 15px;
            position:relative;
            overflow:hidden;
        }
        body:before,body:after{
            content:"";position:absolute;border-radius:50%;background:rgba(245,196,0,.14);
        }
        body:before{width:330px;height:330px;top:-130px;left:-100px}
        body:after{width:430px;height:430px;bottom:-220px;right:-140px}
        .login-wrap{width:100%;max-width:900px;position:relative;z-index:1}
        .login-card{
            background:#fff;border:0;border-radius:22px;overflow:hidden;
            box-shadow:0 20px 55px rgba(0,0,0,.22);
        }
        .brand-panel{
            min-height:510px;background:linear-gradient(160deg,var(--green-dark),var(--green));
            color:#fff;padding:45px 35px;display:flex;align-items:center;justify-content:center;text-align:center;
        }
        .logo{
            width:105px;height:105px;object-fit:contain;background:#fff;border-radius:50%;padding:7px;
            box-shadow:0 8px 20px rgba(0,0,0,.16);margin-bottom:20px;
        }
        .brand-panel h1{font-size:27px;font-weight:800;line-height:1.25;margin-bottom:12px}
        .brand-panel p{opacity:.9;line-height:1.6;margin-bottom:20px}
        .yellow-line{width:65px;height:5px;background:var(--yellow);border-radius:10px;margin:0 auto 20px}
        .login-panel{padding:45px 40px}
        .login-panel h2{font-size:28px;font-weight:800;color:var(--green-dark);margin-bottom:5px}
        .subtitle{color:#718096;margin-bottom:28px}
        .form-label{font-weight:700;color:var(--text);font-size:14px}
        .input-group-text{background:var(--green-soft);border:1px solid #dce9e1;color:var(--green-dark)}
        .form-control{height:48px;border-color:#dce5df}
        .form-control:focus{border-color:var(--green);box-shadow:0 0 0 .15rem rgba(11,122,69,.12)}
        .btn-login{height:48px;border:0;background:var(--yellow);color:#493d00;font-weight:800;border-radius:9px;width:100%;transition:.2s}
        .btn-login:hover{background:#ffd633;color:#493d00;transform:translateY(-1px)}
        .links a{color:var(--green-dark);font-weight:600;text-decoration:none}
        .links a:hover{color:var(--green);text-decoration:underline}
        .alert{border-radius:10px}
        .back{display:inline-flex;align-items:center;gap:6px;margin-top:18px}
        @media(max-width:767px){
            .brand-panel{min-height:auto;padding:30px 25px}.brand-panel h1{font-size:22px}.logo{width:80px;height:80px}
            .login-panel{padding:32px 25px}.login-card{border-radius:16px}
        }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="row no-gutters">
            <div class="col-md-6">
                <div class="brand-panel">
                    <div>
                        <img src="../assets/img/logo.png" class="logo" alt="Logo sekolah">
                        <div class="yellow-line"></div>
                        <h1>MTs Ulumul Qur'an<br>Al Madani</h1>
                        <p>Penerimaan Murid Baru Madrasah</p>
                        <span class="badge" style="background:var(--yellow);color:#493d00;padding:9px 15px;border-radius:20px;">PMBM</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="login-panel">
                    <h2>Selamat Datang</h2>
                    <p class="subtitle">Silakan masuk untuk mengakses sistem PMBM.</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle mr-1"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group mb-3">
                            <label class="form-label">Username / NISN</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                                <input type="text" name="username" class="form-control" placeholder="Masukkan username atau NISN" required autocomplete="username">
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-lock"></i></span></div>
                                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required autocomplete="current-password">
                            </div>
                        </div>

                        <button type="submit" name="login" class="btn-login">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </button>
                    </form>

                    <div class="links text-center mt-4">
                        <div><a href="register.php">Belum punya akun? Daftar</a></div>
                        <a href="../index.php" class="back"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center text-white mt-3 small"> <?= date('Y') ?> MTs Ulumul Qur'an Al Madani</div>
</div>
</body>
</html>
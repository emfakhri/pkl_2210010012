<?php
session_start();
include '../config/db.php';

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
    <title>Login PPDB</title>
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-primary">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">

                <div class="card shadow">
                    <div class="card-body">
                        <h4 class="text-center mb-4">Login PPDB</h4>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">

                            <div class="mb-3">
                                <label>Username / NISN</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <button name="login" class="btn btn-primary w-100">
                                Login
                            </button>

                            <div class="text-center mt-3">
                                <a href="register.php">Belum punya akun? Daftar</a>
                            </div>

                            <div class="text-center mt-2">
                                <a href="../index.php">Kembali ke Beranda</a>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>
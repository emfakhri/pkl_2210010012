<?php
session_start();
include '../config/db.php';

/* ===============================
   JIKA SUDAH LOGIN, REDIRECT
================================ */
if (isset($_SESSION['login'])) {
    header("Location: ../dashboard_siswa.php");
    exit;
}

$error = '';

if (isset($_POST['register'])) {

    $nisn = trim($_POST['nisn']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    /* ===============================
       VALIDASI NISN
    ================================ */
    if (!preg_match('/^[0-9]{10}$/', $nisn)) {
        $error = "NISN harus 10 digit angka!";
    }

    /* ===============================
       VALIDASI PASSWORD
    ================================ */ elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password !== $password2) {
        $error = "Konfirmasi password tidak sama!";
    } else {
        /* ===============================
           CEK DUPLIKASI NISN
        ================================ */
        $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$nisn'");

        if (mysqli_num_rows($cek) > 0) {
            $error = "NISN sudah terdaftar!";
        } else {

            /* ===============================
               SIMPAN AKUN
            ================================ */
            $hash = password_hash($password, PASSWORD_DEFAULT);

            mysqli_query($conn, "INSERT INTO users (username, password, role)
            VALUES ('$nisn', '$hash', 'siswa')");

            $_SESSION['login'] = true;
            $_SESSION['username'] = $nisn;
            $_SESSION['role'] = 'siswa';
            $_SESSION['user_id'] = mysqli_insert_id($conn);

            header("Location: ../dashboard_siswa.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Registrasi Akun Siswa</title>
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-primary">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">

                <div class="card shadow">
                    <div class="card-body">
                        <h4 class="text-center mb-4">Registrasi Akun Siswa</h4>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST">

                            <div class="mb-3">
                                <label>NISN (10 Digit)</label>
                                <input type="text" name="nisn" class="form-control" maxlength="10" required>
                            </div>

                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label>Konfirmasi Password</label>
                                <input type="password" name="password2" class="form-control" required>
                            </div>

                            <button name="register" class="btn btn-primary w-100">
                                Daftar
                            </button>

                            <div class="text-center mt-3">
                                <a href="login.php">Sudah punya akun? Login</a>
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
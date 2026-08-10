<?php

session_start();

require_once __DIR__ . '/config/db.php';

/*
|--------------------------------------------------------------------------
| CEK LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| DATA USER
|--------------------------------------------------------------------------
*/

$username = $_SESSION['username'] ?? 'Siswa';

/*
|--------------------------------------------------------------------------
| FOLDER UPLOAD
|--------------------------------------------------------------------------
*/

$upload_dir = __DIR__ . '/uploads/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/*
|--------------------------------------------------------------------------
| PESAN
|--------------------------------------------------------------------------
*/

$pesan = "";
$jenis_pesan = "";

/*
|--------------------------------------------------------------------------
| DAFTAR JENIS BERKAS
|--------------------------------------------------------------------------
*/

$daftar_berkas = [
    'kk' => [
        'nama' => 'Kartu Keluarga',
        'icon' => '📄',
        'format' => 'JPG, JPEG, PNG atau PDF',
        'wajib' => true
    ],

    'akta' => [
        'nama' => 'Akta Kelahiran',
        'icon' => '📄',
        'format' => 'JPG, JPEG, PNG atau PDF',
        'wajib' => true
    ],

    'ijazah' => [
        'nama' => 'Ijazah / Surat Keterangan Aktif',
        'icon' => '🎓',
        'format' => 'JPG, JPEG, PNG atau PDF',
        'wajib' => true
    ],

    'foto' => [
        'nama' => 'Pas Foto',
        'icon' => '📷',
        'format' => 'JPG, JPEG atau PNG',
        'wajib' => true
    ],

    'kip' => [
        'nama' => 'KTP Orang Tua',
        'icon' => '💳',
        'format' => 'JPG, JPEG, PNG atau PDF',
        'wajib' => true
    ],

    'lainnya' => [
        'nama' => 'Nomor Induk Siswa Nasional (NISN)',
        'icon' => '📑',
        'format' => 'JPG, JPEG, PNG atau PDF',
        'wajib' => true
    ]
];


/*
|--------------------------------------------------------------------------
| PROSES UPLOAD
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jenis_berkas = $_POST['jenis_berkas'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | CEK JENIS BERKAS
    |--------------------------------------------------------------------------
    */

    if (!isset($daftar_berkas[$jenis_berkas])) {

        $pesan = "Jenis berkas tidak valid.";
        $jenis_pesan = "error";

    } elseif (!isset($_FILES['berkas'])) {

        $pesan = "Silakan pilih file terlebih dahulu.";
        $jenis_pesan = "error";

    } else {

        $file = $_FILES['berkas'];

        /*
        |--------------------------------------------------------------------------
        | CEK ERROR UPLOAD
        |--------------------------------------------------------------------------
        */

        if ($file['error'] !== UPLOAD_ERR_OK) {

            switch ($file['error']) {

                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $pesan = "Ukuran file terlalu besar.";
                    break;

                case UPLOAD_ERR_NO_FILE:
                    $pesan = "Tidak ada file yang dipilih.";
                    break;

                default:
                    $pesan = "Terjadi kesalahan saat mengupload file.";
                    break;
            }

            $jenis_pesan = "error";

        } else {

            $nama_asli = $file['name'];
            $ukuran = $file['size'];
            $tmp_name = $file['tmp_name'];

            /*
            |--------------------------------------------------------------------------
            | EXTENSION
            |--------------------------------------------------------------------------
            */

            $ext = strtolower(
                pathinfo($nama_asli, PATHINFO_EXTENSION)
            );

            /*
            |--------------------------------------------------------------------------
            | FORMAT YANG DIIZINKAN
            |--------------------------------------------------------------------------
            */

            $format_diperbolehkan = [
                'jpg',
                'jpeg',
                'png',
                'pdf'
            ];

            /*
            |--------------------------------------------------------------------------
            | UKURAN MAKSIMAL 5 MB
            |--------------------------------------------------------------------------
            */

            $maksimal = 5 * 1024 * 1024;

            /*
            |--------------------------------------------------------------------------
            | VALIDASI FORMAT
            |--------------------------------------------------------------------------
            */

            if (!in_array($ext, $format_diperbolehkan, true)) {

                $pesan = "Format file tidak diperbolehkan. Gunakan JPG, JPEG, PNG atau PDF.";
                $jenis_pesan = "error";

            /*
            |--------------------------------------------------------------------------
            | VALIDASI UKURAN
            |--------------------------------------------------------------------------
            */

            } elseif ($ukuran > $maksimal) {

                $pesan = "Ukuran file terlalu besar. Maksimal 5 MB.";
                $jenis_pesan = "error";

            } else {

                /*
                |--------------------------------------------------------------------------
                | NAMA FILE AMAN
                |--------------------------------------------------------------------------
                */

                $nama_file =
                    $user_id . '_' .
                    $jenis_berkas . '_' .
                    time() . '.' .
                    $ext;

                $tujuan = $upload_dir . $nama_file;

                /*
                |--------------------------------------------------------------------------
                | PINDAHKAN FILE
                |--------------------------------------------------------------------------
                */

                if (move_uploaded_file($tmp_name, $tujuan)) {

                    $nama_dokumen =
                        $daftar_berkas[$jenis_berkas]['nama'];

                    $pesan =
                        $nama_dokumen .
                        " berhasil diupload.";

                    $jenis_pesan = "success";

                } else {

                    $pesan =
                        "Gagal menyimpan file ke folder uploads.";

                    $jenis_pesan = "error";
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Upload Berkas - PMBM MTs Ulumul Qur'an Al Madani
    </title>


    <style>

        /*
        |--------------------------------------------------------------------------
        | RESET
        |--------------------------------------------------------------------------
        */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        /*
        |--------------------------------------------------------------------------
        | BODY
        |--------------------------------------------------------------------------
        */

        body {
            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f4f8f5;

            color: #333;

            min-height: 100vh;
        }


        /*
        |--------------------------------------------------------------------------
        | NAVBAR
        |--------------------------------------------------------------------------
        */

        .navbar {

            background: #075e35;

            min-height: 70px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 6%;

            box-shadow:
                0 3px 10px
                rgba(0, 0, 0, 0.12);
        }


        /*
        |--------------------------------------------------------------------------
        | LOGO
        |--------------------------------------------------------------------------
        */

        .logo {

            display: flex;

            align-items: center;

            gap: 12px;

            color: white;

            font-weight: bold;
        }


        .logo-icon {

            width: 43px;

            height: 43px;

            background: #f5c400;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            color: #075e35;

            font-size: 20px;
        }


        .logo-text {

            line-height: 1.2;

            font-size: 15px;
        }


        .logo-text small {

            display: block;

            font-size: 11px;

            font-weight: normal;

            opacity: .85;

            margin-top: 3px;
        }


        /*
        |--------------------------------------------------------------------------
        | NAVBAR RIGHT
        |--------------------------------------------------------------------------
        */

        .nav-right {

            display: flex;

            align-items: center;

            gap: 12px;
        }


        .nav-user {

            color: white;

            font-size: 14px;
        }


        .btn-logout {

            background: #f5c400;

            color: #075e35;

            padding: 10px 17px;

            border-radius: 8px;

            text-decoration: none;

            font-weight: bold;

            font-size: 13px;

            transition: .2s;
        }


        .btn-logout:hover {

            background: #ffd92b;

            transform: translateY(-1px);
        }


        /*
        |--------------------------------------------------------------------------
        | CONTAINER
        |--------------------------------------------------------------------------
        */

        .container {

            width: 92%;

            max-width: 1050px;

            margin: 35px auto 60px;
        }


        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        .page-header {

            background:
                linear-gradient(
                    135deg,
                    #075e35,
                    #0b7a45
                );

            color: white;

            border-radius: 18px;

            padding: 30px;

            margin-bottom: 25px;

            box-shadow:
                0 8px 25px
                rgba(7, 94, 53, .15);
        }


        .page-header h1 {

            font-size: 27px;

            margin-bottom: 8px;
        }


        .page-header p {

            font-size: 14px;

            opacity: .9;

            line-height: 1.6;
        }


        /*
        |--------------------------------------------------------------------------
        | ALERT
        |--------------------------------------------------------------------------
        */

        .alert {

            padding: 15px 18px;

            border-radius: 10px;

            margin-bottom: 20px;

            font-size: 14px;

            line-height: 1.5;
        }


        .alert.success {

            background: #e7f7ed;

            color: #146b3a;

            border-left:
                5px solid #198754;
        }


        .alert.error {

            background: #fdecec;

            color: #a32929;

            border-left:
                5px solid #dc3545;
        }


        /*
        |--------------------------------------------------------------------------
        | INFO BOX
        |--------------------------------------------------------------------------
        */

        .info-box {

            background: #fff9dc;

            border-left:
                5px solid #f5c400;

            padding: 18px;

            border-radius: 10px;

            margin-bottom: 25px;
        }


        .info-box strong {

            color: #075e35;

            font-size: 14px;
        }


        .info-box p {

            margin-top: 7px;

            font-size: 13px;

            line-height: 1.7;
        }


        /*
        |--------------------------------------------------------------------------
        | CARD
        |--------------------------------------------------------------------------
        */

        .upload-card {

            background: white;

            border-radius: 16px;

            padding: 25px;

            margin-bottom: 20px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, .07);

            border:
                1px solid #e5eee8;
        }


        /*
        |--------------------------------------------------------------------------
        | CARD TITLE
        |--------------------------------------------------------------------------
        */

        .card-title {

            display: flex;

            align-items: center;

            gap: 12px;

            margin-bottom: 20px;
        }


        .card-icon {

            width: 45px;

            height: 45px;

            background: #eaf6ef;

            color: #075e35;

            border-radius: 10px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 20px;

            flex-shrink: 0;
        }


        .card-title h2 {

            color: #075e35;

            font-size: 18px;

            margin-bottom: 3px;
        }


        .card-title p {

            color: #777;

            font-size: 12px;

            line-height: 1.5;
        }


        /*
        |--------------------------------------------------------------------------
        | DAFTAR DOKUMEN
        |--------------------------------------------------------------------------
        */

        .required-list {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 14px;
        }


        .required-item {

            display: flex;

            align-items: center;

            gap: 13px;

            background: #f8fbf9;

            border:
                1px solid #e1ebe5;

            padding: 15px;

            border-radius: 11px;

            transition: .2s;
        }


        .required-item:hover {

            border-color: #b8d8c4;

            transform: translateY(-1px);
        }


        .document-number {

            min-width: 35px;

            width: 35px;

            height: 35px;

            border-radius: 50%;

            background: #f5c400;

            color: #075e35;

            display: flex;

            align-items: center;

            justify-content: center;

            font-weight: bold;

            font-size: 14px;
        }


        .required-item strong {

            display: block;

            color: #075e35;

            font-size: 14px;

            line-height: 1.4;
        }


        .required-item span {

            display: block;

            color: #777;

            font-size: 12px;

            margin-top: 4px;

            line-height: 1.5;
        }


        /*
        |--------------------------------------------------------------------------
        | UPLOAD ITEM
        |--------------------------------------------------------------------------
        */

        .upload-item {

            border:
                1px solid #e1ebe5;

            border-radius: 12px;

            padding: 18px;

            margin-bottom: 15px;

            background: #fbfdfc;

            transition: .2s;
        }


        .upload-item:last-child {

            margin-bottom: 0;
        }


        .upload-item:hover {

            border-color: #b8d8c4;

            box-shadow:
                0 3px 10px
                rgba(7, 94, 53, .05);
        }


        /*
        |--------------------------------------------------------------------------
        | UPLOAD INFO
        |--------------------------------------------------------------------------
        */

        .upload-info {

            display: flex;

            align-items: center;

            gap: 13px;

            margin-bottom: 15px;
        }


        .upload-icon {

            width: 43px;

            height: 43px;

            min-width: 43px;

            background: #eaf6ef;

            color: #075e35;

            border-radius: 10px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 19px;
        }


        .upload-info h3 {

            color: #075e35;

            font-size: 15px;

            margin-bottom: 4px;

            line-height: 1.4;
        }


        .upload-info p {

            color: #777;

            font-size: 12px;

            line-height: 1.5;
        }


        /*
        |--------------------------------------------------------------------------
        | FORM UPLOAD
        |--------------------------------------------------------------------------
        */

        .upload-form {

            display: flex;

            align-items: center;

            gap: 10px;
        }


        .upload-form input[type="file"] {

            flex: 1;

            width: 100%;

            background: white;

            padding: 10px;

            border:
                1px solid #d8e2dc;

            border-radius: 8px;

            font-size: 13px;
        }


        .upload-form input[type="file"]:focus {

            outline: none;

            border-color: #075e35;

            box-shadow:
                0 0 0 3px
                rgba(7, 94, 53, .08);
        }


        /*
        |--------------------------------------------------------------------------
        | BUTTON UPLOAD
        |--------------------------------------------------------------------------
        */

        .btn-upload {

            width: auto;

            min-width: 100px;

            border: none;

            padding: 11px 18px;

            border-radius: 8px;

            background: #f5c400;

            color: #075e35;

            font-weight: bold;

            cursor: pointer;

            font-size: 13px;

            transition: .2s;

            white-space: nowrap;
        }


        .btn-upload:hover {

            background: #ffd92b;

            transform: translateY(-1px);
        }


        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .footer {

            text-align: center;

            padding: 25px;

            color: #777;

            font-size: 12px;
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 700px) {

            .navbar {

                padding: 0 4%;
            }


            .logo-text {

                font-size: 13px;
            }


            .logo-text small {

                font-size: 9px;
            }


            .nav-user {

                display: none;
            }


            .container {

                width: 94%;

                margin-top: 20px;
            }


            .page-header {

                padding: 23px;
            }


            .page-header h1 {

                font-size: 22px;
            }


            .required-list {

                grid-template-columns: 1fr;
            }


            .upload-form {

                flex-direction: column;

                align-items: stretch;
            }


            .upload-form input[type="file"] {

                width: 100%;
            }


            .btn-upload {

                width: 100%;
            }
        }

    </style>

</head>


<body>


<!--
|--------------------------------------------------------------------------
| NAVBAR
|--------------------------------------------------------------------------
-->

<nav class="navbar">


    <div class="logo">

        <div class="logo-icon">
            🏫
        </div>


        <div class="logo-text">

            PMBM MTs Ulumul Qur'an Al Madani

            <small>
                Penerimaan Murid Baru Madrasah
            </small>

        </div>

    </div>


    <div class="nav-right">

        <div class="nav-user">

            👤
            <?= htmlspecialchars($username) ?>

        </div>


        <a
            href="logout.php"
            class="btn-logout"
        >
            Logout
        </a>

    </div>

</nav>



<!--
|--------------------------------------------------------------------------
| CONTENT
|--------------------------------------------------------------------------
-->

<div class="container">


    <!-- HEADER -->

    <div class="page-header">

        <h1>
            📁 Upload Berkas
        </h1>

        <p>
            Silakan unggah dokumen persyaratan
            pendaftaran untuk melengkapi data
            PMBM Anda.
        </p>

    </div>



    <!-- ALERT -->

    <?php if ($pesan !== ""): ?>

        <div
            class="alert <?= htmlspecialchars($jenis_pesan) ?>"
        >

            <?= htmlspecialchars($pesan) ?>

        </div>

    <?php endif; ?>



    <!-- INFO -->

    <div class="info-box">

        <strong>
            📌 Perhatian
        </strong>

        <p>

            Pastikan dokumen yang diunggah
            dapat dibaca dengan jelas.

            Format yang diperbolehkan adalah
            <b>JPG, JPEG, PNG dan PDF</b>.

            Ukuran maksimal setiap file
            adalah <b>5 MB</b>.

        </p>

    </div>



    <!--
    |--------------------------------------------------------------------------
    | DAFTAR DOKUMEN
    |--------------------------------------------------------------------------
    -->

    <div class="upload-card">


        <div class="card-title">

            <div class="card-icon">
                📋
            </div>


            <div>

                <h2>
                    Dokumen yang Harus Disiapkan
                </h2>

                <p>
                    Pastikan dokumen berikut sudah tersedia
                    sebelum melakukan upload.
                </p>

            </div>

        </div>



        <div class="required-list">


            <!-- 1 -->

            <div class="required-item">

                <div class="document-number">
                    1
                </div>

                <div>

                    <strong>
                        Kartu Keluarga
                    </strong>

                    <span>
                        KK terbaru dan dapat dibaca
                        dengan jelas.
                    </span>

                </div>

            </div>



            <!-- 2 -->

            <div class="required-item">

                <div class="document-number">
                    2
                </div>

                <div>

                    <strong>
                        Akta Kelahiran
                    </strong>

                    <span>
                        Akta kelahiran calon murid.
                    </span>

                </div>

            </div>



            <!-- 3 -->

            <div class="required-item">

                <div class="document-number">
                    3
                </div>

                <div>

                    <strong>
                        Ijazah / Surat Keterangan Aktif
                    </strong>

                    <span>
                        Ijazah atau Surat Keterangan Aktif dari sekolah asal.
                    </span>

                </div>

            </div>



            <!-- 4 -->

            <div class="required-item">

                <div class="document-number">
                    4
                </div>

                <div>

                    <strong>
                        Pas Foto
                    </strong>

                    <span>
                        Pas foto terbaru calon murid.
                    </span>

                </div>

            </div>



            <!-- 5 -->

            <div class="required-item">

                <div class="document-number">
                    5
                </div>

                <div>

                    <strong>
                        KTP Orangtua / Wali
                    </strong>

                    <span>
                        KTP orangtua atau wali murid
                    </span>

                </div>

            </div>



            <!-- 6 -->

            <div class="required-item">

                <div class="document-number">
                    6
                </div>

                <div>

                    <strong>
                        Nomor Induk Siswa Nasional (NISN)
                    </strong>

                    <span>
                        Bukti Keaktifan NISN.
                    </span>

                </div>

            </div>


        </div>

    </div>



    <!--
    |--------------------------------------------------------------------------
    | UPLOAD BERKAS
    |--------------------------------------------------------------------------
    -->

    <div class="upload-card">


        <div class="card-title">

            <div class="card-icon">
                📤
            </div>


            <div>

                <h2>
                    Upload Berkas
                </h2>

                <p>
                    Upload setiap dokumen sesuai
                    dengan jenisnya.
                </p>

            </div>

        </div>



        <!--
        ============================================================
        KARTU KELUARGA
        ============================================================
        -->

        <div class="upload-item">


            <div class="upload-info">

                <div class="upload-icon">
                    📄
                </div>


                <div>

                    <h3>
                        Kartu Keluarga
                    </h3>

                    <p>
                        Format JPG, JPEG, PNG atau PDF
                        • Maksimal 5 MB
                    </p>

                </div>

            </div>



            <form
                method="POST"
                enctype="multipart/form-data"
                class="upload-form"
            >

                <input
                    type="hidden"
                    name="jenis_berkas"
                    value="kk"
                >


                <input
                    type="file"
                    name="berkas"
                    accept=".jpg,.jpeg,.png,.pdf"
                    required
                >


                <button
                    type="submit"
                    class="btn-upload"
                >
                    📤 Upload
                </button>

            </form>

        </div>



        <!--
        ============================================================
        AKTA KELAHIRAN
        ============================================================
        -->

        <div class="upload-item">


            <div class="upload-info">

                <div class="upload-icon">
                    📄
                </div>


                <div>

                    <h3>
                        Akta Kelahiran
                    </h3>

                    <p>
                        Format JPG, JPEG, PNG atau PDF
                        • Maksimal 5 MB
                    </p>

                </div>

            </div>



            <form
                method="POST"
                enctype="multipart/form-data"
                class="upload-form"
            >

                <input
                    type="hidden"
                    name="jenis_berkas"
                    value="akta"
                >


                <input
                    type="file"
                    name="berkas"
                    accept=".jpg,.jpeg,.png,.pdf"
                    required
                >


                <button
                    type="submit"
                    class="btn-upload"
                >
                    📤 Upload
                </button>

            </form>

        </div>



        <!--
        ============================================================
        IJAZAH / Surat Keterangan Aktif
        ============================================================
        -->

        <div class="upload-item">


            <div class="upload-info">

                <div class="upload-icon">
                    🎓
                </div>


                <div>

                    <h3>
                        Ijazah / Surat Keterangan Aktif
                    </h3>

                    <p>
                        Format JPG, JPEG, PNG atau PDF
                        • Maksimal 5 MB
                    </p>

                </div>

            </div>



            <form
                method="POST"
                enctype="multipart/form-data"
                class="upload-form"
            >

                <input
                    type="hidden"
                    name="jenis_berkas"
                    value="ijazah"
                >


                <input
                    type="file"
                    name="berkas"
                    accept=".jpg,.jpeg,.png,.pdf"
                    required
                >


                <button
                    type="submit"
                    class="btn-upload"
                >
                    📤 Upload
                </button>

            </form>

        </div>



        <!--
        ============================================================
        PAS FOTO
        ============================================================
        -->

        <div class="upload-item">


            <div class="upload-info">

                <div class="upload-icon">
                    📷
                </div>


                <div>

                    <h3>
                        Pas Foto
                    </h3>

                    <p>
                        Format JPG, JPEG atau PNG
                        • Maksimal 5 MB
                    </p>

                </div>

            </div>



            <form
                method="POST"
                enctype="multipart/form-data"
                class="upload-form"
            >

                <input
                    type="hidden"
                    name="jenis_berkas"
                    value="foto"
                >


                <input
                    type="file"
                    name="berkas"
                    accept=".jpg,.jpeg,.png"
                    required
                >


                <button
                    type="submit"
                    class="btn-upload"
                >
                    📤 Upload
                </button>

            </form>

        </div>



        <!--
        ============================================================
        KTP ORANGTUA / WALI
        ============================================================
        -->

        <div class="upload-item">


            <div class="upload-info">

                <div class="upload-icon">
                    💳
                </div>


                <div>

                    <h3>
                        KTP Orangtua / Wali
                    </h3>

                    <p>
                        Format JPG, JPEG, PNG atau PDF
                        • Maksimal 5 MB
                    </p>

                </div>

            </div>



            <form
                method="POST"
                enctype="multipart/form-data"
                class="upload-form"
            >

                <input
                    type="hidden"
                    name="jenis_berkas"
                    value="kip"
                >


                <input
                    type="file"
                    name="berkas"
                    accept=".jpg,.jpeg,.png,.pdf"
                >


                <button
                    type="submit"
                    class="btn-upload"
                >
                    📤 Upload
                </button>

            </form>

        </div>



        <!--
        ============================================================
        NISN
        ============================================================
        -->

        <div class="upload-item">


            <div class="upload-info">

                <div class="upload-icon">
                    📑
                </div>


                <div>

                    <h3>
                        Nomor Induk Siswa Nasional (NISN)
                    </h3>

                    <p>
                        Format JPG, JPEG, PNG atau PDF
                        • Maksimal 5 MB
                    </p>

                </div>

            </div>



            <form
                method="POST"
                enctype="multipart/form-data"
                class="upload-form"
            >

                <input
                    type="hidden"
                    name="jenis_berkas"
                    value="lainnya"
                >


                <input
                    type="file"
                    name="berkas"
                    accept=".jpg,.jpeg,.png,.pdf"
                >


                <button
                    type="submit"
                    class="btn-upload"
                >
                    📤 Upload
                </button>

            </form>

        </div>


    </div>


</div>



<!--
|--------------------------------------------------------------------------
| FOOTER
|--------------------------------------------------------------------------
-->

<div class="footer">

    © <?= date('Y') ?>
    MTs Ulumul Qur'an Al Madani

</div>


</body>

</html>
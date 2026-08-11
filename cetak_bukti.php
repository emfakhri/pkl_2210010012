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
| AMBIL DATA SISWA
|--------------------------------------------------------------------------
*/

$query = "
    SELECT
        id,
        user_id,
        nama,
        nisn,
        nik,
        tempat_lahir,
        tanggal_lahir,
        jk,
        telepon,
        nama_sekolah_asal,
        status_sekolah_asal,
        npsn_nsm_asal,
        alamat_sekolah_asal,
        status,
        created_at
    FROM students
    WHERE user_id = ?
    LIMIT 1
";


$stmt = mysqli_prepare($conn, $query);


if (!$stmt) {

    die(
        "Query database gagal: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);


$data = mysqli_fetch_assoc($result);


mysqli_stmt_close($stmt);


/*
|--------------------------------------------------------------------------
| CEK DATA
|--------------------------------------------------------------------------
*/

if (!$data) {

    ?>

    <!DOCTYPE html>

    <html lang="id">

    <head>

        <meta charset="UTF-8">

        <title>
            Data Belum Ditemukan
        </title>

        <style>

            body {
                font-family: Arial, sans-serif;
                background: #f4f8f5;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
            }

            .box {
                background: white;
                padding: 40px;
                border-radius: 15px;
                text-align: center;
                box-shadow:
                    0 5px 20px
                    rgba(0,0,0,.1);
                max-width: 450px;
            }

            h2 {
                color: #075e35;
            }

            p {
                color: #666;
                line-height: 1.6;
            }

            a {
                display: inline-block;
                margin-top: 15px;
                padding: 11px 20px;
                background: #075e35;
                color: white;
                text-decoration: none;
                border-radius: 8px;
            }

        </style>

    </head>

    <body>

        <div class="box">

            <h2>
                Data Pendaftaran Belum Ditemukan
            </h2>

            <p>
                Kamu belum memiliki data pendaftaran.
                Silakan lengkapi formulir pendaftaran
                terlebih dahulu.
            </p>

            <a href="dashboard.php">
                ← Kembali ke Dashboard
            </a>

        </div>

    </body>

    </html>

    <?php

    exit;

}


/*
|--------------------------------------------------------------------------
| FUNGSI TAMPIL DATA
|--------------------------------------------------------------------------
*/

function tampil($nilai)
{

    if (
        isset($nilai) &&
        $nilai !== null &&
        $nilai !== ''
    ) {

        return htmlspecialchars(
            $nilai,
            ENT_QUOTES,
            'UTF-8'
        );

    }

    return '-';

}


/*
|--------------------------------------------------------------------------
| NOMOR PENDAFTARAN
|--------------------------------------------------------------------------
|
| Karena tabel student belum memiliki kolom
| nomor_pendaftaran, kita menggunakan ID siswa
| sebagai nomor pendaftaran sementara.
|
*/

$nomor_pendaftaran =
    'PMBM-' .
    date(
        'Y',
        strtotime($data['created_at'])
    ) .
    '-' .
    str_pad(
        $data['id'],
        5,
        '0',
        STR_PAD_LEFT
    );


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

$status = $data['status'] ?? '';


if ($status === '') {

    $status_tampil = 'Menunggu Verifikasi';

} else {

    $status_tampil = $status;

}


/*
|--------------------------------------------------------------------------
| TANGGAL PENDAFTARAN
|--------------------------------------------------------------------------
*/

if (!empty($data['created_at'])) {

    $tanggal_pendaftaran =
        date(
            'd-m-Y H:i',
            strtotime($data['created_at'])
        );

} else {

    $tanggal_pendaftaran = '-';

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
    Bukti Pendaftaran -
    <?= tampil($data['nama']) ?>
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

    background: #eef3ef;

    color: #222;

    padding: 30px;

}


/*
|--------------------------------------------------------------------------
| CONTAINER
|--------------------------------------------------------------------------
*/

.container {

    max-width: 850px;

    margin: auto;

}


/*
|--------------------------------------------------------------------------
| ACTION BUTTON
|--------------------------------------------------------------------------
*/

.action {

    display: flex;

    justify-content: flex-end;

    gap: 10px;

    margin-bottom: 15px;

}


.btn {

    border: none;

    border-radius: 8px;

    padding: 11px 18px;

    font-size: 14px;

    font-weight: bold;

    cursor: pointer;

    text-decoration: none;

    display: inline-block;

}


.btn-print {

    background: #f5c400;

    color: #075e35;

}


.btn-back {

    background: #075e35;

    color: white;

}


.btn:hover {

    opacity: .9;

}


/*
|--------------------------------------------------------------------------
| BUKTI
|--------------------------------------------------------------------------
*/

.bukti {

    background: white;

    padding: 45px;

    box-shadow:
        0 5px 25px
        rgba(0,0,0,.1);

    border-radius: 6px;

}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

.header {

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 20px;

    padding-bottom: 20px;

    border-bottom:
        3px solid #075e35;

}


/*
|--------------------------------------------------------------------------
| LOGO
|--------------------------------------------------------------------------
*/

.logo {

    width: 75px;

    height: 75px;

    border-radius: 50%;

    background: #f5c400;

    display: flex;

    align-items: center;

    justify-content: center;

    color: #075e35;

    font-size: 32px;

    font-weight: bold;

}


/*
|--------------------------------------------------------------------------
| SEKOLAH
|--------------------------------------------------------------------------
*/

.school {

    text-align: center;

}


.school h1 {

    color: #075e35;

    font-size: 22px;

    margin-bottom: 5px;

}


.school h2 {

    font-size: 16px;

    margin-bottom: 5px;

}


.school p {

    font-size: 12px;

    color: #666;

}


/*
|--------------------------------------------------------------------------
| JUDUL
|--------------------------------------------------------------------------
*/

.title {

    text-align: center;

    margin: 30px 0;

}


.title h2 {

    color: #075e35;

    font-size: 21px;

    margin-bottom: 8px;

}


.title p {

    font-size: 13px;

    color: #666;

}


/*
|--------------------------------------------------------------------------
| NOMOR PENDAFTARAN
|--------------------------------------------------------------------------
*/

.registration-number {

    background: #fff5c7;

    border:
        1px solid #f5c400;

    border-radius: 10px;

    padding: 16px;

    text-align: center;

    margin-bottom: 25px;

}


.registration-number span {

    display: block;

    color: #075e35;

    font-size: 12px;

    margin-bottom: 5px;

}


.registration-number strong {

    color: #075e35;

    font-size: 21px;

    letter-spacing: 1px;

}


/*
|--------------------------------------------------------------------------
| SECTION
|--------------------------------------------------------------------------
*/

.section {

    margin-top: 25px;

}


.section-title {

    background: #075e35;

    color: white;

    padding: 10px 14px;

    font-size: 14px;

    font-weight: bold;

    border-radius:
        6px 6px 0 0;

}


/*
|--------------------------------------------------------------------------
| TABLE
|--------------------------------------------------------------------------
*/

.data-table {

    width: 100%;

    border-collapse: collapse;

    border:
        1px solid #ddd;

}


.data-table tr {

    border-bottom:
        1px solid #ddd;

}


.data-table td {

    padding: 11px;

    font-size: 13px;

    vertical-align: top;

}


.data-table td:first-child {

    width: 35%;

    font-weight: bold;

    color: #555;

    background: #f8faf8;

}


.data-table td:nth-child(2) {

    color: #222;

}


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

.status {

    display: inline-block;

    padding: 6px 12px;

    border-radius: 20px;

    background: #fff3cd;

    color: #856404;

    font-size: 12px;

    font-weight: bold;

}


/*
|--------------------------------------------------------------------------
| FOOTER
|--------------------------------------------------------------------------
*/

.footer {

    margin-top: 35px;

    display: flex;

    justify-content: space-between;

    font-size: 12px;

    color: #666;

}


.signature {

    text-align: center;

    min-width: 180px;

}


.signature-space {

    height: 55px;

}


/*
|--------------------------------------------------------------------------
| PRINT
|--------------------------------------------------------------------------
*/

@media print {

    body {

        background: white;

        padding: 0;

    }


    .action {

        display: none;

    }


    .bukti {

        box-shadow: none;

        border-radius: 0;

        padding: 20px;

    }


    .container {

        max-width: none;

    }


    @page {

        size: A4;

        margin: 15mm;

    }

}


/*
|--------------------------------------------------------------------------
| MOBILE
|--------------------------------------------------------------------------
*/

@media (max-width: 600px) {

    body {

        padding: 10px;

    }


    .bukti {

        padding: 20px;

    }


    .header {

        flex-direction: column;

    }


    .school h1 {

        font-size: 18px;

    }


    .school h2 {

        font-size: 14px;

    }


    .footer {

        flex-direction: column;

        gap: 30px;

        align-items: center;

    }

}

</style>

</head>


<body>


<div class="container">


    <!--
    |--------------------------------------------------------------------------
    | BUTTON
    |--------------------------------------------------------------------------
    -->

    <div class="action">


        <a
            href="dashboard.php"
            class="btn btn-back"
        >
            ← Kembali
        </a>


        <button
            onclick="window.print()"
            class="btn btn-print"
        >
            🖨 Cetak Bukti
        </button>


    </div>



    <!--
    |--------------------------------------------------------------------------
    | BUKTI PENDAFTARAN
    |--------------------------------------------------------------------------
    -->

    <div class="bukti">


        <!-- HEADER -->

        <div class="header">


            <div class="logo">

                🏫

            </div>


            <div class="school">


                <h1>

                    MTs ULUMUL QUR'AN AL MADANI

                </h1>


                <h2>

                    PENERIMAAN PESERTA DIDIK BARU

                </h2>


                <p>

                    Bukti Pendaftaran Calon Peserta Didik Baru

                </p>


            </div>


        </div>



        <!-- JUDUL -->

        <div class="title">


            <h2>

                BUKTI PENDAFTARAN

            </h2>


            <p>

                Dokumen resmi bukti pendaftaran
                calon peserta didik baru.

            </p>


        </div>



        <!-- NOMOR -->

        <div class="registration-number">


            <span>

                NOMOR PENDAFTARAN

            </span>


            <strong>

                <?= htmlspecialchars(
                    $nomor_pendaftaran
                ) ?>

            </strong>


        </div>



        <!--
        |--------------------------------------------------------------------------
        | DATA SISWA
        |--------------------------------------------------------------------------
        -->

        <div class="section">


            <div class="section-title">

                DATA CALON PESERTA DIDIK

            </div>


            <table class="data-table">


                <tr>

                    <td>
                        Nama Lengkap
                    </td>

                    <td>
                        <?= tampil(
                            $data['nama']
                        ) ?>
                    </td>

                </tr>


                <tr>

                    <td>
                        NISN
                    </td>

                    <td>
                        <?= tampil(
                            $data['nisn']
                        ) ?>
                    </td>

                </tr>


                <tr>

                    <td>
                        NIK
                    </td>

                    <td>
                        <?= tampil(
                            $data['nik']
                        ) ?>
                    </td>

                </tr>


                <tr>

                    <td>
                        Tempat Lahir
                    </td>

                    <td>
                        <?= tampil(
                            $data['tempat_lahir']
                        ) ?>
                    </td>

                </tr>


                <tr>

                    <td>
                        Tanggal Lahir
                    </td>

                    <td>
                        <?= tampil(
                            $data['tanggal_lahir']
                        ) ?>
                    </td>

                </tr>


                <tr>

                    <td>
                        Jenis Kelamin
                    </td>

                    <td>

                        <?php

                        if (
                            $data['jk'] === 'L'
                        ) {

                            echo 'Laki-laki';

                        } elseif (
                            $data['jk'] === 'P'
                        ) {

                            echo 'Perempuan';

                        } else {

                            echo tampil(
                                $data['jk']
                            );

                        }

                        ?>

                    </td>

                </tr>


                <tr>

                    <td>
                        Nomor HP / WhatsApp
                    </td>

                    <td>
                        <?= tampil(
                            $data['telepon']
                        ) ?>
                    </td>

                </tr>


            </table>


        </div>



        <!--
        |--------------------------------------------------------------------------
        | SEKOLAH ASAL
        |--------------------------------------------------------------------------
        -->

        <div class="section">


            <div class="section-title">

                DATA SEKOLAH ASAL

            </div>


            <table class="data-table">


                <tr>

                    <td>
                        Nama Sekolah
                    </td>

                    <td>
                        <?= tampil(
                            $data['nama_sekolah_asal']
                        ) ?>
                    </td>

                </tr>


                <tr>

                    <td>
                        Status Sekolah
                    </td>

                    <td>
                        <?= tampil(
                            $data['status_sekolah_asal']
                        ) ?>
                    </td>

                </tr>


                <tr>

                    <td>
                        NPSN / NSM
                    </td>

                    <td>
                        <?= tampil(
                            $data['npsn_nsm_asal']
                        ) ?>
                    </td>

                </tr>


                <tr>

                    <td>
                        Alamat Sekolah
                    </td>

                    <td>
                        <?= tampil(
                            $data['alamat_sekolah_asal']
                        ) ?>
                    </td>

                </tr>


            </table>


        </div>



        <!--
        |--------------------------------------------------------------------------
        | STATUS PENDAFTARAN
        |--------------------------------------------------------------------------
        -->

        <div class="section">


            <div class="section-title">

                STATUS PENDAFTARAN

            </div>


            <table class="data-table">


                <tr>

                    <td>
                        Status
                    </td>

                    <td>

                        <span class="status">

                            <?= tampil(
                                $status_tampil
                            ) ?>

                        </span>

                    </td>

                </tr>


                <tr>

                    <td>
                        Tanggal Pendaftaran
                    </td>

                    <td>

                        <?= tampil(
                            $tanggal_pendaftaran
                        ) ?>

                    </td>

                </tr>


            </table>


        </div>



        <!--
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        -->

        <div class="footer">


            <div>

                Dicetak pada:

                <?= date('d-m-Y H:i') ?>

            </div>


            <div class="signature">

                Mengetahui,

                <br>

                Panitia PMBM


                <div class="signature-space"></div>


                <strong>

                    ______________________

                </strong>


            </div>


        </div>


    </div>


</div>


</body>

</html>
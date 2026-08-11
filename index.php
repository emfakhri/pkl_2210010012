<style>
    :root {
        --green-dark: #075e35;
        --green: #0b7a45;
        --green-light: #e9f8ef;
        --yellow: #f5c400;
        --yellow-light: #fff8d6;
        --dark: #1f2937;
        --gray: #6b7280;
        --white: #ffffff;
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        margin: 0;
        background: #f8faf9;
        color: var(--dark);
        font-family: "Segoe UI", Arial, sans-serif;
    }

    /* =========================
       HERO
    ========================= */

    .hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(5, 91, 51, 0.98), rgba(11, 122, 69, 0.94));
        color: white;
        padding: 90px 20px 100px;
        text-align: center;
    }

    .hero::before {
        content: "";
        position: absolute;
        width: 420px;
        height: 420px;
        border-radius: 50%;
        background: rgba(245, 196, 0, 0.12);
        right: -150px;
        top: -160px;
    }

    .hero::after {
        content: "";
        position: absolute;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
        left: -120px;
        bottom: -150px;
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .hero-logo {
        width: 115px;
        height: 115px;
        object-fit: contain;
        background: white;
        padding: 10px;
        border-radius: 50%;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        margin-bottom: 25px;
    }

    .hero-small {
        color: var(--yellow);
        font-weight: 800;
        letter-spacing: 2px;
        font-size: 14px;
    }

    .hero h1 {
        font-size: 46px;
        font-weight: 900;
        margin: 12px 0 20px;
    }

    .hero p {
        max-width: 750px;
        margin: auto;
        font-size: 17px;
        line-height: 1.8;
        opacity: 0.95;
    }

    .btn-daftar {
        background: var(--yellow);
        color: #4a3b00 !important;
        border: none;
        border-radius: 8px;
        padding: 13px 28px;
        font-weight: 800;
        text-decoration: none;
        display: inline-block;
        margin-top: 15px;
    }

    .btn-daftar:hover {
        background: #ffd633;
    }

    .btn-login-hero {
        background: white;
        color: var(--green-dark) !important;
        border-radius: 8px;
        padding: 13px 28px;
        font-weight: 800;
        text-decoration: none;
        display: inline-block;
        margin-left: 10px;
    }

    /* =========================
       SECTION
    ========================= */

    .section {
        padding: 75px 20px;
    }

    .section-green {
        background: var(--green-light);
    }

    .section-title {
        color: var(--green-dark);
        font-weight: 900;
        margin-bottom: 10px;
        text-align: center;
    }

    .section-subtitle {
        color: var(--gray);
        margin-bottom: 45px;
        text-align: center;
    }

    /* =========================
       CARD
    ========================= */

    .schedule-card,
    .requirement-card,
    .step-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 18px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.06);
    }

    .schedule-card {
        border-left: 5px solid var(--yellow);
    }

    .schedule-title {
        color: var(--green-dark);
        font-weight: 800;
        font-size: 18px;
        margin-bottom: 6px;
    }

    .schedule-card p,
    .requirement-card ul,
    .step-card p {
        color: var(--gray);
        line-height: 1.7;
    }

    .requirement-card h4,
    .step-card h5 {
        color: var(--green-dark);
        font-weight: 800;
    }

    .requirement-icon {
        width: 58px;
        height: 58px;
        border-radius: 12px;
        background: var(--yellow-light);
        color: #a07b00;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 20px;
    }

    .step-number {
        width: 62px;
        height: 62px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: var(--green);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        font-weight: 900;
    }

    /* =========================
       CTA
    ========================= */

    .cta {
        background: var(--green-dark);
        color: white;
        padding: 70px 20px;
        text-align: center;
    }

    .cta h2 {
        font-weight: 900;
        margin-bottom: 15px;
    }

    .cta p {
        opacity: 0.9;
        margin-bottom: 30px;
    }

    /* =========================
       FOOTER
    ========================= */

    .footer-custom {
        background: #043d24;
        color: white;
        text-align: center;
        padding: 25px 15px;
    }
</style>

<!-- HERO -->
<section class="hero">

    <div class="hero-content">

        <img src="assets/img/logo.png" class="hero-logo">

        <div class="hero-small">
            PENERIMAAN MURID BARU MADRASAH
        </div>

        <h1>MTs Ulumul Qur'an Al Madani</h1>

        <p>
            Selamat datang di MTs Ulumul Qur'an Al Madani. Terakreditasi 'A', School for Leader, Sekolah Adiwiyata, Sekolah Berwawasan Lingkungan.
        </p>

        <a href="pendaftaran.php" class="btn-daftar">
            Daftar Sekarang
        </a>

        <a href="auth/login.php" class="btn-login-hero">
            Login
        </a>

    </div>

</section>

<!-- JADWAL -->
<section class="section">

    <h2 class="section-title">Jadwal Pendaftaran</h2>
    <p class="section-subtitle">Tahapan PMBM</p>

    <div class="schedule-card">
        <div class="schedule-title">01. Pendaftaran</div>
        <p>Calon siswa mendaftar secara online.</p>
    </div>

    <div class="schedule-card">
        <div class="schedule-title">02. Verifikasi</div>
        <p>Data diperiksa oleh pihak madrasah.</p>
    </div>

    <div class="schedule-card">
        <div class="schedule-title">03. Asesmen</div>
        <p>Seleksi sesuai ketentuan.</p>
    </div>

    <div class="schedule-card">
        <div class="schedule-title">04. Pengumuman</div>
        <p>Hasil diumumkan melalui sistem.</p>
    </div>

</section>

<!-- CTA -->
<section class="cta">

    <h2>Daftar Sekarang</h2>
    <p>Jadilah bagian dari MTs Ulumul Qur'an Al Madani</p>

    <a href="pendaftaran.php" class="btn-daftar">
        Mulai Pendaftaran
    </a>

</section>

<!-- FOOTER -->
<footer class="footer-custom">
<?php echo date('Y'); ?> MTs Ulumul Qur'an Al Madani
</footer>
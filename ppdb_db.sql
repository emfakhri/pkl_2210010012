CREATE DATABASE ppdb_db;
USE ppdb_db;

-- TABEL USER (ADMIN, PANITIA, SISWA)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(255),
    role ENUM('admin','panitia','siswa')
);

-- TABEL SISWA
CREATE TABLE siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100),
    nisn VARCHAR(20),
    asal_sekolah VARCHAR(100),
    status_verifikasi ENUM('menunggu','diterima','ditolak') DEFAULT 'menunggu',
    status_lulus ENUM('belum','lulus','tidak lulus') DEFAULT 'belum'
);

-- TABEL BERKAS
CREATE TABLE berkas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT,
    ijazah VARCHAR(100),
    akta VARCHAR(100),
    kk VARCHAR(100),
    FOREIGN KEY (siswa_id) REFERENCES siswa(id)
);

-- USER DEFAULT
INSERT INTO users VALUES
(NULL,'admin','admin123','admin'),
(NULL,'panitia','panitia123','panitia');

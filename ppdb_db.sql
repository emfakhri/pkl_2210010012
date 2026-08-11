-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 10, 2026 at 01:52 PM
-- Server version: 10.11.18-MariaDB-cll-lve
-- PHP Version: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uqac5968_ppdb_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_sekolah_asal` varchar(150) DEFAULT NULL,
  `status_sekolah_asal` varchar(20) DEFAULT NULL,
  `npsn_nsm_asal` varchar(30) DEFAULT NULL,
  `alamat_sekolah_asal` text DEFAULT NULL,
  `nama` varchar(150) DEFAULT NULL,
  `nisn` varchar(20) DEFAULT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jk` varchar(20) DEFAULT NULL,
  `jumlah_saudara` int(11) DEFAULT NULL,
  `anak_ke` int(11) DEFAULT NULL,
  `cita_cita` varchar(100) DEFAULT NULL,
  `hobi` varchar(100) DEFAULT NULL,
  `telepon` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `pembiaya_sekolah` varchar(100) DEFAULT NULL,
  `pra_sekolah` varchar(50) DEFAULT NULL,
  `imunisasi` varchar(50) DEFAULT NULL,
  `no_kk` varchar(20) DEFAULT NULL,
  `nama_kepala_keluarga` varchar(150) DEFAULT NULL,
  `nama_ayah` varchar(150) DEFAULT NULL,
  `status_ayah` varchar(30) DEFAULT NULL,
  `nik_ayah` varchar(20) DEFAULT NULL,
  `tanggal_lahir_ayah` date DEFAULT NULL,
  `pendidikan_ayah` varchar(80) DEFAULT NULL,
  `pekerjaan_ayah` varchar(150) DEFAULT NULL,
  `penghasilan_ayah` varchar(100) DEFAULT NULL,
  `hp_ayah` varchar(30) DEFAULT NULL,
  `nama_ibu` varchar(150) DEFAULT NULL,
  `status_ibu` varchar(30) DEFAULT NULL,
  `nik_ibu` varchar(20) DEFAULT NULL,
  `tanggal_lahir_ibu` date DEFAULT NULL,
  `pendidikan_ibu` varchar(80) DEFAULT NULL,
  `pekerjaan_ibu` varchar(150) DEFAULT NULL,
  `penghasilan_ibu` varchar(100) DEFAULT NULL,
  `hp_ibu` varchar(30) DEFAULT NULL,
  `kepemilikan_rumah_ayah` varchar(100) DEFAULT NULL,
  `provinsi_ayah` varchar(100) DEFAULT NULL,
  `kabupaten_ayah` varchar(100) DEFAULT NULL,
  `kecamatan_ayah` varchar(100) DEFAULT NULL,
  `kelurahan_ayah` varchar(100) DEFAULT NULL,
  `rt_ayah` varchar(10) DEFAULT NULL,
  `rw_ayah` varchar(10) DEFAULT NULL,
  `jalan_ayah` text DEFAULT NULL,
  `kode_pos_ayah` varchar(10) DEFAULT NULL,
  `alamat_ibu_status` varchar(40) DEFAULT NULL,
  `kepemilikan_rumah_ibu` varchar(100) DEFAULT NULL,
  `provinsi_ibu` varchar(100) DEFAULT NULL,
  `kabupaten_ibu` varchar(100) DEFAULT NULL,
  `kecamatan_ibu` varchar(100) DEFAULT NULL,
  `kelurahan_ibu` varchar(100) DEFAULT NULL,
  `rt_ibu` varchar(10) DEFAULT NULL,
  `rw_ibu` varchar(10) DEFAULT NULL,
  `jalan_ibu` text DEFAULT NULL,
  `kode_pos_ibu` varchar(10) DEFAULT NULL,
  `domisili_murid` varchar(100) DEFAULT NULL,
  `transportasi` varchar(100) DEFAULT NULL,
  `jarak_rumah` varchar(100) DEFAULT NULL,
  `waktu_tempuh` varchar(100) DEFAULT NULL,
  `kebutuhan_khusus` varchar(200) DEFAULT NULL,
  `kebutuhan_disabilitas` varchar(100) DEFAULT NULL,
  `status_wali` varchar(100) DEFAULT NULL,
  `nama_wali` varchar(150) DEFAULT NULL,
  `hp_wali` varchar(30) DEFAULT NULL,
  `tinggal_bersama` varchar(100) DEFAULT NULL,
  `alamat_wali` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'Menunggu Verifikasi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `nama_sekolah_asal`, `status_sekolah_asal`, `npsn_nsm_asal`, `alamat_sekolah_asal`, `nama`, `nisn`, `nik`, `tempat_lahir`, `tanggal_lahir`, `jk`, `jumlah_saudara`, `anak_ke`, `cita_cita`, `hobi`, `telepon`, `email`, `pembiaya_sekolah`, `pra_sekolah`, `imunisasi`, `no_kk`, `nama_kepala_keluarga`, `nama_ayah`, `status_ayah`, `nik_ayah`, `tanggal_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `penghasilan_ayah`, `hp_ayah`, `nama_ibu`, `status_ibu`, `nik_ibu`, `tanggal_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `penghasilan_ibu`, `hp_ibu`, `kepemilikan_rumah_ayah`, `provinsi_ayah`, `kabupaten_ayah`, `kecamatan_ayah`, `kelurahan_ayah`, `rt_ayah`, `rw_ayah`, `jalan_ayah`, `kode_pos_ayah`, `alamat_ibu_status`, `kepemilikan_rumah_ibu`, `provinsi_ibu`, `kabupaten_ibu`, `kecamatan_ibu`, `kelurahan_ibu`, `rt_ibu`, `rw_ibu`, `jalan_ibu`, `kode_pos_ibu`, `domisili_murid`, `transportasi`, `jarak_rumah`, `waktu_tempuh`, `kebutuhan_khusus`, `kebutuhan_disabilitas`, `status_wali`, `nama_wali`, `hp_wali`, `tinggal_bersama`, `alamat_wali`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'MTs Ulumul Qur\'an Al Madani', 'SWASTA', '69787317', 'Jl. Guntung Manggis', 'Farid Ulatih', '1212637200', '6203032808940002', 'Banjarbaru', '2021-08-28', 'L', 1, 1, 'TNI', 'Kesenian', '081251561415', 'official.faridulatih@gmail.com', 'ORANGTUA', 'PERNAH TK / RA', 'Lengkap', '62030328080009', 'fahri', 'fahri', 'MASIH HIDUP', '6203032808940002', '2021-06-23', 'D1', 'Pensiunan', 'Rp. 1.800.001 - Rp. 2.500.000', '081251561415', 'anna', 'MASIH HIDUP', '62030808940002', '2021-06-16', 'D2', 'Polri', 'Rp. 2.500.001 - Rp. 3.500.000', '', 'MILIK SENDIRI', 'kalimantan selatan', 'banjarbaru', 'landasan ulin', 'guntung manggis', '18', '3', 'jl. guntung mnaggis', '70724', 'SAMA_DENGAN_AYAH', '', '', '', '', '', '', '', '', '', 'TINGGAL DENGAN ORANG TUA', 'SEPEDA / SEPEDA LISTRIK', 'ANTARA 5 – 10 KM', '10 - 19 Menit', 'TIDAK ADA', 'TIDAK ADA', '', '', '', '', '', 'Menunggu Verifikasi', '2026-08-09 10:14:17', '2026-08-09 10:14:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','panitia','siswa') NOT NULL DEFAULT 'siswa',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `username`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@gmail.com', 'admin', '$2y$10$GnPW5T6wiQ1V/uakAPLV/O4HYCEqybuJuvNgok1n3GihVWxgoS9v2', 'admin', '2026-08-09 09:52:43', '2026-08-09 09:52:43'),
(2, 'Farid Ulatih', 'official.faridulatih@gmail.com', '121263720009', '$2y$10$8UjrXt8ly62JqSNznl8BV.5v.UsMKWFK6EpmludU2C/JFr5LW2yRS', 'siswa', '2026-08-09 10:09:06', '2026-08-09 10:09:06'),
(3, 'Alfaqih Arrasyid', 'alfaqiharrasyid@gmail.com', '69787317', '$2y$10$UAv3V/VWqQhp5O9G6gYehedDsUl3./eyrRuYtmd/lo9qo1ootARBK', 'siswa', '2026-08-10 01:09:22', '2026-08-10 01:09:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_nisn` (`nisn`),
  ADD KEY `idx_nik` (`nik`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

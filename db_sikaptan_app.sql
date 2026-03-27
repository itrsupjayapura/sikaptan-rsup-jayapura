-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2025 at 12:10 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `apd_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `apd_fields`
--

CREATE TABLE `apd_fields` (
  `id` int(11) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `display_label` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `apd_fields`
--

INSERT INTO `apd_fields` (`id`, `field_name`, `display_label`) VALUES
(1, 'haircap', 'Haircap'),
(2, 'faceshield', 'Faceshield / Google'),
(3, 'masker', 'Masker'),
(4, 'gown', 'Gown'),
(5, 'sarung_tangan', 'Sarung Tangan'),
(6, 'boot', 'Boot');

-- --------------------------------------------------------

--
-- Table structure for table `cuci_tangan_fields`
--

CREATE TABLE `cuci_tangan_fields` (
  `id` int(11) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `display_label` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cuci_tangan_fields`
--

INSERT INTO `cuci_tangan_fields` (`id`, `field_name`, `display_label`) VALUES
(56, 'bagus', 'bagus'),
(57, 'pandu', 'pandu'),
(58, 'makan', 'makan'),
(59, 'rumah', 'rumah');

-- --------------------------------------------------------

--
-- Table structure for table `data_observasi`
--

CREATE TABLE `data_observasi` (
  `id` int(11) NOT NULL,
  `bulan` varchar(50) DEFAULT NULL,
  `ruangan` varchar(100) DEFAULT NULL,
  `observer` varchar(100) DEFAULT NULL,
  `tanggal_input` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('aktif','selesai') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_observasi`
--

INSERT INTO `data_observasi` (`id`, `bulan`, `ruangan`, `observer`, `tanggal_input`, `status`) VALUES
(422, '2025-11', 'IGD Ruangan', 'admin.com', '2025-11-04 15:00:00', 'aktif'),
(423, '2025-11', 'Laboratorium', 'admin.com', '2025-11-04 15:00:00', 'aktif'),
(424, '2025-11', 'Poli Anak BAYI', 'admin.com', '2025-11-04 15:00:00', 'aktif'),
(425, '2025-11', 'Poli Jantung', 'admin.com', '2025-11-04 15:00:00', 'aktif'),
(426, '2025-11', 'Poli Mata', 'admin.com', '2025-11-04 15:00:00', 'aktif'),
(427, '2025-11', 'Poli Obgyn', 'admin.com', '2025-11-04 15:00:00', 'aktif');

-- --------------------------------------------------------

--
-- Table structure for table `data_observasi_cuci_tangan`
--

CREATE TABLE `data_observasi_cuci_tangan` (
  `id` int(11) NOT NULL,
  `bulan` varchar(50) DEFAULT NULL,
  `ruangan` varchar(100) DEFAULT NULL,
  `observer` varchar(100) DEFAULT NULL,
  `tanggal_input` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('aktif','selesai') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_observasi_cuci_tangan`
--

INSERT INTO `data_observasi_cuci_tangan` (`id`, `bulan`, `ruangan`, `observer`, `tanggal_input`, `status`) VALUES
(53, '2025-11', 'IGD Ruangan', 'admin.com', '2025-11-04 15:00:00', 'aktif'),
(54, '2025-11', 'Laboratorium', 'admin.com', '2025-11-04 15:00:00', 'aktif'),
(55, '2025-11', 'Poli Anak BAYI', 'admin.com', '2025-11-04 15:00:00', 'aktif'),
(56, '2025-11', 'Poli Bedah', 'admin.com', '2025-11-04 15:00:00', 'aktif'),
(57, '2025-11', 'Poli Gigi', 'admin.com', '2025-11-04 15:00:00', 'aktif'),
(58, '2025-11', 'Poli Mataku', 'admin.com', '2025-11-04 15:00:00', 'aktif'),
(59, '2025-11', 'Poli Obgyn', 'admin.com', '2025-11-04 15:00:00', 'aktif'),
(60, '2025-11', 'Poli Penyakit Dalam', 'admin.com', '2025-11-04 15:00:00', 'aktif'),
(61, '2025-11', 'Poli Syaraf', 'admin.com', '2025-11-04 15:00:00', 'aktif');

-- --------------------------------------------------------

--
-- Table structure for table `observasi_apd`
--

CREATE TABLE `observasi_apd` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `petugas` varchar(100) NOT NULL,
  `nama_rekan_dilaporkan` varchar(100) DEFAULT NULL,
  `tindakan` varchar(255) DEFAULT NULL,
  `haircap` enum('Ya','Tidak','Tidak Dinilai') DEFAULT NULL,
  `faceshield` enum('Ya','Tidak','Tidak Dinilai') DEFAULT NULL,
  `masker` enum('Ya','Tidak','Tidak Dinilai') DEFAULT NULL,
  `gown` enum('Ya','Tidak','Tidak Dinilai') DEFAULT NULL,
  `sarung_tangan` enum('Ya','Tidak','Tidak Dinilai') DEFAULT NULL,
  `boot` enum('Ya','Tidak','Tidak Dinilai') DEFAULT NULL,
  `numerator` int(11) DEFAULT 0,
  `denumerator` int(11) DEFAULT 0,
  `id_observasi` int(11) DEFAULT NULL,
  `bulan` varchar(20) DEFAULT NULL,
  `ruangan` varchar(100) DEFAULT NULL,
  `rumah` varchar(20) DEFAULT 'Tidak Dinilai',
  `bhayangkara` varchar(20) DEFAULT 'Tidak Dinilai',
  `handphone` enum('Ya','Tidak','Tidak Dinilai') DEFAULT 'Tidak Dinilai',
  `sepatu` enum('Ya','Tidak','Tidak Dinilai') DEFAULT 'Tidak Dinilai',
  `celana` enum('Ya','Tidak','Tidak Dinilai') DEFAULT 'Tidak Dinilai',
  `baju` enum('Ya','Tidak','Tidak Dinilai') DEFAULT 'Tidak Dinilai',
  `tangan` enum('Ya','Tidak','Tidak Dinilai') DEFAULT 'Tidak Dinilai'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `observasi_apd`
--

INSERT INTO `observasi_apd` (`id`, `tanggal`, `petugas`, `nama_rekan_dilaporkan`, `tindakan`, `haircap`, `faceshield`, `masker`, `gown`, `sarung_tangan`, `boot`, `numerator`, `denumerator`, `id_observasi`, `bulan`, `ruangan`, `rumah`, `bhayangkara`, `handphone`, `sepatu`, `celana`, `baju`, `tangan`) VALUES
(205, '2025-11-05', 'user.com', '', '', 'Ya', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', 5, 6, 423, '2025-11', 'Laboratorium', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai'),
(206, '2025-11-05', 'user.com', 'Pandu', 'kurang mengambil tindakan', 'Ya', 'Ya', 'Ya', 'Ya', 'Ya', 'Ya', 6, 6, 422, '2025-11', 'IGD Ruangan', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai'),
(207, '2025-11-05', 'user.com', '', 'bar', 'Ya', 'Ya', 'Ya', 'Ya', 'Ya', 'Tidak', 5, 6, 423, '2025-11', 'Laboratorium', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai');

-- --------------------------------------------------------

--
-- Table structure for table `observasi_cuci_tangan`
--

CREATE TABLE `observasi_cuci_tangan` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `petugas` varchar(100) NOT NULL,
  `nama_rekan_dilaporkan` varchar(100) DEFAULT NULL,
  `tindakan` varchar(255) DEFAULT NULL,
  `numerator` int(11) DEFAULT 0,
  `denumerator` int(11) DEFAULT 0,
  `id_observasi` int(11) DEFAULT NULL,
  `bulan` varchar(20) DEFAULT NULL,
  `ruangan` varchar(100) DEFAULT NULL,
  `handrub` enum('Ya','Tidak','Tidak Dinilai') DEFAULT 'Tidak Dinilai',
  `sabun` enum('Ya','Tidak','Tidak Dinilai') DEFAULT 'Tidak Dinilai',
  `air_mengalir` enum('Ya','Tidak','Tidak Dinilai') DEFAULT 'Tidak Dinilai',
  `tisu` enum('Ya','Tidak','Tidak Dinilai') DEFAULT 'Tidak Dinilai',
  `catatan` text DEFAULT NULL,
  `cuci_tangan_menggunakan` enum('Handwash','Handrub') DEFAULT NULL,
  `nilai_cuci_tangan` int(11) DEFAULT 0,
  `tangan baru` enum('Ya','Tidak','Tidak Dinilai') DEFAULT 'Tidak Dinilai',
  `bagus` enum('Ya','Tidak','Tidak Dinilai') DEFAULT 'Tidak Dinilai',
  `pandu` enum('Ya','Tidak','Tidak Dinilai') DEFAULT 'Tidak Dinilai',
  `makan` enum('Ya','Tidak','Tidak Dinilai') DEFAULT 'Tidak Dinilai',
  `rumah` enum('Ya','Tidak','Tidak Dinilai') DEFAULT 'Tidak Dinilai'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `observasi_cuci_tangan`
--

INSERT INTO `observasi_cuci_tangan` (`id`, `tanggal`, `petugas`, `nama_rekan_dilaporkan`, `tindakan`, `numerator`, `denumerator`, `id_observasi`, `bulan`, `ruangan`, `handrub`, `sabun`, `air_mengalir`, `tisu`, `catatan`, `cuci_tangan_menggunakan`, `nilai_cuci_tangan`, `tangan baru`, `bagus`, `pandu`, `makan`, `rumah`) VALUES
(33, '2025-11-05', 'user.com', 'Pandu', 'Ambil Jarum SUntik', 2, 3, 58, '2025-11', 'Poli Mataku', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', NULL, 'Handwash', 50, 'Tidak Dinilai', 'Ya', 'Tidak Dinilai', 'Ya', 'Tidak'),
(34, '2025-11-05', 'user.com', '', '', 4, 4, 59, '2025-11', 'Poli Obgyn', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', NULL, 'Handrub', 50, 'Tidak Dinilai', 'Ya', 'Ya', 'Ya', 'Ya'),
(35, '2025-11-05', 'user.com', '', '', 3, 4, 60, '2025-11', 'Poli Penyakit Dalam', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', NULL, 'Handwash', 50, 'Tidak Dinilai', 'Ya', 'Ya', 'Ya', 'Tidak'),
(36, '2025-11-05', 'user.com', '', 'bagus', 2, 3, 55, '2025-11', 'Poli Anak BAYI', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', 'Tidak Dinilai', NULL, 'Handrub', 50, 'Tidak Dinilai', 'Tidak', 'Tidak Dinilai', 'Ya', 'Ya');

-- --------------------------------------------------------

--
-- Table structure for table `ruangan`
--

CREATE TABLE `ruangan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ruangan`
--

INSERT INTO `ruangan` (`id`, `nama`, `created_at`) VALUES
(1, 'IGD Ruangan', '2025-10-25 08:29:39'),
(4, 'Poli Penyakit Dalam', '2025-10-25 08:29:39'),
(5, 'Poli Obgyn', '2025-10-25 08:29:39'),
(6, 'Poli Anak BAYI', '2025-10-25 08:29:39'),
(7, 'Poli Bedah', '2025-10-25 08:29:39'),
(8, 'Poli Syaraf', '2025-10-25 08:29:39'),
(9, 'Poli Jantung', '2025-10-25 08:29:39'),
(10, 'Poli Mata', '2025-10-25 08:29:39'),
(11, 'Poli Gigi', '2025-10-25 08:29:39'),
(12, 'Laboratorium', '2025-10-25 08:29:39'),
(13, 'Radiologi', '2025-10-25 08:29:39');

-- --------------------------------------------------------

--
-- Table structure for table `ruangan_cuci_tangan`
--

CREATE TABLE `ruangan_cuci_tangan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ruangan_cuci_tangan`
--

INSERT INTO `ruangan_cuci_tangan` (`id`, `nama`, `created_at`) VALUES
(1, 'IGD Ruangan', '2025-10-31 22:25:43'),
(4, 'Poli Penyakit Dalam', '2025-10-31 22:25:43'),
(5, 'Poli Obgyn', '2025-10-31 22:25:43'),
(6, 'Poli Anak BAYI', '2025-10-31 22:25:43'),
(7, 'Poli Bedah', '2025-10-31 22:25:43'),
(8, 'Poli Syaraf', '2025-10-31 22:25:43'),
(9, 'Poli Jantung', '2025-10-31 22:25:43'),
(10, 'Poli Mataku', '2025-10-31 22:25:43'),
(11, 'Poli Gigi', '2025-10-31 22:25:43'),
(12, 'Laboratorium', '2025-10-31 22:25:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL,
  `nama` varchar(100) NOT NULL DEFAULT '',
  `ruangan` varchar(100) NOT NULL DEFAULT '',
  `jabatan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `nama`, `ruangan`, `jabatan`) VALUES
(22, 'nilawati', '$2y$10$jL7TOvGeD7B0XPta/3C6eOTwfbTyE8rpk6CcLemdEmE.luL9e13ky', 'admin', 'nilawati', 'istana', 'presiden'),
(23, '13', '$2y$10$2QzUmi0t307VJ0yEXRaPZeT7aD3T0P.xxAIxE4Hm3eqGTwSqmV8Ve', 'admin', '13', '13', NULL),
(24, '1', '$2y$10$Xk5i7WJgN/SvmllFUr2n6uEiPexQac9opjCOIzE21sf0Rr1vT266e', 'admin', '1', '1', '1'),
(25, 'panduprayudi', '$2y$10$uil1wMCL/QUwf29AAQaEZuZ7Uhca0exxUL7E27Ibik8i/XWKvaQvC', 'admin', 'Pandu Prayudi, S.Kom', '123', '123'),
(27, 'user', '$2y$10$zdPpy2jCnzVsRceWG70DF.88FU4kK2.rabEiNQmHVhhZB5v1MA15C', 'user', 'user.com', 'GTP', NULL),
(28, 'admin', '$2y$10$BsnhU7IY26pRWSEQ3k3SC.9Iime1pVO2Fx69xK2cm.X4FX2ndVxGK', 'admin', 'admin.com', 'GTE', 'karumkit');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `apd_fields`
--
ALTER TABLE `apd_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cuci_tangan_fields`
--
ALTER TABLE `cuci_tangan_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `data_observasi`
--
ALTER TABLE `data_observasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `data_observasi_cuci_tangan`
--
ALTER TABLE `data_observasi_cuci_tangan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `observasi_apd`
--
ALTER TABLE `observasi_apd`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `observasi_cuci_tangan`
--
ALTER TABLE `observasi_cuci_tangan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ruangan`
--
ALTER TABLE `ruangan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ruangan_cuci_tangan`
--
ALTER TABLE `ruangan_cuci_tangan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `apd_fields`
--
ALTER TABLE `apd_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `cuci_tangan_fields`
--
ALTER TABLE `cuci_tangan_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `data_observasi`
--
ALTER TABLE `data_observasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=428;

--
-- AUTO_INCREMENT for table `data_observasi_cuci_tangan`
--
ALTER TABLE `data_observasi_cuci_tangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `observasi_apd`
--
ALTER TABLE `observasi_apd`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT for table `observasi_cuci_tangan`
--
ALTER TABLE `observasi_cuci_tangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `ruangan`
--
ALTER TABLE `ruangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `ruangan_cuci_tangan`
--
ALTER TABLE `ruangan_cuci_tangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 09, 2026 at 09:37 AM
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
-- Database: `aplikasi_labor`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `nama`, `username`, `password`) VALUES
(1, 'Petugas Labor', 'admin', '$2y$10$V3Z3nN0dY8Z1M0xZ4lG0ye1p4ZC7mXGZ2WwJ5Z6l0v3M2B6KQXlG6'),
(2, NULL, 'admin123', '$2y$10$QkvUOnOp33g/EnhPhUu3J.A3bUVP3Tu.BPUvXa8CD2MRo5tiClrA.');

-- --------------------------------------------------------

--
-- Table structure for table `barang_labor`
--

CREATE TABLE `barang_labor` (
  `id` int(11) NOT NULL,
  `labor_id` int(11) NOT NULL,
  `nama` varchar(191) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `stok` int(11) DEFAULT 1,
  `status` varchar(20) DEFAULT 'Tersedia',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang_labor`
--

INSERT INTO `barang_labor` (`id`, `labor_id`, `nama`, `deskripsi`, `stok`, `status`, `created_at`) VALUES
(1, 1, 'Tablet Wacom Pro', 'Tablet grafis profesional 22 inch', 2, 'Tersedia', '2026-01-09 09:29:01'),
(2, 1, 'Monitor 4K', 'Monitor resolusi 4K untuk color grading', 3, 'Tersedia', '2026-01-09 09:29:01'),
(3, 1, 'Laptop Rendering', 'Laptop dengan GPU RTX 3080 Ti', 2, 'Tersedia', '2026-01-09 09:29:01'),
(4, 1, 'Stylus Pen Premium', 'Stylus dengan pressure sensitivity tinggi', 5, 'Tersedia', '2026-01-09 09:29:01'),
(5, 1, 'External SSD 2TB', 'Storage eksternal untuk project besar', 4, 'Tersedia', '2026-01-09 09:29:01'),
(6, 2, 'VR Headset HTC Vive', 'Virtual reality headset untuk development', 2, 'Tersedia', '2026-01-09 09:29:01'),
(7, 2, 'Gaming PC High-End', 'PC dengan RTX 4070 untuk game development', 3, 'Tersedia', '2026-01-09 09:29:01'),
(8, 2, 'Motion Capture Suit', 'Suit untuk motion capture 3D character', 1, 'Tersedia', '2026-01-09 09:29:01'),
(9, 2, 'Racing Simulator Setup', 'Cockpit racing game profesional', 2, 'Tersedia', '2026-01-09 09:29:01'),
(10, 2, 'Joy-Con Pro Controller', 'Wireless game controller premium', 6, 'Tersedia', '2026-01-09 09:29:01'),
(11, 3, 'Microphone Neumann U87', 'Microphone studio kondenser profesional', 2, 'Tersedia', '2026-01-09 09:29:01'),
(12, 3, 'Audio Interface MOTU', 'Audio interface 16 channel profesional', 2, 'Tersedia', '2026-01-09 09:29:01'),
(13, 3, 'Monitor Speaker Yamaha', 'Speaker monitor studio aktif', 4, 'Tersedia', '2026-01-09 09:29:01'),
(14, 3, 'Headphone Reference Audio', 'Headphone monitoring akurat untuk mixing', 5, 'Tersedia', '2026-01-09 09:29:01'),
(15, 3, 'XLR Cable Professional', 'Kabel audio profesional 10 meter', 10, 'Tersedia', '2026-01-09 09:29:01');

-- --------------------------------------------------------

--
-- Table structure for table `info_dashboard`
--

CREATE TABLE `info_dashboard` (
  `id` int(11) NOT NULL,
  `judul` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `terakhir_update` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `info_dashboard`
--

INSERT INTO `info_dashboard` (`id`, `judul`, `deskripsi`, `terakhir_update`) VALUES
(1, 'Informasi Labor', 'Silakan ajukan peminjaman labor sesuai jadwal dan peraturan yang berlaku.', '2026-01-09 01:32:13');

-- --------------------------------------------------------

--
-- Table structure for table `labor`
--

CREATE TABLE `labor` (
  `id` int(11) NOT NULL,
  `nama` varchar(191) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `labor`
--

INSERT INTO `labor` (`id`, `nama`, `deskripsi`, `icon`, `color`, `created_at`) VALUES
(1, 'Labor Animasi', 'Fasilitas lengkap untuk pembuatan animasi 2D dan 3D dengan perangkat profesional', 'fa-film', 'primary', '2026-01-09 09:29:01'),
(2, 'Labor Game', 'Studio pengembangan game dengan perangkat gaming dan development tools terlengkap', 'fa-gamepad', 'success', '2026-01-09 09:29:01'),
(3, 'Labor Audio', 'Studio rekaman dan produksi audio profesional dengan peralatan berkualitas tinggi', 'fa-volume-up', 'danger', '2026-01-09 09:29:01');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(191) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'Peminjaman Disetujui', 'Halo azmi,\n\nPeminjaman Anda telah disetujui.\n\nDetail:\n- Peralatan: -\n- Keperluan: yag\n- Penanggung Jawab Labor hari ini: admin\n\nSilakan hubungi penanggung jawab untuk koordinasi lebih lanjut.', 1, '2026-01-09 09:00:39'),
(2, 1, 'Peminjaman Disetujui', 'Halo azmi,\n\nPeminjaman Anda telah disetujui.\n\nDetail:\n- Peralatan: 1\n- Keperluan: untuk keperluan tugas\n- Penanggung Jawab Labor hari ini: admin\n\nSilakan hubungi penanggung jawab untuk koordinasi lebih lanjut.', 1, '2026-01-09 09:12:41');

-- --------------------------------------------------------

--
-- Table structure for table `notification_logs`
--

CREATE TABLE `notification_logs` (
  `id` int(11) NOT NULL,
  `notification_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `success` tinyint(1) DEFAULT 0,
  `error_message` text DEFAULT NULL,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_logs`
--

INSERT INTO `notification_logs` (`id`, `notification_id`, `user_id`, `email`, `success`, `error_message`, `sent_at`) VALUES
(1, 1, 1, 'azmi@gmail.com', 0, 'send_failed', '2026-01-09 09:00:42'),
(2, 2, 1, 'azmi@gmail.com', 0, 'send_failed', '2026-01-09 09:12:43');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `tanggung_jawab` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'Menunggu',
  `keterangan` varchar(255) DEFAULT NULL,
  `alat_id` int(11) DEFAULT NULL,
  `labor_id` int(11) DEFAULT NULL,
  `barang_labor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id`, `user_id`, `tanggal`, `tanggung_jawab`, `created_at`, `status`, `keterangan`, `alat_id`, `labor_id`, `barang_labor_id`) VALUES
(1, 1, '2026-01-13', 'yag', '2026-01-09 01:42:20', 'Disetujui', NULL, NULL, NULL, NULL),
(2, 1, '2026-01-13', 'yag', '2026-01-09 01:50:14', 'Ditolak', 'labor penuh\r\n', NULL, NULL, NULL),
(3, 1, '2026-01-13', 'yag', '2026-01-09 01:59:40', 'Disetujui', NULL, NULL, NULL, NULL),
(4, 1, '2026-01-12', 'untuk keperluan tugas', '2026-01-09 02:11:18', 'Disetujui', NULL, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `peralatan`
--

CREATE TABLE `peralatan` (
  `id` int(11) NOT NULL,
  `nama` varchar(191) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `stok` int(11) DEFAULT 1,
  `status` varchar(20) DEFAULT 'Tersedia',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peralatan`
--

INSERT INTO `peralatan` (`id`, `nama`, `deskripsi`, `stok`, `status`, `created_at`) VALUES
(1, 'VR', 'VR dapat di pinjam dengan ketentuan yang telah di tetapkan. Jika terjadi kendala terhadapsistem silahkan beritahu admin/Penanggung jawab labor hari ini.Jika terjadi kerusukan yang di sebabkan oleh peminjam maka tanggung jawab penuh di berikan kepada peminjam', 11, 'Tersedia', '2026-01-09 09:10:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `nama`, `nim`, `password`, `jurusan`, `foto`) VALUES
(1, 'azmi@gmail.com', 'azmi', '12345', '$2y$10$ctQRxeb2v2lwyu9SmOQmN.UIKWAc49yINH7Gcw05z4huBTfWNreG2', '', ''),
(4, 'azik@gmail.com', 'azik', '4567', '$2y$10$VmM7/VfQatDLPH7yUBrNweMBEchjwgR.0idyvuKptv/3Q/9Pgc1Li', NULL, 'logo unp.png'),
(5, 'azik@gmail.com', 'azik', '0852', '$2y$10$6yFAFtmBy1SUlTcQ1YDTTOnQIcZuqNeEGOJFF3A2OdE5oJb9Sjsri', NULL, 'logo unp.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `barang_labor`
--
ALTER TABLE `barang_labor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `labor_id` (`labor_id`);

--
-- Indexes for table `info_dashboard`
--
ALTER TABLE `info_dashboard`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `labor`
--
ALTER TABLE `labor`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `peralatan`
--
ALTER TABLE `peralatan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nim` (`nim`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `barang_labor`
--
ALTER TABLE `barang_labor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `info_dashboard`
--
ALTER TABLE `info_dashboard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `labor`
--
ALTER TABLE `labor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notification_logs`
--
ALTER TABLE `notification_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `peralatan`
--
ALTER TABLE `peralatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang_labor`
--
ALTER TABLE `barang_labor`
  ADD CONSTRAINT `barang_labor_ibfk_1` FOREIGN KEY (`labor_id`) REFERENCES `labor` (`id`);

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

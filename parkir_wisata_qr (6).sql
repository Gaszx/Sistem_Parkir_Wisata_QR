-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 12 Agu 2025 pada 05.18
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `parkir_wisata_qr`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_tiket`
--

CREATE TABLE `jenis_tiket` (
  `id` int(11) NOT NULL,
  `nama_jenis` varchar(50) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `loket` enum('A','B') NOT NULL,
  `durasi_jam` int(11) DEFAULT NULL COMMENT 'Durasi parkir dalam jam, NULL untuk wisata',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jenis_tiket`
--

INSERT INTO `jenis_tiket` (`id`, `nama_jenis`, `harga`, `loket`, `durasi_jam`, `created_at`) VALUES
(1, 'Parkir Motor', 3000.00, 'A', 24, '2025-07-22 10:12:37'),
(2, 'Parkir Mobil', 5000.00, 'A', 24, '2025-07-22 10:12:37'),
(3, 'Tiket Masuk Wisata', 10000.00, 'B', NULL, '2025-07-22 10:12:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_scan`
--

CREATE TABLE `log_scan` (
  `id` int(11) NOT NULL,
  `kode_tiket` varchar(20) NOT NULL,
  `jenis_scan` enum('masuk','keluar') NOT NULL,
  `waktu_scan` timestamp NOT NULL DEFAULT current_timestamp(),
  `petugas` varchar(100) DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `kode_tiket` varchar(20) NOT NULL,
  `qr_code` text NOT NULL,
  `jenis_tiket_id` int(11) NOT NULL,
  `loket` enum('A','B') NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `tanggal_masuk` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_keluar` timestamp NULL DEFAULT NULL,
  `status` enum('aktif','keluar','expired') DEFAULT 'aktif',
  `petugas_masuk` varchar(100) DEFAULT NULL,
  `petugas_keluar` varchar(100) DEFAULT NULL,
  `plat_nomor` varchar(20) DEFAULT NULL COMMENT 'Untuk kendaraan',
  `nama_pengunjung` varchar(100) DEFAULT NULL COMMENT 'Untuk wisata',
  `jumlah_pengunjung` int(11) NOT NULL DEFAULT 1,
  `denda` decimal(10,2) DEFAULT 0.00,
  `total_bayar` decimal(10,2) NOT NULL,
  `is_warga_lokal` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Bernilai 1 jika tiket untuk warga lokal (gratis)',
  `harga_manual` decimal(10,2) DEFAULT NULL COMMENT 'Harga manual yang diinput petugas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `jenis_tiket`
--
ALTER TABLE `jenis_tiket`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `log_scan`
--
ALTER TABLE `log_scan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kode_tiket` (`kode_tiket`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_tiket` (`kode_tiket`),
  ADD KEY `jenis_tiket_id` (`jenis_tiket_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `jenis_tiket`
--
ALTER TABLE `jenis_tiket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `log_scan`
--
ALTER TABLE `log_scan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `log_scan`
--
ALTER TABLE `log_scan`
  ADD CONSTRAINT `log_scan_ibfk_1` FOREIGN KEY (`kode_tiket`) REFERENCES `transaksi` (`kode_tiket`);

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`jenis_tiket_id`) REFERENCES `jenis_tiket` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

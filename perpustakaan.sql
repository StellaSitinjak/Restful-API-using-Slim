-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 17, 2020 at 05:31 AM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 7.1.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perpustakaan`
--

-- --------------------------------------------------------

--
-- Table structure for table `anggota`
--

CREATE TABLE `anggota` (
  `id` varchar(8) NOT NULL,
  `nama` varchar(40) NOT NULL,
  `alamat` varchar(64) NOT NULL,
  `no_telepon` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `anggota`
--

INSERT INTO `anggota` (`id`, `nama`, `alamat`, `no_telepon`) VALUES
('11151231', 'Test', 'asdasbnasjb', '123083294729'),
('11416013', 'Abdi Elman Daniel Aruan', 'Hinalang', '827324982347'),
('11S19028', 'James', 'Solo', '089813245768'),
('12S17027', 'Stella', 'Rumah', '085359198820'),
('31517029', 'Ruby', 'Jepang', '081197865342');

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `kode` varchar(8) NOT NULL,
  `nama` varchar(20) NOT NULL,
  `jenis` varchar(20) NOT NULL,
  `stok` int(20) NOT NULL,
  `ISBN` varchar(10) NOT NULL,
  `nama_penerbit` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`kode`, `nama`, `jenis`, `stok`, `ISBN`, `nama_penerbit`) VALUES
('0', 'Ruang Diskusi', 'Ruangan', 1, '0', '0'),
('BLTN0001', 'Buletin', 'Koran', 1, '78b977hcdi', 'Buletin'),
('HRPT0001', 'Harry Potter', 'Novel', 3, 'v69b876ggu', 'Mizan'),
('LOTR0001', 'Lord Of The Ring', 'Novel', 5, '1yr78bc10i', 'Gramedia'),
('PHPC0001', 'PHP Code', 'Pendidikan', 7, '6v7t8byn99', 'Erlangga');

-- --------------------------------------------------------

--
-- Table structure for table `pengunjung`
--

CREATE TABLE `pengunjung` (
  `id_kunjungan` int(11) NOT NULL,
  `id_anggota` varchar(8) NOT NULL,
  `nama_anggota` varchar(40) NOT NULL,
  `waktu_kedatangan` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pengunjung`
--

INSERT INTO `pengunjung` (`id_kunjungan`, `id_anggota`, `nama_anggota`, `waktu_kedatangan`) VALUES
(6, '31517029', 'Ruby', '2020-01-16 20:06:58'),
(7, '11416013', 'Abdi Elman Daniel Aruan', '2020-01-16 20:07:01'),
(8, '11S19028', 'James', '2020-01-16 20:08:51'),
(9, '11S19028', 'James', '2020-01-16 20:11:54'),
(10, '11S19028', 'James', '2020-01-16 20:13:37'),
(11, '11S19028', 'James', '2020-01-16 20:15:54'),
(12, '11416013', 'Abdi Elman Daniel Aruan', '2020-01-16 20:15:59'),
(13, '11416013', 'Abdi Elman Daniel Aruan', '2020-01-16 20:22:46'),
(14, '31517029', 'Ruby', '2020-01-16 20:26:26'),
(15, '31517029', 'Ruby', '2020-01-16 20:26:46'),
(16, '31517029', 'Ruby', '2020-01-16 20:26:55'),
(17, '11416013', 'Abdi Elman Daniel Aruan', '2020-01-16 20:36:20'),
(18, '11416013', 'Abdi Elman Daniel Aruan', '2020-01-16 20:36:52'),
(19, '11416013', 'Abdi Elman Daniel Aruan', '2020-01-16 20:38:18'),
(20, '11416013', 'Abdi Elman Daniel Aruan', '2020-01-16 20:39:31'),
(21, '11416013', 'Abdi Elman Daniel Aruan', '2020-01-16 21:08:42'),
(22, '11416013', 'Abdi Elman Daniel Aruan', '2020-01-16 21:09:52');

-- --------------------------------------------------------

--
-- Table structure for table `pustakawan`
--

CREATE TABLE `pustakawan` (
  `id` varchar(10) NOT NULL,
  `jabatan` char(20) NOT NULL,
  `nama` varchar(40) NOT NULL,
  `alamat` varchar(64) NOT NULL,
  `no_telepon` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pustakawan`
--

INSERT INTO `pustakawan` (`id`, `jabatan`, `nama`, `alamat`, `no_telepon`) VALUES
('123abc45df', 'Kepala', 'Minna', 'Jepang', '084657247913'),
('1a2b3c4d5f', 'Pegawai', 'Taehyung', 'Korea', '081157682938'),
('832fwej928', 'Pegawai', 'Phichit', 'Thailand', '083629471920');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` varchar(10) NOT NULL,
  `id_anggota` varchar(8) NOT NULL,
  `kode_buku` varchar(8) NOT NULL,
  `jenis` char(20) NOT NULL,
  `jumlah_buku` int(5) DEFAULT NULL,
  `total_denda` int(32) DEFAULT NULL,
  `tanggal_transaksi` date DEFAULT NULL,
  `tanggal_kembali` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `id_anggota`, `kode_buku`, `jenis`, `jumlah_buku`, `total_denda`, `tanggal_transaksi`, `tanggal_kembali`) VALUES
('2a78c8b0e7', '11416013', 'BLTN0001', 'Buku', 1, NULL, '2020-01-16', '2020-01-16'),
('88c11596ee', '11416013', 'PHPC0001', 'Buku', 2, NULL, '2020-01-16', '2020-01-16'),
('8b7d22996d', '11416013', 'BLTN0001', 'Buku', 1, NULL, '2020-01-16', '2020-01-16'),
('ba6661ed00', '11416013', 'HRPT0001', 'Buku', 1, NULL, '2020-01-17', '2020-01-17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`kode`);

--
-- Indexes for table `pengunjung`
--
ALTER TABLE `pengunjung`
  ADD PRIMARY KEY (`id_kunjungan`);

--
-- Indexes for table `pustakawan`
--
ALTER TABLE `pustakawan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`,`id_anggota`,`kode_buku`),
  ADD KEY `id_anggota` (`id_anggota`),
  ADD KEY `kode_buku` (`kode_buku`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pengunjung`
--
ALTER TABLE `pengunjung`
  MODIFY `id_kunjungan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `id_anggota` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id`),
  ADD CONSTRAINT `kode_buku` FOREIGN KEY (`kode_buku`) REFERENCES `buku` (`kode`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

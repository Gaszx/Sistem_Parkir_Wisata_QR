-- Create database
CREATE DATABASE IF NOT EXISTS parkir_wisata_qr;
USE parkir_wisata_qr;

-- Table untuk jenis tiket
CREATE TABLE jenis_tiket (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_jenis VARCHAR(50) NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    loket ENUM('A', 'B') NOT NULL,
    durasi_jam INT DEFAULT NULL COMMENT 'Durasi parkir dalam jam, NULL untuk wisata',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table untuk transaksi tiket
CREATE TABLE transaksi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_tiket VARCHAR(20) UNIQUE NOT NULL,
    qr_code TEXT NOT NULL,
    jenis_tiket_id INT NOT NULL,
    loket ENUM('A', 'B') NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    tanggal_masuk TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_keluar TIMESTAMP NULL,
    status ENUM('aktif', 'keluar', 'expired') DEFAULT 'aktif',
    petugas_masuk VARCHAR(100),
    petugas_keluar VARCHAR(100),
    plat_nomor VARCHAR(20) NULL COMMENT 'Untuk kendaraan',
    nama_pengunjung VARCHAR(100) NULL COMMENT 'Untuk wisata',
    denda DECIMAL(10,2) DEFAULT 0,
    total_bayar DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (jenis_tiket_id) REFERENCES jenis_tiket(id)
);

-- Table untuk log scan
CREATE TABLE log_scan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_tiket VARCHAR(20) NOT NULL,
    jenis_scan ENUM('masuk', 'keluar') NOT NULL,
    waktu_scan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    petugas VARCHAR(100),
    keterangan TEXT,
    FOREIGN KEY (kode_tiket) REFERENCES transaksi(kode_tiket)
);

-- Insert data jenis tiket
INSERT INTO jenis_tiket (nama_jenis, harga, loket, durasi_jam) VALUES
('Parkir Motor', 3000.00, 'A', 24),
('Parkir Mobil', 5000.00, 'A', 24),
('Tiket Masuk Wisata', 10000.00, 'B', NULL);

-- Database: pengaduan_sekolah_dywa

CREATE DATABASE IF NOT EXISTS pengaduan_sekolah_dywa;
USE pengaduan_sekolah_dywa;

-- Tabel Users (untuk admin dan siswa)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'siswa') NOT NULL DEFAULT 'siswa',
    kelas VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Kategori Aspirasi
CREATE TABLE kategori (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(50) NOT NULL,
    deskripsi TEXT
);

-- Tabel Aspirasi
CREATE TABLE aspirasi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    kategori_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    lokasi VARCHAR(100),
    status ENUM('pending', 'diproses', 'selesai', 'ditolak') DEFAULT 'pending',
    prioritas ENUM('rendah', 'sedang', 'tinggi') DEFAULT 'sedang',
    tanggal_pengaduan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_selesai TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE RESTRICT
);

-- Tabel Umpan Balik
CREATE TABLE umpan_balik (
    id INT PRIMARY KEY AUTO_INCREMENT,
    aspirasi_id INT NOT NULL,
    admin_id INT NOT NULL,
    keterangan TEXT NOT NULL,
    progres INT DEFAULT 0,
    tanggal_feedback TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aspirasi_id) REFERENCES aspirasi(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert data default
INSERT INTO users (username, password, nama_lengkap, email, role, kelas) VALUES
('admin', MD5('admin123'), 'Administrator', 'admin@sekolah.com', 'admin', NULL),
('siswa1', MD5('siswa123'), 'Ahmad Fauzi', 'ahmad@siswa.com', 'siswa', 'XII RPL 1'),
('siswa2', MD5('siswa123'), 'Siti Nurhaliza', 'siti@siswa.com', 'siswa', 'XII RPL 2');

INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Ruang Kelas', 'Pengaduan terkait kondisi ruang kelas'),
('Laboratorium', 'Pengaduan terkait fasilitas laboratorium'),
('Perpustakaan', 'Pengaduan terkait perpustakaan sekolah'),
('Toilet', 'Pengaduan terkait kebersihan dan kondisi toilet'),
('Lapangan', 'Pengaduan terkait lapangan olahraga'),
('Kantin', 'Pengaduan terkait kantin dan kebersihan'),
('Lainnya', 'Pengaduan lainnya');

-- Insert sample data aspirasi
INSERT INTO aspirasi (user_id, kategori_id, judul, deskripsi, lokasi, status, prioritas) VALUES
(2, 1, 'Kipas Angin Rusak', 'Kipas angin di kelas XII RPL 1 tidak berfungsi, membuat kelas sangat panas', 'Ruang XII RPL 1', 'pending', 'tinggi'),
(2, 4, 'Toilet Bocor', 'Kran air di toilet lantai 2 bocor dan membuat lantai licin', 'Toilet Lantai 2', 'diproses', 'sedang'),
(3, 3, 'Buku Kurang', 'Buku pemrograman di perpustakaan sangat terbatas', 'Perpustakaan', 'selesai', 'rendah');

-- Insert sample umpan balik
INSERT INTO umpan_balik (aspirasi_id, admin_id, keterangan, progres) VALUES
(2, 1, 'Sudah dilakukan pengecekan, akan segera diperbaiki minggu depan', 50),
(3, 1, 'Sudah dilakukan pembelian buku baru, total 10 judul pemrograman', 100);
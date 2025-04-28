-- Membuat database
CREATE DATABASE wakacao_db;
USE wakacao_db;

-- Membuat tabel pengguna
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_karyawan VARCHAR(10) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    outlet VARCHAR(50) NOT NULL,
    role ENUM('admin', 'karyawan', 'supervisor') NOT NULL DEFAULT 'karyawan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Membuat tabel shift
CREATE TABLE shift (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_shift VARCHAR(20) NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL
);

-- Membuat tabel jadwal dengan relasi yang diperbaiki
CREATE TABLE jadwal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_karyawan VARCHAR(10) NOT NULL,
    tanggal DATE NOT NULL,
    id_shift INT,
    status ENUM('masuk', 'libur', 'izin', 'sakit', 'cuti') DEFAULT 'masuk',
    FOREIGN KEY (id_karyawan) REFERENCES users(id_karyawan),
    FOREIGN KEY (id_shift) REFERENCES shift(id)
);

-- Membuat tabel absensi dengan relasi yang diperbaiki
CREATE TABLE absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_karyawan VARCHAR(10) NOT NULL,
    tanggal DATE NOT NULL,
    id_shift INT,
    check_in DATETIME,
    foto_check_in VARCHAR(255),
    check_out DATETIME,
    foto_check_out VARCHAR(255),
    status_check_in ENUM('tepat waktu', 'terlambat', 'tidak absen') DEFAULT 'tidak absen',
    status_check_out ENUM('tepat waktu', 'lebih awal', 'tidak absen') DEFAULT 'tidak absen',
    FOREIGN KEY (id_karyawan) REFERENCES users(id_karyawan),
    FOREIGN KEY (id_shift) REFERENCES shift(id)
);

-- Membuat tabel izin dengan relasi yang diperbaiki
CREATE TABLE izin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_karyawan VARCHAR(10) NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    jenis_izin ENUM('sakit', 'izin', 'cuti', 'lainnya') NOT NULL,
    keterangan TEXT,
    bukti_file VARCHAR(255),
    solusi_pengganti ENUM('shift', 'libur', 'cuti', 'gaji') NOT NULL,
    status ENUM('pending', 'disetujui', 'ditolak') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_karyawan) REFERENCES users(id_karyawan)
);

-- Membuat tabel untuk menyimpan lokasi absensi
CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    outlet_name VARCHAR(100) NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    radius INT NOT NULL DEFAULT 100,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Menambahkan kolom lokasi pada tabel absensi
ALTER TABLE absensi 
ADD COLUMN latitude_in DECIMAL(10, 8) NULL AFTER status_check_in,
ADD COLUMN longitude_in DECIMAL(11, 8) NULL AFTER latitude_in,
ADD COLUMN latitude_out DECIMAL(10, 8) NULL AFTER status_check_out,
ADD COLUMN longitude_out DECIMAL(11, 8) NULL AFTER latitude_out;

-- Data contoh untuk lokasi
INSERT INTO locations (outlet_name, latitude, longitude, radius, active) VALUES
('Wakacao Bintaro', -6.289089, 106.714976, 100, 1),
('Wakacao BSD', -6.289985, 106.664780, 80, 1);

-- Memasukkan data contoh untuk shift
INSERT INTO shift (nama_shift, jam_mulai, jam_selesai) VALUES
('Pagi', '07:00:00', '15:00:00'),
('Siang', '15:00:00', '23:00:00'),
('Malam', '23:00:00', '07:00:00');

-- Memasukkan data contoh untuk users
INSERT INTO users (id_karyawan, nama, email, password, outlet, role) VALUES
('001', 'John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bintaro', 'karyawan'),
('002', 'Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bintaro', 'karyawan'),
('003', 'Michael Johnson', 'michael@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bintaro', 'karyawan'),
('004', 'Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pusat', 'admin');

-- Gunakan tanggal dinamis untuk jadwal
-- Memasukkan data contoh untuk jadwal (hari ini + 2 hari ke depan)
INSERT INTO jadwal (id_karyawan, tanggal, id_shift, status) VALUES
-- Hari ini
('001', CURDATE(), 1, 'masuk'),
('002', CURDATE(), 2, 'masuk'),
('003', CURDATE(), 3, 'masuk'),
-- Besok
('001', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 2, 'masuk'),
('002', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 3, 'masuk'),
('003', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 1, 'masuk'),
-- Lusa
('001', DATE_ADD(CURDATE(), INTERVAL 2 DAY), 3, 'masuk'),
('002', DATE_ADD(CURDATE(), INTERVAL 2 DAY), 1, 'masuk'),
('003', DATE_ADD(CURDATE(), INTERVAL 2 DAY), 2, 'masuk');

-- Catatan: Password untuk semua pengguna adalah 'password' 
-- Buat database
CREATE DATABASE IF NOT EXISTS narcism_db;
USE narcism_db;

-- Tabel Admin
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert admin default (username: admin, password: admin123)
-- Password di-hash pakai password_hash() PHP
INSERT INTO admin (username, password, nama_lengkap) 
VALUES ('admin', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdBqBOMsqzLhH8Wy2', 'Administrator');

-- Tabel Portfolio
CREATE TABLE IF NOT EXISTS portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    gambar VARCHAR(255) NOT NULL,
    urutan INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample portfolio
INSERT INTO portfolio (judul, kategori, deskripsi, gambar) VALUES
('Kaos Komunitas Motor', 'Komunitas', 'Sablon plastisol 3 warna untuk komunitas motor', 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=400'),
('Seragam Sekolah', 'Seragam', 'Sablon rubber untuk seragam OSIS', 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400'),
('Kaos Band Merch', 'Merchandise', 'Sablon DTF full color untuk merchandise band', 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=400');

-- Tabel Produk Kaos
CREATE TABLE IF NOT EXISTS produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(50) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    harga_dasar INT NOT NULL,
    ikon VARCHAR(10) DEFAULT '👕',
    aktif TINYINT(1) DEFAULT 1,
    urutan INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample produk
INSERT INTO produk (kode, nama, deskripsi, harga_dasar, ikon) VALUES
('reguler', 'Kaos Reguler', 'Kaos standar dengan bahan cotton combed 30s', 45000, '👕'),
('boxy', 'Kaos Boxy', 'Kaos dengan potongan boxy fit yang trendy', 55000, '👔'),
('longsleeve', 'Kaos Long Sleeve', 'Kaos lengan panjang untuk gaya casual', 65000, '🧥'),
('oversize', 'Kaos Oversize', 'Kaos dengan ukuran oversize yang nyaman', 60000, '👚'),
('polo', 'Polo Shirt', 'Kaos polo dengan kerah, cocok untuk formal', 75000, '🎽'),
('raglan', 'Kaos Raglan', 'Kaos dengan lengan raglan dua warna', 58000, '🥋');

-- Tabel Tingkat Kerumitan
CREATE TABLE IF NOT EXISTS tingkat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(50) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    tambahan_harga INT NOT NULL,
    ikon VARCHAR(10) DEFAULT '🎨',
    aktif TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO tingkat (kode, nama, deskripsi, tambahan_harga, ikon) VALUES
('normal', 'Sablon Normal', 'Desain sederhana, 1-3 warna, tanpa gradasi', 15000, '🎨'),
('rumit', 'Sablon Rumit', 'Desain kompleks, banyak warna, gradasi, atau detail tinggi', 35000, '🖌️');

-- Tabel Pengaturan
CREATE TABLE IF NOT EXISTS pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_key VARCHAR(50) UNIQUE NOT NULL,
    nilai TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pengaturan (nama_key, nilai) VALUES
('whatsapp_number', '6281234567890'),
('site_name', 'Narcism Sablon'),
('min_order', '12'),
('alamat', 'Jl. Sablon No. 123, Jakarta'),
('email', 'info@narcism.com');

-- Tabel Pesanan (data diri pemesan + detail order)

CREATE TABLE IF NOT EXISTS pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan VARCHAR(30) UNIQUE NOT NULL,

    -- Data diri pemesan
    nama_pelanggan VARCHAR(100) NOT NULL,
    no_wa VARCHAR(20) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,

    -- Pengambilan / pengiriman
    metode_pengambilan ENUM('kirim','ambil') NOT NULL DEFAULT 'kirim',
    alamat TEXT DEFAULT NULL,
    kecamatan VARCHAR(100) DEFAULT NULL,
    kota VARCHAR(100) DEFAULT NULL,
    provinsi VARCHAR(100) DEFAULT NULL,
    kode_pos VARCHAR(10) DEFAULT NULL,

    -- Detail pesanan (disimpan sebagai snapshot, jadi tidak berubah
    -- walaupun admin mengubah harga/nama produk di kemudian hari)
    produk_kode VARCHAR(50) NOT NULL,
    produk_nama VARCHAR(100) NOT NULL,
    tingkat_kode VARCHAR(50) NOT NULL,
    tingkat_nama VARCHAR(100) NOT NULL,
    jumlah INT NOT NULL,
    warna_kaos VARCHAR(100) DEFAULT NULL,
    rincian_ukuran TEXT DEFAULT NULL,
    tanggal_dibutuhkan DATE DEFAULT NULL,
    catatan TEXT DEFAULT NULL,
    file_desain VARCHAR(255) DEFAULT NULL,

    -- Harga
    harga_satuan INT NOT NULL,
    subtotal INT NOT NULL,
    diskon_persen INT NOT NULL DEFAULT 0,
    diskon_nominal INT NOT NULL DEFAULT 0,
    total INT NOT NULL,

    -- Pengelolaan admin
    status ENUM('menunggu','dikonfirmasi','diproses','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
    catatan_admin TEXT DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

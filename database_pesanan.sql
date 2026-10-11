-- ============================================================
-- MIGRASI: Tabel Pesanan (fitur data diri pemesan)
-- Jalankan file ini di phpMyAdmin (tab SQL) pada database narcism_db
-- kalau kamu SUDAH import database.sql sebelumnya.
-- ============================================================
USE narcism_db;

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

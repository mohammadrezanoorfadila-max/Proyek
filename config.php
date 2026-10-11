<?php
// ============ DATABASE ============
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // XAMPP default kosong
define('DB_NAME', 'narcism_db');

// ============ SITE ============
define('SITE_NAME', 'Narcism Sablon');
define('BASE_URL', '/narcism');   // sesuaikan folder kamu

// ============ KONEKSI DATABASE ============
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// ============ HELPER PENGATURAN ============
function getSetting($key, $default = '') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT nilai FROM pengaturan WHERE nama_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['nilai'] : $default;
}

define('WHATSAPP_NUMBER', getSetting('whatsapp_number', '6281234567890'));
define('MIN_ORDER', (int) getSetting('min_order', 12));
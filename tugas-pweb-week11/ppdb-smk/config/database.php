<?php
// Koneksi ke database menggunakan PDO.
// Password bisa diset lewat environment variable DB_PASSWORD (lebih aman di server).
// Default "kopi" dipakai di XAMPP lokal — sesuaikan bila berbeda.

define('DB_HOST', 'localhost');
define('DB_NAME', 'db_ppdb_smk');
define('DB_USER', 'root');
define('DB_PASS', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST, DB_NAME, DB_CHARSET
    );
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // Jangan tampilkan detail koneksi ke browser.
        error_log('DB connection failed: ' . $e->getMessage());
        http_response_code(503);
        exit('Layanan tidak tersedia. Coba beberapa saat lagi.');
    }
    return $pdo;
}


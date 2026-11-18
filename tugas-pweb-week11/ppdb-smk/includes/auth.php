<?php
// Guard: semua halaman yang butuh session memanggil file ini di baris pertama.

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

function isLoggedIn(): bool {
    return isset($_SESSION['id_user']);
}

function requireLogin(string $redirect = '/ppdb-smk/auth/login.php'): void {
    if (!isLoggedIn()) {
        header('Location: ' . $redirect);
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: /ppdb-smk/siswa/dashboard.php');
        exit;
    }
}

function requireSiswa(): void {
    requireLogin();
    if (($_SESSION['role'] ?? '') !== 'siswa') {
        header('Location: /ppdb-smk/admin/dashboard.php');
        exit;
    }
}

function currentUser(): array {
    return [
        'id_user' => $_SESSION['id_user'] ?? null,
        'nama'    => $_SESSION['nama']    ?? '',
        'role'    => $_SESSION['role']    ?? '',
    ];
}

// Token CSRF: dibuat sekali per session, dirotasi setelah validasi.
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Permintaan tidak valid. Muat ulang halaman dan coba lagi.');
    }
    // Rotasi token setelah sukses.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function flashSet(string $key, string $msg): void {
    $_SESSION['flash'][$key] = $msg;
}

function flashGet(string $key): string {
    $msg = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $msg;
}


<?php
require_once __DIR__ . '/../includes/auth.php';

// Sudah login langsung redirect
if (isLoggedIn()) {
    $r = $_SESSION['role'] === 'admin' ? '/ppdb-smk/admin/dashboard.php' : '/ppdb-smk/siswa/dashboard.php';
    header("Location: $r");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id_user, nama, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];

            $redirect = $user['role'] === 'admin'
                ? '/ppdb-smk/admin/dashboard.php'
                : '/ppdb-smk/siswa/dashboard.php';
            header("Location: $redirect");
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    }
}

$pageTitle = 'Masuk | PPDB Online SMK Nusantara';
$bodyClass = 'auth-page';
include __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrap">
  <div class="auth-box">
    <div class="auth-logo">
      <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round">
        <path d="M5 20 24 6l19 14v22H5z"/>
        <path d="M18 42V27h12v15M11 23h26M20 14h8" stroke-linecap="round"/>
      </svg>
      <b>SMK Nusantara</b>
    </div>
    <h1>Masuk ke Akun</h1>

    <?php if ($error): ?>
      <div class="flash flash-err" role="alert"><?= e($error) ?></div>
    <?php endif ?>

    <?php $ok = flashGet('register_ok'); if ($ok): ?>
      <div class="flash flash-ok" role="alert"><?= e($ok) ?></div>
    <?php endif ?>

    <form class="form-ppdb" method="POST" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

      <div class="mb-field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control"
               value="<?= e($_POST['email'] ?? '') ?>"
               autocomplete="email" required>
      </div>
      <div class="mb-field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control"
               autocomplete="current-password" required>
      </div>

      <button type="submit" class="btn-ppdb btn-blue w-100" style="width:100%;justify-content:center">
        Masuk
      </button>
    </form>

    <p class="auth-link">Belum punya akun? <a href="/ppdb-smk/auth/register.php">Daftar di sini</a></p>
    <p class="auth-link"><a href="/ppdb-smk/">&#8592; Kembali ke beranda</a></p>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>


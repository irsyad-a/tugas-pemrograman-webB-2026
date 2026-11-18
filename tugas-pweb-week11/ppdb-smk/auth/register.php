<?php
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    header('Location: /ppdb-smk/');
    exit;
}

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $old['nama']    = trim($_POST['nama']     ?? '');
    $old['email']   = trim($_POST['email']    ?? '');
    $pw             = $_POST['password']       ?? '';
    $pw2            = $_POST['password2']      ?? '';

    if ($old['nama'] === '')  $errors[] = 'Nama lengkap wajib diisi.';
    if ($old['email'] === '') $errors[] = 'Email wajib diisi.';
    elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (strlen($pw) < 8)      $errors[] = 'Password minimal 8 karakter.';
    if ($pw !== $pw2)         $errors[] = 'Konfirmasi password tidak cocok.';

    if (empty($errors)) {
        $db   = getDB();
        $chk  = $db->prepare("SELECT id_user FROM users WHERE email = ?");
        $chk->execute([$old['email']]);
        if ($chk->fetch()) {
            $errors[] = 'Email sudah terdaftar.';
        } else {
            $hash = password_hash($pw, PASSWORD_BCRYPT, ['cost' => 12]);
            $ins  = $db->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'siswa')");
            $ins->execute([$old['nama'], $old['email'], $hash]);

            flashSet('register_ok', 'Akun berhasil dibuat. Silakan masuk.');
            header('Location: /ppdb-smk/auth/login.php');
            exit;
        }
    }
}

$pageTitle = 'Daftar Akun | PPDB Online SMK Nusantara';
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
    <h1>Buat Akun PPDB</h1>

    <?php if ($errors): ?>
      <div class="flash flash-err" role="alert">
        <?php foreach ($errors as $e): ?><div><?= e($e) ?></div><?php endforeach ?>
      </div>
    <?php endif ?>

    <form class="form-ppdb" method="POST" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

      <div class="mb-field">
        <label for="nama">Nama Lengkap</label>
        <input type="text" id="nama" name="nama" class="form-control"
               value="<?= e($old['nama'] ?? '') ?>"
               autocomplete="name" required>
      </div>
      <div class="mb-field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control"
               value="<?= e($old['email'] ?? '') ?>"
               autocomplete="email" required>
      </div>
      <div class="mb-field">
        <label for="password">Password <small style="font-weight:400;color:var(--muted)">(min. 8 karakter)</small></label>
        <input type="password" id="password" name="password" class="form-control"
               autocomplete="new-password" required minlength="8">
      </div>
      <div class="mb-field">
        <label for="password2">Konfirmasi Password</label>
        <input type="password" id="password2" name="password2" class="form-control"
               autocomplete="new-password" required minlength="8">
      </div>

      <button type="submit" class="btn-ppdb btn-blue" style="width:100%;justify-content:center">
        Buat Akun
      </button>
    </form>

    <p class="auth-link">Sudah punya akun? <a href="/ppdb-smk/auth/login.php">Masuk di sini</a></p>
    <p class="auth-link"><a href="/ppdb-smk/">&#8592; Kembali ke beranda</a></p>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>


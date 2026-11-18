<?php
require_once __DIR__ . '/../includes/auth.php';
requireSiswa();

$db   = getDB();
$user = currentUser();
$errors = [];
$old    = [];

// Ambil biodata yang sudah ada
$stmt = $db->prepare("SELECT * FROM siswa WHERE id_user = ?");
$stmt->execute([$user['id_user']]);
$bio  = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $fields = ['nisn','nama_lengkap','jenis_kelamin','tempat_lahir','tanggal_lahir','agama','alamat','asal_sekolah','no_hp'];
    foreach ($fields as $f) {
        $old[$f] = trim($_POST[$f] ?? '');
    }

    if ($old['nama_lengkap'] === '')  $errors[] = 'Nama lengkap wajib diisi.';
    if ($old['jenis_kelamin'] === '') $errors[] = 'Jenis kelamin wajib dipilih.';

    // Validasi NISN unik (kecuali milik sendiri)
    if ($old['nisn'] !== '') {
        $chk = $db->prepare("SELECT id_siswa FROM siswa WHERE nisn = ? AND id_user != ?");
        $chk->execute([$old['nisn'], $user['id_user']]);
        if ($chk->fetch()) $errors[] = 'NISN sudah digunakan akun lain.';
    }

    if (empty($errors)) {
        if ($bio) {
            $upd = $db->prepare("
                UPDATE siswa SET nisn=?, nama_lengkap=?, jenis_kelamin=?,
                  tempat_lahir=?, tanggal_lahir=?, agama=?, alamat=?,
                  asal_sekolah=?, no_hp=?
                WHERE id_user=?
            ");
            $upd->execute([
                $old['nisn'] ?: null,
                $old['nama_lengkap'], $old['jenis_kelamin'],
                $old['tempat_lahir'] ?: null,
                $old['tanggal_lahir'] ?: null,
                $old['agama'] ?: null,
                $old['alamat'] ?: null,
                $old['asal_sekolah'] ?: null,
                $old['no_hp'] ?: null,
                $user['id_user'],
            ]);
        } else {
            $ins = $db->prepare("
                INSERT INTO siswa (id_user, nisn, nama_lengkap, jenis_kelamin,
                  tempat_lahir, tanggal_lahir, agama, alamat, asal_sekolah, no_hp)
                VALUES (?,?,?,?,?,?,?,?,?,?)
            ");
            $ins->execute([
                $user['id_user'],
                $old['nisn'] ?: null,
                $old['nama_lengkap'], $old['jenis_kelamin'],
                $old['tempat_lahir'] ?: null,
                $old['tanggal_lahir'] ?: null,
                $old['agama'] ?: null,
                $old['alamat'] ?: null,
                $old['asal_sekolah'] ?: null,
                $old['no_hp'] ?: null,
            ]);
        }
        flashSet('ok', 'Biodata berhasil disimpan.');
        header('Location: /ppdb-smk/siswa/biodata.php');
        exit;
    }
    // Jika error, tampilkan nilai yang baru dipost
} else {
    // Isi dari database kalau sudah ada
    if ($bio) {
        $old = $bio;
    }
}

$agamaOpts = ['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'];
$jkOpts    = ['Laki-laki','Perempuan'];

$pageTitle = 'Biodata | PPDB Online SMK Nusantara';
include __DIR__ . '/../includes/header.php';
$flash_ok  = flashGet('ok');
$flash_err = flashGet('err');
?>

<header class="site-header">
  <div class="nav-wrap">
    <a class="brand" href="/ppdb-smk/"><span class="brand-mark" aria-hidden="true">
      <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round">
        <path d="M5 20 24 6l19 14v22H5z"/><path d="M18 42V27h12v15M11 23h26" stroke-linecap="round"/>
      </svg></span><span>SMK Nusantara</span>
    </a>
    <a class="nav-cta" href="/ppdb-smk/auth/logout.php">Keluar</a>
  </div>
</header>

<div class="dashboard-wrap">
  <aside class="sidebar" aria-label="Menu siswa">
    <div class="sidebar-brand">
      <div style="font-size:36px">&#x1F393;</div>
      <b><?= e($user['nama']) ?></b><small>Calon Siswa</small>
    </div>
    <ul class="sidebar-nav">
      <li><a href="/ppdb-smk/siswa/dashboard.php"><i class="nav-icon">&#x25A0;</i>Dashboard</a></li>
      <li><a href="/ppdb-smk/siswa/biodata.php" class="active"><i class="nav-icon">&#x270D;</i>Biodata</a></li>
      <li><a href="/ppdb-smk/siswa/pendaftaran.php"><i class="nav-icon">&#x270E;</i>Pendaftaran</a></li>
      <li><a href="/ppdb-smk/siswa/status.php"><i class="nav-icon">&#x25CE;</i>Status</a></li>
      <li><a href="/ppdb-smk/auth/logout.php"><i class="nav-icon">&#x21B0;</i>Keluar</a></li>
    </ul>
  </aside>

  <main>
    <h1 class="page-title">Biodata Calon Siswa</h1>

    <?php if ($flash_ok):  ?><div class="flash flash-ok"  role="alert"><?= e($flash_ok)  ?></div><?php endif ?>
    <?php if ($flash_err): ?><div class="flash flash-err" role="alert"><?= e($flash_err) ?></div><?php endif ?>

    <?php if ($errors): ?>
      <div class="flash flash-err" role="alert">
        <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach ?>
      </div>
    <?php endif ?>

    <div class="card-ppdb">
      <form class="form-ppdb" method="POST" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div class="mb-field">
            <label for="nama_lengkap">Nama Lengkap <span style="color:#c00">*</span></label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control"
                   value="<?= e($old['nama_lengkap'] ?? '') ?>" required>
          </div>
          <div class="mb-field">
            <label for="nisn">NISN</label>
            <input type="text" id="nisn" name="nisn" class="form-control"
                   value="<?= e($old['nisn'] ?? '') ?>"
                   pattern="\d{10}" maxlength="10" inputmode="numeric"
                   placeholder="10 digit">
          </div>
          <div class="mb-field">
            <label for="jenis_kelamin">Jenis Kelamin <span style="color:#c00">*</span></label>
            <select id="jenis_kelamin" name="jenis_kelamin" class="form-control" required>
              <option value="">-- Pilih --</option>
              <?php foreach ($jkOpts as $jk): ?>
                <option value="<?= e($jk) ?>" <?= ($old['jenis_kelamin'] ?? '') === $jk ? 'selected' : '' ?>>
                  <?= e($jk) ?>
                </option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="mb-field">
            <label for="agama">Agama</label>
            <select id="agama" name="agama" class="form-control">
              <option value="">-- Pilih --</option>
              <?php foreach ($agamaOpts as $ag): ?>
                <option value="<?= e($ag) ?>" <?= ($old['agama'] ?? '') === $ag ? 'selected' : '' ?>>
                  <?= e($ag) ?>
                </option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="mb-field">
            <label for="tempat_lahir">Tempat Lahir</label>
            <input type="text" id="tempat_lahir" name="tempat_lahir" class="form-control"
                   value="<?= e($old['tempat_lahir'] ?? '') ?>">
          </div>
          <div class="mb-field">
            <label for="tanggal_lahir">Tanggal Lahir</label>
            <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control"
                   value="<?= e($old['tanggal_lahir'] ?? '') ?>">
          </div>
          <div class="mb-field">
            <label for="asal_sekolah">Asal Sekolah</label>
            <input type="text" id="asal_sekolah" name="asal_sekolah" class="form-control"
                   value="<?= e($old['asal_sekolah'] ?? '') ?>">
          </div>
          <div class="mb-field">
            <label for="no_hp">Nomor HP</label>
            <input type="tel" id="no_hp" name="no_hp" class="form-control"
                   value="<?= e($old['no_hp'] ?? '') ?>" inputmode="tel">
          </div>
        </div>

        <div class="mb-field">
          <label for="alamat">Alamat Lengkap</label>
          <textarea id="alamat" name="alamat" class="form-control" rows="3"><?= e($old['alamat'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn-ppdb btn-blue">Simpan Biodata</button>
      </form>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>


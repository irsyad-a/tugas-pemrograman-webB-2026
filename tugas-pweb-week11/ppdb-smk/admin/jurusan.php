<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db   = getDB();
$user = currentUser();
$errors = [];

// Tambah jurusan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    verifyCsrf();
    $kode  = strtoupper(trim($_POST['kode_jurusan'] ?? ''));
    $nama  = trim($_POST['nama_jurusan'] ?? '');
    $kuota = (int)($_POST['kuota'] ?? 0);
    $desc  = trim($_POST['deskripsi'] ?? '');

    if ($kode === '')  $errors[] = 'Kode jurusan wajib diisi.';
    if ($nama === '')  $errors[] = 'Nama jurusan wajib diisi.';
    if ($kuota <= 0)   $errors[] = 'Kuota harus lebih dari 0.';

    if (empty($errors)) {
        $ins = $db->prepare("INSERT INTO jurusan (kode_jurusan, nama_jurusan, kuota, deskripsi) VALUES (?,?,?,?)");
        $ins->execute([$kode, $nama, $kuota, $desc ?: null]);
        flashSet('ok', 'Jurusan berhasil ditambahkan.');
        header('Location: /ppdb-smk/admin/jurusan.php');
        exit;
    }
}

// Toggle aktif/nonaktif
if (isset($_GET['toggle'])) {
    $id  = (int)$_GET['toggle'];
    $upd = $db->prepare("UPDATE jurusan SET aktif = NOT aktif WHERE id_jurusan = ?");
    $upd->execute([$id]);
    header('Location: /ppdb-smk/admin/jurusan.php');
    exit;
}

$jurusanList = $db->query("SELECT * FROM jurusan ORDER BY kode_jurusan")->fetchAll();

$pageTitle = 'Kelola Jurusan | PPDB Online SMK Nusantara';
include __DIR__ . '/../includes/header.php';
$flash_ok  = flashGet('ok');
$flash_err = flashGet('err');
?>

<header class="site-header">
  <div class="nav-wrap">
    <a class="brand" href="/ppdb-smk/admin/dashboard.php"><span class="brand-mark" aria-hidden="true">
      <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round">
        <path d="M5 20 24 6l19 14v22H5z"/><path d="M18 42V27h12v15M11 23h26" stroke-linecap="round"/>
      </svg></span><span>SMK Nusantara</span>
    </a>
    <a class="nav-cta" href="/ppdb-smk/auth/logout.php">Keluar</a>
  </div>
</header>

<div class="dashboard-wrap">
  <aside class="sidebar" aria-label="Menu admin">
    <div class="sidebar-brand">
      <div style="font-size:36px">&#x1F4CB;</div>
      <b><?= e($user['nama']) ?></b><small>Administrator</small>
    </div>
    <ul class="sidebar-nav">
      <li><a href="/ppdb-smk/admin/dashboard.php"><i class="nav-icon">&#x25A0;</i>Dashboard</a></li>
      <li><a href="/ppdb-smk/admin/siswa.php"><i class="nav-icon">&#x1F465;</i>Data Siswa</a></li>
      <li><a href="/ppdb-smk/admin/jurusan.php" class="active"><i class="nav-icon">&#x1F4DA;</i>Jurusan</a></li>
      <li><a href="/ppdb-smk/admin/verifikasi.php"><i class="nav-icon">&#x2713;</i>Verifikasi</a></li>
      <li><a href="/ppdb-smk/auth/logout.php"><i class="nav-icon">&#x21B0;</i>Keluar</a></li>
    </ul>
  </aside>

  <main>
    <h1 class="page-title">Kelola Jurusan</h1>

    <?php if ($flash_ok):  ?><div class="flash flash-ok"  role="alert"><?= e($flash_ok)  ?></div><?php endif ?>
    <?php if ($flash_err): ?><div class="flash flash-err" role="alert"><?= e($flash_err) ?></div><?php endif ?>

    <?php if ($errors): ?>
      <div class="flash flash-err" role="alert">
        <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach ?>
      </div>
    <?php endif ?>

    <!-- Form tambah -->
    <div class="card-ppdb">
      <h3>Tambah Jurusan Baru</h3>
      <form class="form-ppdb" method="POST" novalidate
            style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="tambah" value="1">
        <div class="mb-field">
          <label for="kode_jurusan">Kode Jurusan <span style="color:#c00">*</span></label>
          <input type="text" id="kode_jurusan" name="kode_jurusan" class="form-control"
                 value="<?= e($_POST['kode_jurusan'] ?? '') ?>" maxlength="8" required>
        </div>
        <div class="mb-field">
          <label for="nama_jurusan">Nama Jurusan <span style="color:#c00">*</span></label>
          <input type="text" id="nama_jurusan" name="nama_jurusan" class="form-control"
                 value="<?= e($_POST['nama_jurusan'] ?? '') ?>" required>
        </div>
        <div class="mb-field">
          <label for="kuota">Kuota <span style="color:#c00">*</span></label>
          <input type="number" id="kuota" name="kuota" class="form-control"
                 value="<?= e($_POST['kuota'] ?? '30') ?>" min="1" max="500" required>
        </div>
        <div class="mb-field">
          <label for="deskripsi">Deskripsi</label>
          <input type="text" id="deskripsi" name="deskripsi" class="form-control"
                 value="<?= e($_POST['deskripsi'] ?? '') ?>">
        </div>
        <div style="grid-column:1/-1">
          <button type="submit" class="btn-ppdb btn-gold">Tambah Jurusan</button>
        </div>
      </form>
    </div>

    <!-- Daftar jurusan -->
    <div class="card-ppdb" style="overflow-x:auto">
      <h3>Daftar Jurusan</h3>
      <table class="tbl-ppdb">
        <thead><tr><th>Kode</th><th>Nama Jurusan</th><th>Kuota</th><th>Deskripsi</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($jurusanList as $j): ?>
          <tr>
            <td><b><?= e($j['kode_jurusan']) ?></b></td>
            <td><?= e($j['nama_jurusan']) ?></td>
            <td><?= (int)$j['kuota'] ?></td>
            <td style="font-size:12px;color:var(--muted)"><?= e($j['deskripsi'] ?? '-') ?></td>
            <td>
              <?php if ($j['aktif']): ?>
                <span class="badge-ppdb badge-diterima">Aktif</span>
              <?php else: ?>
                <span class="badge-ppdb badge-ditolak">Nonaktif</span>
              <?php endif ?>
            </td>
            <td>
              <a href="?toggle=<?= (int)$j['id_jurusan'] ?>"
                 class="btn-ppdb btn-outline"
                 style="padding:5px 10px;font-size:11px"
                 data-confirm="<?= e($j['aktif'] ? 'Nonaktifkan jurusan ini?' : 'Aktifkan jurusan ini?') ?>">
                <?= $j['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
              </a>
            </td>
          </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>


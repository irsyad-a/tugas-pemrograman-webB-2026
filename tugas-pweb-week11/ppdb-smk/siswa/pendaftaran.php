<?php
require_once __DIR__ . '/../includes/auth.php';
requireSiswa();

$db   = getDB();
$user = currentUser();

// Pastikan biodata sudah ada
$bio = $db->prepare("SELECT * FROM siswa WHERE id_user = ?");
$bio->execute([$user['id_user']]);
$biodata = $bio->fetch();

if (!$biodata) {
    flashSet('err', 'Lengkapi biodata terlebih dahulu sebelum mendaftar.');
    header('Location: /ppdb-smk/siswa/biodata.php');
    exit;
}

// Cek sudah daftar tahun ini?
$chk = $db->prepare("SELECT id_pendaftaran FROM pendaftaran WHERE id_siswa = ? AND tahun_ajaran = '2026/2027'");
$chk->execute([$biodata['id_siswa']]);
$sudahDaftar = $chk->fetch();

$jurusanList = $db->query("SELECT id_jurusan, kode_jurusan, nama_jurusan, kuota FROM jurusan WHERE aktif = 1 ORDER BY kode_jurusan")->fetchAll();

$errors = [];

if (!$sudahDaftar && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $id_jurusan = (int)($_POST['id_jurusan'] ?? 0);
    if ($id_jurusan <= 0) {
        $errors[] = 'Pilih jurusan terlebih dahulu.';
    } else {
        // Generate no. pendaftaran: PPDB-{tahun}-{id_siswa padded}
        $noPend = sprintf('PPDB-2026-%05d', $biodata['id_siswa']);

        $ins = $db->prepare("
            INSERT INTO pendaftaran (no_pendaftaran, id_siswa, id_jurusan, tahun_ajaran, status)
            VALUES (?, ?, ?, '2026/2027', 'Menunggu')
        ");
        $ins->execute([$noPend, $biodata['id_siswa'], $id_jurusan]);

        $id_pend = $db->lastInsertId();

        // Catat riwayat awal
        $rw = $db->prepare("
            INSERT INTO riwayat_status (id_pendaftaran, status_sebelumnya, status_baru, catatan)
            VALUES (?, NULL, 'Menunggu', 'Pendaftaran baru diajukan.')
        ");
        $rw->execute([$id_pend]);

        flashSet('ok', "Pendaftaran berhasil. Nomor pendaftaran Anda: $noPend");
        header('Location: /ppdb-smk/siswa/status.php');
        exit;
    }
}

$pageTitle = 'Pendaftaran | PPDB Online SMK Nusantara';
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
      <li><a href="/ppdb-smk/siswa/biodata.php"><i class="nav-icon">&#x270D;</i>Biodata</a></li>
      <li><a href="/ppdb-smk/siswa/pendaftaran.php" class="active"><i class="nav-icon">&#x270E;</i>Pendaftaran</a></li>
      <li><a href="/ppdb-smk/siswa/status.php"><i class="nav-icon">&#x25CE;</i>Status</a></li>
      <li><a href="/ppdb-smk/auth/logout.php"><i class="nav-icon">&#x21B0;</i>Keluar</a></li>
    </ul>
  </aside>

  <main>
    <h1 class="page-title">Formulir Pendaftaran</h1>

    <?php if ($flash_ok):  ?><div class="flash flash-ok"  role="alert"><?= e($flash_ok)  ?></div><?php endif ?>
    <?php if ($flash_err): ?><div class="flash flash-err" role="alert"><?= e($flash_err) ?></div><?php endif ?>

    <?php if ($sudahDaftar): ?>
      <div class="card-ppdb">
        <p style="font-size:14px;color:var(--muted)">
          Anda sudah mengajukan pendaftaran untuk tahun ajaran 2026/2027.
          Pantau statusnya di halaman <a href="/ppdb-smk/siswa/status.php">Status Pendaftaran</a>.
        </p>
      </div>
    <?php else: ?>

      <?php if ($errors): ?>
        <div class="flash flash-err" role="alert">
          <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach ?>
        </div>
      <?php endif ?>

      <!-- Ringkasan biodata -->
      <div class="card-ppdb">
        <h3>Ringkasan Biodata</h3>
        <table class="tbl-ppdb">
          <tr><th style="width:160px">Nama</th><td><?= e($biodata['nama_lengkap']) ?></td></tr>
          <tr><th>NISN</th><td><?= e($biodata['nisn'] ?? '-') ?></td></tr>
          <tr><th>Jenis Kelamin</th><td><?= e($biodata['jenis_kelamin']) ?></td></tr>
          <tr><th>Asal Sekolah</th><td><?= e($biodata['asal_sekolah'] ?? '-') ?></td></tr>
        </table>
        <a href="/ppdb-smk/siswa/biodata.php" style="font-size:12px;color:var(--blue);display:block;margin-top:8px">Edit biodata</a>
      </div>

      <!-- Form pilih jurusan -->
      <div class="card-ppdb">
        <h3>Pilih Program Keahlian</h3>
        <form class="form-ppdb" method="POST" novalidate>
          <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

          <div class="mb-field">
            <label for="id_jurusan">Jurusan Pilihan <span style="color:#c00">*</span></label>
            <select id="id_jurusan" name="id_jurusan" class="form-control" required>
              <option value="">-- Pilih jurusan --</option>
              <?php foreach ($jurusanList as $j): ?>
                <option value="<?= (int)$j['id_jurusan'] ?>"
                  <?= ((int)($_POST['id_jurusan'] ?? 0)) === (int)$j['id_jurusan'] ? 'selected' : '' ?>>
                  <?= e($j['kode_jurusan']) ?> — <?= e($j['nama_jurusan']) ?> (Kuota: <?= (int)$j['kuota'] ?>)
                </option>
              <?php endforeach ?>
            </select>
          </div>

          <button type="submit" class="btn-ppdb btn-gold">Kirim Pendaftaran</button>
        </form>
      </div>
    <?php endif ?>
  </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>


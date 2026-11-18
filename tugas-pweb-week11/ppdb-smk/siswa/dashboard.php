<?php
require_once __DIR__ . '/../includes/auth.php';
requireSiswa();

$db     = getDB();
$user   = currentUser();

// Data biodata
$bio = $db->prepare("SELECT * FROM siswa WHERE id_user = ?");
$bio->execute([$user['id_user']]);
$biodata = $bio->fetch();

// Data pendaftaran aktif
$pend = null;
if ($biodata) {
    $ps = $db->prepare("
        SELECT p.*, j.nama_jurusan, j.kode_jurusan
        FROM pendaftaran p
        JOIN jurusan j ON j.id_jurusan = p.id_jurusan
        WHERE p.id_siswa = ? AND p.tahun_ajaran = '2026/2027'
    ");
    $ps->execute([$biodata['id_siswa']]);
    $pend = $ps->fetch();
}

$pageTitle = 'Dashboard Siswa | PPDB Online SMK Nusantara';
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
    <span style="color:#e8f5ff;font-size:13px">Halo, <?= e($user['nama']) ?></span>
    <a class="nav-cta" href="/ppdb-smk/auth/logout.php">Keluar</a>
  </div>
</header>

<div class="dashboard-wrap">
  <!-- Sidebar -->
  <aside class="sidebar" aria-label="Menu siswa">
    <div class="sidebar-brand">
      <div style="font-size:36px">&#x1F393;</div>
      <b><?= e($user['nama']) ?></b>
      <small>Calon Siswa</small>
    </div>
    <ul class="sidebar-nav">
      <li><a href="/ppdb-smk/siswa/dashboard.php" class="active"><i class="nav-icon">&#x25A0;</i>Dashboard</a></li>
      <li><a href="/ppdb-smk/siswa/biodata.php"><i class="nav-icon">&#x270D;</i>Biodata</a></li>
      <li><a href="/ppdb-smk/siswa/pendaftaran.php"><i class="nav-icon">&#x270E;</i>Pendaftaran</a></li>
      <li><a href="/ppdb-smk/siswa/status.php"><i class="nav-icon">&#x25CE;</i>Status</a></li>
      <li><a href="/ppdb-smk/auth/logout.php"><i class="nav-icon">&#x21B0;</i>Keluar</a></li>
    </ul>
  </aside>

  <!-- Konten utama -->
  <main>
    <h1 class="page-title">Dashboard Siswa</h1>

    <?php if ($flash_ok):  ?><div class="flash flash-ok"  role="alert"><?= e($flash_ok)  ?></div><?php endif ?>
    <?php if ($flash_err): ?><div class="flash flash-err" role="alert"><?= e($flash_err) ?></div><?php endif ?>

    <!-- Status pendaftaran -->
    <div class="card-ppdb">
      <h3>Status Pendaftaran</h3>
      <?php if ($pend): ?>
        <table class="tbl-ppdb">
          <tr><th style="width:180px">No. Pendaftaran</th><td><?= e($pend['no_pendaftaran']) ?></td></tr>
          <tr><th>Jurusan</th><td><?= e($pend['kode_jurusan']) ?> — <?= e($pend['nama_jurusan']) ?></td></tr>
          <tr><th>Tanggal Daftar</th><td><?= e(date('d M Y', strtotime($pend['tanggal_daftar']))) ?></td></tr>
          <tr><th>Status</th><td>
            <span class="badge-ppdb badge-<?= strtolower(str_replace(' ','',$pend['status'])) ?>">
              <?= e($pend['status']) ?>
            </span>
          </td></tr>
        </table>
      <?php elseif ($biodata): ?>
        <p style="color:var(--muted);font-size:14px">Biodata sudah diisi. Lanjutkan ke halaman <a href="/ppdb-smk/siswa/pendaftaran.php">Pendaftaran</a>.</p>
      <?php else: ?>
        <p style="color:var(--muted);font-size:14px">Lengkapi <a href="/ppdb-smk/siswa/biodata.php">biodata</a> terlebih dahulu sebelum mendaftar.</p>
      <?php endif ?>
    </div>

    <!-- Jadwal singkat -->
    <div class="card-ppdb">
      <h3>Jadwal PPDB 2026/2027</h3>
      <div class="schedule" style="margin:0;padding:12px 16px">
        <div class="timeline" style="flex:1">
          <div class="timeline-item"><i class="dot dot-blue"></i><b>Pendaftaran</b><small>1–20 Jun 2026</small></div>
          <div class="timeline-item"><i class="dot dot-green"></i><b>Verifikasi</b><small>1–22 Jun 2026</small></div>
          <div class="timeline-item"><i class="dot dot-orange"></i><b>Seleksi</b><small>23–25 Jun 2026</small></div>
          <div class="timeline-item"><i class="dot dot-pink"></i><b>Pengumuman</b><small>26 Jun 2026</small></div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>


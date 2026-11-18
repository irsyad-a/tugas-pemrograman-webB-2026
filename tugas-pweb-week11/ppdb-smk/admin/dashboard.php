<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db   = getDB();
$user = currentUser();

// Statistik ringkasan
$total     = $db->query("SELECT COUNT(*) FROM pendaftaran WHERE tahun_ajaran='2026/2027'")->fetchColumn();
$menunggu  = $db->query("SELECT COUNT(*) FROM pendaftaran WHERE tahun_ajaran='2026/2027' AND status='Menunggu'")->fetchColumn();
$diterima  = $db->query("SELECT COUNT(*) FROM pendaftaran WHERE tahun_ajaran='2026/2027' AND status='Diterima'")->fetchColumn();
$ditolak   = $db->query("SELECT COUNT(*) FROM pendaftaran WHERE tahun_ajaran='2026/2027' AND status='Ditolak'")->fetchColumn();

// 5 pendaftar terbaru
$terbaru = $db->query("
    SELECT p.no_pendaftaran, s.nama_lengkap, j.kode_jurusan, p.status, p.tanggal_daftar
    FROM pendaftaran p
    JOIN siswa   s ON s.id_siswa   = p.id_siswa
    JOIN jurusan j ON j.id_jurusan = p.id_jurusan
    WHERE p.tahun_ajaran = '2026/2027'
    ORDER BY p.tanggal_daftar DESC
    LIMIT 5
")->fetchAll();

$pageTitle = 'Dashboard Admin | PPDB Online SMK Nusantara';
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
    <span style="color:#e8f5ff;font-size:13px">Admin: <?= e($user['nama']) ?></span>
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
      <li><a href="/ppdb-smk/admin/dashboard.php" class="active"><i class="nav-icon">&#x25A0;</i>Dashboard</a></li>
      <li><a href="/ppdb-smk/admin/siswa.php"><i class="nav-icon">&#x1F465;</i>Data Siswa</a></li>
      <li><a href="/ppdb-smk/admin/jurusan.php"><i class="nav-icon">&#x1F4DA;</i>Jurusan</a></li>
      <li><a href="/ppdb-smk/admin/verifikasi.php"><i class="nav-icon">&#x2713;</i>Verifikasi</a></li>
      <li><a href="/ppdb-smk/auth/logout.php"><i class="nav-icon">&#x21B0;</i>Keluar</a></li>
    </ul>
  </aside>

  <main>
    <h1 class="page-title">Dashboard Admin</h1>

    <?php if ($flash_ok):  ?><div class="flash flash-ok"  role="alert"><?= e($flash_ok)  ?></div><?php endif ?>
    <?php if ($flash_err): ?><div class="flash flash-err" role="alert"><?= e($flash_err) ?></div><?php endif ?>

    <!-- Statistik -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-num"><?= (int)$total ?></div>
        <div class="stat-lbl">Total Pendaftar</div>
      </div>
      <div class="stat-card">
        <div class="stat-num" style="color:#d97706"><?= (int)$menunggu ?></div>
        <div class="stat-lbl">Menunggu Verifikasi</div>
      </div>
      <div class="stat-card">
        <div class="stat-num" style="color:#059669"><?= (int)$diterima ?></div>
        <div class="stat-lbl">Diterima</div>
      </div>
      <div class="stat-card">
        <div class="stat-num" style="color:#dc2626"><?= (int)$ditolak ?></div>
        <div class="stat-lbl">Ditolak</div>
      </div>
    </div>

    <!-- Pendaftar terbaru -->
    <div class="card-ppdb">
      <h3>5 Pendaftar Terbaru</h3>
      <?php if ($terbaru): ?>
        <table class="tbl-ppdb">
          <thead>
            <tr><th>No. Pendaftaran</th><th>Nama</th><th>Jurusan</th><th>Status</th><th>Tanggal</th></tr>
          </thead>
          <tbody>
            <?php foreach ($terbaru as $t): ?>
            <tr>
              <td><?= e($t['no_pendaftaran']) ?></td>
              <td><?= e($t['nama_lengkap']) ?></td>
              <td><?= e($t['kode_jurusan']) ?></td>
              <td><span class="badge-ppdb badge-<?= strtolower(str_replace(' ','',$t['status'])) ?>"><?= e($t['status']) ?></span></td>
              <td><?= e(date('d M Y', strtotime($t['tanggal_daftar']))) ?></td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>
        <a href="/ppdb-smk/admin/verifikasi.php" style="font-size:13px;color:var(--blue);display:inline-block;margin-top:10px">Lihat semua pendaftaran</a>
      <?php else: ?>
        <p style="font-size:14px;color:var(--muted)">Belum ada pendaftar untuk tahun ajaran 2026/2027.</p>
      <?php endif ?>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>


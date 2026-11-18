<?php
require_once __DIR__ . '/../includes/auth.php';
requireSiswa();

$db   = getDB();
$user = currentUser();

$bio = $db->prepare("SELECT id_siswa FROM siswa WHERE id_user = ?");
$bio->execute([$user['id_user']]);
$biodata = $bio->fetch();

$pend    = null;
$riwayat = [];

if ($biodata) {
    $ps = $db->prepare("
        SELECT p.*, j.nama_jurusan, j.kode_jurusan
        FROM pendaftaran p
        JOIN jurusan j ON j.id_jurusan = p.id_jurusan
        WHERE p.id_siswa = ? AND p.tahun_ajaran = '2026/2027'
    ");
    $ps->execute([$biodata['id_siswa']]);
    $pend = $ps->fetch();

    if ($pend) {
        $rw = $db->prepare("
            SELECT * FROM riwayat_status
            WHERE id_pendaftaran = ?
            ORDER BY tanggal_perubahan ASC
        ");
        $rw->execute([$pend['id_pendaftaran']]);
        $riwayat = $rw->fetchAll();
    }
}

$pageTitle = 'Status Pendaftaran | PPDB Online SMK Nusantara';
include __DIR__ . '/../includes/header.php';
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
      <li><a href="/ppdb-smk/siswa/pendaftaran.php"><i class="nav-icon">&#x270E;</i>Pendaftaran</a></li>
      <li><a href="/ppdb-smk/siswa/status.php" class="active"><i class="nav-icon">&#x25CE;</i>Status</a></li>
      <li><a href="/ppdb-smk/auth/logout.php"><i class="nav-icon">&#x21B0;</i>Keluar</a></li>
    </ul>
  </aside>

  <main>
    <h1 class="page-title">Status Pendaftaran</h1>

    <?php if (!$pend): ?>
      <div class="card-ppdb">
        <p style="font-size:14px;color:var(--muted)">
          Belum ada pendaftaran. Silakan isi
          <a href="/ppdb-smk/siswa/biodata.php">biodata</a> lalu lanjut ke
          <a href="/ppdb-smk/siswa/pendaftaran.php">pendaftaran</a>.
        </p>
      </div>
    <?php else: ?>
      <div class="card-ppdb">
        <h3>Detail Pendaftaran</h3>
        <table class="tbl-ppdb">
          <tr><th style="width:180px">No. Pendaftaran</th><td><b><?= e($pend['no_pendaftaran']) ?></b></td></tr>
          <tr><th>Jurusan</th><td><?= e($pend['kode_jurusan']) ?> — <?= e($pend['nama_jurusan']) ?></td></tr>
          <tr><th>Tahun Ajaran</th><td><?= e($pend['tahun_ajaran']) ?></td></tr>
          <tr><th>Tanggal Daftar</th><td><?= e(date('d M Y, H:i', strtotime($pend['tanggal_daftar']))) ?></td></tr>
          <tr><th>Status Saat Ini</th>
            <td>
              <span class="badge-ppdb badge-<?= strtolower(str_replace(' ','',$pend['status'])) ?>">
                <?= e($pend['status']) ?>
              </span>
            </td>
          </tr>
        </table>
      </div>

      <?php if ($riwayat): ?>
      <div class="card-ppdb">
        <h3>Riwayat Status</h3>
        <table class="tbl-ppdb">
          <thead><tr><th>Tanggal</th><th>Status</th><th>Catatan</th></tr></thead>
          <tbody>
            <?php foreach ($riwayat as $r): ?>
            <tr>
              <td><?= e(date('d M Y, H:i', strtotime($r['tanggal_perubahan']))) ?></td>
              <td><span class="badge-ppdb badge-<?= strtolower(str_replace(' ','',$r['status_baru'])) ?>"><?= e($r['status_baru']) ?></span></td>
              <td><?= e($r['catatan'] ?? '-') ?></td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
      <?php endif ?>
    <?php endif ?>
  </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>


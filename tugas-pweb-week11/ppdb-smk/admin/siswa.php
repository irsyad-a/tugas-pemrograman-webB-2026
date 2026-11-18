<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db   = getDB();
$user = currentUser();

// Cari siswa
$q   = trim($_GET['q'] ?? '');
$sql = "
    SELECT s.id_siswa, s.nisn, s.nama_lengkap, s.jenis_kelamin, s.asal_sekolah,
           u.email,
           (SELECT status FROM pendaftaran p WHERE p.id_siswa = s.id_siswa AND p.tahun_ajaran='2026/2027' LIMIT 1) AS status_pend
    FROM siswa s
    JOIN users u ON u.id_user = s.id_user
";
$params = [];
if ($q !== '') {
    $sql    .= " WHERE s.nama_lengkap LIKE ? OR s.nisn LIKE ? OR u.email LIKE ?";
    $like    = '%' . $q . '%';
    $params  = [$like, $like, $like];
}
$sql .= " ORDER BY s.nama_lengkap";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$siswaList = $stmt->fetchAll();

$pageTitle = 'Data Siswa | PPDB Online SMK Nusantara';
include __DIR__ . '/../includes/header.php';
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
      <li><a href="/ppdb-smk/admin/siswa.php" class="active"><i class="nav-icon">&#x1F465;</i>Data Siswa</a></li>
      <li><a href="/ppdb-smk/admin/jurusan.php"><i class="nav-icon">&#x1F4DA;</i>Jurusan</a></li>
      <li><a href="/ppdb-smk/admin/verifikasi.php"><i class="nav-icon">&#x2713;</i>Verifikasi</a></li>
      <li><a href="/ppdb-smk/auth/logout.php"><i class="nav-icon">&#x21B0;</i>Keluar</a></li>
    </ul>
  </aside>

  <main>
    <h1 class="page-title">Data Calon Siswa</h1>

    <form method="GET" style="margin-bottom:16px;display:flex;gap:8px;flex-wrap:wrap">
      <label for="q" class="visually-hidden">Cari siswa</label>
      <input type="search" id="q" name="q" class="form-control"
             value="<?= e($q) ?>" placeholder="Cari nama, NISN, atau email..."
             style="max-width:320px;border-radius:10px;border:1.5px solid var(--line);padding:8px 12px;font-size:13px">
      <button type="submit" class="btn-ppdb btn-blue" style="padding:8px 16px;font-size:13px">Cari</button>
      <?php if ($q): ?><a href="/ppdb-smk/admin/siswa.php" style="font-size:13px;color:var(--blue);display:flex;align-items:center">Reset</a><?php endif ?>
    </form>

    <div class="card-ppdb" style="overflow-x:auto">
      <p style="font-size:12px;color:var(--muted);margin:0 0 10px">
        <?= count($siswaList) ?> data ditemukan<?= $q ? " untuk \"" . e($q) . "\"" : '' ?>
      </p>
      <?php if ($siswaList): ?>
        <table class="tbl-ppdb">
          <thead>
            <tr><th>NISN</th><th>Nama Lengkap</th><th>Jenis Kelamin</th><th>Asal Sekolah</th><th>Email</th><th>Status PPDB</th></tr>
          </thead>
          <tbody>
            <?php foreach ($siswaList as $s): ?>
            <tr>
              <td><?= e($s['nisn'] ?? '-') ?></td>
              <td><?= e($s['nama_lengkap']) ?></td>
              <td><?= e($s['jenis_kelamin']) ?></td>
              <td><?= e($s['asal_sekolah'] ?? '-') ?></td>
              <td><?= e($s['email']) ?></td>
              <td>
                <?php if ($s['status_pend']): ?>
                  <span class="badge-ppdb badge-<?= strtolower(str_replace(' ','',$s['status_pend'])) ?>">
                    <?= e($s['status_pend']) ?>
                  </span>
                <?php else: ?>
                  <span style="font-size:11px;color:var(--muted)">Belum daftar</span>
                <?php endif ?>
              </td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      <?php else: ?>
        <p style="font-size:14px;color:var(--muted)">Tidak ada data siswa<?= $q ? " yang cocok." : '.' ?></p>
      <?php endif ?>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>


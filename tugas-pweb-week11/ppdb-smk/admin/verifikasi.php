<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db   = getDB();
$user = currentUser();

// Filter status
$filterStatus = $_GET['status'] ?? '';
$validStatus  = ['Menunggu','Diverifikasi','Diterima','Ditolak'];

$sql = "
    SELECT p.id_pendaftaran, p.no_pendaftaran, p.status, p.tanggal_daftar,
           s.nama_lengkap, s.asal_sekolah,
           j.kode_jurusan, j.nama_jurusan
    FROM pendaftaran p
    JOIN siswa   s ON s.id_siswa   = p.id_siswa
    JOIN jurusan j ON j.id_jurusan = p.id_jurusan
    WHERE p.tahun_ajaran = '2026/2027'
";
$params = [];

if ($filterStatus !== '' && in_array($filterStatus, $validStatus, true)) {
    $sql    .= " AND p.status = ?";
    $params[] = $filterStatus;
}
$sql .= " ORDER BY p.tanggal_daftar DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$pendaftaran = $stmt->fetchAll();

// Update status (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    verifyCsrf();

    $id_pend    = (int)($_POST['id_pendaftaran'] ?? 0);
    $statusBaru = $_POST['status_baru'] ?? '';
    $catatan    = trim($_POST['catatan'] ?? '');

    if ($id_pend > 0 && in_array($statusBaru, $validStatus, true)) {
        // Ambil status lama
        $old = $db->prepare("SELECT status FROM pendaftaran WHERE id_pendaftaran = ?");
        $old->execute([$id_pend]);
        $statusLama = $old->fetchColumn();

        // Update
        $upd = $db->prepare("UPDATE pendaftaran SET status = ? WHERE id_pendaftaran = ?");
        $upd->execute([$statusBaru, $id_pend]);

        // Riwayat
        $rw = $db->prepare("
            INSERT INTO riwayat_status (id_pendaftaran, status_sebelumnya, status_baru, catatan, id_admin)
            VALUES (?, ?, ?, ?, ?)
        ");
        $rw->execute([$id_pend, $statusLama, $statusBaru, $catatan ?: null, $user['id_user']]);

        flashSet('ok', 'Status pendaftaran berhasil diperbarui.');
    }
    header('Location: /ppdb-smk/admin/verifikasi.php');
    exit;
}

$pageTitle = 'Verifikasi Pendaftaran | PPDB Online SMK Nusantara';
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
      <li><a href="/ppdb-smk/admin/jurusan.php"><i class="nav-icon">&#x1F4DA;</i>Jurusan</a></li>
      <li><a href="/ppdb-smk/admin/verifikasi.php" class="active"><i class="nav-icon">&#x2713;</i>Verifikasi</a></li>
      <li><a href="/ppdb-smk/auth/logout.php"><i class="nav-icon">&#x21B0;</i>Keluar</a></li>
    </ul>
  </aside>

  <main>
    <h1 class="page-title">Verifikasi Pendaftaran</h1>

    <?php if ($flash_ok):  ?><div class="flash flash-ok"  role="alert"><?= e($flash_ok)  ?></div><?php endif ?>
    <?php if ($flash_err): ?><div class="flash flash-err" role="alert"><?= e($flash_err) ?></div><?php endif ?>

    <!-- Filter -->
    <form method="GET" style="margin-bottom:16px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      <label for="status" style="font-size:13px;font-weight:700">Filter status:</label>
      <select id="status" name="status" class="form-control" style="width:auto;font-size:13px;border-radius:8px;border:1.5px solid var(--line);padding:6px 10px">
        <option value="">Semua</option>
        <?php foreach ($validStatus as $vs): ?>
          <option value="<?= e($vs) ?>" <?= $filterStatus === $vs ? 'selected' : '' ?>><?= e($vs) ?></option>
        <?php endforeach ?>
      </select>
      <button type="submit" class="btn-ppdb btn-blue" style="padding:8px 16px;font-size:13px">Terapkan</button>
      <?php if ($filterStatus): ?>
        <a href="/ppdb-smk/admin/verifikasi.php" style="font-size:13px;color:var(--blue)">Reset</a>
      <?php endif ?>
    </form>

    <div class="card-ppdb" style="overflow-x:auto">
      <?php if ($pendaftaran): ?>
        <table class="tbl-ppdb">
          <thead>
            <tr>
              <th>No. Pendaftaran</th>
              <th>Nama Siswa</th>
              <th>Asal Sekolah</th>
              <th>Jurusan</th>
              <th>Status</th>
              <th>Tanggal Daftar</th>
              <th>Ubah Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pendaftaran as $p): ?>
            <tr>
              <td><?= e($p['no_pendaftaran']) ?></td>
              <td><?= e($p['nama_lengkap']) ?></td>
              <td><?= e($p['asal_sekolah'] ?? '-') ?></td>
              <td><?= e($p['kode_jurusan']) ?></td>
              <td>
                <span class="badge-ppdb badge-<?= strtolower(str_replace(' ','',$p['status'])) ?>">
                  <?= e($p['status']) ?>
                </span>
              </td>
              <td><?= e(date('d M Y', strtotime($p['tanggal_daftar']))) ?></td>
              <td>
                <button type="button" class="btn-ppdb btn-outline"
                        style="padding:5px 10px;font-size:11px"
                        onclick="showModal(<?= (int)$p['id_pendaftaran'] ?>, '<?= e(addslashes($p['nama_lengkap'])) ?>', '<?= e($p['status']) ?>')">
                  Ubah
                </button>
              </td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      <?php else: ?>
        <p style="font-size:14px;color:var(--muted)">Tidak ada data pendaftaran<?= $filterStatus ? " dengan status \"$filterStatus\"" : '' ?>.</p>
      <?php endif ?>
    </div>
  </main>
</div>

<!-- Modal ubah status -->
<div id="modal-status" role="dialog" aria-modal="true" aria-labelledby="modal-title"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:100;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;padding:28px;width:100%;max-width:420px;box-shadow:0 20px 48px rgba(0,0,0,.18)">
    <h2 id="modal-title" style="font-size:17px;margin:0 0 16px">Ubah Status Pendaftaran</h2>
    <p id="modal-nama" style="font-size:13px;color:var(--muted);margin:0 0 16px"></p>

    <form class="form-ppdb" method="POST" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
      <input type="hidden" name="update_status" value="1">
      <input type="hidden" name="id_pendaftaran" id="modal-id">

      <div class="mb-field">
        <label for="status_baru">Status Baru</label>
        <select id="status_baru" name="status_baru" class="form-control" required>
          <?php foreach ($validStatus as $vs): ?>
            <option value="<?= e($vs) ?>"><?= e($vs) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="mb-field">
        <label for="catatan">Catatan (opsional)</label>
        <textarea id="catatan" name="catatan" class="form-control" rows="2"></textarea>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn-ppdb btn-white" onclick="closeModal()" style="border:1.5px solid var(--line)">Batal</button>
        <button type="submit" class="btn-ppdb btn-blue">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
const modal = document.getElementById('modal-status');
function showModal(id, nama, statusSaat) {
  document.getElementById('modal-id').value   = id;
  document.getElementById('modal-nama').textContent = 'Siswa: ' + nama + ' | Status saat ini: ' + statusSaat;
  document.getElementById('status_baru').value = statusSaat;
  modal.style.display = 'flex';
  document.getElementById('status_baru').focus();
}
function closeModal() {
  modal.style.display = 'none';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>


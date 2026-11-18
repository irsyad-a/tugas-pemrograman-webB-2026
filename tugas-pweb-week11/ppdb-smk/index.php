<?php
// Halaman publik utama — PPDB Online SMK Nusantara
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'PPDB Online | SMK Nusantara';
$activeNav = 'beranda';
include __DIR__ . '/includes/header.php';

// Ambil jurusan aktif untuk ditampilkan di halaman publik
$db = getDB();
$jurusan = $db->query("SELECT kode_jurusan, nama_jurusan, kuota FROM jurusan WHERE aktif = 1 ORDER BY kode_jurusan")->fetchAll();

// Redirect jika sudah login
if (isLoggedIn()) {
    $role = $_SESSION['role'];
    header('Location: /ppdb-smk/' . ($role === 'admin' ? 'admin' : 'siswa') . '/dashboard.php');
    exit;
}
?>

<header class="site-header">
  <div class="nav-wrap">
    <a class="brand" href="/ppdb-smk/" aria-label="SMK Nusantara - Beranda">
      <span class="brand-mark" aria-hidden="true">
        <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round">
          <path d="M5 20 24 6l19 14v22H5z"/>
          <path d="M18 42V27h12v15M11 23h26M20 14h8" stroke-linecap="round"/>
          <path d="M24 2v7m-5-4h10" stroke-width="2.5" stroke-linecap="round"/>
        </svg>
      </span>
      <span class="brand-name">SMK<br>Nusantara</span>
    </a>
    <button class="menu-toggle" aria-label="Buka navigasi" aria-expanded="false">&#9776;</button>
    <nav class="main-nav" aria-label="Navigasi utama">
      <a href="#beranda">Beranda</a>
      <a href="#informasi">Informasi</a>
      <a href="#jurusan">Jurusan</a>
      <a href="#panduan">Panduan</a>
      <a href="#kontak">Kontak</a>
      <a class="nav-cta"        href="/ppdb-smk/auth/login.php">Masuk</a>
      <a class="nav-cta nav-daftar" href="/ppdb-smk/auth/register.php">Daftar</a>
    </nav>
  </div>
</header>

<main>
  <!-- Hero -->
  <section class="hero" id="beranda">
    <div class="hero-copy">
      <span class="eyebrow">Penerimaan Peserta Didik Baru</span>
      <h1>PPDB Online<br>SMK Nusantara</h1>
      <p class="hero-subtitle">Penerimaan Peserta Didik Baru<br>Tahun Ajaran <strong>2026/2027</strong></p>
      <a class="btn-ppdb btn-gold" href="/ppdb-smk/auth/register.php">
        Daftar Sekarang <span aria-hidden="true">&#8594;</span>
      </a>
    </div>
    <div class="hero-art" aria-label="Ilustrasi siswa dan gedung SMK Nusantara" role="img">
      <div class="sun"></div>
      <div class="cloud cloud-one"></div>
      <div class="cloud cloud-two"></div>
      <div class="school">
        <div class="school-roof"></div>
        <div class="school-sign">SMK NUSANTARA</div>
        <div class="school-body">
          <div class="school-window"></div>
          <div class="school-window"></div>
          <div class="school-door"></div>
        </div>
        <div class="school-steps"></div>
      </div>
      <div class="student student-one"><span class="student-head">&#x1F468;&#x1F3FB;&#x200D;&#x1F393;</span><span class="student-label">Calon Siswa</span></div>
      <div class="student student-two"><span class="student-head">&#x1F469;&#x1F3FB;&#x200D;&#x1F393;</span><span class="student-label">Semangat Belajar</span></div>
      <div class="leaf leaf-a"></div>
      <div class="leaf leaf-b"></div>
    </div>
  </section>

  <!-- Quick links -->
  <nav class="quick-links" aria-label="Akses cepat">
    <a class="quick-card" href="#daftar">
      <span class="quick-icon icon-blue" aria-hidden="true">&#x270E;</span>
      <strong>Daftar Sekarang</strong>
      <small>Buat akun dan lakukan<br>pendaftaran PPDB.</small>
    </a>
    <a class="quick-card" href="#informasi">
      <span class="quick-icon icon-cyan" aria-hidden="true">&#x25A6;</span>
      <strong>Informasi PPDB</strong>
      <small>Jadwal, syarat, dan<br>alur pendaftaran.</small>
    </a>
    <a class="quick-card" href="#panduan">
      <span class="quick-icon icon-indigo" aria-hidden="true">&#x25A4;</span>
      <strong>Panduan Pendaftaran</strong>
      <small>Langkah-langkah<br>daftar secara online.</small>
    </a>
    <a class="quick-card" href="#kontak">
      <span class="quick-icon icon-sky" aria-hidden="true">&#x260E;</span>
      <strong>Kontak Sekolah</strong>
      <small>Hubungi kami untuk<br>informasi lebih lanjut.</small>
    </a>
  </nav>

  <!-- Tentang PPDB -->
  <section class="about section-pad" id="informasi">
    <div class="about-visual" aria-hidden="true">
      <div class="mini-school"><span>&#x1F3EB;</span><b>SMK<br>NUSANTARA</b></div>
      <div class="about-students">&#x1F468;&#x1F3FB;&#x200D;&#x1F393;&nbsp;&nbsp;&#x1F469;&#x1F3FB;&#x200D;&#x1F393;</div>
    </div>
    <div class="about-content">
      <p class="section-kicker">Selamat datang di sekolah kami</p>
      <h2>Tentang PPDB Online</h2>
      <p>PPDB Online SMK Nusantara adalah sistem penerimaan peserta didik baru secara daring yang memudahkan calon siswa melakukan pendaftaran, mengunggah berkas, dan memantau hasil seleksi secara transparan, cepat, dan akurat.</p>
      <div class="benefits">
        <div><span>&#9889;</span><b>Mudah Daftar<br>Online</b></div>
        <div><span>&#9826;</span><b>Aman &amp;<br>Terpercaya</b></div>
        <div><span>&#9719;</span><b>Proses<br>Cepat</b></div>
        <div><span>&#9637;</span><b>Transparan &amp;<br>Terukur</b></div>
      </div>
    </div>
  </section>

  <!-- Jadwal PPDB -->
  <section class="schedule section-pad" aria-labelledby="jadwal-heading">
    <div class="schedule-title">
      <span class="calendar-icon" aria-hidden="true">&#x25A6;</span>
      <div>
        <strong id="jadwal-heading">Jadwal PPDB</strong>
        <small>Tahun Ajaran 2026/2027</small>
      </div>
    </div>
    <div class="timeline" role="list">
      <div class="timeline-item" role="listitem"><i class="dot dot-blue"></i><b>Pendaftaran</b><small>1 – 20 Juni 2026</small></div>
      <div class="timeline-item" role="listitem"><i class="dot dot-green"></i><b>Verifikasi</b><small>1 – 22 Juni 2026</small></div>
      <div class="timeline-item" role="listitem"><i class="dot dot-orange"></i><b>Seleksi</b><small>23 – 25 Juni 2026</small></div>
      <div class="timeline-item" role="listitem"><i class="dot dot-pink"></i><b>Pengumuman</b><small>26 Juni 2026</small></div>
    </div>
  </section>

  <!-- Program Keahlian -->
  <section class="section-pad" id="jurusan" style="padding-bottom:28px">
    <p class="section-kicker">Program keahlian tersedia</p>
    <h2>Jurusan yang Dibuka</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:12px;margin-top:16px">
      <?php foreach ($jurusan as $j): ?>
      <div class="card-ppdb" style="margin-bottom:0;text-align:center;padding:16px 12px">
        <div style="font-size:28px;margin-bottom:6px">&#x1F393;</div>
        <b style="font-size:14px;color:var(--deep)"><?= e($j['kode_jurusan']) ?></b>
        <div style="font-size:12px;color:var(--muted);margin-top:2px"><?= e($j['nama_jurusan']) ?></div>
        <div style="font-size:11px;color:#1487d0;margin-top:6px">Kuota: <?= (int)$j['kuota'] ?> siswa</div>
      </div>
      <?php endforeach ?>
    </div>
  </section>

  <!-- Panduan + Kontak -->
  <section class="details-grid section-pad" style="padding-top:8px">
    <article class="info-panel" id="panduan">
      <div class="panel-icon" aria-hidden="true">&#x25A4;</div>
      <div>
        <p class="section-kicker">Langkah mudah</p>
        <h2>Panduan Pendaftaran</h2>
        <ol class="steps">
          <li><span>1</span><div><b>Buat akun</b><small>Daftarkan email dan buat password.</small></div></li>
          <li><span>2</span><div><b>Isi biodata</b><small>Lengkapi identitas calon siswa.</small></div></li>
          <li><span>3</span><div><b>Unggah berkas</b><small>Siapkan KK, akta, rapor, dan pas foto.</small></div></li>
          <li><span>4</span><div><b>Pilih jurusan</b><small>Tentukan program keahlian pilihan.</small></div></li>
          <li><span>5</span><div><b>Pantau status</b><small>Cek hasil verifikasi dan seleksi lewat dashboard.</small></div></li>
        </ol>
      </div>
    </article>
    <article class="info-panel" id="kontak">
      <div class="panel-icon" aria-hidden="true">&#x260E;</div>
      <div>
        <p class="section-kicker">Butuh bantuan?</p>
        <h2>Kontak Sekolah</h2>
        <p class="contact-intro">Hubungi SMK Nusantara untuk informasi lebih lanjut mengenai PPDB.</p>
        <div class="contact-row"><span>&#x2316;</span><div><b>Alamat</b><small>Jl. Pendidikan No. 10, Sidoarjo, Jawa Timur</small></div></div>
        <div class="contact-row"><span>&#x260E;</span><div><b>Telepon</b><small>(031) 1234 5678</small></div></div>
        <div class="contact-row"><span>&#x2709;</span><div><b>Email</b><small>ppdb@smknusantara.sch.id</small></div></div>
      </div>
    </article>
  </section>
  <!-- Akses Akun: Masuk & Daftar -->
  <section class="account-actions section-pad" id="daftar">
    <div class="account-card" id="login">
      <div class="account-icon" aria-hidden="true">&#x1F510;</div>
      <h2>Login Siswa</h2>
      <p>Sudah memiliki akun PPDB?</p>
      <a class="button button-blue" href="/ppdb-smk/auth/login.php">Masuk ke Akun</a>
    </div>
    <div class="account-card">
      <div class="account-icon" aria-hidden="true">&#x1F4DD;</div>
      <h2>Registrasi Siswa</h2>
      <p>Belum memiliki akun? Mulai pendaftaran sekarang.</p>
      <a class="button button-yellow" href="/ppdb-smk/auth/register.php">Daftar Akun</a>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>


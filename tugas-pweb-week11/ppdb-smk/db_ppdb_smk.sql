-- ============================================================
-- Database: db_ppdb_smk
-- SMK Nusantara - PPDB Online
-- ============================================================

CREATE DATABASE IF NOT EXISTS `db_ppdb_smk`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `db_ppdb_smk`;

-- ------------------------------------------------------------
-- 1. users — akun login (siswa & admin)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id_user`     INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `nama`        VARCHAR(128)    NOT NULL,
  `email`       VARCHAR(128)    NOT NULL,
  `password`    VARCHAR(255)    NOT NULL,
  `role`        ENUM('siswa','admin') NOT NULL DEFAULT 'siswa',
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. siswa — biodata calon siswa (1:1 dengan users)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `siswa` (
  `id_siswa`      INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `id_user`       INT UNSIGNED  NOT NULL,
  `nisn`          VARCHAR(10)   DEFAULT NULL,
  `nama_lengkap`  VARCHAR(128)  NOT NULL,
  `jenis_kelamin` ENUM('Laki-laki','Perempuan') NOT NULL,
  `tempat_lahir`  VARCHAR(64)   DEFAULT NULL,
  `tanggal_lahir` DATE          DEFAULT NULL,
  `agama`         ENUM('Islam','Kristen','Katolik','Hindu','Buddha','Konghucu') DEFAULT NULL,
  `alamat`        TEXT          DEFAULT NULL,
  `asal_sekolah`  VARCHAR(128)  DEFAULT NULL,
  `no_hp`         VARCHAR(16)   DEFAULT NULL,
  `updated_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_siswa`),
  UNIQUE KEY `uq_id_user` (`id_user`),
  UNIQUE KEY `uq_nisn`    (`nisn`),
  CONSTRAINT `fk_siswa_user` FOREIGN KEY (`id_user`)
    REFERENCES `users` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. jurusan — program keahlian
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `jurusan` (
  `id_jurusan`   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `kode_jurusan` VARCHAR(8)    NOT NULL,
  `nama_jurusan` VARCHAR(100)  NOT NULL,
  `kuota`        SMALLINT UNSIGNED NOT NULL DEFAULT 30,
  `deskripsi`    TEXT          DEFAULT NULL,
  `aktif`        TINYINT(1)    NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_jurusan`),
  UNIQUE KEY `uq_kode` (`kode_jurusan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed jurusan awal
INSERT INTO `jurusan` (`kode_jurusan`, `nama_jurusan`, `kuota`, `deskripsi`) VALUES
  ('RPL',  'Rekayasa Perangkat Lunak',          36, 'Mempelajari pengembangan perangkat lunak dan aplikasi berbasis web maupun mobile.'),
  ('TKJ',  'Teknik Komputer dan Jaringan',      36, 'Mempelajari instalasi, konfigurasi, dan pemeliharaan jaringan komputer.'),
  ('AKL',  'Akuntansi dan Keuangan Lembaga',    30, 'Mempelajari pencatatan keuangan, perpajakan, dan akuntansi perusahaan.'),
  ('DKV',  'Desain Komunikasi Visual',          36, 'Mempelajari desain grafis, animasi, fotografi, videografi, dan media komunikasi visual.'),
  ('TKR',  'Teknik Kendaraan Ringan',            30, 'Mempelajari perawatan dan perbaikan kendaraan bermotor roda empat.');

-- ------------------------------------------------------------
-- 4. pendaftaran — transaksi pendaftaran siswa
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pendaftaran` (
  `id_pendaftaran`  INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `no_pendaftaran`  VARCHAR(20)   NOT NULL,
  `id_siswa`        INT UNSIGNED  NOT NULL,
  `id_jurusan`      INT UNSIGNED  NOT NULL,
  `tahun_ajaran`    VARCHAR(9)    NOT NULL DEFAULT '2026/2027',
  `tanggal_daftar`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status`          ENUM('Menunggu','Diverifikasi','Diterima','Ditolak')
                    NOT NULL DEFAULT 'Menunggu',
  PRIMARY KEY (`id_pendaftaran`),
  UNIQUE KEY `uq_no_pendaftaran` (`no_pendaftaran`),
  UNIQUE KEY `uq_siswa_tahun`    (`id_siswa`, `tahun_ajaran`),
  CONSTRAINT `fk_pend_siswa`   FOREIGN KEY (`id_siswa`)   REFERENCES `siswa`    (`id_siswa`)   ON DELETE CASCADE,
  CONSTRAINT `fk_pend_jurusan` FOREIGN KEY (`id_jurusan`) REFERENCES `jurusan`  (`id_jurusan`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. berkas — metadata dokumen yang diunggah
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `berkas` (
  `id_berkas`       INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `id_pendaftaran`  INT UNSIGNED  NOT NULL,
  `jenis_berkas`    ENUM('KK','Akta','Rapor','Foto') NOT NULL,
  `nama_file`       VARCHAR(255)  NOT NULL,
  `tanggal_upload`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_berkas`),
  CONSTRAINT `fk_berkas_pend` FOREIGN KEY (`id_pendaftaran`)
    REFERENCES `pendaftaran` (`id_pendaftaran`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. riwayat_status — audit trail perubahan status
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `riwayat_status` (
  `id_riwayat`        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `id_pendaftaran`    INT UNSIGNED  NOT NULL,
  `status_sebelumnya` VARCHAR(20)   DEFAULT NULL,
  `status_baru`       VARCHAR(20)   NOT NULL,
  `catatan`           TEXT          DEFAULT NULL,
  `id_admin`          INT UNSIGNED  DEFAULT NULL,
  `tanggal_perubahan` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_riwayat`),
  CONSTRAINT `fk_riwayat_pend`  FOREIGN KEY (`id_pendaftaran`) REFERENCES `pendaftaran` (`id_pendaftaran`) ON DELETE CASCADE,
  CONSTRAINT `fk_riwayat_admin` FOREIGN KEY (`id_admin`)       REFERENCES `users`        (`id_user`)       ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Akun admin default — ganti password setelah import!
-- Password: admin123 (bcrypt)
-- ------------------------------------------------------------
INSERT INTO `users` (`nama`, `email`, `password`, `role`) VALUES
  ('Admin PPDB', 'admin@smknusantara.sch.id',
   '$2y$10$jfUKUv915jMveSOYZT7Ob./8Wf2ZLuly6zV45hLEvsJmB2SCEgAi6',
   'admin');


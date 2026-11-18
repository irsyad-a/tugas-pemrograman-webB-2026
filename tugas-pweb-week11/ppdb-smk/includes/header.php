<?php
// Header HTML yang dipakai bersama. Panggil dengan:
//   include INCLUDES . '/header.php';
// setelah mendefinisikan $pageTitle dan $bodyClass (opsional).

$pageTitle  = $pageTitle  ?? 'PPDB Online SMK Nusantara';
$bodyClass  = $bodyClass  ?? '';
$activeNav  = $activeNav  ?? '';   // 'beranda' | 'informasi' | 'panduan' | 'kontak'
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Portal Penerimaan Peserta Didik Baru SMK Nusantara Tahun Ajaran 2026/2027.">
  <title><?= e($pageTitle) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        crossorigin="anonymous">
  <link rel="stylesheet" href="/ppdb-smk/assets/css/main.css">
</head>
<body class="<?= e($bodyClass) ?>">


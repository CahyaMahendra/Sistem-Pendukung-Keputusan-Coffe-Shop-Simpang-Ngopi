<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SPK Seleksi Karyawan - Simpang Ngopi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark" style="background:#3b2417;">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="index.php">
      <i class="bi bi-cup-hot-fill"></i> Simpang Ngopi <small class="fw-normal opacity-75">| SPK AHP-TOPSIS</small>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link <?= $currentPage=='index.php'?'active fw-semibold':'' ?>" href="index.php"><i class="bi bi-house-door"></i> Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage=='kriteria.php'?'active fw-semibold':'' ?>" href="kriteria.php"><i class="bi bi-list-check"></i> Kriteria</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage=='alternatif.php'?'active fw-semibold':'' ?>" href="alternatif.php"><i class="bi bi-people"></i> Calon Karyawan</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage=='penilaian.php'?'active fw-semibold':'' ?>" href="penilaian.php"><i class="bi bi-clipboard-data"></i> Penilaian</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage=='ahp.php'?'active fw-semibold':'' ?>" href="ahp.php"><i class="bi bi-diagram-3"></i> AHP (Bobot)</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage=='topsis.php'?'active fw-semibold':'' ?>" href="topsis.php"><i class="bi bi-trophy"></i> TOPSIS (Hasil)</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<div class="container my-4">
<?php if (!empty($_SESSION['flash'])): ?>
  <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show" role="alert">
    <?= $_SESSION['flash']['message'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

`<?php
session_start();
require 'config.php';
require 'functions.php';

$jumlahKriteria   = $pdo->query("SELECT COUNT(*) c FROM kriteria")->fetch()['c'];
$jumlahAlternatif = $pdo->query("SELECT COUNT(*) c FROM alternatif")->fetch()['c'];
$jumlahPenilaian  = $pdo->query("SELECT COUNT(*) c FROM penilaian")->fetch()['c'];

$topAlternatif = $pdo->query("
    SELECT a.nama, h.nilai_preferensi, h.peringkat
    FROM hasil_topsis h
    JOIN alternatif a ON a.id = h.alternatif_id
    ORDER BY h.peringkat ASC
    LIMIT 5
")->fetchAll();

include 'includes/header.php';
?>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="stat-card bg-coffee1">
      <div class="d-flex justify-content-between align-items-center">
        <div><h3><?= $jumlahKriteria ?></h3><span>Kriteria Penilaian</span></div>
        <i class="bi bi-list-check fs-1 opacity-50"></i>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card bg-coffee2">
      <div class="d-flex justify-content-between align-items-center">
        <div><h3><?= $jumlahAlternatif ?></h3><span>Calon Karyawan</span></div>
        <i class="bi bi-people fs-1 opacity-50"></i>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card bg-coffee3">
      <div class="d-flex justify-content-between align-items-center">
        <div><h3><?= $jumlahPenilaian ?></h3><span>Data Penilaian</span></div>
        <i class="bi bi-clipboard-data fs-1 opacity-50"></i>
      </div>
    </div>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header"><i class="bi bi-info-circle"></i> Tentang Sistem</div>
  <div class="card-body">
    <p>Sistem Pendukung Keputusan (SPK) ini digunakan untuk membantu <strong>Coffee Shop Simpang Ngopi</strong>
    dalam menyeleksi calon karyawan secara lebih objektif, dengan menggabungkan dua metode:</p>
    <ul>
      <li><strong>AHP (Analytical Hierarchy Process)</strong> &mdash; digunakan untuk menentukan <em>bobot prioritas</em> tiap kriteria penilaian berdasarkan matriks perbandingan berpasangan.</li>
      <li><strong>TOPSIS (Technique for Order Preference by Similarity to Ideal Solution)</strong> &mdash; digunakan untuk <em>merangking</em> calon karyawan berdasarkan kedekatan terhadap solusi ideal, menggunakan bobot dari AHP.</li>
    </ul>
    <p class="mb-0">Alur penggunaan: <strong>Kriteria &rarr; Calon Karyawan &rarr; Penilaian &rarr; AHP (hitung bobot) &rarr; TOPSIS (hitung ranking)</strong>.</p>
  </div>
</div>

<?php if ($topAlternatif): ?>
<div class="card">
  <div class="card-header"><i class="bi bi-trophy"></i> Hasil Perangkingan Terakhir</div>
  <div class="card-body">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Peringkat</th><th>Nama Calon Karyawan</th><th>Nilai Preferensi (V)</th></tr></thead>
      <tbody>
      <?php foreach ($topAlternatif as $row): ?>
        <tr>
          <td><span class="badge <?= $row['peringkat']==1?'badge-rank-1':'bg-secondary' ?>">#<?= $row['peringkat'] ?></span></td>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><?= fnum($row['nilai_preferensi'], 4) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php else: ?>
<div class="alert alert-info">Belum ada hasil perhitungan. Silakan lengkapi data lalu jalankan perhitungan di menu <a href="ahp.php">AHP</a> dan <a href="topsis.php">TOPSIS</a>.</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

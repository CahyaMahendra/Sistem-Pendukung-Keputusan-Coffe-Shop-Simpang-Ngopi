<?php
session_start();
require 'config.php';
require 'functions.php';

$kriteriaList   = $pdo->query("SELECT * FROM kriteria ORDER BY urutan ASC, id ASC")->fetchAll();
$alternatifList = $pdo->query("SELECT * FROM alternatif ORDER BY id ASC")->fetchAll();

$weights = array_column($kriteriaList, 'bobot_ahp');
$types   = array_column($kriteriaList, 'jenis');
$bobotBelumDihitung = array_sum($weights) == 0;

$hasil = null;
$decisionMatrix = [];

if ($kriteriaList && $alternatifList && !$bobotBelumDihitung) {
   
    $nilaiMap = [];
    $rows = $pdo->query("SELECT * FROM penilaian")->fetchAll();
    foreach ($rows as $r) {
        $nilaiMap[$r['alternatif_id']][$r['kriteria_id']] = (float)$r['nilai'];
    }

    foreach ($alternatifList as $a) {
        $row = [];
        foreach ($kriteriaList as $k) {
            $row[] = $nilaiMap[$a['id']][$k['id']] ?? 0;
        }
        $decisionMatrix[] = $row;
    }

    if (isset($_POST['hitung'])) {
        $hasil = hitungTOPSIS($decisionMatrix, $weights, $types);

        // Simpan hasil ke tabel hasil_topsis
        $pdo->exec("DELETE FROM hasil_topsis");
        $pref = $hasil['preference'];
        $order = $pref;
        arsort($order); 
        $peringkat = [];
        $rank = 1;
        foreach ($order as $idx => $val) {
            $peringkat[$idx] = $rank++;
        }

        $ins = $pdo->prepare("INSERT INTO hasil_topsis (alternatif_id, nilai_preferensi, peringkat) VALUES (?,?,?)");
        foreach ($alternatifList as $idx => $a) {
            $ins->execute([$a['id'], $pref[$idx], $peringkat[$idx]]);
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Perhitungan TOPSIS berhasil dan hasil ranking telah disimpan.'];
        header('Location: topsis.php');
        exit;
    }
}

$hasilTersimpan = $pdo->query("
    SELECT h.*, a.kode, a.nama FROM hasil_topsis h
    JOIN alternatif a ON a.id = h.alternatif_id
    ORDER BY h.peringkat ASC
")->fetchAll();

include 'includes/header.php';
?>

<?php if ($bobotBelumDihitung): ?>
  <div class="alert alert-warning">Bobot AHP belum dihitung. Silakan hitung bobot kriteria terlebih dahulu di menu <a href="ahp.php">AHP</a>.</div>
<?php elseif (!$kriteriaList || !$alternatifList): ?>
  <div class="alert alert-warning">Lengkapi data <a href="kriteria.php">Kriteria</a> dan <a href="alternatif.php">Calon Karyawan</a> terlebih dahulu.</div>
<?php else: ?>

<div class="card mb-4">
  <div class="card-header"><i class="bi bi-table"></i> Matriks Keputusan (Nilai Penilaian &times; Bobot AHP)</div>
  <div class="card-body table-responsive">
    <table class="table table-bordered text-center">
      <thead class="table-light">
        <tr>
          <th>Alternatif</th>
          <?php foreach ($kriteriaList as $k): ?><th><?= htmlspecialchars($k['kode']) ?></th><?php endforeach; ?>
        </tr>
        <tr class="table-secondary">
          <th>Bobot</th>
          <?php foreach ($weights as $w): ?><th><?= fnum($w, 4) ?></th><?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($alternatifList as $i => $a): ?>
        <tr>
          <td class="text-start"><?= htmlspecialchars($a['kode'].' - '.$a['nama']) ?></td>
          <?php foreach ($decisionMatrix[$i] as $val): ?><td><?= fnum($val, 0) ?></td><?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <form method="post">
      <button type="submit" name="hitung" class="btn btn-brown"><i class="bi bi-calculator"></i> Hitung TOPSIS &amp; Simpan Ranking</button>
    </form>
  </div>
</div>

<?php if ($hasil): ?>
<div class="card mb-4">
  <div class="card-header"><i class="bi bi-graph-up"></i> Matriks Ternormalisasi Terbobot</div>
  <div class="card-body table-responsive">
    <table class="table table-bordered text-center">
      <thead class="table-light">
        <tr><th>Alternatif</th><?php foreach ($kriteriaList as $k): ?><th><?= htmlspecialchars($k['kode']) ?></th><?php endforeach; ?></tr>
      </thead>
      <tbody>
      <?php foreach ($alternatifList as $i => $a): ?>
        <tr>
          <td class="text-start"><?= htmlspecialchars($a['kode']) ?></td>
          <?php foreach ($hasil['weightedNormalized'][$i] as $val): ?><td><?= fnum($val, 4) ?></td><?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
      <tr class="table-success"><td class="text-start fw-semibold">Solusi Ideal Positif (A+)</td>
        <?php foreach ($hasil['idealPositive'] as $val): ?><td><?= fnum($val, 4) ?></td><?php endforeach; ?></tr>
      <tr class="table-danger"><td class="text-start fw-semibold">Solusi Ideal Negatif (A-)</td>
        <?php foreach ($hasil['idealNegative'] as $val): ?><td><?= fnum($val, 4) ?></td><?php endforeach; ?></tr>
      </tbody>
    </table>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header"><i class="bi bi-rulers"></i> Jarak ke Solusi Ideal & Nilai Preferensi</div>
  <div class="card-body table-responsive">
    <table class="table table-bordered text-center">
      <thead class="table-light"><tr><th>Alternatif</th><th>D+</th><th>D-</th><th>Nilai Preferensi (V)</th></tr></thead>
      <tbody>
      <?php foreach ($alternatifList as $i => $a): ?>
        <tr>
          <td class="text-start"><?= htmlspecialchars($a['kode'].' - '.$a['nama']) ?></td>
          <td><?= fnum($hasil['distancePositive'][$i], 4) ?></td>
          <td><?= fnum($hasil['distanceNegative'][$i], 4) ?></td>
          <td class="fw-semibold"><?= fnum($hasil['preference'][$i], 4) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php if ($hasilTersimpan): ?>
<div class="card">
  <div class="card-header"><i class="bi bi-trophy"></i> Hasil Akhir Perangkingan Calon Karyawan</div>
  <div class="card-body table-responsive">
    <table class="table table-hover text-center align-middle">
      <thead class="table-light"><tr><th>Peringkat</th><th>Kode</th><th>Nama</th><th>Nilai Preferensi (V)</th><th>Rekomendasi</th></tr></thead>
      <tbody>
      <?php foreach ($hasilTersimpan as $h): ?>
        <tr class="<?= $h['peringkat']==1 ? 'table-warning' : '' ?>">
          <td><span class="badge <?= $h['peringkat']==1?'badge-rank-1':'bg-secondary' ?>">#<?= $h['peringkat'] ?></span></td>
          <td><?= htmlspecialchars($h['kode']) ?></td>
          <td><?= htmlspecialchars($h['nama']) ?></td>
          <td><?= fnum($h['nilai_preferensi'], 4) ?></td>
          <td><?= $h['peringkat'] <= 3 ? '<span class="badge bg-success">Direkomendasikan</span>' : '<span class="badge bg-light text-dark">-</span>' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>

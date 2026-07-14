<?php
session_start();
require 'config.php';
require 'functions.php';

$kriteriaList = $pdo->query("SELECT * FROM kriteria ORDER BY urutan ASC, id ASC")->fetchAll();
$n = count($kriteriaList);
$ids = array_column($kriteriaList, 'id');


$skalaSaaty = [
    '1/9' => 1/9, '1/8' => 1/8, '1/7' => 1/7, '1/6' => 1/6, '1/5' => 1/5,
    '1/4' => 1/4, '1/3' => 1/3, '1/2' => 1/2, '1' => 1, '2' => 2, '3' => 3,
    '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
];

$hasilAHP = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hitung'])) {
    $matrix = [];
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            if ($i == $j) {
                $matrix[$i][$j] = 1.0;
            } elseif ($i < $j) {
                $val = (float)$_POST['nilai'][$i][$j];
                $matrix[$i][$j] = $val;
                $matrix[$j][$i] = $val != 0 ? 1 / $val : 0;
            }
        }
    }

    $hasilAHP = hitungAHP($matrix);

    $pdo->exec("DELETE FROM perbandingan_ahp");
    $insPair = $pdo->prepare("INSERT INTO perbandingan_ahp (kriteria_baris_id, kriteria_kolom_id, nilai) VALUES (?,?,?)");
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $insPair->execute([$ids[$i], $ids[$j], $matrix[$i][$j]]);
        }
    }

    $updBobot = $pdo->prepare("UPDATE kriteria SET bobot_ahp=? WHERE id=?");
    for ($i = 0; $i < $n; $i++) {
        $updBobot->execute([$hasilAHP['weights'][$i], $ids[$i]]);
    }

    $_SESSION['flash'] = [
        'type' => $hasilAHP['consistent'] ? 'success' : 'danger',
        'message' => $hasilAHP['consistent']
            ? 'Bobot AHP berhasil dihitung dan disimpan. Matriks KONSISTEN (CR &le; 0.1).'
            : 'Bobot AHP dihitung, namun matriks TIDAK KONSISTEN (CR > 0.1). Sebaiknya perbaiki penilaian perbandingan.',
    ];
}

$savedPairs = [];
$rows = $pdo->query("SELECT * FROM perbandingan_ahp")->fetchAll();
foreach ($rows as $r) {
    $savedPairs[$r['kriteria_baris_id']][$r['kriteria_kolom_id']] = $r['nilai'];
}

include 'includes/header.php';
?>

<div class="card mb-4">
  <div class="card-header"><i class="bi bi-diagram-3"></i> Matriks Perbandingan Berpasangan (AHP)</div>
  <div class="card-body">
    <p class="text-muted small">
      Isi tingkat kepentingan kriteria <strong>baris</strong> dibandingkan kriteria <strong>kolom</strong> menggunakan skala Saaty 1&ndash;9.
      Nilai 1 = sama penting, 9 = mutlak lebih penting. Sel segitiga bawah otomatis dihitung sebagai kebalikannya.
    </p>

    <?php if ($n < 2): ?>
      <div class="alert alert-warning">Minimal dibutuhkan 2 kriteria. Silakan tambah di menu <a href="kriteria.php">Kriteria</a>.</div>
    <?php else: ?>
    <form method="post">
      <div class="table-responsive">
        <table class="table table-bordered text-center matrix-input">
          <thead class="table-light">
            <tr>
              <th>Kriteria</th>
              <?php foreach ($kriteriaList as $k): ?><th><?= htmlspecialchars($k['kode']) ?></th><?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
          <?php for ($i = 0; $i < $n; $i++): ?>
            <tr>
              <td class="text-start fw-semibold"><?= htmlspecialchars($kriteriaList[$i]['kode'].' - '.$kriteriaList[$i]['nama']) ?></td>
              <?php for ($j = 0; $j < $n; $j++): ?>
                <td>
                  <?php if ($i == $j): ?>
                    1
                  <?php elseif ($i < $j): ?>
                    <?php $savedVal = $savedPairs[$ids[$i]][$ids[$j]] ?? 1; ?>
                    <select name="nilai[<?= $i ?>][<?= $j ?>]" class="form-select form-select-sm">
                      <?php foreach ($skalaSaaty as $label => $val): ?>
                        <option value="<?= $val ?>" <?= (abs($savedVal - $val) < 0.001) ? 'selected' : '' ?>><?= $label ?></option>
                      <?php endforeach; ?>
                    </select>
                  <?php else: ?>
                    <span class="text-muted">otomatis</span>
                  <?php endif; ?>
                </td>
              <?php endfor; ?>
            </tr>
          <?php endfor; ?>
          </tbody>
        </table>
      </div>
      <button type="submit" name="hitung" class="btn btn-brown"><i class="bi bi-calculator"></i> Hitung Bobot AHP</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<?php if ($hasilAHP): ?>
<div class="card mb-4">
  <div class="card-header"><i class="bi bi-check2-square"></i> Hasil Perhitungan Bobot & Uji Konsistensi</div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered text-center">
        <thead class="table-light"><tr><th>Kriteria</th><th>Bobot Prioritas</th></tr></thead>
        <tbody>
        <?php foreach ($kriteriaList as $i => $k): ?>
          <tr><td class="text-start"><?= htmlspecialchars($k['kode'].' - '.$k['nama']) ?></td><td><?= fnum($hasilAHP['weights'][$i], 4) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="row g-2 mt-2">
      <div class="col-md-3"><div class="border rounded p-2"><small class="text-muted">&lambda; max</small><br><strong><?= fnum($hasilAHP['lambdaMax'],4) ?></strong></div></div>
      <div class="col-md-3"><div class="border rounded p-2"><small class="text-muted">CI</small><br><strong><?= fnum($hasilAHP['CI'],4) ?></strong></div></div>
      <div class="col-md-3"><div class="border rounded p-2"><small class="text-muted">RI</small><br><strong><?= fnum($hasilAHP['RI'],4) ?></strong></div></div>
      <div class="col-md-3"><div class="border rounded p-2"><small class="text-muted">CR</small><br><strong><?= fnum($hasilAHP['CR'],4) ?></strong></div></div>
    </div>
    <div class="alert <?= $hasilAHP['consistent'] ? 'alert-success' : 'alert-danger' ?> mt-3 mb-0">
      <?= $hasilAHP['consistent']
        ? 'CR &le; 0.1 &rarr; matriks perbandingan KONSISTEN, bobot dapat digunakan.'
        : 'CR > 0.1 &rarr; matriks perbandingan TIDAK KONSISTEN, sebaiknya isi ulang penilaian perbandingan.' ?>
    </div>
  </div>
</div>
<?php else: ?>
  <?php
  $sudahAda = array_filter(array_column($kriteriaList, 'bobot_ahp'));
  if ($sudahAda):
  ?>
  <div class="card">
    <div class="card-header"><i class="bi bi-clock-history"></i> Bobot AHP Tersimpan</div>
    <div class="card-body">
      <table class="table table-bordered text-center">
        <thead class="table-light"><tr><th>Kriteria</th><th>Bobot Prioritas</th></tr></thead>
        <tbody>
        <?php foreach ($kriteriaList as $k): ?>
          <tr><td class="text-start"><?= htmlspecialchars($k['kode'].' - '.$k['nama']) ?></td><td><?= fnum($k['bobot_ahp'],4) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

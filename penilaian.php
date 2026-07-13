<?php
session_start();
require 'config.php';
require 'functions.php';

$kriteriaList   = $pdo->query("SELECT * FROM kriteria ORDER BY urutan ASC, id ASC")->fetchAll();
$alternatifList = $pdo->query("SELECT * FROM alternatif ORDER BY id ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $stmt = $pdo->prepare("
        INSERT INTO penilaian (alternatif_id, kriteria_id, nilai) VALUES (?,?,?)
        ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)
    ");
    foreach ($_POST['nilai'] as $altId => $kritValues) {
        foreach ($kritValues as $kritId => $nilai) {
            $stmt->execute([(int)$altId, (int)$kritId, (float)$nilai]);
        }
    }
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data penilaian berhasil disimpan.'];
    header('Location: penilaian.php');
    exit;
}


$existing = $pdo->query("SELECT * FROM penilaian")->fetchAll();
$matrix = [];
foreach ($existing as $row) {
    $matrix[$row['alternatif_id']][$row['kriteria_id']] = $row['nilai'];
}

include 'includes/header.php';
?>

<div class="card">
  <div class="card-header"><i class="bi bi-clipboard-data"></i> Input Penilaian Calon Karyawan per Kriteria</div>
  <div class="card-body">
    <p class="text-muted small">Isi nilai untuk tiap calon karyawan pada setiap kriteria menggunakan skala <strong>1 &ndash; 5</strong>
      (1 = Sangat Kurang, 2 = Kurang, 3 = Cukup, 4 = Baik, 5 = Sangat Baik).</p>

    <?php if (!$kriteriaList || !$alternatifList): ?>
      <div class="alert alert-warning">Lengkapi data <a href="kriteria.php">Kriteria</a> dan <a href="alternatif.php">Calon Karyawan</a> terlebih dahulu.</div>
    <?php else: ?>
    <form method="post">
      <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
          <thead class="table-light">
            <tr>
              <th>Calon Karyawan</th>
              <?php foreach ($kriteriaList as $k): ?>
                <th><?= htmlspecialchars($k['kode']) ?><br><small class="fw-normal"><?= htmlspecialchars($k['nama']) ?></small></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($alternatifList as $a): ?>
            <tr>
              <td class="text-start"><strong><?= htmlspecialchars($a['kode']) ?></strong> - <?= htmlspecialchars($a['nama']) ?></td>
              <?php foreach ($kriteriaList as $k): ?>
                <td>
                  <select name="nilai[<?= $a['id'] ?>][<?= $k['id'] ?>]" class="form-select form-select-sm">
                    <?php for ($v = 1; $v <= 5; $v++): ?>
                      <option value="<?= $v ?>" <?= (isset($matrix[$a['id']][$k['id']]) && (int)$matrix[$a['id']][$k['id']] === $v) ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endfor; ?>
                  </select>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <button type="submit" name="simpan" class="btn btn-brown"><i class="bi bi-save"></i> Simpan Penilaian</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

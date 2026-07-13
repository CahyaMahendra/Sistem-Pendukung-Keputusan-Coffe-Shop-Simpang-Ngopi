<?php
session_start();
require 'config.php';
require 'functions.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $id     = $_POST['id'] ?? '';
    $kode   = trim($_POST['kode']);
    $nama   = trim($_POST['nama']);
    $jenis  = $_POST['jenis'];
    $urutan = (int)$_POST['urutan'];

    if ($id) {
        $stmt = $pdo->prepare("UPDATE kriteria SET kode=?, nama=?, jenis=?, urutan=? WHERE id=?");
        $stmt->execute([$kode, $nama, $jenis, $urutan, $id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kriteria berhasil diperbarui.'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO kriteria (kode, nama, jenis, urutan) VALUES (?,?,?,?)");
        $stmt->execute([$kode, $nama, $jenis, $urutan]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kriteria berhasil ditambahkan.'];
    }
    header('Location: kriteria.php');
    exit;
}


if (isset($_GET['hapus'])) {
    $stmt = $pdo->prepare("DELETE FROM kriteria WHERE id=?");
    $stmt->execute([$_GET['hapus']]);
    $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Kriteria berhasil dihapus.'];
    header('Location: kriteria.php');
    exit;
}

$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM kriteria WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch();
}

$listKriteria = $pdo->query("SELECT * FROM kriteria ORDER BY urutan ASC, id ASC")->fetchAll();
$totalBobot = array_sum(array_column($listKriteria, 'bobot_ahp'));

include 'includes/header.php';
?>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><i class="bi bi-plus-circle"></i> <?= $editData ? 'Edit Kriteria' : 'Tambah Kriteria' ?></div>
      <div class="card-body">
        <form method="post">
          <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
          <div class="mb-3">
            <label class="form-label">Kode</label>
            <input type="text" name="kode" class="form-control" required maxlength="10"
                   value="<?= htmlspecialchars($editData['kode'] ?? 'C'.(count($listKriteria)+1)) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Nama Kriteria</label>
            <input type="text" name="nama" class="form-control" required
                   value="<?= htmlspecialchars($editData['nama'] ?? '') ?>" placeholder="Misal: Pengalaman Kerja">
          </div>
          <div class="mb-3">
            <label class="form-label">Jenis Atribut</label>
            <select name="jenis" class="form-select">
              <option value="benefit" <?= (($editData['jenis'] ?? '') == 'benefit') ? 'selected' : '' ?>>Benefit (semakin besar semakin baik)</option>
              <option value="cost" <?= (($editData['jenis'] ?? '') == 'cost') ? 'selected' : '' ?>>Cost (semakin kecil semakin baik)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Urutan</label>
            <input type="number" name="urutan" class="form-control" value="<?= $editData['urutan'] ?? (count($listKriteria)+1) ?>">
          </div>
          <button type="submit" name="simpan" class="btn btn-brown w-100"><i class="bi bi-save"></i> Simpan</button>
          <?php if ($editData): ?>
            <a href="kriteria.php" class="btn btn-outline-secondary w-100 mt-2">Batal</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-check"></i> Daftar Kriteria</span>
        <span class="badge bg-light text-dark">Total bobot AHP: <?= fnum($totalBobot, 4) ?></span>
      </div>
      <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr><th>Kode</th><th>Nama Kriteria</th><th>Jenis</th><th>Bobot AHP</th><th>Aksi</th></tr>
          </thead>
          <tbody>
          <?php foreach ($listKriteria as $k): ?>
            <tr>
              <td><span class="badge bg-secondary"><?= htmlspecialchars($k['kode']) ?></span></td>
              <td><?= htmlspecialchars($k['nama']) ?></td>
              <td><?= $k['jenis'] === 'benefit' ? '<span class="badge bg-success">Benefit</span>' : '<span class="badge bg-danger">Cost</span>' ?></td>
              <td><?= fnum($k['bobot_ahp'], 4) ?></td>
              <td>
                <a href="kriteria.php?edit=<?= $k['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <a href="kriteria.php?hapus=<?= $k['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kriteria ini? Data penilaian & perbandingan terkait juga akan terhapus.')"><i class="bi bi-trash"></i></a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$listKriteria): ?>
            <tr><td colspan="5" class="text-center text-muted">Belum ada kriteria.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
        <div class="alert alert-info small mb-0">
          <i class="bi bi-lightbulb"></i> Bobot AHP terisi otomatis setelah Anda menghitung di menu <a href="ahp.php">AHP</a>.
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

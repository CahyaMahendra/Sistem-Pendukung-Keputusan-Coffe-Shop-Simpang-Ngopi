<?php
session_start();
require 'config.php';
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $id     = $_POST['id'] ?? '';
    $kode   = trim($_POST['kode']);
    $nama   = trim($_POST['nama']);
    $ket    = trim($_POST['keterangan']);

    if ($id) {
        $stmt = $pdo->prepare("UPDATE alternatif SET kode=?, nama=?, keterangan=? WHERE id=?");
        $stmt->execute([$kode, $nama, $ket, $id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data calon karyawan berhasil diperbarui.'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO alternatif (kode, nama, keterangan) VALUES (?,?,?)");
        $stmt->execute([$kode, $nama, $ket]);
        $id = $pdo->lastInsertId();

        $kriteriaIds = $pdo->query("SELECT id FROM kriteria")->fetchAll(PDO::FETCH_COLUMN);
        $insPenilaian = $pdo->prepare("INSERT IGNORE INTO penilaian (alternatif_id, kriteria_id, nilai) VALUES (?,?,0)");
        foreach ($kriteriaIds as $kid) {
            $insPenilaian->execute([$id, $kid]);
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Calon karyawan berhasil ditambahkan.'];
    }
    header('Location: alternatif.php');
    exit;
}

if (isset($_GET['hapus'])) {
    $stmt = $pdo->prepare("DELETE FROM alternatif WHERE id=?");
    $stmt->execute([$_GET['hapus']]);
    $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Calon karyawan berhasil dihapus.'];
    header('Location: alternatif.php');
    exit;
}

$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM alternatif WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch();
}

$listAlternatif = $pdo->query("SELECT * FROM alternatif ORDER BY id ASC")->fetchAll();

include 'includes/header.php';
?>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><i class="bi bi-person-plus"></i> <?= $editData ? 'Edit Calon Karyawan' : 'Tambah Calon Karyawan' ?></div>
      <div class="card-body">
        <form method="post">
          <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
          <div class="mb-3">
            <label class="form-label">Kode</label>
            <input type="text" name="kode" class="form-control" required maxlength="10"
                   value="<?= htmlspecialchars($editData['kode'] ?? 'A'.(count($listAlternatif)+1)) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Nama Calon Karyawan</label>
            <input type="text" name="nama" class="form-control" required
                   value="<?= htmlspecialchars($editData['nama'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Keterangan (posisi dilamar, dll)</label>
            <input type="text" name="keterangan" class="form-control"
                   value="<?= htmlspecialchars($editData['keterangan'] ?? '') ?>" placeholder="Misal: Pelamar Barista">
          </div>
          <button type="submit" name="simpan" class="btn btn-brown w-100"><i class="bi bi-save"></i> Simpan</button>
          <?php if ($editData): ?>
            <a href="alternatif.php" class="btn btn-outline-secondary w-100 mt-2">Batal</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card">
      <div class="card-header"><i class="bi bi-people"></i> Daftar Calon Karyawan</div>
      <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
          <thead><tr><th>Kode</th><th>Nama</th><th>Keterangan</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php foreach ($listAlternatif as $a): ?>
            <tr>
              <td><span class="badge bg-secondary"><?= htmlspecialchars($a['kode']) ?></span></td>
              <td><?= htmlspecialchars($a['nama']) ?></td>
              <td><?= htmlspecialchars($a['keterangan']) ?></td>
              <td>
                <a href="alternatif.php?edit=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <a href="alternatif.php?hapus=<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus calon karyawan ini?')"><i class="bi bi-trash"></i></a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$listAlternatif): ?>
            <tr><td colspan="4" class="text-center text-muted">Belum ada data calon karyawan.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

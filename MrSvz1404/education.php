<?php
require_once __DIR__ . '/auth_check.php';

$db = Database::getConnection();
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// DELETE
if ($action === 'delete' && $id > 0) {
    $stmt = $db->prepare("DELETE FROM education WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Riwayat pendidikan berhasil dihapus!');
    redirect('MrSvz1404/education.php');
}

// SAVE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $year_range = clean_input($_POST['year_range'] ?? '');
    $institution = clean_input($_POST['institution'] ?? '');
    $major = clean_input($_POST['major'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (empty($year_range) || empty($institution) || empty($major)) {
        setFlash('danger', 'Tahun, Nama Institusi, dan Jurusan/Bidang Pelatihan wajib diisi.');
    } else {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE education SET year_range = ?, institution = ?, major = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$year_range, $institution, $major, $sort_order, $id]);
            setFlash('success', 'Riwayat pendidikan berhasil diperbarui!');
        } else {
            $stmt = $db->prepare("INSERT INTO education (year_range, institution, major, sort_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$year_range, $institution, $major, $sort_order]);
            setFlash('success', 'Riwayat pendidikan baru berhasil ditambahkan!');
        }
        redirect('MrSvz1404/education.php');
    }
}

// Fetch current
$currentItem = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM education WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $currentItem = $stmt->fetch();
    if (!$currentItem) {
        setFlash('danger', 'Data pendidikan tidak ditemukan.');
        redirect('MrSvz1404/education.php');
    }
}

$educations = $db->query("SELECT * FROM education ORDER BY sort_order ASC, id ASC")->fetchAll();

$pageTitle = 'Kelola Riwayat Pendidikan';
require_once __DIR__ . '/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title">
        <i class="fas <?= $action === 'add' ? 'fa-plus-circle' : 'fa-edit' ?>" style="color: var(--admin-primary); margin-right: 6px;"></i>
        <?= $action === 'add' ? 'Tambah Pendidikan Baru' : 'Edit Pendidikan' ?>
      </h3>
      <a href="<?= base_url('MrSvz1404/education.php') ?>" class="btn-outline-action">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
      </a>
    </div>

    <div class="admin-modal-body">
      <form action="<?= base_url('MrSvz1404/education.php' . ($action === 'edit' ? '?action=edit&id=' . $id : '?action=add')) ?>" method="POST">
        <div class="form-grid">
          <div class="admin-input-group">
            <label class="admin-label" for="year_range">Periode Tahun *</label>
            <input type="text" id="year_range" name="year_range" class="admin-input" required 
                   value="<?= sanitize($currentItem['year_range'] ?? '') ?>" placeholder="Contoh: (2014 - 2017) atau (2021 - 2021)">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="institution">Institusi / Sekolah / Lembaga *</label>
            <input type="text" id="institution" name="institution" class="admin-input" required 
                   value="<?= sanitize($currentItem['institution'] ?? '') ?>" placeholder="Contoh: SMK MULTISTUDI HIGHSCHOOL">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="major">Jurusan / Kompetensi *</label>
            <input type="text" id="major" name="major" class="admin-input" required 
                   value="<?= sanitize($currentItem['major'] ?? '') ?>" placeholder="Contoh: Rekayasa Perangkat Lunak / Welder GTAW+SMAW 6G">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="sort_order">Urutan Tampil (Angka kecil tampil di atas)</label>
            <input type="number" id="sort_order" name="sort_order" class="admin-input" 
                   value="<?= (int)($currentItem['sort_order'] ?? 0) ?>">
          </div>
        </div>

        <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--admin-border);">
          <a href="<?= base_url('MrSvz1404/education.php') ?>" class="btn-outline-action">Batal</a>
          <button type="submit" class="btn-admin-action">
            <i class="fas fa-save"></i> Simpan Pendidikan
          </button>
        </div>
      </form>
    </div>
  </div>

<?php else: ?>
  <div class="admin-card">
    <div class="admin-card-header">
      <div>
        <h3 class="admin-card-title"><i class="fas fa-graduation-cap" style="color: var(--admin-primary); margin-right: 6px;"></i> Daftar Pendidikan & Pelatihan</h3>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 2px;">Daftar pendidikan formal dan pelatihan sertifikasi kejuruan.</p>
      </div>
      <a href="<?= base_url('MrSvz1404/education.php?action=add') ?>" class="btn-admin-action">
        <i class="fas fa-plus"></i> Tambah Pendidikan Baru
      </a>
    </div>

    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Tahun</th>
            <th>Institusi</th>
            <th>Jurusan / Kompetensi</th>
            <th>Urutan</th>
            <th style="width: 120px; text-align: center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($educations)): ?>
            <tr><td colspan="5" style="text-align: center; padding: 2rem;">Belum ada data pendidikan.</td></tr>
          <?php else: ?>
            <?php foreach ($educations as $edu): ?>
              <tr>
                <td><strong><?= sanitize($edu['year_range']) ?></strong></td>
                <td><?= sanitize($edu['institution']) ?></td>
                <td><span class="badge-status purple"><?= sanitize($edu['major']) ?></span></td>
                <td><?= (int)$edu['sort_order'] ?></td>
                <td style="text-align: center;">
                  <div class="table-actions" style="justify-content: center;">
                    <a href="<?= base_url('MrSvz1404/education.php?action=edit&id=' . $edu['id']) ?>" class="btn-icon edit" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <a href="<?= base_url('MrSvz1404/education.php?action=delete&id=' . $edu['id']) ?>" 
                       class="btn-icon delete" title="Hapus" 
                       onclick="return confirmDelete('Hapus data pendidikan di \'<?= addslashes(sanitize($edu['institution'])) ?>\'?');">
                      <i class="fas fa-trash-alt"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>

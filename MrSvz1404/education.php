<?php
require_once __DIR__ . '/auth_check.php';

$db = Database::getConnection();
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// DELETE
if ($action === 'delete' && $id > 0) {
    $stmtC = $db->prepare("SELECT image FROM education WHERE id = ? LIMIT 1");
    $stmtC->execute([$id]);
    $item = $stmtC->fetch();
    if ($item && !empty($item['image']) && strpos($item['image'], 'uploads/') === 0 && file_exists(BASE_DIR . $item['image'])) {
        @unlink(BASE_DIR . $item['image']);
    }

    $stmt = $db->prepare("DELETE FROM education WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Riwayat pendidikan berhasil dihapus!');
    redirect('MrSvz1404/education.php');
}

$currentItem = null;

// Handle post_max_size exceeded (PHP drops $_POST and $_FILES)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
    setFlash('danger', 'Ukuran total file yang diunggah melebihi batas maksimal server. Silakan unggah foto dengan ukuran lebih kecil.');
    $action = ($id > 0 ? 'edit' : 'add');
}

// SAVE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    $year_range = clean_input($_POST['year_range'] ?? '');
    $institution = clean_input($_POST['institution'] ?? '');
    $major = clean_input($_POST['major'] ?? '');
    $major_en = clean_input($_POST['major_en'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $existingImage = $_POST['existing_image'] ?? '';

    // Preserve form input in case of validation or upload errors
    $currentItem = [
        'id' => $id,
        'year_range' => $year_range,
        'institution' => $institution,
        'major' => $major,
        'major_en' => $major_en,
        'sort_order' => $sort_order,
        'image' => $existingImage
    ];

    $hasError = false;

    if (empty($year_range) || empty($institution) || empty($major)) {
        setFlash('danger', 'Tahun, Nama Institusi, dan Jurusan/Bidang Pelatihan wajib diisi.');
        $hasError = true;
    } else {
        $imagePath = $existingImage;
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                setFlash('danger', 'Gagal upload foto lampiran: ' . getUploadErrorMessage($_FILES['image']['error']));
                $hasError = true;
            } else {
                $uploadRes = handleUpload($_FILES['image'], 'uploads/education');
                if ($uploadRes['success']) {
                    $imagePath = $uploadRes['filename'];
                    $currentItem['image'] = $imagePath;
                } else {
                    setFlash('danger', 'Gagal upload foto lampiran: ' . $uploadRes['error']);
                    $hasError = true;
                }
            }
        }

        if (!$hasError) {
            if ($id > 0) {
                $stmt = $db->prepare("UPDATE education SET year_range = ?, institution = ?, major = ?, major_en = ?, sort_order = ?, image = ? WHERE id = ?");
                $stmt->execute([$year_range, $institution, $major, $major_en, $sort_order, $imagePath, $id]);
                setFlash('success', 'Riwayat pendidikan berhasil diperbarui!');
            } else {
                $stmt = $db->prepare("INSERT INTO education (year_range, institution, major, major_en, sort_order, image) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$year_range, $institution, $major, $major_en, $sort_order, $imagePath]);
                setFlash('success', 'Riwayat pendidikan baru berhasil ditambahkan!');
            }
            redirect('MrSvz1404/education.php');
        } else {
            $action = ($id > 0 ? 'edit' : 'add');
        }
    }
}

// Fetch current
if ($action === 'edit' && $id > 0) {
    if ($currentItem === null) {
        $stmt = $db->prepare("SELECT * FROM education WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $currentItem = $stmt->fetch();
        if (!$currentItem) {
            setFlash('danger', 'Data pendidikan tidak ditemukan.');
            redirect('MrSvz1404/education.php');
        }
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
      <form action="<?= base_url('MrSvz1404/education.php' . ($action === 'edit' ? '?action=edit&id=' . $id : '?action=add')) ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="existing_image" value="<?= sanitize($currentItem['image'] ?? '') ?>">

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
            <label class="admin-label" for="major">Jurusan / Kompetensi (Bahasa Indonesia) *</label>
            <input type="text" id="major" name="major" class="admin-input" required 
                   value="<?= sanitize($currentItem['major'] ?? '') ?>" placeholder="Contoh: Rekayasa Perangkat Lunak / Welder GTAW+SMAW 6G">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="major_en">Major / Training Focus (English) <span style="color:#0284c7;">[Default Website]</span></label>
            <input type="text" id="major_en" name="major_en" class="admin-input" 
                   value="<?= sanitize($currentItem['major_en'] ?? '') ?>" placeholder="e.g. Software Engineering / 6G Pipe Welding">
          </div>

          <div class="admin-input-group form-full">
            <label class="admin-label" for="sort_order">Urutan Tampil (Angka kecil tampil di atas)</label>
            <input type="number" id="sort_order" name="sort_order" class="admin-input" 
                   value="<?= (int)($currentItem['sort_order'] ?? 0) ?>">
          </div>

          <!-- Photo / Diploma / Certificate Upload -->
          <div class="admin-input-group form-full">
            <label class="admin-label" for="image">Foto Ijazah / Sertifikat / Logo Institusi (Opsional)</label>
            <input type="file" id="image" name="image" class="admin-input" accept="image/*" data-preview="edu-preview">
            <small style="color: #64748b; font-size: 0.78rem;">Foto ijazah kelulusan atau logo lembaga pendidikan. Format: JPG, PNG, WEBP. Maks 5MB.</small>
            
            <div id="edu-preview" class="preview-box" style="<?= !empty($currentItem['image']) ? 'display:block;' : '' ?>">
              <?php if (!empty($currentItem['image'])): ?>
                <img src="<?= base_url($currentItem['image']) ?>" alt="Preview" style="max-height: 80px; object-fit: contain;">
              <?php endif; ?>
            </div>
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
            <th style="width: 70px;">Foto/Logo</th>
            <th>Tahun</th>
            <th>Institusi</th>
            <th>Jurusan / Kompetensi</th>
            <th>Urutan</th>
            <th style="width: 120px; text-align: center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($educations)): ?>
            <tr><td colspan="6" style="text-align: center; padding: 2rem;">Belum ada data pendidikan.</td></tr>
          <?php else: ?>
            <?php foreach ($educations as $edu): ?>
              <tr>
                <td>
                  <?php if (!empty($edu['image'])): ?>
                    <img src="<?= base_url($edu['image']) ?>" class="table-img" alt="" style="width: 44px; height: 44px; object-fit: contain;">
                  <?php else: ?>
                    <div style="width: 44px; height: 44px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 1.1rem;">
                      <i class="fas fa-graduation-cap"></i>
                    </div>
                  <?php endif; ?>
                </td>
                <td><strong><?= sanitize($edu['year_range']) ?></strong></td>
                <td><?= sanitize($edu['institution']) ?></td>
                <td>
                  <span class="badge-status purple"><?= sanitize($edu['major']) ?></span>
                  <?php if (!empty($edu['major_en']) && $edu['major_en'] !== $edu['major']): ?>
                    <div style="font-size: 0.75rem; color: #0284c7; margin-top: 2px;">EN: <?= sanitize($edu['major_en']) ?></div>
                  <?php endif; ?>
                </td>
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

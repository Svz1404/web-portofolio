<?php
require_once __DIR__ . '/auth_check.php';

$db = Database::getConnection();
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// DELETE
if ($action === 'delete' && $id > 0) {
    $stmtC = $db->prepare("SELECT image FROM skills WHERE id = ? LIMIT 1");
    $stmtC->execute([$id]);
    $item = $stmtC->fetch();
    if ($item && !empty($item['image']) && strpos($item['image'], 'uploads/') === 0 && file_exists(BASE_DIR . $item['image'])) {
        @unlink(BASE_DIR . $item['image']);
    }

    $stmt = $db->prepare("DELETE FROM skills WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Skill / Bahasa berhasil dihapus!');
    redirect('MrSvz1404/skills.php');
}

// SAVE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name'] ?? '');
    $percentage = min(100, max(1, (int)($_POST['percentage'] ?? 100)));
    $category = clean_input($_POST['category'] ?? 'skill');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $existingImage = $_POST['existing_image'] ?? '';

    if (empty($name)) {
        setFlash('danger', 'Nama keahlian atau bahasa wajib diisi.');
    } else {
        $imagePath = $existingImage;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadRes = handleUpload($_FILES['image'], 'uploads/skills');
            if ($uploadRes['success']) {
                $imagePath = $uploadRes['filename'];
            } else {
                setFlash('danger', $uploadRes['error']);
                redirect('MrSvz1404/skills.php' . ($id > 0 ? '?action=edit&id=' . $id : '?action=add'));
            }
        }

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE skills SET name = ?, percentage = ?, category = ?, sort_order = ?, image = ? WHERE id = ?");
            $stmt->execute([$name, $percentage, $category, $sort_order, $imagePath, $id]);
            setFlash('success', 'Skill berhasil diperbarui!');
        } else {
            $stmt = $db->prepare("INSERT INTO skills (name, percentage, category, sort_order, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $percentage, $category, $sort_order, $imagePath]);
            setFlash('success', 'Skill baru berhasil ditambahkan!');
        }
        redirect('MrSvz1404/skills.php');
    }
}

// Fetch current
$currentItem = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM skills WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $currentItem = $stmt->fetch();
    if (!$currentItem) {
        setFlash('danger', 'Data skill tidak ditemukan.');
        redirect('MrSvz1404/skills.php');
    }
}

$skills = $db->query("SELECT * FROM skills ORDER BY category ASC, sort_order ASC, id ASC")->fetchAll();

$pageTitle = 'Kelola Skills & Bahasa';
require_once __DIR__ . '/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title">
        <i class="fas <?= $action === 'add' ? 'fa-plus-circle' : 'fa-edit' ?>" style="color: var(--admin-primary); margin-right: 6px;"></i>
        <?= $action === 'add' ? 'Tambah Skill / Bahasa Baru' : 'Edit Skill' ?>
      </h3>
      <a href="<?= base_url('MrSvz1404/skills.php') ?>" class="btn-outline-action">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
      </a>
    </div>

    <div class="admin-modal-body">
      <form action="<?= base_url('MrSvz1404/skills.php' . ($action === 'edit' ? '?action=edit&id=' . $id : '?action=add')) ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="existing_image" value="<?= sanitize($currentItem['image'] ?? '') ?>">

        <div class="form-grid">
          <div class="admin-input-group">
            <label class="admin-label" for="name">Nama Keahlian / Bahasa *</label>
            <input type="text" id="name" name="name" class="admin-input" required 
                   value="<?= sanitize($currentItem['name'] ?? '') ?>" placeholder="Contoh: Node JS / Welding / Inggris">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="category">Kategori *</label>
            <select id="category" name="category" class="admin-select" required>
              <option value="skill" <?= ($currentItem['category'] ?? '') === 'skill' ? 'selected' : '' ?>>Teknis & Komputer (Skills)</option>
              <option value="bahasa" <?= ($currentItem['category'] ?? '') === 'bahasa' ? 'selected' : '' ?>>Kemampuan Bahasa (Language)</option>
            </select>
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="percentage">Persentase Penguasaan (1 - 100%) *</label>
            <input type="number" id="percentage" name="percentage" class="admin-input" min="1" max="100" required 
                   value="<?= (int)($currentItem['percentage'] ?? 85) ?>">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="sort_order">Urutan Tampil (Angka kecil tampil di atas)</label>
            <input type="number" id="sort_order" name="sort_order" class="admin-input" 
                   value="<?= (int)($currentItem['sort_order'] ?? 0) ?>">
          </div>

          <!-- Photo / Icon Upload -->
          <div class="admin-input-group form-full">
            <label class="admin-label" for="image">Foto / Ikon / Logo Badge Keahlian (Opsional)</label>
            <input type="file" id="image" name="image" class="admin-input" accept="image/*" data-preview="skill-preview">
            <small style="color: #64748b; font-size: 0.78rem;">Foto badge atau ikon penanda keahlian. Format: JPG, PNG, WEBP. Maks 5MB.</small>
            
            <div id="skill-preview" class="preview-box" style="<?= !empty($currentItem['image']) ? 'display:block;' : '' ?>">
              <?php if (!empty($currentItem['image'])): ?>
                <img src="<?= base_url($currentItem['image']) ?>" alt="Preview" style="max-height: 80px; object-fit: contain;">
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--admin-border);">
          <a href="<?= base_url('MrSvz1404/skills.php') ?>" class="btn-outline-action">Batal</a>
          <button type="submit" class="btn-admin-action">
            <i class="fas fa-save"></i> Simpan Skill
          </button>
        </div>
      </form>
    </div>
  </div>

<?php else: ?>
  <div class="admin-card">
    <div class="admin-card-header">
      <div>
        <h3 class="admin-card-title"><i class="fas fa-sliders-h" style="color: var(--admin-primary); margin-right: 6px;"></i> Daftar Skills & Bahasa</h3>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 2px;">Kelola progress bar keahlian dan bahasa yang tampil pada CV & portofolio.</p>
      </div>
      <a href="<?= base_url('MrSvz1404/skills.php?action=add') ?>" class="btn-admin-action">
        <i class="fas fa-plus"></i> Tambah Skill / Bahasa
      </a>
    </div>

    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 70px;">Ikon/Foto</th>
            <th>Keahlian / Bahasa</th>
            <th>Kategori</th>
            <th>Tingkat Penguasaan</th>
            <th>Urutan</th>
            <th style="width: 120px; text-align: center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($skills)): ?>
            <tr><td colspan="6" style="text-align: center; padding: 2rem;">Belum ada data skill.</td></tr>
          <?php else: ?>
            <?php foreach ($skills as $s): ?>
              <tr>
                <td>
                  <?php if (!empty($s['image'])): ?>
                    <img src="<?= base_url($s['image']) ?>" class="table-img" alt="" style="width: 44px; height: 44px; object-fit: contain;">
                  <?php else: ?>
                    <div style="width: 44px; height: 44px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 1.1rem;">
                      <i class="fas <?= $s['category'] === 'bahasa' ? 'fa-language' : 'fa-code' ?>"></i>
                    </div>
                  <?php endif; ?>
                </td>
                <td><strong><?= sanitize($s['name']) ?></strong></td>
                <td>
                  <span class="badge-status <?= $s['category'] === 'bahasa' ? 'green' : 'blue' ?>">
                    <?= $s['category'] === 'bahasa' ? 'Bahasa' : 'Skill Teknis' ?>
                  </span>
                </td>
                <td>
                  <div style="display: flex; align-items: center; gap: 0.75rem; max-width: 200px;">
                    <div style="flex: 1; height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                      <div style="width: <?= (int)$s['percentage'] ?>%; height: 100%; background: #0284c7;"></div>
                    </div>
                    <span style="font-weight: 700; font-size: 0.85rem;"><?= (int)$s['percentage'] ?>%</span>
                  </div>
                </td>
                <td><?= (int)$s['sort_order'] ?></td>
                <td style="text-align: center;">
                  <div class="table-actions" style="justify-content: center;">
                    <a href="<?= base_url('MrSvz1404/skills.php?action=edit&id=' . $s['id']) ?>" class="btn-icon edit" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <a href="<?= base_url('MrSvz1404/skills.php?action=delete&id=' . $s['id']) ?>" 
                       class="btn-icon delete" title="Hapus" 
                       onclick="return confirmDelete('Hapus \'<?= addslashes(sanitize($s['name'])) ?>\'?');">
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

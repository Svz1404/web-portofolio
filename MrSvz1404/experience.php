<?php
require_once __DIR__ . '/auth_check.php';

$db = Database::getConnection();
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// DELETE
if ($action === 'delete' && $id > 0) {
    $stmt = $db->prepare("DELETE FROM experience WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Riwayat pengalaman kerja berhasil dihapus!');
    redirect('MrSvz1404/experience.php');
}

// SAVE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_range = clean_input($_POST['date_range'] ?? '');
    $date_range_en = clean_input($_POST['date_range_en'] ?? '');
    $company = clean_input($_POST['company'] ?? '');
    $role = clean_input($_POST['role'] ?? '');
    $role_en = clean_input($_POST['role_en'] ?? '');
    $details = clean_input($_POST['details'] ?? '');
    $details_en = clean_input($_POST['details_en'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (empty($date_range) || empty($company) || empty($role)) {
        setFlash('danger', 'Periode tanggal, Nama Perusahaan, dan Posisi/Role wajib diisi.');
    } else {
        if ($id > 0) {
            $stmt = $db->prepare("
                UPDATE experience 
                SET date_range = ?, date_range_en = ?, company = ?, role = ?, role_en = ?, details = ?, details_en = ?, sort_order = ? 
                WHERE id = ?
            ");
            $stmt->execute([$date_range, $date_range_en, $company, $role, $role_en, $details, $details_en, $sort_order, $id]);
            setFlash('success', 'Pengalaman kerja berhasil diperbarui!');
        } else {
            $stmt = $db->prepare("
                INSERT INTO experience (date_range, date_range_en, company, role, role_en, details, details_en, sort_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$date_range, $date_range_en, $company, $role, $role_en, $details, $details_en, $sort_order]);
            setFlash('success', 'Pengalaman kerja baru berhasil ditambahkan!');
        }
        redirect('MrSvz1404/experience.php');
    }
}

// Fetch current
$currentItem = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM experience WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $currentItem = $stmt->fetch();
    if (!$currentItem) {
        setFlash('danger', 'Data pengalaman tidak ditemukan.');
        redirect('MrSvz1404/experience.php');
    }
}

$experiences = $db->query("SELECT * FROM experience ORDER BY sort_order ASC, id ASC")->fetchAll();

$pageTitle = 'Kelola Pengalaman Kerja';
require_once __DIR__ . '/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title">
        <i class="fas <?= $action === 'add' ? 'fa-plus-circle' : 'fa-edit' ?>" style="color: var(--admin-primary); margin-right: 6px;"></i>
        <?= $action === 'add' ? 'Tambah Pengalaman Kerja Baru' : 'Edit Pengalaman Kerja' ?>
      </h3>
      <a href="<?= base_url('MrSvz1404/experience.php') ?>" class="btn-outline-action">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
      </a>
    </div>

    <div class="admin-modal-body">
      <form action="<?= base_url('MrSvz1404/experience.php' . ($action === 'edit' ? '?action=edit&id=' . $id : '?action=add')) ?>" method="POST">
        <div class="form-grid">
          <div class="admin-input-group form-full">
            <label class="admin-label" for="company">Nama Perusahaan / Organisasi *</label>
            <input type="text" id="company" name="company" class="admin-input" required 
                   value="<?= sanitize($currentItem['company'] ?? '') ?>" placeholder="Contoh: CATERPILLAR INDONESIA">
          </div>

          <!-- Indonesian Fields -->
          <div class="admin-input-group">
            <label class="admin-label" for="date_range">Periode Waktu (Indonesia) *</label>
            <input type="text" id="date_range" name="date_range" class="admin-input" required 
                   value="<?= sanitize($currentItem['date_range'] ?? '') ?>" placeholder="Contoh: JANUARI 2026 – JUNE 2026">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="date_range_en">Date Range (English) <span style="color:#0284c7;">[Default Website]</span></label>
            <input type="text" id="date_range_en" name="date_range_en" class="admin-input" 
                   value="<?= sanitize($currentItem['date_range_en'] ?? '') ?>" placeholder="e.g. JANUARY 2026 – JUNE 2026">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="role">Jabatan / Role (Indonesia) *</label>
            <input type="text" id="role" name="role" class="admin-input" required 
                   value="<?= sanitize($currentItem['role'] ?? '') ?>" placeholder="Contoh: Welder GMAW">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="role_en">Role / Job Title (English) <span style="color:#0284c7;">[Default Website]</span></label>
            <input type="text" id="role_en" name="role_en" class="admin-input" 
                   value="<?= sanitize($currentItem['role_en'] ?? '') ?>" placeholder="e.g. GMAW Welder">
          </div>

          <div class="admin-input-group form-full">
            <label class="admin-label" for="sort_order">Urutan Tampil (Angka kecil tampil di atas)</label>
            <input type="number" id="sort_order" name="sort_order" class="admin-input" 
                   value="<?= (int)($currentItem['sort_order'] ?? 0) ?>">
          </div>

          <div class="admin-input-group form-full">
            <label class="admin-label" for="details">Deskripsi Tugas & Tanggung Jawab (Bahasa Indonesia)</label>
            <textarea id="details" name="details" class="admin-textarea" rows="4" 
                      placeholder="Gunakan tanda titik atau baris baru untuk bullet points..."><?= sanitize($currentItem['details'] ?? '') ?></textarea>
          </div>

          <div class="admin-input-group form-full">
            <label class="admin-label" for="details_en">Job Responsibilities & Scope (English - Tampil Sebagai Default)</label>
            <textarea id="details_en" name="details_en" class="admin-textarea" rows="4" 
                      placeholder="Write duties and responsibilities in English..."><?= sanitize($currentItem['details_en'] ?? '') ?></textarea>
          </div>
        </div>

        <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--admin-border);">
          <a href="<?= base_url('MrSvz1404/experience.php') ?>" class="btn-outline-action">Batal</a>
          <button type="submit" class="btn-admin-action">
            <i class="fas fa-save"></i> Simpan Pengalaman
          </button>
        </div>
      </form>
    </div>
  </div>

<?php else: ?>
  <div class="admin-card">
    <div class="admin-card-header">
      <div>
        <h3 class="admin-card-title"><i class="fas fa-briefcase" style="color: var(--admin-primary); margin-right: 6px;"></i> Daftar Pengalaman Kerja (Bilingual)</h3>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 2px;">Data riwayat karir yang ditampilkan pada timeline portofolio dan CV (Mendukung Indonesia & English).</p>
      </div>
      <a href="<?= base_url('MrSvz1404/experience.php?action=add') ?>" class="btn-admin-action">
        <i class="fas fa-plus"></i> Tambah Pengalaman Baru
      </a>
    </div>

    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Periode (ID / EN)</th>
            <th>Perusahaan</th>
            <th>Posisi / Role</th>
            <th>Detail & Tanggung Jawab</th>
            <th>Urutan</th>
            <th style="width: 120px; text-align: center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($experiences)): ?>
            <tr><td colspan="6" style="text-align: center; padding: 2rem;">Belum ada pengalaman kerja.</td></tr>
          <?php else: ?>
            <?php foreach ($experiences as $exp): ?>
              <tr>
                <td>
                  <strong><?= sanitize($exp['date_range']) ?></strong>
                  <?php if (!empty($exp['date_range_en'])): ?>
                    <div style="font-size: 0.75rem; color: #0284c7; margin-top: 2px;"><i class="fas fa-globe"></i> <?= sanitize($exp['date_range_en']) ?></div>
                  <?php endif; ?>
                </td>
                <td><strong><?= sanitize($exp['company']) ?></strong></td>
                <td>
                  <span class="badge-status blue"><?= sanitize($exp['role']) ?></span>
                  <?php if (!empty($exp['role_en'])): ?>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">EN: <?= sanitize($exp['role_en']) ?></div>
                  <?php endif; ?>
                </td>
                <td style="max-width: 320px; white-space: pre-line; font-size: 0.82rem; color: #64748b;">
                  <?= sanitize($exp['details_en'] ?: $exp['details']) ?>
                </td>
                <td><?= (int)$exp['sort_order'] ?></td>
                <td style="text-align: center;">
                  <div class="table-actions" style="justify-content: center;">
                    <a href="<?= base_url('MrSvz1404/experience.php?action=edit&id=' . $exp['id']) ?>" class="btn-icon edit" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <a href="<?= base_url('MrSvz1404/experience.php?action=delete&id=' . $exp['id']) ?>" 
                       class="btn-icon delete" title="Hapus" 
                       onclick="return confirmDelete('Hapus pengalaman di \'<?= addslashes(sanitize($exp['company'])) ?>\'?');">
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

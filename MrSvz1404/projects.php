<?php
require_once __DIR__ . '/auth_check.php';

$db = Database::getConnection();
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// DELETE SINGLE GALLERY IMAGE ACTION
if ($action === 'delete_img') {
    $imgId = isset($_GET['img_id']) ? (int)$_GET['img_id'] : 0;
    $projId = isset($_GET['proj_id']) ? (int)$_GET['proj_id'] : 0;
    if ($imgId > 0) {
        $stmtImg = $db->prepare("SELECT * FROM project_images WHERE id = ? LIMIT 1");
        $stmtImg->execute([$imgId]);
        $imgRow = $stmtImg->fetch();
        if ($imgRow) {
            if (!empty($imgRow['image']) && strpos($imgRow['image'], 'uploads/') === 0 && file_exists(BASE_DIR . $imgRow['image'])) {
                @unlink(BASE_DIR . $imgRow['image']);
            }
            $stmtDel = $db->prepare("DELETE FROM project_images WHERE id = ?");
            $stmtDel->execute([$imgId]);
            setFlash('success', 'Foto galeri berhasil dihapus!');
        }
    }
    redirect('MrSvz1404/projects.php?action=edit&id=' . $projId);
}

// DELETE ENTIRE PROJECT ACTION
if ($action === 'delete' && $id > 0) {
    // Delete all gallery images from disk
    $stmtG = $db->prepare("SELECT image FROM project_images WHERE project_id = ?");
    $stmtG->execute([$id]);
    $galleryImgs = $stmtG->fetchAll();
    foreach ($galleryImgs as $g) {
        if (!empty($g['image']) && strpos($g['image'], 'uploads/') === 0 && file_exists(BASE_DIR . $g['image'])) {
            @unlink(BASE_DIR . $g['image']);
        }
    }
    $db->prepare("DELETE FROM project_images WHERE project_id = ?")->execute([$id]);

    // Delete project cover image from disk if in uploads/
    $stmtP = $db->prepare("SELECT image FROM projects WHERE id = ? LIMIT 1");
    $stmtP->execute([$id]);
    $projRow = $stmtP->fetch();
    if ($projRow && !empty($projRow['image']) && strpos($projRow['image'], 'uploads/') === 0 && file_exists(BASE_DIR . $projRow['image'])) {
        @unlink(BASE_DIR . $projRow['image']);
    }

    $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Project dan seluruh foto galeri berhasil dihapus!');
    redirect('MrSvz1404/projects.php');
}

// DELETE ALL PROJECTS ACTION (KOSONGKAN SEMUA)
if ($action === 'delete_all') {
    $galleryImgs = $db->query("SELECT image FROM project_images")->fetchAll();
    foreach ($galleryImgs as $g) {
        if (!empty($g['image']) && strpos($g['image'], 'uploads/') === 0 && file_exists(BASE_DIR . $g['image'])) {
            @unlink(BASE_DIR . $g['image']);
        }
    }
    $db->exec("DELETE FROM project_images");

    $projImgs = $db->query("SELECT image FROM projects")->fetchAll();
    foreach ($projImgs as $p) {
        if (!empty($p['image']) && strpos($p['image'], 'uploads/') === 0 && file_exists(BASE_DIR . $p['image'])) {
            @unlink(BASE_DIR . $p['image']);
        }
    }
    $db->exec("DELETE FROM projects");

    setFlash('success', 'Semua project berhasil dikosongkan!');
    redirect('MrSvz1404/projects.php');
}

// SAVE (ADD or EDIT)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_input($_POST['title'] ?? '');
    $title_en = clean_input($_POST['title_en'] ?? '');
    $category = clean_input($_POST['category'] ?? '');
    $description = clean_input($_POST['description'] ?? '');
    $description_en = clean_input($_POST['description_en'] ?? '');
    $tech_stack = clean_input($_POST['tech_stack'] ?? '');
    $live_url = clean_input($_POST['live_url'] ?? '');
    $github_url = clean_input($_POST['github_url'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $existingImage = $_POST['existing_image'] ?? '';

    if (empty($title_en)) $title_en = $title;
    if (empty($description_en)) $description_en = $description;

    if (empty($title) || empty($category)) {
        setFlash('danger', 'Judul proyek dan Kategori wajib diisi.');
    } else {
        $imagePath = $existingImage;

        // Check if new cover image uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadRes = handleUpload($_FILES['image'], 'uploads/projects');
            if ($uploadRes['success']) {
                $imagePath = $uploadRes['filename'];
            } else {
                setFlash('danger', $uploadRes['error']);
                redirect('MrSvz1404/projects.php' . ($id > 0 ? '?action=edit&id=' . $id : '?action=add'));
            }
        }

        if ($id > 0) {
            // Update
            $stmt = $db->prepare("
                UPDATE projects 
                SET title = ?, title_en = ?, category = ?, description = ?, description_en = ?, tech_stack = ?, live_url = ?, github_url = ?, image = ?, sort_order = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $title_en, $category, $description, $description_en, $tech_stack, $live_url, $github_url, $imagePath, $sort_order, $id]);
            $projectId = $id;
            $flashMsg = 'Project berhasil diperbarui!';
        } else {
            // Insert
            $stmt = $db->prepare("
                INSERT INTO projects (title, title_en, category, description, description_en, tech_stack, live_url, github_url, image, sort_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $title_en, $category, $description, $description_en, $tech_stack, $live_url, $github_url, $imagePath, $sort_order]);
            $projectId = (int)$db->lastInsertId();
            $flashMsg = 'Project baru berhasil ditambahkan!';
        }

        // Process multiple gallery uploads
        if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
            $uploadedGalleryCount = 0;
            $fileCount = count($_FILES['gallery']['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['gallery']['error'][$i] === UPLOAD_ERR_OK) {
                    $singleFile = [
                        'name' => $_FILES['gallery']['name'][$i],
                        'type' => $_FILES['gallery']['type'][$i],
                        'tmp_name' => $_FILES['gallery']['tmp_name'][$i],
                        'error' => $_FILES['gallery']['error'][$i],
                        'size' => $_FILES['gallery']['size'][$i]
                    ];
                    $uploadRes = handleUpload($singleFile, 'uploads/projects');
                    if ($uploadRes['success']) {
                        $stmtImg = $db->prepare("INSERT INTO project_images (project_id, image, sort_order) VALUES (?, ?, ?)");
                        $stmtImg->execute([$projectId, $uploadRes['filename'], $i]);
                        $uploadedGalleryCount++;

                        // If cover image was empty, set first uploaded gallery photo as cover
                        if (empty($imagePath)) {
                            $imagePath = $uploadRes['filename'];
                            $db->prepare("UPDATE projects SET image = ? WHERE id = ?")->execute([$imagePath, $projectId]);
                        }
                    }
                }
            }
            if ($uploadedGalleryCount > 0) {
                $flashMsg .= " ($uploadedGalleryCount foto galeri berhasil ditambahkan)";
            }
        }

        setFlash('success', $flashMsg);
        redirect('MrSvz1404/projects.php');
    }
}

// Fetch current item if edit
$currentItem = null;
$galleryImages = [];
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $currentItem = $stmt->fetch();
    if (!$currentItem) {
        setFlash('danger', 'Project tidak ditemukan.');
        redirect('MrSvz1404/projects.php');
    }
    // Fetch gallery images
    $stmtGallery = $db->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order ASC, id ASC");
    $stmtGallery->execute([$id]);
    $galleryImages = $stmtGallery->fetchAll();
}

// Fetch all projects
$projects = $db->query("SELECT * FROM projects ORDER BY sort_order ASC, id DESC")->fetchAll();

// Fetch gallery image counts per project
$imgCounts = [];
$countsQuery = $db->query("SELECT project_id, COUNT(*) as cnt FROM project_images GROUP BY project_id")->fetchAll();
foreach ($countsQuery as $cq) {
    $imgCounts[$cq['project_id']] = (int)$cq['cnt'];
}

$pageTitle = 'Kelola Projects (Multi-Image Gallery)';
require_once __DIR__ . '/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
  <!-- FORM ADD / EDIT -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title">
        <i class="fas <?= $action === 'add' ? 'fa-plus-circle' : 'fa-edit' ?>" style="color: var(--admin-primary); margin-right: 6px;"></i>
        <?= $action === 'add' ? 'Tambah Project Baru' : 'Edit Project' ?>
      </h3>
      <a href="<?= base_url('MrSvz1404/projects.php') ?>" class="btn-outline-action">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
      </a>
    </div>

    <div class="admin-modal-body">
      <form action="<?= base_url('MrSvz1404/projects.php' . ($action === 'edit' ? '?action=edit&id=' . $id : '?action=add')) ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="existing_image" value="<?= sanitize($currentItem['image'] ?? 'assets/images/proj-automation.jpg') ?>">

        <div class="form-grid">
          <div class="admin-input-group">
            <label class="admin-label" for="title">Judul / Nama Project (Bahasa Indonesia) *</label>
            <input type="text" id="title" name="title" class="admin-input" required 
                   value="<?= sanitize($currentItem['title'] ?? '') ?>" placeholder="Contoh: Pengelasan Pipeline 6G Tekanan Tinggi">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="title_en">Project Title (English) <span style="color:#0284c7;">[Default Website]</span></label>
            <input type="text" id="title_en" name="title_en" class="admin-input" 
                   value="<?= sanitize($currentItem['title_en'] ?? '') ?>" placeholder="e.g. High Pressure Pipeline & Vessel 6G Welding">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="category">Kategori Proyek *</label>
            <input type="text" id="category" name="category" list="category_suggestions" class="admin-input" required 
                   value="<?= sanitize($currentItem['category'] ?? '') ?>" placeholder="Pilih atau ketik kategori baru">
            <datalist id="category_suggestions">
              <option value="Welding & Fabrikasi">
              <option value="Web Automation">
              <option value="Web Development">
              <option value="Desktop Application">
              <option value="Data Scraping">
            </datalist>
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="tech_stack">Teknologi / Metode / Alat yang Digunakan</label>
            <input type="text" id="tech_stack" name="tech_stack" class="admin-input" 
                   value="<?= sanitize($currentItem['tech_stack'] ?? '') ?>" placeholder="Contoh: GTAW (TIG), SMAW, ASME IX, Pipe 6G">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="live_url">Link Demo / Aplikasi Berjalan (Opsional)</label>
            <input type="text" id="live_url" name="live_url" class="admin-input" 
                   value="<?= sanitize($currentItem['live_url'] ?? '') ?>" placeholder="https://demo-app.com">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="github_url">Link Repositori GitHub / Video Kerja (Opsional)</label>
            <input type="text" id="github_url" name="github_url" class="admin-input" 
                   value="<?= sanitize($currentItem['github_url'] ?? '') ?>" placeholder="https://github.com/masputra1404/...">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="sort_order">Urutan Tampil (Angka kecil tampil di depan)</label>
            <input type="number" id="sort_order" name="sort_order" class="admin-input" 
                   value="<?= (int)($currentItem['sort_order'] ?? 0) ?>">
          </div>

          <!-- Cover Image Upload -->
          <div class="admin-input-group">
            <label class="admin-label" for="image">Foto Sampul Utama (Cover Thumbnail)</label>
            <input type="file" id="image" name="image" class="admin-input" accept="image/*" data-preview="proj-preview">
            <small style="color: #64748b; font-size: 0.78rem;">Foto utama yang tampil pada kartu project di halaman muka. Maks 5MB.</small>
            
            <div id="proj-preview" class="preview-box" style="<?= !empty($currentItem['image']) ? 'display:block;' : '' ?>">
              <?php if (!empty($currentItem['image'])): ?>
                <img src="<?= base_url($currentItem['image']) ?>" alt="Preview">
              <?php endif; ?>
            </div>
          </div>

          <!-- Multi-Image Gallery Upload -->
          <div class="admin-input-group form-full" style="background: #f0fdf4; border: 1.5px dashed #86efac; border-radius: 12px; padding: 1.25rem;">
            <label class="admin-label" for="gallery" style="color: #166534; font-size: 0.95rem; font-weight: 700;">
              <i class="fas fa-images" style="color: #16a34a; margin-right: 6px;"></i> Unggah Foto Galeri / Hasil Pengelasan (Bisa Pilih Banyak Foto Sekaligus)
            </label>
            <input type="file" id="gallery" name="gallery[]" class="admin-input" accept="image/*" multiple style="background: #ffffff;">
            <div style="color: #15803d; font-size: 0.8rem; margin-top: 6px; line-height: 1.45;">
              <i class="fas fa-lightbulb"></i> <strong>Tips:</strong> Tekan tombol <kbd style="background:#e2e8f0; padding:1px 5px; border-radius:4px; font-weight:700;">Ctrl</kbd> atau <kbd style="background:#e2e8f0; padding:1px 5px; border-radius:4px; font-weight:700;">Shift</kbd> saat memilih file di komputer untuk memilih <strong>banyak foto sekaligus</strong>. Sangat cocok untuk mengunggah berbagai tahap pengelasan (Root Pass, Capping, Fit-up, Hasil X-Ray, dsb).
            </div>
          </div>

          <!-- Existing Gallery Photos Grid (When Editing) -->
          <?php if ($action === 'edit'): ?>
            <div class="admin-input-group form-full" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem;">
              <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem;">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin: 0;">
                  <i class="fas fa-photo-video" style="color: #0284c7; margin-right: 6px;"></i>
                  Galeri Foto Proyek Saat Ini (<?= count($galleryImages) ?> Foto Tambahan)
                </h4>
                <span style="font-size: 0.78rem; color: #64748b;">Klik tombol sampah merah untuk menghapus foto individual</span>
              </div>

              <?php if (empty($galleryImages)): ?>
                <p style="font-size: 0.85rem; color: #64748b; font-style: italic; margin: 0.5rem 0 0;">
                  Belum ada foto galeri tambahan. Unggah foto-foto pengelasan pada kotak hijau di atas lalu simpan.
                </p>
              <?php else: ?>
                <div class="gallery-thumbs-grid">
                  <?php foreach ($galleryImages as $gimg): ?>
                    <div class="gallery-thumb-item">
                      <img src="<?= base_url($gimg['image']) ?>" alt="Gallery Foto">
                      <a href="<?= base_url('MrSvz1404/projects.php?action=delete_img&img_id=' . $gimg['id'] . '&proj_id=' . $id) ?>" 
                         onclick="return confirm('Hapus foto ini dari galeri project?');" 
                         class="gallery-thumb-delete" title="Hapus foto ini">
                        <i class="fas fa-trash-alt"></i>
                      </a>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <div class="admin-input-group form-full">
            <label class="admin-label" for="description">Deskripsi Lengkap Project (Bahasa Indonesia)</label>
            <textarea id="description" name="description" class="admin-textarea" rows="3" 
                      placeholder="Jelaskan spesifikasi pengelasan, standar ASME/AWS, metode pemeriksaan NDT/Visual, atau fitur aplikasi..."><?= sanitize($currentItem['description'] ?? '') ?></textarea>
          </div>

          <div class="admin-input-group form-full">
            <label class="admin-label" for="description_en">Project Description (English) <span style="color:#0284c7;">[Tampil Sebagai Default di Website]</span></label>
            <textarea id="description_en" name="description_en" class="admin-textarea" rows="3" 
                      placeholder="Explain welding specs, ASME/AWS code, NDT/Visual testing methods, or system features in English..."><?= sanitize($currentItem['description_en'] ?? '') ?></textarea>
          </div>
        </div>

        <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--admin-border);">
          <a href="<?= base_url('MrSvz1404/projects.php') ?>" class="btn-outline-action">Batal</a>
          <button type="submit" class="btn-admin-action">
            <i class="fas fa-save"></i> Simpan Project & Galeri
          </button>
        </div>
      </form>
    </div>
  </div>

<?php else: ?>
  <!-- LIST VIEW -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div>
        <h3 class="admin-card-title"><i class="fas fa-laptop-code" style="color: var(--admin-primary); margin-right: 6px;"></i> Daftar Proyek Portofolio (Multi-Image)</h3>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 2px;">Kelola proyek dan karya dengan dukungan banyak foto galeri pengelasan per proyek.</p>
      </div>
      <div style="display: flex; gap: 0.5rem; align-items: center;">
        <?php if (!empty($projects)): ?>
          <a href="<?= base_url('MrSvz1404/projects.php?action=delete_all') ?>" 
             onclick="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGOSONGKAN (menghapus semua) project? Tindakan ini akan menghapus seluruh data project yang ada.');" 
             class="btn-outline-action" style="color: #ef4444; border-color: #fca5a5;">
            <i class="fas fa-trash-alt"></i> Kosongkan Semua
          </a>
        <?php endif; ?>
        <a href="<?= base_url('MrSvz1404/projects.php?action=add') ?>" class="btn-admin-action">
          <i class="fas fa-plus"></i> Tambah Project Baru
        </a>
      </div>
    </div>

    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 70px;">Cover</th>
            <th>Judul Project</th>
            <th>Kategori</th>
            <th>Foto Galeri</th>
            <th>Tech Stack</th>
            <th>Tautan</th>
            <th>Urutan</th>
            <th style="width: 120px; text-align: center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($projects)): ?>
            <tr>
              <td colspan="8" style="text-align: center; padding: 2.5rem; color: #64748b;">
                <i class="fas fa-folder-open" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 0.5rem; display: block;"></i>
                Belum ada project yang ditambahkan. Klik tombol "Tambah Project Baru" untuk memulai.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($projects as $p): ?>
              <tr>
                <td>
                  <img src="<?= base_url($p['image'] ?: 'assets/images/proj-automation.jpg') ?>" class="table-img" alt="">
                </td>
                <td>
                  <strong><?= sanitize($p['title']) ?></strong>
                  <?php if (!empty($p['title_en']) && $p['title_en'] !== $p['title']): ?>
                    <div style="font-size: 0.8rem; color: #0284c7; font-weight: 600;">
                      <i class="fas fa-globe" style="font-size: 0.75rem;"></i> <?= sanitize($p['title_en']) ?>
                    </div>
                  <?php endif; ?>
                  <div style="font-size: 0.78rem; color: #64748b; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;">
                    <?= sanitize($p['description']) ?>
                  </div>
                </td>
                <td>
                  <span class="badge-status blue"><?= sanitize($p['category']) ?></span>
                </td>
                <td>
                  <?php 
                    $additionalCount = $imgCounts[$p['id']] ?? 0;
                    $totalPhotos = $additionalCount + (!empty($p['image']) ? 1 : 0);
                  ?>
                  <span class="badge-status <?= $totalPhotos > 1 ? 'green' : 'gray' ?>" style="font-weight: 700;">
                    <i class="fas fa-images"></i> <?= $totalPhotos ?> Foto
                  </span>
                </td>
                <td>
                  <div style="font-size: 0.8rem; color: #475569; max-width: 180px;">
                    <?= sanitize($p['tech_stack']) ?>
                  </div>
                </td>
                <td>
                  <div style="display: flex; gap: 0.4rem;">
                    <?php if (!empty($p['live_url']) && $p['live_url'] !== '#'): ?>
                      <a href="<?= sanitize($p['live_url']) ?>" target="_blank" class="badge-status green" title="Live Demo">Live</a>
                    <?php endif; ?>
                    <?php if (!empty($p['github_url']) && $p['github_url'] !== '#'): ?>
                      <a href="<?= sanitize($p['github_url']) ?>" target="_blank" class="badge-status gray" title="GitHub"><i class="fab fa-github"></i> Code</a>
                    <?php endif; ?>
                  </div>
                </td>
                <td><?= (int)$p['sort_order'] ?></td>
                <td style="text-align: center;">
                  <div class="table-actions" style="justify-content: center;">
                    <a href="<?= base_url('MrSvz1404/projects.php?action=edit&id=' . $p['id']) ?>" class="btn-icon edit" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <a href="<?= base_url('MrSvz1404/projects.php?action=delete&id=' . $p['id']) ?>" 
                       class="btn-icon delete" title="Hapus" 
                       onclick="return confirmDelete('Hapus project \'<?= addslashes(sanitize($p['title'])) ?>\' beserta seluruh foto galerinya?');">
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

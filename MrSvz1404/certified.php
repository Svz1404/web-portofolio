<?php
require_once __DIR__ . '/auth_check.php';

$db = Database::getConnection();
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// DELETE SINGLE CERTIFICATE IMAGE ACTION
if ($action === 'delete_img') {
    $imgId = isset($_GET['img_id']) ? (int)$_GET['img_id'] : 0;
    $certId = isset($_GET['cert_id']) ? (int)$_GET['cert_id'] : 0;
    if ($imgId > 0) {
        $stmtImg = $db->prepare("SELECT * FROM certificate_images WHERE id = ? LIMIT 1");
        $stmtImg->execute([$imgId]);
        $imgRow = $stmtImg->fetch();
        if ($imgRow) {
            if (!empty($imgRow['image']) && strpos($imgRow['image'], 'uploads/') === 0 && file_exists(BASE_DIR . $imgRow['image'])) {
                @unlink(BASE_DIR . $imgRow['image']);
            }
            $stmtDel = $db->prepare("DELETE FROM certificate_images WHERE id = ?");
            $stmtDel->execute([$imgId]);
            setFlash('success', 'Foto/halaman sertifikat berhasil dihapus!');
        }
    }
    redirect('MrSvz1404/certified.php?action=edit&id=' . $certId);
}

// DELETE ENTIRE CERTIFICATE ACTION
if ($action === 'delete' && $id > 0) {
    // Delete all additional certificate images from disk
    $stmtG = $db->prepare("SELECT image FROM certificate_images WHERE certificate_id = ?");
    $stmtG->execute([$id]);
    $galleryImgs = $stmtG->fetchAll();
    foreach ($galleryImgs as $g) {
        if (!empty($g['image']) && strpos($g['image'], 'uploads/') === 0 && file_exists(BASE_DIR . $g['image'])) {
            @unlink(BASE_DIR . $g['image']);
        }
    }
    $db->prepare("DELETE FROM certificate_images WHERE certificate_id = ?")->execute([$id]);

    // Delete primary certificate image from disk if in uploads/
    $stmtC = $db->prepare("SELECT image FROM certificates WHERE id = ? LIMIT 1");
    $stmtC->execute([$id]);
    $certRow = $stmtC->fetch();
    if ($certRow && !empty($certRow['image']) && strpos($certRow['image'], 'uploads/') === 0 && file_exists(BASE_DIR . $certRow['image'])) {
        @unlink(BASE_DIR . $certRow['image']);
    }

    $stmt = $db->prepare("DELETE FROM certificates WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Sertifikat dan seluruh foto lembar timbal balik berhasil dihapus!');
    redirect('MrSvz1404/certified.php');
}

// SAVE (ADD or EDIT)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_input($_POST['title'] ?? '');
    $title_en = clean_input($_POST['title_en'] ?? '');
    $issuer = clean_input($_POST['issuer'] ?? '');
    $issue_date = clean_input($_POST['issue_date'] ?? '');
    $credential_id = clean_input($_POST['credential_id'] ?? '');
    $credential_url = clean_input($_POST['credential_url'] ?? '');
    $description = clean_input($_POST['description'] ?? '');
    $description_en = clean_input($_POST['description_en'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $existingImage = $_POST['existing_image'] ?? '';

    if (empty($title_en)) {
        $title_en = $title;
    }
    if (empty($description_en)) {
        $description_en = $description;
    }

    if (empty($title) || empty($issuer)) {
        setFlash('danger', 'Judul sertifikat dan Institusi Penerbit wajib diisi.');
    } else {
        $imagePath = $existingImage;

        // Check if new primary image uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadRes = handleUpload($_FILES['image'], 'uploads/certificates');
            if ($uploadRes['success']) {
                $imagePath = $uploadRes['filename'];
            } else {
                setFlash('danger', $uploadRes['error']);
                redirect('MrSvz1404/certified.php' . ($id > 0 ? '?action=edit&id=' . $id : '?action=add'));
            }
        }

        if ($id > 0) {
            // Update
            $stmt = $db->prepare("
                UPDATE certificates 
                SET title = ?, title_en = ?, issuer = ?, issue_date = ?, credential_id = ?, credential_url = ?, description = ?, description_en = ?, image = ?, sort_order = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $title_en, $issuer, $issue_date, $credential_id, $credential_url, $description, $description_en, $imagePath, $sort_order, $id]);
            $certId = $id;
            $flashMsg = 'Sertifikat berhasil diperbarui!';
        } else {
            // Insert
            $stmt = $db->prepare("
                INSERT INTO certificates (title, title_en, issuer, issue_date, credential_id, credential_url, description, description_en, image, sort_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $title_en, $issuer, $issue_date, $credential_id, $credential_url, $description, $description_en, $imagePath, $sort_order]);
            $certId = (int)$db->lastInsertId();
            $flashMsg = 'Sertifikat baru berhasil ditambahkan!';
        }

        // Process multiple gallery / timbal balik uploads
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
                    $uploadRes = handleUpload($singleFile, 'uploads/certificates');
                    if ($uploadRes['success']) {
                        $stmtImg = $db->prepare("INSERT INTO certificate_images (certificate_id, image, sort_order) VALUES (?, ?, ?)");
                        $stmtImg->execute([$certId, $uploadRes['filename'], $i]);
                        $uploadedGalleryCount++;

                        // If primary image was empty, set first uploaded photo as primary
                        if (empty($imagePath)) {
                            $imagePath = $uploadRes['filename'];
                            $db->prepare("UPDATE certificates SET image = ? WHERE id = ?")->execute([$imagePath, $certId]);
                        }
                    }
                }
            }
            if ($uploadedGalleryCount > 0) {
                $flashMsg .= " ($uploadedGalleryCount foto tambahan/timbal balik berhasil ditambahkan)";
            }
        }

        setFlash('success', $flashMsg);
        redirect('MrSvz1404/certified.php');
    }
}

// Fetch current item if edit
$currentItem = null;
$galleryImages = [];
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM certificates WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $currentItem = $stmt->fetch();
    if (!$currentItem) {
        setFlash('danger', 'Sertifikat tidak ditemukan.');
        redirect('MrSvz1404/certified.php');
    }
    // Fetch additional certificate images (timbal balik / multi-page)
    $stmtGallery = $db->prepare("SELECT * FROM certificate_images WHERE certificate_id = ? ORDER BY sort_order ASC, id ASC");
    $stmtGallery->execute([$id]);
    $galleryImages = $stmtGallery->fetchAll();
}

// Fetch all certificates
$certificates = $db->query("SELECT * FROM certificates ORDER BY sort_order ASC, id DESC")->fetchAll();

// Count additional photos for each certificate
$imgCounts = [];
$countsQuery = $db->query("SELECT certificate_id, COUNT(*) as cnt FROM certificate_images GROUP BY certificate_id")->fetchAll();
foreach ($countsQuery as $cq) {
    $imgCounts[$cq['certificate_id']] = (int)$cq['cnt'];
}

$pageTitle = 'Kelola Sertifikat (Multi-Foto / Timbal Balik)';
require_once __DIR__ . '/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
  <!-- FORM ADD / EDIT -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title">
        <i class="fas <?= $action === 'add' ? 'fa-plus-circle' : 'fa-edit' ?>" style="color: var(--admin-primary); margin-right: 6px;"></i>
        <?= $action === 'add' ? 'Tambah Sertifikat Baru' : 'Edit Sertifikat' ?>
      </h3>
      <a href="<?= base_url('MrSvz1404/certified.php') ?>" class="btn-outline-action">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
      </a>
    </div>

    <div class="admin-modal-body">
      <form action="<?= base_url('MrSvz1404/certified.php' . ($action === 'edit' ? '?action=edit&id=' . $id : '?action=add')) ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="existing_image" value="<?= sanitize($currentItem['image'] ?? 'assets/images/cert-welder.jpg') ?>">

        <div class="form-grid">
          <div class="admin-input-group">
            <label class="admin-label" for="title">Nama / Judul Sertifikat (Bahasa Indonesia) *</label>
            <input type="text" id="title" name="title" class="admin-input" required 
                   value="<?= sanitize($currentItem['title'] ?? '') ?>" placeholder="Contoh: Sertifikasi Welder 6G (GTAW & SMAW)">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="title_en">Certificate Title (English) <span style="color:#0284c7;">[Default Website]</span></label>
            <input type="text" id="title_en" name="title_en" class="admin-input" 
                   value="<?= sanitize($currentItem['title_en'] ?? '') ?>" placeholder="e.g. 6G Welder Competency Certification (GTAW & SMAW)">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="issuer">Penerbit / Institusi *</label>
            <input type="text" id="issuer" name="issuer" class="admin-input" required 
                   value="<?= sanitize($currentItem['issuer'] ?? '') ?>" placeholder="Contoh: PT. Global Sarana Internusa / BNSP">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="issue_date">Tahun / Periode Penerbitan</label>
            <input type="text" id="issue_date" name="issue_date" class="admin-input" 
                   value="<?= sanitize($currentItem['issue_date'] ?? '') ?>" placeholder="Contoh: 2021">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="credential_id">Nomor / ID Kredensial</label>
            <input type="text" id="credential_id" name="credential_id" class="admin-input" 
                   value="<?= sanitize($currentItem['credential_id'] ?? '') ?>" placeholder="Contoh: CERT-WELD-6G-091">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="credential_url">Tautan / Link Verifikasi Sertifikat (Opsional)</label>
            <input type="text" id="credential_url" name="credential_url" class="admin-input" 
                   value="<?= sanitize($currentItem['credential_url'] ?? '') ?>" placeholder="https://...">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="sort_order">Urutan Tampil (Angka kecil tampil di depan)</label>
            <input type="number" id="sort_order" name="sort_order" class="admin-input" 
                   value="<?= (int)($currentItem['sort_order'] ?? 0) ?>">
          </div>

          <div class="admin-input-group">
            <label class="admin-label" for="image">File Gambar Utama (Halaman Depan / Cover)</label>
            <input type="file" id="image" name="image" class="admin-input" accept="image/*" data-preview="cert-preview">
            <small style="color: #64748b; font-size: 0.78rem;">Foto utama sertifikat (tampak depan). Format: JPG, PNG, WEBP. Maks 5MB.</small>
            
            <div id="cert-preview" class="preview-box" style="<?= !empty($currentItem['image']) ? 'display:block;' : '' ?>">
              <?php if (!empty($currentItem['image'])): ?>
                <img src="<?= base_url($currentItem['image']) ?>" alt="Preview">
              <?php endif; ?>
            </div>
          </div>

          <!-- Multi-Image / Timbal Balik Upload -->
          <div class="admin-input-group form-full" style="background: #f0fdf4; border: 1.5px dashed #86efac; border-radius: 12px; padding: 1.25rem;">
            <label class="admin-label" for="gallery" style="color: #166534; font-size: 0.95rem; font-weight: 700;">
              <i class="fas fa-images" style="color: #16a34a; margin-right: 6px;"></i> Unggah Foto Halaman Belakang / Timbal Balik / Lembar Tambahan (Bisa Pilih Banyak Sekaligus)
            </label>
            <input type="file" id="gallery" name="gallery[]" class="admin-input" accept="image/*" multiple style="background: #ffffff;">
            <div style="color: #15803d; font-size: 0.8rem; margin-top: 6px; line-height: 1.45;">
              <i class="fas fa-lightbulb"></i> <strong>Fitur Timbal Balik:</strong> Sangat cocok jika sertifikat memiliki sisi depan & belakang (timbal balik), lembar transkrip nilai, atau hasil uji NDT/Radiografi. Tekan tombol <kbd style="background:#e2e8f0; padding:1px 5px; border-radius:4px; font-weight:700;">Ctrl</kbd> atau <kbd style="background:#e2e8f0; padding:1px 5px; border-radius:4px; font-weight:700;">Shift</kbd> saat memilih file di komputer untuk memilih <strong>banyak foto sekaligus</strong>.
            </div>
          </div>

          <!-- Existing Gallery Photos Grid (When Editing) -->
          <?php if ($action === 'edit'): ?>
            <div class="admin-input-group form-full" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem;">
              <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem;">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin: 0;">
                  <i class="fas fa-copy" style="color: #0284c7; margin-right: 6px;"></i>
                  Foto Tambahan / Timbal Balik Saat Ini (<?= count($galleryImages) ?> Foto Tambahan)
                </h4>
                <span style="font-size: 0.78rem; color: #64748b;">Klik tombol sampah merah untuk menghapus foto individual</span>
              </div>

              <?php if (empty($galleryImages)): ?>
                <p style="font-size: 0.85rem; color: #64748b; font-style: italic; margin: 0.5rem 0 0;">
                  Belum ada foto tambahan/timbal balik. Unggah foto halaman belakang pada kotak hijau di atas lalu klik Simpan Sertifikat.
                </p>
              <?php else: ?>
                <div class="gallery-thumbs-grid">
                  <?php foreach ($galleryImages as $gimg): ?>
                    <div class="gallery-thumb-item">
                      <img src="<?= base_url($gimg['image']) ?>" alt="Foto Sertifikat">
                      <a href="<?= base_url('MrSvz1404/certified.php?action=delete_img&img_id=' . $gimg['id'] . '&cert_id=' . $id) ?>" 
                         onclick="return confirm('Hapus foto/halaman sertifikat ini?');" 
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
            <label class="admin-label" for="description">Deskripsi Kompetensi Sertifikat (Bahasa Indonesia)</label>
            <textarea id="description" name="description" class="admin-textarea" rows="3" 
                      placeholder="Jelaskan kompetensi apa saja yang dicakup oleh sertifikasi ini dalam Bahasa Indonesia..."><?= sanitize($currentItem['description'] ?? '') ?></textarea>
          </div>

          <div class="admin-input-group form-full">
            <label class="admin-label" for="description_en">Certificate Competency Description (English) <span style="color:#0284c7;">[Tampil Sebagai Default di Website]</span></label>
            <textarea id="description_en" name="description_en" class="admin-textarea" rows="3" 
                      placeholder="Explain the competencies covered by this certification in English..."><?= sanitize($currentItem['description_en'] ?? '') ?></textarea>
          </div>
        </div>

        <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--admin-border);">
          <a href="<?= base_url('MrSvz1404/certified.php') ?>" class="btn-outline-action">Batal</a>
          <button type="submit" class="btn-admin-action">
            <i class="fas fa-save"></i> Simpan Sertifikat &amp; Halaman Timbal Balik
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
        <h3 class="admin-card-title"><i class="fas fa-certificate" style="color: var(--admin-primary); margin-right: 6px;"></i> Daftar Sertifikasi &amp; Lisensi (Multi-Foto Timbal Balik)</h3>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 2px;">Kelola sertifikat resmi dengan dukungan foto timbal balik (halaman depan &amp; belakang).</p>
      </div>
      <a href="<?= base_url('MrSvz1404/certified.php?action=add') ?>" class="btn-admin-action">
        <i class="fas fa-plus"></i> Tambah Sertifikat Baru
      </a>
    </div>

    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 70px;">Cover</th>
            <th>Nama &amp; Keterangan Sertifikat</th>
            <th>Foto / Lembar</th>
            <th>Penerbit</th>
            <th>Tahun</th>
            <th>ID Kredensial</th>
            <th>Urutan</th>
            <th style="width: 120px; text-align: center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($certificates)): ?>
            <tr>
              <td colspan="8" style="text-align: center; padding: 2.5rem; color: #64748b;">
                <i class="fas fa-certificate" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 0.5rem; display: block;"></i>
                Belum ada sertifikat. Klik tombol "Tambah Sertifikat Baru" untuk menambahkan.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($certificates as $cert): ?>
              <tr>
                <td>
                  <img src="<?= base_url($cert['image'] ?: 'assets/images/cert-welder.jpg') ?>" class="table-img" alt="">
                </td>
                <td>
                  <strong><?= sanitize($cert['title']) ?></strong>
                  <?php if (!empty($cert['title_en']) && $cert['title_en'] !== $cert['title']): ?>
                    <div style="font-size: 0.8rem; color: #0284c7; font-weight: 600;">
                      <i class="fas fa-globe" style="font-size: 0.75rem;"></i> <?= sanitize($cert['title_en']) ?>
                    </div>
                  <?php endif; ?>
                  <div style="font-size: 0.78rem; color: #64748b; max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 3px;">
                    <span style="font-weight: 600; color: #475569;">[ID]</span> <?= sanitize($cert['description']) ?>
                  </div>
                  <?php if (!empty($cert['description_en'])): ?>
                    <div style="font-size: 0.78rem; color: #64748b; max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                      <span style="font-weight: 600; color: #0284c7;">[EN]</span> <?= sanitize($cert['description_en']) ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php 
                    $additionalCount = $imgCounts[$cert['id']] ?? 0;
                    $totalPhotos = 1 + $additionalCount;
                  ?>
                  <span class="badge-status <?= $additionalCount > 0 ? 'green' : 'gray' ?>" style="font-size: 0.76rem; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fas <?= $additionalCount > 0 ? 'fa-copy' : 'fa-image' ?>"></i>
                    <?= $totalPhotos ?> <?= $additionalCount > 0 ? 'Foto (Timbal Balik)' : 'Foto' ?>
                  </span>
                </td>
                <td>
                  <span class="badge-status blue"><?= sanitize($cert['issuer']) ?></span>
                </td>
                <td><?= sanitize($cert['issue_date']) ?></td>
                <td><code><?= sanitize($cert['credential_id'] ?: '-') ?></code></td>
                <td><?= (int)$cert['sort_order'] ?></td>
                <td style="text-align: center;">
                  <div class="table-actions" style="justify-content: center;">
                    <a href="<?= base_url('MrSvz1404/certified.php?action=edit&id=' . $cert['id']) ?>" class="btn-icon edit" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <a href="<?= base_url('MrSvz1404/certified.php?action=delete&id=' . $cert['id']) ?>" 
                       class="btn-icon delete" title="Hapus" 
                       onclick="return confirmDelete('Hapus sertifikat \'<?= addslashes(sanitize($cert['title'])) ?>\'?');">
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

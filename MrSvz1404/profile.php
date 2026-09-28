<?php
require_once __DIR__ . '/auth_check.php';

$db = Database::getConnection();
$profile = getProfileData();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = clean_input($_POST['full_name'] ?? '');
    $title = clean_input($_POST['title'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $address = clean_input($_POST['address'] ?? '');
    $linkedin = clean_input($_POST['linkedin'] ?? '');
    $github = clean_input($_POST['github'] ?? '');
    $bio = clean_input($_POST['bio'] ?? '');
    $bio_en = clean_input($_POST['bio_en'] ?? '');
    $avatarPath = $profile['avatar'];

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadRes = handleUpload($_FILES['avatar'], 'uploads/profile');
        if ($uploadRes['success']) {
            $avatarPath = $uploadRes['filename'];
        } else {
            setFlash('danger', $uploadRes['error']);
            redirect('MrSvz1404/profile.php');
        }
    }

    $stmt = $db->prepare("
        UPDATE profile 
        SET full_name = ?, title = ?, phone = ?, email = ?, address = ?, linkedin = ?, github = ?, bio = ?, bio_en = ?, avatar = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = 1
    ");
    $stmt->execute([$full_name, $title, $phone, $email, $address, $linkedin, $github, $bio, $bio_en, $avatarPath]);

    setFlash('success', 'Biodata profil berhasil diperbarui!');
    redirect('MrSvz1404/profile.php');
}

$pageTitle = 'Edit Biodata & Profil';
require_once __DIR__ . '/header.php';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <div>
      <h3 class="admin-card-title"><i class="fas fa-user-edit" style="color: var(--admin-primary); margin-right: 6px;"></i> Edit Informasi Biodata CV</h3>
      <p style="font-size: 0.85rem; color: #64748b; margin-top: 2px;">Data ini akan langsung tampil pada header portofolio web dan dokumen CV print.</p>
    </div>
    <a href="<?= base_url('cv.php') ?>" target="_blank" class="btn-outline-action">
      <i class="fas fa-file-pdf"></i> Preview Tampilan CV
    </a>
  </div>

  <div class="admin-modal-body">
    <form action="<?= base_url('MrSvz1404/profile.php') ?>" method="POST" enctype="multipart/form-data">
      <div class="form-grid">
        <div class="admin-input-group">
          <label class="admin-label" for="full_name">Nama Lengkap *</label>
          <input type="text" id="full_name" name="full_name" class="admin-input" required 
                 value="<?= sanitize($profile['full_name']) ?>" placeholder="SAPUTRA">
        </div>

        <div class="admin-input-group">
          <label class="admin-label" for="title">Profesi / Title Utama *</label>
          <input type="text" id="title" name="title" class="admin-input" required 
                 value="<?= sanitize($profile['title']) ?>" placeholder="Programmer / Welder">
        </div>

        <div class="admin-input-group">
          <label class="admin-label" for="phone">Nomor Telepon / WhatsApp</label>
          <input type="text" id="phone" name="phone" class="admin-input" 
                 value="<?= sanitize($profile['phone']) ?>" placeholder="+628 1277 900210">
        </div>

        <div class="admin-input-group">
          <label class="admin-label" for="email">Alamat Email</label>
          <input type="email" id="email" name="email" class="admin-input" 
                 value="<?= sanitize($profile['email']) ?>" placeholder="masputra1404@gmail.com">
        </div>

        <div class="admin-input-group">
          <label class="admin-label" for="address">Alamat Domisili</label>
          <input type="text" id="address" name="address" class="admin-input" 
                 value="<?= sanitize($profile['address']) ?>" placeholder="Griya Laguna Mas c3 18">
        </div>

        <div class="admin-input-group">
          <label class="admin-label" for="linkedin">URL Profil LinkedIn</label>
          <input type="text" id="linkedin" name="linkedin" class="admin-input" 
                 value="<?= sanitize($profile['linkedin']) ?>" placeholder="https://www.linkedin.com/in/masputra1404/">
        </div>

        <div class="admin-input-group">
          <label class="admin-label" for="github">URL Profil GitHub (Opsional)</label>
          <input type="text" id="github" name="github" class="admin-input" 
                 value="<?= sanitize($profile['github'] ?? '') ?>" placeholder="https://github.com/masputra1404">
        </div>

        <div class="admin-input-group">
          <label class="admin-label" for="avatar">Foto Profil Avatar</label>
          <input type="file" id="avatar" name="avatar" class="admin-input" accept="image/*" data-preview="avatar-preview">
          <small style="color: #64748b; font-size: 0.78rem;">Foto berbentuk lingkaran di CV & portofolio. Format: JPG/PNG/WEBP.</small>

          <div id="avatar-preview" class="preview-box" style="display: block; width: 85px; height: 85px; border-radius: 50%; margin-top: 0.75rem; border: 2px solid var(--admin-primary);">
            <img src="<?= base_url($profile['avatar'] ?: 'assets/images/avatar.png') ?>" alt="Avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
          </div>
        </div>

        <div class="admin-input-group form-full">
          <label class="admin-label" for="bio">Uraian / Ringkasan Tentang Saya (Bahasa Indonesia)</label>
          <textarea id="bio" name="bio" class="admin-textarea" rows="4" 
                    placeholder="Tuliskan ringkasan profil profesional dalam Bahasa Indonesia..."><?= sanitize($profile['bio'] ?? '') ?></textarea>
        </div>

        <div class="admin-input-group form-full">
          <label class="admin-label" for="bio_en">About Me Summary (English - Tampil Sebagai Default Website)</label>
          <textarea id="bio_en" name="bio_en" class="admin-textarea" rows="4" 
                    placeholder="Write your professional summary in English..."><?= sanitize($profile['bio_en'] ?? 'I am a Combination Welder experienced in GTAW, SMAW, GMAW, and FCAW processes. Beyond fabrication and welding, I also possess strong capabilities as a software programmer with a primary focus on web automation and web scraping using Node.js. Familiar with programming languages including PHP, VB.Net, C#, Node.js, and HTML. Possessing extensive computer knowledge, highly adaptable, and dedicated to delivering excellence and accountability in every project.') ?></textarea>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--admin-border);">
        <button type="submit" class="btn-admin-action">
          <i class="fas fa-save"></i> Simpan Perubahan Biodata
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

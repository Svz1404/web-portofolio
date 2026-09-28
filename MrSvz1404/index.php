<?php
$pageTitle = 'Dashboard Ringkasan';
require_once __DIR__ . '/header.php';

$db = Database::getConnection();

// Counters
$projectCount = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$certCount = $db->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
$expCount = $db->query("SELECT COUNT(*) FROM experience")->fetchColumn();
$skillCount = $db->query("SELECT COUNT(*) FROM skills")->fetchColumn();
$unreadMsgCount = $db->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();

// Recent Projects
$recentProjects = $db->query("SELECT * FROM projects ORDER BY id DESC LIMIT 4")->fetchAll();

// Recent Certificates
$recentCerts = $db->query("SELECT * FROM certificates ORDER BY id DESC LIMIT 4")->fetchAll();

// Recent Messages
$recentMessages = $db->query("SELECT * FROM messages ORDER BY id DESC LIMIT 5")->fetchAll();
?>

<!-- KPI Grid -->
<div class="kpi-grid">
  <div class="kpi-card">
    <div class="kpi-icon blue">
      <i class="fas fa-laptop-code"></i>
    </div>
    <div class="kpi-info">
      <h4><?= $projectCount ?></h4>
      <span>Total Projects</span>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon green">
      <i class="fas fa-certificate"></i>
    </div>
    <div class="kpi-info">
      <h4><?= $certCount ?></h4>
      <span>Total Sertifikat</span>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon purple">
      <i class="fas fa-briefcase"></i>
    </div>
    <div class="kpi-info">
      <h4><?= $expCount ?></h4>
      <span>Pengalaman Kerja</span>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon amber">
      <i class="fas fa-envelope"></i>
    </div>
    <div class="kpi-info">
      <h4><?= $unreadMsgCount ?></h4>
      <span>Pesan Baru</span>
    </div>
  </div>
</div>

<!-- Quick Actions Banner -->
<div style="background: #ffffff; border: 1px solid var(--admin-border); border-radius: var(--admin-radius); padding: 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h3 style="font-family: var(--admin-heading); font-size: 1.15rem; color: #0f172a; margin-bottom: 0.25rem;">
      Aksi Cepat Pengelolaan
    </h3>
    <p style="font-size: 0.88rem; color: #64748b;">Tambahkan portofolio atau sertifikat terbaru Anda dalam beberapa klik.</p>
  </div>
  <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
    <a href="<?= base_url('MrSvz1404/certified.php?action=add') ?>" class="btn-admin-action" style="background: #0284c7;">
      <i class="fas fa-plus-circle"></i> Tambah Sertifikat
    </a>
    <a href="<?= base_url('MrSvz1404/projects.php?action=add') ?>" class="btn-admin-action" style="background: #10b981;">
      <i class="fas fa-plus-circle"></i> Tambah Project
    </a>
    <a href="<?= base_url('MrSvz1404/profile.php') ?>" class="btn-outline-action">
      <i class="fas fa-user-edit"></i> Edit Biodata
    </a>
  </div>
</div>

<!-- Two Column Tables: Projects & Certificates -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 1.75rem; margin-bottom: 2rem;">
  
  <!-- Projects Summary Card -->
  <div class="admin-card" style="margin-bottom: 0;">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="fas fa-laptop-code" style="color: #0284c7; margin-right: 6px;"></i> Project Terbaru</h3>
      <a href="<?= base_url('MrSvz1404/projects.php') ?>" style="font-size: 0.85rem; color: var(--admin-primary); font-weight: 700;">Lihat Semua &rarr;</a>
    </div>
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Gambar</th>
            <th>Judul & Kategori</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentProjects)): ?>
            <tr><td colspan="3" style="text-align: center; color: #94a3b8;">Belum ada project yang ditambahkan.</td></tr>
          <?php else: ?>
            <?php foreach ($recentProjects as $p): ?>
              <tr>
                <td>
                  <img src="<?= base_url($p['image'] ?: 'assets/images/proj-automation.jpg') ?>" class="table-img" alt="">
                </td>
                <td>
                  <strong><?= sanitize($p['title']) ?></strong>
                  <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">
                    <span class="badge-status blue"><?= sanitize($p['category']) ?></span>
                  </div>
                </td>
                <td>
                  <a href="<?= base_url('MrSvz1404/projects.php?action=edit&id=' . $p['id']) ?>" class="btn-icon edit" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Certificates Summary Card -->
  <div class="admin-card" style="margin-bottom: 0;">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="fas fa-certificate" style="color: #10b981; margin-right: 6px;"></i> Sertifikat Terbaru</h3>
      <a href="<?= base_url('MrSvz1404/certified.php') ?>" style="font-size: 0.85rem; color: var(--admin-primary); font-weight: 700;">Lihat Semua &rarr;</a>
    </div>
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Gambar</th>
            <th>Sertifikat & Penerbit</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentCerts)): ?>
            <tr><td colspan="3" style="text-align: center; color: #94a3b8;">Belum ada sertifikat yang ditambahkan.</td></tr>
          <?php else: ?>
            <?php foreach ($recentCerts as $c): ?>
              <tr>
                <td>
                  <img src="<?= base_url($c['image'] ?: 'assets/images/cert-welder.jpg') ?>" class="table-img" alt="">
                </td>
                <td>
                  <strong><?= sanitize($c['title']) ?></strong>
                  <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">
                    <?= sanitize($c['issuer']) ?> (<?= sanitize($c['issue_date']) ?>)
                  </div>
                </td>
                <td>
                  <a href="<?= base_url('MrSvz1404/certified.php?action=edit&id=' . $c['id']) ?>" class="btn-icon edit" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Contact Messages Table -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title"><i class="fas fa-inbox" style="color: #f59e0b; margin-right: 6px;"></i> Pesan Masuk dari Pengunjung</h3>
  </div>
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Nama Pengirim</th>
          <th>Email</th>
          <th>Subjek</th>
          <th>Isi Pesan</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recentMessages)): ?>
          <tr><td colspan="5" style="text-align: center; color: #94a3b8; padding: 2rem;">Belum ada pesan masuk dari pengunjung.</td></tr>
        <?php else: ?>
          <?php foreach ($recentMessages as $m): ?>
            <tr>
              <td style="font-size: 0.8rem; color: #64748b;"><?= date('d M Y, H:i', strtotime($m['created_at'])) ?></td>
              <td><strong><?= sanitize($m['name']) ?></strong></td>
              <td><a href="mailto:<?= sanitize($m['email']) ?>" style="color: var(--admin-primary);"><?= sanitize($m['email']) ?></a></td>
              <td><?= sanitize($m['subject']) ?></td>
              <td style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                <?= sanitize($m['message']) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

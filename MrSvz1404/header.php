<?php
require_once __DIR__ . '/auth_check.php';
$currentPage = basename($_SERVER['PHP_SELF']);
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'Admin Control Panel' ?> | Mas Putra</title>
  
  <link rel="icon" type="image/png" href="<?= base_url('assets/images/avatar.png') ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body class="admin-body">

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="sidebar-header">
      <div class="sidebar-brand-badge">P</div>
      <div class="sidebar-brand-text">
        <h2>PORTFOLIO</h2>
        <span>ADMIN CONTROL</span>
      </div>
    </div>

    <ul class="sidebar-menu">
      <li class="sidebar-heading">Menu Utama</li>
      <li class="sidebar-item">
        <a href="<?= base_url('MrSvz1404/index.php') ?>" class="sidebar-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">
          <i class="fas fa-chart-pie"></i>
          <span>Dashboard</span>
        </a>
      </li>

      <li class="sidebar-heading">Koleksi Portofolio</li>
      <li class="sidebar-item">
        <a href="<?= base_url('MrSvz1404/certified.php') ?>" class="sidebar-link <?= $currentPage === 'certified.php' ? 'active' : '' ?>">
          <i class="fas fa-certificate"></i>
          <span>Kelola Certified</span>
        </a>
      </li>
      <li class="sidebar-item">
        <a href="<?= base_url('MrSvz1404/projects.php') ?>" class="sidebar-link <?= $currentPage === 'projects.php' ? 'active' : '' ?>">
          <i class="fas fa-laptop-code"></i>
          <span>Kelola Projects</span>
        </a>
      </li>

      <li class="sidebar-heading">Data Karir & CV</li>
      <li class="sidebar-item">
        <a href="<?= base_url('MrSvz1404/experience.php') ?>" class="sidebar-link <?= $currentPage === 'experience.php' ? 'active' : '' ?>">
          <i class="fas fa-briefcase"></i>
          <span>Pengalaman Kerja</span>
        </a>
      </li>
      <li class="sidebar-item">
        <a href="<?= base_url('MrSvz1404/education.php') ?>" class="sidebar-link <?= $currentPage === 'education.php' ? 'active' : '' ?>">
          <i class="fas fa-graduation-cap"></i>
          <span>Pendidikan</span>
        </a>
      </li>
      <li class="sidebar-item">
        <a href="<?= base_url('MrSvz1404/skills.php') ?>" class="sidebar-link <?= $currentPage === 'skills.php' ? 'active' : '' ?>">
          <i class="fas fa-sliders-h"></i>
          <span>Skills & Bahasa</span>
        </a>
      </li>
      <li class="sidebar-item">
        <a href="<?= base_url('MrSvz1404/profile.php') ?>" class="sidebar-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>">
          <i class="fas fa-user-edit"></i>
          <span>Biodata & Profil</span>
        </a>
      </li>

      <li class="sidebar-heading">Pengaturan</li>
      <li class="sidebar-item">
        <a href="<?= base_url('MrSvz1404/settings.php') ?>" class="sidebar-link <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
          <i class="fas fa-lock"></i>
          <span>Ganti Password</span>
        </a>
      </li>
      <li class="sidebar-item">
        <a href="<?= base_url('cv.php') ?>" target="_blank" class="sidebar-link">
          <i class="fas fa-file-pdf"></i>
          <span>Lihat Format CV</span>
        </a>
      </li>
      <li class="sidebar-item">
        <a href="<?= base_url() ?>" target="_blank" class="sidebar-link">
          <i class="fas fa-globe"></i>
          <span>Lihat Website</span>
        </a>
      </li>
    </ul>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <img src="<?= base_url($profile['avatar'] ?: 'assets/images/avatar.png') ?>" alt="User" class="sidebar-user-avatar">
        <div class="sidebar-user-info">
          <span><?= sanitize($profile['full_name']) ?></span>
          <small>Administrator</small>
        </div>
      </div>
      <a href="<?= base_url('MrSvz1404/logout.php') ?>" title="Keluar / Logout" style="color: #ef4444; font-size: 1.1rem;">
        <i class="fas fa-sign-out-alt"></i>
      </a>
    </div>
  </aside>

  <!-- Main Content Wrapper -->
  <main class="admin-main">
    <header class="admin-topbar">
      <div class="topbar-left">
        <button class="topbar-toggle" aria-label="Toggle sidebar">
          <i class="fas fa-bars"></i>
        </button>
        <div class="topbar-title">
          <h1><?= $pageTitle ?? 'Admin Control' ?></h1>
        </div>
      </div>

      <div class="topbar-right">
        <a href="<?= base_url() ?>" target="_blank" class="btn-outline-action">
          <i class="fas fa-external-link-alt"></i> Web Publik
        </a>
        <a href="<?= base_url('MrSvz1404/logout.php') ?>" class="btn-outline-action" style="color: #ef4444; border-color: #fca5a5;">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </header>

    <div class="admin-content">
      <?php
        $adminHost = $_SERVER['HTTP_HOST'] ?? '';
        $isAdminLocalhost = in_array($adminHost, ['localhost', '127.0.0.1']) || strpos($adminHost, 'localhost:') === 0;
        $isAdminProduction = !$isAdminLocalhost || !empty($_ENV['VERCEL']) || !empty($_SERVER['VERCEL']) || !empty($_SERVER['HTTP_X_VERCEL_ID']);
      ?>
      <?php if ($isAdminProduction): ?>
        <div class="alert-banner warning" style="background: #fffbeb; border: 1.5px solid #fcd34d; color: #92400e; margin-bottom: 1.25rem; display: flex; align-items: flex-start; gap: 0.85rem; padding: 1rem 1.25rem; border-radius: 10px;">
          <i class="fas fa-exclamation-triangle" style="font-size: 1.25rem; color: #d97706; margin-top: 2px;"></i>
          <div style="font-size: 0.88rem; line-height: 1.5;">
            <strong style="font-size: 0.95rem; color: #b45309; display: block; margin-bottom: 3px;">Perhatian: Anda sedang membuka Admin Panel di Hosting Online (Vercel / masputra.xyz)</strong>
            Server cloud Vercel bersifat <em>Read-Only</em> (hanya baca). Untuk menambah / mengedit proyek, sertifikat, atau upload foto baru, silakan lakukan di <strong>komputer lokal Anda (<a href="http://localhost/MasPutra/MrSvz1404/" target="_blank" style="color: #b45309; text-decoration: underline; font-weight: 700;">http://localhost/MasPutra/MrSvz1404/</a>)</strong>, lalu upload perubahannya ke GitHub agar otomatis terpasang di website online.
          </div>
        </div>
      <?php endif; ?>

      <?php if ($flash): ?>
        <div class="alert-banner <?= $flash['type'] ?>">
          <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : ($flash['type'] === 'danger' ? 'fa-exclamation-circle' : 'fa-info-circle') ?>"></i>
          <span><?= $flash['message'] ?></span>
        </div>
      <?php endif; ?>

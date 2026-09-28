<?php
require_once __DIR__ . '/functions.php';
$profile = getProfileData();
$currentLang = current_lang();
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= sanitize($profile['full_name']) ?> | <?= sanitize($profile['title']) ?> - Portofolio & CV</title>
  <meta name="description" content="Official Portfolio of <?= sanitize($profile['full_name']) ?> - <?= sanitize($profile['title']) ?>. Combination Welder 6G & Software Programmer (Node.js, Web Automation, PHP).">
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="<?= base_url('assets/images/avatar.png') ?>">
  
  <!-- Font Awesome 6 Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  
  <!-- Main Stylesheet -->
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

  <!-- Sticky Navbar -->
  <nav class="navbar-custom">
    <div class="nav-container">
      <a href="<?= base_url() ?>" class="brand-logo">
        <div class="brand-badge">S</div>
        <div class="brand-text">
          <h1><?= sanitize($profile['full_name']) ?></h1>
          <span><?= sanitize($profile['title']) ?></span>
        </div>
      </a>

      <!-- Desktop Nav -->
      <ul class="nav-links">
        <li><a href="#home" class="nav-link"><?= __('nav_home') ?></a></li>
        <li><a href="#about" class="nav-link"><?= __('nav_about') ?></a></li>
        <li><a href="#skills" class="nav-link"><?= __('nav_skills') ?></a></li>
        <li><a href="#experience" class="nav-link"><?= __('nav_experience') ?></a></li>
        <li><a href="#projects" class="nav-link"><?= __('nav_projects') ?></a></li>
        <li><a href="#certified" class="nav-link"><?= __('nav_certified') ?></a></li>
        <li><a href="#contact" class="nav-link"><?= __('nav_contact') ?></a></li>
      </ul>

      <!-- Action Buttons & Language Switcher -->
      <div class="nav-actions">
        <!-- Language Switcher (EN default | ID) -->
        <?= render_lang_switcher() ?>

        <a href="<?= base_url('cv.php') ?>" target="_blank" class="btn-cv" title="<?= __('nav_view_cv') ?>">
          <i class="fas fa-file-pdf"></i> <?= __('nav_view_cv') ?>
        </a>
        <button class="mobile-toggle" aria-label="Toggle navigation">
          <i class="fas fa-bars"></i>
        </button>
      </div>
    </div>
  </nav>

  <!-- Mobile Drawer Menu -->
  <div class="mobile-drawer">
    <div>
      <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
        <div style="display: flex; align-items: center; gap: 0.6rem;">
          <div class="brand-badge" style="width: 34px; height: 34px; font-size: 1rem;">S</div>
          <strong style="font-size: 1.1rem; color: #0f172a;"><?= sanitize($profile['full_name']) ?></strong>
        </div>
        <button class="drawer-close" style="background: none; border: none; font-size: 1.4rem; color: #64748b; cursor: pointer;">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Mobile Language Switcher -->
      <div style="margin-top: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Language / Bahasa:</span>
        <?= render_lang_switcher() ?>
      </div>

      <ul class="drawer-links">
        <li><a href="#home" class="drawer-link"><i class="fas fa-home"></i> <?= __('nav_home') ?></a></li>
        <li><a href="#about" class="drawer-link"><i class="fas fa-user"></i> <?= __('nav_about') ?></a></li>
        <li><a href="#skills" class="drawer-link"><i class="fas fa-chart-bar"></i> <?= __('nav_skills') ?></a></li>
        <li><a href="#experience" class="drawer-link"><i class="fas fa-briefcase"></i> <?= __('nav_experience') ?></a></li>
        <li><a href="#projects" class="drawer-link"><i class="fas fa-laptop-code"></i> <?= __('nav_projects') ?></a></li>
        <li><a href="#certified" class="drawer-link"><i class="fas fa-certificate"></i> <?= __('nav_certified') ?></a></li>
        <li><a href="#contact" class="drawer-link"><i class="fas fa-envelope"></i> <?= __('nav_contact') ?></a></li>
      </ul>
    </div>
    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
      <a href="<?= base_url('cv.php') ?>" target="_blank" class="btn-cv" style="justify-content: center;">
        <i class="fas fa-file-pdf"></i> <?= __('nav_view_cv') ?>
      </a>
    </div>
  </div>

<?php
require_once __DIR__ . '/includes/functions.php';

$db = Database::getConnection();
$profile = getProfileData();
$currentLang = current_lang();

// Fetch Skills
$skills = $db->query("SELECT * FROM skills WHERE category = 'skill' ORDER BY sort_order ASC, id ASC")->fetchAll();
$languages = $db->query("SELECT * FROM skills WHERE category = 'bahasa' ORDER BY sort_order ASC, id ASC")->fetchAll();

// Fetch Education
$educations = $db->query("SELECT * FROM education ORDER BY sort_order ASC, id ASC")->fetchAll();

// Fetch Experience
$experiences = $db->query("SELECT * FROM experience ORDER BY sort_order ASC, id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Curriculum Vitae - <?= sanitize($profile['full_name']) ?> (<?= sanitize($profile['title']) ?>)</title>
  
  <link rel="icon" type="image/png" href="<?= base_url('assets/images/avatar.png') ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="<?= base_url('assets/css/cv.css') ?>">
</head>
<body>

  <!-- Floating Action Toolbar (Hidden when printed) -->
  <div class="cv-toolbar">
    <?= render_lang_switcher() ?>

    <a href="<?= base_url() ?>" class="cv-tool-btn">
      <i class="fas fa-arrow-left"></i> <?= __('cv_back_btn') ?>
    </a>
    <button onclick="window.print()" class="cv-tool-btn primary">
      <i class="fas fa-print"></i> <?= __('cv_print_btn') ?>
    </button>
  </div>

  <div class="cv-page-wrapper">
    <div class="cv-paper">
      
      <!-- Top Curved Header -->
      <div class="cv-header-graphic">
        <div class="cv-wave-1"></div>
        <div class="cv-wave-2"></div>
      </div>

      <!-- Header Content (Avatar & Bio details) -->
      <div class="cv-header-content">
        <div class="cv-avatar-container">
          <img src="<?= base_url($profile['avatar'] ?: 'assets/images/avatar.png') ?>" alt="<?= sanitize($profile['full_name']) ?>">
        </div>

        <div class="cv-header-details">
          <h1 class="cv-name"><?= sanitize($profile['full_name']) ?></h1>
          <div class="cv-title"><?= sanitize($profile['title']) ?></div>

          <div class="cv-contact-grid">
            <div class="cv-contact-item">
              <i class="fas fa-phone-alt"></i>
              <span><?= sanitize($profile['phone']) ?></span>
            </div>
            <div class="cv-contact-item">
              <i class="fas fa-envelope"></i>
              <span><?= sanitize($profile['email']) ?></span>
            </div>
            <div class="cv-contact-item">
              <i class="fas fa-map-marker-alt"></i>
              <span><?= sanitize($profile['address']) ?></span>
            </div>
            <div class="cv-contact-item">
              <i class="fab fa-linkedin"></i>
              <span><a href="<?= sanitize($profile['linkedin']) ?>" target="_blank"><?= sanitize($profile['linkedin']) ?></a></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Body: 2 Columns -->
      <div class="cv-body">
        
        <!-- Left Column -->
        <div class="cv-left-col">
          
          <!-- PENDIDIKAN / EDUCATION -->
          <div class="cv-section-title-wrap">
            <div class="cv-section-badge-circle"></div>
            <h2 class="cv-section-title"><?= __('cv_edu_title') ?></h2>
          </div>

          <div style="margin-bottom: 25px;">
            <?php foreach ($educations as $edu): 
              $ledu = get_localized_edu($edu);
            ?>
              <div class="cv-edu-item">
                <div class="cv-edu-year"><?= sanitize($ledu['year_range']) ?></div>
                <div class="cv-edu-inst"><?= sanitize($ledu['institution']) ?></div>
                <div class="cv-edu-major"><?= sanitize($ledu['major']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- SKILLS -->
          <div class="cv-section-title-wrap">
            <div class="cv-section-badge-circle"></div>
            <h2 class="cv-section-title"><?= __('cv_skills_title') ?></h2>
          </div>

          <div style="margin-bottom: 20px;">
            <?php foreach ($skills as $s): ?>
              <div class="cv-skill-item">
                <div class="cv-skill-name">
                  <span class="cv-skill-bullet"></span>
                  <span><?= sanitize($s['name']) ?></span>
                </div>
                <div class="cv-bar-track">
                  <div class="cv-bar-fill" style="width: <?= (int)$s['percentage'] ?>%;"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="cv-divider"></div>

          <!-- BAHASA / LANGUAGES -->
          <div class="cv-section-title-wrap">
            <div class="cv-section-badge-circle"></div>
            <h2 class="cv-section-title"><?= __('cv_lang_title') ?></h2>
          </div>

          <div>
            <?php foreach ($languages as $l): ?>
              <div class="cv-skill-item">
                <div class="cv-skill-name">
                  <span>
                    <?= sanitize(get_localized_language_name($l['name'])) ?>
                  </span>
                </div>
                <div class="cv-bar-track">
                  <div class="cv-bar-fill" style="width: <?= (int)$l['percentage'] ?>%;"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

        </div>

        <!-- Right Column -->
        <div class="cv-right-col">
          
          <!-- TENTANG SAYA / ABOUT ME -->
          <div class="cv-section-title-wrap">
            <div class="cv-section-badge-circle"></div>
            <h2 class="cv-section-title"><?= __('cv_about_title') ?></h2>
          </div>

          <p class="cv-about-text">
            <?= sanitize(get_localized_bio($profile)) ?>
          </p>

          <div class="cv-divider" style="margin: 0 0 25px 0;"></div>

          <!-- PENGALAMAN KERJA / WORK EXPERIENCE -->
          <div class="cv-section-title-wrap">
            <div class="cv-section-badge-circle"></div>
            <h2 class="cv-section-title"><?= __('cv_exp_title') ?></h2>
          </div>

          <div class="cv-exp-timeline">
            <?php foreach ($experiences as $exp): 
              $lexp = get_localized_exp($exp);
            ?>
              <div class="cv-exp-node">
                <div class="cv-exp-bullet"></div>
                <div class="cv-exp-date"><?= sanitize($lexp['date_range']) ?></div>
                <div class="cv-exp-company"><?= sanitize($lexp['company']) ?></div>

                <div class="cv-exp-role-list">
                  <?php
                  $lines = explode("\n", str_replace("\r", "", $lexp['details']));
                  foreach ($lines as $line):
                    $trimmed = trim($line);
                    if (empty($trimmed)) continue;
                    // If line starts with bullet
                    $cleanLine = ltrim($trimmed, "•-* \t");
                  ?>
                    <div class="cv-exp-role-item">
                      <span class="sub-bullet"></span>
                      <span><?= sanitize($cleanLine) ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

        </div>

      </div>

    </div>
  </div>

</body>
</html>

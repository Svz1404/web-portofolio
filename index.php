<?php
require_once __DIR__ . '/includes/functions.php';

$db = Database::getConnection();

// Handle Contact Form Submission
$contactSuccess = false;
$contactError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name = clean_input($_POST['name'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $subject = clean_input($_POST['subject'] ?? '');
    $message = clean_input($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $contactError = current_lang() === 'en' ? 'Please fill in all required fields.' : 'Harap lengkapi semua kolom yang wajib diisi.';
    } else {
        $stmt = $db->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        $contactSuccess = true;
    }
}

// Fetch Profile
$profile = getProfileData();
$currentLang = current_lang();

// Fetch Skills
$skillsQuery = $db->query("SELECT * FROM skills WHERE category = 'skill' ORDER BY sort_order ASC, id ASC");
$skills = $skillsQuery->fetchAll();

// Fetch Languages
$langQuery = $db->query("SELECT * FROM skills WHERE category = 'bahasa' ORDER BY sort_order ASC, id ASC");
$languages = $langQuery->fetchAll();

// Fetch Education
$eduQuery = $db->query("SELECT * FROM education ORDER BY sort_order ASC, id ASC");
$educations = $eduQuery->fetchAll();

// Fetch Experience
$expQuery = $db->query("SELECT * FROM experience ORDER BY sort_order ASC, id ASC");
$experiences = $expQuery->fetchAll();

// Fetch Certificates
$certQuery = $db->query("SELECT * FROM certificates ORDER BY sort_order ASC, id DESC");
$certificates = $certQuery->fetchAll();

// Fetch Certificate Gallery Images (Timbal Balik / Multi-Foto)
$certGalleryRows = $db->query("SELECT certificate_id, image, caption FROM certificate_images ORDER BY sort_order ASC, id ASC")->fetchAll();
$certificateGalleries = [];
foreach ($certGalleryRows as $cgr) {
    $certificateGalleries[$cgr['certificate_id']][] = [
        'url' => base_url($cgr['image']),
        'caption' => $cgr['caption'] ?? ''
    ];
}

// Fetch Projects
$projQuery = $db->query("SELECT * FROM projects ORDER BY sort_order ASC, id DESC");
$projects = $projQuery->fetchAll();

// Fetch Project Gallery Images
$galleryRows = $db->query("SELECT project_id, image, caption FROM project_images ORDER BY sort_order ASC, id ASC")->fetchAll();
$projectGalleries = [];
foreach ($galleryRows as $grow) {
    $projectGalleries[$grow['project_id']][] = [
        'url' => base_url($grow['image']),
        'caption' => $grow['caption'] ?? ''
    ];
}

// Get unique project categories for filter
$categories = [];
foreach ($projects as $p) {
    if (!empty($p['category']) && !in_array($p['category'], $categories)) {
        $categories[] = $p['category'];
    }
}

include __DIR__ . '/includes/header.php';
?>

  <!-- Hero Section -->
  <section class="hero-section" id="home">
    <div class="hero-wave-bg"></div>
    <div class="hero-container">
      <div class="hero-card">
        <div class="hero-grid">
          <!-- Avatar Column -->
          <div class="hero-avatar-wrapper">
            <div class="avatar-ring">
              <img src="<?= base_url($profile['avatar'] ?: 'assets/images/avatar.png') ?>" alt="<?= sanitize($profile['full_name']) ?>" class="avatar-img">
            </div>
            <div class="hero-status-pill">
              <span class="status-dot"></span> <?= __('hero_status') ?>
            </div>
          </div>

          <!-- Info Column -->
          <div class="hero-info">
            <div class="hero-info-header">
              <span class="hero-subtitle-tag"><i class="fas fa-check-circle"></i> <?= __('hero_badge') ?></span>
              <h1 class="hero-name"><?= sanitize($profile['full_name']) ?></h1>
              <div class="hero-role">
                <span><?= sanitize($profile['title']) ?></span>
              </div>
            </div>

            <p class="hero-bio">
              <?= sanitize(get_localized_bio($profile)) ?>
            </p>

            <!-- Contact Pills -->
            <div class="hero-contact-row">
              <?php if (!empty($profile['phone'])): ?>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $profile['phone']) ?>" target="_blank" class="contact-pill-item">
                  <i class="fab fa-whatsapp"></i>
                  <div>
                    <span style="font-size: 0.72rem; color: #64748b; display: block;"><?= __('hero_phone_label') ?></span>
                    <strong><?= sanitize($profile['phone']) ?></strong>
                  </div>
                </a>
              <?php endif; ?>

              <?php if (!empty($profile['email'])): ?>
                <a href="mailto:<?= sanitize($profile['email']) ?>" class="contact-pill-item">
                  <i class="fas fa-envelope"></i>
                  <div>
                    <span style="font-size: 0.72rem; color: #64748b; display: block;"><?= __('hero_email_label') ?></span>
                    <strong><?= sanitize($profile['email']) ?></strong>
                  </div>
                </a>
              <?php endif; ?>

              <?php if (!empty($profile['address'])): ?>
                <div class="contact-pill-item">
                  <i class="fas fa-map-marker-alt"></i>
                  <div>
                    <span style="font-size: 0.72rem; color: #64748b; display: block;"><?= __('hero_location_label') ?></span>
                    <strong><?= sanitize($profile['address']) ?></strong>
                  </div>
                </div>
              <?php endif; ?>

              <?php if (!empty($profile['linkedin'])): ?>
                <a href="<?= sanitize($profile['linkedin']) ?>" target="_blank" rel="noopener" class="contact-pill-item">
                  <i class="fab fa-linkedin-in"></i>
                  <div>
                    <span style="font-size: 0.72rem; color: #64748b; display: block;"><?= __('hero_linkedin_label') ?></span>
                    <strong>/in/masputra1404</strong>
                  </div>
                </a>
              <?php endif; ?>
            </div>

            <!-- CTA Buttons -->
            <div class="hero-cta-buttons">
              <a href="#projects" class="btn-primary-gradient">
                <i class="fas fa-folder-open"></i> <?= __('btn_view_projects') ?>
              </a>
              <a href="#certified" class="btn-secondary-outline">
                <i class="fas fa-award"></i> <?= __('btn_view_certs') ?>
              </a>
              <a href="<?= base_url('cv.php') ?>" target="_blank" class="btn-secondary-outline">
                <i class="fas fa-print"></i> <?= __('btn_cv_print') ?>
              </a>
              <a href="#contact" class="btn-primary-gradient" style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="fab fa-whatsapp"></i> <?= __('btn_contact_me') ?>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Stats Bar -->
  <section class="stats-section">
    <div class="section-container">
      <div class="stats-grid">
        <div class="stat-box">
          <div class="stat-icon blue"><i class="fas fa-history"></i></div>
          <div class="stat-content">
            <h3><?= __('stat_exp_years') ?></h3>
            <p><?= __('stat_exp_desc') ?></p>
          </div>
        </div>
        <div class="stat-box">
          <div class="stat-icon cyan"><i class="fas fa-layer-group"></i></div>
          <div class="stat-content">
            <h3><?= count($projects) ?> <?= __('stat_projects') ?></h3>
            <p><?= __('stat_projects_desc') ?></p>
          </div>
        </div>
        <div class="stat-box">
          <div class="stat-icon orange"><i class="fas fa-certificate"></i></div>
          <div class="stat-content">
            <h3><?= count($certificates) ?> <?= __('stat_certs') ?></h3>
            <p><?= __('stat_certs_desc') ?></p>
          </div>
        </div>
        <div class="stat-box">
          <div class="stat-icon green"><i class="fas fa-tools"></i></div>
          <div class="stat-content">
            <h3><?= __('stat_disciplines') ?></h3>
            <p><?= __('stat_disciplines_desc') ?></p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section class="section-pad" id="about" style="background-color: #ffffff;">
    <div class="section-container">
      <div class="section-header">
        <span class="section-badge"><i class="fas fa-user"></i> <?= __('about_badge') ?></span>
        <h2 class="section-title"><?= __('about_title') ?></h2>
        <p class="section-subtitle">
          <?= __('about_subtitle') ?>
        </p>
      </div>

      <div class="about-grid">
        <div class="about-card">
          <div>
            <h3 class="about-card-title"><i class="fas fa-fire-alt"></i> <?= __('about_welder_title') ?></h3>
            <div class="about-card-body">
              <p><?= __('about_welder_intro') ?></p>
              <ul class="feature-list">
                <li class="feature-item">
                  <i class="fas fa-check-circle"></i>
                  <div><?= __('about_welder_f1') ?></div>
                </li>
                <li class="feature-item">
                  <i class="fas fa-check-circle"></i>
                  <div><?= __('about_welder_f2') ?></div>
                </li>
                <li class="feature-item">
                  <i class="fas fa-check-circle"></i>
                  <div><?= __('about_welder_f3') ?></div>
                </li>
                <li class="feature-item">
                  <i class="fas fa-check-circle"></i>
                  <div><?= __('about_welder_f4') ?></div>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="about-card">
          <div>
            <h3 class="about-card-title"><i class="fas fa-code"></i> <?= __('about_prog_title') ?></h3>
            <div class="about-card-body">
              <p><?= __('about_prog_intro') ?></p>
              <ul class="feature-list">
                <li class="feature-item">
                  <i class="fas fa-check-circle"></i>
                  <div><?= __('about_prog_f1') ?></div>
                </li>
                <li class="feature-item">
                  <i class="fas fa-check-circle"></i>
                  <div><?= __('about_prog_f2') ?></div>
                </li>
                <li class="feature-item">
                  <i class="fas fa-check-circle"></i>
                  <div><?= __('about_prog_f3') ?></div>
                </li>
                <li class="feature-item">
                  <i class="fas fa-check-circle"></i>
                  <div><?= __('about_prog_f4') ?></div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Skills & Bahasa Section -->
  <section class="section-pad" id="skills" style="background: #f8fafc;">
    <div class="section-container">
      <div class="section-header">
        <span class="section-badge"><i class="fas fa-chart-line"></i> <?= __('skills_badge') ?></span>
        <h2 class="section-title"><?= __('skills_title') ?></h2>
        <p class="section-subtitle">
          <?= __('skills_subtitle') ?>
        </p>
      </div>

      <div class="skills-container">
        <!-- Technical Skills -->
        <div class="skills-card">
          <h3 style="font-family: var(--font-heading); font-size: 1.3rem; font-weight: 700; color: #0f172a; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.6rem;">
            <i class="fas fa-laptop-code" style="color: var(--primary);"></i> <?= __('skills_tech_heading') ?>
          </h3>
          <?php foreach ($skills as $s): ?>
            <div class="skill-row">
              <div class="skill-info">
                <span><i class="fas fa-circle" style="font-size: 0.45rem; color: #0284c7; vertical-align: middle; margin-right: 6px;"></i> <?= sanitize($s['name']) ?></span>
                <span class="skill-percent"><?= (int)$s['percentage'] ?>%</span>
              </div>
              <div class="skill-bar-bg">
                <div class="skill-bar-fill" style="width: <?= (int)$s['percentage'] ?>%;"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Language & Education Column -->
        <div style="display: flex; flex-direction: column; gap: 2rem;">
          <!-- Languages Card -->
          <div class="skills-card">
            <h3 style="font-family: var(--font-heading); font-size: 1.3rem; font-weight: 700; color: #0f172a; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.6rem;">
              <i class="fas fa-language" style="color: #ea580c;"></i> <?= __('skills_lang_heading') ?>
            </h3>
            <?php foreach ($languages as $l): ?>
              <div class="skill-row">
                <div class="skill-info">
                  <span>
                    <i class="fas fa-circle" style="font-size: 0.45rem; color: #ea580c; vertical-align: middle; margin-right: 6px;"></i> 
                    <?= sanitize(get_localized_language_name($l['name'])) ?>
                  </span>
                  <span class="skill-percent" style="color: #ea580c;"><?= (int)$l['percentage'] ?>%</span>
                </div>
                <div class="skill-bar-bg">
                  <div class="skill-bar-fill orange" style="width: <?= (int)$l['percentage'] ?>%;"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Highlight Box -->
          <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border-radius: var(--radius-lg); padding: 2rem; color: #ffffff;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
              <i class="fas fa-bolt" style="font-size: 1.5rem; color: #fde047;"></i>
              <h4 style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 800;"><?= __('skills_value_heading') ?></h4>
            </div>
            <p style="color: #e0f2fe; font-size: 0.92rem; line-height: 1.6;">
              <?= __('skills_value_text') ?>
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Experience & Education Timeline -->
  <section class="section-pad" id="experience" style="background: #ffffff;">
    <div class="section-container">
      <div class="section-header">
        <span class="section-badge"><i class="fas fa-briefcase"></i> <?= __('exp_badge') ?></span>
        <h2 class="section-title"><?= __('exp_title') ?></h2>
        <p class="section-subtitle">
          <?= __('exp_subtitle') ?>
        </p>
      </div>

      <div class="timeline-wrapper">
        <!-- Work Experience Column -->
        <div>
          <h3 class="timeline-column-title">
            <span class="dot"></span> <?= __('exp_heading') ?>
          </h3>
          <div class="timeline-list">
            <?php foreach ($experiences as $exp): 
              $lexp = get_localized_exp($exp);
            ?>
              <div class="timeline-item">
                <div class="timeline-badge-dot"></div>
                <span class="timeline-date"><?= sanitize($lexp['date_range']) ?></span>
                <h4 class="timeline-company"><?= sanitize($lexp['company']) ?></h4>
                <div class="timeline-role"><?= sanitize($lexp['role']) ?></div>
                <div class="timeline-desc"><?= nl2br(sanitize($lexp['details'])) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Education Column -->
        <div>
          <h3 class="timeline-column-title">
            <span class="dot" style="background: #ea580c; box-shadow: 0 0 0 4px #ffedd5;"></span> <?= __('edu_heading') ?>
          </h3>
          <div class="timeline-list" style="border-left-color: #fed7aa;">
            <?php foreach ($educations as $edu): 
              $ledu = get_localized_edu($edu);
            ?>
              <div class="timeline-item">
                <div class="timeline-badge-dot" style="border-color: #ea580c;"></div>
                <span class="timeline-date" style="background: #fff7ed; border-color: #fed7aa; color: #ea580c;">
                  <?= sanitize($ledu['year_range']) ?>
                </span>
                <h4 class="timeline-company"><?= sanitize($ledu['institution']) ?></h4>
                <div class="timeline-role" style="color: #0284c7;"><?= sanitize($ledu['major']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Quick CV Callout -->
          <div style="margin-top: 3rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: var(--radius-md); padding: 1.75rem; text-align: center;">
            <i class="fas fa-file-contract" style="font-size: 2.2rem; color: #0284c7; margin-bottom: 0.75rem;"></i>
            <h4 style="font-family: var(--font-heading); font-size: 1.15rem; color: #0f172a; margin-bottom: 0.4rem;"><?= __('cv_callout_title') ?></h4>
            <p style="font-size: 0.88rem; color: #64748b; margin-bottom: 1.25rem;">
              <?= __('cv_callout_desc') ?>
            </p>
            <a href="<?= base_url('cv.php') ?>" target="_blank" class="btn-cv" style="display: inline-flex;">
              <i class="fas fa-external-link-alt"></i> <?= __('cv_callout_btn') ?>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Projects Section (Menu Project) -->
  <section class="section-pad" id="projects" style="background: #f8fafc;">
    <div class="section-container">
      <div class="section-header">
        <span class="section-badge"><i class="fas fa-laptop-code"></i> <?= __('proj_badge') ?></span>
        <h2 class="section-title"><?= __('proj_title') ?></h2>
        <p class="section-subtitle">
          <?= __('proj_subtitle') ?>
        </p>
      </div>

      <?php if (empty($projects)): ?>
        <div style="text-align: center; padding: 60px 20px; background: #ffffff; border-radius: 20px; border: 1px dashed #cbd5e1; max-width: 600px; margin: 0 auto;">
          <div style="width: 70px; height: 70px; line-height: 70px; border-radius: 50%; background: rgba(37,99,235,0.08); color: var(--primary); font-size: 1.8rem; margin: 0 auto 16px;">
            <i class="fas fa-folder-open"></i>
          </div>
          <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 8px;"><?= __('proj_empty_title') ?></h3>
          <p style="color: #64748b; font-size: 0.95rem; margin: 0;"><?= __('proj_empty_desc') ?></p>
        </div>
      <?php else: ?>
        <!-- Category Filter -->
        <div class="filter-nav">
          <button class="filter-btn active" data-filter="all"><?= __('proj_filter_all') ?></button>
          <?php foreach ($categories as $cat): ?>
            <button class="filter-btn" data-filter="<?= sanitize($cat) ?>"><?= sanitize($cat) ?></button>
          <?php endforeach; ?>
        </div>

        <!-- Projects Grid -->
        <div class="projects-grid">
          <?php foreach ($projects as $proj): 
            $lproj = get_localized_proj($proj);
            $galleryList = [];
            if (!empty($lproj['image'])) {
                $galleryList[] = [
                    'url' => base_url($lproj['image']),
                    'caption' => $lproj['title']
                ];
            }
            if (!empty($projectGalleries[$lproj['id']])) {
                foreach ($projectGalleries[$lproj['id']] as $gitem) {
                    $galleryList[] = [
                        'url' => $gitem['url'],
                        'caption' => $gitem['caption'] ?: $lproj['title']
                    ];
                }
            }
            $photoCount = count($galleryList);
          ?>
            <div class="project-card" data-category="<?= sanitize($lproj['category']) ?>">
              <div class="project-image-box">
                <img src="<?= base_url($lproj['image'] ?: 'assets/images/proj-automation.jpg') ?>" alt="<?= sanitize($lproj['title']) ?>" class="project-img">
                <span class="project-category-badge"><?= sanitize($lproj['category']) ?></span>
                <?php if ($photoCount > 1): ?>
                  <span class="project-gallery-badge" title="<?= $photoCount ?> <?= __('proj_photos_count') ?>">
                    <i class="fas fa-images"></i> <?= $photoCount ?> <?= __('proj_photos_count') ?>
                  </span>
                <?php endif; ?>
              </div>

              <div class="project-body">
                <div>
                  <h3 class="project-title"><?= sanitize($lproj['title']) ?></h3>
                  <p class="project-desc"><?= sanitize($lproj['description']) ?></p>
                </div>

                <div>
                  <?php if (!empty($lproj['tech_stack'])): ?>
                    <div class="project-tech">
                      <?php foreach (explode(',', $lproj['tech_stack']) as $tag): ?>
                        <span class="tech-tag"><?= trim(sanitize($tag)) ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                  <div class="project-footer">
                    <button type="button" class="btn-link-action btn-proj-view"
                      data-title="<?= sanitize($lproj['title']) ?>"
                      data-category="<?= sanitize($lproj['category']) ?>"
                      data-tech="<?= sanitize($lproj['tech_stack']) ?>"
                      data-desc="<?= sanitize($lproj['description']) ?>"
                      data-img="<?= base_url($lproj['image'] ?: 'assets/images/proj-automation.jpg') ?>"
                      data-gallery='<?= htmlspecialchars(json_encode($galleryList), ENT_QUOTES, 'UTF-8') ?>'
                      data-live="<?= sanitize($lproj['live_url']) ?>"
                      data-github="<?= sanitize($lproj['github_url']) ?>"
                      data-photos-count="<?= $photoCount ?>"
                      data-counter-text="<?= __('proj_photo_counter') ?>"
                      data-of-text="<?= __('proj_photo_of') ?>"
                      data-gallery-hint="<?= __('proj_gallery_hint') ?>">
                      <i class="fas fa-info-circle"></i> <?= __('proj_btn_details') ?>
                    </button>

                    <?php if (!empty($lproj['live_url']) && $lproj['live_url'] !== '#'): ?>
                      <a href="<?= sanitize($lproj['live_url']) ?>" target="_blank" rel="noopener" class="btn-link-action">
                        <i class="fas fa-play"></i> <?= __('proj_btn_live') ?>
                      </a>
                    <?php endif; ?>

                    <?php if (!empty($lproj['github_url']) && $lproj['github_url'] !== '#'): ?>
                      <a href="<?= sanitize($lproj['github_url']) ?>" target="_blank" rel="noopener" class="btn-link-action" style="color: #334155;">
                        <i class="fab fa-github"></i> <?= __('proj_btn_github') ?>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Certified Section (Menu Certified) -->
  <section class="section-pad" id="certified" style="background: #ffffff;">
    <div class="section-container">
      <div class="section-header">
        <span class="section-badge"><i class="fas fa-award"></i> <?= __('cert_badge') ?></span>
        <h2 class="section-title"><?= __('cert_title') ?></h2>
        <p class="section-subtitle">
          <?= __('cert_subtitle') ?>
        </p>
      </div>

      <div class="certificates-grid">
        <?php foreach ($certificates as $cert): 
          $lcert = get_localized_cert($cert);
          $certGalleryList = [];
          if (!empty($lcert['image'])) {
              $certGalleryList[] = [
                  'url' => base_url($lcert['image']),
                  'caption' => $lcert['title'] . ' (Tampak Depan / Halaman 1)'
              ];
          }
          if (!empty($certificateGalleries[$lcert['id']])) {
              $sideNum = 2;
              foreach ($certificateGalleries[$lcert['id']] as $cgitem) {
                  $certGalleryList[] = [
                      'url' => $cgitem['url'],
                      'caption' => $cgitem['caption'] ?: ($lcert['title'] . ' (Halaman ' . $sideNum . ' / Timbal Balik)')
                  ];
                  $sideNum++;
              }
          }
          $certPhotoCount = count($certGalleryList);
        ?>
          <div class="cert-card">
            <div class="cert-preview-box">
              <img src="<?= base_url($lcert['image'] ?: 'assets/images/cert-welder.jpg') ?>" alt="<?= sanitize($lcert['title']) ?>">
              <?php if (!empty($lcert['issue_date'])): ?>
                <span class="cert-year-badge"><?= sanitize($lcert['issue_date']) ?></span>
              <?php endif; ?>
              <?php if ($certPhotoCount > 1): ?>
                <span class="cert-gallery-badge" title="<?= $certPhotoCount ?> <?= __('cert_photos_count') ?>">
                  <i class="fas fa-copy"></i> <?= $certPhotoCount ?> <?= __('cert_photos_count') ?>
                </span>
              <?php endif; ?>
            </div>

            <div class="cert-body">
              <div>
                <h3 class="cert-title"><?= sanitize($lcert['title']) ?></h3>
                <div class="cert-issuer">
                  <i class="fas fa-shield-alt"></i> <?= sanitize($lcert['issuer']) ?>
                </div>
                <?php if (!empty($lcert['credential_id'])): ?>
                  <div class="cert-id-tag">ID: <?= sanitize($lcert['credential_id']) ?></div>
                <?php endif; ?>
                <p class="cert-desc"><?= sanitize($lcert['description']) ?></p>
              </div>

              <div class="cert-actions">
                <button type="button" class="btn-cert-view"
                  data-title="<?= sanitize($lcert['title']) ?>"
                  data-issuer="<?= sanitize($lcert['issuer']) ?>"
                  data-date="<?= sanitize($lcert['issue_date']) ?>"
                  data-id="<?= sanitize($lcert['credential_id']) ?>"
                  data-desc="<?= sanitize($lcert['description']) ?>"
                  data-img="<?= base_url($lcert['image'] ?: 'assets/images/cert-welder.jpg') ?>"
                  data-gallery='<?= htmlspecialchars(json_encode($certGalleryList), ENT_QUOTES, 'UTF-8') ?>'
                  data-photos-count="<?= $certPhotoCount ?>"
                  data-counter-text="<?= __('cert_photo_counter') ?>"
                  data-of-text="<?= __('cert_photo_of') ?>"
                  data-gallery-hint="<?= __('cert_gallery_hint') ?>"
                  data-url="<?= sanitize($lcert['credential_url']) ?>"
                  data-verify-label="<?= __('cert_modal_verify') ?>"
                  data-year-na="<?= __('cert_year_na') ?>">
                  <i class="fas fa-search-plus"></i> <?= __('cert_btn_view') ?>
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section class="section-pad" id="contact" style="background: #f8fafc;">
    <div class="section-container">
      <div class="section-header">
        <span class="section-badge"><i class="fas fa-paper-plane"></i> <?= __('contact_badge') ?></span>
        <h2 class="section-title"><?= __('contact_title') ?></h2>
        <p class="section-subtitle">
          <?= __('contact_subtitle') ?>
        </p>
      </div>

      <div class="contact-grid">
        <!-- Contact Information -->
        <div class="contact-info-card">
          <div>
            <h3><?= __('contact_info_title') ?></h3>
            <p><?= __('contact_info_desc') ?></p>

            <div class="contact-methods">
              <div class="method-item">
                <div class="method-icon"><i class="fab fa-whatsapp"></i></div>
                <div class="method-detail">
                  <span><?= __('hero_phone_label') ?></span>
                  <strong><?= sanitize($profile['phone']) ?></strong>
                </div>
              </div>

              <div class="method-item">
                <div class="method-icon"><i class="fas fa-envelope"></i></div>
                <div class="method-detail">
                  <span><?= __('hero_email_label') ?></span>
                  <strong><?= sanitize($profile['email']) ?></strong>
                </div>
              </div>

              <div class="method-item">
                <div class="method-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div class="method-detail">
                  <span><?= __('hero_location_label') ?></span>
                  <strong><?= sanitize($profile['address']) ?></strong>
                </div>
              </div>

              <div class="method-item">
                <div class="method-icon"><i class="fab fa-linkedin-in"></i></div>
                <div class="method-detail">
                  <span>LinkedIn</span>
                  <strong><?= sanitize($profile['linkedin']) ?></strong>
                </div>
              </div>
            </div>
          </div>

          <div class="contact-social-links">
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $profile['phone']) ?>" target="_blank" class="social-circle-btn" title="Chat WhatsApp">
              <i class="fab fa-whatsapp"></i>
            </a>
            <a href="mailto:<?= sanitize($profile['email']) ?>" class="social-circle-btn" title="Email">
              <i class="fas fa-envelope"></i>
            </a>
            <a href="<?= sanitize($profile['linkedin']) ?>" target="_blank" class="social-circle-btn" title="LinkedIn">
              <i class="fab fa-linkedin-in"></i>
            </a>
            <?php if (!empty($profile['github'])): ?>
              <a href="<?= sanitize($profile['github']) ?>" target="_blank" class="social-circle-btn" title="GitHub">
                <i class="fab fa-github"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Contact Form -->
        <div class="contact-form-card">
          <h3 style="font-family: var(--font-heading); font-size: 1.35rem; font-weight: 700; color: #0f172a; margin-bottom: 0.5rem;">
            <?= __('contact_form_title') ?>
          </h3>
          <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 1.75rem;">
            <?= __('contact_form_desc') ?>
          </p>

          <?php if ($contactSuccess): ?>
            <div style="background: #dcfce7; color: #166534; padding: 1rem 1.25rem; border-radius: 10px; border: 1px solid #86efac; margin-bottom: 1.5rem; font-weight: 600;">
              <i class="fas fa-check-circle"></i> <?= __('msg_success') ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($contactError)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem 1.25rem; border-radius: 10px; border: 1px solid #fca5a5; margin-bottom: 1.5rem; font-weight: 600;">
              <i class="fas fa-exclamation-triangle"></i> <?= $contactError ?>
            </div>
          <?php endif; ?>

          <form action="<?= base_url('#contact') ?>" method="POST">
            <div class="form-group">
              <label for="contact_name" class="form-label"><?= __('form_name') ?></label>
              <input type="text" id="contact_name" name="name" class="form-control" placeholder="<?= __('form_name_ph') ?>" required>
            </div>

            <div class="form-group">
              <label for="contact_email" class="form-label"><?= __('form_email') ?></label>
              <input type="email" id="contact_email" name="email" class="form-control" placeholder="<?= __('form_email_ph') ?>" required>
            </div>

            <div class="form-group">
              <label for="contact_subject" class="form-label"><?= __('form_subject') ?></label>
              <input type="text" id="contact_subject" name="subject" class="form-control" placeholder="<?= __('form_subject_ph') ?>">
            </div>

            <div class="form-group">
              <label for="contact_message" class="form-label"><?= __('form_message') ?></label>
              <textarea id="contact_message" name="message" class="form-control" rows="4" placeholder="<?= __('form_message_ph') ?>" required></textarea>
            </div>

            <div style="display: flex; gap: 0.85rem; flex-wrap: wrap;">
              <button type="submit" name="send_message" class="btn-primary-gradient" style="border: none; cursor: pointer;">
                <i class="fas fa-paper-plane"></i> <?= __('btn_send_form') ?>
              </button>
              <button type="button" id="btnSendWhatsapp" class="btn-primary-gradient" style="background: linear-gradient(135deg, #10b981, #059669); border: none; cursor: pointer;">
                <i class="fab fa-whatsapp"></i> <?= __('btn_send_wa') ?>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>

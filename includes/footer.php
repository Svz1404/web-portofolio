<?php
require_once __DIR__ . '/functions.php';
$profile = getProfileData();
?>
  <!-- Global Details Modal -->
  <div class="modal-overlay" id="detailsModal">
    <div class="modal-container">
      <button class="modal-close-btn" aria-label="Close modal">
        <i class="fas fa-times"></i>
      </button>
      <div id="modalContent"></div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="footer-custom">
    <div class="footer-container">
      <div class="footer-top">
        <div class="footer-brand">
          <h2><?= sanitize($profile['full_name']) ?></h2>
          <p style="color: #38bdf8; font-size: 0.9rem; font-weight: 600; margin-top: 0.25rem;">
            <?= sanitize($profile['title']) ?>
          </p>
          <p style="color: #94a3b8; font-size: 0.85rem; max-width: 450px; margin-top: 0.5rem;">
            Kombinasi keahlian di bidang Teknologi Informasi (Software Development & Web Automation) dan Industri Manufaktur (Welder 6G GTAW/SMAW/GMAW).
          </p>
        </div>

        <div style="display: flex; gap: 0.85rem;">
          <?php if (!empty($profile['phone'])): ?>
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $profile['phone']) ?>" target="_blank" class="social-circle-btn" title="WhatsApp">
              <i class="fab fa-whatsapp"></i>
            </a>
          <?php endif; ?>
          <?php if (!empty($profile['email'])): ?>
            <a href="mailto:<?= sanitize($profile['email']) ?>" class="social-circle-btn" title="Email">
              <i class="fas fa-envelope"></i>
            </a>
          <?php endif; ?>
          <?php if (!empty($profile['linkedin'])): ?>
            <a href="<?= sanitize($profile['linkedin']) ?>" target="_blank" class="social-circle-btn" title="LinkedIn">
              <i class="fab fa-linkedin-in"></i>
            </a>
          <?php endif; ?>
          <?php if (!empty($profile['github'])): ?>
            <a href="<?= sanitize($profile['github']) ?>" target="_blank" class="social-circle-btn" title="GitHub">
              <i class="fab fa-github"></i>
            </a>
          <?php endif; ?>
        </div>
      </div>

      <div class="footer-bottom">
        <div>
          &copy; <?= date('Y') ?> <strong><?= sanitize($profile['full_name']) ?></strong>. All rights reserved.
        </div>
        <div style="display: flex; gap: 1.5rem; font-size: 0.82rem;">
          <a href="<?= base_url('cv.php') ?>" target="_blank" style="color: #94a3b8;">Format CV Asli</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>

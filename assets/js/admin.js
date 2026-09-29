/**
 * Admin Panel JavaScript
 */

// Image Error Auto-Recovery: Fallback to GitHub Raw CDN if an uploaded image fails to load
window.addEventListener('error', function (e) {
  if (e.target && e.target.tagName === 'IMG') {
    const src = e.target.getAttribute('src');
    if (src && src.includes('/uploads/') && !src.includes('raw.githubusercontent.com')) {
      const uploadIdx = src.indexOf('/uploads/');
      const relPath = src.substring(uploadIdx);
      e.target.src = 'https://raw.githubusercontent.com/Svz1404/web-portofolio/main' + relPath;
    }
  }
}, true);

document.addEventListener('DOMContentLoaded', function () {
  // Sidebar Toggle on Mobile
  const toggleBtn = document.querySelector('.topbar-toggle');
  const sidebar = document.querySelector('.admin-sidebar');

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function () {
      sidebar.classList.toggle('open');
    });
  }

  // Generic Modal Opener
  const modalTriggers = document.querySelectorAll('[data-toggle="modal"]');
  modalTriggers.forEach(function (btn) {
    btn.addEventListener('click', function () {
      const targetId = this.getAttribute('data-target');
      const targetModal = document.querySelector(targetId);
      if (targetModal) {
        targetModal.classList.add('show');
      }
    });
  });

  // Modal Closers
  const closeBtns = document.querySelectorAll('[data-dismiss="modal"], .admin-modal');
  closeBtns.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      if (e.target === this || this.hasAttribute('data-dismiss')) {
        const modal = this.closest('.admin-modal') || this;
        modal.classList.remove('show');
      }
    });
  });

  // Image Preview on file upload
  const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
  fileInputs.forEach(function (input) {
    input.addEventListener('change', function () {
      const previewId = this.getAttribute('data-preview');
      const previewBox = document.getElementById(previewId);
      if (!previewBox) return;

      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          let img = previewBox.querySelector('img');
          if (!img) {
            img = document.createElement('img');
            previewBox.appendChild(img);
          }
          img.src = e.target.result;
          previewBox.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });
  });
});

// Confirmation helper
function confirmDelete(message) {
  return confirm(message || 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.');
}

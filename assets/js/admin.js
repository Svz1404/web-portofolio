/**
 * Admin Panel JavaScript
 * Includes Auto Image Compression (bypasses server upload limits safely)
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

/**
 * Compress an image file using HTML5 Canvas
 * Reduces modern smartphone camera photos (5MB - 15MB) to crisp ~300KB web images.
 */
function compressImage(file, maxDimension = 1920, quality = 0.85) {
  return new Promise((resolve) => {
    // Only compress standard raster images (skip SVG, GIF)
    if (!file.type.match(/image\/(jpeg|png|webp|jpg)/i)) {
      return resolve(file);
    }

    // If file is already small (under 800KB), return as-is
    if (file.size < 800 * 1024) {
      return resolve(file);
    }

    const reader = new FileReader();
    reader.onerror = () => resolve(file);
    reader.onload = function (event) {
      const img = new Image();
      img.onerror = () => resolve(file);
      img.onload = function () {
        let width = img.width;
        let height = img.height;

        if (width > maxDimension || height > maxDimension) {
          if (width > height) {
            height = Math.round((height * maxDimension) / width);
            width = maxDimension;
          } else {
            width = Math.round((width * maxDimension) / height);
            height = maxDimension;
          }
        }

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, height);
        ctx.drawImage(img, 0, 0, width, height);

        canvas.toBlob(
          function (blob) {
            if (!blob || blob.size >= file.size) {
              return resolve(file); // Don't use if didn't reduce size
            }
            const cleanName = file.name.replace(/\.[^.]+$/, '.jpg');
            const compressedFile = new File([blob], cleanName, {
              type: 'image/jpeg',
              lastModified: Date.now()
            });
            resolve(compressedFile);
          },
          'image/jpeg',
          quality
        );
      };
      img.src = event.target.result;
    };
    reader.readAsDataURL(file);
  });
}

function formatFileSize(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

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

  // Client-Side Image Compression & Live Preview for all file inputs
  const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
  fileInputs.forEach(function (input) {
    // Container for compression feedback status
    let statusBadge = document.createElement('div');
    statusBadge.className = 'upload-compression-status';
    statusBadge.style.cssText = 'font-size: 0.78rem; margin-top: 5px; font-weight: 600; display: none;';
    input.parentNode.insertBefore(statusBadge, input.nextSibling);

    input.addEventListener('change', async function () {
      if (!this.files || this.files.length === 0) {
        statusBadge.style.display = 'none';
        return;
      }

      const originalFiles = Array.from(this.files);
      const originalTotalSize = originalFiles.reduce((acc, f) => acc + f.size, 0);

      // Check if any file is over 1MB
      const needsCompression = originalFiles.some(f => f.size > 800 * 1024);

      if (needsCompression && window.DataTransfer) {
        statusBadge.style.display = 'block';
        statusBadge.style.color = '#0284c7';
        statusBadge.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengoptimalkan & memperkecil ukuran foto secara otomatis...';

        try {
          const compressedFiles = await Promise.all(
            originalFiles.map(file => compressImage(file))
          );

          const dt = new DataTransfer();
          compressedFiles.forEach(f => dt.items.add(f));
          this.files = dt.files;

          const newTotalSize = compressedFiles.reduce((acc, f) => acc + f.size, 0);

          statusBadge.style.color = '#16a34a';
          statusBadge.innerHTML = `<i class="fas fa-check-circle"></i> Foto berhasil dioptimalkan: <strong>${formatFileSize(originalTotalSize)}</strong> ➔ <strong>${formatFileSize(newTotalSize)}</strong> (Upload siap & ringan)`;
        } catch (err) {
          console.warn('Auto compression warning:', err);
          statusBadge.style.display = 'none';
        }
      } else {
        statusBadge.style.display = 'none';
      }

      // Update Preview Box if defined
      const previewId = this.getAttribute('data-preview');
      if (previewId) {
        const previewBox = document.getElementById(previewId);
        if (previewBox && this.files[0]) {
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
          reader.readAsDataURL(this.files[0]);
        }
      }
    });
  });

  // Prevent multiple form submits and show spinner
  const adminForms = document.querySelectorAll('form');
  adminForms.forEach(function (form) {
    form.addEventListener('submit', function () {
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn && !submitBtn.disabled) {
        setTimeout(function () {
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        }, 50);
      }
    });
  });
});

// Confirmation helper
function confirmDelete(message) {
  return confirm(message || 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.');
}

/**
 * Main Frontend JavaScript - Mas Putra Portfolio
 */

document.addEventListener('DOMContentLoaded', function () {
  // Navbar scroll background effect
  const navbar = document.querySelector('.navbar-custom');
  window.addEventListener('scroll', function () {
    if (window.scrollY > 40) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  });

  // Mobile Drawer Toggle
  const mobileToggle = document.querySelector('.mobile-toggle');
  const mobileDrawer = document.querySelector('.mobile-drawer');
  const drawerClose = document.querySelector('.drawer-close');

  if (mobileToggle && mobileDrawer) {
    mobileToggle.addEventListener('click', function () {
      mobileDrawer.classList.toggle('open');
    });

    if (drawerClose) {
      drawerClose.addEventListener('click', function () {
        mobileDrawer.classList.remove('open');
      });
    }

    // Close drawer when link clicked
    const drawerLinks = document.querySelectorAll('.drawer-link');
    drawerLinks.forEach(function (link) {
      link.addEventListener('click', function () {
        mobileDrawer.classList.remove('open');
      });
    });
  }

  // Project Category Filter
  const filterBtns = document.querySelectorAll('.filter-btn');
  const projectCards = document.querySelectorAll('.project-card');

  filterBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      filterBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');

      const filter = this.getAttribute('data-filter');

      projectCards.forEach(function (card) {
        if (filter === 'all' || card.getAttribute('data-category') === filter) {
          card.style.display = 'flex';
          setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'scale(1)';
          }, 50);
        } else {
          card.style.opacity = '0';
          card.style.transform = 'scale(0.95)';
          setTimeout(() => {
            card.style.display = 'none';
          }, 200);
        }
      });
    });
  });

  // Modal Functionality for Certificate & Project Details
  const modalOverlay = document.getElementById('detailsModal');
  const modalBody = document.getElementById('modalContent');
  const modalClose = document.querySelector('.modal-close-btn');

  function openModal(htmlContent) {
    if (modalOverlay && modalBody) {
      modalBody.innerHTML = htmlContent;
      modalOverlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeModal() {
    if (modalOverlay) {
      modalOverlay.classList.remove('active');
      document.body.style.overflow = 'auto';
    }
  }

  if (modalClose) {
    modalClose.addEventListener('click', closeModal);
  }

  if (modalOverlay) {
    modalOverlay.addEventListener('click', function (e) {
      if (e.target === modalOverlay) {
        closeModal();
      }
    });
  }

  // Modal Gallery Helper: Generate HTML for Image Stage & Thumbnails
  function renderModalGalleryHtml(gallery, title, counterText, ofText, galleryHint, counterIcon) {
    const totalPhotos = gallery.length;
    if (totalPhotos === 0) return '';
    const first = gallery[0];
    const iconClass = counterIcon || 'fa-camera';

    return `
      <div class="modal-gallery-stage" id="modalGalleryStage">
        <img id="modalMainImg" class="modal-gallery-img" src="${first.url}" alt="${first.caption || title}">
        <div class="modal-gallery-counter" id="modalGalleryCounter">
          <i class="fas ${iconClass}"></i> <span>${counterText} 1 ${ofText} ${totalPhotos}</span>
        </div>
        <a id="modalGalleryFullBtn" href="${first.url}" target="_blank" rel="noopener" class="modal-gallery-full-btn" title="Buka Gambar Penuh">
          <i class="fas fa-expand"></i>
        </a>
        ${totalPhotos > 1 ? `
          <button type="button" class="gallery-nav-btn prev" id="btnGalleryPrev" aria-label="Previous Photo">
            <i class="fas fa-chevron-left"></i>
          </button>
          <button type="button" class="gallery-nav-btn next" id="btnGalleryNext" aria-label="Next Photo">
            <i class="fas fa-chevron-right"></i>
          </button>
        ` : ''}
        <div class="modal-gallery-caption" id="modalGalleryCaption">
          <i class="fas fa-info-circle" style="color: #38bdf8;"></i> <span>${first.caption || title}</span>
        </div>
      </div>
      ${totalPhotos > 1 ? `
        <div class="modal-thumbnails-strip" id="modalThumbsStrip">
          ${gallery.map((g, idx) => `
            <div class="modal-thumb-item ${idx === 0 ? 'active' : ''}" data-index="${idx}" title="${g.caption || (title + ' #' + (idx + 1))}">
              <img src="${g.url}" alt="Thumbnail ${idx + 1}">
            </div>
          `).join('')}
        </div>
        ${galleryHint ? `<div style="font-size: 0.78rem; color: #64748b; margin-top: 0.25rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.45rem;"><i class="fas fa-hand-pointer" style="color: #0284c7;"></i> ${galleryHint}</div>` : '<div style="margin-bottom: 1.25rem;"></div>'}
      ` : '<div style="margin-bottom: 1.25rem;"></div>'}
    `;
  }

  // Modal Gallery Helper: Attach Interactivity (Thumbnails, Arrows, Keyboard)
  function attachModalGalleryEvents(gallery, title, counterText, ofText) {
    const totalPhotos = gallery.length;
    if (totalPhotos <= 1) return;

    let currentIndex = 0;
    const mainImg = document.getElementById('modalMainImg');
    const counterSpan = document.querySelector('#modalGalleryCounter span');
    const fullBtn = document.getElementById('modalGalleryFullBtn');
    const captionSpan = document.querySelector('#modalGalleryCaption span');
    const thumbItems = document.querySelectorAll('.modal-thumb-item');
    const prevBtn = document.getElementById('btnGalleryPrev');
    const nextBtn = document.getElementById('btnGalleryNext');

    function updateGallery(newIndex) {
      if (newIndex < 0) newIndex = totalPhotos - 1;
      if (newIndex >= totalPhotos) newIndex = 0;
      currentIndex = newIndex;

      const item = gallery[currentIndex];
      if (mainImg) {
        mainImg.style.opacity = '0.25';
        setTimeout(() => {
          mainImg.src = item.url;
          mainImg.alt = item.caption || title;
          mainImg.style.opacity = '1';
        }, 120);
      }

      if (counterSpan) {
        counterSpan.textContent = `${counterText} ${currentIndex + 1} ${ofText} ${totalPhotos}`;
      }

      if (fullBtn) {
        fullBtn.href = item.url;
      }

      if (captionSpan) {
        captionSpan.textContent = item.caption || `${title} (${currentIndex + 1}/${totalPhotos})`;
      }

      thumbItems.forEach((t, idx) => {
        if (idx === currentIndex) {
          t.classList.add('active');
          t.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        } else {
          t.classList.remove('active');
        }
      });
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        updateGallery(currentIndex - 1);
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        updateGallery(currentIndex + 1);
      });
    }

    thumbItems.forEach(thumb => {
      thumb.addEventListener('click', function (e) {
        e.stopPropagation();
        const idx = parseInt(this.getAttribute('data-index'), 10);
        if (!isNaN(idx)) {
          updateGallery(idx);
        }
      });
    });

    const keyHandler = function (e) {
      if (!modalOverlay || !modalOverlay.classList.contains('active')) {
        document.removeEventListener('keydown', keyHandler);
        return;
      }
      if (e.key === 'ArrowLeft') {
        updateGallery(currentIndex - 1);
      } else if (e.key === 'ArrowRight') {
        updateGallery(currentIndex + 1);
      }
    };
    document.addEventListener('keydown', keyHandler);
  }

  // Certificate View Click (With Multi-Image / Timbal Balik Gallery)
  document.querySelectorAll('.btn-cert-view').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const title = this.getAttribute('data-title');
      const issuer = this.getAttribute('data-issuer');
      const date = this.getAttribute('data-date');
      const id = this.getAttribute('data-id');
      const desc = this.getAttribute('data-desc');
      const defaultImg = this.getAttribute('data-img');
      const url = this.getAttribute('data-url');
      const verifyLabel = this.getAttribute('data-verify-label') || 'Verifikasi / Buka Sertifikat';
      const yearNa = this.getAttribute('data-year-na') || 'Tahun N/A';
      const counterText = this.getAttribute('data-counter-text') || 'Halaman / Sisi';
      const ofText = this.getAttribute('data-of-text') || 'dari';
      const galleryHint = this.getAttribute('data-gallery-hint') || '';

      let gallery = [];
      try {
        const rawGallery = this.getAttribute('data-gallery');
        if (rawGallery) {
          gallery = JSON.parse(rawGallery);
        }
      } catch (e) {
        gallery = [];
      }

      if (!gallery || gallery.length === 0) {
        if (defaultImg) {
          gallery = [{ url: defaultImg, caption: title }];
        }
      }

      const mediaSection = renderModalGalleryHtml(gallery, title, counterText, ofText, galleryHint, 'fa-copy');

      let content = `
        <div style="padding: 2rem;">
          ${mediaSection}
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
            <span style="font-size: 0.8rem; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 0.25rem 0.75rem; border-radius: 999px;">${date || yearNa}</span>
            ${id ? `<span style="font-family: monospace; font-size: 0.8rem; color: #64748b; background: #f1f5f9; padding: 0.2rem 0.6rem; border-radius: 6px;">ID: ${id}</span>` : ''}
          </div>
          <h2 style="font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-bottom: 0.4rem; line-height: 1.3;">${title}</h2>
          <p style="font-size: 0.95rem; font-weight: 600; color: #0284c7; margin-bottom: 1.25rem;"><i class="fas fa-award"></i> ${issuer}</p>
          <div style="font-size: 0.92rem; color: #475569; line-height: 1.65; margin-bottom: 1.5rem; white-space: pre-line;">${desc}</div>
          ${url && url !== '#' ? `<a href="${url}" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.25rem; background: #0284c7; color: #fff; font-weight: 700; font-size: 0.9rem; border-radius: 8px; text-decoration: none;"><i class="fas fa-external-link-alt"></i> ${verifyLabel}</a>` : ''}
        </div>
      `;
      openModal(content);
      attachModalGalleryEvents(gallery, title, counterText, ofText);
    });
  });

  // Project Quick View Click (With Multi-Image Gallery)
  document.querySelectorAll('.btn-proj-view').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const title = this.getAttribute('data-title');
      const category = this.getAttribute('data-category');
      const tech = this.getAttribute('data-tech');
      const desc = this.getAttribute('data-desc');
      const defaultImg = this.getAttribute('data-img');
      const live = this.getAttribute('data-live');
      const github = this.getAttribute('data-github');
      const counterText = this.getAttribute('data-counter-text') || 'Photo';
      const ofText = this.getAttribute('data-of-text') || 'of';
      const galleryHint = this.getAttribute('data-gallery-hint') || '';

      let gallery = [];
      try {
        const rawGallery = this.getAttribute('data-gallery');
        if (rawGallery) {
          gallery = JSON.parse(rawGallery);
        }
      } catch (e) {
        gallery = [];
      }

      if (!gallery || gallery.length === 0) {
        if (defaultImg) {
          gallery = [{ url: defaultImg, caption: title }];
        }
      }

      let techBadges = '';
      if (tech) {
        tech.split(',').forEach(t => {
          techBadges += `<span style="display: inline-block; font-size: 0.78rem; font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 6px; background: #f1f5f9; color: #334155; margin-right: 0.4rem; margin-bottom: 0.4rem;">${t.trim()}</span>`;
        });
      }

      const mediaSection = renderModalGalleryHtml(gallery, title, counterText, ofText, galleryHint, 'fa-camera');

      let content = `
        <div style="padding: 2rem;">
          ${mediaSection}
          <div style="margin-bottom: 0.5rem;">
            <span style="font-size: 0.8rem; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 0.25rem 0.75rem; border-radius: 999px;">${category}</span>
          </div>
          <h2 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin-bottom: 0.85rem; line-height: 1.3;">${title}</h2>
          <div style="margin-bottom: 1.25rem;">${techBadges}</div>
          <div style="font-size: 0.95rem; color: #475569; line-height: 1.65; margin-bottom: 1.75rem; white-space: pre-line;">${desc}</div>
          <div style="display: flex; gap: 0.85rem; flex-wrap: wrap;">
            ${live && live !== '#' ? `<a href="${live}" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.25rem; background: #0284c7; color: #fff; font-weight: 700; font-size: 0.9rem; border-radius: 8px; text-decoration: none;"><i class="fas fa-play"></i> Live Demo</a>` : ''}
            ${github && github !== '#' ? `<a href="${github}" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.25rem; background: #1e293b; color: #fff; font-weight: 700; font-size: 0.9rem; border-radius: 8px; text-decoration: none;"><i class="fab fa-github"></i> Source Code</a>` : ''}
          </div>
        </div>
      `;
      openModal(content);
      attachModalGalleryEvents(gallery, title, counterText, ofText);
    });
  });

  // Contact to WhatsApp generator
  const sendWaBtn = document.getElementById('btnSendWhatsapp');
  if (sendWaBtn) {
    sendWaBtn.addEventListener('click', function (e) {
      e.preventDefault();
      const name = document.getElementById('contact_name').value.trim();
      const subject = document.getElementById('contact_subject').value.trim();
      const message = document.getElementById('contact_message').value.trim();
      const phone = '+6281277900210';

      if (!message) {
        alert('Silakan tulis pesan Anda terlebih dahulu.');
        return;
      }

      let text = `Halo Mas Saputra,\n\nNama: ${name || 'Klien'}\nTopik: ${subject || 'Kerjasama/Pekerjaan'}\nPesan:\n${message}\n\n(Dikirim dari website portofolio)`;
      let url = `https://wa.me/${phone.replace(/[^0-9]/g, '')}?text=${encodeURIComponent(text)}`;
      window.open(url, '_blank');
    });
  }
});

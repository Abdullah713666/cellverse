(function () {
  'use strict';

  var doc = document.documentElement;

  function initThemeToggle() {
    var btn = document.getElementById('themeToggle');
    if (!btn) return;

    btn.addEventListener('click', function () {
      var current = doc.getAttribute('data-theme');
      if (current === 'dark') {
        doc.removeAttribute('data-theme');
        localStorage.setItem('cellverse-theme', 'light');
      } else {
        doc.setAttribute('data-theme', 'dark');
        localStorage.setItem('cellverse-theme', 'dark');
      }
    });
  }

  function initHamburger() {
    var btn = document.getElementById('hamburgerBtn');
    if (!btn) return;
    var nav = document.getElementById('mobileNav');
    if (!nav) return;

    btn.addEventListener('click', function () {
      var open = btn.classList.toggle('is-active');
      btn.setAttribute('aria-expanded', open);
      nav.classList.toggle('is-open', open);
      nav.setAttribute('aria-hidden', !open);
      nav.querySelectorAll('a').forEach(function (a) {
        a.setAttribute('tabindex', open ? '0' : '-1');
      });
    });
  }

  function initScrollTop() {
    var btn = document.getElementById('scrollUp');
    if (!btn) return;

    window.addEventListener('scroll', function () {
      btn.classList.toggle('visible', window.scrollY > 600);
    });

    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  function initScrollDown() {
    var btn = document.getElementById('scrollDown');
    if (!btn) return;

    function updateVisibility() {
      var scrollBottom = window.scrollY + window.innerHeight;
      var docHeight = document.documentElement.scrollHeight;
      var atBottom = scrollBottom >= docHeight - 100;
      btn.classList.toggle('visible', !atBottom && window.scrollY > 300);
    }

    window.addEventListener('scroll', updateVisibility, { passive: true });
    updateVisibility();

    btn.addEventListener('click', function () {
      window.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'smooth' });
    });
  }

  function initScrollProgress() {
    var bar = document.getElementById('scrollProgress');
    if (!bar) return;

    window.addEventListener('scroll', function () {
      var h = document.documentElement.scrollHeight - window.innerHeight;
      if (h > 0) {
        bar.style.width = (window.scrollY / h * 100) + '%';
      }
    });
  }

  function initPageTransition() {
    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        document.body.classList.remove('is-loading');
      });
    });

    document.addEventListener('click', function (e) {
      var a = e.target.closest('a');
      if (!a) return;
      var href = a.getAttribute('href');
      if (!href || a.target === '_blank' || a.hasAttribute('download') ||
          href.startsWith('http') || href.startsWith('mailto:') ||
          href.startsWith('tel:') || href.startsWith('javascript:') ||
          href.startsWith('#') || href.startsWith('?') ||
          e.ctrlKey || e.metaKey || e.shiftKey) return;

      e.preventDefault();
      document.body.classList.add('is-loading');
      setTimeout(function () { window.location.href = href; }, 500);
    });

    window.addEventListener('pageshow', function (e) {
      if (e.persisted) document.body.classList.remove('is-loading');
    });
  }

  function initFAQ() {
    document.querySelectorAll('.faq-item').forEach(function (item) {
      var btn = item.querySelector('.faq-question');
      if (!btn) return;

      btn.addEventListener('click', function () {
        item.classList.toggle('open');
        var answer = item.querySelector('.faq-answer');
        if (item.classList.contains('open')) {
          answer.style.maxHeight = answer.scrollHeight + 'px';
        } else {
          answer.style.maxHeight = null;
        }
      });
    });

    var search = document.querySelector('[data-faq-search]');
    if (search) {
      search.addEventListener('input', function () {
        var q = this.value.toLowerCase();
        document.querySelectorAll('.faq-item').forEach(function (item) {
          var text = item.textContent.toLowerCase();
          item.style.display = text.indexOf(q) > -1 ? '' : 'none';
        });
      });
    }
  }

  function initLightbox() {
    var imgs = document.querySelectorAll('[data-lightbox]');
    if (!imgs.length) return;

    var overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.85);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity 0.3s;cursor:pointer;';
    overlay.innerHTML = '<img style="max-width:90vw;max-height:90vh;border-radius:8px;">';
    var img = overlay.querySelector('img');
    overlay.addEventListener('click', function () { closeLightbox(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeLightbox(); });

    function openLightbox(src) {
      img.src = src;
      document.body.appendChild(overlay);
      requestAnimationFrame(function () { overlay.style.opacity = '1'; });
      document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
      overlay.style.opacity = '0';
      setTimeout(function () {
        if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
        document.body.style.overflow = '';
      }, 300);
    }

    imgs.forEach(function (el) {
      el.addEventListener('click', function () {
        var src = el.getAttribute('data-lightbox') || el.querySelector('img')?.src || el.src;
        if (src) openLightbox(src);
      });
    });
  }

  function initFormPolish() {
    var groups = document.querySelectorAll('.form-group');
    groups.forEach(function (group) {
      var input = group.querySelector('input, textarea, select');
      if (!input) return;

      var sync = function () {
        group.classList.toggle('is-focused', input === document.activeElement);
        group.classList.toggle('has-value', !!input.value && input.value.toString().trim() !== '');
      };

      ['focus', 'blur', 'input', 'change'].forEach(function (evt) {
        input.addEventListener(evt, sync);
      });
      sync();
    });
  }

  function initFormSubmit() {
    var forms = document.querySelectorAll('form[data-ajax], form.contact-form, form.bulk-form, form#bulkOrderForm');
    forms.forEach(function (form) {
      var btn = form.querySelector('button[type="submit"], input[type="submit"]');
      if (!btn) return;
      var original = btn.innerHTML;

      form.addEventListener('submit', function () {
        var msg = form.querySelector('.form-success, .form-error, #formMessage');
        if (!msg) return;

        var obs = new MutationObserver(function () {
          if (msg.classList.contains('form-success') && msg.textContent.trim() !== '') {
            btn.innerHTML = '✓ Sent';
            btn.disabled = true;
            btn.style.opacity = '0.7';
            setTimeout(function () {
              btn.innerHTML = original;
              btn.disabled = false;
              btn.style.opacity = '';
            }, 3500);
            obs.disconnect();
          }
        });
        obs.observe(msg, { childList: true, subtree: true, characterData: true, attributes: true, attributeFilter: ['class'] });
        setTimeout(function () { obs.disconnect(); }, 8000);
      });
    });
  }

  function initProductFilters() {
    var pills = document.querySelectorAll('[data-filter]');
    pills.forEach(function (pill) {
      pill.addEventListener('click', function () {
        pills.forEach(function (p) { p.classList.remove('active'); });
        pill.classList.add('active');
        var filter = pill.getAttribute('data-filter');
        document.querySelectorAll('[data-category]').forEach(function (card) {
          if (filter === 'all' || card.getAttribute('data-category') === filter) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });
  }

  function initBulkOrderSummary() {
    var select = document.getElementById('productSelect');
    if (!select) return;
    var priceEl = document.getElementById('selectedPrice');
    var moqEl = document.getElementById('selectedMoq');

    select.addEventListener('change', function () {
      var opt = select.options[select.selectedIndex];
      if (!opt || !opt.value) {
        if (priceEl) priceEl.textContent = '-';
        if (moqEl) moqEl.textContent = '-';
        return;
      }
      var price = parseFloat(opt.getAttribute('data-price'));
      var moq = parseInt(opt.getAttribute('data-moq'), 10);
      if (priceEl) priceEl.textContent = isNaN(price) ? '-' : '$' + price.toFixed(2);
      if (moqEl) moqEl.textContent = isNaN(moq) ? '-' : moq.toLocaleString() + ' pcs';
    });
  }

  function initHeaderScroll() {
    var header = document.getElementById('siteHeader');
    if (!header) return;
    var last = 0;

    window.addEventListener('scroll', function () {
      var cur = window.scrollY;
      if (cur > 200 && cur > last) {
        header.classList.add('hidden');
      } else {
        header.classList.remove('hidden');
      }
      last = cur;
    }, { passive: true });
  }

  function initReveals() {
    var els = document.querySelectorAll('[data-reveal]');
    if (!els.length || !('IntersectionObserver' in window)) {
      els.forEach(function (el) { el.classList.add('revealed'); });
      return;
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        el.classList.add('revealed');

        var isStagger = el.classList.contains('stagger-fade');
        if (isStagger) {
          el.classList.add('revealed');
        }

        io.unobserve(el);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });

    els.forEach(function (el) { io.observe(el); });
  }

  function init() {
    initThemeToggle();
    initHamburger();
    initScrollTop();
    initScrollDown();
    initScrollProgress();
    initPageTransition();
    initFAQ();
    initLightbox();
    initFormPolish();
    initFormSubmit();
    initProductFilters();
    initBulkOrderSummary();
    initHeaderScroll();
    initReveals();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

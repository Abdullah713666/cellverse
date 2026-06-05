/* ============================================================
   Sprint 1 — Cellverse polish JS
   B: page transition curtain
   C: form polish (focus + has-value + submit success)
   A (products): live stock counter
   All gated by prefers-reduced-motion.
   ============================================================ */
(function () {
  'use strict';

  const prefersReduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const doc = document.documentElement;

  // -----------------------------------------------
  // B: Page transition curtain
  // -----------------------------------------------
  function initPageTransition() {
    const curtain = document.querySelector('.page-curtain');
    if (!curtain) return;

    if (prefersReduce) {
      // Skip animation entirely. Make sure curtain is hidden.
      curtain.style.display = 'none';
      return;
    }

    // On page load: body.is-loading is set in <head> inline (see header.php),
    // which makes the curtain cover the page. We then remove the class
    // on the next frame, which transitions the curtain open (right→left sweep).
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        document.body.classList.remove('is-loading');
      });
    });

    // On outbound click: re-add is-loading to close the curtain, then navigate
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a');
      if (!a) return;
      const href = a.getAttribute('href');
      if (!href) return;
      if (
        a.target === '_blank' ||
        a.hasAttribute('download') ||
        href.startsWith('http') ||
        href.startsWith('mailto:') ||
        href.startsWith('tel:') ||
        href.startsWith('javascript:') ||
        href.startsWith('#') ||
        href.startsWith('?') ||
        /^[a-z0-9.+_-]+@/i.test(href)
      ) return;

      if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;

      e.preventDefault();
      document.body.classList.add('is-loading');
      setTimeout(() => { window.location.href = href; }, 520);
    });

    // Safety: if page is bfcache-restored, ensure curtain is open
    window.addEventListener('pageshow', (e) => {
      if (e.persisted) document.body.classList.remove('is-loading');
    });
  }

  // -----------------------------------------------
  // C: Form polish — focus + has-value classes
  // -----------------------------------------------
  function initFormPolish() {
    const groups = document.querySelectorAll('.form-group');
    if (!groups.length) return;

    groups.forEach((group) => {
      const input = group.querySelector('input, textarea, select');
      if (!input) return;

      const sync = () => {
        if (input === document.activeElement) group.classList.add('is-focused');
        else group.classList.remove('is-focused');

        if (input.value && input.value.toString().trim() !== '') {
          group.classList.add('has-value');
        } else {
          group.classList.remove('has-value');
        }
      };

      ['focus', 'blur', 'input', 'change'].forEach((evt) =>
        input.addEventListener(evt, sync)
      );
      sync(); // initial state (autofill, etc.)
    });
  }

  // -----------------------------------------------
  // C: Form submit success — button morph
  // Assumes fetch/AJAX submit (the cellverse pattern).
  // Hooks the form's submit event; if a fetch happens we
  // animate the submit button on success.
  // -----------------------------------------------
  function initFormSubmitPolish() {
    const forms = document.querySelectorAll('form[data-ajax], form.contact-form, form.bulk-form, form#bulkOrderForm');
    forms.forEach((form) => {
      const btn = form.querySelector('button[type="submit"], input[type="submit"]');
      if (!btn) return;

      const original = btn.innerHTML;
      form.addEventListener('submit', () => {
        // optimistic: assume success will follow
        const checkLabel = (btn.dataset.checkLabel || 'Sent');
        if (prefersReduce) return;
        // Watch for response via observer on the form message div
        const msg = form.querySelector('.form-success, .form-error, #formMessage');
        if (!msg) return;
        const obs = new MutationObserver(() => {
          if (msg.classList.contains('form-success') && msg.textContent.trim() !== '') {
            btn.innerHTML = '✓ ' + checkLabel;
            btn.style.background = 'var(--color-primary)';
            btn.style.color = 'var(--bg-primary)';
            btn.disabled = true;
            setTimeout(() => {
              btn.innerHTML = original;
              btn.style.background = '';
              btn.style.color = '';
              btn.disabled = false;
            }, 3500);
            obs.disconnect();
          }
        });
        obs.observe(msg, { childList: true, subtree: true, characterData: true, attributes: true, attributeFilter: ['class'] });
        // safety: disconnect after 8s if no response
        setTimeout(() => obs.disconnect(), 8000);
      });
    });
  }

  // -----------------------------------------------
  // A (products): live stock counter
  // -----------------------------------------------
  function initLiveStock() {
    const el = document.querySelector('.live-stock-num');
    if (!el) return;
    const target = parseInt(el.dataset.count || '1247', 10);
    if (prefersReduce) { el.textContent = target.toLocaleString(); return; }

    const dur = 1800;
    const start = performance.now();
    const easeOut = (t) => 1 - Math.pow(1 - t, 3);

    const tick = (now) => {
      const t = Math.min(1, (now - start) / dur);
      const v = Math.floor(easeOut(t) * target);
      el.textContent = v.toLocaleString();
      if (t < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);

    // ambient drift: tiny up/down oscillation after the initial count
    setTimeout(() => {
      let v = target;
      setInterval(() => {
        v += Math.round((Math.random() - 0.5) * 6);
        if (Math.abs(v - target) > 30) v = target + (v > target ? 30 : -30);
        el.textContent = v.toLocaleString();
      }, 2200);
    }, dur + 400);
  }

  // -----------------------------------------------
  // D (enhancement): FAQ left border pulse on toggle
  // The existing GSAP handler in script.js already toggles
  // .open on .faq-item. We just observe and trigger a soft
  // shimmer on the left border when an item opens.
  // -----------------------------------------------
  function initFaqShimmer() {
    if (prefersReduce) return;
    document.querySelectorAll('.faq-item').forEach((item) => {
      const obs = new MutationObserver((muts) => {
        muts.forEach((m) => {
          if (m.attributeName === 'class' && item.classList.contains('open')) {
            item.style.boxShadow = 'inset 4px 0 0 0 var(--color-primary)';
            setTimeout(() => { item.style.boxShadow = ''; }, 600);
          }
        });
      });
      obs.observe(item, { attributes: true, attributeFilter: ['class'] });
    });
  }

  // -----------------------------------------------
  // E: Simple motion — IntersectionObserver reveals
  // (af-workshop style: tiny CSS-driven, no GSAP needed)
  // Coexists with the existing GSAP work because we only
  // target elements that DON'T already have GSAP-revealed
  // ancestors (heuristic: no .word-mask inside, no .hero-word
  // inside, not a .hero-* element).
  // -----------------------------------------------
  function initReveals() {
    if (prefersReduce) {
      // Show everything in final state
      document.querySelectorAll('.fade-up, .slide-left, .scale-reveal, .stagger-children, .section-divider')
        .forEach((el) => el.classList.add('in-view'));
      return;
    }

    // Skip elements that GSAP already drives. Heuristic: anything
    // inside a .hero-card / .hero-content, anything with a
    // .word-mask descendant, anything with .hero-word.
    const skip = (el) => {
      if (el.closest('.hero-card, .hero-content, .hero-bg, .hero-trust, .hero-stats, .hero-buttons')) return true;
      if (el.closest('.word-mask, .hero-word, .char')) return true;
      if (el.closest('[data-no-reveal]')) return true;
      return false;
    };

    const targets = document.querySelectorAll('.fade-up, .slide-left, .scale-reveal, .stagger-children, .section-divider');
    if (!targets.length) return;

    if (!('IntersectionObserver' in window)) {
      // Fallback: just reveal everything
      targets.forEach((el) => el.classList.add('in-view'));
      return;
    }

    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        if (skip(el)) { io.unobserve(el); return; }

        if (el.classList.contains('stagger-children')) {
          // Set --i on direct children for the CSS delay calc
          Array.from(el.children).forEach((c, i) => c.style.setProperty('--i', i));
        }
        el.classList.add('in-view');
        io.unobserve(el);
      });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.05 });

    targets.forEach((el) => {
      if (skip(el)) return;
      io.observe(el);
    });
  }

  // -----------------------------------------------
  // E: Tilt + shimmer hooks (af-workshop pattern).
  // These are CSS-driven but we add a tiny mouse-tilt
  // override for .tilt-card-mouse (optional, opt-in).
  // -----------------------------------------------
  function initTiltMouse() {
    if (prefersReduce) return;
    document.querySelectorAll('.tilt-card-mouse').forEach((el) => {
      el.addEventListener('mousemove', (e) => {
        const rect = el.getBoundingClientRect();
        const rx = ((e.clientY - rect.top) / rect.height - 0.5) * -6;
        const ry = ((e.clientX - rect.left) / rect.width - 0.5) * 6;
        el.style.transform = `perspective(800px) rotateX(${rx}deg) rotateY(${ry}deg) translateY(-4px)`;
      });
      el.addEventListener('mouseleave', () => {
        el.style.transform = '';
      });
    });
  }

  // -----------------------------------------------
  // Boot
  // -----------------------------------------------
  function init() {
    initPageTransition();
    initFormPolish();
    initFormSubmitPolish();
    initLiveStock();
    initFaqShimmer();
    initReveals();
    initTiltMouse();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

/* =====================================================================
   CellVerse — Hero & Stats interaction layer
   - Staggered text reveal (IntersectionObserver)
   - Count-up odometer (viewport-triggered, eased)
   - Magnetic lift on primary CTAs (mouse tracking)
   - Respects prefers-reduced-motion
   ===================================================================== */
(function () {
    'use strict';

    const prefersReduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- 1. Staggered Text Reveal ---------- */
    const revealEls = document.querySelectorAll('.reveal');
    if (revealEls.length) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                const delay = parseInt(el.dataset.revealDelay || '0', 10) * 80;
                window.setTimeout(() => el.classList.add('is-revealed'), delay);
                revealObserver.unobserve(el);
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        revealEls.forEach((el) => revealObserver.observe(el));
    }

    /* ---------- 2. Count-Up Stats ---------- */
    const statCards = document.querySelectorAll('.stat-card[data-count]');
    if (statCards.length) {
        const formatValue = (n) => n.toLocaleString('en-US');

        const animateCount = (card) => {
            const target = parseInt(card.dataset.count, 10);
            const suffix = card.dataset.suffix || '';
            const valueEl = card.querySelector('.count-value');
            if (!valueEl || isNaN(target)) return;

            if (prefersReduce) {
                valueEl.textContent = formatValue(target) + suffix;
                return;
            }

            const duration = 1600;
            const start = performance.now();
            const step = (now) => {
                const t = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - t, 3); // easeOutCubic
                valueEl.textContent = formatValue(Math.floor(target * eased));
                if (t < 1) requestAnimationFrame(step);
                else valueEl.textContent = formatValue(target) + suffix;
            };
            requestAnimationFrame(step);
        };

        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const card = entry.target;
                const idx = Array.from(statCards).indexOf(card);
                window.setTimeout(() => animateCount(card), idx * 120);
                statsObserver.unobserve(card);
            });
        }, { threshold: 0.35 });

        statCards.forEach((c) => statsObserver.observe(c));
    }

    /* ---------- 3. Magnetic Lift on CTAs ---------- */
    const magneticEls = document.querySelectorAll('.magnetic');
    if (magneticEls.length && !prefersReduce) {
        const STRENGTH = 0.25; // fraction of cursor offset applied

        magneticEls.forEach((el) => {
            el.addEventListener('mousemove', (e) => {
                const r = el.getBoundingClientRect();
                const x = (e.clientX - r.left - r.width / 2) * STRENGTH;
                const y = (e.clientY - r.top - r.height / 2) * STRENGTH;
                el.style.transform = `translate(${x.toFixed(1)}px, ${y.toFixed(1)}px)`;
            });
            el.addEventListener('mouseleave', () => {
                el.style.transform = '';
            });
        });
    }

    /* ---------- 4. Reduced-motion: force-reveal everything ---------- */
    if (prefersReduce) {
        revealEls.forEach((el) => el.classList.add('is-revealed'));
    }
})();

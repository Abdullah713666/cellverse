/* =====================================================================
   CellVerse — Hero Live Feed (GSAP)
   - Initial entrance stagger (rows slide up)
   - Simulated real-time value updates (3-5s cadence, random walk)
   - Value text flash cue (scale + color, leaves pill/bar untouched)
   - Live dot heartbeat
   - Header dots blink (asynchronous)
   - Pill and bar colors stay static — only text + bar WIDTH change
   - Respects prefers-reduced-motion
   Requires: GSAP 3.x globally available (loaded earlier in footer)
   ===================================================================== */
(function () {
    'use strict';

    if (typeof gsap === 'undefined') return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    /* ---------- Initial entrance ---------- */
    const tickerItems = document.querySelectorAll('.ticker-item');
    if (tickerItems.length) {
        gsap.from(tickerItems, {
            y: 16,
            opacity: 0,
            duration: 0.6,
            stagger: 0.08,
            ease: 'power2.out',
            delay: 0.4
        });
    }

    /* ---------- Live-indicator heartbeat ---------- */
    const liveDot = document.querySelector('.hero-card-dot.live');
    if (liveDot) {
        gsap.to(liveDot, {
            scale: 1.6,
            opacity: 0.35,
            duration: 1.0,
            repeat: -1,
            yoyo: true,
            ease: 'sine.inOut'
        });
    }

    /* ---------- Header terminal dots (asynchronous blink) ---------- */
    const headerDots = document.querySelectorAll('.hero-card-dots span');
    headerDots.forEach((dot, i) => {
        gsap.to(dot, {
            opacity: 0.35,
            duration: gsap.utils.random(0.5, 1.1),
            repeat: -1,
            yoyo: true,
            ease: 'sine.inOut',
            delay: i * 0.27
        });
    });

    /* ---------- Simulated real-time value updates ---------- */
    const FEED = [
        { id: 'row-cases',     value: 2400, min: 2280, max: 2520 },
        { id: 'row-chargers',  value: 1820, min: 1720, max: 1910 },
        { id: 'row-earphones', value: 1310, min: 1230, max: 1390 },
        { id: 'row-power',     value: 910,  min: 850,  max: 970  }
    ];

    // Map value (600..2600) to bar fill width (0..100%)
    const barPct = (n) => Math.round(((n - 600) / 2000) * 100);

    const flashValue = (valEl) => {
        gsap.fromTo(valEl,
            { scale: 1 },
            {
                scale: 1.18,
                color: '#00d4aa',
                textShadow: '0 0 8px rgba(0, 212, 170, 0.6)',
                duration: 0.22,
                yoyo: true,
                repeat: 1,
                ease: 'back.out(2)',
                onComplete: () => gsap.set(valEl, { color: '#ffffff', textShadow: 'none' })
            }
        );
    };

    FEED.forEach((row, i) => {
        const rowEl = document.getElementById(row.id);
        if (!rowEl) return;

        const valEl = rowEl.querySelector('.hero-card-val');
        const barEl = rowEl.querySelector('.hero-card-bar > span');

        // Seed initial bar width to match HTML value
        if (barEl) barEl.style.width = barPct(row.value) + '%';

        // Random-walk value tween (3-5s, clamped to per-row band)
        gsap.to(row, {
            value: `+=${gsap.utils.random(10, 30) - 20}`, // signed delta ±10
            duration: gsap.utils.random(3.0, 5.0),
            repeat: -1,
            repeatRefresh: true,
            yoyo: true,
            ease: 'sine.inOut',
            delay: 0.6 + i * 0.4,
            onUpdate: () => {
                const v = gsap.utils.clamp(row.min, row.max, Math.round(row.value));
                if (valEl) valEl.textContent = v.toLocaleString();
                if (barEl) barEl.style.width = barPct(v) + '%';
            }
        });

        // Periodic value flash (independent of tween direction)
        if (valEl) {
            const cycle = () => {
                flashValue(valEl);
                setTimeout(cycle, gsap.utils.random(3000, 5000));
            };
            setTimeout(cycle, gsap.utils.random(1200, 2400) + i * 300);
        }
    });
})();

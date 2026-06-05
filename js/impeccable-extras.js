/**
 * CellVerse - Impeccable UI/UX GSAP Layer
 * Premium motion: magnetic buttons, mouse-tracking parallax, scroll-driven progress,
 * word/line reveals, header shrink, animated sections, testimonial carousel.
 * Respects prefers-reduced-motion via gsap.matchMedia().
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', init);

    // ============================================================
    // 0. Cursor-following spotlight (vanilla, runs everywhere)
    // ============================================================
    (function setupSpotlight() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        if (window.matchMedia('(hover: none)').matches) return;

        const layer = document.createElement('div');
        layer.className = 'cursor-spotlight';
        document.body.appendChild(layer);
        document.body.classList.add('has-motion');

        let mx = window.innerWidth / 2;
        let my = window.innerHeight * 0.3;
        let tx = mx;
        let ty = my;
        let raf = null;

        function tick() {
            mx += (tx - mx) * 0.12;
            my += (ty - my) * 0.12;
            layer.style.setProperty('--mx', mx + 'px');
            layer.style.setProperty('--my', my + 'px');
            if (Math.abs(tx - mx) > 0.5 || Math.abs(ty - my) > 0.5) {
                raf = requestAnimationFrame(tick);
            } else {
                raf = null;
            }
        }

        document.addEventListener('mousemove', (e) => {
            tx = e.clientX;
            ty = e.clientY;
            if (!raf) raf = requestAnimationFrame(tick);
        }, { passive: true });

        document.addEventListener('mouseleave', () => {
            tx = window.innerWidth / 2;
            ty = window.innerHeight * 0.3;
        });
    })();

    // ============================================================
    // 0b. About page stat counter (when data-count present)
    // ============================================================
    (function setupAboutCounters() {
        const els = document.querySelectorAll('.about-hero-stat-num[data-count]');
        if (!els.length) return;
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const animate = (el) => {
            if (el.dataset.counted === '1') return;
            el.dataset.counted = '1';
            const target = parseInt(el.dataset.count, 10) || 0;
            if (reduce) { el.textContent = target.toLocaleString(); return; }
            const dur = 1400;
            const start = performance.now();
            function step(now) {
                const t = Math.min(1, (now - start) / dur);
                const eased = 1 - Math.pow(1 - t, 3);
                el.textContent = Math.round(target * eased).toLocaleString();
                if (t < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        };
        const io = new IntersectionObserver((entries) => {
            entries.forEach((e) => {
                if (e.isIntersecting) {
                    animate(e.target);
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.1 });
        els.forEach((el) => {
            // Set initial value to 0 if not already in viewport
            const rect = el.getBoundingClientRect();
            const inView = rect.top < window.innerHeight && rect.bottom > 0;
            if (!inView) el.textContent = '0';
            io.observe(el);
            // If already in view (above-the-fold), animate immediately on next frame
            if (inView) {
                requestAnimationFrame(() => animate(el));
            }
        });
    })();

    function init() {
        if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
        gsap.registerPlugin(ScrollTrigger);

        const mm = gsap.matchMedia();

        mm.add({
            isDesktop: '(min-width: 901px) and (hover: hover) and (pointer: fine)',
            isMobile: '(max-width: 900px), (hover: none)',
            reduceMotion: '(prefers-reduced-motion: reduce)'
        }, (ctx) => {
            const { isDesktop, isMobile, reduceMotion } = ctx.conditions;
            const dur = reduceMotion ? 0 : null;
            const ease = 'power3.out';
            const easeSnap = 'power4.out';

            // ============================================================
            // 1. Hero word-by-word + line reveal
            // ============================================================
            const heroWords = document.querySelectorAll('.hero-word');
            if (heroWords.length) {
                if (reduceMotion) {
                    gsap.set(heroWords, { opacity: 1, y: 0 });
                } else {
                    gsap.set(heroWords, { opacity: 0, yPercent: 110, rotateX: -40 });
                    gsap.to(heroWords, {
                        opacity: 1,
                        yPercent: 0,
                        rotateX: 0,
                        duration: 0.9,
                        stagger: { each: 0.06, from: 'start' },
                        ease: 'expo.out',
                        delay: 0.15
                    });
                }
            }

            // ============================================================
            // 2. Hero entrance for tag / paragraph / buttons / trust
            // ============================================================
            const heroRest = ['.hero-tag', '.hero p', '.hero-buttons', '.hero-trust'];
            heroRest.forEach((sel, i) => {
                const el = document.querySelector(sel);
                if (!el) return;
                if (reduceMotion) {
                    gsap.set(el, { opacity: 1, y: 0 });
                } else {
                    gsap.set(el, { opacity: 0, y: 20 });
                    gsap.to(el, {
                        opacity: 1,
                        y: 0,
                        duration: 0.6,
                        delay: 0.65 + i * 0.1,
                        ease
                    });
                }
            });

            // Hero visual cards: float-in
            const heroCards = document.querySelectorAll('.hero-card');
            if (heroCards.length) {
                if (reduceMotion) {
                    gsap.set(heroCards, { opacity: 1, scale: 1 });
                } else {
                    gsap.set(heroCards, { opacity: 0, y: 40, scale: 0.94 });
                    gsap.to(heroCards, {
                        opacity: 1,
                        y: 0,
                        scale: 1,
                        duration: 0.9,
                        delay: 0.55,
                        stagger: 0.12,
                        ease
                    });
                }
            }

            // ============================================================
            // 3. Mouse-tracking parallax for hero
            // ============================================================
            if (isDesktop) {
                const hero = document.querySelector('.hero');
                if (hero) {
                    const onMove = (e) => {
                        const rect = hero.getBoundingClientRect();
                        const x = (e.clientX - rect.left) / rect.width - 0.5;
                        const y = (e.clientY - rect.top) / rect.height - 0.5;
                        hero.querySelectorAll('[data-parallax]').forEach((el) => {
                            const strength = parseFloat(el.dataset.parallax) || 0.1;
                            gsap.to(el, {
                                x: x * 60 * strength * 3,
                                y: y * 60 * strength * 3,
                                duration: 1.2,
                                ease: 'power2.out',
                                overwrite: 'auto'
                            });
                        });
                    };
                    hero.addEventListener('mousemove', onMove);
                    ctx.add(() => hero.removeEventListener('mousemove', onMove));
                }
            }

            // ============================================================
            // 4. Header shrink + blur on scroll
            // ============================================================
            const header = document.getElementById('siteHeader');
            if (header) {
                ScrollTrigger.create({
                    start: 50,
                    end: 99999,
                    toggleClass: { className: 'is-scrolled', targets: header }
                });
            }

            // ============================================================
            // 5. Section header (h2) reveal with underline draw
            // ============================================================
            document.querySelectorAll('.section-header--lg h2, .stats-header h2').forEach((h2) => {
                if (reduceMotion) {
                    gsap.set(h2, { opacity: 1, y: 0 });
                } else {
                    gsap.set(h2, { opacity: 0, y: 28 });
                    ScrollTrigger.create({
                        trigger: h2,
                        start: 'top 88%',
                        once: true,
                        onEnter: () => gsap.to(h2, { opacity: 1, y: 0, duration: 0.7, ease })
                    });
                }
            });

            // Kickers
            gsap.utils.toArray('.kicker').forEach((k) => {
                if (reduceMotion) return;
                if (k.dataset.kickerDone === '1') return;
                k.dataset.kickerDone = '1';
                gsap.set(k, { opacity: 0, x: -12 });
                ScrollTrigger.create({
                    trigger: k,
                    start: 'top 90%',
                    once: true,
                    onEnter: () => gsap.to(k, { opacity: 1, x: 0, duration: 0.5, ease })
                });
            });

            // ============================================================
            // 6. Stat cards reveal (using to-tween with autoAlpha to avoid from-restart issues)
            // ============================================================
            const statCards = document.querySelectorAll('.stat-card');
            if (statCards.length) {
                if (reduceMotion) {
                    gsap.set(statCards, { opacity: 1, y: 0 });
                } else {
                    gsap.set(statCards, { opacity: 0, y: 24 });
                    ScrollTrigger.create({
                        trigger: '.stats-grid',
                        start: 'top 85%',
                        once: true,
                        onEnter: () => {
                            gsap.to(statCards, {
                                opacity: 1,
                                y: 0,
                                duration: 0.6,
                                stagger: 0.1,
                                ease
                            });
                        }
                    });
                }
            }

            // Stat count-up (re-used from animations-gsap but in case it's already handled, skip duplicates)
            document.querySelectorAll('.stat-number[data-count]').forEach((el) => {
                if (el.dataset.counted === '1') return;
                el.dataset.counted = '1';
                if (reduceMotion) {
                    el.textContent = parseInt(el.dataset.count, 10).toLocaleString() + '+';
                    return;
                }
                const target = parseInt(el.dataset.count, 10);
                const obj = { v: 0 };
                gsap.to(obj, {
                    v: target,
                    duration: 1.8,
                    ease: easeSnap,
                    scrollTrigger: { trigger: el, start: 'top 85%', once: true },
                    onUpdate() {
                        el.textContent = Math.floor(obj.v).toLocaleString() + '+';
                    }
                });
            });

            // ============================================================
            // 7. Section dividers — draw in
            // ============================================================
            gsap.utils.toArray('.section-divider').forEach((d) => {
                if (reduceMotion) return;
                if (d.dataset.dividerDone === '1') return;
                d.dataset.dividerDone = '1';
                gsap.set(d, { scaleX: 0 });
                ScrollTrigger.create({
                    trigger: d,
                    start: 'top 88%',
                    once: true,
                    onEnter: () => gsap.to(d, { scaleX: 1, duration: 1, ease: 'expo.out' })
                });
            });

            // ============================================================
            // 8. Product cards: scrub-by-row reveal + mouse parallax
            // ============================================================
            document.querySelectorAll('.products-grid').forEach((grid) => {
                const cards = grid.querySelectorAll('.product-card');
                if (!cards.length) return;

                if (reduceMotion) {
                    gsap.set(cards, { opacity: 1, y: 0, scale: 1 });
                } else {
                    gsap.set(cards, { opacity: 0, y: 32, scale: 0.96 });
                    ScrollTrigger.create({
                        trigger: grid,
                        start: 'top 85%',
                        once: true,
                        onEnter: () => {
                            gsap.to(cards, { opacity: 1, y: 0, scale: 1, duration: 0.6, stagger: 0.08, ease });
                        }
                    });
                }
            });

            // Mouse-tracking parallax + 3D tilt for product cards
            if (isDesktop) {
                document.querySelectorAll('.product-card[data-product]').forEach((card) => {
                    const img = card.querySelector('[data-product-img]');
                    const MAX_TILT = 6;
                    const MAX_SHIFT = 8;

                    const onEnter = () => {
                        gsap.to(card, { y: -6, duration: 0.4, ease, overwrite: 'auto' });
                    };
                    const onMove = (e) => {
                        const r = card.getBoundingClientRect();
                        const px = (e.clientX - r.left) / r.width - 0.5;
                        const py = (e.clientY - r.top) / r.height - 0.5;
                        gsap.to(card, {
                            rotateY: px * MAX_TILT,
                            rotateX: -py * MAX_TILT,
                            duration: 0.6,
                            ease: 'power2.out',
                            overwrite: 'auto'
                        });
                        if (img) {
                            gsap.to(img, {
                                x: px * -MAX_SHIFT,
                                y: py * -MAX_SHIFT,
                                scale: 1.06,
                                duration: 0.6,
                                ease: 'power2.out',
                                overwrite: 'auto'
                            });
                        }
                    };
                    const onLeave = () => {
                        gsap.to(card, {
                            y: 0, rotateX: 0, rotateY: 0, duration: 0.7, ease, overwrite: 'auto'
                        });
                        if (img) {
                            gsap.to(img, {
                                x: 0, y: 0, scale: 1, duration: 0.7, ease, overwrite: 'auto'
                            });
                        }
                    };
                    card.addEventListener('mouseenter', onEnter);
                    card.addEventListener('mousemove', onMove);
                    card.addEventListener('mouseleave', onLeave);
                    ctx.add(() => {
                        card.removeEventListener('mouseenter', onEnter);
                        card.removeEventListener('mousemove', onMove);
                        card.removeEventListener('mouseleave', onLeave);
                    });
                });
            }

            // ============================================================
            // 9. "How It Works" — scroll-driven progress line + step reveal
            // ============================================================
            const stepsWrap = document.querySelector('.steps-wrap');
            if (stepsWrap) {
                const fill = stepsWrap.querySelector('[data-steps-fill]');
                const stepCards = stepsWrap.querySelectorAll('[data-step]');

                if (reduceMotion) {
                    if (fill) fill.style.height = '100%';
                    gsap.set(stepCards, { opacity: 1, x: 0 });
                } else {
                    gsap.set(stepCards, { opacity: 0, x: 32 });
                    stepCards.forEach((step) => {
                        ScrollTrigger.create({
                            trigger: step,
                            start: 'top 85%',
                            once: true,
                            onEnter: () => gsap.to(step, { opacity: 1, x: 0, duration: 0.7, ease })
                        });
                    });

                    if (fill) {
                        ScrollTrigger.create({
                            trigger: stepsWrap,
                            start: 'top 70%',
                            end: 'bottom 70%',
                            scrub: 0.5,
                            onUpdate: (self) => {
                                gsap.to(fill, {
                                    height: (self.progress * 100) + '%',
                                    duration: 0.2,
                                    ease: 'power2.out',
                                    overwrite: 'auto'
                                });
                            }
                        });
                    }
                }
            }

            // ============================================================
            // 10. CTA banner reveal + orb float
            // ============================================================
            const cta = document.querySelector('.cta-banner');
            if (cta) {
                if (reduceMotion) {
                    gsap.set(cta, { opacity: 1, y: 0, scale: 1 });
                } else {
                    gsap.set(cta, { opacity: 0, y: 32, scale: 0.98 });
                    ScrollTrigger.create({
                        trigger: cta,
                        start: 'top 85%',
                        once: true,
                        onEnter: () => gsap.to(cta, { opacity: 1, y: 0, scale: 1, duration: 0.8, ease })
                    });
                }
            }

            // ============================================================
            // 11. Generic section reveal (non-overlapping)
            // ============================================================
            gsap.utils.toArray('.section').forEach((sec) => {
                if (sec.querySelector('.hero, .products-grid, .stat-card, .step-card, .cta-banner, .testimonial-carousel, .marquee, .contact-form, .contact-info-card, .about-hero, .faq-item, .value-card, .bulk-form, .page-header, .stats-grid')) return;
            });

            // Footer
            const footer = document.querySelector('.site-footer');
            if (footer && !reduceMotion) {
                gsap.set(footer, { opacity: 0, y: 24 });
                ScrollTrigger.create({
                    trigger: footer,
                    start: 'top 95%',
                    once: true,
                    onEnter: () => gsap.to(footer, { opacity: 1, y: 0, duration: 0.7, ease })
                });
            }
        });

        // ============================================================
        // 12. Magnetic buttons — desktop only, reduced-motion safe
        // ============================================================
        const magneticEls = document.querySelectorAll('.magnetic');
        if (magneticEls.length && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
            magneticEls.forEach((el) => {
                const STRENGTH = 0.25;
                let raf = null;
                let targetX = 0, targetY = 0;
                let currentX = 0, currentY = 0;

                const apply = () => {
                    currentX += (targetX - currentX) * 0.18;
                    currentY += (targetY - currentY) * 0.18;
                    el.style.transform = `translate(${currentX.toFixed(2)}px, ${currentY.toFixed(2)}px)`;
                    if (Math.abs(targetX - currentX) > 0.1 || Math.abs(targetY - currentY) > 0.1) {
                        raf = requestAnimationFrame(apply);
                    } else {
                        raf = null;
                    }
                };

                const onMove = (e) => {
                    const r = el.getBoundingClientRect();
                    const x = e.clientX - (r.left + r.width / 2);
                    const y = e.clientY - (r.top + r.height / 2);
                    targetX = x * STRENGTH;
                    targetY = y * STRENGTH;
                    if (!raf) raf = requestAnimationFrame(apply);
                };
                const onLeave = () => {
                    targetX = 0; targetY = 0;
                    if (!raf) raf = requestAnimationFrame(apply);
                };
                el.addEventListener('mousemove', onMove);
                el.addEventListener('mouseleave', onLeave);
            });
        }

        // ============================================================
        // 13. Testimonial carousel
        // ============================================================
        const carousels = document.querySelectorAll('[data-carousel]');
        carousels.forEach(initCarousel);

        function initCarousel(root) {
            const slides = root.querySelectorAll('[data-slide]');
            if (slides.length < 2) return;

            const prevBtn = root.querySelector('[data-carousel-prev]');
            const nextBtn = root.querySelector('[data-carousel-next]');
            const dotsContainer = root.querySelector('[data-carousel-dots]');
            const dots = dotsContainer ? dotsContainer.querySelectorAll('[data-carousel-dot]') : [];
            const INTERVAL = 5000;
            let index = 0;
            let timer = null;
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            function show(i) {
                index = (i + slides.length) % slides.length;
                slides.forEach((s, idx) => s.classList.toggle('is-active', idx === index));
                dots.forEach((d, idx) => {
                    d.classList.toggle('active', idx === index);
                    // Restart CSS animation
                    if (idx === index) {
                        d.style.animation = 'none';
                        // force reflow
                        void d.offsetWidth;
                        d.style.animation = '';
                    }
                });
            }

            function next() { show(index + 1); }
            function prev() { show(index - 1); }

            function startAuto() {
                if (reduceMotion) return;
                stopAuto();
                timer = setInterval(next, INTERVAL);
            }
            function stopAuto() {
                if (timer) { clearInterval(timer); timer = null; }
            }

            if (prevBtn) prevBtn.addEventListener('click', () => { prev(); startAuto(); });
            if (nextBtn) nextBtn.addEventListener('click', () => { next(); startAuto(); });
            dots.forEach((d) => {
                d.addEventListener('click', () => {
                    show(parseInt(d.dataset.carouselDot, 10));
                    startAuto();
                });
            });

            root.addEventListener('mouseenter', stopAuto);
            root.addEventListener('mouseleave', startAuto);

            // Pause when offscreen
            const io = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) startAuto();
                    else stopAuto();
                });
            }, { threshold: 0.25 });
            io.observe(root);

            show(0);
        }

        // ============================================================
        // 14. Refresh ScrollTrigger after fonts load
        // ============================================================
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(() => ScrollTrigger.refresh());
        }
        window.addEventListener('load', () => ScrollTrigger.refresh());
    }
})();

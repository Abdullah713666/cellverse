/**
 * CellVerse - Impeccable UI/UX Layer 2
 * Adds: split text reveal, scroll-stagger children, value-card rotate-in,
 *       SVG line-draw icons, hero-card row stagger, card shine sweep,
 *       continuous text shimmer, stat-card scale-pulse, value-card pulse rings.
 * Respects prefers-reduced-motion via gsap.matchMedia().
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', init);

    function init() {
        if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
        gsap.registerPlugin(ScrollTrigger);

        const mm = gsap.matchMedia();

        mm.add({
            isDesktop: '(min-width: 901px) and (hover: hover) and (pointer: fine)',
            isMobile: '(max-width: 900px), (hover: none)',
            reduceMotion: '(prefers-reduced-motion: reduce)'
        }, (ctx) => {
            const { isDesktop, reduceMotion } = ctx.conditions;
            const ease = 'power3.out';

            // ============================================================
            // 1. SPLIT TEXT REVEAL — word-by-word with mask for headings
            // ============================================================
            const splitTargets = document.querySelectorAll(
                '.section-header h2, .page-hero h1, .stats-header h2, .cta-banner h2, .value-card h3, .step-card h3, .testimonial-quote'
            );
            splitTargets.forEach((el) => {
                if (el.dataset.splitDone === '1') return;
                if (!el.textContent.trim()) return;
                // Skip if already has .hero-word spans (handled by impeccable-extras.js)
                if (el.querySelector('.hero-word')) {
                    el.dataset.splitDone = '1';
                    return;
                }
                // Avoid splitting if already wrapped
                if (el.querySelector('.word, .char')) {
                    el.dataset.splitDone = '1';
                    return;
                }
                // DOMParser-based split: preserves nested markup (e.g. <span class="accent">) without regex.
                // We tokenize the element's children by walking text nodes and splitting on whitespace,
                // wrapping each word in a <span class="word-mask"><span class="word">…</span></span>.
                const frag = document.createDocumentFragment();
                const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, null);
                const textNodes = [];
                let node;
                while ((node = walker.nextNode())) {
                    if (node.nodeValue && node.nodeValue.trim()) textNodes.push(node);
                }
                if (!textNodes.length) { el.dataset.splitDone = '1'; return; }

                const wrapWord = (text) => {
                    const mask = document.createElement('span');
                    mask.className = 'word-mask';
                    const word = document.createElement('span');
                    word.className = 'word';
                    word.textContent = text;
                    mask.appendChild(word);
                    return mask;
                };

                const segmentize = (text) => {
                    // Split on whitespace but keep the whitespace as separate tokens
                    return text.split(/(\s+)/).filter(Boolean);
                };

                const newTree = document.createElement('div');
                textNodes.forEach((tn) => {
                    const tokens = segmentize(tn.nodeValue);
                    tokens.forEach((tok) => {
                        if (/^\s+$/.test(tok)) {
                            newTree.appendChild(document.createTextNode(tok));
                        } else {
                            newTree.appendChild(wrapWord(tok));
                        }
                    });
                });

                // Preserve original non-text children (inline elements like <span class="accent">)
                // by appending them after the text segments, but only if they are direct children of `el`.
                // Walk the original children, replacing each text node with our wrapped version.
                const originalChildren = Array.from(el.childNodes);
                originalChildren.forEach((child) => {
                    if (child.nodeType === Node.TEXT_NODE) {
                        if (child.nodeValue && child.nodeValue.trim()) {
                            const tokens = segmentize(child.nodeValue);
                            tokens.forEach((tok) => {
                                if (/^\s+$/.test(tok)) {
                                    el.insertBefore(document.createTextNode(tok), child);
                                } else {
                                    el.insertBefore(wrapWord(tok), child);
                                }
                            });
                        }
                        el.removeChild(child);
                    }
                });

                el.dataset.splitDone = '1';

                const words = el.querySelectorAll('.word');
                if (reduceMotion) {
                    gsap.set(words, { y: 0, opacity: 1 });
                } else {
                    // Always animate on load — section headers are usually visible
                    gsap.set(words, { yPercent: 100, opacity: 0 });
                    const rect = el.getBoundingClientRect();
                    const inView = rect.top < window.innerHeight && rect.bottom > 0;
                    if (inView) {
                        gsap.to(words, {
                            yPercent: 0,
                            opacity: 1,
                            duration: 0.7,
                            stagger: 0.04,
                            ease,
                            delay: 0.25
                        });
                    } else {
                        gsap.to(words, {
                            yPercent: 0,
                            opacity: 1,
                            duration: 0.7,
                            stagger: 0.04,
                            ease,
                            scrollTrigger: {
                                trigger: el,
                                start: 'top 90%',
                                once: true
                            }
                        });
                    }
                }
            });

            // ============================================================
            // 2. SECTION HEADER — kicker line draw + paragraphs stagger
            // ============================================================
            document.querySelectorAll('.section-header, .stats-header, .page-hero, .cta-content').forEach((hdr) => {
                if (hdr.dataset.hdrDone === '1') return;
                hdr.dataset.hdrDone = '1';
                if (reduceMotion) return;

                const kicker = hdr.querySelector('.kicker');
                if (kicker) {
                    gsap.set(kicker, { opacity: 0, y: 12 });
                    ScrollTrigger.create({
                        trigger: hdr,
                        start: 'top 90%',
                        once: true,
                        onEnter: () => gsap.to(kicker, { opacity: 1, y: 0, duration: 0.5, ease })
                    });
                }
                const p = hdr.querySelector('p');
                if (p && !p.classList.contains('testimonial-quote')) {
                    gsap.set(p, { opacity: 0, y: 16 });
                    ScrollTrigger.create({
                        trigger: hdr,
                        start: 'top 90%',
                        once: true,
                        onEnter: () => gsap.to(p, { opacity: 1, y: 0, duration: 0.6, ease, delay: 0.15 })
                    });
                }
            });

            // ============================================================
            // 3. HERO CARD — Live Order Feed row stagger + bar grow
            // ============================================================
            const heroCard = document.querySelector('.hero-card--main');
            if (heroCard) {
                const rows = heroCard.querySelectorAll('.hero-card-row');
                const bars = heroCard.querySelectorAll('.hero-card-bar > span');
                if (reduceMotion) {
                    gsap.set(rows, { opacity: 1, x: 0 });
                    bars.forEach((b) => { b.style.transform = 'scaleX(1)'; });
                } else {
                    gsap.set(rows, { opacity: 0, x: 24 });
                    gsap.set(bars, { transformOrigin: 'left center', scaleX: 0 });
                    ScrollTrigger.create({
                        trigger: heroCard,
                        start: 'top 80%',
                        once: true,
                        onEnter: () => {
                            gsap.to(rows, { opacity: 1, x: 0, duration: 0.5, stagger: 0.08, ease });
                            gsap.to(bars, { scaleX: 1, duration: 1.2, stagger: 0.08, ease: 'power4.out', delay: 0.2 });
                        }
                    });
                }
            }

            // ============================================================
            // 4. STAT CARDS — shimmer scan line on scroll-in
            // ============================================================
            document.querySelectorAll('.stat-card, .about-hero-stat-card').forEach((card) => {
                if (card.dataset.shimmerDone === '1') return;
                card.dataset.shimmerDone = '1';
                if (reduceMotion) return;
                if (!isDesktop) return;
                // Inject scan-line pseudo via CSS class toggle
                ScrollTrigger.create({
                    trigger: card,
                    start: 'top 88%',
                    once: true,
                    onEnter: () => {
                        card.classList.add('is-shimmered');
                    }
                });
            });

            // ============================================================
            // 5. STAT ICONS — SVG line-draw on scroll-in
            // ============================================================
            document.querySelectorAll('.stat-icon svg, .step-number svg, .contact-info-icon svg, .about-hero-stat-icon svg').forEach((svg) => {
                if (svg.dataset.drawDone === '1') return;
                if (!svg.querySelector('path, circle, rect, line, polyline, polygon')) return;
                svg.dataset.drawDone = '1';
                const drawables = svg.querySelectorAll('path, circle, rect, line, polyline, polygon');
                drawables.forEach((p) => {
                    try {
                        const len = p.getTotalLength ? p.getTotalLength() : 0;
                        if (len > 0) {
                            p.style.strokeDasharray = len + ' ' + len;
                            p.style.strokeDashoffset = len;
                        }
                    } catch (e) { /* ignore */ }
                });
                if (reduceMotion) {
                    drawables.forEach((p) => { p.style.strokeDashoffset = '0'; });
                } else {
                    ScrollTrigger.create({
                        trigger: svg.closest('.stat-card, .step-number, .contact-info-card, .about-hero-stat-card') || svg,
                        start: 'top 88%',
                        once: true,
                        onEnter: () => {
                            gsap.set(svg, { scale: 0.6, opacity: 0, transformOrigin: 'center center' });
                            drawables.forEach((p) => {
                                gsap.to(p, { strokeDashoffset: 0, duration: 1.2, ease });
                            });
                            gsap.to(svg, { scale: 1, opacity: 1, duration: 0.5, ease, transformOrigin: 'center center' });
                        }
                    });
                }
            });

            // ============================================================
            // 6. VALUE CARDS — rotate-in on scroll + corner accent pulse
            // ============================================================
            document.querySelectorAll('.value-card, .bulk-process-step').forEach((card, i) => {
                if (card.dataset.valueReveal === '1') return;
                card.dataset.valueReveal = '1';
                if (reduceMotion) {
                    gsap.set(card, { opacity: 1, y: 0, rotateX: 0 });
                } else {
                    gsap.set(card, { opacity: 0, y: 32, rotateX: -8 });
                    ScrollTrigger.create({
                        trigger: card,
                        start: 'top 88%',
                        once: true,
                        onEnter: () => gsap.to(card, { opacity: 1, y: 0, rotateX: 0, duration: 0.7, delay: (i % 4) * 0.08, ease })
                    });
                }
            });

            // ============================================================
            // 7. BULK SUMMARY CARDS — stagger reveal
            // ============================================================
            document.querySelectorAll('.bulk-summary, .about-hero-stats').forEach((grp) => {
                if (grp.dataset.grpReveal === '1') return;
                grp.dataset.grpReveal = '1';
                const kids = grp.children;
                if (!kids.length) return;
                if (reduceMotion) {
                    gsap.set(kids, { opacity: 1, y: 0 });
                } else {
                    gsap.set(kids, { opacity: 0, y: 20 });
                    ScrollTrigger.create({
                        trigger: grp,
                        start: 'top 88%',
                        once: true,
                        onEnter: () => gsap.to(kids, { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, ease })
                    });
                }
            });

            // ============================================================
            // 8. CONTACT INFO CARDS — stagger reveal
            // ============================================================
            document.querySelectorAll('.contact-info-grid').forEach((grid) => {
                if (grid.dataset.contactReveal === '1') return;
                grid.dataset.contactReveal = '1';
                const cards = grid.children;
                if (!cards.length) return;
                if (reduceMotion) {
                    gsap.set(cards, { opacity: 1, y: 0 });
                } else {
                    gsap.set(cards, { opacity: 0, y: 28, scale: 0.96 });
                    ScrollTrigger.create({
                        trigger: grid,
                        start: 'top 85%',
                        once: true,
                        onEnter: () => gsap.to(cards, { opacity: 1, y: 0, scale: 1, duration: 0.6, stagger: 0.1, ease })
                    });
                }
            });

            // ============================================================
            // 9. FAQ ITEMS — fade-in with X shift on scroll
            // ============================================================
            document.querySelectorAll('.faq-list').forEach((list) => {
                const items = list.querySelectorAll('.faq-item');
                if (!items.length) return;
                if (reduceMotion) {
                    gsap.set(items, { opacity: 1, x: 0 });
                } else {
                    gsap.set(items, { opacity: 0, x: -16 });
                    ScrollTrigger.create({
                        trigger: list,
                        start: 'top 88%',
                        once: true,
                        onEnter: () => gsap.to(items, { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, ease })
                    });
                }
            });

            // ============================================================
            // 10. P tags in cards & sections — fade-in with subtle blur
            // ============================================================
            document.querySelectorAll('.section > .container > p:not(.kicker)').forEach((p) => {
                if (p.dataset.pReveal === '1') return;
                p.dataset.pReveal = '1';
                if (reduceMotion) return;
                gsap.set(p, { opacity: 0, y: 12, filter: 'blur(6px)' });
                ScrollTrigger.create({
                    trigger: p,
                    start: 'top 92%',
                    once: true,
                    onEnter: () => gsap.to(p, { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.6, ease })
                });
            });

            // ============================================================
            // 11. STAGGER CHILDREN ATTR — auto-stagger direct children
            // ============================================================
            document.querySelectorAll('[data-stagger-children]').forEach((parent) => {
                if (parent.dataset.staggerDone === '1') return;
                parent.dataset.staggerDone = '1';
                const kids = Array.from(parent.children);
                if (!kids.length) return;
                if (reduceMotion) {
                    gsap.set(kids, { opacity: 1, y: 0 });
                } else {
                    gsap.set(kids, { opacity: 0, y: 24 });
                    ScrollTrigger.create({
                        trigger: parent,
                        start: 'top 88%',
                        once: true,
                        onEnter: () => gsap.to(kids, { opacity: 1, y: 0, duration: 0.5, stagger: parseFloat(parent.dataset.staggerChildren) || 0.08, ease })
                    });
                }
            });

            // ============================================================
            // 12. CARD SHINE SWEEP — diagonal shine on hover (desktop)
            // ============================================================
            if (isDesktop) {
                document.querySelectorAll('.product-card, .stat-card, .value-card, .bulk-process-step, .contact-info-card, .about-hero-stat-card, .bulk-summary-card').forEach((card) => {
                    if (card.dataset.shineDone === '1') return;
                    card.dataset.shineDone = '1';
                    card.addEventListener('mouseenter', () => {
                        card.classList.remove('shine-active');
                        // force reflow
                        void card.offsetWidth;
                        card.classList.add('shine-active');
                    });
                });
            }
        });

        // ============================================================
        // 13. Counter complete callback — when number lands, scale-pulse
        // ============================================================
        if (typeof ScrollTrigger !== 'undefined') {
            ScrollTrigger.create({
                trigger: '.stats-grid',
                start: 'top 85%',
                once: true,
                onEnter: () => {
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                    document.querySelectorAll('.stat-number[data-count], .about-hero-stat-num[data-count]').forEach((el) => {
                        el.classList.add('counter-landed');
                    });
                }
            });
        }

        // Refresh (delayed to avoid restarting original gsap.from tweens)
        setTimeout(() => {
            if (typeof ScrollTrigger !== 'undefined') ScrollTrigger.refresh();
        }, 1500);

        // Safety net: after 3s, force all from-animated elements to final visible state
        // (handles edge cases where ScrollTrigger.refresh() or DOM changes restart from-tweens)
        setTimeout(() => {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            const animatedSelectors = [
                { sel: '.product-card', final: { opacity: 1, y: 0, scale: 1 } },
                { sel: '.step-card', final: { opacity: 1, x: 0, y: 0 } },
                { sel: '.stat-card', final: { opacity: 1, y: 0 } },
                { sel: '.value-card', final: { opacity: 1, y: 0, rotate: 0 } },
                { sel: '.section-header h2, .stats-header h2, .cta-banner h2, .page-hero h1', final: { opacity: 1, y: 0, yPercent: 0 } },
                { sel: '.section-header .kicker, .page-hero .kicker, .stats-header .kicker', final: { opacity: 1, y: 0, x: 0 } },
                { sel: '.section-header p, .page-hero p, .stats-header p', final: { opacity: 1, y: 0 } },
                { sel: '.cta-content', final: { opacity: 1, y: 0 } },
                { sel: '.site-footer', final: { opacity: 1, y: 0 } }
            ];
            // Force all to final state (no viewport check — applies to all on page)
            animatedSelectors.forEach(({ sel, final }) => {
                document.querySelectorAll(sel).forEach((el) => {
                    gsap.set(el, final);
                });
            });
        }, 3000);
    }
})();

/**
 * CellVerse - GSAP ScrollTrigger Animations
 * Smooth premium feel: power3.out / power4.out
 * Respects prefers-reduced-motion
 */
document.addEventListener('DOMContentLoaded', () => {
    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

    gsap.registerPlugin(ScrollTrigger);

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reducedMotion) return;

    // === Legacy guard: skip all from-tweens (handled by impeccable-extras.js / impeccable-extras2.js) ===
    if (window.__cellverseLegacyAnimationsDisabled) return;
    window.__cellverseLegacyAnimationsDisabled = true;

    // === Scroll Progress Bar ===
    const progressBar = document.getElementById('scrollProgress');
    if (progressBar) {
        gsap.to(progressBar, {
            width: '100%',
            ease: 'none',
            scrollTrigger: {
                trigger: document.body,
                start: 'top top',
                end: 'bottom bottom',
                scrub: 0.3
            }
        });
    }

    // === Floating shapes entrance ===
    gsap.utils.toArray('.floating-shape').forEach((shape, i) => {
        gsap.from(shape, {
            opacity: 0,
            scale: 0.5,
            rotation: 45,
            duration: 1.2,
            delay: 0.3 + i * 0.15,
            ease: 'power3.out'
        });
    });

    // Hero parallax (subtle background)
    const heroBg = document.querySelector('.hero-bg');
    if (heroBg) {
        gsap.to(heroBg, {
            y: 80,
            ease: 'none',
            scrollTrigger: {
                trigger: '.hero',
                start: 'top top',
                end: 'bottom top',
                scrub: true
            }
        });
    }

    // === Page Headers ===
    gsap.utils.toArray('.page-header').forEach(header => {
        const tl = gsap.timeline({
            scrollTrigger: {
                trigger: header,
                start: 'top 80%',
                once: true
            }
        });
        tl.from(header.querySelector('h1'), { opacity: 0, y: 28, duration: 0.6, ease: 'power3.out' })
          .from(header.querySelector('p'), { opacity: 0, y: 20, duration: 0.5, ease: 'power3.out' }, '-=0.3');
    });

    // === Stats Counter (deferred to impeccable-extras.js when present) ===
    // The new layer handles this with proper matchMedia + reduced-motion support.
    if (typeof window.__cellverseStatsHandled === 'undefined') {
        document.querySelectorAll('.stat-number').forEach(el => {
            if (el.dataset.counted === '1') return;
            const target = parseInt(el.getAttribute('data-count'));
            gsap.to(el, {
                innerHTML: target,
                duration: 1.8,
                ease: 'power4.out',
                snap: { innerHTML: 1 },
                scrollTrigger: {
                    trigger: el,
                    start: 'top 85%',
                    once: true
                },
                onUpdate: function() {
                    el.textContent = Math.floor(parseFloat(el.textContent)).toLocaleString() + '+';
                }
            });
        });
    }

    // === Section Headers ===
    gsap.utils.toArray('.section-header').forEach(el => {
        gsap.from(el, {
            opacity: 0,
            y: 28,
            duration: 0.6,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: el,
                start: 'top 85%',
                once: true
            }
        });
    });

    // === Product Cards ===
    gsap.utils.toArray('.products-grid').forEach(grid => {
        const cards = grid.querySelectorAll('.product-card');
        if (cards.length === 0) return;

        gsap.from(cards, {
            opacity: 0,
            y: 32,
            duration: 0.5,
            stagger: 0.08,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: grid,
                start: 'top 85%',
                once: true
            }
        });
    });

    // === Step Cards ===
    gsap.utils.toArray('.steps-grid').forEach(grid => {
        const cards = grid.querySelectorAll('.step-card');
        if (cards.length === 0) return;

        gsap.from(cards, {
            opacity: 0,
            y: 32,
            duration: 0.6,
            stagger: 0.12,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: grid,
                start: 'top 85%',
                once: true
            }
        });
    });

    // === Testimonial Cards ===
    gsap.utils.toArray('.testimonials-grid').forEach(grid => {
        const cards = grid.querySelectorAll('.testimonial-card');
        if (cards.length === 0) return;

        gsap.from(cards, {
            opacity: 0,
            y: 28,
            duration: 0.6,
            stagger: 0.1,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: grid,
                start: 'top 85%',
                once: true
            }
        });
    });

    // === Value Cards ===
    gsap.utils.toArray('.values-grid').forEach(grid => {
        const cards = grid.querySelectorAll('.value-card');
        if (cards.length === 0) return;

        gsap.from(cards, {
            opacity: 0,
            y: 28,
            scale: 0.96,
            duration: 0.5,
            stagger: 0.1,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: grid,
                start: 'top 85%',
                once: true
            }
        });
    });

    // === Stat Cards ===
    gsap.utils.toArray('.stats-grid').forEach(grid => {
        const cards = grid.querySelectorAll('.stat-card');
        if (cards.length === 0) return;

        gsap.from(cards, {
            opacity: 0,
            y: 24,
            duration: 0.5,
            stagger: 0.08,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: grid,
                start: 'top 85%',
                once: true
            }
        });
    });

    // === Contact Info Cards ===
    gsap.utils.toArray('.contact-info-card').forEach((card, i) => {
        gsap.from(card, {
            opacity: 0,
            x: 32,
            duration: 0.5,
            delay: i * 0.1,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: card,
                start: 'top 85%',
                once: true
            }
        });
    });

    // === Contact Form ===
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        gsap.from(contactForm, {
            opacity: 0,
            x: -32,
            duration: 0.6,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: contactForm,
                start: 'top 85%',
                once: true
            }
        });
    }

    // === Bulk Order Form ===
    const bulkForm = document.querySelector('.bulk-form');
    if (bulkForm) {
        gsap.from(bulkForm, {
            opacity: 0,
            y: 32,
            duration: 0.6,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: bulkForm,
                start: 'top 85%',
                once: true
            }
        });
    }

    // === About Hero ===
    const aboutHero = document.querySelector('.about-hero');
    if (aboutHero) {
        const aboutTl = gsap.timeline({
            scrollTrigger: {
                trigger: aboutHero,
                start: 'top 80%',
                once: true
            }
        });
        aboutTl
            .from(aboutHero.querySelector('.about-hero-text, .about-hero > div:first-child'), {
                opacity: 0, x: -40, duration: 0.7, ease: 'power3.out'
            })
            .from(aboutHero.querySelector('.about-hero-image, .about-hero > div:last-child'), {
                opacity: 0, x: 40, duration: 0.7, ease: 'power3.out'
            }, '-=0.5');
    }

    // === FAQ Items Stagger ===
    const faqList = document.querySelector('.faq-list');
    if (faqList) {
        const faqItems = faqList.querySelectorAll('.faq-item');
        if (faqItems.length > 0) {
            gsap.from(faqItems, {
                opacity: 0,
                y: 20,
                duration: 0.4,
                stagger: 0.06,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: faqList,
                    start: 'top 85%',
                    once: true
                }
            });
        }
    }

    // === CTA Banner ===
    const cta = document.querySelector('.cta-banner');
    if (cta) {
        gsap.from(cta, {
            opacity: 0,
            y: 32,
            scale: 0.98,
            duration: 0.7,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: cta,
                start: 'top 85%',
                once: true
            }
        });
    }

    // === Footer ===
    const footer = document.querySelector('.site-footer');
    if (footer) {
        gsap.from(footer, {
            opacity: 0,
            y: 24,
            duration: 0.6,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: footer,
                start: 'top 95%',
                once: true
            }
        });
    }

    // === Section Dividers ===
    gsap.utils.toArray('.section-divider').forEach(divider => {
        gsap.from(divider, {
            scaleX: 0,
            duration: 0.8,
            ease: 'power3.inOut',
            scrollTrigger: {
                trigger: divider,
                start: 'top 90%',
                once: true
            }
        });
    });
});

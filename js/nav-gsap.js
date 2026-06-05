/**
 * CellVerse - Nav Hover Micro-interactions (GSAP)
 * Smooth premium feel: power3.out
 * Respects prefers-reduced-motion
 */
document.addEventListener('DOMContentLoaded', () => {
    if (typeof gsap === 'undefined') return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    // === Desktop Nav Link Hover ===
    document.querySelectorAll('.desktop-nav .nav-link').forEach(link => {
        link.addEventListener('mouseenter', () => {
            gsap.to(link, { scale: 1.04, duration: 0.2, ease: 'power3.out' });
        });
        link.addEventListener('mouseleave', () => {
            gsap.to(link, { scale: 1, duration: 0.2, ease: 'power3.out' });
        });
    });

    // === Logo Hover ===
    const logo = document.querySelector('.logo');
    if (logo) {
        logo.addEventListener('mouseenter', () => {
            gsap.to(logo.querySelector('.logo-icon'), {
                rotation: 8,
                scale: 1.08,
                duration: 0.3,
                ease: 'power3.out'
            });
        });
        logo.addEventListener('mouseleave', () => {
            gsap.to(logo.querySelector('.logo-icon'), {
                rotation: 0,
                scale: 1,
                duration: 0.3,
                ease: 'power3.out'
            });
        });
    }

    // === Button Click Feedback ===
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mousedown', () => {
            if (btn.classList.contains('magnetic')) return;
            gsap.to(btn, { scale: 0.96, duration: 0.08, ease: 'power2.in' });
        });
        btn.addEventListener('mouseup', () => {
            if (btn.classList.contains('magnetic')) return;
            gsap.to(btn, { scale: 1, duration: 0.2, ease: 'power3.out' });
        });
        btn.addEventListener('mouseleave', () => {
            if (btn.classList.contains('magnetic')) return;
            gsap.to(btn, { scale: 1, duration: 0.15, ease: 'power3.out' });
        });
    });

    // === Filter Button Transitions ===
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            gsap.fromTo(btn, { scale: 0.92 }, { scale: 1, duration: 0.3, ease: 'power3.out' });
        });
    });

    // === Social Link Hover ===
    document.querySelectorAll('.social-link').forEach(link => {
        link.addEventListener('mouseenter', () => {
            gsap.to(link, { y: -3, scale: 1.1, duration: 0.2, ease: 'power3.out' });
        });
        link.addEventListener('mouseleave', () => {
            gsap.to(link, { y: 0, scale: 1, duration: 0.2, ease: 'power3.out' });
        });
    });

    // === Product Card Glow (only on cards without data-product, since those use the parallax handler) ===
    document.querySelectorAll('.product-card:not([data-product])').forEach(card => {
        card.addEventListener('mouseenter', () => {
            gsap.to(card, {
                boxShadow: '0 0 24px rgba(0, 212, 170, 0.15)',
                duration: 0.3,
                ease: 'power3.out'
            });
        });
        card.addEventListener('mouseleave', () => {
            gsap.to(card, {
                boxShadow: '0 0 0 rgba(0, 212, 170, 0)',
                duration: 0.4,
                ease: 'power3.out'
            });
        });
    });

    // === Scroll Buttons Hover ===
    document.querySelectorAll('.scroll-btn').forEach(btn => {
        btn.addEventListener('mouseenter', () => {
            gsap.to(btn, { scale: 1.08, duration: 0.2, ease: 'power3.out' });
        });
        btn.addEventListener('mouseleave', () => {
            gsap.to(btn, { scale: 1, duration: 0.2, ease: 'power3.out' });
        });
    });
});

/**
 * CellVerse - Main JavaScript
 * Navigation, filters, forms, lightbox, FAQ accordion
 */
document.addEventListener('DOMContentLoaded', () => {

    // === Header auto-hide on scroll ===
    const header = document.getElementById('siteHeader');
    let lastScroll = 0;

    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        if (currentScroll > 100) {
            if (currentScroll > lastScroll) {
                header.classList.add('hidden');
            } else {
                header.classList.remove('hidden');
            }
        } else {
            header.classList.remove('hidden');
        }
        lastScroll = currentScroll;
    });

    // === Hamburger menu ===
    const hamburger = document.getElementById('hamburgerBtn');
    const mobileNav = document.getElementById('mobileNav');

    if (hamburger && mobileNav) {
        const setMobileNavState = (isOpen) => {
            mobileNav.classList.toggle('open', isOpen);
            hamburger.classList.toggle('active', isOpen);
            hamburger.setAttribute('aria-expanded', String(isOpen));
            mobileNav.setAttribute('aria-hidden', String(!isOpen));
            // When hidden, prevent focus on inner links
            mobileNav.querySelectorAll('a').forEach((link) => {
                if (isOpen) {
                    link.removeAttribute('tabindex');
                } else {
                    link.setAttribute('tabindex', '-1');
                }
            });
        };
        setMobileNavState(false);
        hamburger.addEventListener('click', () => {
            setMobileNavState(!mobileNav.classList.contains('open'));
        });

        // Close on link click
        mobileNav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                setMobileNavState(false);
            });
        });
    }

    // === Scroll to top/bottom ===
    const scrollControls = document.getElementById('scrollControls');
    const scrollUp = document.getElementById('scrollUp');
    const scrollDown = document.getElementById('scrollDown');

    if (scrollControls) {
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 400) {
                scrollControls.classList.add('visible');
            } else {
                scrollControls.classList.remove('visible');
            }
        });

        if (scrollUp) {
            scrollUp.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        if (scrollDown) {
            scrollDown.addEventListener('click', () => {
                window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
            });
        }
    }

    // === Gallery / Products Filters ===
    const filterBtns = document.querySelectorAll('.filter-btn');
    const searchInput = document.getElementById('productSearch');
    const productCards = document.querySelectorAll('.product-card[data-category]');

    function applyFilters() {
        const activeFilter = document.querySelector('.filter-btn.active')?.getAttribute('data-filter') || 'all';
        const searchTerm = (searchInput?.value || '').toLowerCase();

        productCards.forEach(card => {
            const category = card.getAttribute('data-category');
            const name = (card.querySelector('.product-card-body h3')?.textContent || '').toLowerCase();
            const desc = (card.querySelector('.product-card-body p')?.textContent || '').toLowerCase();

            const matchesFilter = activeFilter === 'all' || category === activeFilter;
            const matchesSearch = !searchTerm || name.includes(searchTerm) || desc.includes(searchTerm);

            if (matchesFilter && matchesSearch) {
                if (typeof gsap !== 'undefined' && !card.classList.contains('is-visible')) {
                    card.classList.add('is-visible');
                    card.style.display = '';
                    gsap.fromTo(card, { opacity: 0, y: 16, scale: 0.97 }, {
                        opacity: 1, y: 0, scale: 1, duration: 0.3, ease: 'power3.out'
                    });
                } else {
                    card.style.display = '';
                    card.style.opacity = '1';
                    card.style.transform = '';
                }
            } else {
                card.style.display = 'none';
                card.classList.remove('is-visible');
            }
        });
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            applyFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    // Auto-focus search if URL has ?search=focus
    if (window.location.search.includes('search=focus') && searchInput) {
        searchInput.focus();
    }

    // === FAQ Accordion ===
    document.querySelectorAll('.faq-question').forEach(btn => {
        btn.addEventListener('click', () => {
            const item = btn.closest('.faq-item');
            const answer = item.querySelector('.faq-answer');
            const isOpen = item.classList.contains('open');

            // Close all
            document.querySelectorAll('.faq-item.open').forEach(openItem => {
                openItem.classList.remove('open');
                const openAnswer = openItem.querySelector('.faq-answer');
                if (typeof gsap !== 'undefined') {
                    gsap.to(openAnswer, { maxHeight: 0, duration: 0.3, ease: 'power2.inOut' });
                } else {
                    openAnswer.style.maxHeight = '0';
                }
            });

            // Toggle current
            if (!isOpen) {
                item.classList.add('open');
                if (typeof gsap !== 'undefined') {
                    gsap.fromTo(answer,
                        { maxHeight: 0 },
                        { maxHeight: answer.scrollHeight + 'px', duration: 0.35, ease: 'power3.out' }
                    );
                } else {
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                }
            }
        });
    });

    // FAQ search
    const faqSearch = document.getElementById('faqSearch');
    if (faqSearch) {
        faqSearch.addEventListener('input', () => {
            const term = faqSearch.value.toLowerCase();
            document.querySelectorAll('.faq-item').forEach(item => {
                const question = item.querySelector('.faq-question')?.textContent.toLowerCase() || '';
                const answer = item.querySelector('.faq-answer-inner')?.textContent.toLowerCase() || '';
                const matches = question.includes(term) || answer.includes(term);
                item.style.display = matches ? '' : 'none';
            });
        });
    }

    // === Lightbox ===
    const lightbox = document.getElementById('lightbox');
    if (lightbox) {
        const lightboxImg = lightbox.querySelector('img');
        const lightboxClose = lightbox.querySelector('.lightbox-close');
        const lightboxContent = lightbox.querySelector('.lightbox-content');

        function openLightbox(src) {
            if (!lightboxImg || !src) return;
            lightboxImg.src = src;
            lightbox.classList.add('open');
            document.body.style.overflow = 'hidden';

            if (typeof gsap !== 'undefined') {
                gsap.fromTo(lightbox, { opacity: 0 }, { opacity: 1, duration: 0.3, ease: 'power2.out' });
                gsap.fromTo(lightboxContent,
                    { scale: 0.92, opacity: 0 },
                    { scale: 1, opacity: 1, duration: 0.35, ease: 'power3.out', delay: 0.05 }
                );
            }
        }

        function closeLightbox() {
            if (typeof gsap !== 'undefined') {
                gsap.to(lightboxContent, {
                    scale: 0.95, opacity: 0, duration: 0.2, ease: 'power2.in',
                    onComplete: () => {
                        lightbox.classList.remove('open');
                        document.body.style.overflow = '';
                        gsap.set(lightbox, { opacity: 0 });
                    }
                });
                gsap.to(lightbox, {
                    opacity: 0, duration: 0.25, ease: 'power2.in'
                });
            } else {
                lightbox.classList.remove('open');
                document.body.style.overflow = '';
            }
        }

        document.querySelectorAll('[data-lightbox]').forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                // Don't open lightbox when clicking the quick-quote overlay
                if (e.target.closest('.product-quick-btn')) return;
                const src = trigger.getAttribute('data-lightbox') || trigger.querySelector('img')?.src;
                openLightbox(src);
            });
        });

        if (lightboxClose) {
            lightboxClose.addEventListener('click', closeLightbox);
        }

        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) closeLightbox();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && lightbox.classList.contains('open')) {
                closeLightbox();
            }
        });
    }

    // === Contact Form Validation ===
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', handleFormSubmit);
    }

    // === Bulk Order Form ===
    const bulkOrderForm = document.getElementById('bulkOrderForm');
    if (bulkOrderForm) {
        bulkOrderForm.addEventListener('submit', handleFormSubmit);

        // Auto-fill product details
        const productSelect = document.getElementById('productSelect');
        if (productSelect) {
            productSelect.addEventListener('change', () => {
                const option = productSelect.options[productSelect.selectedIndex];
                const priceDisplay = document.getElementById('selectedPrice');
                const moqDisplay = document.getElementById('selectedMoq');
                if (priceDisplay) priceDisplay.textContent = option.dataset.price ? '$' + parseFloat(option.dataset.price).toFixed(2) : '-';
                if (moqDisplay) moqDisplay.textContent = option.dataset.moq || '-';
            });
        }
    }

    // === Form Submit Handler ===
    async function handleFormSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn?.textContent;
        const formMsg = form.querySelector('.form-success, .form-error');

        if (formMsg) formMsg.remove();

        // Client-side validation
        const nameInput = form.querySelector('[name="name"]');
        if (nameInput) {
            nameInput.value = nameInput.value.replace(/[^a-zA-Z\s'-]/g, '');
        }

        // Check reCAPTCHA if present
        const recaptcha = form.querySelector('.g-recaptcha-response');
        if (recaptcha && !recaptcha.value) {
            showMessage(form, 'Please complete the captcha.', 'error');
            return;
        }

        // Check CSRF
        const csrf = form.querySelector('[name="csrf_token"]');
        if (csrf && !csrf.value) {
            showMessage(form, 'Security token missing. Please refresh the page.', 'error');
            return;
        }

        // Submit
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Sending...';
        }

        try {
            const formData = new FormData(form);
            formData.append('ajax', '1');

            const response = await fetch(form.action || window.location.href, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const result = await response.json();

            if (result.success) {
                showMessage(form, result.message || 'Submitted successfully!', 'success');
                form.reset();
            } else {
                showMessage(form, result.message || 'Something went wrong. Please try again.', 'error');
            }
        } catch (err) {
            showMessage(form, 'Network error. Please try again.', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    }

    function showMessage(form, msg, type) {
        const div = document.createElement('div');
        div.className = type === 'success' ? 'form-success' : 'form-error';
        div.textContent = msg;
        form.insertBefore(div, form.firstChild);
        div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // === Name input filter (strip numbers) ===
    document.querySelectorAll('[data-filter-alpha]').forEach(input => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/[^a-zA-Z\s'-]/g, '');
        });
    });

    // === Phone number auto-format ===
    document.querySelectorAll('[name="phone"]').forEach(input => {
        input.addEventListener('input', () => {
            let val = input.value.replace(/\D/g, '');
            if (val.length > 0) {
                if (val.startsWith('92')) {
                    val = '+' + val.substring(0, 2) + ' ' + val.substring(2, 5) + ' ' + val.substring(5, 9) + ' ' + val.substring(9);
                } else if (val.startsWith('0')) {
                    val = val.substring(0, 4) + ' ' + val.substring(4, 7) + ' ' + val.substring(7);
                }
            }
            input.value = val;
        });
    });
});

(function () {
  'use strict';

  if (typeof gsap === 'undefined') return;

  gsap.registerPlugin(ScrollTrigger);

  function splitText(el) {
    if (!el) return [];
    el.setAttribute('aria-hidden', 'true');
    el.style.display = 'inline-block';

    var chars = [];

    function splitNode(node) {
      if (node.nodeType === 3) {
        var text = node.textContent;
        for (var i = 0; i < text.length; i++) {
          var span = document.createElement('span');
          span.className = 'char-span';
          span.textContent = text[i] === ' ' ? '\u00A0' : text[i];
          span.style.display = 'inline-block';
          chars.push(span);
        }
      } else if (node.nodeName === 'BR') {
        var br = document.createElement('br');
        chars.push(br);
      } else if (node.nodeType === 1) {
        var tag = node.nodeName.toLowerCase();
        var wrapper = document.createElement(tag);
        if (tag === 'em' || tag === 'strong' || tag === 'b' || tag === 'i') {
          wrapper.className = node.className;
        }
        var before = chars.length;
        for (var c = 0; c < node.childNodes.length; c++) {
          splitNode(node.childNodes[c]);
        }
        for (var j = before; j < chars.length; j++) {
          wrapper.appendChild(chars[j]);
        }
        chars.push(wrapper);
      }
    }

    var fragment = document.createDocumentFragment();
    var childNodes = Array.prototype.slice.call(el.childNodes);
    el.textContent = '';

    for (var i = 0; i < childNodes.length; i++) {
      splitNode(childNodes[i]);
    }

    for (var i = 0; i < chars.length; i++) {
      fragment.appendChild(chars[i]);
    }
    el.appendChild(fragment);

    var parent = el.parentElement;
    if (parent && !parent.getAttribute('role')) {
      parent.setAttribute('role', 'text');
    }

    return chars;
  }

  function animateHero() {
    var heroH1 = document.querySelector('#hero h1') ||
                 document.querySelector('main .section h1') ||
                 document.querySelector('main section h1');
    if (!heroH1) return;

    var heroSection = heroH1.closest('section') || heroH1.closest('#hero');
    if (!heroSection) return;

    var kicker = heroSection.querySelector('.kicker');
    var para = heroSection.querySelector('p');
    var ctaActions = heroSection.querySelector('.cta-actions');

    var chars = splitText(heroH1);

    var tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

    if (kicker) {
      tl.fromTo(kicker, { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.5, delay: 0.3 });
    }

    if (chars.length) {
      tl.fromTo(chars, {
        y: 30,
        opacity: 0,
      }, {
        y: 0,
        opacity: 1,
        duration: 0.8,
        stagger: 0.03,
        ease: 'power3.out',
      }, kicker ? '-=0.2' : '+=0.3');
    } else {
      tl.fromTo(heroH1, { opacity: 0, y: 30 }, { opacity: 1, y: 0, duration: 0.7 }, kicker ? '-=0.2' : '+=0.3');
    }

    if (para) {
      tl.fromTo(para, { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.6 }, '-=0.3');
    }

    if (ctaActions) {
      tl.fromTo(ctaActions, { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: 0.5 }, '-=0.2');
    }
  }

  function animateFilters() {
    var pills = document.querySelectorAll('.pill-group .pill');
    var searchBox = document.querySelector('.search-box');
    if (!pills.length && !searchBox) return;

    var targets = Array.from(pills);
    if (searchBox) targets.push(searchBox);

    gsap.set(targets, { opacity: 0, scale: 0.9 });

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          gsap.to(targets, {
            opacity: 1,
            scale: 1,
            duration: 0.4,
            stagger: 0.05,
            ease: 'back.out(1.7)',
          });
          observer.disconnect();
        }
      });
    }, { threshold: 0.1 });

    var container = pills[0] ? pills[0].parentElement : searchBox;
    if (container) observer.observe(container);
  }

  function animateStats() {
    var cards = document.querySelectorAll('.stat-card[data-count]');
    cards.forEach(function (card) {
      var valueEl = card.querySelector('.count-value');
      var target = parseInt(card.getAttribute('data-count'), 10);
      var suffix = card.getAttribute('data-suffix') || '';
      if (!valueEl || isNaN(target)) return;

      ScrollTrigger.create({
        trigger: card,
        start: 'top 85%',
        once: true,
        onEnter: function () {
          gsap.fromTo(valueEl, { textContent: 0 }, {
            textContent: target,
            duration: 1.6,
            ease: 'power3.out',
            snap: { textContent: 1 },
            onUpdate: function () {
              valueEl.textContent = Math.round(parseFloat(valueEl.textContent)).toLocaleString();
            },
            onComplete: function () {
              valueEl.textContent = target.toLocaleString() + suffix;
            }
          });
        }
      });
    });
  }

  function animateCards() {
    document.querySelectorAll('.section-header').forEach(function (header) {
      ScrollTrigger.create({
        trigger: header,
        start: 'top 80%',
        once: true,
        onEnter: function () {
          gsap.fromTo(header.querySelectorAll('[data-reveal]'),
            { opacity: 0, y: 24 },
            { opacity: 1, y: 0, duration: 0.6, stagger: 0.1, ease: 'power3.out' }
          );
        }
      });
    });

    var gridSelectors = [
      '.grid-auto', '.grid-3', '.grid-4',
      '.stats-grid', '.values-grid', '.bulk-process', '.contact-paths'
    ];

    gridSelectors.forEach(function (selector) {
      document.querySelectorAll(selector).forEach(function (grid) {
        ScrollTrigger.create({
          trigger: grid,
          start: 'top 80%',
          once: true,
          onEnter: function () {
            var children = grid.querySelectorAll('.card, .step-card, .value-card, .bulk-process-step');
            if (!children.length) return;

            gsap.set(grid, { perspective: 1000 });

            gsap.fromTo(children,
              { opacity: 0, y: 60, rotationX: 15 },
              {
                opacity: 1,
                y: 0,
                rotationX: 0,
                duration: 0.8,
                stagger: 0.1,
                ease: 'power3.out',
              }
            );
          }
        });
      });
    });

    var otherCards = document.querySelectorAll('.contact-info-card, .faq-item');
    if (otherCards.length) {
      ScrollTrigger.create({
        trigger: otherCards[0].parentElement,
        start: 'top 80%',
        once: true,
        onEnter: function () {
          gsap.fromTo(otherCards,
            { opacity: 0, y: 40, rotationX: 10 },
            { opacity: 1, y: 0, rotationX: 0, duration: 0.7, stagger: 0.08, ease: 'power3.out' }
          );
        }
      });
    }
  }

  function animateCta() {
    document.querySelectorAll('.cta-banner').forEach(function (cta) {
      ScrollTrigger.create({
        trigger: cta,
        start: 'top 85%',
        once: true,
        onEnter: function () {
          gsap.fromTo(cta.children,
            { opacity: 0, y: 20 },
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.08, ease: 'power3.out' }
          );
        }
      });
    });
  }

  function animateMarquee() {
    var track = document.querySelector('.marquee-track');
    if (!track) return;

    var trackWidth = track.scrollWidth;
    var containerWidth = track.parentElement ? track.parentElement.offsetWidth : window.innerWidth;

    if (trackWidth > 0) {
      gsap.to(track, {
        x: function () { return -(trackWidth / 2); },
        duration: 30,
        repeat: -1,
        ease: 'none',
      });
    }
  }

  function animateMagneticButtons() {
    document.querySelectorAll('.btn').forEach(function (btn) {
      var maxOffset = 15;

      btn.addEventListener('mousemove', function (e) {
        var rect = btn.getBoundingClientRect();
        var x = e.clientX - rect.left - rect.width / 2;
        var y = e.clientY - rect.top - rect.height / 2;
        var offsetX = Math.max(-maxOffset, Math.min(maxOffset, x * 0.3));
        var offsetY = Math.max(-maxOffset, Math.min(maxOffset, y * 0.3));

        gsap.to(btn, { x: offsetX, y: offsetY, duration: 0.3, ease: 'power2.out' });
      });

      btn.addEventListener('mouseleave', function () {
        gsap.to(btn, { x: 0, y: 0, duration: 0.5, ease: 'elastic.out(1, 0.3)' });
      });
    });
  }

  function animateCardHover() {
    document.querySelectorAll('.product-card').forEach(function (card) {
      card.addEventListener('mouseenter', function () {
        gsap.to(card, { scale: 1.03, boxShadow: '0 20px 40px rgba(0,0,0,0.12)', duration: 0.3, ease: 'power2.out' });
      });

      card.addEventListener('mouseleave', function () {
        gsap.to(card, { scale: 1, boxShadow: '0 0 0 rgba(0,0,0,0)', duration: 0.4, ease: 'power2.out' });
      });

      card.addEventListener('mousemove', function (e) {
        var rect = card.getBoundingClientRect();
        var x = (e.clientX - rect.left) / rect.width - 0.5;
        var y = (e.clientY - rect.top) / rect.height - 0.5;

        gsap.to(card, {
          rotationY: x * 5,
          rotationX: -y * 5,
          transformPerspective: 800,
          duration: 0.3,
          ease: 'power2.out',
        });
      });

      card.addEventListener('mouseleave', function () {
        gsap.to(card, { rotationY: 0, rotationX: 0, duration: 0.5, ease: 'power2.out' });
      });
    });
  }

  function init() {
    animateHero();
    animateFilters();
    animateStats();
    animateCards();
    animateCta();
    animateMarquee();
    animateMagneticButtons();
    animateCardHover();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

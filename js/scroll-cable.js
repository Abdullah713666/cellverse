(function () {
  'use strict';

  if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
  gsap.registerPlugin(ScrollTrigger);

  // Homepage only — heroCanvas only exists on index.php
  var heroCanvas = document.getElementById('heroCanvas');
  if (!heroCanvas) return;

  // Skip on mobile
  if (window.innerWidth <= 768) return;

  var isDark = document.documentElement.getAttribute('data-theme') === 'dark';

  // ── SVG Setup ──────────────────────────────────────────────
  var svgNS = 'http://www.w3.org/2000/svg';
  var svg = document.createElementNS(svgNS, 'svg');
  svg.setAttribute('class', 'scroll-cable-svg');
  svg.setAttribute('viewBox', '0 0 1000 3000');
  svg.setAttribute('preserveAspectRatio', 'none');

  // ── Cable Path ─────────────────────────────────────────────
  // Zigzag from top-left downward with irregular bends
  var cablePath = document.createElementNS(svgNS, 'path');
  var cableD = [
    'M 80 0',
    'C 80 100, 200 160, 320 280',
    'S 140 440, 180 560',
    'S 520 680, 640 840',
    'S 220 1000, 300 1160',
    'S 680 1300, 780 1440',
    'S 380 1600, 420 1740',
    'S 720 1880, 840 2020',
    'S 480 2180, 540 2320',
    'S 760 2460, 860 2600',
    'L 860 2640'
  ].join(' ');

  cablePath.setAttribute('d', cableD);
  cablePath.setAttribute('fill', 'none');
  cablePath.setAttribute('stroke', '#d4a843');
  cablePath.setAttribute('stroke-width', '4');
  cablePath.setAttribute('stroke-linecap', 'round');
  cablePath.setAttribute('stroke-linejoin', 'round');

  svg.appendChild(cablePath);

  // ── USB-C Connector at tip ─────────────────────────────────
  var connector = document.createElementNS(svgNS, 'g');
  connector.setAttribute('class', 'scroll-cable-connector');

  // Connector housing
  var housing = document.createElementNS(svgNS, 'rect');
  housing.setAttribute('x', '848');
  housing.setAttribute('y', '2636');
  housing.setAttribute('width', '24');
  housing.setAttribute('height', '40');
  housing.setAttribute('rx', '4');
  housing.setAttribute('fill', '#d4a843');
  housing.setAttribute('opacity', '0.7');

  // Connector tongue
  var tongue = document.createElementNS(svgNS, 'rect');
  tongue.setAttribute('x', '853');
  tongue.setAttribute('y', '2670');
  tongue.setAttribute('width', '14');
  tongue.setAttribute('height', '8');
  tongue.setAttribute('rx', '2');
  tongue.setAttribute('fill', '#d4a843');
  tongue.setAttribute('opacity', '0.5');

  connector.appendChild(housing);
  connector.appendChild(tongue);
  svg.appendChild(connector);

  document.body.appendChild(svg);

  // ── Stroke Draw Animation ──────────────────────────────────
  var totalLength = cablePath.getTotalLength();
  cablePath.setAttribute('stroke-dasharray', totalLength);
  cablePath.setAttribute('stroke-dashoffset', totalLength);

  gsap.to(cablePath, {
    strokeDashoffset: 0,
    ease: 'none',
    scrollTrigger: {
      trigger: document.body,
      start: 'top top',
      end: 'bottom bottom',
      scrub: 0.5,
    }
  });

  // ── Sway Animation ─────────────────────────────────────────
  // Subtle horizontal sway that increases with scroll depth
  var swayTimeline = gsap.timeline({
    scrollTrigger: {
      trigger: document.body,
      start: 'top top',
      end: 'bottom bottom',
      scrub: 0.3,
    }
  });

  // Sway the entire SVG slightly left-right as user scrolls
  swayTimeline.fromTo(svg,
    { x: 0 },
    { x: 15, ease: 'sine.inOut', yoyo: true, repeat: 5 }
  );

  // ── Theme Change ───────────────────────────────────────────
  var observer = new MutationObserver(function (mutations) {
    for (var i = 0; i < mutations.length; i++) {
      if (mutations[i].attributeName === 'data-theme') {
        isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        var color = isDark ? '#d4a843' : '#d4a843';
        cablePath.setAttribute('stroke', color);
        housing.setAttribute('fill', color);
        tongue.setAttribute('fill', color);
      }
    }
  });
  observer.observe(document.documentElement, { attributes: true });

  // ── Visibility Change ──────────────────────────────────────
  document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
      ScrollTrigger.getAll().forEach(function (st) { st.disable(); });
    } else {
      ScrollTrigger.getAll().forEach(function (st) { st.enable(); });
    }
  });
})();

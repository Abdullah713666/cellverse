(function () {
  'use strict';

  var canvas = document.getElementById('heroCanvas');
  if (!canvas || typeof THREE === 'undefined') return;

  var visual = canvas.parentElement;
  if (!visual) return;

  var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  var goldColor = 0xd4a843;
  var navyColor = 0x1e3a5f;

  // ── Cable SVG ──────────────────────────────────────────────
  var cableSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  cableSvg.setAttribute('class', 'hero-cable');
  cableSvg.setAttribute('viewBox', '0 0 400 600');
  cableSvg.setAttribute('preserveAspectRatio', 'none');
  cableSvg.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;pointer-events:none;z-index:1;';

  var cablePath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
  var cableD = 'M 200 0 C 200 60, 60 80, 60 140 S 340 200, 340 260 S 60 320, 60 380 S 340 440, 340 500 L 340 560';
  cablePath.setAttribute('d', cableD);
  cablePath.setAttribute('fill', 'none');
  cablePath.setAttribute('stroke', isDark ? '#d4a843' : '#1e3a5f');
  cablePath.setAttribute('stroke-width', '3');
  cablePath.setAttribute('stroke-linecap', 'round');
  cablePath.setAttribute('opacity', '0.4');

  var cableConnector = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
  cableConnector.setAttribute('x', '328');
  cableConnector.setAttribute('y', '555');
  cableConnector.setAttribute('width', '24');
  cableConnector.setAttribute('height', '36');
  cableConnector.setAttribute('rx', '4');
  cableConnector.setAttribute('fill', isDark ? '#d4a843' : '#1e3a5f');
  cableConnector.setAttribute('opacity', '0.35');

  cableSvg.appendChild(cablePath);
  cableSvg.appendChild(cableConnector);
  visual.appendChild(cableSvg);

  // Animate cable stroke draw-in
  var cableLength = cablePath.getTotalLength();
  cablePath.setAttribute('stroke-dasharray', cableLength);
  cablePath.setAttribute('stroke-dashoffset', cableLength);

  function drawCable() {
    var start = null;
    var duration = 1800;
    function step(ts) {
      if (!start) start = ts;
      var progress = Math.min((ts - start) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3);
      cablePath.setAttribute('stroke-dashoffset', cableLength * (1 - eased));
      if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  setTimeout(drawCable, 600);

  // ── Three.js Wireframe Scene ───────────────────────────────
  var scene = new THREE.Scene();
  scene.background = null;

  var w = visual.clientWidth || 400;
  var h = visual.clientHeight || 300;
  var camera = new THREE.OrthographicCamera(-w / 2, w / 2, h / 2, -h / 2, -1000, 1000);
  camera.position.z = 500;

  var renderer = new THREE.WebGLRenderer({ canvas: canvas, alpha: true, antialias: true });
  renderer.setSize(w, h);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setClearColor(0x000000, 0);

  var wireColor = isDark ? goldColor : navyColor;

  // ── Phone case wireframe ───────────────────────────────────
  var phoneGeo = new THREE.BoxGeometry(90, 160, 10);
  var phoneEdges = new THREE.EdgesGeometry(phoneGeo);
  var phoneMat = new THREE.LineBasicMaterial({ color: wireColor, transparent: true, opacity: 0.55 });
  var phoneWire = new THREE.LineSegments(phoneEdges, phoneMat);
  phoneWire.position.set(-80, 30, 0);
  scene.add(phoneWire);

  var screenGeo = new THREE.BoxGeometry(70, 120, 2);
  var screenEdges = new THREE.EdgesGeometry(screenGeo);
  var screenMat = new THREE.LineBasicMaterial({ color: wireColor, transparent: true, opacity: 0.3 });
  var screenWire = new THREE.LineSegments(screenEdges, screenMat);
  screenWire.position.set(-80, 35, 5);
  scene.add(screenWire);

  // ── Earbuds wireframe ──────────────────────────────────────
  var budGroup = new THREE.Group();
  var budGeo = new THREE.SphereGeometry(18, 8, 6);
  var budEdges = new THREE.EdgesGeometry(budGeo);
  var budMat = new THREE.LineBasicMaterial({ color: wireColor, transparent: true, opacity: 0.5 });

  var bud1 = new THREE.LineSegments(budEdges, budMat);
  bud1.position.set(-15, 0, 0);
  budGroup.add(bud1);

  var bud2 = new THREE.LineSegments(budEdges.clone(), budMat);
  bud2.position.set(15, 0, 0);
  budGroup.add(bud2);

  var stemGeo = new THREE.CylinderGeometry(3, 3, 25, 6);
  var stemEdges = new THREE.EdgesGeometry(stemGeo);
  var stemMat = new THREE.LineBasicMaterial({ color: wireColor, transparent: true, opacity: 0.4 });

  var stem1 = new THREE.LineSegments(stemEdges, stemMat);
  stem1.position.set(-15, -22, 0);
  budGroup.add(stem1);

  var stem2 = new THREE.LineSegments(stemEdges.clone(), stemMat);
  stem2.position.set(15, -22, 0);
  budGroup.add(stem2);

  budGroup.position.set(90, -20, 0);
  scene.add(budGroup);

  // ── Powerbank wireframe ────────────────────────────────────
  var pbGeo = new THREE.BoxGeometry(120, 60, 15);
  var pbEdges = new THREE.EdgesGeometry(pbGeo);
  var pbMat = new THREE.LineBasicMaterial({ color: wireColor, transparent: true, opacity: 0.45 });
  var pbWire = new THREE.LineSegments(pbEdges, pbMat);
  pbWire.position.set(10, -140, 0);
  scene.add(pbWire);

  var portGeo = new THREE.BoxGeometry(12, 5, 5);
  var portEdges = new THREE.EdgesGeometry(portGeo);
  var portMat = new THREE.LineBasicMaterial({ color: wireColor, transparent: true, opacity: 0.35 });
  var portWire = new THREE.LineSegments(portEdges, portMat);
  portWire.position.set(10, -115, 0);
  scene.add(portWire);

  // ── Mouse tracking ─────────────────────────────────────────
  var mouseX = 0;
  var mouseY = 0;
  var targetMouseX = 0;
  var targetMouseY = 0;

  visual.addEventListener('mousemove', function (e) {
    var rect = visual.getBoundingClientRect();
    targetMouseX = ((e.clientX - rect.left) / rect.width - 0.5) * 40;
    targetMouseY = ((e.clientY - rect.top) / rect.height - 0.5) * 40;
  });

  visual.addEventListener('mouseleave', function () {
    targetMouseX = 0;
    targetMouseY = 0;
  });

  // ── Animation loop ─────────────────────────────────────────
  var animating = true;
  var lastTime = 0;
  var time = 0;

  function animate(timestamp) {
    if (!animating) return;
    requestAnimationFrame(animate);

    if (timestamp - lastTime < 16) return;
    lastTime = timestamp;
    time += 0.008;

    mouseX += (targetMouseX - mouseX) * 0.06;
    mouseY += (targetMouseY - mouseY) * 0.06;

    phoneWire.rotation.y = Math.sin(time * 0.4) * 0.3 + mouseX * 0.005;
    phoneWire.rotation.x = Math.cos(time * 0.3) * 0.15;
    screenWire.rotation.y = phoneWire.rotation.y;
    screenWire.rotation.x = phoneWire.rotation.x;

    budGroup.rotation.y = Math.sin(time * 0.5 + 1) * 0.25 + mouseX * 0.004;
    budGroup.rotation.x = Math.cos(time * 0.35 + 1) * 0.12;
    bud1.rotation.z = Math.sin(time * 0.6) * 0.1;
    bud2.rotation.z = -Math.sin(time * 0.6) * 0.1;

    pbWire.rotation.y = Math.sin(time * 0.3 + 2) * 0.2 + mouseX * 0.003;
    pbWire.rotation.x = Math.cos(time * 0.25 + 2) * 0.1;
    portWire.rotation.y = pbWire.rotation.y;
    portWire.rotation.x = pbWire.rotation.x;

    var swayX = Math.sin(time * 0.8) * 12 + mouseX * 0.5;
    var swayY = Math.cos(time * 0.6) * 6 + mouseY * 0.3;
    cableSvg.style.transform = 'translate(' + swayX + 'px, ' + swayY + 'px)';

    renderer.render(scene, camera);
  }

  requestAnimationFrame(animate);

  // ── Resize handler ─────────────────────────────────────────
  window.addEventListener('resize', function () {
    var newW = visual.clientWidth || 400;
    var newH = visual.clientHeight || 300;
    camera.left = -newW / 2;
    camera.right = newW / 2;
    camera.top = newH / 2;
    camera.bottom = -newH / 2;
    camera.updateProjectionMatrix();
    renderer.setSize(newW, newH);
  });

  // ── Theme change listener ──────────────────────────────────
  var observer = new MutationObserver(function (mutations) {
    for (var i = 0; i < mutations.length; i++) {
      if (mutations[i].attributeName === 'data-theme') {
        isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        wireColor = isDark ? goldColor : navyColor;
        phoneMat.color.setHex(wireColor);
        screenMat.color.setHex(wireColor);
        budMat.color.setHex(wireColor);
        stemMat.color.setHex(wireColor);
        pbMat.color.setHex(wireColor);
        portMat.color.setHex(wireColor);
        cablePath.setAttribute('stroke', isDark ? '#d4a843' : '#1e3a5f');
        cableConnector.setAttribute('fill', isDark ? '#d4a843' : '#1e3a5f');
      }
    }
  });
  observer.observe(document.documentElement, { attributes: true });

  // ── Visibility change ──────────────────────────────────────
  document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
      animating = false;
    } else {
      animating = true;
      lastTime = 0;
      requestAnimationFrame(animate);
    }
  });
})();

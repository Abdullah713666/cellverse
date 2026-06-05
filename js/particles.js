/**
 * CellVerse - Enhanced Canvas Particle Engine
 * Neural network constellation with mouse interaction
 * Respects prefers-reduced-motion
 */
(function() {
    const canvas = document.getElementById('particleCanvas');
    if (!canvas) return;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reducedMotion) return;

    const ctx = canvas.getContext('2d');
    let particles = [];
    let mouse = { x: null, y: null, radius: 160 };
    let animationId;
    let pulseTime = 0;

    const COLORS = [
        [0, 212, 170],    // teal
        [124, 58, 237],   // purple
        [0, 180, 150],    // dim teal
        [100, 116, 139],  // muted
        [226, 232, 240],  // light (sparse)
    ];

    function resize() {
        const parent = canvas.parentElement;
        const dpr = window.devicePixelRatio || 1;
        canvas.width = parent.offsetWidth * dpr;
        canvas.height = parent.offsetHeight * dpr;
        ctx.scale(dpr, dpr);
        canvas.style.width = parent.offsetWidth + 'px';
        canvas.style.height = parent.offsetHeight + 'px';
    }

    function Particle() {
        this.x = Math.random() * (canvas.width / (window.devicePixelRatio || 1));
        this.y = Math.random() * (canvas.height / (window.devicePixelRatio || 1));
        this.size = Math.random() * 2.2 + 0.6;
        this.baseX = this.x;
        this.baseY = this.y;
        this.speedX = (Math.random() - 0.5) * 0.6;
        this.speedY = (Math.random() - 0.5) * 0.6;
        const c = COLORS[Math.floor(Math.random() * COLORS.length)];
        this.color = c;
        this.pulseOffset = Math.random() * Math.PI * 2;
        this.pulseSpeed = 0.005 + Math.random() * 0.008;
    }

    Particle.prototype.update = function() {
        if (mouse.x !== null && mouse.y !== null) {
            const dx = mouse.x - this.x;
            const dy = mouse.y - this.y;
            const dist = Math.sqrt(dx * dx + dy * dy);

            if (dist < mouse.radius) {
                const force = (mouse.radius - dist) / mouse.radius;
                const angle = Math.atan2(dy, dx);
                this.speedX -= Math.cos(angle) * force * 1.5;
                this.speedY -= Math.sin(angle) * force * 1.5;
            }
        }

        this.speedX *= 0.97;
        this.speedY *= 0.97;

        const maxSpeed = 2.5;
        const speed = Math.sqrt(this.speedX * this.speedX + this.speedY * this.speedY);
        if (speed > maxSpeed) {
            this.speedX = (this.speedX / speed) * maxSpeed;
            this.speedY = (this.speedY / speed) * maxSpeed;
        }

        this.x += this.speedX;
        this.y += this.speedY;

        const w = canvas.width / (window.devicePixelRatio || 1);
        const h = canvas.height / (window.devicePixelRatio || 1);
        if (this.x < -10) this.x = w + 10;
        if (this.x > w + 10) this.x = -10;
        if (this.y < -10) this.y = h + 10;
        if (this.y > h + 10) this.y = -10;
    };

    Particle.prototype.draw = function() {
        const pulse = Math.sin(pulseTime * this.pulseSpeed + this.pulseOffset) * 0.3 + 0.7;
        const alpha = pulse * 0.6;
        const [r, g, b] = this.color;

        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size * pulse, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(${r}, ${g}, ${b}, ${alpha})`;
        ctx.fill();
    };

    function init() {
        resize();
        particles = [];
        const w = canvas.width / (window.devicePixelRatio || 1);
        const h = canvas.height / (window.devicePixelRatio || 1);
        const area = w * h;
        const count = Math.min(Math.max(Math.floor(area / 7000), 60), 180);

        for (let i = 0; i < count; i++) {
            particles.push(new Particle());
        }
    }

    function connectParticles() {
        const connectionDist = 130;
        for (let a = 0; a < particles.length; a++) {
            for (let b = a + 1; b < particles.length; b++) {
                const dx = particles[a].x - particles[b].x;
                const dy = particles[a].y - particles[b].y;
                const dist = Math.sqrt(dx * dx + dy * dy);

                if (dist < connectionDist) {
                    const opacity = (1 - dist / connectionDist) * 0.18;
                    const [r, g, b] = particles[a].color;
                    ctx.beginPath();
                    ctx.strokeStyle = `rgba(${r}, ${g}, ${b}, ${opacity})`;
                    ctx.lineWidth = 0.6;
                    ctx.moveTo(particles[a].x, particles[a].y);
                    ctx.lineTo(particles[b].x, particles[b].y);
                    ctx.stroke();
                }
            }
        }
    }

    function drawMouseGlow() {
        if (mouse.x === null || mouse.y === null) return;
        const gradient = ctx.createRadialGradient(mouse.x, mouse.y, 0, mouse.x, mouse.y, mouse.radius);
        gradient.addColorStop(0, 'rgba(0, 212, 170, 0.06)');
        gradient.addColorStop(1, 'rgba(0, 212, 170, 0)');
        ctx.beginPath();
        ctx.arc(mouse.x, mouse.y, mouse.radius, 0, Math.PI * 2);
        ctx.fillStyle = gradient;
        ctx.fill();
    }

    function animate() {
        const w = canvas.width / (window.devicePixelRatio || 1);
        const h = canvas.height / (window.devicePixelRatio || 1);
        ctx.clearRect(0, 0, w, h);
        pulseTime++;

        drawMouseGlow();

        for (let i = 0; i < particles.length; i++) {
            particles[i].update();
            particles[i].draw();
        }

        connectParticles();
        animationId = requestAnimationFrame(animate);
    }

    window.addEventListener('resize', () => {
        resize();
        init();
    });

    canvas.addEventListener('mousemove', (e) => {
        const rect = canvas.getBoundingClientRect();
        mouse.x = e.clientX - rect.left;
        mouse.y = e.clientY - rect.top;
    });

    canvas.addEventListener('mouseleave', () => {
        mouse.x = null;
        mouse.y = null;
    });

    canvas.addEventListener('touchmove', (e) => {
        const rect = canvas.getBoundingClientRect();
        mouse.x = e.touches[0].clientX - rect.left;
        mouse.y = e.touches[0].clientY - rect.top;
    }, { passive: true });

    canvas.addEventListener('touchend', () => {
        mouse.x = null;
        mouse.y = null;
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            cancelAnimationFrame(animationId);
        } else {
            animate();
        }
    });

    init();
    animate();
})();

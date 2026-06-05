/**
 * CellVerse - Text Scramble Effect
 * Cyberpunk decode animation for headings
 * Respects prefers-reduced-motion
 */
class TextScramble {
    constructor(el) {
        this.el = el;
        this.chars = '!<>-_\\/[]{}#$%^&*+=~`|';
        this.originalText = el.textContent;
        this.frame = 0;
        this.queue = [];
        this.resolve = null;
        this.frameRequest = null;
    }

    setText(newText) {
        const oldText = this.el.textContent;
        const length = Math.max(oldText.length, newText.length);
        const promise = new Promise(resolve => this.resolve = resolve);
        this.queue = [];

        for (let i = 0; i < length; i++) {
            const from = oldText[i] || '';
            const to = newText[i] || '';
            const start = Math.floor(Math.random() * 30);
            const end = start + Math.floor(Math.random() * 30);
            this.queue.push({ from, to, start, end });
        }

        cancelAnimationFrame(this.frameRequest);
        this.frame = 0;
        this.update();
        return promise;
    }

    update() {
        let output = '';
        let complete = 0;

        for (let i = 0, n = this.queue.length; i < n; i++) {
            let { from, to, start, end, char } = this.queue[i];

            if (this.frame >= end) {
                complete++;
                output += to;
            } else if (this.frame >= start) {
                if (!char || Math.random() < 0.28) {
                    char = this.chars[Math.floor(Math.random() * this.chars.length)];
                    this.queue[i].char = char;
                }
                output += `<span style="color:var(--color-primary);opacity:0.6;">${char}</span>`;
            } else {
                output += from;
            }
        }

        this.el.innerHTML = output;

        if (complete === this.queue.length) {
            this.resolve();
        } else {
            this.frameRequest = requestAnimationFrame(() => this.update());
            this.frame++;
        }
    }
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const heroH1 = document.querySelector('.hero h1');
    if (!heroH1) return;

    // Skip if the new GSAP word-reveal is in use (hero-word spans present)
    if (heroH1.querySelector('.hero-word')) return;

    const originalText = heroH1.textContent;
    const scramble = new TextScramble(heroH1);

    // Run scramble after a short delay
    setTimeout(() => {
        scramble.setText(originalText);
    }, 400);
});

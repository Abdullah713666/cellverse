# CLAUDE.md — CellVerse Project Context

## Project Overview

**CellVerse** is a B2B wholesale mobile accessories catalog and ordering platform targeting the Pakistani market. It's a server-rendered PHP + MySQL web application with a modern dark-themed UI, GSAP animations, and a full admin panel.

- **GitHub**: `https://github.com/Abdullah713666/cellverse` (owner: `Abdullah713666`)
- **Branch**: `main` (single branch, no tags/releases)
- **Local path**: `E:\XAMPP\htdocs\cellverse`
- **Database**: `cellverse_db` (MySQL)
- **Default admin**: `admin` / `admin` (change via `/admin/settings.php`)

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.2 (vanilla, no framework) |
| Database | MySQL (PDO with real prepared statements) |
| Frontend | HTML5, CSS3 (custom properties), Vanilla JS |
| Animations | GSAP 3.12.5 + ScrollTrigger (CDN) |
| 3D | Three.js r128 (CDN, hero canvas) |
| Fonts | DM Serif Display, Plus Jakarta Sans, JetBrains Mono (Google Fonts) |
| Deployment | Railway (Docker via `php:8.2-cli`) |
| Local dev | XAMPP (Apache + PHP) |

---

## Directory Structure

```
cellverse/
├── config/
│   ├── database.php          # PDO connection, CSRF helpers, validation functions
│   └── init.php              # Session, security headers, BASE_URL detection
├── includes/
│   ├── header.php            # Global header, nav, OG/meta tags, JSON-LD, dark mode init
│   └── footer.php            # Global footer, scroll buttons, script tags
├── admin/
│   ├── auth.php              # Login/session/brute-force protection
│   ├── index.php             # Admin dashboard (stats, recent orders)
│   ├── login.php / logout.php
│   ├── products.php / categories.php / orders.php
│   ├── messages.php / faqs.php / users.php
│   ├── settings.php / reports.php
│   ├── includes/sidebar.php
│   ├── style.css             # Admin-specific styles
│   └── *.php                 # Each admin page is self-contained
├── css/
│   ├── tokens.css            # Design tokens (colors, spacing, typography, dark mode)
│   ├── base.css              # Reset, global styles
│   ├── components.css        # Buttons, cards, badges, forms
│   ├── layout.css            # Container, grid, header, footer
│   ├── animations.css        # GSAP-triggered classes, transitions
│   └── responsive.css        # Media queries
├── js/
│   ├── app.js                # Core init (minimal)
│   ├── hero-mesh.js          # Three.js hero canvas animation
│   ├── animations.js         # GSAP scroll animations, count-up, reveals
│   ├── interactions.js       # Theme toggle, mobile nav, carousel, form UX
│   └── scroll-cable.js       # Custom scroll-driven cable visual
├── images/
│   ├── favicon.svg / logo.svg / og-default.svg
├── index.php                 # Homepage (hero, stats, featured products, testimonials, CTA)
├── products.php              # Product catalog with category filtering
├── bulk-order.php            # Bulk order request form
├── about.php / faq.php / contact.php / privacy.php / terms.php
├── sitemap.php / sitemap.xml / robots.txt
├── install.php               # One-time DB installer (creates tables + seeds)
├── database.sql              # Full schema + seed data
├── Dockerfile                # php:8.2-cli + pdo_mysql for Railway
├── nixpacks.toml             # Railway build config
├── Procfile                  # Railway process definition
├── start.sh                  # Entrypoint script (PORT expansion)
├── .htaccess                 # Apache rewrite, GZIP, caching, CSP, security headers
├── .gitignore / .dockerignore
└── test-*.png                # Screenshot artifacts (not deployed)
```

---

## Database Schema (8 tables)

| Table | Purpose |
|-------|---------|
| `categories` | Product categories (8 seeded) |
| `products` | Products with price, MOQ, stock, SKU, featured flag |
| `bulk_orders` | B2B order requests (company, contact, product, qty, status) |
| `contact_submissions` | Contact form submissions |
| `testimonials` | Client testimonials (3 seeded) |
| `faqs` | FAQ entries (8 seeded) |
| `site_settings` | Key-value site configuration |
| `admin_users` | Admin accounts (bcrypt hashed) |
| `users` | Registered user accounts |

---

## Key Features

### Public Pages (9)
- **Homepage**: Hero with Three.js canvas, stats counter, featured products, testimonials carousel, marquee, CTA banner
- **Products**: Category-filtered grid, search, product cards with price/MOQ
- **Bulk Order**: Form with product selection, quantity, delivery details
- **About / FAQ / Contact / Privacy / Terms / Sitemap**

### Admin Panel (9 pages)
- **Dashboard**: Stats cards (products, orders, pending, messages), recent orders table
- **CRUD**: Products, categories, FAQs, users, messages, orders
- **Settings / Reports**
- Auth: bcrypt password verification, brute-force lockout (5 attempts / 15 min), 30-min idle timeout, tab-scoped sessions

### Design System
- **Colors**: Navy (#1e3a5f) + Gold (#b8860b) palette with light/dark modes
- **Dark mode**: `[data-theme="dark"]` toggle persisted in localStorage
- **CSS custom properties**: Full token system in `tokens.css`
- **Animations**: GSAP ScrollTrigger reveals, count-up, marquee, carousel, magnetic CTA

### Security
- CSRF tokens on all state-changing forms (`csrf_field()` + `require_csrf_or_die()`)
- PDO with `EMULATE_PREPARES => false` (real prepared statements)
- Session cookies: `httponly`, `samesite=Strict`
- Security headers via `.htaccess`: CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy
- Input validation: `clamp_int()`, `validate_phone()`, length caps, regex
- Image upload validated via `finfo->file()` (server-side MIME check)
- Admin pages have `noindex, nofollow` robots header

---

## Deployment

### Railway (Production)
- **Auto-detects** Railway MySQL via `MYSQLHOST`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD` env vars
- **BASE_URL** auto-detected: empty string on Railway (root serving), `/{folder}` locally
- **Start command**: `start.sh` → `php -S 0.0.0.0:${PORT:-8080} -t .`
- **Dockerfile**: `php:8.2-cli` with `pdo_mysql` extension
- **First run**: Import `database.sql` or delete `.installed` marker to run `install.php`

### Local Development (XAMPP)
1. Drop folder into `htdocs/`
2. Create MySQL database `cellverse_db`
3. Visit `http://localhost/cellverse/install.php`
4. Default admin: `admin` / `admin`

---

## Git History (20 commits, all from 2026-06-05)

Key commits in reverse chronological order:
1. `75e06c4` — Live feed: GSAP random-walk value updates, heartbeat, header-dot blink
2. `f9eb566` — Hero & stats: ambient pulse, staggered reveal, count-up, border beam
3. `b664bdf` — Fix BASE_URL on Railway
4. `e0a2761` — Add Dockerfile + composer.json for pdo_mysql
5. `b960be3` — Initial commit: full B2B catalog with 9 public + 9 admin pages

---

## Code Conventions

- **No framework**: Pure PHP, each page is self-contained (includes header/footer/config)
- **Database access**: Always via `getDB()` singleton (PDO)
- **CSRF pattern**: `<?php csrf_field(); ?>` in forms, `require_csrf_or_die()` in handlers
- **HTML output**: `htmlspecialchars()` on all dynamic content
- **CSS**: Custom properties only, BEM-like naming, dark mode via `[data-theme="dark"]`
- **JS**: IIFE modules, `defer` loading, no build step
- **PHP 8.2**: Uses named args, `match`, enums, readonly where appropriate
- **No Composer dependencies**: Pure PHP, no vendor directory

---

## Environment Variables (Railway)

| Variable | Purpose |
|----------|---------|
| `MYSQLHOST` | MySQL host |
| `MYSQLDATABASE` | Database name |
| `MYSQLUSER` | MySQL username |
| `MYSQLPASSWORD` | MySQL password |
| `PORT` | Server port (default: 8080) |
| `RAILWAY_ENVIRONMENT` | Detected for BASE_URL |
| `RAILWAY_PROJECT_ID` | Detected for BASE_URL |
| `RAILWAY_SERVICE_ID` | Detected for BASE_URL |

---

## Important Notes

- `install.php` must be deleted after first run (security)
- `.installed` marker file prevents re-installation
- Admin session timeout: 30 minutes idle
- Brute-force protection: 5 failed attempts → 15-minute lockout
- No API endpoints — all server-rendered PHP pages
- No test suite — no PHPUnit or testing framework configured
- GSAP animations respect `prefers-reduced-motion`
- All CSS/JS uses `?v=3.0` cache-busting

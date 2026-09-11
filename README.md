# CellVerse

A full-stack PHP/MySQL portfolio project for a B2B mobile-accessories catalogue and wholesale-ordering concept. It demonstrates server-rendered PHP, relational data modelling, administration, authentication, security hardening, responsive UI engineering, and containerized deployment.

> **Portfolio/demo note:** Product catalogue entries, customer/testimonial names, contact details, pricing, and other business content in the repository are demonstration data for a portfolio project. They should not be interpreted as claims about a real operating business unless the data is replaced.

## What the project demonstrates

- Responsive product catalogue with search and category filtering
- B2B bulk-order workflow with minimum-order quantities and stock information
- Database-backed administration for products, categories, orders, messages, FAQs, users, and reports
- CSRF protection, prepared SQL statements, hardened sessions, RBAC, and login-rate limiting
- Security headers including CSP, HSTS, X-Frame-Options, and X-Content-Type-Options
- Environment-based production database and integration configuration
- GSAP/ScrollTrigger animation and a custom CSS design-token system
- Three.js hero visual
- Docker/Railway deployment support
- PHPMailer integration for password-reset mail

## Tech stack

- PHP 8.2
- MySQL / MariaDB
- Vanilla JavaScript
- Custom CSS with design tokens and responsive layers
- GSAP / ScrollTrigger
- Three.js
- PHPMailer (vendored under `vendor/phpmailer/`)
- Docker

## Local development

1. Install XAMPP with Apache, PHP, and MySQL.
2. Clone this repository into `htdocs/`.
3. Create a database named `cellverse_db`.
4. Import `database.sql` using phpMyAdmin or the MySQL client.
5. Configure the database through environment variables or the local defaults in `config/database.php`.
6. Open `http://localhost/cellverse/`.

There is intentionally no web-accessible installer in the repository. Database initialization is performed explicitly from `database.sql` so a deployed site does not expose a database-creation endpoint.

## Production configuration

The application can read the following Railway/PaaS variables:

| Variable | Purpose |
|---|---|
| `MYSQLHOST` | MySQL host |
| `MYSQLDATABASE` | Database name |
| `MYSQLUSER` | Database user |
| `MYSQLPASSWORD` | Database password |
| `PORT` | HTTP server port |
| `RECAPTCHA_SITE_KEY` | Production reCAPTCHA site key |
| `RECAPTCHA_SECRET_KEY` | Production reCAPTCHA secret |
| `SMTP_HOST` | SMTP server |
| `SMTP_PORT` | SMTP port |
| `SMTP_USER` | SMTP username |
| `SMTP_PASS` | SMTP password |
| `SMTP_FROM` | Sender address |
| `SMTP_FROM_NAME` | Sender display name |

`.env.example` is a reference template only; the application reads variables from the host environment and does not load the file itself.

Never commit `.env` files, database passwords, SMTP passwords, API credentials, or production reCAPTCHA secrets.

## Admin bootstrap

`database.sql` includes a demonstration admin row with a random bcrypt hash and intentionally does not publish a default administrator password.

For a local demo database, generate a password hash with PHP:

```bash
php -r 'echo password_hash("YOUR_PASSWORD", PASSWORD_DEFAULT), PHP_EOL;'
```

Then update the `admin_users.password_hash` value in the local database before signing in. Do not reuse a shared/default password in a real deployment.

## Repository layout

```text
admin/       Authentication and administration
config/      Database and application initialization
css/         Design system and responsive styles
images/      Site imagery and icons
includes/    Shared public components
js/          Front-end behavior and animation
database.sql Schema and demonstration seed data
vendor/      Vendored PHPMailer source
Dockerfile   Production container configuration
Procfile     Process definition
start.sh     Runtime entrypoint
.htaccess    Apache routing and security headers
```

## Security

- State-changing forms use CSRF protection.
- PDO prepared statements use emulated prepares disabled.
- Sessions use hardened cookie settings and bounded lifetime.
- Login attempts are rate limited in the database.
- Forgot-password requests use rate limiting and single-use reset tokens.
- Role-based access controls protect administrative functions.
- Input is validated server-side and rendered output is escaped.
- Uploaded images are checked server-side by MIME type.
- Runtime uploads and logs are excluded from version control.

## Testing

No automated test suite is currently included. The expected verification workflow is local smoke testing of the public pages, forms, authentication flow, database-backed CRUD, and deployment entrypoint.

## Portfolio context

CellVerse is a portfolio artifact demonstrating full-stack PHP/MySQL development, relational database design, CRUD administration, authentication, application security, responsive UI engineering, and deployment considerations.
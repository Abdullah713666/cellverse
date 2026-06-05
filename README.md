# CellVerse

B2B wholesale mobile accessories catalog (Pakistan). PHP + MySQL.

## Local setup (XAMPP)

1. Drop the folder into `htdocs/`
2. Create MySQL database `cellverse_db`
3. Visit `http://localhost/cellverse/install.php` once (creates tables + seeds data and writes `.installed` marker)
4. Default admin: `admin` / `admin` — change immediately via `/admin/settings.php`

## Railway / production

The app auto-detects Railway's MySQL service via the standard env vars:

| Env var | Source |
|---------|--------|
| `MYSQLHOST` | Railway MySQL plugin |
| `MYSQLDATABASE` | Railway MySQL plugin |
| `MYSQLUSER` | Railway MySQL plugin |
| `MYSQLPASSWORD` | Railway MySQL plugin |
| `PORT` | Railway auto-set |

See `config/database.php` — `getDB()` falls back to `localhost` for dev and reads env vars in production.

### First-run on Railway

1. Provision a MySQL service
2. Import schema: `mysql -h $MYSQLHOST -u $MYSQLUSER -p$MYSQLPASSWORD $MYSQLDATABASE < database.sql`
3. Either delete the `.installed` marker (so `install.php` runs) **or** seed manually
4. Change the default admin password from `/admin/settings.php`

## Security

- All state-changing forms require a CSRF token (`csrf_field()` + `require_csrf_or_die()`)
- PDO with `EMULATE_PREPARES => false` (real prepared statements)
- Session cookies hardened: `httponly`, `samesite=Strict`, 30-minute idle timeout
- CSP, HSTS, X-Frame-Options, X-Content-Type-Options set via `.htaccess`
- Per-field input validation: `clamp_int()`, `validate_phone()`, length caps, regex patterns
- Image upload validated via `finfo->file()` (not client-supplied MIME)

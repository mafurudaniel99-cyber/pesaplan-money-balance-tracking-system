# Security Documentation

PesaPlan follows a defense-in-depth approach. The threats considered are: SQL injection, XSS, CSRF, broken authentication, broken access control (cross-user data access), insecure session management, information disclosure, and CSV formula injection.

## Authentication & Passwords

- **Password hashing**: `password_hash($password, PASSWORD_DEFAULT)` (bcrypt with cost 10). The hash is stored in `users.password_hash` (VARCHAR(255)).
- **Password verification**: `password_verify($input, $hash)` in `Auth::attempt()`.
- **Rate limiting**: after 5 failed attempts (`login_max_attempts` in `config/app.php`), the account is locked for 15 minutes (`locked_until` column). Each failed attempt increments `failed_logins`; a successful login resets it.
- **Session regeneration**: `session_regenerate_id(true)` runs after every successful login and after registration. This prevents session-fixation attacks.
- **Error messages**: authentication errors say "Barua pepe au nenosiri si sahihi" (email or password is incorrect) — they never reveal which one is wrong.
- **Logout**: full session destruction (`Session::destroy()`) including cookie deletion.

## Session Security

Configured in `Session::start()` using `session_set_cookie_params()`:

```php
'lifetime' => 7200,         // 2 hours of inactivity
'secure'   => false,        // set to TRUE in production (HTTPS only)
'httponly' => true,         // JS cannot read the cookie
'samesite' => 'Lax',        // CSRF mitigation
```

Set `session_secure => true` in `config/app.php` once the site is served over HTTPS.

## CSRF Protection

- A per-session token is generated in `Csrf::token()` and stored in `$_SESSION['csrf_token']`.
- Every form includes the hidden field via `csrf_field()`.
- Every POST/PUT/DELETE handler in a controller calls `$this->requireCsrf()` at the top, which verifies the token with `hash_equals()` (timing-safe).
- The token is rotated on login (`Csrf::rotate()`) to prevent token reuse across sessions.
- AJAX requests can send the token via the `X-CSRF-Token` HTTP header (the JS `pesaFetch()` helper does this automatically).

A failing CSRF check returns HTTP 419 with a clear message.

## SQL Injection Prevention

- **100% prepared statements** via MySQLi. The `Database::query()` helper takes a SQL string with `?` placeholders and a params array, then calls `bind_param()` with the correct type string.
- No user input is ever concatenated into SQL.
- The `Database::escape()` helper exists for the rare cases where a value must go into a quoted context (e.g., the `SET time_zone` query) — but it is used only for trusted internal values.

## XSS Prevention

- Every output of user-supplied data goes through the `e()` helper, which wraps `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`.
- The `Validator::sanitize()` helper applies `trim(strip_tags())` on input — but this is **not** the primary defense; output escaping is.
- The application never uses `innerHTML` with user data in JavaScript. Chart data is encoded via `json_encode(..., JSON_UNESCAPED_UNICODE)` and parsed as JSON.

## Access Control (Multi-user Isolation)

This is the most critical security property: a user must never access another user's financial records.

Three layers of defense:

1. **SQL layer**: Every query against a private table includes `AND user_id = ?` in the WHERE clause. The `user_id` comes from the session, never from the request. Example from `Transaction::findForUser`:
   ```php
   SELECT * FROM transactions WHERE id = ? AND user_id = ?
   ```
   Even if an attacker guesses another user's transaction ID, the row will not be returned.

2. **Controller layer**: Every controller action calls `$this->auth->requireAuth()` at the top, which redirects to `/login` if there is no session. Most actions also call `$this->auth->requireOnboarding()` to ensure the user has completed onboarding.

3. **Audit layer**: Every important financial mutation writes to `transaction_audit` and/or `audit_logs` with the acting user's ID and a timestamp. This does not prevent attacks but provides forensic traceability.

### What a user CANNOT do

- Access another user's transactions by modifying a URL ID — the lookup filters by `user_id`.
- Access another user's savings goals, debts, budgets, etc. — same pattern.
- See another user's notifications or reviews.
- Forge a `user_id` in a POST request — the controller always reads `user_id` from the session, never from the form.

## Input Validation

The `Validator` class enforces rules declared per field:

```php
$v = new Validator($_POST, [
    'email'    => 'required|email',
    'password' => 'required|min:8',
    'amount'   => 'required',
]);
if (!$v->validate()) { /* show $v->errors() */ }
```

Monetary amounts are parsed via `Validator::amount()`, which strips thousands separators and rejects non-numeric input. Negative amounts are blocked at the application level (the form inputs have `min="0"` and the validator rejects negatives).

## Output Encoding for CSV (Formula Injection)

CSV exports are vulnerable to formula injection: a malicious user could enter a description like `=HYPERLINK("http://evil.com","click")` and have it execute when the CSV is opened in Excel.

`ReportService::csvCell()` defends against this:
- Cells starting with `=`, `+`, `-`, `@`, tab, or CR are prefixed with a single quote (`'`).
- Embedded double quotes are escaped by doubling (`""`).
- The CSV starts with a UTF-8 BOM (`\xEF\xBB\xBF`) so Excel opens it as UTF-8.

## File Access Protection

- `public/.htaccess` denies direct access to `*.sql`, `*.md`, `*.log`, `*.env`, `*.example` files.
- `storage/` is outside `public/` so logs and exports cannot be accessed directly via URL.
- `config/database.php` is excluded from version control via `.gitignore`.

## Information Disclosure

- Database connection failures print a generic "Hitilafu kwenye seva" message and log the real error to `error_log()` — never to the user.
- PHP error display is OFF in production (`debug => false` in `config/app.php`).
- The `.htaccess` adds security headers: `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`, `X-XSS-Protection: 1; mode=block`.
- Passwords are never logged. Session tokens are never logged. CSRF tokens are never logged.

## Audit Logging

Two audit mechanisms:

1. **`transaction_audit`** — append-only JSON snapshots of every create/update/delete on `transactions`. Allows full reconstruction of the transaction's history.
2. **`audit_logs`** — generic log for non-transaction events: `login`, `logout`, `register`, `password_change`, `settings_update`, `csv_export`, `account_deleted`. Includes the acting user, IP address, and JSON details.

## Deployment Hardening

See [DEPLOYMENT.md](DEPLOYMENT.md) for production configuration, including:
- Dedicated MySQL user with only the privileges PesaPlan needs (no `GRANT`, no `SUPER`).
- HTTPS via Let's Encrypt.
- `session_secure => true` and `debug => false` in `config/app.php`.
- Daily database backups.
- Apache `mod_security` recommended.

## What PesaPlan Does NOT Implement (and Why)

- **SMS / email notifications**: the spec explicitly says "Do not claim that an external notification has been delivered unless the relevant integration exists." So PesaPlan only generates in-app notifications.
- **Bank balance verification**: there is no bank API integration. The dashboard explicitly labels the cash figure as "recorded cash balance" and "projected available cash", never as "verified bank balance".
- **OAuth / social login**: out of scope for the initial version.
- **Two-factor authentication**: not implemented; recommended for a future version if sensitive features are added.

## Security Checklist for Deployment

- [ ] `config/database.php` uses a non-root MySQL user with limited privileges
- [ ] `config/app.php` has `debug => false`
- [ ] `config/app.php` has `session_secure => true`
- [ ] HTTPS is enforced (Apache redirect from HTTP)
- [ ] `storage/` is not web-accessible
- [ ] Backups run daily and are stored off-site
- [ ] PHP `error_log` is rotated
- [ ] `mod_security` is enabled (optional but recommended)

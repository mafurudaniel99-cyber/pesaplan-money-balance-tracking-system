# Architecture

PesaPlan follows a **MVC-with-services** architecture on top of a small custom OOP framework (no third-party frameworks). The design goals are: separation of concerns, testability of financial calculations, and strict data isolation between users.

## Request Lifecycle

```
HTTP request
   ↓
public/index.php          (front controller — autoloader, helpers, App bootstrap)
   ↓
App\Core\App             (session, config, DB init, route loading)
   ↓
App\Core\Router::dispatch(method, uri)   (regex match → handler)
   ↓
Controller (resolved by App::resolve)
   ↓
Controller calls Model(s) and Service(s)
   ↓
Controller renders View with layout
   ↓
HTTP response (HTML / JSON / CSV / redirect)
```

## Layered Responsibilities

### Core (`app/Core/`)
Infrastructure classes with no business logic:

| Class | Responsibility |
|-------|---------------|
| `App` | Bootstrap: session, config, DB init, route loading, dispatching. Mini DI container via `resolve($class)`. |
| `Database` | MySQLi OOP singleton with prepared-statement helpers (`query`, `one`, `scalar`, `transaction`). |
| `Session` | Secure session start with HttpOnly + SameSite cookies; flash data. |
| `Auth` | Login/register/logout; rate limiting; password hashing; `requireAuth()` / `requireOnboarding()` gates. |
| `Csrf` | Per-session token; `field()` for forms, `meta()` for JS, `verify()` for handlers. |
| `Validator` | Declarative rule validator (`required|string|email|min:N|max:N|in:a,b,c`). |
| `Controller` | Base class: `view()`, `viewAuth()`, `json()`, `redirect()`, `back()`, `requireCsrf()`, `input()`/`query()`. |
| `Router` | Regex-based route matcher with `{param}` placeholders. |

### Models (`app/Models/`)
Each model corresponds to one or more database tables. Models are **static classes** (no instantiation) that wrap `Database::query(...)` calls and always include `user_id` in WHERE clauses for private data. They return plain associative arrays (no ORM overhead).

Example pattern:
```php
public static function findForUser(int $id, int $userId): ?array {
    return Database::one(
        "SELECT * FROM transactions WHERE id = ? AND user_id = ?",
        [$id, $userId]
    );
}
```

The `AND user_id = ?` is **mandatory** for every private-data query. This is the primary defense against cross-user data access.

### Services (`app/Services/`)
Services encapsulate business logic that spans multiple models or that must run inside a DB transaction:

| Service | Responsibility |
|---------|---------------|
| `FinancialSummaryService` | The financial brain. Computes salary received, expenses, recorded cash balance, unpaid obligations, projected available cash, savings, budget items with actuals, alerts. All formulas documented in code. |
| `TransactionService` | Wraps multi-step transaction creation (expense, salary receipt, savings contribution, debt payment, refund) inside `Database::transaction(...)`. Ensures e.g. a savings contribution atomically creates a transaction AND updates the goal balance. |
| `BudgetService` | Suggests starter budgets based on 3-month averages; produces item-level warnings. |
| `ReportService` | Builds report datasets and CSV cell escaping (formula-injection safe). |
| `NotificationService` | Generates in-app notifications from current data; de-duplicates per day. |
| `AllocationService` | Validates allocations against received income; enforces no double-counting. |

### Controllers (`app/Controllers/`)
Thin layer that:
1. Verifies auth + CSRF.
2. Validates input.
3. Calls services / models.
4. Renders the appropriate view (or returns JSON for AJAX endpoints).

### Views (`app/Views/`)
Pure PHP templates. Two layouts:
- `layouts/app.php` — sidebar + topbar + content (used for all authenticated pages).
- `layouts/auth.php` — no chrome, used for login/register/landing/onboarding.

Views receive data via `extract($data, EXTR_SKIP)` and have access to `$auth`, `$config`, `$currentUser`, `$csrfToken`, `$flash`, and the `e()` / `money()` / `sw_date()` helpers.

### Helpers (`app/Helpers/helpers.php`)
Global functions used everywhere:
- `e($value)` — htmlspecialchars wrapper
- `money($amount)` — TZS formatting with thousand separators
- `sw_date($date)` — Kiswahili month names
- `percent($num, $den)` — safe percentage
- `csrf_field()` — hidden input for forms
- `flash_messages($flash)` — renders alert HTML

### Routes (`routes/web.php`)
Flat file declaring all routes against `$router` (an `App\Core\Router` instance injected by `App::loadRoutes()`):
```php
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->post('/transactions', [TransactionController::class, 'store']);
```
URL parameters like `{id}` become method arguments.

## Financial Calculation Rules (canonical)

All financial math is centralised in `FinancialSummaryService` so the same formulas are used everywhere. The rules:

| Concept | Formula | Notes |
|---------|---------|-------|
| Recorded cash balance | Σ(inflows) − Σ(outflows) | `savings_in` counts as 0 (money still owned); `savings_out` counts as inflow (cash returns). |
| Budget remaining | planned − actual_expenses_in_category_for_month | Always non-negative cap on `remaining` shown when planned is 0. |
| Budget variance | actual − planned | Positive = overspent. |
| Savings goal remaining | target − current_amount | Adjusted for withdrawals (already subtracted from `current_amount`). |
| Savings progress | current / target × 100 | Only when target > 0. |
| Projected available cash | recorded_cash − unpaid_obligations | Clearly labelled as projected, not bank-verified. |
| Net cash (report) | total_income − total_expense | Distinct from net worth or accounting profit. |

Transfers between accounts (`transfer_in`/`transfer_out`) never count as income or expense. Savings contributions never count as consumption. Debt principal repayment (`debt_repay`) reduces cash but is reported separately from consumption expenses.

## Multi-user Isolation

Three layers of defense:
1. **SQL-level**: every private query includes `WHERE user_id = ?`. There is no path that returns another user's data.
2. **Controller-level**: every action calls `$this->auth->requireAuth()` and most call `$this->auth->requireOnboarding()`.
3. **Audit-level**: financial mutations write to `transaction_audit` and `audit_logs` so cross-user access attempts (if they ever happened) would be traceable.

A user **cannot** access another user's records by:
- Modifying an ID in a URL — the controller looks up with `AND user_id = ?`.
- Modifying a hidden form field — the user_id comes from the session, never from the request.
- Forging a CSRF token — every mutation handler calls `$this->requireCsrf()`.

## Frontend

Vanilla JavaScript, no frameworks. Three script files:
- `app.js` — sidebar, modals, password toggle, toasts, CSRF fetch helper.
- `charts.js` — pure SVG donut + grouped bar charts (no Chart.js, no D3).
- `transactions.js`, `dashboard.js` — page-specific enhancements.

CSS is a single `style.css` (~900 lines) using CSS custom properties (design tokens), Grid, Flexbox, and mobile-first breakpoints at 1024 / 768 / 480 px.

## Autoloading

PSR-4-style autoloader registered in `public/index.php` (no Composer needed):
```php
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = BASE_PATH . '/app/' . $relative . '.php';
    if (file_exists($file)) require $file;
});
```

## Error Handling

In development (`debug => true`), PHP errors are displayed. In production, errors are written to `storage/logs/` and the user sees a generic error message. Database connection failures never expose credentials — the error is logged and a polite "Hitilafu kwenye seva" message is shown.

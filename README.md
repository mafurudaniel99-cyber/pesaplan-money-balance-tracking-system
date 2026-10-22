# PesaPlan — Mfumo wa Nidhamu ya Fedha

**Personal Financial Discipline & Money Management System**

A complete, secure, responsive web application that helps salaried individuals manage their monthly income, control spending, build savings, and develop long-term financial discipline. The interface is fully in **Kiswahili** (Tanzanian context, TZS currency) and the developer documentation is in **English**.

> _"Kila shilingi iwe na kusudi, kila matumizi yawe na kumbukumbu, na kila uamuzi wa fedha uwe na sababu inayoeleweka."_

---

## Features

- **Authentication & onboarding** — secure registration, login, password change, account deletion, first-time onboarding wizard (salary, recurring expenses, debts, savings target).
- **Income management** — distinguish expected salary from received salary; record other income; prevent double-counting.
- **Transaction management** — full CRUD with audit trail, search, filters (date / category / type / payment method), pagination.
- **Categories** — editable default categories (Chakula, Kodi ya nyumba, Usafiri, Umeme na maji, …) plus user-created categories; soft-deactivate without losing history.
- **Budget planning** — per-month budget with per-category planned amounts; live comparison to actuals; variance and percentage tracking.
- **Savings & emergency fund** — multiple named goals; contributions / withdrawals tracked separately from expenses; progress bars.
- **Debts & obligations** — separate tables for debts (with interest, minimum payment, due day) and recurring bills (rent, utilities); payment history with principal/interest split.
- **Money allocation** — logical allocation of received salary into purposes (essential, rent, debt, savings, development, discretionary); enforced against received amounts only.
- **Kagua Kabla ya Kutumia** — pre-spending decision tool with cooling-off period; evaluates proposed purchase against remaining budget, available cash, and obligations.
- **Daily / weekly / monthly reviews** — checklists persisted in DB; mark as complete; review history.
- **Reports** — income, expense, by-category, transaction history, indicators, budget vs actual, monthly summary; CSV export with formula-injection protection; print-friendly layouts.
- **Financial education** — 15 lessons in Kiswahili with examples, common mistakes, recommended actions, exercises.
- **Notifications** — in-app only (no SMS/email claims); generated from real data.
- **Responsive** — mobile-first; tested layouts for small phones, tablets, laptops, desktops.
- **Accessible** — semantic HTML, keyboard navigation, visible focus indicators, reduced-motion support, ARIA labels on icons.

---

## Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | **PHP 8.2+** (OOP, no framework) |
| Database | **MySQL 5.7+ / MariaDB 10.3+** (InnoDB, utf8mb4) |
| DB access | **MySQLi (object-oriented)** with prepared statements |
| Frontend | **HTML5 + custom CSS3 + vanilla JavaScript** (no frameworks) |
| Icons | **Font Awesome 6** (CDN) |
| Typography | **Plus Jakarta Sans** + **Inter** (Google Fonts) |
| Server | **Apache** (XAMPP-compatible) with `mod_rewrite` |

No Laravel, no React/Vue/Angular, no Bootstrap/Tailwind, no PDO — as required by the specification.

---

## Project Structure

```
pesa-plan/
├── app/
│   ├── Core/           — Database, Auth, Session, Csrf, Validator, Controller, Router, App
│   ├── Controllers/    — 15 controllers (Auth, Dashboard, Income, Transaction, Budget, ...)
│   ├── Models/         — 12 models (User, Income, Transaction, Category, Budget, SavingsGoal, Debt, ...)
│   ├── Services/       — FinancialSummaryService, BudgetService, TransactionService, ReportService, NotificationService, AllocationService
│   ├── Views/          — layouts + auth + 13 feature view folders
│   └── Helpers/        — helpers.php (e(), money(), sw_date(), ...)
├── config/             — app.php, database.example.php
├── database/           — schema.sql, seed.sql
├── public/             — index.php (front controller), .htaccess, assets/
├── routes/             — web.php
├── storage/            — logs/, exports/
├── tests/              — manual test plan
├── docs/               — English documentation (INSTALLATION, ARCHITECTURE, DATABASE, SECURITY, USER_GUIDE, TESTING, DEPLOYMENT)
├── .gitignore
└── README.md
```

---

## Quick Start (XAMPP)

1. Copy the `pesa-plan/` folder into `C:\xampp\htdocs\` (Windows) or `/opt/lampp/htdocs/` (Linux).
2. Start Apache and MySQL from the XAMPP control panel.
3. Open `http://localhost/phpmyadmin` and create a new database named `pesaplan` (collation `utf8mb4_unicode_ci`).
4. Import `database/schema.sql` then `database/seed.sql`.
5. Copy `config/database.example.php` → `config/database.php` and adjust credentials (default XAMPP: user `root`, empty password).
6. Visit `http://localhost/pesa-plan/public/` in your browser.
7. Register a new account → complete the onboarding wizard → open the dashboard.

For full step-by-step instructions see [docs/INSTALLATION.md](docs/INSTALLATION.md).

---

## Documentation

All developer documentation is in English under [`docs/`](docs/):

- [INSTALLATION.md](docs/INSTALLATION.md) — XAMPP / Apache setup, database import, configuration
- [ARCHITECTURE.md](docs/ARCHITECTURE.md) — request lifecycle, class responsibilities, separation of concerns
- [DATABASE.md](docs/DATABASE.md) — tables, relationships, indexes, financial rules
- [SECURITY.md](docs/SECURITY.md) — authentication, CSRF, XSS, SQL injection, deployment hardening
- [USER_GUIDE.md](docs/USER_GUIDE.md) — how to register, record expenses, create a budget, manage savings
- [TESTING.md](docs/TESTING.md) — automated tests + manual test scenarios
- [DEPLOYMENT.md](docs/DEPLOYMENT.md) — production configuration, HTTPS, backups, logging

---

## Security Highlights

- Passwords hashed with `password_hash()` (bcrypt).
- CSRF tokens per session, verified on every POST/PUT/DELETE.
- All database queries use prepared statements (MySQLi).
- Output escaped with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` via `e()` helper.
- Multi-user data isolation enforced at the SQL level (every query includes `user_id`).
- Login rate limiting (account lock after 5 failed attempts).
- CSV exports prefixed to prevent spreadsheet formula injection.
- Audit log of important financial changes.
- Sensitive config (`config/database.php`) excluded from version control.

See [docs/SECURITY.md](docs/SECURITY.md) for the full treatment.

---

## Known Limitations

- The application is single-currency (TZS). Multi-currency support is not implemented.
- There is no SMS or email integration; notifications are in-app only.
- Bank balance verification is not implemented — the dashboard shows a _recorded cash balance_ based on transactions, clearly labelled as not bank-verified.
- Money allocations are _logical_ assignments, not actual bank transfers (this is made explicit in the UI).
- No automatic recurring transaction generation (e.g., monthly rent); users must record each transaction or use obligations as reminders.

---

## License

This project is provided as-is for educational and personal use.

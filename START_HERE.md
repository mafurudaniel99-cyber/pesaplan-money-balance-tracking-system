# PesaPlan — Quick Start

> **Full structure**: see [PROJECT_STRUCTURE.md](PROJECT_STRUCTURE.md)
> **Full installation guide**: see [docs/INSTALLATION.md](docs/INSTALLATION.md)

## 3-step setup (XAMPP)

### Step 1 — Copy the project

Copy the entire `pesa-plan/` folder into your XAMPP htdocs:

- **Windows**: `C:\xampp\htdocs\pesa-plan\`
- **Linux**: `/opt/lampp/htdocs/pesa-plan/`
- **macOS**: `/Applications/XAMPP/htdocs/pesa-plan/`

### Step 2 — Create the database

1. Start **Apache** and **MySQL** from the XAMPP control panel.
2. Open `http://localhost/phpmyadmin`.
3. Click **Databases** → **Create database** → name: `pesaplan` → collation: `utf8mb4_unicode_ci` → **Create**.
4. Open the new database, click **Import**, choose `database/schema.sql` → **Go**.
5. Repeat with `database/seed.sql`.

### Step 3 — Configure credentials

Edit `config/database.php` with your MySQL credentials.
For default XAMPP installations, no changes are needed (root user, no password).

### Step 4 — Open the app

Visit `http://localhost/pesa-plan/public/` in your browser.

- Click **Anza bure** to register.
- Complete the onboarding wizard (salary, recurring expenses, debts, savings target).
- Land on your dashboard.

---

## Faster install (CLI)

If you have MySQL CLI access, run the installer script:

- **Linux/macOS**: `./install.sh`
- **Windows**: double-click `install.bat`

The script creates the database, imports the schema and seed, and verifies the table count.

---

## What you get

- **17 default categories** (Chakula, Kodi ya nyumba, Usafiri, Umeme na maji, Mawasiliano, Elimu, Afya, Familia, Madeni, Akiba, Maendeleo binafsi, Starehe, Manunuzi, Dharura, Nyinginezo + Mshahara + Mapato mengine).
- **15 financial education lessons** in Kiswahili with TZS examples.
- **Dashboard** with 6 summary cards, donut chart (expenses by category), bar chart (income vs expense, 6 months), budget progress bars, recent transactions, real-time alerts.
- **Transactions** with full search/filter/pagination, audit trail.
- **Budgets** with per-category planned vs actual vs remaining, color-coded progress bars, warnings.
- **Savings goals** (multiple, including emergency fund), contributions/withdrawals tracked separately from expenses.
- **Debts** with interest calculation, payment history, principal/interest split.
- **Obligations** (recurring bills) with due-date reminders.
- **Money allocations** — logical assignment of received salary into purposes.
- **Kagua Kabla ya Kutumia** — pre-spending decision tool with cooling-off period.
- **Daily/weekly/monthly reviews** with persisted checklists.
- **Reports** (7 types) with CSV export (formula-injection safe) and print layout.
- **Settings** — profile, financial preferences, password change, account deletion.

---

## Tech stack (as required by spec)

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.2+ (OOP, no framework) |
| Database | MySQL 5.7+ / MariaDB 10.3+ (InnoDB) |
| DB access | MySQLi OOP with prepared statements |
| Frontend | HTML5 + custom CSS3 + vanilla JavaScript |
| Icons | Font Awesome 6 (CDN) |
| Fonts | Plus Jakarta Sans + Inter (Google Fonts) |
| Server | Apache with mod_rewrite |

**No Laravel. No React/Vue/Angular. No Bootstrap/Tailwind. No PDO.**

---

## Documentation (English)

| Document | Purpose |
|----------|---------|
| [PROJECT_STRUCTURE.md](PROJECT_STRUCTURE.md) | Folder-by-folder map of the codebase |
| [README.md](README.md) | Project overview, features, security highlights |
| [docs/INSTALLATION.md](docs/INSTALLATION.md) | Detailed XAMPP setup with troubleshooting |
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Request lifecycle, class responsibilities, financial rules |
| [docs/DATABASE.md](docs/DATABASE.md) | All 18 tables, columns, relationships, indexes |
| [docs/SECURITY.md](docs/SECURITY.md) | Auth, CSRF, XSS, SQL injection, multi-user isolation |
| [docs/USER_GUIDE.md](docs/USER_GUIDE.md) | Feature walkthrough in English |
| [docs/TESTING.md](docs/TESTING.md) | Test plan (automated + manual) |
| [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) | Production deployment, HTTPS, backups, hardening |

---

## Troubleshooting

| Symptom | Fix |
|---------|-----|
| Blank page | Set `debug => true` in `config/app.php`, check PHP error log |
| "Faili ya usanidi wa hifadhidata haipo" | Copy `config/database.example.php` → `config/database.php` |
| 404 on every link | Enable `mod_rewrite` in Apache's `httpd.conf` |
| "Access denied for user" | Verify `config/database.php` credentials |
| Buttons do nothing | Open browser console, check JS errors |
| Charts don't render | Ensure `/assets/js/charts.js` is reachable |

---

## License

Provided as-is for educational and personal use.

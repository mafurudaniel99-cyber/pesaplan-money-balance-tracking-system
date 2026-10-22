# PesaPlan — Project Structure

```
pesa-plan/
│
├── app/                                  # Application source code (PSR-4: App\ namespace)
│   │
│   ├── Core/                             # Framework infrastructure (no business logic)
│   │   ├── App.php                       # Bootstrap, mini DI container, dispatcher
│   │   ├── Auth.php                      # Login/register/logout, rate limiting
│   │   ├── Controller.php                # Base controller: view(), json(), redirect(), CSRF
│   │   ├── Csrf.php                      # Per-session CSRF token helper
│   │   ├── Database.php                  # MySQLi OOP singleton + prepared-statement helpers
│   │   ├── Router.php                    # Regex-based route matcher with {param} placeholders
│   │   ├── Session.php                   # Secure session start (HttpOnly + SameSite)
│   │   └── Validator.php                 # Declarative rule validator
│   │
│   ├── Controllers/                      # 15 thin controllers (auth + CRUD + reports)
│   │   ├── AllocationsController.php
│   │   ├── AuthController.php            # register, login, logout, onboarding
│   │   ├── BudgetController.php
│   │   ├── CategoriesController.php
│   │   ├── DashboardController.php
│   │   ├── DebtsController.php
│   │   ├── EducationController.php       # 15 Kiswahili lessons
│   │   ├── IncomeController.php
│   │   ├── NotificationsController.php
│   │   ├── PlannedPurchasesController.php# "Kagua Kabla ya Kutumia"
│   │   ├── ReportsController.php         # 7 report types + CSV export
│   │   ├── ReviewsController.php         # Daily/weekly/monthly checklists
│   │   ├── SavingsController.php
│   │   ├── SettingsController.php
│   │   └── TransactionController.php
│   │
│   ├── Models/                           # 12 static-model classes (always filter by user_id)
│   │   ├── AuditLog.php
│   │   ├── Budget.php
│   │   ├── Category.php
│   │   ├── Debt.php
│   │   ├── FinancialObligation.php
│   │   ├── Income.php
│   │   ├── Notification.php
│   │   ├── PlannedPurchase.php
│   │   ├── Review.php
│   │   ├── SavingsGoal.php
│   │   ├── Transaction.php
│   │   └── User.php
│   │
│   ├── Services/                         # 6 business-logic services
│   │   ├── AllocationService.php         # Validates allocations vs received income
│   │   ├── BudgetService.php             # Suggests starter budgets; item warnings
│   │   ├── FinancialSummaryService.php   # THE financial brain — all formulas
│   │   ├── NotificationService.php       # Generates in-app alerts from real data
│   │   ├── ReportService.php             # Builds reports + CSV formula-injection safe
│   │   └── TransactionService.php        # Multi-step tx ops inside DB transactions
│   │
│   ├── Views/                            # Pure PHP templates (no templating engine)
│   │   ├── layouts/
│   │   │   ├── app.php                   # Sidebar + topbar layout (authenticated)
│   │   │   └── auth.php                  # No-chrome layout (login/register/onboarding)
│   │   ├── auth/
│   │   │   ├── landing.php
│   │   │   ├── login.php
│   │   │   ├── onboarding.php
│   │   │   └── register.php
│   │   ├── dashboard/
│   │   │   └── index.php                 # 6 summary cards + charts + alerts + recent tx
│   │   ├── transactions/
│   │   │   ├── categories.php
│   │   │   ├── create.php
│   │   │   ├── edit.php
│   │   │   ├── index.php
│   │   │   └── show.php
│   │   ├── income/
│   │   │   └── index.php
│   │   ├── budgets/
│   │   │   └── index.php
│   │   ├── savings/
│   │   │   └── index.php
│   │   ├── debts/
│   │   │   └── index.php
│   │   ├── allocations/
│   │   │   └── index.php
│   │   ├── planned_purchases/
│   │   │   └── index.php
│   │   ├── reviews/
│   │   │   └── index.php
│   │   ├── reports/
│   │   │   ├── index.php
│   │   │   └── partials/
│   │   │       ├── budget_actual.php
│   │   │       ├── category.php
│   │   │       ├── expense.php
│   │   │       ├── income.php
│   │   │       ├── indicators.php
│   │   │       ├── summary.php
│   │   │       └── transactions.php
│   │   ├── education/
│   │   │   ├── index.php
│   │   │   └── lesson.php
│   │   ├── notifications/
│   │   │   └── index.php
│   │   └── settings/
│   │       └── index.php
│   │
│   └── Helpers/
│       └── helpers.php                   # Global helpers: e(), money(), sw_date(), csrf_field()
│
├── config/                               # Configuration (database.php is git-ignored)
│   ├── app.php                           # App config: env, debug, session, currency, paths
│   └── database.example.php              # Copy to database.php and edit
│
├── database/                             # SQL schema and seed data
│   ├── schema.sql                        # 18 tables, InnoDB, FKs, indexes, DECIMAL amounts
│   └── seed.sql                          # 17 default categories (Chakula, Kodi, Usafiri...)
│
├── docs/                                 # English developer documentation
│   ├── ARCHITECTURE.md                   # Request lifecycle, class responsibilities
│   ├── DATABASE.md                       # Tables, columns, relationships, indexes
│   ├── DEPLOYMENT.md                     # Production setup, HTTPS, backups, hardening
│   ├── INSTALLATION.md                   # XAMPP step-by-step
│   ├── SECURITY.md                       # Auth, CSRF, XSS, SQL injection, isolation
│   ├── TESTING.md                        # Automated + manual test plan
│   └── USER_GUIDE.md                     # Feature walkthrough (English summary)
│
├── public/                               # Web root — point Apache DocumentRoot here
│   ├── .htaccess                         # Rewrite rules + security headers + file deny
│   ├── index.php                         # Front controller (autoloader, bootstrap, dispatch)
│   └── assets/
│       ├── css/
│       │   └── style.css                 # ~900 lines, design tokens, responsive
│       └── js/
│           ├── app.js                    # Sidebar, modals, toasts, CSRF fetch
│           ├── charts.js                 # Pure SVG donut + bar charts (no libraries)
│           ├── dashboard.js              # Dashboard enhancements
│           └── transactions.js           # Form visibility logic
│
├── routes/
│   └── web.php                           # All 63 routes declared here
│
├── storage/                              # Runtime artifacts (NOT web-accessible)
│   ├── exports/                          # CSV exports (git-ignored except .gitkeep)
│   │   └── .gitkeep
│   └── logs/                             # Application logs (git-ignored except .gitkeep)
│       └── .gitkeep
│
├── tests/
│   ├── ValidatorTest.php                 # PHPUnit tests (Validator + CSV injection)
│   └── manual_test_plan.md               # 80+ checkbox test scenarios
│
├── .gitignore                            # Excludes database.php, storage/*, vendor/
├── README.md                             # Project overview + quick start
├── composer.json                         # PHP package manifest (phpunit dev dep)
└── phpunit.xml                           # PHPUnit configuration
```

## Architecture in one paragraph

`public/index.php` is the front controller. It registers a PSR-4-style autoloader,
loads helpers, boots `App\Core\App` (which starts the session, loads config,
initialises the database, and registers routes from `routes/web.php`), then
dispatches the request. The `Router` matches the URL against a regex pattern
and calls the corresponding controller method. Controllers are thin: they
verify auth + CSRF, validate input, call services/models, and render a view.
All financial calculations live in `FinancialSummaryService` so the same
formulas are used everywhere. Every private SQL query includes `WHERE user_id = ?`
to enforce multi-user isolation.

## Where to put things

| You want to... | Edit this |
|----------------|-----------|
| Add a new page | `routes/web.php` + new controller method + new view file |
| Add a new DB table | `database/schema.sql` + new model in `app/Models/` |
| Change colours / fonts | `public/assets/css/style.css` → `:root` design tokens |
| Change app name | `config/app.php` → `app_name` |
| Add a financial lesson | `app/Controllers/EducationController.php` → `lessons()` |
| Change default categories | `database/seed.sql` |
| Add a new report | `app/Services/ReportService.php` + `app/Controllers/ReportsController.php` + new partial in `app/Views/reports/partials/` |

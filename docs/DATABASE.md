# Database Documentation

## Overview

The PesaPlan database is a normalized relational schema with 17 tables, all using **InnoDB** and **utf8mb4 / utf8mb4_unicode_ci**. Monetary amounts are stored as `DECIMAL(15,2)` — never floating point. Every private row carries a `user_id` foreign key, and every private query filters by it.

## Schema Diagram (textual)

```
users (1) ──── (N) user_settings
users (1) ──── (N) income_records
users (1) ──── (N) transactions ─── (N) transaction_audit
users (1) ──── (N) expense_categories (also has system defaults, user_id NULL)
users (1) ──── (N) budgets ──── (N) budget_items ─── (1) expense_categories
users (1) ──── (N) money_allocations ─── (1) income_records (optional)
users (1) ──── (N) savings_goals ──── (N) savings_contributions ─── (1) transactions (optional)
users (1) ──── (N) debts ──── (N) debt_payments ─── (1) transactions (optional)
users (1) ──── (N) financial_obligations
users (1) ──── (N) planned_purchases
users (1) ──── (N) financial_reviews
users (1) ──── (N) notifications
users (1) ──── (N) audit_logs (also has system-level entries with user_id NULL)
income_records (1) ─── (N) transactions.income_id
```

## Tables

### 1. `users`
Primary account table.
- `id` PK, `email` UNIQUE, `password_hash` (bcrypt), `failed_logins`, `locked_until` (rate limiting), `onboarding_complete` flag.
- Indexes: PK, unique email.

### 2. `user_settings`
1:1 with users. Stores expected salary, salary day, cooling-off hours, notification preferences, budget threshold percentage.

### 3. `expense_categories`
Holds categories for income, expense, savings, and debt (the `type` column).
- `user_id` is **nullable**: NULL means it is a system default shared by all users (the seed inserts 17 defaults).
- `kind` enum (`essential`, `discretionary`, `obligation`, `savings`, `other`) drives the indicators report.
- `is_active` flag enables soft-deactivation without losing historical records.
- Indexes: `user_id`, `type`.

### 4. `income_records`
Distinguishes **expected** salary from **received** salary. A single row may exist as expected (`is_received = 0`) and later be flipped to received (`is_received = 1`, `received_amount`, `received_date` set).
- `period_month` (CHAR(7) `YYYY-MM`) is used for monthly aggregation.
- `is_salary` flag distinguishes the salary from other income.

### 5. `transactions`
The single source of truth for every money movement. Type column controls how each row contributes to calculations:

| type | Cash effect | Income? | Expense? | Savings? |
|------|-------------|---------|----------|----------|
| `salary` | +amount | yes | no | no |
| `other_income` | +amount | yes | no | no |
| `expense` | −amount | no | yes | no |
| `savings_in` | 0 (still owned) | no | no | yes |
| `savings_out` | +amount | no | no | yes (withdrawal) |
| `debt_repay` | −amount | no | no (separate) | no |
| `refund` | +amount | no (recovery) | no | no |
| `transfer_in` | +amount | no | no | no |
| `transfer_out` | −amount | no | no | no |
| `adjustment` | ±amount | no | no | no |

- `is_deleted` for soft delete (audit history preserved).
- Indexes: `(user_id, transaction_date)`, `(user_id, type)`, `category_id`.
- FKs to `users`, `expense_categories`, `income_records`.

### 6. `transaction_audit`
Append-only JSON snapshot of every create/update/delete on `transactions`. Allows reconstructing any past state.

### 7. `budgets`
Per-user, per-month (`period_month` CHAR(7)). Unique on `(user_id, period_month)` so a user can have only one active budget per month.

### 8. `budget_items`
Per-category planned amounts inside a budget. Unique on `(budget_id, category_id)` so inserting the same category twice updates instead of duplicating.

### 9. `money_allocations`
Logical allocation of received income into purposes. Always linked to an `income_records` row (which must have `is_received = 1`). The sum of allocations for one income cannot exceed `received_amount` (enforced in `AllocationService`).

### 10. `savings_goals`
Multiple per user. `is_emergency_fund` flag marks the emergency fund goal. `current_amount` is kept in sync via `savings_contributions`.

### 11. `savings_contributions`
Append-only ledger per goal. Each row optionally links to a `transactions` row (so the underlying transaction can be inspected). Direction `in` or `out`.

### 12. `debts`
Debt principal tracking. `interest_rate_pct` is annual; monthly interest = `outstanding_balance × rate / 12`. `is_paid_off` auto-set when `outstanding_balance` reaches 0.

### 13. `debt_payments`
Each payment links to a transaction and records the split into `interest_portion` and `principal_portion`. Only the principal reduces `outstanding_balance`.

### 14. `financial_obligations`
Distinct from debts — these are recurring bills (rent, utilities) and one-off commitments. `is_paid` flag, optional `due_day` for monthly recurring.

### 15. `planned_purchases`
The "Kagua Kabla ya Kutumia" feature. Status flow: `cooling` → `approved`/`rejected` → `completed`. `cooling_until` enforces the cooling-off window. `decision_result` and `decision_explanation` capture the system's evaluation.

### 16. `financial_reviews`
Daily/weekly/monthly checklists stored as JSON. Unique on `(user_id, review_type, period_label)` so re-saving the same period updates the checklist.

### 17. `notifications`
In-app only. `is_read` flag, optional `action_url`. Generated by `NotificationService` from current data.

### 18. `audit_logs`
Generic audit log for non-transaction changes (login, settings change, account deletion, CSV export). `user_id` nullable for system-level entries. `details` JSON.

## Indexes

| Table | Indexes |
|-------|---------|
| users | PK, UNIQUE(email) |
| expense_categories | PK, (user_id), (type) |
| income_records | PK, (user_id), (user_id, period_month) |
| transactions | PK, (user_id, transaction_date), (user_id, type), (category_id) |
| transaction_audit | PK, (transaction_id), (user_id) |
| budgets | PK, UNIQUE(user_id, period_month) |
| budget_items | PK, UNIQUE(budget_id, category_id), (category_id) |
| money_allocations | PK, (user_id), (income_id) |
| savings_goals | PK, (user_id) |
| savings_contributions | PK, (goal_id) |
| debts | PK, (user_id) |
| debt_payments | PK, (debt_id) |
| financial_obligations | PK, (user_id) |
| planned_purchases | PK, (user_id), (status) |
| financial_reviews | PK, UNIQUE(user_id, review_type, period_label) |
| notifications | PK, (user_id, is_read) |
| audit_logs | PK, (user_id), (action) |

## Foreign Keys

All foreign keys use `ON DELETE CASCADE ON UPDATE CASCADE` for user-owned data (deleting a user deletes all their data). For optional links (e.g., `transactions.category_id`), the FK uses `ON DELETE SET NULL` so deleting a category does not lose the transaction.

## Monetary Storage

- All amounts use `DECIMAL(15,2)`.
- Max representable value: 999,999,999,999,999.99 TZS — more than enough for personal finance.
- Currency is implicit (TZS); no separate currency column on each row.
- In PHP, amounts are cast to `float` only at the boundary for display. All SQL aggregation uses `SUM()` on DECIMAL columns (no precision loss).

## Timezone

All `DATETIME` columns use the server timezone (`+03:00` East Africa). The `Database` connection issues `SET time_zone = '+03:00'` on connect. `created_at` and `updated_at` default to `CURRENT_TIMESTAMP`.

## Multi-user Isolation

Every private table has a `user_id` column with a foreign key to `users`. Every query in the model layer includes `WHERE user_id = ?` in the WHERE clause. There is no administrator role that can read other users' data. A user cannot access another user's records by modifying a URL ID — the lookup query filters by `user_id`.

## Backup

```bash
mysqldump -u root -p --single-transaction --routines pesaplan > pesaplan_backup.sql
```

Restore:
```bash
mysql -u root -p pesaplan < pesaplan_backup.sql
```

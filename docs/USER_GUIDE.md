# User Guide (English Summary)

> The PesaPlan application interface is entirely in **Kiswahili**. This guide explains each feature in English for developers and reviewers.

## 1. Register and Onboard

1. Visit the application URL (e.g., `http://localhost/pesa-plan/public/`).
2. Click **Anza bure** (Start free) → fill in name, email, password (8+ characters) → **Jisajili**.
3. The onboarding wizard appears. Fill in:
   - Expected monthly salary (TZS) and the day you usually receive it (1–28).
   - (Optional) Recurring essential expenses — one per line, format `Name | amount`.
   - (Optional) Outstanding debts — format `Name | balance | minimum payment`.
   - (Optional) Starter savings target.
4. Click **Maliza usanidi na anza** to land on the dashboard.

You can change any of these later in **Mipangilio** (Settings).

## 2. Dashboard — Muhtasari wa Fedha

The dashboard answers "where does my money stand right now?":

- **Six summary cards**: salary received this month, actual expenses, remaining budget, total savings, unpaid obligations, and projected available cash.
- **Alerts**: budget categories near limit, upcoming obligations, no recent transactions, etc. Alerts are never invented — they always come from real data.
- **Donut chart**: expense distribution by category for the current month.
- **Bar chart**: monthly income vs expense for the last 6 months.
- **Budget progress**: top 6 budget categories with progress bars.
- **Recent transactions**: latest 6 transactions with category icons and amounts.

## 3. Record a Transaction — Miamala

1. Click **Muamala** in the top bar (or sidebar → Miamala → Ongeza muamala).
2. Choose the type: **Matumizi** (expense), **Mapato mengine** (other income), or **Rudisha pesa** (refund).
3. Enter amount, date, description, category, payment method, optional notes.
4. Click **Hifadhi muamala**.

The transaction appears immediately in the dashboard's recent list and in the transactions table. Every mutation is recorded in `transaction_audit`.

### Search & filter

The Miamala page supports full-text search, date range, category filter, type filter, payment method filter, and sorting by date or amount. Pagination is at 20 per page.

## 4. Income Management — Mapato

1. Sidebar → **Mapato** → **Ongeza mapato**.
2. Enter source (e.g., "Mshahara"), expected amount, expected date. Check "Hii ni mshahara wangu" if it is your monthly salary.
3. Save. The income appears as **Inasubiri** (pending).
4. When the salary actually arrives, click the green check icon → enter received amount and date → confirm. The system creates a `salary` transaction automatically.

**Important**: Expected salary is **never** treated as available cash until you mark it received.

## 5. Budget — Bajeti

1. Sidebar → **Bajeti**.
2. If no budget exists for the month, click **Weka bajeti ya mwezi** → enter total planned income → save.
3. Click **Ongeza kategoria** → pick a category, planned amount, priority (1–10), and whether it is essential → save.
4. The table shows planned vs actual vs remaining vs percentage, with color-coded progress bars (green/amber/red).
5. The system also shows **suggestions** based on your 3-month averages — these are optional and editable.

Warnings appear when:
- A category exceeds its planned amount (red).
- A category reaches 80% of its planned amount (amber).
- Total planned expenses exceed salary received (red banner).

## 6. Savings — Akiba

1. Sidebar → **Akiba** → **Lengo jipya**.
2. Enter name (e.g., "Akiba ya dharura"), target amount, optional target date, priority, and check "Hii ni akiba ya dharura" if applicable.
3. Each goal shows current amount, target, remaining, and percentage progress.
4. To add money, click the **+** icon → enter amount, date, method, description. The system creates a `savings_in` transaction (which is **not** counted as an expense — your money is still yours, just reserved).
5. To withdraw, click the **arrow-up** icon → enter amount (cannot exceed current balance).

## 7. Debts & Obligations — Madeni & Majukumu

### Debts
1. Sidebar → **Madeni & Majukumu** → **Deni jipya**.
2. Enter name, outstanding balance, original amount, minimum payment, interest rate (annual %), due day, next due date.
3. To make a payment, click **Lipa** on the debt card → enter amount, date, method, optional interest portion. The system splits the payment into interest + principal, updates the debt balance, and creates a `debt_repay` transaction.

### Obligations (bills)
1. Click **Mjukumu mnyan** → enter name (e.g., "Kodi ya nyumba"), amount, due day, next due date, check "Inarudia kila mwezi" if recurring.
2. When paid, click the green check to mark it paid.

## 8. Money Allocation — Ugawaji wa Fedha

This is a **logical** allocation (not a bank transfer). Use it to plan how your received salary will be split:

1. Sidebar → **Ugawaji wa Fedha** → **Ugawaji mpya**.
2. Pick a received income (only received income appears in the dropdown), choose a purpose (essential, rent, debt, emergency savings, etc.), enter amount.
3. The system prevents allocating more than the income's received amount.

The dashboard shows totals by purpose so you can see your overall split.

## 9. Pre-Spending Decision — Kagua Kabla ya Kutumia

Before making a discretionary purchase, run it through this tool:

1. Sidebar → **Kagua Kabla ya Kutumia** → **Ongeza ununuzi wa kufikiri**.
2. Enter item name, proposed cost, reason, classification (essential/useful/discretionary), proposed date.
3. The system enters a **cooling** state for the configured cooling-off period (default 24 hours, configurable in Settings).
4. Click **Idhinisha** to approve. The system evaluates the purchase against:
   - Remaining category budget.
   - Available cash after recorded obligations.
   - Whether the cost exceeds recorded cash.
5. The decision result and explanation are stored on the purchase card.

This feature does **not** block purchases — it gives you information to decide.

## 10. Reviews — Ukaguzi

Three checklists:

- **Daily** (~5 min): record today's income/expenses, review transactions, check remaining budget, identify unplanned spending.
- **Weekly** (~15–20 min): compare planned vs actual, identify top categories, review unplanned purchases, check upcoming bills.
- **Monthly** (~30–60 min): confirm salary received, review totals, check budget variances, review savings, identify patterns, create next month's budget.

Check off items as you complete them. Add notes. Mark the whole review as complete. Past reviews appear in the history table.

## 11. Reports — Ripoti

1. Sidebar → **Ripoti**.
2. Choose date range and report type:
   - **Muhtasari wa mwezi** — monthly summary with indicators.
   - **Mapato ya mwezi** — monthly income breakdown.
   - **Matumizi ya mwezi** — monthly expense totals.
   - **Matumizi kwa kategoria** — category breakdown.
   - **Historia ya miamala** — full transaction list.
   - **Vielelezo vya fedha** — financial indicators.
   - **Bajeti vs Halisi** — budget vs actual for one month.
3. Click **Pakua CSV** to download as CSV (safe against spreadsheet formula injection).
4. Click **Chapisha** to print (uses a print-friendly stylesheet).

## 12. Education — Jifunze Kusimamia Fedha

15 lessons in Kiswahili:

1. What financial discipline means
2. Income vs expenses vs savings vs available cash
3. How to prepare a monthly budget
4. Why every transaction should be recorded
5. Having money vs being able to afford a purchase
6. Separating money by purpose
7. Essential vs discretionary expenses
8. Budget variances
9. Building an emergency fund
10. Debt and repayment obligations
11. Opportunity cost
12. Daily/weekly/monthly reviews
13. Recognizing repeated small expenses
14. Planning for irregular and annual expenses
15. Correcting financial mistakes without abandoning the budget

Each lesson has: explanation, TZS example, common mistake, recommended action, exercise.

## 13. Settings — Mipangilio

- Update name and phone.
- Change expected salary, salary day, cooling-off hours, budget threshold percentage.
- Toggle notification types.
- Change password (requires current password).
- Delete account (type "FUTA" to confirm — irreversibly deletes all data).

## 14. Categories — Kategoria

- The system ships with 17 default categories (Chakula, Kodi ya nyumba, Usafiri, …).
- Create custom categories with name, type, icon (Font Awesome class), color, and kind.
- Deactivate categories without losing historical transactions (the transactions keep their category_id; the category just stops appearing in dropdowns).

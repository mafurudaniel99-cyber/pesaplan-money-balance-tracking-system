# PesaPlan — Manual Test Plan

This file is a checklist for manually verifying the application before each release.
For the full testing methodology, see [../docs/TESTING.md](../docs/TESTING.md).

## Authentication
- [ ] Register new account → success, redirected to onboarding
- [ ] Register with existing email → error
- [ ] Register with short password (<8) → error
- [ ] Login with valid credentials → success
- [ ] Login with wrong password → error (generic)
- [ ] 5 failed login attempts → account locked 15 min
- [ ] Logout → session destroyed
- [ ] Access /dashboard without login → redirected to /login
- [ ] POST without CSRF → HTTP 419
- [ ] Change password with wrong current → error
- [ ] Change password correctly → can login with new password

## Onboarding
- [ ] New user lands on onboarding after register
- [ ] Salary and day saved correctly
- [ ] Recurring expenses parsed (one per line, `Name | amount`)
- [ ] Debts parsed
- [ ] Savings target creates a goal
- [ ] Income record created for current month
- [ ] Onboarding marked complete → dashboard accessible

## Transactions
- [ ] Create expense → appears in list, reduces cash
- [ ] Create other income → appears in list, increases cash
- [ ] Create refund → increases cash, NOT counted as income
- [ ] Filter by date range, category, type → only matching rows shown
- [ ] Search by description → matches
- [ ] Pagination works (need >20 transactions)
- [ ] Edit transaction → audit trail entry written
- [ ] Delete transaction → soft-deleted, not in list
- [ ] Access another user's transaction by URL ID → 404 / not found

## Budget
- [ ] Create budget for current month → success
- [ ] Add category to budget → appears in table
- [ ] Spend on that category → actual updates, percent updates, color changes
- [ ] Exceed budget → red bar, alert on dashboard
- [ ] Reach 80% → amber bar, warning alert
- [ ] Budget exceeds salary → red banner
- [ ] Suggestion panel shows past averages

## Savings
- [ ] Create goal → appears with 0% progress
- [ ] Contribute → balance increases, transaction of type savings_in created
- [ ] Contribute is NOT counted as expense
- [ ] Withdraw → balance decreases, transaction of type savings_out created
- [ ] Withdraw > balance → rejected
- [ ] Delete goal → removed

## Debts & Obligations
- [ ] Add debt → appears with 0% paid
- [ ] Pay debt → balance decreases, debt_repay transaction created
- [ ] Pay debt is NOT counted as expense
- [ ] Interest portion calculated if rate > 0
- [ ] Mark obligation paid → "Majukumu yasiyolipwa" decreases
- [ ] Delete debt/obligation → removed

## Allocations
- [ ] Cannot allocate against unreceived income → not in dropdown
- [ ] Allocate > received amount → error
- [ ] Allocation appears in list
- [ ] Totals by purpose aggregate correctly
- [ ] Delete allocation → removed

## Kagua Kabla ya Kutumia
- [ ] Add planned purchase → status "cooling"
- [ ] Approve → decision_result and explanation stored
- [ ] Reject → status "rejected"
- [ ] Complete → status "completed"
- [ ] Decision logic correct:
  - Cost > cash → "Fedha hazitoshi"
  - Cost > projected → "Kinakabiliwa na majukumu"
  - Cost > category remaining → "Inahitaji marekebisho ya bajeti"
  - Otherwise → "Ndani ya bajeti"

## Reviews
- [ ] Daily checklist saves
- [ ] Weekly checklist saves
- [ ] Monthly checklist saves
- [ ] Mark complete → badge shows
- [ ] Past reviews appear in history

## Reports
- [ ] Monthly summary shows correct totals
- [ ] Income report per month
- [ ] Expense report per month
- [ ] Category breakdown
- [ ] Transaction history with all fields
- [ ] Indicators (essential %, discretionary %)
- [ ] Budget vs actual for one month
- [ ] CSV export downloads
- [ ] CSV opens in Excel without formula execution
- [ ] Print-friendly layout (no sidebar/topbar in print)

## Education
- [ ] All 15 lessons appear on index
- [ ] Click lesson → detail page
- [ ] Prev/Next navigation works
- [ ] Each lesson has explanation, example, mistake, action, exercise

## Notifications
- [ ] Upcoming obligation within 3 days → notification created
- [ ] No transactions for 3+ days → notification created
- [ ] Mark read → badge count decreases
- [ ] Mark all read → all become read

## Settings
- [ ] Update profile → name saved
- [ ] Update financial settings → saved
- [ ] Change password → success
- [ ] Delete account with "FUTA" → account and all data gone
- [ ] Delete account with wrong word → rejected

## Categories
- [ ] Default categories visible
- [ ] Create custom category → appears
- [ ] Toggle active → category disappears from dropdowns
- [ ] Transactions with deactivated category still show category name

## Security
- [ ] SQL injection attempt in description → stored as text
- [ ] XSS attempt in description → escaped on output
- [ ] CSV injection attempt → prefixed with '
- [ ] Direct access to /config/database.php → 403
- [ ] Direct access to /storage/logs/ → 404
- [ ] Cross-user access by URL ID → not found

## Responsive
- [ ] 360px mobile: sidebar drawer, single column, scrollable tables
- [ ] 768px tablet: 2-column cards
- [ ] 1440px desktop: full layout

## Accessibility
- [ ] Keyboard navigation through all interactive elements
- [ ] Visible focus indicators
- [ ] Screen reader announces icons with aria-label
- [ ] Reduced motion preference respected (animations off)

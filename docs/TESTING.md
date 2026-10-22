# Testing Documentation

This document describes the manual test plan for PesaPlan. Automated tests are limited to a small PHPUnit-compatible suite (see `tests/`); the bulk of testing is manual because the application's correctness depends on financial calculations that must be verified by inspection.

## 1. Authentication Tests

| # | Scenario | Steps | Expected |
|---|----------|-------|----------|
| A1 | Register | Fill name, email, password (8+), confirm → submit | Account created, redirected to onboarding |
| A2 | Register with short password | Password "abc" | Error "Nenosiri linapaswa kuwa na herufi 8 au zaidi" |
| A3 | Register with duplicate email | Use an email that already exists | Error "Barua pepe tayari inatumika" |
| A4 | Login with valid credentials | After registering | Redirected to dashboard (or onboarding if not complete) |
| A5 | Login with wrong password | Correct email, wrong password | Error "Barua pepe au nenosiri si sahihi" |
| A6 | Login rate limiting | 5 failed attempts | Account locked for 15 minutes |
| A7 | Logout | Click "Toka" | Session destroyed, redirected to /login |
| A8 | Unauthorized access | Visit /dashboard without login | Redirected to /login |
| A9 | CSRF on logout | Submit logout form without CSRF token | HTTP 419 |
| A10 | Password change | Enter current + new + confirm | Success message, can log in with new password |
| A11 | Password change wrong current | Enter wrong current password | Error "Nenosiri la sasa si sahihi" |

## 2. Financial Calculation Tests

| # | Scenario | Setup | Expected |
|---|----------|-------|----------|
| F1 | Salary recorded once | Mark salary as received TZS 500,000 | Dashboard "Mshahara uliopokelewa" = 500,000 |
| F2 | Actual expenses | Record 3 expenses totaling TZS 150,000 | Dashboard "Matumizi halisi" = 150,000 |
| F3 | Budget limit | Set budget category "Chakula" = 100,000, spend 80,000 | Remaining = 20,000, percent = 80% (amber) |
| F4 | Overspending | Spend 120,000 on a 100,000 budget | Remaining = -20,000 (red), percent = 120% |
| F5 | Savings contribution | Contribute TZS 50,000 to a goal | Goal balance +50,000; expense total unchanged (savings_in is NOT an expense) |
| F6 | Savings withdrawal | Withdraw TZS 20,000 from goal | Goal balance -20,000; cash balance +20,000 (savings_out is an inflow) |
| F7 | Refund | Record a refund of TZS 10,000 | Cash balance +10,000; income total unchanged (refund is NOT income) |
| F8 | Debt payment | Pay TZS 30,000 on a debt, interest 5,000 | Outstanding balance -25,000; cash balance -30,000; expense total unchanged (debt_repay is NOT consumption expense) |
| F9 | Unpaid obligation | Add obligation TZS 40,000, unpaid | "Majukumu yasiyolipwa" = 40,000; "Fedha zinazopatikana" = cash - 40,000 |
| F10 | Pay obligation | Mark obligation as paid | "Majukumu yasiyolipwa" returns to 0 |
| F11 | Zero income | New user, no transactions | Dashboard shows 0 / empty states, no errors |
| F12 | Recorded cash balance | (Inflows 600,000) − (Outflows 350,000) | Cash = 250,000 |
| F13 | Projected available cash | Cash 250,000 − obligations 40,000 | Projected = 210,000 |
| F14 | Cross-month aggregation | Record transactions in March and April | Monthly report shows correct per-month totals |

## 3. Security Tests

| # | Scenario | Steps | Expected |
|---|----------|-------|----------|
| S1 | SQL injection in description | Enter `' OR '1'='1` in description | Saved as literal text; no data leak |
| S2 | XSS in description | Enter `<script>alert(1)</script>` | Displayed as literal text (escaped) |
| S3 | XSS in notes | Same as S2 in notes field | Displayed as literal text |
| S4 | CSRF on transaction create | Submit form without CSRF token | HTTP 419 |
| S5 | Cross-user access by URL ID | User A logs in, visits /transactions/{B's tx id} | 404 / "Muamala haupatikani" (the query filters by user_id) |
| S6 | Cross-user access by form | User A posts to /transactions/{B's tx id}/delete | No effect (query filters by user_id) |
| S7 | Negative amount | Submit amount = -100 | Rejected by validator (min="1" + server-side check) |
| S8 | Duplicate submission | Double-click submit | Two records may be created (mitigation: disable button on click — implemented in some forms) |
| S9 | CSV formula injection | Enter `=HYPERLINK("http://evil","x")` as description, export CSV | CSV cell prefixed with `'`, Excel does not execute formula |
| S10 | Direct file access | Visit /config/database.php | 403 (denied by .htaccess) |
| S11 | Direct log access | Visit /storage/logs/error.log | 404 (storage is outside public/) |

## 4. Interface Tests

### Mobile (small Android phone, 360 × 640)
- [ ] Sidebar hidden, hamburger icon visible, opens drawer on tap
- [ ] All cards stack to single column
- [ ] Tables scroll horizontally without breaking layout
- [ ] Forms are usable, inputs are large enough to tap
- [ ] Modal dialogs fit the screen, close button reachable

### Tablet (768 × 1024)
- [ ] Sidebar visible by default
- [ ] Cards in 2-column grid
- [ ] Charts side by side

### Desktop (1440 × 900)
- [ ] Full sidebar with all nav items visible
- [ ] Cards in 3–4 column grid
- [ ] Charts in 2-column layout

### Empty states
- [ ] Dashboard with no transactions shows empty state with CTA
- [ ] Transactions page with no records shows empty state
- [ ] Donut chart not rendered when no expenses (text empty state instead)
- [ ] Budget page with no budget shows empty state

### Form validation
- [ ] Required fields show error if empty
- [ ] Email format validated
- [ ] Password length validated
- [ ] Amount must be > 0
- [ ] Date format validated

### Long content
- [ ] Long transaction description (255 chars) wraps in table
- [ ] Long category name wraps in chips

### Large values
- [ ] TZS 999,999,999 displays correctly with thousand separators
- [ ] DECIMAL(15,2) handles large sums without overflow

## 5. Automated Tests

A small PHPUnit-compatible test file is in `tests/`. To run (requires PHPUnit installed separately):

```bash
cd pesa-plan
./vendor/bin/phpunit tests/
```

The automated tests cover:
- `Validator::amount()` — parsing and rejecting invalid input
- `ReportService::csvCell()` — formula injection prevention
- `Validator::validate()` — required, email, min, max rules

Full automated coverage of financial calculations requires a test database fixture; this is documented as a known limitation.

## 6. Manual Test Procedure

For each release, run all tests in sections 1–4 above. Sign off each row with initials and date. Any failed test must be fixed before release.

## 7. Browser Compatibility

Tested on:
- Chrome 120+
- Firefox 120+
- Edge 120+
- Safari 17+
- Samsung Internet (Android)

IE 11 is not supported.

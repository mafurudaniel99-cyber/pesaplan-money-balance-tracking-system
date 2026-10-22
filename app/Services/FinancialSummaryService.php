<?php
declare(strict_types=1);
namespace App\Services;

use App\Core\Database;
use App\Models\{Income, Transaction, Budget, SavingsGoal, Debt, FinancialObligation};

/**
 * Central financial summary service.
 *
 * CALCULATION RULES (documented):
 *
 *  - Recorded cash balance = opening (0) + cash inflows - cash outflows.
 *    Savings contributions are NOT counted as consumption (money is still owned).
 *  - Budget remaining = planned amount - qualifying actual expenses in that
 *    category for the budget month.
 *  - Budget variance = actual expenses - planned expenses.
 *  - Savings goal remaining = target - current_amount (adjusted for withdrawals).
 *  - Savings progress = current / target (when target > 0).
 *  - Available cash after obligations = recorded cash balance - unpaid
 *    obligations. Obligations are NOT subtracted from the actual cash balance;
 *    they are shown separately and projected availability is computed.
 *  - Transfers between accounts never count as income or expense.
 */
final class FinancialSummaryService
{
    private int $userId;
    private string $month;

    public function __construct(int $userId, ?string $month = null)
    {
        $this->userId = $userId;
        $this->month = $month ?? date('Y-m');
    }

    public function month(): string { return $this->month; }

    /** Total actual salary received in the month. */
    public function salaryReceived(): float
    {
        return Income::receivedSalaryForMonth($this->userId, $this->month);
    }

    /** Total other income received in the month. */
    public function otherIncomeReceived(): float
    {
        return Income::receivedOtherIncomeForMonth($this->userId, $this->month);
    }

    public function totalIncomeReceived(): float
    {
        return $this->salaryReceived() + $this->otherIncomeReceived();
    }

    public function salaryExpected(): float
    {
        return Income::expectedSalaryForMonth($this->userId, $this->month);
    }

    public function totalExpenses(): float
    {
        return Transaction::totalExpensesForMonth($this->userId, $this->month);
    }

    /** Recorded cash balance (not bank-verified). */
    public function recordedCashBalance(): float
    {
        return Transaction::recordedCashBalance($this->userId);
    }

    /** Sum of unpaid obligations (bills, rent) — NOT debt principal. */
    public function unpaidObligations(): float
    {
        return FinancialObligation::totalUnpaid($this->userId);
    }

    /**
     * Projected available cash = recorded cash - unpaid obligations.
     * Clearly labelled as projected, not bank-verified.
     */
    public function projectedAvailableCash(): float
    {
        return $this->recordedCashBalance() - $this->unpaidObligations();
    }

    public function totalSavings(): float
    {
        return SavingsGoal::totalSaved($this->userId);
    }

    public function outstandingDebt(): float
    {
        return Debt::totalOutstanding($this->userId);
    }

    public function budget(): ?array
    {
        return Budget::findForUserMonth($this->userId, $this->month);
    }

    public function budgetItemsWithActuals(): array
    {
        $budget = $this->budget();
        if (!$budget) return [];
        $items = Budget::itemsForUser((int) $budget['id'], $this->userId);
        foreach ($items as &$it) {
            $actual = Transaction::expensesForCategoryInMonth(
                $this->userId,
                (int) $it['category_id'],
                $this->month
            );
            $it['actual_amount'] = $actual;
            $it['remaining'] = (float) $it['planned_amount'] - $actual;
            $it['variance'] = $actual - (float) $it['planned_amount'];
            $it['percent'] = $it['planned_amount'] > 0
                ? ($actual / (float) $it['planned_amount']) * 100.0
                : 0.0;
            $it['over_budget'] = $actual > (float) $it['planned_amount'];
        }
        unset($it);
        return $items;
    }

    public function budgetTotals(): array
    {
        $items = $this->budgetItemsWithActuals();
        $planned = 0.0; $actual = 0.0;
        foreach ($items as $it) {
            $planned += (float) $it['planned_amount'];
            $actual  += (float) $it['actual_amount'];
        }
        return [
            'planned' => $planned,
            'actual'  => $actual,
            'remaining' => $planned - $actual,
            'variance'  => $actual - $planned,
            'percent'   => $planned > 0 ? ($actual / $planned) * 100.0 : 0.0,
        ];
    }

    public function expenseBreakdownByCategory(): array
    {
        return Transaction::expensesByCategoryForMonth($this->userId, $this->month);
    }

    public function monthlyTrend(int $months = 6): array
    {
        return Transaction::monthlyIncomeExpenseTrend($this->userId, $months);
    }

    public function recentTransactions(int $limit = 8): array
    {
        return Transaction::recentForUser($this->userId, $limit);
    }

    /**
     * Alerts the dashboard should show. Never invented.
     */
    public function alerts(): array
    {
        $alerts = [];
        $items = $this->budgetItemsWithActuals();
        foreach ($items as $it) {
            if ($it['over_budget']) {
                $alerts[] = [
                    'severity' => 'danger',
                    'icon' => 'fa-triangle-exclamation',
                    'title' => 'Bajeti imezidi: ' . $it['category_name'],
                    'body' => 'Umetumia ' . money($it['actual_amount']) . ' kati ya ' . money($it['planned_amount']) . ' kilichopangwa.',
                    'url' => '/budgets',
                ];
            } elseif ($it['percent'] >= 80) {
                $alerts[] = [
                    'severity' => 'warning',
                    'icon' => 'fa-gauge-high',
                    'title' => 'Karibu kufikia kikomo: ' . $it['category_name'],
                    'body' => 'Umetumia ' . round($it['percent']) . '% ya bajeti ya mwezi huu.',
                    'url' => '/budgets',
                ];
            }
        }
        // Upcoming obligations within 7 days
        $upcoming = Database::query(
            "SELECT * FROM financial_obligations
             WHERE user_id = ? AND is_paid = 0
               AND next_due_date IS NOT NULL
               AND next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
             ORDER BY next_due_date ASC",
            [$this->userId]
        ) ?: [];
        foreach ($upcoming as $u) {
            $alerts[] = [
                'severity' => 'warning',
                'icon' => 'fa-calendar-day',
                'title' => 'Mjukumu unakaribia: ' . $u['name'],
                'body' => 'Lipa ' . money($u['amount']) . ' ifikapo ' . sw_date($u['next_due_date']) . '.',
                'url' => '/debts',
            ];
        }
        // No recent transactions
        $days = Transaction::daysSinceLast($this->userId);
        if ($days === null) {
            $alerts[] = [
                'severity' => 'info',
                'icon' => 'fa-circle-info',
                'title' => 'Hujaweka miamala yoyote bado',
                'body' => 'Anza kwa kuandika matumizi ya leo ili kuona picha kamili ya fedha zako.',
                'url' => '/transactions/create',
            ];
        } elseif ($days >= 3) {
            $alerts[] = [
                'severity' => 'info',
                'icon' => 'fa-clock',
                'title' => 'Siku ' . $days . ' bila kuandika muamala',
                'body' => 'Kumbuka kuandika miamala yako ili kuwa na rekodi sahihi.',
                'url' => '/transactions/create',
            ];
        }
        return $alerts;
    }
}

<?php
declare(strict_types=1);
namespace App\Services;

use App\Core\Database;
use App\Models\Transaction;
use App\Services\FinancialSummaryService;

/**
 * Reporting service — produces aggregates and CSV exports.
 */
final class ReportService
{
    private int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function monthlyIncomeReport(string $from, string $to): array
    {
        return Database::query(
            "SELECT DATE_FORMAT(t.transaction_date, '%Y-%m') AS month,
                    SUM(CASE WHEN t.type = 'salary' THEN t.amount ELSE 0 END) AS salary,
                    SUM(CASE WHEN t.type = 'other_income' THEN t.amount ELSE 0 END) AS other,
                    SUM(CASE WHEN t.type IN ('salary','other_income') THEN t.amount ELSE 0 END) AS total
             FROM transactions t
             WHERE t.user_id = ? AND t.is_deleted = 0
               AND t.transaction_date BETWEEN ? AND ?
             GROUP BY month
             ORDER BY month ASC",
            [$this->userId, $from, $to]
        ) ?: [];
    }

    public function monthlyExpenseReport(string $from, string $to): array
    {
        return Database::query(
            "SELECT DATE_FORMAT(t.transaction_date, '%Y-%m') AS month,
                    SUM(t.amount) AS total
             FROM transactions t
             WHERE t.user_id = ? AND t.type = 'expense' AND t.is_deleted = 0
               AND t.transaction_date BETWEEN ? AND ?
             GROUP BY month
             ORDER BY month ASC",
            [$this->userId, $from, $to]
        ) ?: [];
    }

    public function expenseByCategoryReport(string $from, string $to): array
    {
        return Database::query(
            "SELECT c.id, c.name, c.icon, c.color, c.kind,
                    COUNT(t.id) AS tx_count,
                    SUM(t.amount) AS total
             FROM transactions t
             JOIN expense_categories c ON c.id = t.category_id
             WHERE t.user_id = ? AND t.type = 'expense' AND t.is_deleted = 0
               AND t.transaction_date BETWEEN ? AND ?
             GROUP BY c.id, c.name, c.icon, c.color, c.kind
             ORDER BY total DESC",
            [$this->userId, $from, $to]
        ) ?: [];
    }

    public function budgetVsActualReport(string $month): array
    {
        $svc = new FinancialSummaryService($this->userId, $month);
        return [
            'totals' => $svc->budgetTotals(),
            'items'  => $svc->budgetItemsWithActuals(),
        ];
    }

    public function transactionHistoryReport(string $from, string $to): array
    {
        return Database::query(
            "SELECT t.transaction_date, t.type, t.description, t.amount,
                    t.payment_method, c.name AS category_name
             FROM transactions t
             LEFT JOIN expense_categories c ON c.id = t.category_id
             WHERE t.user_id = ? AND t.is_deleted = 0
               AND t.transaction_date BETWEEN ? AND ?
             ORDER BY t.transaction_date DESC, t.id DESC",
            [$this->userId, $from, $to]
        ) ?: [];
    }

    public function financialIndicators(string $from, string $to): array
    {
        $row = Database::one(
            "SELECT
                COALESCE(SUM(CASE WHEN type IN ('salary','other_income') THEN amount ELSE 0 END), 0) AS total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expense,
                COALESCE(SUM(CASE WHEN type = 'savings_in' THEN amount ELSE 0 END), 0) AS savings_contrib,
                COALESCE(SUM(CASE WHEN type = 'debt_repay' THEN amount ELSE 0 END), 0) AS debt_repay,
                COALESCE(SUM(CASE WHEN type = 'refund' THEN amount ELSE 0 END), 0) AS refunds,
                COALESCE(SUM(
                    CASE WHEN c.kind = 'essential' AND t.type = 'expense' THEN t.amount ELSE 0 END
                ), 0) AS essential_expense,
                COALESCE(SUM(
                    CASE WHEN c.kind = 'discretionary' AND t.type = 'expense' THEN t.amount ELSE 0 END
                ), 0) AS discretionary_expense
             FROM transactions t
             LEFT JOIN expense_categories c ON c.id = t.category_id
             WHERE t.user_id = ? AND t.is_deleted = 0
               AND t.transaction_date BETWEEN ? AND ?",
            [$this->userId, $from, $to]
        );
        $totalIncome = (float) ($row['total_income'] ?? 0);
        $totalExpense = (float) ($row['total_expense'] ?? 0);
        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_cash' => $totalIncome - $totalExpense,
            'savings_contrib' => (float) ($row['savings_contrib'] ?? 0),
            'debt_repay' => (float) ($row['debt_repay'] ?? 0),
            'refunds' => (float) ($row['refunds'] ?? 0),
            'essential_expense' => (float) ($row['essential_expense'] ?? 0),
            'discretionary_expense' => (float) ($row['discretionary_expense'] ?? 0),
            'essential_pct' => $totalIncome > 0 ? ((float) $row['essential_expense'] / $totalIncome) * 100 : 0,
            'discretionary_pct' => $totalIncome > 0 ? ((float) $row['discretionary_expense'] / $totalIncome) * 100 : 0,
        ];
    }

    /**
     * Convert a row of values into a CSV-safe cell. Prefixes dangerous
     * characters (=, +, -, @, tab, CR) with a single quote to prevent
     * spreadsheet formula injection.
     */
    public static function csvCell(?string $value, string $prefix = "'"): string
    {
        if ($value === null) return '';
        if (preg_match('/^[=+\-@]/', $value) || strpbrk($value, "\t\r") !== false) {
            $value = $prefix . $value;
        }
        // Escape embedded quotes
        return '"' . str_replace('"', '""', $value) . '"';
    }

    public static function csvRow(array $cells): string
    {
        return implode(',', $cells) . "\r\n";
    }
}

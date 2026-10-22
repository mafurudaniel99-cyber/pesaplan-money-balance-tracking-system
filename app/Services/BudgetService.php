<?php
declare(strict_types=1);
namespace App\Services;

use App\Models\{Budget, Transaction};
use App\Core\Database;

/**
 * Budget service — recommend, save, and warn about budgets.
 */
final class BudgetService
{
    private int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Suggest a starter budget based on actual income & expenses.
     * Returns suggested category limits. Not enforced — user can override.
     */
    public function suggestStarter(float $monthlyIncome, array $pastExpensesByCategory = []): array
    {
        $suggestion = [];
        $remaining = $monthlyIncome;

        // Start from past averages if available
        foreach ($pastExpensesByCategory as $row) {
            $suggested = round($row['avg'] ?? ($row['total'] ?? 0), 0);
            if ($suggested > 0) {
                $suggestion[] = [
                    'category_id' => (int) $row['category_id'],
                    'category_name' => $row['category_name'],
                    'icon' => $row['icon'] ?? 'fa-tag',
                    'color' => $row['color'] ?? '#4a6fa5',
                    'kind' => $row['kind'] ?? 'other',
                    'planned_amount' => $suggested,
                    'priority' => $row['kind'] === 'essential' ? 1 : 5,
                    'is_essential' => $row['kind'] === 'essential' ? 1 : 0,
                    'reason' => 'Wastani wa matumizi yako ya miezi 3 iliyopita',
                ];
                $remaining -= $suggested;
            }
        }

        return [
            'items' => $suggestion,
            'remaining' => $remaining,
            'income' => $monthlyIncome,
        ];
    }

    public function pastAveragesByCategory(int $months = 3): array
    {
        return Database::query(
            "SELECT c.id AS category_id, c.name AS category_name, c.icon, c.color, c.kind,
                    AVG(month_total) AS avg,
                    SUM(month_total) AS total
             FROM (
                SELECT t.category_id,
                       DATE_FORMAT(t.transaction_date, '%Y-%m') AS m,
                       SUM(t.amount) AS month_total
                FROM transactions t
                WHERE t.user_id = ? AND t.type = 'expense' AND t.is_deleted = 0
                  AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                GROUP BY t.category_id, m
             ) AS sub
             JOIN expense_categories c ON c.id = sub.category_id
             GROUP BY c.id, c.name, c.icon, c.color, c.kind
             ORDER BY avg DESC",
            [$this->userId, $months]
        ) ?: [];
    }

    /**
     * Warnings for a particular budget item vs. actual spending.
     */
    public function itemWarnings(float $planned, float $actual): array
    {
        $pct = $planned > 0 ? ($actual / $planned) * 100.0 : 0.0;
        $warnings = [];
        if ($actual > $planned && $planned > 0) {
            $warnings[] = [
                'level' => 'danger',
                'message' => 'Umezidi bajeti kwa ' . money($actual - $planned) . '. Pakia upya bajeti au punguza matumizi.',
            ];
        } elseif ($pct >= 100 && $planned > 0) {
            $warnings[] = [
                'level' => 'danger',
                'message' => 'Umefikia kikomo cha bajeti.',
            ];
        } elseif ($pct >= 80) {
            $warnings[] = [
                'level' => 'warning',
                'message' => 'Karibu kufikia kikomo (' . round($pct) . '%).',
            ];
        }
        return $warnings;
    }
}

<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class Budget
{
    public static function findForUserMonth(int $userId, string $month): ?array
    {
        return Database::one(
            "SELECT * FROM budgets WHERE user_id = ? AND period_month = ?",
            [$userId, $month]
        );
    }

    public static function findForUser(int $id, int $userId): ?array
    {
        return Database::one(
            "SELECT * FROM budgets WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
    }

    public static function create(int $userId, string $month, float $totalIncomePlanned, ?string $notes): int
    {
        Database::query(
            "INSERT INTO budgets (user_id, period_month, total_income_planned, notes)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE total_income_planned = VALUES(total_income_planned), notes = VALUES(notes)",
            [$userId, $month, $totalIncomePlanned, $notes]
        );
        $existing = self::findForUserMonth($userId, $month);
        return $existing ? (int) $existing['id'] : Database::insertId();
    }

    public static function itemsForUser(int $budgetId, int $userId): array
    {
        // JOIN through budgets to enforce user_id at the SQL level (defense in depth).
        return Database::query(
            "SELECT bi.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color, c.kind
             FROM budget_items bi
             JOIN budgets b ON b.id = bi.budget_id AND b.user_id = ?
             JOIN expense_categories c ON c.id = bi.category_id
             WHERE bi.budget_id = ?
             ORDER BY bi.priority ASC, c.sort_order ASC",
            [$userId, $budgetId]
        ) ?: [];
    }

    public static function addItem(int $budgetId, int $categoryId, float $planned, int $priority, bool $isEssential): void
    {
        Database::query(
            "INSERT INTO budget_items (budget_id, category_id, planned_amount, priority, is_essential)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE planned_amount = VALUES(planned_amount), priority = VALUES(priority), is_essential = VALUES(is_essential)",
            [$budgetId, $categoryId, $planned, $priority, (int) $isEssential]
        );
    }

    public static function deleteItem(int $itemId, int $userId): void
    {
        // Ensure item belongs to user's budget before delete.
        Database::query(
            "DELETE bi FROM budget_items bi
             JOIN budgets b ON b.id = bi.budget_id
             WHERE bi.id = ? AND b.user_id = ?",
            [$itemId, $userId]
        );
    }

    public static function recentMonths(int $userId, int $limit = 6): array
    {
        return Database::query(
            "SELECT * FROM budgets WHERE user_id = ? ORDER BY period_month DESC LIMIT ?",
            [$userId, $limit]
        ) ?: [];
    }
}

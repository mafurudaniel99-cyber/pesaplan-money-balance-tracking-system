<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class FinancialObligation
{
    public static function allForUser(int $userId): array
    {
        return Database::query(
            "SELECT o.*, c.name AS category_name
             FROM financial_obligations o
             LEFT JOIN expense_categories c ON c.id = o.category_id
             WHERE o.user_id = ?
             ORDER BY o.is_paid ASC, o.next_due_date ASC, o.id ASC",
            [$userId]
        ) ?: [];
    }

    public static function unpaid(int $userId): array
    {
        return Database::query(
            "SELECT * FROM financial_obligations WHERE user_id = ? AND is_paid = 0 ORDER BY next_due_date ASC",
            [$userId]
        ) ?: [];
    }

    public static function totalUnpaid(int $userId): float
    {
        $v = Database::scalar(
            "SELECT COALESCE(SUM(amount), 0) FROM financial_obligations WHERE user_id = ? AND is_paid = 0",
            [$userId]
        );
        return (float) $v;
    }

    public static function findForUser(int $id, int $userId): ?array
    {
        return Database::one("SELECT * FROM financial_obligations WHERE id = ? AND user_id = ?", [$id, $userId]);
    }

    public static function create(int $userId, array $data): int
    {
        Database::query(
            "INSERT INTO financial_obligations
                (user_id, name, amount, due_day, next_due_date, is_recurring, is_paid, category_id, notes)
             VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?)",
            [
                $userId,
                $data['name'],
                (float) $data['amount'],
                $data['due_day'] ?? null,
                $data['next_due_date'] ?? null,
                (int) !empty($data['is_recurring']),
                $data['category_id'] ?? null,
                $data['notes'] ?? null,
            ]
        );
        return Database::insertId();
    }

    public static function markPaid(int $id, int $userId, bool $paid = true): void
    {
        Database::query(
            "UPDATE financial_obligations SET is_paid = ? WHERE id = ? AND user_id = ?",
            [(int) $paid, $id, $userId]
        );
    }

    public static function delete(int $id, int $userId): void
    {
        Database::query("DELETE FROM financial_obligations WHERE id = ? AND user_id = ?", [$id, $userId]);
    }
}

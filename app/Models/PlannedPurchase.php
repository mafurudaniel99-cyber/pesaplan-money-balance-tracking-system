<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class PlannedPurchase
{
    public static function allForUser(int $userId): array
    {
        return Database::query(
            "SELECT p.*, c.name AS category_name
             FROM planned_purchases p
             LEFT JOIN expense_categories c ON c.id = p.category_id
             WHERE p.user_id = ?
             ORDER BY
               CASE p.status WHEN 'cooling' THEN 0 WHEN 'pending' THEN 1 WHEN 'approved' THEN 2 WHEN 'completed' THEN 3 ELSE 4 END,
               p.created_at DESC",
            [$userId]
        ) ?: [];
    }

    public static function findForUser(int $id, int $userId): ?array
    {
        return Database::one("SELECT * FROM planned_purchases WHERE id = ? AND user_id = ?", [$id, $userId]);
    }

    public static function create(int $userId, array $data, int $coolingHours): int
    {
        $coolingUntil = date('Y-m-d H:i:s', time() + $coolingHours * 3600);
        Database::query(
            "INSERT INTO planned_purchases
                (user_id, item_name, proposed_cost, reason, category_id, classification,
                 can_postpone, proposed_date, status, cooling_until, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'cooling', ?, ?)",
            [
                $userId,
                $data['item_name'],
                (float) $data['proposed_cost'],
                $data['reason'],
                $data['category_id'] ?? null,
                $data['classification'] ?? 'discretionary',
                (int) !empty($data['can_postpone']),
                $data['proposed_date'],
                $coolingUntil,
                $data['notes'] ?? null,
            ]
        );
        return Database::insertId();
    }

    public static function setStatus(int $id, int $userId, string $status, ?string $result = null, ?string $explanation = null): void
    {
        Database::query(
            "UPDATE planned_purchases
             SET status = ?, decision_result = ?, decision_explanation = ?,
                 completed_at = IF(? = 'completed', NOW(), completed_at)
             WHERE id = ? AND user_id = ?",
            [$status, $result, $explanation, $status, $id, $userId]
        );
    }

    public static function delete(int $id, int $userId): void
    {
        Database::query("DELETE FROM planned_purchases WHERE id = ? AND user_id = ?", [$id, $userId]);
    }
}

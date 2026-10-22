<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class SavingsGoal
{
    public static function allForUser(int $userId): array
    {
        return Database::query(
            "SELECT * FROM savings_goals WHERE user_id = ? AND is_active = 1 ORDER BY priority ASC, id ASC",
            [$userId]
        ) ?: [];
    }

    public static function findForUser(int $id, int $userId): ?array
    {
        return Database::one(
            "SELECT * FROM savings_goals WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
    }

    public static function create(int $userId, array $data): int
    {
        Database::query(
            "INSERT INTO savings_goals
                (user_id, name, target_amount, current_amount, target_date, priority,
                 is_emergency_fund, is_active, notes)
             VALUES (?, ?, ?, 0, ?, ?, ?, 1, ?)",
            [
                $userId,
                $data['name'],
                (float) $data['target_amount'],
                $data['target_date'] ?? null,
                (int) ($data['priority'] ?? 5),
                (int) !empty($data['is_emergency_fund']),
                $data['notes'] ?? null,
            ]
        );
        return Database::insertId();
    }

    public static function adjustBalance(int $goalId, int $userId, float $delta): void
    {
        Database::query(
            "UPDATE savings_goals SET current_amount = GREATEST(0, current_amount + ?) WHERE id = ? AND user_id = ?",
            [$delta, $goalId, $userId]
        );
    }

    public static function delete(int $id, int $userId): void
    {
        Database::query("DELETE FROM savings_goals WHERE id = ? AND user_id = ?", [$id, $userId]);
    }

    public static function totalSaved(int $userId): float
    {
        $v = Database::scalar(
            "SELECT COALESCE(SUM(current_amount), 0) FROM savings_goals WHERE user_id = ? AND is_active = 1",
            [$userId]
        );
        return (float) $v;
    }

    /**
     * User-scoped: JOINs through savings_goals to enforce user_id at SQL level.
     */
    public static function contributionsForUser(int $goalId, int $userId): array
    {
        return Database::query(
            "SELECT sc.* FROM savings_contributions sc
             JOIN savings_goals g ON g.id = sc.goal_id AND g.user_id = ?
             WHERE sc.goal_id = ?
             ORDER BY sc.contribution_date DESC, sc.id DESC",
            [$userId, $goalId]
        ) ?: [];
    }

    public static function addContribution(int $goalId, ?int $txId, float $amount, string $direction, string $date, ?string $notes): void
    {
        Database::query(
            "INSERT INTO savings_contributions (goal_id, transaction_id, amount, direction, contribution_date, notes)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$goalId, $txId, $amount, $direction, $date, $notes]
        );
    }
}

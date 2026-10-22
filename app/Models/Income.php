<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class Income
{
    public static function allForUser(int $userId, int $limit = 60): array
    {
        return Database::query(
            "SELECT * FROM income_records WHERE user_id = ? ORDER BY expected_date DESC, id DESC LIMIT ?",
            [$userId, $limit]
        ) ?: [];
    }

    public static function forMonth(int $userId, string $month): array
    {
        return Database::query(
            "SELECT * FROM income_records WHERE user_id = ? AND period_month = ? ORDER BY expected_date ASC",
            [$userId, $month]
        ) ?: [];
    }

    public static function findForUser(int $id, int $userId): ?array
    {
        return Database::one(
            "SELECT * FROM income_records WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
    }

    public static function create(int $userId, array $data): int
    {
        Database::query(
            "INSERT INTO income_records
                (user_id, source, expected_amount, received_amount, expected_date,
                 received_date, is_received, is_salary, period_month, notes)
             VALUES (?, ?, ?, 0, ?, NULL, ?, ?, ?, ?)",
            [
                $userId,
                $data['source'],
                (float) $data['expected_amount'],
                $data['expected_date'],
                (int) !empty($data['is_received']),
                (int) !empty($data['is_salary']),
                substr($data['expected_date'], 0, 7), // YYYY-MM
                $data['notes'] ?? null
            ]
        );
        return Database::insertId();
    }

    public static function markReceived(int $id, int $userId, float $receivedAmount, string $receivedDate): void
    {
        Database::query(
            "UPDATE income_records
             SET received_amount = ?, received_date = ?, is_received = 1
             WHERE id = ? AND user_id = ?",
            [$receivedAmount, $receivedDate, $id, $userId]
        );
    }

    public static function delete(int $id, int $userId): void
    {
        Database::query(
            "DELETE FROM income_records WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
    }

    /**
     * Total expected salary for a month.
     */
    public static function expectedSalaryForMonth(int $userId, string $month): float
    {
        $v = Database::scalar(
            "SELECT COALESCE(SUM(expected_amount), 0) FROM income_records
             WHERE user_id = ? AND period_month = ? AND is_salary = 1",
            [$userId, $month]
        );
        return (float) $v;
    }

    public static function receivedSalaryForMonth(int $userId, string $month): float
    {
        $v = Database::scalar(
            "SELECT COALESCE(SUM(received_amount), 0) FROM income_records
             WHERE user_id = ? AND period_month = ? AND is_salary = 1 AND is_received = 1",
            [$userId, $month]
        );
        return (float) $v;
    }

    public static function receivedOtherIncomeForMonth(int $userId, string $month): float
    {
        $v = Database::scalar(
            "SELECT COALESCE(SUM(received_amount), 0) FROM income_records
             WHERE user_id = ? AND period_month = ? AND is_salary = 0 AND is_received = 1",
            [$userId, $month]
        );
        return (float) $v;
    }
}

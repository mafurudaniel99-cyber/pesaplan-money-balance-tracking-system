<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class Debt
{
    public static function allForUser(int $userId): array
    {
        return Database::query(
            "SELECT * FROM debts WHERE user_id = ? ORDER BY is_paid_off ASC, next_due_date ASC, id ASC",
            [$userId]
        ) ?: [];
    }

    public static function findForUser(int $id, int $userId): ?array
    {
        return Database::one("SELECT * FROM debts WHERE id = ? AND user_id = ?", [$id, $userId]);
    }

    public static function create(int $userId, array $data): int
    {
        Database::query(
            "INSERT INTO debts
                (user_id, name, original_amount, outstanding_balance, minimum_payment,
                 interest_rate_pct, due_day, next_due_date, is_recurring, is_paid_off, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)",
            [
                $userId,
                $data['name'],
                (float) ($data['original_amount'] ?? 0),
                (float) ($data['outstanding_balance'] ?? 0),
                (float) ($data['minimum_payment'] ?? 0),
                (float) ($data['interest_rate_pct'] ?? 0),
                $data['due_day'] ?? null,
                $data['next_due_date'] ?? null,
                (int) !empty($data['is_recurring']),
                $data['notes'] ?? null,
            ]
        );
        return Database::insertId();
    }

    public static function applyPayment(int $id, int $userId, float $principal, float $interest): void
    {
        Database::query(
            "UPDATE debts
             SET outstanding_balance = GREATEST(0, outstanding_balance - ?),
                 is_paid_off = IF(outstanding_balance - ? <= 0.005, 1, 0)
             WHERE id = ? AND user_id = ?",
            [$principal, $principal, $id, $userId]
        );
    }

    public static function delete(int $id, int $userId): void
    {
        Database::query("DELETE FROM debts WHERE id = ? AND user_id = ?", [$id, $userId]);
    }

    /**
     * User-scoped: JOINs through debts to enforce user_id at SQL level.
     */
    public static function paymentsForUser(int $debtId, int $userId): array
    {
        return Database::query(
            "SELECT dp.* FROM debt_payments dp
             JOIN debts d ON d.id = dp.debt_id AND d.user_id = ?
             WHERE dp.debt_id = ?
             ORDER BY dp.payment_date DESC, dp.id DESC",
            [$userId, $debtId]
        ) ?: [];
    }

    public static function addPayment(int $debtId, ?int $txId, float $amount, float $interest, float $principal, string $date, ?string $notes): void
    {
        Database::query(
            "INSERT INTO debt_payments (debt_id, transaction_id, amount, interest_portion, principal_portion, payment_date, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$debtId, $txId, $amount, $interest, $principal, $date, $notes]
        );
    }

    public static function totalOutstanding(int $userId): float
    {
        $v = Database::scalar(
            "SELECT COALESCE(SUM(outstanding_balance), 0) FROM debts WHERE user_id = ? AND is_paid_off = 0",
            [$userId]
        );
        return (float) $v;
    }

    public static function totalMinimumPayments(int $userId): float
    {
        $v = Database::scalar(
            "SELECT COALESCE(SUM(minimum_payment), 0) FROM debts WHERE user_id = ? AND is_paid_off = 0",
            [$userId]
        );
        return (float) $v;
    }
}

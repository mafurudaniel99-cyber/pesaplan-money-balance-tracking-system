<?php
declare(strict_types=1);
namespace App\Services;

use App\Core\Database;
use App\Models\Income;

/**
 * Allocation service — logical allocation of received salary into purposes.
 * This is NOT bank transfers — interface makes that clear.
 *
 * Rules:
 *  - Allocations only against REAL received funds (must reference an income_id
 *    that has is_received = 1).
 *  - The sum of allocations for a given income_id cannot exceed the
 *    received_amount of that income.
 *  - Each allocation is independent (no double counting).
 */
final class AllocationService
{
    private int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function all(): array
    {
        return Database::query(
            "SELECT a.*, i.source AS income_source, i.received_amount AS income_amount,
                    c.name AS category_name
             FROM money_allocations a
             LEFT JOIN income_records i ON i.id = a.income_id
             LEFT JOIN expense_categories c ON c.id = a.category_id
             WHERE a.user_id = ?
             ORDER BY a.allocation_date DESC, a.id DESC",
            [$this->userId]
        ) ?: [];
    }

    public function create(array $data): int
    {
        $incomeId = (int) ($data['income_id'] ?? 0);
        $amount = (float) $data['allocated_amount'];
        if ($amount <= 0) {
            throw new \RuntimeException('Kiasi cha ugawaji lazima kiwe kikubwa kuliko 0.');
        }
        if ($incomeId > 0) {
            $income = Income::findForUser($incomeId, $this->userId);
            if (!$income) {
                throw new \RuntimeException('Mapato yaliyochaguliwa hayapatikani.');
            }
            if (!$income['is_received']) {
                throw new \RuntimeException('Huwezi kugawa mapato ambayo bado hayajapokelewa.');
            }
            $alreadyAllocated = (float) (Database::scalar(
                "SELECT COALESCE(SUM(allocated_amount), 0) FROM money_allocations WHERE income_id = ? AND user_id = ?",
                [$incomeId, $this->userId]
            ) ?? 0);
            if ($alreadyAllocated + $amount > (float) $income['received_amount']) {
                throw new \RuntimeException(
                    'Ugawaji unazidi kiasi kilichopokelewa. Kilichogawiwa: '
                    . money($alreadyAllocated) . '. Kipokelewa: ' . money($income['received_amount']) . '.'
                );
            }
        }
        Database::query(
            "INSERT INTO money_allocations
                (user_id, income_id, category_id, purpose, allocated_amount, allocation_date, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $this->userId,
                $incomeId > 0 ? $incomeId : null,
                $data['category_id'] ?? null,
                $data['purpose'],
                $amount,
                $data['allocation_date'],
                $data['notes'] ?? null,
            ]
        );
        return Database::insertId();
    }

    public function delete(int $id): void
    {
        Database::query(
            "DELETE FROM money_allocations WHERE id = ? AND user_id = ?",
            [$id, $this->userId]
        );
    }

    /**
     * Aggregate allocations by purpose for the dashboard.
     */
    public function totalsByPurpose(): array
    {
        return Database::query(
            "SELECT purpose, SUM(allocated_amount) AS total
             FROM money_allocations WHERE user_id = ?
             GROUP BY purpose ORDER BY total DESC",
            [$this->userId]
        ) ?: [];
    }
}

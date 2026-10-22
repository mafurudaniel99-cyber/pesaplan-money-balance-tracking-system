<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class Transaction
{
    public const TYPE_EXPENSE      = 'expense';
    public const TYPE_SALARY       = 'salary';
    public const TYPE_OTHER_INCOME = 'other_income';
    public const TYPE_SAVINGS_IN   = 'savings_in';
    public const TYPE_SAVINGS_OUT  = 'savings_out';
    public const TYPE_DEBT_REPAY   = 'debt_repay';
    public const TYPE_REFUND       = 'refund';
    public const TYPE_TRANSFER_IN  = 'transfer_in';
    public const TYPE_TRANSFER_OUT = 'transfer_out';
    public const TYPE_ADJUSTMENT   = 'adjustment';

    /**
     * Filtered, paginated transaction list for a user.
     */
    public static function search(int $userId, array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where = ["t.user_id = ?", "t.is_deleted = 0"];
        $params = [$userId];

        if (!empty($filters['from'])) {
            $where[] = "t.transaction_date >= ?";
            $params[] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $where[] = "t.transaction_date <= ?";
            $params[] = $filters['to'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = "t.category_id = ?";
            $params[] = (int) $filters['category_id'];
        }
        if (!empty($filters['type'])) {
            $where[] = "t.type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['payment_method'])) {
            $where[] = "t.payment_method = ?";
            $params[] = $filters['payment_method'];
        }
        if (!empty($filters['q'])) {
            $where[] = "(t.description LIKE ? OR t.notes LIKE ?)";
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }

        $orderBy = 't.transaction_date DESC, t.id DESC';
        if (($filters['sort'] ?? '') === 'amount_asc')  $orderBy = 't.amount ASC, t.transaction_date DESC';
        if (($filters['sort'] ?? '') === 'amount_desc') $orderBy = 't.amount DESC, t.transaction_date DESC';
        if (($filters['sort'] ?? '') === 'date_asc')    $orderBy = 't.transaction_date ASC, t.id ASC';

        $offset = ($page - 1) * $perPage;
        $sql = "SELECT t.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color
                FROM transactions t
                LEFT JOIN expense_categories c ON c.id = t.category_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY $orderBy
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        return Database::query($sql, $params) ?: [];
    }

    public static function countForUser(int $userId, array $filters = []): int
    {
        $where = ["user_id = ?", "is_deleted = 0"];
        $params = [$userId];
        if (!empty($filters['from'])) { $where[] = "transaction_date >= ?"; $params[] = $filters['from']; }
        if (!empty($filters['to']))   { $where[] = "transaction_date <= ?"; $params[] = $filters['to']; }
        if (!empty($filters['category_id'])) { $where[] = "category_id = ?"; $params[] = (int) $filters['category_id']; }
        if (!empty($filters['type'])) { $where[] = "type = ?"; $params[] = $filters['type']; }
        if (!empty($filters['payment_method'])) { $where[] = "payment_method = ?"; $params[] = $filters['payment_method']; }
        if (!empty($filters['q'])) {
            $where[] = "(description LIKE ? OR notes LIKE ?)";
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }
        return (int) (Database::scalar(
            "SELECT COUNT(*) FROM transactions WHERE " . implode(' AND ', $where),
            $params
        ) ?? 0);
    }

    public static function findForUser(int $id, int $userId): ?array
    {
        return Database::one(
            "SELECT t.*, c.name AS category_name
             FROM transactions t
             LEFT JOIN expense_categories c ON c.id = t.category_id
             WHERE t.id = ? AND t.user_id = ? AND t.is_deleted = 0",
            [$id, $userId]
        );
    }

    public static function create(int $userId, array $data): int
    {
        Database::query(
            "INSERT INTO transactions
                (user_id, category_id, income_id, type, amount, transaction_date,
                 description, payment_method, notes, is_deleted)
             VALUES ?, ?, ?, ?, ?, ?, ?, ?, ?, 0",
            [
                $userId,
                $data['category_id'] ?? null,
                $data['income_id'] ?? null,
                $data['type'],
                (float) $data['amount'],
                $data['transaction_date'],
                $data['description'],
                $data['payment_method'] ?? 'cash',
                $data['notes'] ?? null,
            ]
        );
        $id = Database::insertId();
        self::audit($id, $userId, 'create', null);
        return $id;
    }

    public static function update(int $id, int $userId, array $data): void
    {
        $before = self::findForUser($id, $userId);
        Database::query(
            "UPDATE transactions
             SET category_id = ?, type = ?, amount = ?, transaction_date = ?,
                 description = ?, payment_method = ?, notes = ?
             WHERE id = ? AND user_id = ?",
            [
                $data['category_id'] ?? null,
                $data['type'],
                (float) $data['amount'],
                $data['transaction_date'],
                $data['description'],
                $data['payment_method'] ?? 'cash',
                $data['notes'] ?? null,
                $id,
                $userId
            ]
        );
        self::audit($id, $userId, 'update', $before);
    }

    public static function softDelete(int $id, int $userId): void
    {
        $before = self::findForUser($id, $userId);
        Database::query(
            "UPDATE transactions SET is_deleted = 1 WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
        self::audit($id, $userId, 'delete', $before);
    }

    public static function audit(int $txId, int $userId, string $action, ?array $snapshot): void
    {
        Database::query(
            "INSERT INTO transaction_audit (user_id, transaction_id, action, snapshot, changed_by, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())",
            [
                $userId,
                $txId,
                $action,
                $snapshot ? json_encode($snapshot, JSON_UNESCAPED_UNICODE) : null,
                $userId
            ]
        );
    }

    /**
     * Total actual expenses for a user in a month.
     */
    public static function totalExpensesForMonth(int $userId, string $month): float
    {
        $v = Database::scalar(
            "SELECT COALESCE(SUM(amount), 0) FROM transactions
             WHERE user_id = ? AND type = ? AND is_deleted = 0
               AND DATE_FORMAT(transaction_date, '%Y-%m') = ?",
            [$userId, self::TYPE_EXPENSE, $month]
        );
        return (float) $v;
    }

    public static function totalIncomeForMonth(int $userId, string $month): float
    {
        $v = Database::scalar(
            "SELECT COALESCE(SUM(amount), 0) FROM transactions
             WHERE user_id = ? AND type IN (?, ?) AND is_deleted = 0
               AND DATE_FORMAT(transaction_date, '%Y-%m') = ?",
            [$userId, self::TYPE_SALARY, self::TYPE_OTHER_INCOME, $month]
        );
        return (float) $v;
    }

    public static function expensesByCategoryForMonth(int $userId, string $month): array
    {
        return Database::query(
            "SELECT c.id, c.name, c.icon, c.color,
                    COALESCE(SUM(t.amount), 0) AS total
             FROM transactions t
             LEFT JOIN expense_categories c ON c.id = t.category_id
             WHERE t.user_id = ? AND t.type = ? AND t.is_deleted = 0
               AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ?
             GROUP BY c.id, c.name, c.icon, c.color
             HAVING total > 0
             ORDER BY total DESC",
            [$userId, self::TYPE_EXPENSE, $month]
        ) ?: [];
    }

    public static function recentForUser(int $userId, int $limit = 8): array
    {
        return Database::query(
            "SELECT t.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color
             FROM transactions t
             LEFT JOIN expense_categories c ON c.id = t.category_id
             WHERE t.user_id = ? AND t.is_deleted = 0
             ORDER BY t.transaction_date DESC, t.id DESC
             LIMIT ?",
            [$userId, $limit]
        ) ?: [];
    }

    public static function monthlyIncomeExpenseTrend(int $userId, int $months = 6): array
    {
        return Database::query(
            "SELECT DATE_FORMAT(t.transaction_date, '%Y-%m') AS month,
                    COALESCE(SUM(CASE WHEN t.type IN ('salary','other_income') THEN t.amount ELSE 0 END), 0) AS income,
                    COALESCE(SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END), 0) AS expense
             FROM transactions t
             WHERE t.user_id = ? AND t.is_deleted = 0
               AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
             GROUP BY DATE_FORMAT(t.transaction_date, '%Y-%m')
             ORDER BY month ASC",
            [$userId, $months]
        ) ?: [];
    }

    /**
     * Recorded cash balance = opening (0) + cash inflows - cash outflows.
     * Savings contributions are NOT counted as consumption here, because they
     * remain the user's money (just moved into a savings goal).
     */
    public static function recordedCashBalance(int $userId): float
    {
        $v = Database::scalar(
            "SELECT COALESCE(SUM(
                CASE
                    WHEN type IN ('salary','other_income','refund','transfer_in') THEN amount
                    WHEN type IN ('expense','debt_repay','transfer_out') THEN -amount
                    WHEN type = 'savings_in' THEN 0     -- money is still owned, just reserved
                    WHEN type = 'savings_out' THEN amount
                    WHEN type = 'adjustment' THEN amount
                    ELSE 0
                END
            ), 0)
            FROM transactions
            WHERE user_id = ? AND is_deleted = 0",
            [$userId]
        );
        return (float) $v;
    }

    /**
     * Sum of expenses in a category for a given month.
     */
    public static function expensesForCategoryInMonth(int $userId, int $categoryId, string $month): float
    {
        $v = Database::scalar(
            "SELECT COALESCE(SUM(amount), 0) FROM transactions
             WHERE user_id = ? AND category_id = ? AND type = ? AND is_deleted = 0
               AND DATE_FORMAT(transaction_date, '%Y-%m') = ?",
            [$userId, $categoryId, self::TYPE_EXPENSE, $month]
        );
        return (float) $v;
    }

    /**
     * Days since the user's last recorded transaction.
     */
    public static function daysSinceLast(int $userId): ?int
    {
        $v = Database::scalar(
            "SELECT MAX(transaction_date) FROM transactions WHERE user_id = ? AND is_deleted = 0",
            [$userId]
        );
        if (!$v) return null;
        $diff = (new \DateTime($v))->diff(new \DateTime('today'));
        return (int) $diff->days;
    }
}

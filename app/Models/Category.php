<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class Category
{
    /**
     * Get active categories visible to a user (system defaults + user's own).
     */
    public static function allForUser(int $userId, ?string $type = null): array
    {
        $sql = "SELECT * FROM expense_categories
                WHERE is_active = 1 AND (user_id IS NULL OR user_id = ?)";
        $params = [$userId];
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        $sql .= " ORDER BY sort_order ASC, name ASC";
        return Database::query($sql, $params) ?: [];
    }

    public static function allForUserIncludingInactive(int $userId): array
    {
        return Database::query(
            "SELECT * FROM expense_categories
             WHERE (user_id IS NULL OR user_id = ?)
             ORDER BY is_active DESC, sort_order ASC, name ASC",
            [$userId]
        ) ?: [];
    }

    public static function find(int $id): ?array
    {
        return Database::one("SELECT * FROM expense_categories WHERE id = ?", [$id]);
    }

    /**
     * Find a category and confirm it belongs to the user (or is a system default).
     */
    public static function findForUser(int $id, int $userId): ?array
    {
        return Database::one(
            "SELECT * FROM expense_categories
             WHERE id = ? AND (user_id IS NULL OR user_id = ?)",
            [$id, $userId]
        );
    }

    public static function create(int $userId, array $data): int
    {
        Database::query(
            "INSERT INTO expense_categories (user_id, name, type, icon, color, kind, is_active, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, 1, ?)",
            [
                $userId,
                $data['name'],
                $data['type'] ?? 'expense',
                $data['icon'] ?? 'fa-tag',
                $data['color'] ?? '#4a6fa5',
                $data['kind'] ?? 'other',
                (int) ($data['sort_order'] ?? 0)
            ]
        );
        return Database::insertId();
    }

    public static function update(int $id, int $userId, array $data): void
    {
        Database::query(
            "UPDATE expense_categories
             SET name = ?, icon = ?, color = ?, kind = ?, sort_order = ?
             WHERE id = ? AND user_id = ?",
            [
                $data['name'],
                $data['icon'] ?? 'fa-tag',
                $data['color'] ?? '#4a6fa5',
                $data['kind'] ?? 'other',
                (int) ($data['sort_order'] ?? 0),
                $id,
                $userId
            ]
        );
    }

    public static function toggleActive(int $id, int $userId): void
    {
        Database::query(
            "UPDATE expense_categories SET is_active = 1 - is_active WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
    }
}

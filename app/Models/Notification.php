<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class Notification
{
    public static function allForUser(int $userId, int $limit = 50): array
    {
        return Database::query(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT ?",
            [$userId, $limit]
        ) ?: [];
    }

    public static function unreadCount(int $userId): int
    {
        return (int) (Database::scalar(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        ) ?? 0);
    }

    public static function create(int $userId, string $type, string $title, ?string $body, string $severity = 'info', string $icon = 'fa-bell', ?string $actionUrl = null): void
    {
        Database::query(
            "INSERT INTO notifications (user_id, type, title, body, icon, severity, is_read, action_url)
             VALUES (?, ?, ?, ?, ?, ?, 0, ?)",
            [$userId, $type, $title, $body, $icon, $severity, $actionUrl]
        );
    }

    public static function markRead(int $id, int $userId): void
    {
        Database::query("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$id, $userId]);
    }

    public static function markAllRead(int $userId): void
    {
        Database::query("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [$userId]);
    }
}

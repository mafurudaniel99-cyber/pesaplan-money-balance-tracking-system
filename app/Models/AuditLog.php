<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class AuditLog
{
    public static function write(?int $userId, string $action, ?string $entity = null, ?string $entityId = null, ?array $details = null): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        Database::query(
            "INSERT INTO audit_logs (user_id, action, entity, entity_id, details, ip_address)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $userId,
                $action,
                $entity,
                $entityId,
                $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
                $ip
            ]
        );
    }
}

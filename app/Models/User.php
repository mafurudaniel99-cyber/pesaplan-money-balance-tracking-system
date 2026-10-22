<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class User
{
    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::one('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public static function create(array $data): int
    {
        Database::query(
            "INSERT INTO users (name, email, password_hash, phone, preferred_language, timezone, currency, created_at, updated_at)
             VALUES (?, ?, ?, NULL, 'sw', 'Africa/Dar_es_Salaam', 'TZS', NOW(), NOW())",
            [$data['name'], $data['email'], $data['password_hash']]
        );
        $id = Database::insertId();
        // Create empty settings row
        Database::query("INSERT INTO user_settings (user_id) VALUES (?)", [$id]);
        return $id;
    }

    public static function updateLastLogin(int $id): void
    {
        Database::query("UPDATE users SET last_login_at = NOW() WHERE id = ?", [$id]);
    }

    public static function incrementFailedLogin(int $id, int $max, int $lockMinutes): void
    {
        Database::query(
            "UPDATE users
             SET failed_logins = failed_logins + 1,
                 locked_until = IF(failed_logins + 1 >= ?, DATE_ADD(NOW(), INTERVAL ? MINUTE), locked_until)
             WHERE id = ?",
            [$max, $lockMinutes, $id]
        );
    }

    public static function resetFailedLogin(int $id): void
    {
        Database::query("UPDATE users SET failed_logins = 0, locked_until = NULL WHERE id = ?", [$id]);
    }

    public static function updatePassword(int $id, string $hash): void
    {
        Database::query("UPDATE users SET password_hash = ? WHERE id = ?", [$hash, $id]);
    }

    public static function markOnboardingComplete(int $id): void
    {
        Database::query("UPDATE users SET onboarding_complete = 1 WHERE id = ?", [$id]);
    }

    public static function updateProfile(int $id, array $fields): void
    {
        Database::query(
            "UPDATE users SET name = ?, phone = ? WHERE id = ?",
            [$fields['name'], $fields['phone'] ?? null, $id]
        );
    }

    public static function delete(int $id): void
    {
        // Hard delete — cascade handles child tables.
        Database::query("DELETE FROM users WHERE id = ?", [$id]);
    }

    public static function settings(int $userId): array
    {
        $s = Database::one("SELECT * FROM user_settings WHERE user_id = ?", [$userId]);
        return $s ?? [
            'user_id' => $userId,
            'expected_salary' => 0,
            'expected_salary_day' => 28,
            'cooling_off_hours' => 24,
            'notify_budget_threshold' => 1,
            'notify_upcoming_bills' => 1,
            'notify_review_reminders' => 1,
            'budget_threshold_pct' => 80,
        ];
    }

    public static function updateSettings(int $userId, array $s): void
    {
        Database::query(
            "UPDATE user_settings
             SET expected_salary = ?, expected_salary_day = ?, cooling_off_hours = ?,
                 notify_budget_threshold = ?, notify_upcoming_bills = ?, notify_review_reminders = ?,
                 budget_threshold_pct = ?
             WHERE user_id = ?",
            [
                (float) ($s['expected_salary'] ?? 0),
                (int) ($s['expected_salary_day'] ?? 28),
                (int) ($s['cooling_off_hours'] ?? 24),
                (int) (!empty($s['notify_budget_threshold'])),
                (int) (!empty($s['notify_upcoming_bills'])),
                (int) (!empty($s['notify_review_reminders'])),
                (int) ($s['budget_threshold_pct'] ?? 80),
                $userId
            ]
        );
    }
}

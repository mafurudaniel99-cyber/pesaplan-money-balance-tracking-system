<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class Review
{
    public const TYPE_DAILY = 'daily';
    public const TYPE_WEEKLY = 'weekly';
    public const TYPE_MONTHLY = 'monthly';

    public static function allForUser(int $userId, int $limit = 30): array
    {
        return Database::query(
            "SELECT * FROM financial_reviews WHERE user_id = ? ORDER BY review_date DESC, id DESC LIMIT ?",
            [$userId, $limit]
        ) ?: [];
    }

    public static function findForUser(int $id, int $userId): ?array
    {
        return Database::one("SELECT * FROM financial_reviews WHERE id = ? AND user_id = ?", [$id, $userId]);
    }

    public static function findForPeriod(int $userId, string $type, string $periodLabel): ?array
    {
        return Database::one(
            "SELECT * FROM financial_reviews WHERE user_id = ? AND review_type = ? AND period_label = ?",
            [$userId, $type, $periodLabel]
        );
    }

    public static function create(int $userId, string $type, string $date, string $periodLabel, array $checklist, ?string $notes): int
    {
        Database::query(
            "INSERT INTO financial_reviews (user_id, review_type, review_date, period_label, checklist, notes, is_completed)
             VALUES (?, ?, ?, ?, ?, ?, 0)
             ON DUPLICATE KEY UPDATE checklist = VALUES(checklist), notes = VALUES(notes)",
            [
                $userId,
                $type,
                $date,
                $periodLabel,
                json_encode($checklist, JSON_UNESCAPED_UNICODE),
                $notes
            ]
        );
        $existing = self::findForPeriod($userId, $type, $periodLabel);
        return $existing ? (int) $existing['id'] : Database::insertId();
    }

    public static function complete(int $id, int $userId): void
    {
        Database::query(
            "UPDATE financial_reviews SET is_completed = 1, completed_at = NOW() WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
    }

    public static function dailyChecklist(): array
    {
        return [
            'Nimeandika mapato yote yaliyopokelewa leo',
            'Nimeandika matumizi yote niliyoyafanya leo',
            'Nimekagua miamala ya leo',
            'Nimeangalia bajeti iliyobaki kwa kategoria muhimu',
            'Nimetambua matumizi yasiyopangwa',
        ];
    }

    public static function weeklyChecklist(): array
    {
        return [
            'Nimelinganisha matumizi yaliyopangwa na halisi',
            'Nimetambua kategoria kubwa zaidi ya matumizi',
            'Nimekagua manunuzi yasiyopangwa',
            'Nimeangalia bili na majukumu yanayokaribia',
            'Nimepangilia mpango wa matumizi wa wiki ijayo',
        ];
    }

    public static function monthlyChecklist(): array
    {
        return [
            'Nimehakiki mshahara umepokelewa',
            'Nimeangalia jumla ya mapato na matumizi',
            'Nimekagua tofauti za bajeti',
            'Nimeangalia majukumu yasiyolipwa',
            'Nimeangalia michango ya akiba',
            'Nimetambua mifumo ya matumizi',
            'Nimelinganisha mwezi huu na miezi iliyopita',
            'Nimeunda bajeti ya mwezi ujao',
        ];
    }
}

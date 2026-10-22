<?php
declare(strict_types=1);
namespace App\Services;

use App\Models\Notification;
use App\Models\FinancialObligation;
use App\Models\Transaction;
use App\Core\Database;

/**
 * Generates in-app notifications from current data.
 * Never claims to have sent email/SMS — only in-app.
 */
final class NotificationService
{
    public static function refreshForUser(int $userId, array $settings): void
    {
        // Upcoming obligations
        if (!empty($settings['notify_upcoming_bills'])) {
            $upcoming = Database::query(
                "SELECT * FROM financial_obligations
                 WHERE user_id = ? AND is_paid = 0
                   AND next_due_date IS NOT NULL
                   AND next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)",
                [$userId]
            ) ?: [];
            foreach ($upcoming as $u) {
                // Avoid duplicate notifications for same obligation+date
                $exists = Database::scalar(
                    "SELECT id FROM notifications
                     WHERE user_id = ? AND type = 'obligation_due'
                       AND action_url LIKE ?
                       AND DATE(created_at) = CURDATE()",
                    [$userId, '%/debts%']
                );
                if (!$exists) {
                    Notification::create(
                        $userId,
                        'obligation_due',
                        'Mjukumu unakaribia: ' . $u['name'],
                        'Lipa ' . money((float) $u['amount']) . ' ifikapo ' . sw_date($u['next_due_date']),
                        'warning', 'fa-calendar-day', '/debts'
                    );
                }
            }
        }

        // No recent transactions
        $days = Transaction::daysSinceLast($userId);
        if ($days !== null && $days >= 3) {
            $exists = Database::scalar(
                "SELECT id FROM notifications WHERE user_id = ? AND type = 'no_transactions' AND DATE(created_at) = CURDATE()",
                [$userId]
            );
            if (!$exists) {
                Notification::create(
                    $userId,
                    'no_transactions',
                    'Hujaweka miamala siku ' . $days,
                    'Kumbuka kuandika miamala yako ili kuwa na rekodi sahihi.',
                    'info', 'fa-clock', '/transactions/create'
                );
            }
        }
    }
}

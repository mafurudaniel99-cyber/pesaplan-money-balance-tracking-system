<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Services\FinancialSummaryService;
use App\Services\NotificationService;
use App\Models\User;
use App\Models\Category;
use App\Models\Notification;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $this->auth->requireAuth();
        $this->auth->requireOnboarding();
        $userId = (int) $this->auth->id();

        $settings = User::settings($userId);
        // Refresh in-app notifications from current data
        NotificationService::refreshForUser($userId, $settings);

        $summary = new FinancialSummaryService($userId);
        $data = [
            'activePage' => 'dashboard',
            'summary' => $summary,
            'recent' => $summary->recentTransactions(6),
            'alerts' => $summary->alerts(),
            'breakdown' => $summary->expenseBreakdownByCategory(),
            'trend' => $summary->monthlyTrend(6),
            'budgetTotals' => $summary->budgetTotals(),
            'unreadNotifs' => Notification::unreadCount($userId),
            'categories' => Category::allForUser($userId, 'expense'),
        ];
        $this->view('dashboard/index', $data);
    }
}

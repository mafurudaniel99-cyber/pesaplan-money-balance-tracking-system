<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\{Budget, Category, Income};
use App\Services\{FinancialSummaryService, BudgetService};
use App\Models\User;

final class BudgetController extends Controller
{
    public function index(): void
    {
        $this->auth->requireAuth();
        $this->auth->requireOnboarding();
        $userId = (int) $this->auth->id();

        $month = $this->query('month', date('Y-m'));
        $summary = new FinancialSummaryService($userId, $month);

        // Build suggested budget for current month if none exists
        $budget = Budget::findForUserMonth($userId, $month);
        $items = $summary->budgetItemsWithActuals();
        $totals = $summary->budgetTotals();
        $recentMonths = Budget::recentMonths($userId, 6);

        $budgetService = new BudgetService($userId);
        $suggestion = $budgetService->suggestStarter(
            (float) User::settings($userId)['expected_salary'],
            $budgetService->pastAveragesByCategory(3)
        );

        $this->view('budgets/index', [
            'activePage' => 'budgets',
            'month' => $month,
            'budget' => $budget,
            'items' => $items,
            'totals' => $totals,
            'recentMonths' => $recentMonths,
            'categories' => Category::allForUser($userId, 'expense'),
            'suggestion' => $suggestion,
            'expectedSalary' => (float) User::settings($userId)['expected_salary'],
            'salaryReceived' => $summary->salaryReceived(),
        ]);
    }

    public function store(): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $month = $this->input('period_month', date('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->back('Mwezi si sahihi.');
        }
        $income = Validator::amount($this->input('total_income_planned', '0')) ?? 0;
        $notes = Validator::sanitize($this->input('notes', '') ?: '');
        Budget::create($userId, $month, $income, $notes);
        $this->redirect('/budgets?month=' . urlencode($month), 'Bajeti imewekwa.');
    }

    public function storeItem(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $budget = Budget::findForUser((int) $id, $userId);
        if (!$budget) {
            $this->back('Bajeti haipatikani.');
        }
        $categoryId = (int) $this->input('category_id', 0);
        $planned = Validator::amount($this->input('planned_amount', '0'));
        if (!$categoryId || $planned === null || $planned < 0) {
            $this->back('Kategoria na kiasi vinahitajika.');
        }
        $priority = (int) $this->input('priority', 5);
        $isEssential = $this->input('is_essential', '0') === '1';
        Budget::addItem((int) $budget['id'], $categoryId, $planned, $priority, $isEssential);
        $this->redirect('/budgets?month=' . urlencode($budget['period_month']), 'Kipengele cha bajeti kimeongezwa.');
    }

    public function deleteItem(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        Budget::deleteItem((int) $id, $userId);
        $this->back('Kipengele cha bajeti kimefutwa.');
    }
}

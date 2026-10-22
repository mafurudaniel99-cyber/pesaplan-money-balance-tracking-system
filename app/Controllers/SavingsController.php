<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\SavingsGoal;
use App\Services\TransactionService;

final class SavingsController extends Controller
{
    public function index(): void
    {
        $this->auth->requireAuth();
        $this->auth->requireOnboarding();
        $userId = (int) $this->auth->id();
        $goals = SavingsGoal::allForUser($userId);
        // Attach contributions count
        foreach ($goals as &$g) {
            $g['contributions'] = SavingsGoal::contributionsForUser((int) $g['id'], $userId);
        }
        unset($g);
        $this->view('savings/index', [
            'activePage' => 'savings',
            'goals' => $goals,
        ]);
    }

    public function store(): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $name = Validator::sanitize($this->input('name', ''));
        if ($name === '') {
            $this->back('Jina la lengo linahitajika.');
        }
        $target = Validator::amount($this->input('target_amount', '0')) ?? 0;
        if ($target <= 0) {
            $this->back('Lazima kiasi cha lengo kiwe kikubwa kuliko 0.');
        }
        SavingsGoal::create($userId, [
            'name' => $name,
            'target_amount' => $target,
            'priority' => (int) $this->input('priority', 5),
            'is_emergency_fund' => $this->input('is_emergency_fund', '0') === '1' ? 1 : 0,
            'target_date' => $this->input('target_date') ?: null,
            'notes' => Validator::sanitize($this->input('notes', '') ?: ''),
        ]);
        $this->redirect('/savings', 'Lengo la akiba limeongezwa.');
    }

    public function contribute(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $goalId = (int) $id;
        $amount = Validator::amount($this->input('amount', '0'));
        if ($amount === null || $amount <= 0) {
            $this->back('Kiasi lazima kiwe kikubwa kuliko 0.');
        }
        $date = $this->input('contribution_date', date('Y-m-d'));
        $desc = Validator::sanitize($this->input('description', 'Mchango wa akiba'));
        $method = $this->input('payment_method', 'mobile_money');
        TransactionService::contributeToSavings($userId, $goalId, $amount, $date, $desc, $method);
        $this->redirect('/savings', 'Mchango wa akiba umehifadhiwa.');
    }

    public function withdraw(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $goalId = (int) $id;
        $goal = SavingsGoal::findForUser($goalId, $userId);
        if (!$goal) $this->back('Lengo halipatikani.');
        $amount = Validator::amount($this->input('amount', '0'));
        if ($amount === null || $amount <= 0) {
            $this->back('Kiasi lazima kiwe kikubwa kuliko 0.');
        }
        if ($amount > (float) $goal['current_amount']) {
            $this->back('Huwezi kutoa zaidi ya kiasi kilichoko.');
        }
        $date = $this->input('withdrawal_date', date('Y-m-d'));
        $desc = Validator::sanitize($this->input('description', 'Kutoa akiba'));
        $method = $this->input('payment_method', 'mobile_money');
        TransactionService::withdrawFromSavings($userId, $goalId, $amount, $date, $desc, $method);
        $this->redirect('/savings', 'Utoaji wa akiba umehifadhiwa.', 'info');
    }

    public function delete(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        SavingsGoal::delete((int) $id, $userId);
        $this->redirect('/savings', 'Lengo la akiba limefutwa.', 'info');
    }
}

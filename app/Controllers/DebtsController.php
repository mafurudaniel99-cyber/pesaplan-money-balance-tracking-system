<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\{Debt, FinancialObligation, Category};
use App\Services\TransactionService;

final class DebtsController extends Controller
{
    public function index(): void
    {
        $this->auth->requireAuth();
        $this->auth->requireOnboarding();
        $userId = (int) $this->auth->id();
        $debts = Debt::allForUser($userId);
        foreach ($debts as &$d) {
            $d['payments'] = Debt::paymentsForUser((int) $d['id'], $userId);
        }
        unset($d);
        $this->view('debts/index', [
            'activePage' => 'debts',
            'debts' => $debts,
            'obligations' => FinancialObligation::allForUser($userId),
            'categories' => Category::allForUser($userId, 'expense'),
        ]);
    }

    public function store(): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $name = Validator::sanitize($this->input('name', ''));
        if ($name === '') $this->back('Jina la deni linahitajika.');
        $balance = Validator::amount($this->input('outstanding_balance', '0')) ?? 0;
        Debt::create($userId, [
            'name' => $name,
            'original_amount' => Validator::amount($this->input('original_amount', '0')) ?? $balance,
            'outstanding_balance' => $balance,
            'minimum_payment' => Validator::amount($this->input('minimum_payment', '0')) ?? 0,
            'interest_rate_pct' => (float) $this->input('interest_rate_pct', '0'),
            'due_day' => $this->input('due_day') ? (int) $this->input('due_day') : null,
            'next_due_date' => $this->input('next_due_date') ?: null,
            'is_recurring' => $this->input('is_recurring', '0') === '1' ? 1 : 0,
            'notes' => Validator::sanitize($this->input('notes', '') ?: ''),
        ]);
        $this->redirect('/debts', 'Deni limeongezwa.');
    }

    public function pay(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $debtId = (int) $id;
        $amount = Validator::amount($this->input('amount', '0'));
        if ($amount === null || $amount <= 0) {
            $this->back('Kiasi cha malipo lazima kiwe kikubwa kuliko 0.');
        }
        $date = $this->input('payment_date', date('Y-m-d'));
        $method = $this->input('payment_method', 'bank');
        $interest = $this->input('interest_portion') ? Validator::amount($this->input('interest_portion')) : null;
        try {
            TransactionService::payDebt($userId, $debtId, $amount, $date, $interest, $method);
        } catch (\RuntimeException $e) {
            $this->back($e->getMessage());
        }
        $this->redirect('/debts', 'Malipo ya deni yamehifadhiwa.');
    }

    public function delete(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        Debt::delete((int) $id, $userId);
        $this->redirect('/debts', 'Deni limefutwa.', 'info');
    }

    public function storeObligation(): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $name = Validator::sanitize($this->input('name', ''));
        if ($name === '') $this->back('Jina la mjukumu linahitajika.');
        $amount = Validator::amount($this->input('amount', '0')) ?? 0;
        if ($amount <= 0) $this->back('Kiasi lazima kiwe kikubwa kuliko 0.');
        FinancialObligation::create($userId, [
            'name' => $name,
            'amount' => $amount,
            'due_day' => $this->input('due_day') ? (int) $this->input('due_day') : null,
            'next_due_date' => $this->input('next_due_date') ?: null,
            'is_recurring' => $this->input('is_recurring', '1') === '1' ? 1 : 0,
            'category_id' => $this->input('category_id') ? (int) $this->input('category_id') : null,
            'notes' => Validator::sanitize($this->input('notes', '') ?: ''),
        ]);
        $this->redirect('/debts', 'Mjukumuumeongezwa.');
    }

    public function deleteObligation(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        FinancialObligation::delete((int) $id, $userId);
        $this->redirect('/debts', 'Mjukumu umefutwa.', 'info');
    }

    public function markObligationPaid(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        FinancialObligation::markPaid((int) $id, $userId, true);
        $this->redirect('/debts', 'Mjukumu umewekwa kama umelipwa.');
    }
}

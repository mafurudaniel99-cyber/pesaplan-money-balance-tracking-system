<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Core\Database;
use App\Models\Transaction;
use App\Models\Category;
use App\Services\TransactionService;

final class TransactionController extends Controller
{
    public function index(): void
    {
        $this->auth->requireAuth();
        $this->auth->requireOnboarding();
        $userId = (int) $this->auth->id();

        $page = max(1, (int) $this->query('page', '1'));
        $perPage = 20;
        $filters = [
            'from' => $this->query('from'),
            'to'   => $this->query('to'),
            'category_id' => $this->query('category_id'),
            'type' => $this->query('type'),
            'payment_method' => $this->query('payment_method'),
            'q' => $this->query('q'),
            'sort' => $this->query('sort'),
        ];
        $transactions = Transaction::search($userId, $filters, $page, $perPage);
        $total = Transaction::countForUser($userId, $filters);
        $totalPages = max(1, (int) ceil($total / $perPage));

        $this->view('transactions/index', [
            'activePage' => 'transactions',
            'transactions' => $transactions,
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'categories' => Category::allForUser($userId, 'expense'),
            'types' => $this->typeLabels(),
        ]);
    }

    public function create(): void
    {
        $this->auth->requireAuth();
        $userId = (int) $this->auth->id();
        $this->view('transactions/create', [
            'activePage' => 'transactions',
            'categories' => Category::allForUser($userId, 'expense'),
            'incomeCategories' => Category::allForUser($userId, 'income'),
        ]);
    }

    public function store(): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();

        $type = $this->input('type', 'expense');
        $amount = Validator::amount($this->input('amount', '0'));
        if ($amount === null || $amount <= 0) {
            $this->back('Kiasi lazima kiwe kikubwa kuliko 0.');
        }
        $date = $this->input('transaction_date', date('Y-m-d'));
        $description = Validator::sanitize($this->input('description', ''));
        if ($description === '') {
            $this->back('Maelezo yanahitajika.');
        }
        $categoryId = $this->input('category_id') ? (int) $this->input('category_id') : null;
        $method = $this->input('payment_method', 'cash');
        $notes = Validator::sanitize($this->input('notes', '') ?: '');

        switch ($type) {
            case 'expense':
                TransactionService::recordExpense($userId, [
                    'category_id' => $categoryId,
                    'amount' => $amount,
                    'transaction_date' => $date,
                    'description' => $description,
                    'payment_method' => $method,
                    'notes' => $notes,
                ]);
                break;
            case 'salary':
            case 'other_income':
                TransactionService::recordOtherIncome($userId, [
                    'category_id' => $categoryId,
                    'amount' => $amount,
                    'transaction_date' => $date,
                    'description' => $description,
                    'payment_method' => $method,
                    'notes' => $notes,
                ]);
                break;
            case 'refund':
                TransactionService::recordRefund($userId, [
                    'category_id' => $categoryId,
                    'amount' => $amount,
                    'transaction_date' => $date,
                    'description' => $description,
                    'payment_method' => $method,
                    'notes' => $notes,
                ]);
                break;
            default:
                $this->back('Aina ya muamala haijulikani.');
        }
        $this->redirect('/transactions', 'Muamala umehifadhiwa.');
    }

    public function show(string $id): void
    {
        $this->auth->requireAuth();
        $userId = (int) $this->auth->id();
        $tx = Transaction::findForUser((int) $id, $userId);
        if (!$tx) {
            $this->redirect('/transactions', 'Muamala haupatikani.', 'error');
        }
        $this->view('transactions/show', [
            'activePage' => 'transactions',
            'tx' => $tx,
        ]);
    }

    public function edit(string $id): void
    {
        $this->auth->requireAuth();
        $userId = (int) $this->auth->id();
        $tx = Transaction::findForUser((int) $id, $userId);
        if (!$tx) {
            $this->redirect('/transactions', 'Muamala haupatikani.', 'error');
        }
        $this->view('transactions/edit', [
            'activePage' => 'transactions',
            'tx' => $tx,
            'categories' => Category::allForUser($userId, 'expense'),
        ]);
    }

    public function update(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $tx = Transaction::findForUser((int) $id, $userId);
        if (!$tx) {
            $this->back('Muamala haupatikani.');
        }
        $amount = Validator::amount($this->input('amount', '0'));
        if ($amount === null || $amount <= 0) {
            $this->back('Kiasi lazima kiwe kikubwa kuliko 0.');
        }
        Transaction::update((int) $id, $userId, [
            'category_id' => $this->input('category_id') ? (int) $this->input('category_id') : null,
            'type' => $this->input('type', $tx['type']),
            'amount' => $amount,
            'transaction_date' => $this->input('transaction_date', date('Y-m-d')),
            'description' => Validator::sanitize($this->input('description', '')),
            'payment_method' => $this->input('payment_method', 'cash'),
            'notes' => Validator::sanitize($this->input('notes', '') ?: ''),
        ]);
        $this->redirect('/transactions', 'Muamala umehaririwa.');
    }

    public function delete(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        Transaction::softDelete((int) $id, $userId);
        $this->redirect('/transactions', 'Muamala umefutwa.', 'info');
    }

    private function typeLabels(): array
    {
        return [
            'expense'      => 'Matumizi',
            'salary'       => 'Mshahara',
            'other_income' => 'Mapato mengine',
            'savings_in'   => 'Akiba (mchango)',
            'savings_out'  => 'Akiba (utoaji)',
            'debt_repay'   => 'Malipo ya deni',
            'refund'       => 'Rudisha pesa',
            'transfer_in'  => 'Hamisha (kuingia)',
            'transfer_out' => 'Hamisha (kutoka)',
            'adjustment'   => 'Marekebisho',
        ];
    }
}

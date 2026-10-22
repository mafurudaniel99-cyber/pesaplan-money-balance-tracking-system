<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Income;
use App\Services\TransactionService;

final class IncomeController extends Controller
{
    public function index(): void
    {
        $this->auth->requireAuth();
        $this->auth->requireOnboarding();
        $userId = (int) $this->auth->id();
        $this->view('income/index', [
            'activePage' => 'income',
            'incomes' => Income::allForUser($userId),
        ]);
    }

    public function store(): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $amount = Validator::amount($this->input('expected_amount', '0'));
        if ($amount === null || $amount <= 0) {
            $this->back('Kiasi lazima kiwe kikubwa kuliko 0.');
        }
        Income::create($userId, [
            'source' => Validator::sanitize($this->input('source', 'Mshahara')),
            'expected_amount' => $amount,
            'expected_date' => $this->input('expected_date', date('Y-m-d')),
            'is_received' => 0,
            'is_salary' => $this->input('is_salary', '0') === '1' ? 1 : 0,
            'notes' => Validator::sanitize($this->input('notes', '') ?: ''),
        ]);
        $this->redirect('/income', 'Mapato yameongezwa.');
    }

    public function markReceived(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $incomeId = (int) $id;
        $income = Income::findForUser($incomeId, $userId);
        if (!$income) {
            $this->back('Mapato hayapatikani.');
        }
        $amount = Validator::amount($this->input('received_amount', (string) $income['expected_amount'])) ?? (float) $income['expected_amount'];
        $date = $this->input('received_date', date('Y-m-d'));
        $desc = $this->input('description', $income['source'] . ' umepokelewa');
        TransactionService::recordSalaryReceipt($userId, $incomeId, $amount, $date, $desc);
        $this->redirect('/income', 'Mapato yamewekwa kama yamepokelewa.');
    }

    public function delete(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        Income::delete((int) $id, $userId);
        $this->redirect('/income', 'Mapato yamefutwa.', 'info');
    }
}

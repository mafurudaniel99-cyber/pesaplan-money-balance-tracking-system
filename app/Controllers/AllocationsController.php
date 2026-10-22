<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Income;
use App\Models\Category;
use App\Services\AllocationService;

final class AllocationsController extends Controller
{
    public function index(): void
    {
        $this->auth->requireAuth();
        $this->auth->requireOnboarding();
        $userId = (int) $this->auth->id();
        $svc = new AllocationService($userId);
        $this->view('allocations/index', [
            'activePage' => 'allocations',
            'allocations' => $svc->all(),
            'totalsByPurpose' => $svc->totalsByPurpose(),
            'incomes' => Income::allForUser($userId, 30),
            'categories' => Category::allForUser($userId),
        ]);
    }

    public function store(): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $svc = new AllocationService($userId);
        try {
            $svc->create([
                'income_id' => $this->input('income_id') ? (int) $this->input('income_id') : null,
                'category_id' => $this->input('category_id') ? (int) $this->input('category_id') : null,
                'purpose' => $this->input('purpose', 'other'),
                'allocated_amount' => Validator::amount($this->input('allocated_amount', '0')) ?? 0,
                'allocation_date' => $this->input('allocation_date', date('Y-m-d')),
                'notes' => Validator::sanitize($this->input('notes', '') ?: ''),
            ]);
        } catch (\RuntimeException $e) {
            $this->back($e->getMessage());
        }
        $this->redirect('/allocations', 'Ugawaji umehifadhiwa.');
    }

    public function delete(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        (new AllocationService($userId))->delete((int) $id);
        $this->redirect('/allocations', 'Ugawaji umefutwa.', 'info');
    }
}

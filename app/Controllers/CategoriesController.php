<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Category;
use App\Models\AuditLog;

final class CategoriesController extends Controller
{
    public function index(): void
    {
        $this->auth->requireAuth();
        $userId = (int) $this->auth->id();
        $this->view('transactions/categories', [
            'activePage' => 'categories',
            'categories' => Category::allForUserIncludingInactive($userId),
        ]);
    }

    public function store(): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $name = Validator::sanitize($this->input('name', ''));
        if ($name === '') {
            $this->back('Jina la kategoria linahitajika.');
        }
        Category::create($userId, [
            'name' => $name,
            'type' => $this->input('type', 'expense'),
            'icon' => $this->input('icon', 'fa-tag'),
            'color' => $this->input('color', '#4a6fa5'),
            'kind' => $this->input('kind', 'other'),
            'sort_order' => (int) $this->input('sort_order', 0),
        ]);
        AuditLog::write($userId, 'category_create', 'category', null, ['name' => $name]);
        $this->redirect('/categories', 'Kategoria imeongezwa.');
    }

    public function update(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $cid = (int) $id;
        $name = Validator::sanitize($this->input('name', ''));
        if ($name === '') {
            $this->back('Jina la kategoria linahitajika.');
        }
        Category::update($cid, $userId, [
            'name' => $name,
            'icon' => $this->input('icon', 'fa-tag'),
            'color' => $this->input('color', '#4a6fa5'),
            'kind' => $this->input('kind', 'other'),
            'sort_order' => (int) $this->input('sort_order', 0),
        ]);
        $this->redirect('/categories', 'Kategoria imerekebishwa.');
    }

    public function toggle(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        Category::toggleActive((int) $id, $userId);
        $this->redirect('/categories', 'Hali ya kategoria imebadilishwa.');
    }
}

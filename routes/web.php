<?php
/**
 * PesaPlan — Route definitions.
 * Routes are declared against $router (instance of App\Core\Router).
 *
 * Middleware pattern: controllers call $this->auth->requireAuth() /
 * $this->auth->requireOnboarding() as needed at the top of their actions.
 */

use App\Controllers\{
    AuthController,
    DashboardController,
    IncomeController,
    TransactionController,
    BudgetController,
    SavingsController,
    DebtsController,
    AllocationsController,
    PlannedPurchasesController,
    ReviewsController,
    ReportsController,
    EducationController,
    NotificationsController,
    SettingsController,
    CategoriesController
};

// Auth
$router->get('/',        [AuthController::class, 'landing']);
$router->get('/login',   [AuthController::class, 'loginForm']);
$router->post('/login',  [AuthController::class, 'login']);
$router->get('/register',[AuthController::class, 'registerForm']);
$router->post('/register',[AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/onboarding', [AuthController::class, 'onboardingForm']);
$router->post('/onboarding', [AuthController::class, 'onboardingSave']);

// Dashboard
$router->get('/dashboard', [DashboardController::class, 'index']);

// Income
$router->get('/income',          [IncomeController::class, 'index']);
$router->post('/income',         [IncomeController::class, 'store']);
$router->post('/income/{id}/receive', [IncomeController::class, 'markReceived']);
$router->post('/income/{id}/delete',  [IncomeController::class, 'delete']);

// Transactions
$router->get('/transactions',          [TransactionController::class, 'index']);
$router->get('/transactions/create',   [TransactionController::class, 'create']);
$router->post('/transactions',         [TransactionController::class, 'store']);
$router->get('/transactions/{id}',     [TransactionController::class, 'show']);
$router->get('/transactions/{id}/edit',[TransactionController::class, 'edit']);
$router->post('/transactions/{id}',    [TransactionController::class, 'update']);
$router->post('/transactions/{id}/delete', [TransactionController::class, 'delete']);

// Categories
$router->get('/categories',          [CategoriesController::class, 'index']);
$router->post('/categories',         [CategoriesController::class, 'store']);
$router->post('/categories/{id}/update', [CategoriesController::class, 'update']);
$router->post('/categories/{id}/toggle', [CategoriesController::class, 'toggle']);

// Budgets
$router->get('/budgets',     [BudgetController::class, 'index']);
$router->post('/budgets',    [BudgetController::class, 'store']);
$router->post('/budgets/{id}/items', [BudgetController::class, 'storeItem']);
$router->post('/budgets/items/{id}/delete', [BudgetController::class, 'deleteItem']);

// Savings
$router->get('/savings',           [SavingsController::class, 'index']);
$router->post('/savings',          [SavingsController::class, 'store']);
$router->post('/savings/{id}/contribute', [SavingsController::class, 'contribute']);
$router->post('/savings/{id}/withdraw', [SavingsController::class, 'withdraw']);
$router->post('/savings/{id}/delete',   [SavingsController::class, 'delete']);

// Debts & obligations
$router->get('/debts',          [DebtsController::class, 'index']);
$router->post('/debts',         [DebtsController::class, 'store']);
$router->post('/debts/{id}/pay',[DebtsController::class, 'pay']);
$router->post('/debts/{id}/delete', [DebtsController::class, 'delete']);
$router->post('/obligations',   [DebtsController::class, 'storeObligation']);
$router->post('/obligations/{id}/delete', [DebtsController::class, 'deleteObligation']);
$router->post('/obligations/{id}/mark-paid', [DebtsController::class, 'markObligationPaid']);

// Allocations
$router->get('/allocations',  [AllocationsController::class, 'index']);
$router->post('/allocations', [AllocationsController::class, 'store']);
$router->post('/allocations/{id}/delete', [AllocationsController::class, 'delete']);

// Planned purchases (Kagua Kabla ya Kutumia)
$router->get('/planned-purchases', [PlannedPurchasesController::class, 'index']);
$router->post('/planned-purchases', [PlannedPurchasesController::class, 'store']);
$router->post('/planned-purchases/{id}/approve', [PlannedPurchasesController::class, 'approve']);
$router->post('/planned-purchases/{id}/reject',  [PlannedPurchasesController::class, 'reject']);
$router->post('/planned-purchases/{id}/complete',[PlannedPurchasesController::class, 'complete']);
$router->post('/planned-purchases/{id}/delete',  [PlannedPurchasesController::class, 'delete']);

// Reviews
$router->get('/reviews',  [ReviewsController::class, 'index']);
$router->post('/reviews', [ReviewsController::class, 'store']);
$router->post('/reviews/{id}/complete', [ReviewsController::class, 'complete']);

// Reports
$router->get('/reports', [ReportsController::class, 'index']);
$router->get('/reports/export', [ReportsController::class, 'exportCsv']);

// Education
$router->get('/education',         [EducationController::class, 'index']);
$router->get('/education/{slug}',  [EducationController::class, 'lesson']);

// Notifications
$router->get('/notifications',          [NotificationsController::class, 'index']);
$router->post('/notifications/{id}/read',[NotificationsController::class, 'markRead']);
$router->post('/notifications/read-all', [NotificationsController::class, 'markAllRead']);

// Settings
$router->get('/settings',  [SettingsController::class, 'index']);
$router->post('/settings', [SettingsController::class, 'update']);
$router->post('/settings/password', [SettingsController::class, 'changePassword']);
$router->post('/settings/delete-account', [SettingsController::class, 'deleteAccount']);

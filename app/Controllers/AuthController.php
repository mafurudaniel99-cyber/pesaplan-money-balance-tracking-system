<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Csrf;
use App\Core\Validator;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\NotificationService;

final class AuthController extends Controller
{
    public function landing(): void
    {
        if ($this->auth->check()) {
            $this->redirect('/dashboard');
        }
        $this->viewAuth('auth/landing', ['activePage' => 'landing']);
    }

    public function loginForm(): void
    {
        if ($this->auth->check()) {
            $this->redirect('/dashboard');
        }
        $this->viewAuth('auth/login', ['activePage' => 'login']);
    }

    public function login(): void
    {
        $this->requireCsrf();
        $email = $this->input('email', '');
        $password = $this->input('password', '');
        $result = $this->auth->attempt($email, $password);
        if (!$result['ok']) {
            $this->redirect('/login', $result['error'], 'error');
        }
        $user = $result['user'];
        AuditLog::write((int) $user['id'], 'login', 'user', (string) $user['id']);
        if (!$user['onboarding_complete']) {
            $this->redirect('/onboarding');
        }
        $this->redirect('/dashboard', 'Karibu tena, ' . $user['name'] . '!');
    }

    public function registerForm(): void
    {
        if ($this->auth->check()) {
            $this->redirect('/dashboard');
        }
        $this->viewAuth('auth/register', ['activePage' => 'register']);
    }

    public function register(): void
    {
        $this->requireCsrf();
        $name = $this->input('name', '');
        $email = $this->input('email', '');
        $password = $this->input('password', '');
        $confirm = $this->input('password_confirm', '');
        if ($password !== $confirm) {
            $this->redirect('/register', 'Nenosiri na uthibitisho wake hazifanani.', 'error');
        }
        $result = $this->auth->register($name, $email, $password);
        if (!$result['ok']) {
            $this->redirect('/register', $result['error'], 'error');
        }
        AuditLog::write($result['user_id'], 'register', 'user', (string) $result['user_id']);
        $this->redirect('/onboarding', 'Akaunti imeundwa. Hebu tukamilishe usanidi wako.');
    }

    public function logout(): void
    {
        $this->requireCsrf();
        $userId = $this->auth->id();
        if ($userId) AuditLog::write($userId, 'logout', 'user', (string) $userId);
        $this->auth->logout();
        $this->redirect('/login', 'Umetoka kwa usalama.', 'info');
    }

    public function onboardingForm(): void
    {
        $this->auth->requireAuth();
        $user = $this->auth->user();
        if ($user['onboarding_complete']) {
            $this->redirect('/dashboard');
        }
        $settings = User::settings((int) $user['id']);
        $this->view('auth/onboarding', [
            'activePage' => 'onboarding',
            'settings' => $settings,
            'layout' => 'layouts/auth',
        ]);
    }

    public function onboardingSave(): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $user = $this->auth->user();
        $userId = (int) $user['id'];

        $salary = Validator::amount($this->input('expected_salary', '0')) ?? 0;
        $day = (int) $this->input('expected_salary_day', 28);
        if ($day < 1) $day = 1;
        if ($day > 28) $day = 28;

        User::updateSettings($userId, [
            'expected_salary' => $salary,
            'expected_salary_day' => $day,
            'cooling_off_hours' => 24,
            'notify_budget_threshold' => 1,
            'notify_upcoming_bills' => 1,
            'notify_review_reminders' => 1,
            'budget_threshold_pct' => 80,
        ]);

        // Optional: recurring essential expenses (name|amount per line)
        $recurring = $this->input('recurring', '');
        if (!empty($recurring)) {
            $lines = explode("\n", $recurring);
            foreach ($lines as $line) {
                $parts = explode('|', $line);
                if (count($parts) === 2) {
                    $name = trim($parts[0]);
                    $amt = Validator::amount($parts[1]);
                    if ($name && $amt && $amt > 0) {
                        \App\Models\FinancialObligation::create($userId, [
                            'name' => $name,
                            'amount' => $amt,
                            'is_recurring' => 1,
                            'next_due_date' => null,
                            'due_day' => null,
                            'category_id' => null,
                            'notes' => 'Imeongezwa wakati wa usanidi',
                        ]);
                    }
                }
            }
        }

        // Optional: debts
        $debts = $this->input('debts', '');
        if (!empty($debts)) {
            $lines = explode("\n", $debts);
            foreach ($lines as $line) {
                $parts = explode('|', $line);
                if (count($parts) >= 2) {
                    $name = trim($parts[0]);
                    $balance = Validator::amount($parts[1]) ?? 0;
                    $minimum = isset($parts[2]) ? (Validator::amount($parts[2]) ?? 0) : 0;
                    if ($name && $balance > 0) {
                        \App\Models\Debt::create($userId, [
                            'name' => $name,
                            'original_amount' => $balance,
                            'outstanding_balance' => $balance,
                            'minimum_payment' => $minimum,
                            'interest_rate_pct' => 0,
                            'due_day' => null,
                            'next_due_date' => null,
                            'is_recurring' => 0,
                            'notes' => 'Imeongezwa wakati wa usanidi',
                        ]);
                    }
                }
            }
        }

        // Optional: savings target
        $savingsTarget = Validator::amount($this->input('savings_target', '0')) ?? 0;
        if ($savingsTarget > 0) {
            \App\Models\SavingsGoal::create($userId, [
                'name' => 'Akiba ya kuanzia',
                'target_amount' => $savingsTarget,
                'priority' => 5,
                'is_emergency_fund' => 0,
                'target_date' => null,
                'notes' => 'Imeongezwa wakati wa usanidi',
            ]);
        }

        // Create an income expectation for the current month if salary set
        if ($salary > 0) {
            $today = date('Y-m-d');
            $yearMonth = substr($today, 0, 7);
            $expectedDate = substr($today, 0, 8) . sprintf('%02d', $day);
            \App\Models\Income::create($userId, [
                'source' => 'Mshahara',
                'expected_amount' => $salary,
                'expected_date' => $expectedDate,
                'is_received' => 0,
                'is_salary' => 1,
                'notes' => 'Mshahara unaotarajiwa',
            ]);
        }

        User::markOnboardingComplete($userId);
        AuditLog::write($userId, 'onboarding_complete', 'user', (string) $userId, ['salary' => $salary]);
        $this->redirect('/dashboard', 'Karibu PesaPlan! Anza kwa kuangalia dashibodi yako.');
    }
}

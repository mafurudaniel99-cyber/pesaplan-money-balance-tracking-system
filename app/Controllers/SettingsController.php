<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\{User, AuditLog};

final class SettingsController extends Controller
{
    public function index(): void
    {
        $this->auth->requireAuth();
        $userId = (int) $this->auth->id();
        $user = User::find($userId);
        $settings = User::settings($userId);
        $this->view('settings/index', [
            'activePage' => 'settings',
            'user' => $user,
            'settings' => $settings,
        ]);
    }

    public function update(): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $name = Validator::sanitize($this->input('name', ''));
        if ($name === '') $this->back('Jina linahitajika.');
        $phone = Validator::sanitize($this->input('phone', '') ?: '');
        User::updateProfile($userId, ['name' => $name, 'phone' => $phone]);
        User::updateSettings($userId, [
            'expected_salary' => Validator::amount($this->input('expected_salary', '0')) ?? 0,
            'expected_salary_day' => (int) $this->input('expected_salary_day', 28),
            'cooling_off_hours' => (int) $this->input('cooling_off_hours', 24),
            'notify_budget_threshold' => $this->input('notify_budget_threshold', '0') === '1',
            'notify_upcoming_bills' => $this->input('notify_upcoming_bills', '0') === '1',
            'notify_review_reminders' => $this->input('notify_review_reminders', '0') === '1',
            'budget_threshold_pct' => (int) $this->input('budget_threshold_pct', 80),
        ]);
        AuditLog::write($userId, 'settings_update', 'user', (string) $userId);
        $this->redirect('/settings', 'Mipangilio imehifadhiwa.');
    }

    public function changePassword(): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $current = $this->input('current_password', '');
        $new = $this->input('new_password', '');
        $confirm = $this->input('new_password_confirm', '');
        $user = User::find($userId);
        if (!password_verify($current, $user['password_hash'])) {
            $this->back('Nenosiri la sasa si sahihi.');
        }
        if (strlen($new) < 8) {
            $this->back('Nenosiri jipya lazima liwe na herufi 8+.');
        }
        if ($new !== $confirm) {
            $this->back('Nenosiri jipya na uthibitisho wake hazifanani.');
        }
        User::updatePassword($userId, password_hash($new, PASSWORD_DEFAULT));
        AuditLog::write($userId, 'password_change', 'user', (string) $userId);
        $this->redirect('/settings', 'Nenosiri limebadilishwa.');
    }

    public function deleteAccount(): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $confirm = $this->input('confirm', '');
        if (strtoupper($confirm) !== 'FUTA') {
            $this->back('Andika "FUTA" kuthibitisha.');
        }
        AuditLog::write($userId, 'account_deleted', 'user', (string) $userId);
        User::delete($userId);
        $this->auth->logout();
        $this->redirect('/login', 'Akaunti imefutwa.', 'info');
    }
}

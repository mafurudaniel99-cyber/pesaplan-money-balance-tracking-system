<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notification;

final class NotificationsController extends Controller
{
    public function index(): void
    {
        $this->auth->requireAuth();
        $userId = (int) $this->auth->id();
        $this->view('notifications/index', [
            'activePage' => 'notifications',
            'notifications' => Notification::allForUser($userId, 100),
        ]);
    }

    public function markRead(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        Notification::markRead((int) $id, $userId);
        $this->redirect('/notifications');
    }

    public function markAllRead(): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        Notification::markAllRead($userId);
        $this->redirect('/notifications', 'Arifa zote zimewekwa kama zimesomwa.');
    }
}

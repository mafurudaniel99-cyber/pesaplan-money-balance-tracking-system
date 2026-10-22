<?php
/**
 * PesaPlan — Authentication.
 * Wraps session-based authentication, rate limiting, password hashing.
 */

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function check(): bool
    {
        return Session::has('user_id');
    }

    public function id(): ?int
    {
        return Session::has('user_id') ? (int) Session::get('user_id') : null;
    }

    public function user(): ?array
    {
        if (!$this->check()) {
            return null;
        }
        return User::find($this->id());
    }

    public function attempt(string $email, string $password): array
    {
        $email = strtolower(trim($email));
        $user = User::findByEmail($email);
        if (!$user || !$user['is_active']) {
            return ['ok' => false, 'error' => 'Barua pepe au nenosiri si sahihi.'];
        }
        // Rate limit
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            return ['ok' => false, 'error' => 'Akaunti imefungwa kwa muda. Jaribu tena baadaye.'];
        }
        if (!password_verify($password, $user['password_hash'])) {
            User::incrementFailedLogin($user['id'], $this->config['login_max_attempts'] ?? 5, $this->config['login_lock_minutes'] ?? 15);
            return ['ok' => false, 'error' => 'Barua pepe au nenosiri si sahihi.'];
        }
        // Success
        User::resetFailedLogin($user['id']);
        Session::regenerate();
        Session::set('user_id', (int) $user['id']);
        Session::set('login_at', time());
        Csrf::rotate();
        User::updateLastLogin($user['id']);
        return ['ok' => true, 'user' => $user];
    }

    public function register(string $name, string $email, string $password): array
    {
        $name = trim($name);
        $email = strtolower(trim($email));
        if (strlen($name) < 2) {
            return ['ok' => false, 'error' => 'Jina ni fupi sana.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'Barua pepe si sahihi.'];
        }
        if (User::findByEmail($email)) {
            return ['ok' => false, 'error' => 'Barua pepe tayari inatumika.'];
        }
        if (strlen($password) < 8) {
            return ['ok' => false, 'error' => 'Nenosiri linapaswa kuwa na herufi 8 au zaidi.'];
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $userId = User::create([
            'name' => $name,
            'email' => $email,
            'password_hash' => $hash,
        ]);
        if (!$userId) {
            return ['ok' => false, 'error' => 'Imeshindwa kuunda akaunti. Jaribu tena.'];
        }
        Session::regenerate();
        Session::set('user_id', (int) $userId);
        Session::set('login_at', time());
        Csrf::rotate();
        return ['ok' => true, 'user_id' => $userId];
    }

    public function logout(): void
    {
        Session::destroy();
    }

    public function requireAuth(): void
    {
        if (!$this->check()) {
            header('Location: /login');
            exit;
        }
    }

    public function requireOnboarding(): void
    {
        $user = $this->user();
        if ($user && !$user['onboarding_complete']) {
            header('Location: /onboarding');
            exit;
        }
    }
}

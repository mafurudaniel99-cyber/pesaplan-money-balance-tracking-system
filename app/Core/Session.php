<?php
/**
 * PesaPlan — Session helper with secure defaults.
 */

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(array $config): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $name = $config['session_name'] ?? 'pesaplan_session';
        session_name($name);
        session_set_cookie_params([
            'lifetime' => $config['session_lifetime'] ?? 7200,
            'path'     => '/',
            'secure'   => $config['session_secure'] ?? false,
            'httponly' => $config['session_httponly'] ?? true,
            'samesite' => $config['session_samesite'] ?? 'Lax',
        ]);
        session_start();
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, $value = null)
    {
        if ($value === null) {
            $val = self::get('_flash:' . $key);
            self::forget('_flash:' . $key);
            return $val;
        }
        self::set('_flash:' . $key, $value);
    }

    public static function hasFlash(string $key): bool
    {
        return self::has('_flash:' . $key);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}

<?php
/**
 * PesaPlan — CSRF token helper.
 * Token is per-session, rotated on login.
 */

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        if (!Session::has('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }

    public static function verify(?string $token): bool
    {
        if (!$token || !Session::has('csrf_token')) {
            return false;
        }
        return hash_equals(Session::get('csrf_token'), $token);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function meta(): string
    {
        return '<meta name="csrf-token" content="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function rotate(): void
    {
        Session::set('csrf_token', bin2hex(random_bytes(32)));
    }
}

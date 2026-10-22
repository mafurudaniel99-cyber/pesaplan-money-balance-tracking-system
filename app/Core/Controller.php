<?php
/**
 * PesaPlan — Base Controller.
 * Provides view rendering, JSON response, redirect, and CSRF gating.
 */

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected Auth $auth;
    protected array $config;

    public function __construct(Auth $auth, array $config)
    {
        $this->auth = $auth;
        $this->config = $config;
    }

    /**
     * Render a view with the application layout.
     */
    protected function view(string $path, array $data = []): void
    {
        $data['auth'] = $this->auth;
        $data['config'] = $this->config;
        $data['currentUser'] = $this->auth->user();
        $data['csrfToken'] = Csrf::token();
        $data['flash'] = [
            'success' => Session::flash('success'),
            'error'   => Session::flash('error'),
            'info'    => Session::flash('info'),
        ];
        $data['activePage'] = $data['activePage'] ?? '';
        $viewFile = dirname(__DIR__) . '/Views/' . $path . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            error_log("View not found: $viewFile");
            echo 'Hitilafu kwenye mfumo: ukurasa haupatikani.';
            return;
        }
        // Extract so views can use variables directly.
        extract($data, EXTR_SKIP);
        $layout = $data['layout'] ?? 'layouts/app';
        $layoutFile = dirname(__DIR__) . '/Views/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            http_response_code(500);
            echo 'Layout haipatikani.';
            return;
        }
        // Capture view content, then embed in layout as $content
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        include $layoutFile;
    }

    /**
     * Render a view with the auth (no-app) layout.
     */
    protected function viewAuth(string $path, array $data = []): void
    {
        $data['layout'] = 'layouts/auth';
        $data['auth'] = $this->auth;
        $data['config'] = $this->config;
        $data['csrfToken'] = Csrf::token();
        $data['flash'] = [
            'success' => Session::flash('success'),
            'error'   => Session::flash('error'),
            'info'    => Session::flash('info'),
        ];
        $this->view($path, $data);
    }

    protected function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $path, ?string $flash = null, string $type = 'success'): void
    {
        if ($flash !== null) {
            Session::flash($type, $flash);
        }
        header('Location: ' . $path);
        exit;
    }

    protected function back(?string $flash = null, string $type = 'error'): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer, $flash, $type);
    }

    /**
     * Verify CSRF on POST/PUT/DELETE. Call at the start of mutation handlers.
     */
    protected function requireCsrf(): void
    {
        $token = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if (!Csrf::verify($token)) {
            http_response_code(419);
            die('Kitambulisho cha CSRF hakipatikani. Tafadhali onyesha upya ukurasa na ujaribu tena.');
        }
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    protected function query(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
}

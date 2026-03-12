<?php

declare(strict_types=1);

namespace ToyShop\Controllers;

use ToyShop\Infrastructure\Response;
use ToyShop\Middleware\AuthMiddleware;
use ToyShop\Services\AuthService;

final class AuthController
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function showLogin(): void
    {
        $user = AuthMiddleware::requireLogin();
        if ($user !== null) {
            $base = rtrim((string) \ToyShop\Infrastructure\Env::get('APP_URL', ''), '/');
            Response::redirect(($user['role'] ?? '') === 'admin' ? $base . '/admin/dashboard' : $base . '/');
            return;
        }
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function showRegister(): void
    {
        if (AuthMiddleware::requireLogin() !== null) {
            Response::redirect('/');
            return;
        }
        require __DIR__ . '/../Views/auth/register.php';
    }

    public function login(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = $this->postInput();
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        if ($email === '' || $password === '') {
            Response::jsonError('VALIDATION', 'E-posta ve şifre gerekli.');
            return;
        }
        $user = $this->authService->loginCustomer($email, $password);
        if ($user === null) {
            $user = $this->authService->loginAdmin($email, $password);
        }
        if ($user === null) {
            Response::jsonError('AUTH', 'E-posta veya şifre hatalı.');
            return;
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_name(\ToyShop\Infrastructure\Env::get('SESSION_NAME', 'toyshop_session'));
            session_start();
        }
        $_SESSION['user'] = $user;
        $base = rtrim((string) \ToyShop\Infrastructure\Env::get('APP_URL', ''), '/');
        if (($user['role'] ?? '') === 'admin') {
            $redirect = $base . '/admin/dashboard';
        } else {
            $redirectParam = isset($input['redirect']) ? trim((string) $input['redirect']) : '';
            if ($redirectParam !== '' && str_starts_with($redirectParam, '/') && !str_starts_with($redirectParam, '//')) {
                $redirect = $base . $redirectParam;
            } else {
                $redirect = $base . '/';
            }
        }
        Response::jsonSuccess(['redirect' => $redirect]);
    }

    public function register(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = $this->postInput();
        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        if ($name === '' || $email === '' || $password === '') {
            Response::jsonError('VALIDATION', 'Ad, e-posta ve şifre gerekli.');
            return;
        }
        if (strlen($password) < 6) {
            Response::jsonError('VALIDATION', 'Şifre en az 6 karakter olmalı.');
            return;
        }
        try {
            $user = $this->authService->register($name, $email, $password);
        } catch (\Throwable $e) {
            \ToyShop\Infrastructure\Logger::error('Auth register error', ['message' => $e->getMessage()]);
            Response::jsonError('REGISTER', $e->getMessage());
            return;
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_name(\ToyShop\Infrastructure\Env::get('SESSION_NAME', 'toyshop_session'));
            session_start();
        }
        $_SESSION['user'] = $user;
        $base = rtrim((string) \ToyShop\Infrastructure\Env::get('APP_URL', ''), '/');
        Response::jsonSuccess(['redirect' => $base . '/']);
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(\ToyShop\Infrastructure\Env::get('SESSION_NAME', 'toyshop_session'));
            session_start();
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
        }
        session_destroy();
        Response::redirect('/');
    }

    /** FormData veya JSON body'den POST verisi (canlı/proxy uyumu). */
    private function postInput(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }
        $raw = file_get_contents('php://input');
        if ($raw !== false && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }
}

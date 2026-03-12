<?php

declare(strict_types=1);

namespace ToyShop\Controllers\Admin;

use ToyShop\Infrastructure\Env;
use ToyShop\Infrastructure\Response;
use ToyShop\Middleware\AdminMiddleware;
use ToyShop\Services\AuthService;

final class AdminAuthController
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function showLogin(): void
    {
        if (AdminMiddleware::requireAdmin() !== null) {
            Response::redirect(rtrim(Env::get('APP_URL', ''), '/') . '/admin/dashboard');
            return;
        }
        require __DIR__ . '/../../Views/admin/login.php';
    }

    public function login(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if ($email === '' || $password === '') {
            Response::jsonError('VALIDATION', 'E-posta ve şifre gerekli.');
            return;
        }
        $user = $this->authService->loginAdmin($email, $password);
        if ($user === null) {
            Response::jsonError('AUTH', 'E-posta veya şifre hatalı.');
            return;
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_name(Env::get('SESSION_NAME', 'toyshop_session'));
            session_start();
        }
        $_SESSION['user'] = $user;
        $redirect = rtrim(Env::get('APP_URL', ''), '/') . '/admin/dashboard';
        Response::jsonSuccess(['redirect' => $redirect]);
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(Env::get('SESSION_NAME', 'toyshop_session'));
            session_start();
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
        }
        session_destroy();
        Response::redirect(rtrim(Env::get('APP_URL', ''), '/') . '/login');
    }
}

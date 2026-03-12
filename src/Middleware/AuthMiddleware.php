<?php

declare(strict_types=1);

namespace ToyShop\Middleware;

final class AuthMiddleware
{
    public static function requireLogin(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        if ($user === null || !is_array($user)) {
            return null;
        }
        return $user;
    }

    public static function guest(): bool
    {
        return self::requireLogin() === null;
    }
}

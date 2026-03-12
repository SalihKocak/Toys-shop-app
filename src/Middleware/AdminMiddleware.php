<?php

declare(strict_types=1);

namespace ToyShop\Middleware;

final class AdminMiddleware
{
    public static function requireAdmin(): ?array
    {
        $user = AuthMiddleware::requireLogin();
        if ($user === null) {
            return null;
        }
        if (($user['role'] ?? '') !== 'admin') {
            return null;
        }
        return $user;
    }
}

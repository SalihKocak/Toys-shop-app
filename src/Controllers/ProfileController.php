<?php

declare(strict_types=1);

namespace ToyShop\Controllers;

use ToyShop\Infrastructure\Env;
use ToyShop\Infrastructure\Response;
use ToyShop\Middleware\AuthMiddleware;
use ToyShop\Services\OrderService;
use ToyShop\Services\ProductService;

final class ProfileController
{
    private const RECENTLY_VIEWED_MAX = 12;

    public function __construct(
        private OrderService $orderService,
        private ProductService $productService
    ) {}

    public function index(): void
    {
        $user = AuthMiddleware::requireLogin();
        if ($user === null) {
            Response::redirect(rtrim(Env::get('APP_URL', ''), '/') . '/login');
            return;
        }

        $section = isset($_GET['section']) ? trim((string) $_GET['section']) : 'profil';
        if (!in_array($section, ['profil', 'orders', 'recent'], true)) {
            $section = 'profil';
        }

        $orders = [];
        $recentProducts = [];

        if (($user['role'] ?? '') === 'customer') {
            $orders = $this->orderService->listByUser($user['id']);
        }

        $recentIds = $_SESSION['recently_viewed'] ?? [];
        if (is_array($recentIds)) {
            foreach (array_slice($recentIds, 0, self::RECENTLY_VIEWED_MAX) as $id) {
                $p = $this->productService->getActiveById($id);
                if ($p !== null) {
                    $recentProducts[] = $p;
                }
            }
        }

        require __DIR__ . '/../Views/profile/index.php';
    }
}

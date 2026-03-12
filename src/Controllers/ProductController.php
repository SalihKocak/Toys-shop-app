<?php

declare(strict_types=1);

namespace ToyShop\Controllers;

use ToyShop\Services\ProductService;

final class ProductController
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function index(): void
    {
        $sort = isset($_GET['sort']) ? trim((string) $_GET['sort']) : null;
        $sort = ($sort === 'price_asc' || $sort === 'price_desc') ? $sort : null;
        $products = $this->productService->listActive(null, null, $sort);
        require __DIR__ . '/../Views/products/index.php';
    }

    public function show(string $id): void
    {
        $product = $this->productService->getActiveById($id);
        if ($product === null) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }
        $this->trackRecentlyViewed($id);
        require __DIR__ . '/../Views/products/show.php';
    }

    private function trackRecentlyViewed(string $productId): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        $list = $_SESSION['recently_viewed'] ?? [];
        if (!is_array($list)) {
            $list = [];
        }
        $list = array_values(array_diff($list, [$productId]));
        array_unshift($list, $productId);
        $_SESSION['recently_viewed'] = array_slice($list, 0, 12);
    }
}

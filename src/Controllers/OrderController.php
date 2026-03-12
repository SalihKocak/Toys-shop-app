<?php

declare(strict_types=1);

namespace ToyShop\Controllers;

use ToyShop\Infrastructure\Response;
use ToyShop\Middleware\AuthMiddleware;
use ToyShop\Services\OrderService;
use ToyShop\Services\ProductService;

final class OrderController
{
    public function __construct(
        private OrderService $orderService,
        private ProductService $productService
    ) {}

    public function checkout(): void
    {
        $user = AuthMiddleware::requireLogin();
        if ($user === null) {
            Response::redirect('/login');
            return;
        }
        $cart = CartController::getCart();
        $items = [];
        $total = 0.0;
        foreach ($cart as $productId => $qty) {
            $product = $this->productService->getActiveById($productId);
            if ($product !== null && $qty > 0) {
                $sub = $product['price'] * $qty;
                $items[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'subtotal' => $sub,
                ];
                $total += $sub;
            }
        }
        if ($items === []) {
            Response::redirect('/cart');
            return;
        }
        require __DIR__ . '/../Views/checkout/index.php';
    }

    public function create(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $user = AuthMiddleware::requireLogin();
        if ($user === null) {
            Response::jsonError('AUTH', 'Giriş yapmalısınız.', 401);
            return;
        }
        $cart = CartController::getCart();
        $orderItems = [];
        $total = 0.0;
        foreach ($cart as $productId => $qty) {
            $product = $this->productService->getActiveById($productId);
            if ($product !== null && $qty > 0) {
                $orderItems[] = [
                    'productId' => $productId,
                    'nameSnapshot' => $product['name'],
                    'priceSnapshot' => $product['price'],
                    'qty' => $qty,
                ];
                $total += $product['price'] * $qty;
            }
        }
        if ($orderItems === []) {
            Response::jsonError('CART', 'Sepet boş.');
            return;
        }
        try {
            $order = $this->orderService->create($user['id'], $orderItems, $total);
        } catch (\Throwable $e) {
            \ToyShop\Infrastructure\Logger::error('Order create error', ['message' => $e->getMessage()]);
            Response::jsonError('ORDER', 'Sipariş oluşturulamadı.');
            return;
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['cart'] = [];
        }
        Response::jsonSuccess(['orderId' => $order['id'], 'redirect' => '/']);
    }
}

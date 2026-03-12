<?php

declare(strict_types=1);

namespace ToyShop\Controllers;

use ToyShop\Services\ProductService;

final class CartController
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function index(): void
    {
        $cart = $this->getCart();
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
        require __DIR__ . '/../Views/cart/index.php';
    }

    public function add(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $productId = trim((string) ($_POST['productId'] ?? ''));
        $qty = (int) ($_POST['qty'] ?? 1);
        if ($productId === '') {
            \ToyShop\Infrastructure\Response::jsonError('VALIDATION', 'Ürün gerekli.');
            return;
        }
        $product = $this->productService->getActiveById($productId);
        if ($product === null) {
            \ToyShop\Infrastructure\Response::jsonError('NOT_FOUND', 'Ürün bulunamadı.');
            return;
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_name(\ToyShop\Infrastructure\Env::get('SESSION_NAME', 'toyshop_session'));
            session_start();
        }
        $cart = $_SESSION['cart'] ?? [];
        $cart[$productId] = ($cart[$productId] ?? 0) + max(1, $qty);
        $_SESSION['cart'] = $cart;
        \ToyShop\Infrastructure\Response::jsonSuccess(['cartCount' => array_sum($cart)]);
    }

    public function update(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $productId = trim((string) ($_POST['productId'] ?? ''));
        $qty = (int) ($_POST['qty'] ?? 0);
        if ($productId === '') {
            \ToyShop\Infrastructure\Response::jsonError('VALIDATION', 'Ürün gerekli.');
            return;
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_name(\ToyShop\Infrastructure\Env::get('SESSION_NAME', 'toyshop_session'));
            session_start();
        }
        $cart = $_SESSION['cart'] ?? [];
        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $qty;
        }
        $_SESSION['cart'] = $cart;
        \ToyShop\Infrastructure\Response::jsonSuccess(['cartCount' => array_sum($cart)]);
    }

    /** @return array<string, int> */
    public static function getCart(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(\ToyShop\Infrastructure\Env::get('SESSION_NAME', 'toyshop_session'));
            session_start();
        }
        return $_SESSION['cart'] ?? [];
    }
}

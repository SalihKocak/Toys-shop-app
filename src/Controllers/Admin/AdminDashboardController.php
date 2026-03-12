<?php

declare(strict_types=1);

namespace ToyShop\Controllers\Admin;

use ToyShop\Infrastructure\Mongo;
use ToyShop\Services\OrderService;

final class AdminDashboardController
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(): void
    {
        $orders = $this->orderService->listAll();
        $recentOrders = array_slice($orders, 0, 10);
        $productsCount = Mongo::collection('products')->countDocuments([]);
        $usersCount = Mongo::collection('users')->countDocuments([]);
        $openChatsCount = Mongo::collection('chat_threads')->countDocuments(['status' => 'open']);
        require __DIR__ . '/../../Views/admin/dashboard.php';
    }
}

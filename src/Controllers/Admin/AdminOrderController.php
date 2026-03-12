<?php

declare(strict_types=1);

namespace ToyShop\Controllers\Admin;

use ToyShop\Infrastructure\Response;
use ToyShop\Services\OrderService;

final class AdminOrderController
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(): void
    {
        $orders = $this->orderService->listAll();
        require __DIR__ . '/../../Views/admin/orders/index.php';
    }

    public function updateStatus(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input');
        $data = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
        $status = isset($data['status']) && is_string($data['status']) ? trim($data['status']) : '';
        $allowed = ['created', 'paid', 'shipped', 'cancelled'];
        if ($status === '' || !in_array($status, $allowed, true)) {
            Response::jsonError('VALIDATION', 'Geçersiz durum.');
            return;
        }
        $ok = $this->orderService->updateStatus($id, $status);
        if (!$ok) {
            Response::jsonError('NOT_FOUND', 'Sipariş bulunamadı veya güncellenemedi.');
            return;
        }
        Response::jsonSuccess(['status' => $status]);
    }
}

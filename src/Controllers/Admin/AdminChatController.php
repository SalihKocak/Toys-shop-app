<?php

declare(strict_types=1);

namespace ToyShop\Controllers\Admin;

use ToyShop\Infrastructure\Response;
use ToyShop\Middleware\AdminMiddleware;
use ToyShop\Services\ChatService;

final class AdminChatController
{
    public function __construct(
        private ChatService $chatService
    ) {}

    public function index(): void
    {
        $openThreads = $this->chatService->listOpenThreadsForAdmin();
        $closedThreads = $this->chatService->listClosedThreadsForAdmin();
        require __DIR__ . '/../../Views/admin/chats/index.php';
    }

    public function show(string $id): void
    {
        $thread = $this->chatService->getThreadById($id);
        if ($thread === null) {
            http_response_code(404);
            require __DIR__ . '/../../Views/errors/404.php';
            return;
        }
        $messages = $this->chatService->getMessagesForThread($id);
        require __DIR__ . '/../../Views/admin/chats/show.php';
    }

    public function send(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $admin = AdminMiddleware::requireAdmin();
        if ($admin === null) {
            Response::jsonError('AUTH', 'Yetkisiz.', 401);
            return;
        }
        $input = self::readJson();
        $text = trim((string) ($input['text'] ?? ''));
        if ($text === '') {
            Response::jsonError('VALIDATION', 'Mesaj metni gerekli.');
            return;
        }
        try {
            $msg = $this->chatService->addMessage($id, 'admin', $admin['id'], $text);
        } catch (\Throwable $e) {
            \ToyShop\Infrastructure\Logger::error('Admin chat send', ['message' => $e->getMessage()]);
            Response::jsonError('CHAT', $e->getMessage());
            return;
        }
        Response::jsonSuccess($msg);
    }

    public function poll(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (AdminMiddleware::requireAdmin() === null) {
            Response::jsonError('AUTH', 'Yetkisiz.', 401);
            return;
        }
        $after = (int) ($_GET['after'] ?? 0);
        $thread = $this->chatService->getThreadById($id);
        if ($thread === null) {
            Response::jsonError('NOT_FOUND', 'Sohbet bulunamadı.');
            return;
        }
        $messages = $this->chatService->getMessagesAfter($id, $after);
        Response::jsonSuccess(['messages' => $messages, 'thread' => $thread]);
    }

    public function close(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $admin = AdminMiddleware::requireAdmin();
        if ($admin === null) {
            Response::jsonError('AUTH', 'Yetkisiz.', 401);
            return;
        }
        try {
            $this->chatService->closeThread($id, $admin['id']);
        } catch (\Throwable $e) {
            \ToyShop\Infrastructure\Logger::error('Admin chat close', ['message' => $e->getMessage()]);
            Response::jsonError('CHAT', $e->getMessage());
            return;
        }
        Response::jsonSuccess(['redirect' => '/admin/chats']);
    }

    /** @return array<string, mixed> */
    private static function readJson(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return $_POST;
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}

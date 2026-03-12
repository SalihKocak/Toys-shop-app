<?php

declare(strict_types=1);

namespace ToyShop\Controllers;

use ToyShop\Infrastructure\Response;
use ToyShop\Middleware\AuthMiddleware;
use ToyShop\Services\ChatService;

final class ChatController
{
    public function __construct(
        private ChatService $chatService
    ) {}

    public function start(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = self::readJson();
        $subject = trim((string) ($input['subject'] ?? 'Destek talebi'));
        $user = AuthMiddleware::requireLogin();
        $customerId = $user !== null ? $user['id'] : null;
        $guestToken = null;
        if ($customerId === null) {
            if (session_status() === PHP_SESSION_NONE) {
                session_name(\ToyShop\Infrastructure\Env::get('SESSION_NAME', 'toyshop_session'));
                session_start();
            }
            $guestToken = $_SESSION['chat_guest_token'] ?? null;
            if ($guestToken === null) {
                $guestToken = $this->chatService->generateGuestToken();
                $_SESSION['chat_guest_token'] = $guestToken;
            }
        }
        try {
            $thread = $this->chatService->startThread($customerId, $guestToken, $subject);
        } catch (\Throwable $e) {
            \ToyShop\Infrastructure\Logger::error('Chat start error', ['message' => $e->getMessage()]);
            Response::jsonError('CHAT', 'Sohbet başlatılamadı.');
            return;
        }
        Response::jsonSuccess($thread);
    }

    public function poll(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $threadId = trim((string) ($_GET['threadId'] ?? ''));
        $after = (int) ($_GET['after'] ?? 0);
        if ($threadId === '') {
            Response::jsonError('VALIDATION', 'threadId gerekli.');
            return;
        }
        $user = AuthMiddleware::requireLogin();
        $customerId = $user !== null ? $user['id'] : null;
        $guestToken = null;
        if ($customerId === null && session_status() === PHP_SESSION_ACTIVE) {
            $guestToken = $_SESSION['chat_guest_token'] ?? null;
        }
        $thread = $this->chatService->getThreadForCustomer($threadId, $customerId, $guestToken);
        if ($thread === null) {
            Response::jsonError('NOT_FOUND', 'Sohbet bulunamadı.');
            return;
        }
        $messages = $this->chatService->getMessagesAfter($threadId, $after);
        Response::jsonSuccess(['messages' => $messages, 'thread' => $thread]);
    }

    public function send(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = self::readJson();
        $threadId = trim((string) ($input['threadId'] ?? ''));
        $text = trim((string) ($input['text'] ?? ''));
        if ($threadId === '' || $text === '') {
            Response::jsonError('VALIDATION', 'threadId ve mesaj metni gerekli.');
            return;
        }
        $user = AuthMiddleware::requireLogin();
        $customerId = $user !== null ? $user['id'] : null;
        $guestToken = null;
        if ($customerId === null && session_status() === PHP_SESSION_ACTIVE) {
            $guestToken = $_SESSION['chat_guest_token'] ?? null;
        }
        $thread = $this->chatService->getThreadForCustomer($threadId, $customerId, $guestToken);
        if ($thread === null) {
            Response::jsonError('NOT_FOUND', 'Sohbet bulunamadı.');
            return;
        }
        try {
            $msg = $this->chatService->addMessage($threadId, 'customer', $customerId, $text);
        } catch (\Throwable $e) {
            \ToyShop\Infrastructure\Logger::error('Chat send error', ['message' => $e->getMessage()]);
            Response::jsonError('CHAT', $e->getMessage());
            return;
        }
        Response::jsonSuccess($msg);
    }

    /** Kullanıcının sohbet listesi (açık + geçmiş). Giriş yapmış olsa bile session'daki guest token ile eski misafir sohbetleri dahil edilir. */
    public function myThreads(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $user = AuthMiddleware::requireLogin();
        $customerId = $user !== null ? $user['id'] : null;
        $guestToken = (session_status() === PHP_SESSION_ACTIVE) ? ($_SESSION['chat_guest_token'] ?? null) : null;
        $threads = $this->chatService->getThreadsForCustomer($customerId, $guestToken);
        Response::jsonSuccess($threads);
    }

    /** Tek sohbet + mesajlar (devam etmek için). */
    public function thread(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $threadId = trim((string) ($_GET['threadId'] ?? ''));
        if ($threadId === '') {
            Response::jsonError('VALIDATION', 'threadId gerekli.');
            return;
        }
        $user = AuthMiddleware::requireLogin();
        $customerId = $user !== null ? $user['id'] : null;
        $guestToken = (session_status() === PHP_SESSION_ACTIVE) ? ($_SESSION['chat_guest_token'] ?? null) : null;
        $thread = $this->chatService->getThreadForCustomer($threadId, $customerId, $guestToken);
        if ($thread === null) {
            Response::jsonError('NOT_FOUND', 'Sohbet bulunamadı.');
            return;
        }
        $messages = $this->chatService->getMessagesForThread($threadId);
        Response::jsonSuccess(['thread' => $thread, 'messages' => $messages]);
    }

    /** Müşteri sohbeti kapatır. */
    public function close(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = self::readJson();
        $threadId = trim((string) ($input['threadId'] ?? ''));
        if ($threadId === '') {
            Response::jsonError('VALIDATION', 'threadId gerekli.');
            return;
        }
        $user = AuthMiddleware::requireLogin();
        $customerId = $user !== null ? $user['id'] : null;
        $guestToken = (session_status() === PHP_SESSION_ACTIVE) ? ($_SESSION['chat_guest_token'] ?? null) : null;
        try {
            $this->chatService->closeThreadByCustomer($threadId, $customerId, $guestToken);
        } catch (\Throwable $e) {
            Response::jsonError('CHAT', $e->getMessage());
            return;
        }
        Response::jsonSuccess(['ok' => true]);
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

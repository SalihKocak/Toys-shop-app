<?php

declare(strict_types=1);

namespace ToyShop\Services;

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use ToyShop\Infrastructure\Mongo;

final class ChatService
{
    private const THREADS = 'chat_threads';
    private const MESSAGES = 'chat_messages';

    public function startThread(?string $customerId, ?string $guestToken, string $subject): array
    {
        $now = new UTCDateTime();
        $doc = [
            'customerId' => $customerId ? new ObjectId($customerId) : null,
            'guestToken' => $guestToken,
            'subject' => trim($subject) ?: 'Destek talebi',
            'status' => 'open',
            'assignedAdminId' => null,
            'lastMessageAt' => $now,
            'createdAt' => $now,
        ];
        $result = Mongo::collection(self::THREADS)->insertOne($doc);
        $doc['_id'] = $result->getInsertedId();
        return $this->threadToArray($doc);
    }

    public function getThreadById(string $id): ?array
    {
        try {
            $doc = Mongo::collection(self::THREADS)->findOne(['_id' => new ObjectId($id)]);
        } catch (\Exception $e) {
            return null;
        }
        return $doc !== null ? $this->threadToArray($doc) : null;
    }

    public function getThreadForCustomer(string $threadId, ?string $customerId, ?string $guestToken): ?array
    {
        $thread = $this->getThreadById($threadId);
        if ($thread === null) {
            return null;
        }
        if ($customerId !== null && ($thread['customerId'] ?? '') === $customerId) {
            return $thread;
        }
        if ($guestToken !== null && ($thread['guestToken'] ?? '') === $guestToken) {
            return $thread;
        }
        return null;
    }

    public function listOpenThreadsForAdmin(): array
    {
        $cursor = Mongo::collection(self::THREADS)->find(
            ['status' => 'open'],
            ['sort' => ['lastMessageAt' => -1]]
        );
        return array_map([$this, 'threadToArray'], iterator_to_array($cursor));
    }

    public function getMessagesAfter(string $threadId, int $afterMs): array
    {
        $after = new UTCDateTime($afterMs);
        $cursor = Mongo::collection(self::MESSAGES)->find(
            [
                'threadId' => $threadId,
                'createdAt' => ['$gt' => $after],
            ],
            ['sort' => ['createdAt' => 1]]
        );
        return array_map([$this, 'messageToArray'], iterator_to_array($cursor));
    }

    public function getMessagesForThread(string $threadId): array
    {
        $cursor = Mongo::collection(self::MESSAGES)->find(
            ['threadId' => $threadId],
            ['sort' => ['createdAt' => 1]]
        );
        return array_map([$this, 'messageToArray'], iterator_to_array($cursor));
    }

    public function addMessage(string $threadId, string $senderRole, ?string $senderId, string $text): array
    {
        $thread = $this->getThreadById($threadId);
        if ($thread === null) {
            throw new \RuntimeException('Thread not found.');
        }
        if (($thread['status'] ?? '') === 'closed') {
            throw new \RuntimeException('Bu sohbet kapatıldı.');
        }
        $now = new UTCDateTime();
        $doc = [
            'threadId' => $threadId,
            'senderRole' => $senderRole,
            'senderId' => $senderId ? new ObjectId($senderId) : null,
            'text' => trim($text),
            'createdAt' => $now,
        ];
        $result = Mongo::collection(self::MESSAGES)->insertOne($doc);
        $doc['_id'] = $result->getInsertedId();
        Mongo::collection(self::THREADS)->updateOne(
            ['_id' => new ObjectId($threadId)],
            ['$set' => ['lastMessageAt' => $now, 'assignedAdminId' => $senderRole === 'admin' ? new ObjectId($senderId) : ($thread['assignedAdminId'] ?? null)]]
        );
        return $this->messageToArray($doc);
    }

    public function closeThread(string $threadId, string $adminId): void
    {
        try {
            Mongo::collection(self::THREADS)->updateOne(
                ['_id' => new ObjectId($threadId)],
                ['$set' => ['status' => 'closed', 'assignedAdminId' => new ObjectId($adminId)]]
            );
        } catch (\Exception $e) {
            throw new \RuntimeException('Thread not found.');
        }
    }

    public function generateGuestToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    /** @param array|object $doc */
    private function threadToArray($doc): array
    {
        $arr = is_array($doc) ? $doc : (array) $doc;
        $id = $arr['_id'] ?? null;
        if ($id instanceof ObjectId) {
            $arr['id'] = $id->__toString();
        }
        unset($arr['_id']);
        foreach (['customerId', 'assignedAdminId'] as $k) {
            if (isset($arr[$k]) && $arr[$k] instanceof ObjectId) {
                $arr[$k] = $arr[$k]->__toString();
            }
        }
        foreach (['lastMessageAt', 'createdAt'] as $k) {
            if (isset($arr[$k]) && $arr[$k] instanceof UTCDateTime) {
                $arr[$k] = $arr[$k]->toDateTime()->getTimestamp() * 1000;
            }
        }
        return $arr;
    }

    /** @param array|object $doc */
    private function messageToArray($doc): array
    {
        $arr = is_array($doc) ? $doc : (array) $doc;
        foreach (['senderId'] as $k) {
            if (isset($arr[$k]) && $arr[$k] instanceof ObjectId) {
                $arr[$k] = $arr[$k]->__toString();
            }
        }
        if (isset($arr['createdAt']) && $arr['createdAt'] instanceof UTCDateTime) {
            $arr['createdAt'] = $arr['createdAt']->toDateTime()->getTimestamp() * 1000;
        }
        return $arr;
    }

    /** Müşteriye ait sohbetleri listeler (açık + kapalı, son mesaj tarihine göre). */
    public function getThreadsForCustomer(?string $customerId, ?string $guestToken): array
    {
        $filter = ['$or' => []];
        if ($customerId !== null && $customerId !== '') {
            $filter['$or'][] = ['customerId' => new ObjectId($customerId)];
        }
        if ($guestToken !== null && $guestToken !== '') {
            $filter['$or'][] = ['guestToken' => $guestToken];
        }
        if ($filter['$or'] === []) {
            return [];
        }
        $cursor = Mongo::collection(self::THREADS)->find(
            $filter,
            ['sort' => ['lastMessageAt' => -1]]
        );
        return array_map([$this, 'threadToArray'], iterator_to_array($cursor));
    }

    /** Müşteri sohbeti kapatır (thread müşteriye ait olmalı). */
    public function closeThreadByCustomer(string $threadId, ?string $customerId, ?string $guestToken): void
    {
        $thread = $this->getThreadForCustomer($threadId, $customerId, $guestToken);
        if ($thread === null) {
            throw new \RuntimeException('Sohbet bulunamadı.');
        }
        Mongo::collection(self::THREADS)->updateOne(
            ['_id' => new ObjectId($threadId)],
            ['$set' => ['status' => 'closed']]
        );
    }

    /** Admin için kapalı sohbetleri listeler. */
    public function listClosedThreadsForAdmin(): array
    {
        $cursor = Mongo::collection(self::THREADS)->find(
            ['status' => 'closed'],
            ['sort' => ['lastMessageAt' => -1]]
        );
        return array_map([$this, 'threadToArray'], iterator_to_array($cursor));
    }
}

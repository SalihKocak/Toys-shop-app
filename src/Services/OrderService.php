<?php

declare(strict_types=1);

namespace ToyShop\Services;

use MongoDB\BSON\ObjectId;
use ToyShop\Infrastructure\Mongo;

final class OrderService
{
    private const COLLECTION = 'orders';

    public function create(string $userId, array $items, float $total): array
    {
        $now = new \MongoDB\BSON\UTCDateTime();
        $doc = [
            'userId' => new ObjectId($userId),
            'items' => $items,
            'total' => $total,
            'status' => 'created',
            'createdAt' => $now,
        ];
        Mongo::collection(self::COLLECTION)->insertOne($doc);
        return $this->orderToArray($doc);
    }

    public function getById(string $id): ?array
    {
        try {
            $doc = Mongo::collection(self::COLLECTION)->findOne(['_id' => new ObjectId($id)]);
        } catch (\Exception $e) {
            return null;
        }
        return $doc !== null ? $this->orderToArray($doc) : null;
    }

    public function listByUser(string $userId): array
    {
        $cursor = Mongo::collection(self::COLLECTION)->find(
            ['userId' => new ObjectId($userId)],
            ['sort' => ['createdAt' => -1]]
        );
        return array_map([$this, 'orderToArray'], iterator_to_array($cursor));
    }

    public function listAll(): array
    {
        $cursor = Mongo::collection(self::COLLECTION)->find([], ['sort' => ['createdAt' => -1]]);
        return array_map([$this, 'orderToArray'], iterator_to_array($cursor));
    }

    public function updateStatus(string $id, string $status): bool
    {
        $allowed = ['created', 'paid', 'shipped', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        try {
            $result = Mongo::collection(self::COLLECTION)->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => ['status' => $status]]
            );
            return $result->getModifiedCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function orderToArray(object $doc): array
    {
        $arr = (array) $doc;
        $id = $arr['_id'] ?? null;
        if ($id instanceof ObjectId) {
            $arr['id'] = $id->__toString();
        }
        unset($arr['_id']);
        if (isset($arr['userId']) && $arr['userId'] instanceof ObjectId) {
            $arr['userId'] = $arr['userId']->__toString();
        }
        if (isset($arr['createdAt']) && $arr['createdAt'] instanceof \MongoDB\BSON\UTCDateTime) {
            $arr['createdAt'] = $arr['createdAt']->toDateTime()->getTimestamp();
        }
        return $arr;
    }
}

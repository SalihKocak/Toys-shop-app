<?php

declare(strict_types=1);

namespace ToyShop\Controllers\Admin;

use ToyShop\Infrastructure\Mongo;

final class AdminUsersController
{
    public function index(): void
    {
        $cursor = Mongo::collection('users')->find(
            [],
            ['sort' => ['createdAt' => -1], 'projection' => ['passwordHash' => 0]]
        );
        $users = [];
        foreach ($cursor as $doc) {
            $users[] = $this->docToArray($doc);
        }
        require __DIR__ . '/../../Views/admin/users/index.php';
    }

    /** @param \MongoDB\Model\BSONDocument|array $doc */
    private function docToArray($doc): array
    {
        $get = function ($key) use ($doc) {
            if (is_array($doc)) {
                return $doc[$key] ?? null;
            }
            return $doc[$key] ?? $doc->{$key} ?? null;
        };
        $id = $get('_id');
        if ($id instanceof \MongoDB\BSON\ObjectId) {
            $id = $id->__toString();
        }
        $createdAt = $get('createdAt');
        $ts = $createdAt instanceof \MongoDB\BSON\UTCDateTime
            ? (int) ((string) $createdAt) / 1000
            : null;
        return [
            'id' => $id,
            'role' => $get('role') ?? 'customer',
            'name' => $get('name') ?? '',
            'email' => $get('email') ?? '',
            'createdAt' => $ts,
        ];
    }
}

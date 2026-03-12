<?php

declare(strict_types=1);

namespace ToyShop\Services;

use ToyShop\Infrastructure\Mongo;

final class AuthService
{
    private const COLLECTION = 'users';

    public function register(string $name, string $email, string $password): array
    {
        $email = strtolower(trim($email));
        $existing = Mongo::collection(self::COLLECTION)->findOne(['email' => $email]);
        if ($existing !== null) {
            throw new \RuntimeException('Bu e-posta adresi zaten kayıtlı.');
        }
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            throw new \RuntimeException('Şifre hashlenemedi.');
        }
        $doc = [
            'role' => 'customer',
            'name' => trim($name),
            'email' => $email,
            'passwordHash' => $passwordHash,
            'createdAt' => new \MongoDB\BSON\UTCDateTime(),
        ];
        $result = Mongo::collection(self::COLLECTION)->insertOne($doc);
        $doc['_id'] = $result->getInsertedId();
        return $this->userToArray($doc);
    }

    public function loginCustomer(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if ($user === null || ($user['role'] ?? '') !== 'customer') {
            return null;
        }
        if (!password_verify($password, $user['passwordHash'])) {
            return null;
        }
        return $this->userToArray($user);
    }

    public function loginAdmin(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if ($user === null || ($user['role'] ?? '') !== 'admin') {
            return null;
        }
        if (!password_verify($password, $user['passwordHash'])) {
            return null;
        }
        return $this->userToArray($user);
    }

    public function findById(string $id): ?array
    {
        try {
            $doc = Mongo::collection(self::COLLECTION)->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        } catch (\Exception $e) {
            return null;
        }
        return $doc !== null ? $this->userToArray($doc) : null;
    }

    private function findByEmail(string $email): ?array
    {
        $doc = Mongo::collection(self::COLLECTION)->findOne(['email' => strtolower(trim($email))]);
        return $doc !== null ? (array) $doc : null;
    }

    private function userToArray(array $doc): array
    {
        $id = $doc['_id'];
        if ($id instanceof \MongoDB\BSON\ObjectId) {
            $id = $id->__toString();
        }
        return [
            'id' => $id,
            'role' => $doc['role'] ?? 'customer',
            'name' => $doc['name'] ?? '',
            'email' => $doc['email'] ?? '',
        ];
    }
}

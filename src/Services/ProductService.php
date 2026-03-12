<?php

declare(strict_types=1);

namespace ToyShop\Services;

use MongoDB\BSON\ObjectId;
use ToyShop\Infrastructure\Mongo;

final class ProductService
{
    private const COLLECTION = 'products';

    public function listFeatured(int $limit = 3): array
    {
        $cursor = Mongo::collection(self::COLLECTION)->find(
            ['isActive' => true, 'isFeatured' => true],
            ['sort' => ['updatedAt' => -1, 'createdAt' => -1], 'limit' => max(1, $limit)]
        );
        return array_map(fn($d) => $this->docToArray($d, false), iterator_to_array($cursor));
    }

    public function listActive(?string $category = null, ?string $brand = null, ?string $sortPrice = null): array
    {
        $filter = ['isActive' => true];
        if ($category !== null && $category !== '') {
            $filter['category'] = $category;
        }
        if ($brand !== null && $brand !== '') {
            $filter['brand'] = $brand;
        }
        $sort = ['createdAt' => -1];
        if ($sortPrice === 'price_asc') {
            $sort = ['price' => 1, 'createdAt' => -1];
        } elseif ($sortPrice === 'price_desc') {
            $sort = ['price' => -1, 'createdAt' => -1];
        }
        $cursor = Mongo::collection(self::COLLECTION)->find(
            $filter,
            ['sort' => $sort]
        );
        return array_map(fn($d) => $this->docToArray($d, false), iterator_to_array($cursor));
    }

    public function listAll(): array
    {
        $cursor = Mongo::collection(self::COLLECTION)->find([], ['sort' => ['createdAt' => -1]]);
        return array_map(fn($d) => $this->docToArray($d, false), iterator_to_array($cursor));
    }

    public function getById(string $id, bool $includeImageData = true): ?array
    {
        try {
            $doc = Mongo::collection(self::COLLECTION)->findOne(['_id' => new ObjectId($id)]);
        } catch (\Exception $e) {
            return null;
        }
        return $doc !== null ? $this->docToArray($doc, $includeImageData) : null;
    }

    public function getActiveById(string $id): ?array
    {
        $product = $this->getById($id);
        return $product !== null && ($product['isActive'] ?? true) ? $product : null;
    }

    /** @param array<string, string> $imageData filename => base64 (production'da kalıcılık için) */
    public function create(array $data, array $imageNames = [], array $imageData = []): array
    {
        $doc = [
            'name' => trim((string) ($data['name'] ?? '')),
            'brand' => trim((string) ($data['brand'] ?? '')),
            'category' => trim((string) ($data['category'] ?? '')),
            'price' => (float) ($data['price'] ?? 0),
            'stock' => (int) ($data['stock'] ?? 0),
            'description' => trim((string) ($data['description'] ?? '')),
            'images' => $imageNames,
            'imageData' => $imageData,
            'isActive' => filter_var($data['isActive'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'isFeatured' => false,
            'specs' => [],
            'createdAt' => new \MongoDB\BSON\UTCDateTime(),
            'updatedAt' => new \MongoDB\BSON\UTCDateTime(),
        ];
        $result = Mongo::collection(self::COLLECTION)->insertOne($doc);
        $doc['_id'] = $result->getInsertedId();
        return $this->docToArray($doc, false);
    }

    /** @param array<string, string>|null $appendImageData filename => base64 (yeni yüklenen görseller için) */
    public function update(string $id, array $data, ?array $appendImages = null, array $appendImageData = []): ?array
    {
        try {
            $oid = new ObjectId($id);
        } catch (\Exception $e) {
            return null;
        }
        $current = Mongo::collection(self::COLLECTION)->findOne(['_id' => $oid]);
        if ($current === null) {
            return null;
        }
        $currentImages = $current['images'] ?? [];
        if ($currentImages instanceof \MongoDB\Model\BSONArray || $currentImages instanceof \Traversable) {
            $currentImages = iterator_to_array($currentImages);
        } elseif (!is_array($currentImages)) {
            $currentImages = [];
        }
        $images = isset($data['images']) && is_array($data['images']) ? $data['images'] : $currentImages;
        if ($appendImages !== null && $appendImages !== []) {
            $images = array_merge($images, $appendImages);
        }
        $currentImageData = $current['imageData'] ?? [];
        if ($currentImageData instanceof \Traversable) {
            $currentImageData = iterator_to_array($currentImageData);
        } elseif (!is_array($currentImageData)) {
            $currentImageData = [];
        }
        $imageData = array_merge($currentImageData, $appendImageData);
        $update = [
            'name' => $data['name'] ?? $current['name'] ?? '',
            'brand' => $data['brand'] ?? $current['brand'] ?? '',
            'category' => $data['category'] ?? $current['category'] ?? '',
            'price' => (float) ($data['price'] ?? $current['price'] ?? 0),
            'stock' => (int) ($data['stock'] ?? $current['stock'] ?? 0),
            'description' => $data['description'] ?? $current['description'] ?? '',
            'isActive' => isset($data['isActive']) ? (bool) $data['isActive'] : ($current['isActive'] ?? true),
            'images' => $images,
            'imageData' => $imageData,
            'updatedAt' => new \MongoDB\BSON\UTCDateTime(),
        ];
        Mongo::collection(self::COLLECTION)->updateOne(['_id' => $oid], ['$set' => $update]);
        return $this->getById($id);
    }

    public function setImages(string $id, array $imageNames): void
    {
        try {
            $oid = new ObjectId($id);
        } catch (\Exception $e) {
            return;
        }
        Mongo::collection(self::COLLECTION)->updateOne(
            ['_id' => $oid],
            ['$set' => ['images' => $imageNames, 'updatedAt' => new \MongoDB\BSON\UTCDateTime()]]
        );
    }

    public function delete(string $id): bool
    {
        try {
            $result = Mongo::collection(self::COLLECTION)->deleteOne(['_id' => new ObjectId($id)]);
            return $result->getDeletedCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getCategories(): array
    {
        $cursor = Mongo::collection(self::COLLECTION)->distinct('category', ['isActive' => true]);
        $out = array_filter($cursor, fn($c) => $c !== null && $c !== '');
        sort($out);
        return array_values($out);
    }

    public function getBrands(): array
    {
        $cursor = Mongo::collection(self::COLLECTION)->distinct('brand', ['isActive' => true]);
        $out = array_filter($cursor, fn($b) => $b !== null && $b !== '');
        sort($out);
        return array_values($out);
    }

    /** @param object|array $doc */
    private function docToArray($doc, bool $includeImageData = true): array
    {
        $arr = is_array($doc) ? $doc : (array) $doc;
        $id = $arr['_id'] ?? null;
        if ($id instanceof ObjectId) {
            $arr['id'] = $id->__toString();
        }
        unset($arr['_id']);
        $arr['isFeatured'] = (bool) ($arr['isFeatured'] ?? false);
        $arr['specs'] = isset($arr['specs']) && is_array($arr['specs']) ? $arr['specs'] : [];
        foreach (['createdAt', 'updatedAt'] as $k) {
            if (isset($arr[$k]) && $arr[$k] instanceof \MongoDB\BSON\UTCDateTime) {
                $arr[$k] = $arr[$k]->toDateTime()->getTimestamp();
            }
        }
        $imgs = $arr['images'] ?? [];
        if ($imgs instanceof \MongoDB\Model\BSONArray || $imgs instanceof \Traversable) {
            $arr['images'] = iterator_to_array($imgs);
        } elseif (!is_array($imgs)) {
            $arr['images'] = [];
        }
        if (!$includeImageData) {
            unset($arr['imageData']);
        } else {
            $imgData = $arr['imageData'] ?? [];
            if ($imgData instanceof \Traversable) {
                $arr['imageData'] = iterator_to_array($imgData);
            } elseif (!is_array($imgData)) {
                $arr['imageData'] = [];
            }
        }
        return $arr;
    }
}

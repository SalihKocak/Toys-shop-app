<?php

declare(strict_types=1);

namespace ToyShop\Infrastructure;

use MongoDB\Client;
use MongoDB\Database;

/**
 * MongoDB baglantisi. Atlas icin: new Client($uri)
 * @see https://www.mongodb.com/docs/php-library/current/connect/
 */
final class Mongo
{
    private static ?Client $client = null;
    private static ?string $dbName = null;

    public static function init(string $uri, string $dbName): void
    {
        self::$client = new Client($uri);
        self::$dbName = $dbName;
    }

    public static function db(): Database
    {
        if (self::$client === null || self::$dbName === null) {
            throw new \RuntimeException('Mongo not initialized. Call Mongo::init() with URI and DB name.');
        }
        return self::$client->selectDatabase(self::$dbName);
    }

    public static function collection(string $name): \MongoDB\Collection
    {
        return self::db()->selectCollection($name);
    }
}

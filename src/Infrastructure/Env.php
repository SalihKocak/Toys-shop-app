<?php

declare(strict_types=1);

namespace ToyShop\Infrastructure;

use Dotenv\Dotenv;

final class Env
{
    private static ?bool $loaded = null;

    public static function load(string $basePath): void
    {
        if (self::$loaded === true) {
            return;
        }
        $dotenv = Dotenv::createImmutable($basePath);
        $dotenv->safeLoad();
        self::$loaded = true;
    }

    public static function get(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? getenv($key);
        return $value !== false ? (string) $value : $default;
    }

    public static function getRequired(string $key): string
    {
        $value = self::get($key);
        if ($value === '') {
            throw new \RuntimeException("Required env variable {$key} is not set.");
        }
        return $value;
    }
}

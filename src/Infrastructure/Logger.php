<?php

declare(strict_types=1);

namespace ToyShop\Infrastructure;

final class Logger
{
    private static ?string $logPath = null;

    public static function setLogPath(string $path): void
    {
        self::$logPath = $path;
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    private static function log(string $level, string $message, array $context): void
    {
        if (self::$logPath === null) {
            return;
        }
        $line = date('Y-m-d H:i:s') . " [{$level}] {$message}";
        if ($context !== []) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        $line .= "\n";
        @file_put_contents(self::$logPath, $line, FILE_APPEND | LOCK_EX);
    }
}

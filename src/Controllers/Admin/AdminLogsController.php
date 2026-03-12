<?php

declare(strict_types=1);

namespace ToyShop\Controllers\Admin;

final class AdminLogsController
{
    private const LINES = 500;
    private const LOG_PATH = 'storage/logs/app.log';

    public function __construct(
        private string $basePath
    ) {}

    public function index(): void
    {
        $logPath = $this->basePath . DIRECTORY_SEPARATOR . self::LOG_PATH;
        $lines = [];
        if (is_file($logPath) && is_readable($logPath)) {
            $content = @file_get_contents($logPath);
            if ($content !== false) {
                $all = explode("\n", $content);
                $lines = array_slice(array_filter($all), -self::LINES);
            }
        }
        require __DIR__ . '/../../Views/admin/logs/index.php';
    }
}

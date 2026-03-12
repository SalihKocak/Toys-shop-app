<?php

declare(strict_types=1);

namespace ToyShop\Controllers;

use ToyShop\Infrastructure\Env;
use ToyShop\Services\ProductService;

/**
 * Ürün görselini veritabanı (imageData) veya dosyadan sunar.
 * Deploy'da dosya olmasa bile DB'deki base64 ile görsel gösterilir.
 */
final class ImageController
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function serve(string $productId, int $index): void
    {
        $product = $this->productService->getById($productId, true);
        if ($product === null) {
            $this->redirectToPlaceholder();
            return;
        }
        $images = $product['images'] ?? [];
        if (!isset($images[$index]) || $images[$index] === '') {
            $this->redirectToPlaceholder();
            return;
        }
        $filename = $images[$index];
        if (is_array($filename)) {
            $filename = (string) ($filename['name'] ?? $filename[0] ?? '');
        }
        $filename = basename((string) $filename);
        $imageData = $product['imageData'] ?? [];
        if (isset($imageData[$filename]) && $imageData[$filename] !== '') {
            $raw = @base64_decode($imageData[$filename], true);
            if ($raw !== false) {
                $this->outputImage($raw, $filename);
                return;
            }
        }
        $path = defined('PROJECT_ROOT') ? (PROJECT_ROOT . '/public/uploads/' . $filename) : '';
        if ($path !== '' && @is_file($path)) {
            $raw = @file_get_contents($path);
            if ($raw !== false) {
                $this->outputImage($raw, $filename);
                return;
            }
        }
        $this->redirectToPlaceholder();
    }

    private function outputImage(string $raw, string $filename): void
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];
        $type = $types[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $type);
        header('Cache-Control: public, max-age=86400');
        echo $raw;
    }

    private function redirectToPlaceholder(): void
    {
        $base = rtrim(parse_url(Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
        header('Location: ' . $base . '/assets/placeholder.svg', true, 302);
        exit;
    }
}

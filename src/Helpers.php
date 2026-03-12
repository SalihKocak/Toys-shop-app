<?php

declare(strict_types=1);

/**
 * Ürün görseli URL'i: /image/{productId}/{index}
 * Görsel veritabanında (imageData) veya dosyadan sunulur; deploy'da da çalışır.
 */
function product_image_url(string $productId, int $index, string $base): string
{
    return $base . '/image/' . htmlspecialchars($productId, ENT_QUOTES, 'UTF-8') . '/' . (int) $index;
}

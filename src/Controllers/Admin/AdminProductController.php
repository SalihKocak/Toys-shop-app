<?php

declare(strict_types=1);

namespace ToyShop\Controllers\Admin;

use ToyShop\Infrastructure\Env;
use ToyShop\Infrastructure\Response;
use ToyShop\Services\ProductService;

final class AdminProductController
{
    private const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'webp'];
    private const MAX_SIZE_BYTES = 5 * 1024 * 1024;

    public function __construct(
        private ProductService $productService
    ) {}

    public function index(): void
    {
        $products = $this->productService->listAll();
        require __DIR__ . '/../../Views/admin/products/index.php';
    }

    public function createForm(): void
    {
        require __DIR__ . '/../../Views/admin/products/create.php';
    }

    public function create(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $data = $this->readProductData();
        $uploadResult = $this->handleUploads('images');
        $names = $uploadResult['names'];
        $imageData = $uploadResult['imageData'];
        try {
            $product = $this->productService->create($data, $names, $imageData);
        } catch (\Throwable $e) {
            \ToyShop\Infrastructure\Logger::error('Admin product create', ['message' => $e->getMessage()]);
            Response::jsonError('CREATE', 'Ürün oluşturulamadı.');
            return;
        }
        Response::jsonSuccess(['id' => $product['id'] ?? null, 'redirect' => '/admin/products']);
    }

    public function editForm(string $id): void
    {
        $product = $this->productService->getById($id);
        if ($product === null) {
            http_response_code(404);
            require __DIR__ . '/../../Views/errors/404.php';
            return;
        }
        require __DIR__ . '/../../Views/admin/products/edit.php';
    }

    public function update(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $data = $this->readProductData();
        $uploadResult = $this->handleUploads('images');
        $appendImages = $uploadResult['names'];
        $appendImageData = $uploadResult['imageData'];
        $product = $this->productService->update($id, $data, $appendImages, $appendImageData);
        if ($product === null) {
            Response::jsonError('NOT_FOUND', 'Ürün bulunamadı.');
            return;
        }
        Response::jsonSuccess(['redirect' => '/admin/products']);
    }

    public function delete(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $ok = $this->productService->delete($id);
        if (!$ok) {
            Response::jsonError('NOT_FOUND', 'Ürün bulunamadı veya silinemedi.');
            return;
        }
        Response::jsonSuccess(['redirect' => '/admin/products']);
    }

    /** @return array<string, mixed> */
    private function readProductData(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw !== false && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return array_merge($_POST, $decoded);
            }
        }
        return $_POST;
    }

    /** @return array{names: list<string>, imageData: array<string, string>} */
    private function handleUploads(string $field): array
    {
        $names = [];
        $imageData = [];
        $files = $_FILES[$field] ?? null;
        if ($files === null || !is_array($files['name'])) {
            if (is_string($files['name'] ?? null) && ($files['error'] ?? 0) === UPLOAD_ERR_OK) {
                $one = $this->saveOneFile($files['tmp_name'], $files['name'], $files['size']);
                if ($one !== null) {
                    $names[] = $one['name'];
                    if (isset($one['data'])) {
                        $imageData[$one['name']] = $one['data'];
                    }
                }
            }
            return ['names' => $names, 'imageData' => $imageData];
        }
        foreach (array_keys($files['name']) as $i) {
            if (($files['error'][$i] ?? 0) !== UPLOAD_ERR_OK) {
                continue;
            }
            $one = $this->saveOneFile(
                $files['tmp_name'][$i] ?? '',
                $files['name'][$i] ?? '',
                $files['size'][$i] ?? 0
            );
            if ($one !== null) {
                $names[] = $one['name'];
                if (isset($one['data'])) {
                    $imageData[$one['name']] = $one['data'];
                }
            }
        }
        return ['names' => $names, 'imageData' => $imageData];
    }

    /** @return array{name: string, data?: string}|null */
    private function saveOneFile(string $tmpPath, string $originalName, int $size): ?array
    {
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            return null;
        }
        if ($size > self::MAX_SIZE_BYTES) {
            return null;
        }
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXT, true)) {
            return null;
        }
        $newName = bin2hex(random_bytes(8)) . '.' . $ext;
        $isProd = Env::get('APP_ENV') === 'production';
        $baseDir = dirname(__DIR__, 3) . '/public/uploads';
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0755, true);
        }
        $target = $baseDir . '/' . $newName;
        $result = ['name' => $newName];
        if ($isProd) {
            $raw = @file_get_contents($tmpPath);
            if ($raw !== false) {
                $result['data'] = base64_encode($raw);
            }
        }
        $moved = move_uploaded_file($tmpPath, $target);
        if (!$isProd && !$moved) {
            return null;
        }
        return $result;
    }
}

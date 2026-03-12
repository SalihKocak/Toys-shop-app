<?php
http_response_code(404);
$pageTitle = 'Bulunamadı';
$base = rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
$content = '<p class="text-muted">Sayfa bulunamadı.</p><a href="' . htmlspecialchars($base) . '/" class="btn btn-primary">Ana Sayfa</a>';
require dirname(__DIR__) . '/layout.php';

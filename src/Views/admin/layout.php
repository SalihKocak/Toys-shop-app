<?php
$appUrl = \ToyShop\Infrastructure\Env::get('APP_URL', '');
$base = rtrim(parse_url($appUrl, PHP_URL_PATH) ?: '', '/') ?: '';
$adminBase = $base . '/admin';
$asset = $base . '/assets';
$adminPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$adminPath = $adminPath ?: '/';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Admin' : 'Admin - ToyShop' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/style.css">
</head>
<body class="admin-panel">
<nav class="navbar navbar-expand-lg navbar-dark navbar-toyshop navbar-admin">
    <div class="container">
        <a class="navbar-brand" href="<?= $adminBase ?>/dashboard">ToyShop <span class="admin-badge">Yönetim</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav" aria-controls="adminNav" aria-expanded="false" aria-label="Menü">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav mx-auto nav-main">
                <li class="nav-item"><a class="nav-link<?= $adminPath === '/admin' || $adminPath === '/admin/dashboard' ? ' active' : '' ?>" href="<?= $adminBase ?>/dashboard">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link<?= str_starts_with($adminPath, '/admin/products') ? ' active' : '' ?>" href="<?= $adminBase ?>/products">Ürünler</a></li>
                <li class="nav-item"><a class="nav-link<?= str_starts_with($adminPath, '/admin/orders') ? ' active' : '' ?>" href="<?= $adminBase ?>/orders">Siparişler</a></li>
                <li class="nav-item"><a class="nav-link<?= str_starts_with($adminPath, '/admin/chats') ? ' active' : '' ?>" href="<?= $adminBase ?>/chats">Canlı Destek</a></li>
                <li class="nav-item"><a class="nav-link<?= str_starts_with($adminPath, '/admin/users') ? ' active' : '' ?>" href="<?= $adminBase ?>/users">Kullanıcılar</a></li>
                <li class="nav-item"><a class="nav-link<?= str_starts_with($adminPath, '/admin/logs') ? ' active' : '' ?>" href="<?= $adminBase ?>/logs">Loglar</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2 nav-actions">
                <a class="btn btn-sm btn-outline-light" href="<?= $base ?>/">Siteye Dön</a>
                <a class="btn btn-sm btn-light" href="<?= $adminBase ?>/logout">Çıkış</a>
            </div>
        </div>
    </div>
</nav>
<main class="container my-4 pb-4">
    <?= $content ?? '' ?>
</main>
<footer class="footer-toyshop">
    <div class="container">
        <div class="footer-bottom text-center">© <?= date('Y') ?> ToyShop Admin. Tüm hakları saklıdır.</div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($extraJs)): ?><?= $extraJs ?><?php endif; ?>
</body>
</html>

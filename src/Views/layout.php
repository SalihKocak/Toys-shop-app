<?php
$appUrl = \ToyShop\Infrastructure\Env::get('APP_URL', '');
$base = rtrim(parse_url($appUrl, PHP_URL_PATH) ?: '', '/') ?: '';
$asset = $base . '/assets';
$user = $user ?? \ToyShop\Middleware\AuthMiddleware::requireLogin();
$cartCount = 0;
if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ToyShop' : 'ToyShop - Premium Oyuncak E-Ticaret' ?></title>
    <meta name="description" content="ToyShop – LEGO, koleksiyon ve premium oyuncaklar. Güvenli alışveriş, hızlı kargo.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($asset) ?>/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-toyshop">
    <div class="container">
        <a class="navbar-brand" href="<?= $base ?>/">ToyShop</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Menü">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav mx-auto nav-main">
                <li class="nav-item"><a class="nav-link<?= ($_SERVER['REQUEST_URI'] ?? '/') === '/' ? ' active' : '' ?>" href="<?= $base ?>/">Ana Sayfa</a></li>
                <li class="nav-item"><a class="nav-link<?= str_starts_with(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', '/products') ? ' active' : '' ?>" href="<?= $base ?>/products">Ürünler</a></li>
                <?php if ($user !== null): ?>
                <li class="nav-item"><a class="nav-link<?= str_starts_with(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', '/support') ? ' active' : '' ?>" href="<?= $base ?>/support">Canlı Destek</a></li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-2 nav-actions">
                <button type="button" class="btn btn-sm lang-pill" disabled>TR</button>

                <a class="btn btn-sm btn-warning" href="<?= $base ?>/cart">
                    Sepet <span class="badge bg-dark text-warning cart-count"><?= (int) $cartCount ?></span>
                </a>

                <?php if ($user !== null): ?>
                    <div class="dropdown nav-user-dropdown">
                        <button class="btn btn-sm nav-user-btn dropdown-toggle" type="button" id="navUserMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="nav-user-avatar"><?= strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?></span>
                            <span class="nav-user-name d-none d-md-inline"><?= htmlspecialchars($user['name']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end nav-user-menu" aria-labelledby="navUserMenu">
                            <li><span class="dropdown-header text-muted small">Hesabım</span></li>
                            <li><a class="dropdown-item" href="<?= $base ?>/profile"><span class="nav-user-menu-icon">👤</span> Profil</a></li>
                            <?php if (($user['role'] ?? '') === 'admin'): ?>
                            <li><a class="dropdown-item" href="<?= $base ?>/admin/dashboard"><span class="nav-user-menu-icon">⚙</span> Yönetim</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $base ?>/logout">Çıkış</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="btn btn-sm btn-light" href="<?= $base ?>/login">Giriş</a>
                    <a class="btn btn-sm btn-light" href="<?= $base ?>/register">Kayıt Ol</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<main class="container my-4 pb-4">
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>
    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>
    <?= $content ?? '' ?>
</main>
<footer class="footer-toyshop">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="footer-brand">ToyShop</div>
                <p class="small mb-0 opacity-90">Premium oyuncaklar, LEGO setleri ve koleksiyon ürünleri. Güvenli ödeme, hızlı kargo.</p>
            </div>
            <div class="col-md-2">
                <strong class="d-block mb-2">Alışveriş</strong>
                <ul class="footer-links">
                    <li><a href="<?= $base ?>/products">Tüm Ürünler</a></li>
                    <li><a href="<?= $base ?>/cart">Sepetim</a></li>
                </ul>
            </div>
            <div class="col-md-2">
                <strong class="d-block mb-2">Hesap</strong>
                <ul class="footer-links">
                    <?php if ($user !== null): ?>
                        <li><a href="<?= $base ?>/logout">Çıkış</a></li>
                    <?php else: ?>
                        <li><a href="<?= $base ?>/login">Giriş</a></li>
                        <li><a href="<?= $base ?>/register">Kayıt</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-2">
                <strong class="d-block mb-2">Destek</strong>
                <ul class="footer-links">
                    <li><a href="<?= $base ?>/support">Canlı Destek</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom text-center">© <?= date('Y') ?> ToyShop. Tüm hakları saklıdır.</div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($extraJs)): ?><?= $extraJs ?><?php endif; ?>
</body>
</html>

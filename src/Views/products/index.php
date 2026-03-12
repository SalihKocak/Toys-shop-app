<?php
$pageTitle = 'Ürünler';
$content = ob_start();
$base = $base ?? rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
$products = $products ?? [];
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $base ?>/">Ana Sayfa</a></li>
        <li class="breadcrumb-item active">Ürünler</li>
    </ol>
</nav>

<h1 class="section-title">Ürünler</h1>
<p class="products-intro mb-3">Tüm ürünlerimiz aşağıda listelenmektedir. Fiyata göre sıralayabilirsiniz.</p>

<form method="get" class="filters-toyshop mb-4">
    <div>
        <label class="form-label mb-0 small text-muted">Fiyata göre</label>
        <select name="sort" class="form-select form-select-sm">
            <option value="">Varsayılan</option>
            <option value="price_asc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'price_asc') ? 'selected' : '' ?>>Artan</option>
            <option value="price_desc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'price_desc') ? 'selected' : '' ?>>Azalan</option>
        </select>
    </div>
    <div>
        <button type="submit" class="btn btn-toyshop btn-sm">Filtrele</button>
    </div>
</form>

<?php if (!empty($products)): ?>
<section class="featured-section">
    <div class="featured-grid2 featured-grid2--products">
        <?php foreach ($products as $p): ?>
            <?php $imgUrl = product_image_url($p['id'] ?? '', 0, $base); ?>
            <a class="product-tile text-decoration-none" href="<?= $base ?>/product/<?= htmlspecialchars($p['id']) ?>">
                <div class="product-tile__media">
                    <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                </div>
                <div class="product-tile__body">
                    <div class="product-tile__title"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="product-tile__meta">
                        <span class="pill"><?= htmlspecialchars($p['brand']) ?></span>
                        <span class="pill"><?= htmlspecialchars($p['category']) ?></span>
                    </div>
                    <div class="product-tile__bottom">
                        <p class="product-tile__price"><?= number_format((float)($p['price'] ?? 0), 2, ',', '.') ?> ₺</p>
                        <span class="product-tile__stock">Stok: <?= (int)($p['stock'] ?? 0) ?></span>
                    </div>
                    <span class="btn btn-toyshop product-tile__cta">Detay</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php else: ?>
<div class="empty-state">
    <p>Bu kriterlere uygun ürün bulunamadı.</p>
    <a class="btn btn-toyshop" href="<?= $base ?>/products">Filtreyi Temizle</a>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';

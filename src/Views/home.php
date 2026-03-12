<?php
$content = ob_start();
$pageTitle = 'Ana Sayfa';
$base = $base ?? rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
$featuredProducts = $featuredProducts ?? [];
$user = \ToyShop\Middleware\AuthMiddleware::requireLogin();
?>
<div class="full-bleed mb-4">
    <section class="home-hero home-hero--full">
        <video autoplay muted loop playsinline>
            <source src="<?= $base ?>/assets/home-hero.mp4" type="video/mp4">
        </video>
        <div class="home-hero-content">
            <div class="small text-uppercase opacity-75 mb-2">ToyShop</div>
            <div class="home-hero-title">Dakikalar İçinde Kapında</div>
            <div class="home-hero-lead">LEGO, koleksiyon figürleri ve premium oyuncaklar. Güvenli alışveriş, hızlı teslimat.</div>
            <div class="home-hero-cta d-flex gap-2 flex-wrap">
                <a class="btn btn-toyshop btn-lg" href="<?= $base ?>/products">Ürünleri Keşfet</a>
                <?php if ($user !== null): ?>
                <a class="btn btn-outline-light btn-lg" href="<?= $base ?>/support">Canlı Destek</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<div class="trust-bar">
    <span><strong>Güvenli Ödeme</strong> · SSL ile korunur</span>
    <span><strong>Hızlı Kargo</strong> · Siparişler aynı gün kargoda</span>
    <span><strong>Kolay İade</strong> · 14 gün içinde ücretsiz iade</span>
    <span><strong>Canlı Destek</strong> · 7/24 yardım</span>
</div>

<section class="home-intro">
    <div class="intro-head text-center mb-4">
        <h2 class="intro-title">ToyShop ile Alışveriş Keyfi</h2>
        <p class="intro-lead">LEGO Technic, koleksiyon figürleri ve premium oyuncaklar tek adreste. Güvenli ödeme, hızlı kargo ve 7/24 canlı destekle alışverişin tadını çıkarın.</p>
    </div>
    <div class="intro-cards">
        <div class="intro-card">
            <span class="intro-icon" aria-hidden="true">◆</span>
            <h3 class="intro-card-title">Orijinal Ürünler</h3>
            <p class="intro-card-text">Tüm ürünlerimiz orijinal ve garantilidir. LEGO, lisanslı figürler ve seçkin markalarla güvenle alışveriş yapın.</p>
        </div>
        <div class="intro-card">
            <span class="intro-icon" aria-hidden="true">◇</span>
            <h3 class="intro-card-title">Hızlı Teslimat</h3>
            <p class="intro-card-text">Siparişleriniz aynı gün kargoya verilir. Kapınıza kadar güvenli paketleme ile ulaştırıyoruz.</p>
        </div>
        <div class="intro-card">
            <span class="intro-icon" aria-hidden="true">○</span>
            <h3 class="intro-card-title">Canlı Destek</h3>
            <p class="intro-card-text">Sorularınız için anında yanıt. Destek ekibimiz sohbet ile 7/24 yanınızda.</p>
        </div>
    </div>
</section>

<?php if (!empty($featuredProducts)): ?>
<section class="featured-section mb-4">
    <div class="featured-header">
        <h2 class="section-title">Öne Çıkan Ürünler</h2>
        <a href="<?= $base ?>/products" class="btn btn-outline-toyshop btn-sm">Tümünü Gör</a>
    </div>
    <div class="featured-grid2">
        <?php foreach ($featuredProducts as $p): ?>
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
<div class="text-center py-5">
    <p class="text-muted">Henüz ürün bulunmuyor.</p>
    <a href="<?= $base ?>/products" class="btn btn-toyshop">Ürünler</a>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

<?php
$pageTitle = 'Profil';
$content = ob_start();
$base = $base ?? rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
$user = $user ?? null;
$section = $section ?? 'profil';
$orders = $orders ?? [];
$recentProducts = $recentProducts ?? [];
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $base ?>/">Ana Sayfa</a></li>
        <li class="breadcrumb-item active">Profil</li>
    </ol>
</nav>
<h1 class="section-title">Hesabım</h1>

<div class="row g-4">
    <div class="col-lg-3">
        <nav class="profile-nav">
            <a class="profile-nav-link <?= $section === 'profil' ? 'active' : '' ?>" href="<?= $base ?>/profile?section=profil">Profil</a>
            <a class="profile-nav-link <?= $section === 'orders' ? 'active' : '' ?>" href="<?= $base ?>/profile?section=orders">Siparişlerim</a>
            <a class="profile-nav-link <?= $section === 'recent' ? 'active' : '' ?>" href="<?= $base ?>/profile?section=recent">Önceden Gezindiklerim</a>
        </nav>
    </div>
    <div class="col-lg-9">
        <?php if ($section === 'profil'): ?>
            <div class="profile-card">
                <h2 class="profile-card-title">Profil Bilgileri</h2>
                <p class="support-intro mb-3">Hesap bilgileriniz aşağıdadır.</p>
                <dl class="profile-dl">
                    <dt>Ad Soyad</dt>
                    <dd><?= htmlspecialchars($user['name'] ?? '') ?></dd>
                    <dt>E-posta</dt>
                    <dd><?= htmlspecialchars($user['email'] ?? '') ?></dd>
                </dl>
            </div>
        <?php elseif ($section === 'orders'): ?>
            <div class="profile-card">
                <h2 class="profile-card-title">Siparişlerim</h2>
                <p class="support-intro mb-3">Verdiğiniz siparişlerin listesi.</p>
                <?php if (empty($orders)): ?>
                    <p class="admin-empty">Henüz siparişiniz yok.</p>
                    <a class="btn btn-toyshop mt-2" href="<?= $base ?>/products">Alışverişe Başla</a>
                <?php else: ?>
                    <div class="profile-orders-wrap">
                        <table class="profile-orders-table">
                            <thead>
                                <tr>
                                    <th>Sipariş No</th>
                                    <th>Toplam</th>
                                    <th>Durum</th>
                                    <th>Tarih</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td><code class="admin-code"><?= htmlspecialchars(substr($o['id'] ?? '', 0, 12)) ?></code></td>
                                        <td><strong class="text-accent"><?= number_format((float)($o['total'] ?? 0), 2, ',', '.') ?> ₺</strong></td>
                                        <td><span class="admin-badge-pill admin-badge-pill--status"><?= htmlspecialchars($o['status'] ?? '') ?></span></td>
                                        <td><?= !empty($o['createdAt']) ? date('d.m.Y H:i', (int)$o['createdAt']) : '—' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($section === 'recent'): ?>
            <div class="profile-card">
                <h2 class="profile-card-title">Önceden Gezindiklerim</h2>
                <p class="support-intro mb-3">Son baktığınız ürünler.</p>
                <?php if (empty($recentProducts)): ?>
                    <p class="admin-empty">Henüz görüntülediğiniz ürün yok.</p>
                    <a class="btn btn-toyshop mt-2" href="<?= $base ?>/products">Ürünleri Keşfet</a>
                <?php else: ?>
                    <div class="featured-grid2">
                        <?php foreach ($recentProducts as $p): ?>
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
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';

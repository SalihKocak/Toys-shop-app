<?php
$pageTitle = 'Ürünler';
$content = ob_start();
$base = rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
$adminBase = $base . '/admin';
$products = $products ?? [];
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $adminBase ?>/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Ürünler</li>
    </ol>
</nav>
<h1 class="section-title">Ürünler</h1>
<p class="admin-lead mb-4">Ürünleri ekleyebilir, düzenleyebilir veya silebilirsiniz.</p>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <span class="admin-lead mb-0">Toplam <?= count($products) ?> ürün</span>
    <a class="btn btn-toyshop" href="<?= $adminBase ?>/products/create">Yeni Ürün</a>
</div>

<div class="admin-product-grid">
    <?php foreach ($products as $p): ?>
        <?php $imgUrl = product_image_url($p['id'] ?? '', 0, $base); ?>
        <div class="admin-product-card">
            <div class="admin-product-card__media">
                <img src="<?= $imgUrl ?>" alt="">
            </div>
            <div class="admin-product-card__body">
                <h3 class="admin-product-card__title"><?= htmlspecialchars($p['name'] ?? '') ?></h3>
                <div class="admin-product-card__meta">
                    <?php if (!empty($p['brand'])): ?>
                        <span class="pill"><?= htmlspecialchars($p['brand']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($p['category'])): ?>
                        <span class="pill"><?= htmlspecialchars($p['category']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="admin-product-card__price"><?= number_format((float)($p['price'] ?? 0), 2, ',', '.') ?> ₺</div>
                <div class="admin-product-card__stock">Stok: <?= (int)($p['stock'] ?? 0) ?></div>
                <div class="admin-product-card__status mb-2">
                    <?php if (!empty($p['isActive'])): ?>
                        <span class="admin-badge-pill admin-badge-pill--active">Aktif</span>
                    <?php else: ?>
                        <span class="admin-badge-pill admin-badge-pill--inactive">Pasif</span>
                    <?php endif; ?>
                </div>
                <div class="admin-row-actions">
                    <a class="btn btn-sm btn-outline-toyshop" href="<?= $adminBase ?>/products/<?= htmlspecialchars($p['id']) ?>/edit">Düzenle</a>
                    <button type="button" class="btn btn-sm btn-outline-danger-admin btn-delete-product" data-id="<?= htmlspecialchars($p['id']) ?>">Sil</button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php if (empty($products)): ?>
    <p class="admin-empty mt-4">Henüz ürün yok. <a href="<?= $adminBase ?>/products/create" class="text-accent">Yeni ürün ekleyin</a>.</p>
<?php endif; ?>
<script>
(function(){
    var adminBase = '<?= $adminBase ?>';
    document.querySelectorAll('.btn-delete-product').forEach(function(btn){
        btn.addEventListener('click', function(){
            if (!confirm('Bu ürünü silmek istediğinize emin misiniz?')) return;
            var id = btn.getAttribute('data-id');
            fetch(adminBase + '/products/' + id + '/delete', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r){ return r.json(); })
                .then(function(d){
                    if (d.ok && d.data.redirect) window.location.href = d.data.redirect;
                    else alert(d.error && d.error.message ? d.error.message : 'Silinemedi.');
                });
        });
    });
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';

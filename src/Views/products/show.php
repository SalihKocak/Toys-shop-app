<?php
$pageTitle = $product['name'] ?? 'Ürün';
$content = ob_start();
$base = $base ?? rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
$images = $product['images'] ?? [];
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $base ?>/">Ana Sayfa</a></li>
        <li class="breadcrumb-item"><a href="<?= $base ?>/products">Ürünler</a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
    </ol>
</nav>

<div class="row product-detail">
    <div class="col-lg-5 mb-4">
        <div class="product-detail-image">
            <img src="<?= product_image_url($product['id'] ?? '', 0, $base) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
    </div>
    <div class="col-lg-7">
        <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
        <p class="product-meta"><?= htmlspecialchars($product['brand']) ?> · <?= htmlspecialchars($product['category']) ?></p>
        <p class="product-price"><?= number_format((float)($product['price'] ?? 0), 2, ',', '.') ?> ₺</p>
        <p class="product-description"><?= nl2br(htmlspecialchars($product['description'] ?? '')) ?></p>
        <div class="mb-3">
            <span class="stock-pill">Stok: <?= (int)($product['stock'] ?? 0) ?> adet</span>
        </div>
        <?php if (!empty($product['specs']) && is_array($product['specs'])): ?>
            <div class="specs-card mb-3">
                <div class="fw-bold mb-2">Ürün Özellikleri</div>
                <?php foreach ($product['specs'] as $k => $v): ?>
                    <div class="spec-row">
                        <div class="spec-k"><?= htmlspecialchars((string)$k) ?></div>
                        <div class="spec-v"><?= htmlspecialchars(is_scalar($v) ? (string)$v : json_encode($v, JSON_UNESCAPED_UNICODE)) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="qty-add">
            <input type="number" id="productQty" class="form-control" value="1" min="1" max="<?= max(1, (int)($product['stock'] ?? 0)) ?>">
            <button type="button" class="btn btn-toyshop" id="addToCart" data-product-id="<?= htmlspecialchars($product['id']) ?>">Sepete Ekle</button>
        </div>
    </div>
</div>
<script>
(function(){
    var btn = document.getElementById('addToCart');
    var qty = document.getElementById('productQty');
    if (!btn || !qty) return;
    btn.addEventListener('click', function(){
        var fd = new FormData();
        fd.append('productId', btn.getAttribute('data-product-id'));
        fd.append('qty', qty.value || 1);
        fetch('<?= $base ?>/cart/add', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.ok) {
                    var cnt = document.querySelector('.cart-count');
                    if (cnt) cnt.textContent = d.data.cartCount || 0;
                    alert('Ürün sepete eklendi.');
                } else alert(d.error && d.error.message ? d.error.message : 'Bir hata oluştu.');
            })
            .catch(function(){ alert('İstek başarısız.'); });
    });
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';

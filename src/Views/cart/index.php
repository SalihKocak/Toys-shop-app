<?php
$pageTitle = 'Sepet';
$content = ob_start();
$base = $base ?? rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $base ?>/">Ana Sayfa</a></li>
        <li class="breadcrumb-item"><a href="<?= $base ?>/products">Ürünler</a></li>
        <li class="breadcrumb-item active">Sepet</li>
    </ol>
</nav>

<h1 class="section-title">Sepetim</h1>

<?php if (empty($items)): ?>
    <div class="empty-state">
        <p>Sepetiniz şu an boş.</p>
        <a class="btn btn-toyshop" href="<?= $base ?>/products">Alışverişe Başla</a>
    </div>
<?php else: ?>
    <div class="table-responsive cart-table">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Ürün</th><th>Birim Fiyat</th><th>Adet</th><th>Toplam</th><th width="80"></th></tr>
            </thead>
            <tbody>
                <?php foreach ($items as $row): ?>
                    <tr data-product-id="<?= htmlspecialchars($row['product']['id']) ?>">
                        <td><strong><?= htmlspecialchars($row['product']['name']) ?></strong></td>
                        <td><?= number_format($row['product']['price'], 2, ',', '.') ?> ₺</td>
                        <td>
                            <input type="number" class="form-control form-control-sm cart-qty" style="width:70px" value="<?= (int)$row['qty'] ?>" min="1" max="<?= max(1, (int)($row['product']['stock'] ?? 0)) ?>">
                        </td>
                        <td class="row-subtotal fw-bold"><?= number_format($row['subtotal'], 2, ',', '.') ?> ₺</td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger cart-remove">Kaldır</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="cart-total-box d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="total-label">Genel Toplam</span>
        <span class="total-value" id="cartTotal"><?= number_format($total ?? 0, 2, ',', '.') ?> ₺</span>
    </div>
    <div class="mt-3">
        <a class="btn btn-toyshop btn-lg" href="<?= $base ?>/checkout">Ödemeye Geç</a>
        <a class="btn btn-outline-secondary ms-2" href="<?= $base ?>/products">Alışverişe Devam</a>
    </div>
<?php endif; ?>
<script>
(function(){
    var base = '<?= $base ?>';
    function updateRow(tr){
        var qty = parseInt(tr.querySelector('.cart-qty').value, 10) || 0;
        var price = parseFloat(tr.querySelector('td:nth-child(2)').textContent.replace(/[^\d,]/g,'').replace(',','.')) || 0;
        tr.querySelector('.row-subtotal').textContent = (price * qty).toFixed(2).replace('.', ',') + ' ₺';
        var fd = new FormData();
        fd.append('productId', tr.getAttribute('data-product-id'));
        fd.append('qty', qty);
        fetch(base + '/cart/update', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.ok && document.querySelector('.cart-count')) document.querySelector('.cart-count').textContent = d.data.cartCount || 0;
            });
        recalcTotal();
    }
    function recalcTotal(){
        var t = 0;
        document.querySelectorAll('.row-subtotal').forEach(function(el){
            t += parseFloat(el.textContent.replace(/[^\d,]/g,'').replace(',','.')) || 0;
        });
        var el = document.getElementById('cartTotal');
        if (el) el.textContent = t.toFixed(2).replace('.', ',') + ' ₺';
    }
    document.querySelectorAll('.cart-qty').forEach(function(inp){
        inp.addEventListener('change', function(){ updateRow(inp.closest('tr')); });
    });
    document.querySelectorAll('.cart-remove').forEach(function(btn){
        btn.addEventListener('click', function(){
            var tr = btn.closest('tr');
            var fd = new FormData();
            fd.append('productId', tr.getAttribute('data-product-id'));
            fd.append('qty', 0);
            fetch(base + '/cart/update', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r){ return r.json(); })
                .then(function(d){
                    if (d.ok) { tr.remove(); recalcTotal(); if (document.querySelector('.cart-count')) document.querySelector('.cart-count').textContent = d.data.cartCount || 0; }
                });
        });
    });
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';

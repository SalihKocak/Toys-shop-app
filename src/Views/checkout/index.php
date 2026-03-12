<?php
$pageTitle = 'Ödeme';
$content = ob_start();
$base = $base ?? rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $base ?>/">Ana Sayfa</a></li>
        <li class="breadcrumb-item"><a href="<?= $base ?>/cart">Sepet</a></li>
        <li class="breadcrumb-item active">Ödeme</li>
    </ol>
</nav>

<h1 class="section-title">Sipariş Özeti</h1>

<div class="cart-total-box mb-4">
    <ul class="list-group list-group-flush">
        <?php foreach ($items ?? [] as $row): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0">
                <span><?= htmlspecialchars($row['product']['name']) ?> × <?= (int)$row['qty'] ?></span>
                <span class="fw-bold"><?= number_format($row['subtotal'], 2, ',', '.') ?> ₺</span>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
        <span class="total-label">Toplam</span>
        <span class="total-value"><?= number_format($total ?? 0, 2, ',', '.') ?> ₺</span>
    </div>
</div>
<button type="button" class="btn btn-toyshop btn-lg" id="placeOrder">Siparişi Tamamla</button>
<a class="btn btn-outline-secondary ms-2" href="<?= $base ?>/cart">Sepete Dön</a>
<script>
(function(){
    var base = '<?= $base ?>';
    document.getElementById('placeOrder').addEventListener('click', function(){
        var btn = this;
        btn.disabled = true;
        fetch(base + '/checkout/create', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: '{}' })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.ok && d.data.redirect) window.location.href = d.data.redirect;
                else { alert(d.error && d.error.message ? d.error.message : 'Bir hata oluştu.'); btn.disabled = false; }
            })
            .catch(function(){ alert('İstek başarısız.'); btn.disabled = false; });
    });
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';

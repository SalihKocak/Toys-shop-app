<?php
$pageTitle = 'Siparişler';
$content = ob_start();
$base = rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
$adminBase = $base . '/admin';
$orders = $orders ?? [];
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $adminBase ?>/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Siparişler</li>
    </ol>
</nav>
<h1 class="section-title">Siparişler</h1>
<p class="admin-lead mb-4">Tüm siparişleri görüntüleyebilirsiniz.</p>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <span class="admin-lead mb-0">Toplam <?= count($orders) ?> sipariş</span>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Kullanıcı ID</th>
                <th>Toplam</th>
                <th>Durum</th>
                <th>Tarih</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
                <?php $status = $o['status'] ?? ''; ?>
                <tr data-order-id="<?= htmlspecialchars($o['id'] ?? '') ?>">
                    <td><code class="admin-code"><?= htmlspecialchars(substr($o['id'] ?? '', 0, 12)) ?></code></td>
                    <td><code class="admin-code"><?= htmlspecialchars(substr($o['userId'] ?? '', 0, 12)) ?></code></td>
                    <td><strong class="text-accent"><?= number_format((float)($o['total'] ?? 0), 2, ',', '.') ?> ₺</strong></td>
                    <td><span class="admin-badge-pill admin-badge-pill--status order-status-badge"><?= htmlspecialchars($status ?: '—') ?></span></td>
                    <td><?= isset($o['createdAt']) ? date('d.m.Y H:i', (int)$o['createdAt']) : '—' ?></td>
                    <td>
                        <select class="form-select form-select-sm admin-order-status-select" style="width: auto; min-width: 110px;">
                            <option value="created" <?= $status === 'created' ? 'selected' : '' ?>>Oluşturuldu</option>
                            <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Ödendi</option>
                            <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>Kargoya Verildi</option>
                            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>İptal</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-toyshop ms-1 btn-order-apply">Uygula</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php if (empty($orders)): ?>
    <p class="admin-empty mt-3">Henüz sipariş yok.</p>
<?php endif; ?>
<script>
(function(){
    var adminBase = '<?= $adminBase ?>';
    var statusLabels = { created: 'Oluşturuldu', paid: 'Ödendi', shipped: 'Kargoya Verildi', cancelled: 'İptal' };
    document.querySelectorAll('.btn-order-apply').forEach(function(btn){
        btn.addEventListener('click', function(){
            var row = btn.closest('tr');
            var orderId = row.getAttribute('data-order-id');
            var select = row.querySelector('.admin-order-status-select');
            var status = select ? select.value : '';
            if (!orderId || !status) return;
            btn.disabled = true;
            fetch(adminBase + '/orders/' + orderId + '/status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ status: status })
            })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.ok && d.data && d.data.status) {
                    var badge = row.querySelector('.order-status-badge');
                    if (badge) badge.textContent = statusLabels[d.data.status] || d.data.status;
                } else alert(d.error && d.error.message ? d.error.message : 'Güncellenemedi.');
            })
            .catch(function(){ alert('İstek başarısız.'); })
            .finally(function(){ btn.disabled = false; });
        });
    });
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';

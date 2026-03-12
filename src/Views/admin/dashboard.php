<?php
$pageTitle = 'Dashboard';
$content = ob_start();
$base = rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
$adminBase = $base . '/admin';
$recentOrders = $recentOrders ?? [];
?>
<h1 class="section-title">Dashboard</h1>
<p class="admin-lead mb-4">Yönetim paneline hoş geldiniz. Özet bilgiler aşağıdadır.</p>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="<?= $adminBase ?>/products" class="admin-stat-card text-decoration-none">
            <div class="admin-stat-card__body">
                <span class="admin-stat-card__label">Ürünler</span>
                <span class="admin-stat-card__value"><?= (int)($productsCount ?? 0) ?></span>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= $adminBase ?>/users" class="admin-stat-card text-decoration-none">
            <div class="admin-stat-card__body">
                <span class="admin-stat-card__label">Kullanıcılar</span>
                <span class="admin-stat-card__value"><?= (int)($usersCount ?? 0) ?></span>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= $adminBase ?>/chats" class="admin-stat-card text-decoration-none">
            <div class="admin-stat-card__body">
                <span class="admin-stat-card__label">Açık Sohbetler</span>
                <span class="admin-stat-card__value"><?= (int)($openChatsCount ?? 0) ?></span>
            </div>
        </a>
    </div>
</div>

<h2 class="admin-subtitle mb-3">Son Siparişler</h2>
<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Toplam</th>
                <th>Durum</th>
                <th>Tarih</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentOrders as $o): ?>
                <tr>
                    <td><code class="admin-code"><?= htmlspecialchars(substr($o['id'] ?? '', 0, 8)) ?></code></td>
                    <td><strong class="text-accent"><?= number_format((float)($o['total'] ?? 0), 2, ',', '.') ?> ₺</strong></td>
                    <td><span class="admin-badge-pill"><?= htmlspecialchars($o['status'] ?? '') ?></span></td>
                    <td><?= isset($o['createdAt']) ? date('d.m.Y H:i', (int)$o['createdAt']) : '' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php if (empty($recentOrders)): ?>
    <p class="admin-empty">Henüz sipariş yok.</p>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

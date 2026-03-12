<?php
$pageTitle = 'Canlı Destek';
$content = ob_start();
$base = rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
$adminBase = $base . '/admin';
$openThreads = $openThreads ?? [];
$closedThreads = $closedThreads ?? [];
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $adminBase ?>/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Canlı Destek</li>
    </ol>
</nav>
<h1 class="section-title">Canlı Destek</h1>
<p class="admin-lead mb-4">Destek taleplerini görüntüleyin, yanıt verin veya geçmiş sohbetleri okuyun.</p>

<h2 class="h5 mb-2">Açık sohbetler</h2>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <span class="admin-lead mb-0"><?= count($openThreads) ?> açık sohbet</span>
</div>
<div class="admin-table-wrap mb-4">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Konu</th>
                <th>ID</th>
                <th>Durum</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($openThreads as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['subject'] ?? 'Konu yok') ?></strong></td>
                    <td><code class="admin-code"><?= htmlspecialchars(substr($t['id'] ?? '', 0, 12)) ?></code></td>
                    <td><span class="admin-badge-pill admin-badge-pill--active">Açık</span></td>
                    <td>
                        <a class="btn btn-sm btn-outline-toyshop" href="<?= $adminBase ?>/chats/<?= htmlspecialchars($t['id']) ?>">Sohbete Git</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php if (empty($openThreads)): ?>
    <p class="admin-empty mb-4">Açık sohbet yok.</p>
<?php endif; ?>

<h2 class="h5 mb-2">Geçmiş sohbetler (kapalı)</h2>
<p class="admin-lead small mb-2">Geçmiş sohbetleri görüntüleyebilirsiniz; yanıt yazılamaz.</p>
<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Konu</th>
                <th>ID</th>
                <th>Durum</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($closedThreads as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['subject'] ?? 'Konu yok') ?></strong></td>
                    <td><code class="admin-code"><?= htmlspecialchars(substr($t['id'] ?? '', 0, 12)) ?></code></td>
                    <td><span class="admin-badge-pill">Kapalı</span></td>
                    <td>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= $adminBase ?>/chats/<?= htmlspecialchars($t['id']) ?>">Görüntüle</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php if (empty($closedThreads)): ?>
    <p class="admin-empty mt-3">Geçmiş sohbet yok.</p>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';

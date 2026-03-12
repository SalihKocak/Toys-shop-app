<?php
$pageTitle = 'Kullanıcılar';
$content = ob_start();
$base = rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
$adminBase = $base . '/admin';
$users = $users ?? [];
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $adminBase ?>/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Kullanıcılar</li>
    </ol>
</nav>
<h1 class="section-title">Kullanıcılar</h1>
<p class="admin-lead mb-4">Siteye kayıt olan kullanıcılar listelenmektedir.</p>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <span class="admin-lead mb-0">Toplam <?= count($users) ?> kullanıcı</span>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Ad</th>
                <th>E-posta</th>
                <th>Rol</th>
                <th>Kayıt Tarihi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($u['name'] ?? '') ?></strong></td>
                    <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                    <td>
                        <?php if (($u['role'] ?? '') === 'admin'): ?>
                            <span class="admin-badge-pill admin-badge-pill--active">Admin</span>
                        <?php else: ?>
                            <span class="admin-badge-pill admin-badge-pill--status">Müşteri</span>
                        <?php endif; ?>
                    </td>
                    <td><?= !empty($u['createdAt']) ? date('d.m.Y H:i', (int) $u['createdAt']) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php if (empty($users)): ?>
    <p class="admin-empty mt-3">Henüz kullanıcı yok.</p>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';

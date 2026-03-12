<?php
$pageTitle = 'Loglar';
$content = ob_start();
$base = rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
$adminBase = $base . '/admin';
$lines = $lines ?? [];
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $adminBase ?>/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Loglar</li>
    </ol>
</nav>
<h1 class="section-title">Loglar</h1>
<p class="admin-lead mb-4">Son log kayıtları (en fazla 500 satır).</p>

<div class="admin-log-box">
    <pre class="admin-log-pre"><?php
        if (empty($lines)) {
            echo 'Log dosyası bulunamadı veya boş.';
        } else {
            echo htmlspecialchars(implode("\n", $lines));
        }
    ?></pre>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';

<?php
$pageTitle = 'Canlı Destek';
$content = ob_start();
$base = $base ?? rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="auth-card text-center">
            <h2 class="mb-3">Canlı Destek</h2>
            <p class="support-intro mb-4">Destek ekibimize ulaşmak ve sohbet başlatmak için giriş yapmanız gerekmektedir.</p>
            <a class="btn btn-toyshop me-2" href="<?= $base ?>/login?redirect=<?= urlencode('/support') ?>">Giriş Yap</a>
            <a class="btn btn-outline-toyshop" href="<?= $base ?>/register">Kayıt Ol</a>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';

<?php
$pageTitle = 'Giriş';
$content = ob_start();
$base = $base ?? rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
$baseUrl = rtrim((string) \ToyShop\Infrastructure\Env::get('APP_URL', ''), '/');
$redirect = isset($_GET['redirect']) ? trim((string) $_GET['redirect']) : '';
if ($redirect !== '' && !str_starts_with($redirect, '/')) {
    $redirect = '';
}
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="auth-card">
            <h2>Giriş Yap</h2>
            <form id="loginForm">
                <?php if ($redirect !== ''): ?>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">E-posta</label>
                    <input type="email" name="email" class="form-control" required placeholder="ornek@email.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Şifre</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-toyshop w-100">Giriş Yap</button>
            </form>
            <p class="mt-3 mb-0 small text-muted text-center">Hesabınız yok mu? <a href="<?= $base ?>/register">Kayıt olun</a>.</p>
        </div>
    </div>
</div>
<script>
document.getElementById('loginForm').addEventListener('submit', function(e){
    e.preventDefault();
    var form = this;
    var payload = { email: (form.querySelector('[name=email]') || {}).value || '', password: (form.querySelector('[name=password]') || {}).value || '' };
    var redirectInput = form.querySelector('[name=redirect]');
    if (redirectInput && redirectInput.value) payload.redirect = redirectInput.value;
    var baseUrl = <?= json_encode($baseUrl) ?>;
    fetch(baseUrl + '/login', {
        method: 'POST',
        body: JSON.stringify(payload),
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.ok && d.data.redirect) window.location.href = d.data.redirect;
            else alert(d.error && d.error.message ? d.error.message : 'Giriş başarısız.');
        })
        .catch(function(){ alert('İstek başarısız.'); });
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';

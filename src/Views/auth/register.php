<?php
$pageTitle = 'Kayıt';
$content = ob_start();
$base = $base ?? rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
$baseUrl = rtrim((string) \ToyShop\Infrastructure\Env::get('APP_URL', ''), '/');
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="auth-card">
            <h2>Hesap Oluştur</h2>
            <form id="registerForm">
                <div class="mb-3">
                    <label class="form-label">Ad Soyad</label>
                    <input type="text" name="name" class="form-control" required placeholder="Adınız Soyadınız">
                </div>
                <div class="mb-3">
                    <label class="form-label">E-posta</label>
                    <input type="email" name="email" class="form-control" required placeholder="ornek@email.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Şifre (en az 6 karakter)</label>
                    <input type="password" name="password" class="form-control" required minlength="6" placeholder="••••••">
                </div>
                <button type="submit" class="btn btn-toyshop w-100">Kayıt Ol</button>
            </form>
            <p class="mt-3 mb-0 small text-muted text-center">Zaten hesabınız var mı? <a href="<?= $base ?>/login">Giriş yapın</a>.</p>
        </div>
    </div>
</div>
<script>
document.getElementById('registerForm').addEventListener('submit', function(e){
    e.preventDefault();
    var form = this;
    var payload = {
        name: (form.querySelector('[name=name]') || {}).value || '',
        email: (form.querySelector('[name=email]') || {}).value || '',
        password: (form.querySelector('[name=password]') || {}).value || ''
    };
    var baseUrl = <?= json_encode($baseUrl) ?>;
    fetch(baseUrl + '/register', {
        method: 'POST',
        body: JSON.stringify(payload),
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.ok && d.data.redirect) window.location.href = d.data.redirect;
            else alert(d.error && d.error.message ? d.error.message : 'Kayıt başarısız.');
        })
        .catch(function(){ alert('İstek başarısız.'); });
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';

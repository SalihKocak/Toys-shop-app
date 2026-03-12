<?php
$content = ob_start();
$base = rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
$adminBase = $base . '/admin';
?>
<div class="row justify-content-center">
    <div class="col-md-4">
        <h2 class="mb-4">Admin Giriş</h2>
        <form id="adminLoginForm">
            <div class="mb-3">
                <label class="form-label">E-posta</label>
                <input type="text" name="email" class="form-control" placeholder="admin123" required autocomplete="username">
            </div>
            <div class="mb-3">
                <label class="form-label">Şifre</label>
                <input type="password" name="password" class="form-control" placeholder="admin123" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary">Giriş</button>
        </form>
    </div>
</div>
<script>
document.getElementById('adminLoginForm').addEventListener('submit', function(e){
    e.preventDefault();
    var fd = new FormData(this);
    fetch('<?= $adminBase ?>/login', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
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
$pageTitle = 'Admin Giriş';
require __DIR__ . '/layout.php';

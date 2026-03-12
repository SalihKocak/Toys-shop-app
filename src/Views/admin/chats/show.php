<?php
$pageTitle = 'Sohbet';
$content = ob_start();
$base = rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
$adminBase = $base . '/admin';
$threadId = $thread['id'] ?? '';
$lastAfter = 0;
if (!empty($messages)) {
    $createdAts = array_filter(array_column($messages, 'createdAt'));
    if ($createdAts !== []) {
        $lastAfter = (int) max($createdAts);
    }
}
$messages = $messages ?? [];
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $adminBase ?>/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= $adminBase ?>/chats">Canlı Destek</a></li>
        <li class="breadcrumb-item active">Sohbet</li>
    </ol>
</nav>
<h1 class="section-title"><?= htmlspecialchars($thread['subject'] ?? 'Sohbet') ?></h1>
<p class="admin-lead mb-3">
    <a href="<?= $adminBase ?>/chats" class="text-accent">&larr; Sohbet listesine dön</a>
    <span class="ms-2 opacity-75 small">· Mesajlar otomatik yenilenir (gerçek zamanlı)</span>
</p>

<div id="adminChatWrap" class="admin-chat-box">
    <div id="adminChatMessages" class="admin-chat-messages">
        <?php foreach ($messages as $m): ?>
            <?php
            $isAdmin = ($m['senderRole'] ?? '') === 'admin';
            $name = $isAdmin ? 'Destek' : 'Müşteri';
            $ts = isset($m['createdAt']) ? (is_numeric($m['createdAt']) ? date('H:i', (int)($m['createdAt']/1000)) : '') : '';
            ?>
            <div class="admin-chat-msg <?= $isAdmin ? 'admin-chat-msg--right' : '' ?>">
                <span class="admin-chat-bubble <?= $isAdmin ? 'admin-chat-bubble--admin' : 'admin-chat-bubble--customer' ?>">
                    <strong><?= htmlspecialchars($name) ?></strong>: <?= htmlspecialchars($m['text'] ?? '') ?>
                    <span class="admin-chat-time"><?= $ts ?></span>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
    <div id="adminChatInputRow" class="admin-chat-input-row" <?= (($thread['status'] ?? '') === 'closed') ? 'style="display: none;"' : '' ?>>
        <input type="text" id="adminChatInput" class="form-control admin-form-control" placeholder="Mesaj yazın..." maxlength="2000">
        <button type="button" class="btn btn-toyshop" id="adminBtnSend">Gönder</button>
    </div>
    <?php if (($thread['status'] ?? '') === 'open'): ?>
        <button type="button" class="btn btn-outline-danger-admin btn-sm mt-2" id="adminBtnClose">Sohbeti Kapat</button>
    <?php else: ?>
        <p class="admin-empty small mt-2 mb-0">Bu sohbet kapatıldı. Geçmiş mesajlar yukarıda görüntülenmektedir.</p>
    <?php endif; ?>
</div>
<script>
(function(){
    var adminBase = '<?= $adminBase ?>';
    var threadId = '<?= htmlspecialchars($threadId) ?>';
    var POLL_MS = 3000; /* 3 sn – yeni mesajları kontrol etme aralığı */
    var lastAfter = <?= (int) $lastAfter ?>;
    var pollTimer = null;
    var isClosed = <?= ($thread['status'] ?? '') === 'closed' ? 'true' : 'false' ?>;

    function esc(s){ var d=document.createElement('div'); d.textContent=s; return d.innerHTML; }

    function renderMessage(m){
        var isAdmin = (m.senderRole || '') === 'admin';
        var name = isAdmin ? 'Destek' : 'Müşteri';
        var ts = m.createdAt ? new Date(m.createdAt).toLocaleTimeString('tr-TR') : '';
        var msgClass = 'admin-chat-msg' + (isAdmin ? ' admin-chat-msg--right' : '');
        var bubbleClass = 'admin-chat-bubble ' + (isAdmin ? 'admin-chat-bubble--admin' : 'admin-chat-bubble--customer');
        return '<div class="'+ msgClass +'"><span class="'+ bubbleClass +'"><strong>'+ esc(name) +'</strong>: '+ esc(m.text || '') +' <span class="admin-chat-time">'+ esc(ts) +'</span></span></div>';
    }

    function appendMessages(messages){
        if (!messages || !messages.length) return;
        var box = document.getElementById('adminChatMessages');
        messages = messages.filter(function(m){ return (m.createdAt || 0) > lastAfter; });
        if (!messages.length) return;
        messages.forEach(function(m){
            var created = m.createdAt || 0;
            if (created > lastAfter) lastAfter = created;
            box.insertAdjacentHTML('beforeend', renderMessage(m));
        });
        box.scrollTop = box.scrollHeight;
    }

    function poll(){
        if (!threadId || isClosed) return;
        fetch(adminBase + '/chats/' + threadId + '/poll?after=' + lastAfter, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.ok && d.data.messages && d.data.messages.length) appendMessages(d.data.messages);
                if (d.ok && d.data.thread && d.data.thread.status === 'closed') {
                    isClosed = true;
                    if (pollTimer) clearInterval(pollTimer);
                    pollTimer = null;
                    var row = document.getElementById('adminChatInputRow');
                    if (row) row.style.display = 'none';
                }
            })
            .catch(function(){});
    }

    if (!isClosed) {
        pollTimer = setInterval(poll, POLL_MS);
    }

    document.getElementById('adminBtnSend').addEventListener('click', function(){ sendMsg(); });
    document.getElementById('adminChatInput').addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); sendMsg(); } });

    function sendMsg(){
        var input = document.getElementById('adminChatInput');
        var text = (input.value || '').trim();
        if (!text || !threadId) return;
        input.value = '';
        fetch(adminBase + '/chats/' + threadId + '/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ text: text })
        })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.ok && d.data) { appendMessages([d.data]); document.getElementById('adminChatMessages').scrollTop = document.getElementById('adminChatMessages').scrollHeight; }
                else if (!d.ok && d.error && d.error.message) alert(d.error.message);
            })
            .catch(function(){ alert('Gönderilemedi.'); });
    }

    var closeBtn = document.getElementById('adminBtnClose');
    if (closeBtn) {
        closeBtn.addEventListener('click', function(){
            if (!confirm('Bu sohbeti kapatmak istediğinize emin misiniz?')) return;
            fetch(adminBase + '/chats/' + threadId + '/close', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: '{}' })
                .then(function(r){ return r.json(); })
                .then(function(d){
                    if (d.ok) { isClosed = true; if (pollTimer) clearInterval(pollTimer); if (d.data.redirect) window.location.href = d.data.redirect; }
                });
        });
    }
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';

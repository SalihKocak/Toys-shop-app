<?php
$pageTitle = 'Canlı Destek';
$content = ob_start();
$base = $base ?? rtrim(parse_url(\ToyShop\Infrastructure\Env::get('APP_URL', ''), PHP_URL_PATH) ?: '', '/') ?: '';
?>
<h1 class="section-title">Canlı Destek</h1>
<p class="support-intro mb-3">Sorularınız için destek ekibimizle anında iletişime geçin. Sohbeti başlattıktan sonra admin ile gerçek zamanlı mesajlaşabilirsiniz.</p>

<div id="chatWrap" class="support-chat-wrap">
    <div id="chatStart" class="support-chat-start">
        <div id="myThreadsList" class="support-my-threads mb-4" style="display: none;">
            <p class="support-intro mb-2"><strong>Mevcut sohbetleriniz</strong></p>
            <div id="myThreadsItems"></div>
            <hr class="my-3">
        </div>
        <p class="support-intro mb-3">Yeni sohbet başlatarak destek ekibimize ulaşın.</p>
        <input type="text" id="chatSubject" class="form-control support-chat-input mx-auto mb-3" placeholder="Konu (isteğe bağlı)" value="Destek talebi" maxlength="200">
        <button type="button" class="btn btn-toyshop btn-lg" id="btnStartChat">Yeni Sohbet Başlat</button>
    </div>
    <div id="chatPanel" class="support-chat-panel" style="display: none;">
        <div class="support-chat-header">
            <span class="support-chat-header-dot"></span>
            <span id="chatThreadSubject" class="support-chat-header-title">Sohbet</span>
        </div>
        <div id="chatMessages" class="support-chat-messages"></div>
        <div id="chatInputRow" class="support-chat-input-row">
            <input type="text" id="chatInput" class="form-control support-chat-input" placeholder="Mesajınızı yazın..." maxlength="2000">
            <button type="button" class="btn btn-toyshop" id="btnSend">Gönder</button>
        </div>
        <div id="chatClosedNotice" class="support-chat-closed small text-muted mt-2" style="display: none;">Bu sohbet sonlandırıldı. Yeni bir sohbet başlatabilirsiniz.</div>
        <div id="chatActionsRow" class="mt-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCloseChat">Sohbeti Sonlandır</button>
            <a href="<?= $base ?? '' ?>/support" class="btn btn-outline-secondary btn-sm ms-2" id="btnBackToList" style="display: none;">Sohbet listesine dön</a>
        </div>
    </div>
</div>
<script>
(function(){
    var base = '<?= $base ?>';
    var POLL_MS = 3000; /* 3 sn – yeni mesajları kontrol etme aralığı */
    var threadId = null;
    var lastAfter = 0;
    var pollTimer = null;
    var isClosed = false;

    function esc(s){ var d=document.createElement('div'); d.textContent=s; return d.innerHTML; }

    function setThreadClosed(){
        isClosed = true;
        stopPoll();
        document.getElementById('chatInputRow').style.display = 'none';
        document.getElementById('chatClosedNotice').style.display = 'block';
        document.getElementById('btnCloseChat').style.display = 'none';
        document.getElementById('btnBackToList').style.display = 'inline-block';
    }

    function renderMessage(m){
        var isCustomer = (m.senderRole || '') === 'customer';
        var name = isCustomer ? 'Siz' : 'Destek';
        var ts = m.createdAt ? new Date(m.createdAt).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' }) : '';
        var msgClass = 'support-chat-msg' + (isCustomer ? ' support-chat-msg--right' : '');
        var bubbleClass = 'support-chat-bubble ' + (isCustomer ? 'support-chat-bubble--me' : 'support-chat-bubble--support');
        return '<div class="'+ msgClass +'"><span class="'+ bubbleClass +'"><strong>'+ esc(name) +'</strong>: '+ esc(m.text || '') +' <span class="support-chat-time">'+ esc(ts) +'</span></span></div>';
    }

    function appendMessages(messages){
        if (!messages || !messages.length) return;
        var box = document.getElementById('chatMessages');
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
        var url = base + '/chat/poll?threadId=' + encodeURIComponent(threadId) + '&after=' + lastAfter;
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.ok && d.data.messages && d.data.messages.length) appendMessages(d.data.messages);
                if (d.ok && d.data.thread && d.data.thread.status === 'closed') setThreadClosed();
            })
            .catch(function(){});
    }

    function startPoll(){
        if (pollTimer) return;
        pollTimer = setInterval(poll, POLL_MS);
        poll();
    }

    function stopPoll(){
        if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
    }

    function openPanel(thread, messages){
        threadId = thread.id;
        lastAfter = 0;
        isClosed = (thread.status || '') === 'closed';
        document.getElementById('chatStart').style.display = 'none';
        document.getElementById('chatPanel').style.display = 'flex';
        document.getElementById('chatThreadSubject').textContent = thread.subject || 'Sohbet';
        document.getElementById('chatMessages').innerHTML = '';
        if (messages && messages.length) {
            messages.forEach(function(m){ var c = m.createdAt || 0; if (c > lastAfter) lastAfter = c; });
            messages.forEach(function(m){ document.getElementById('chatMessages').insertAdjacentHTML('beforeend', renderMessage(m)); });
            document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
        }
        if (isClosed) {
            setThreadClosed();
        } else {
            startPoll();
            document.getElementById('chatInput').focus();
        }
    }

    fetch(base + '/chat/my-threads', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (!d.ok || !d.data || !d.data.length) return;
            var list = document.getElementById('myThreadsList');
            var container = document.getElementById('myThreadsItems');
            list.style.display = 'block';
            d.data.forEach(function(t){
                var open = (t.status || '') === 'open';
                var label = (t.subject || 'Sohbet') + (open ? ' (açık)' : ' (kapalı)');
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm ' + (open ? 'btn-outline-toyshop' : 'btn-outline-secondary') + ' me-2 mb-2';
                btn.textContent = open ? 'Sohbete devam et' : 'Görüntüle';
                btn.addEventListener('click', function(){
                    fetch(base + '/chat/thread?threadId=' + encodeURIComponent(t.id), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(function(r){ return r.json(); })
                        .then(function(res){
                            if (res.ok && res.data) openPanel(res.data.thread, res.data.messages || []);
                            else alert('Sohbet yüklenemedi.');
                        });
                });
                var span = document.createElement('span');
                span.className = 'align-middle';
                span.textContent = label;
                container.appendChild(span);
                container.appendChild(btn);
                container.appendChild(document.createElement('br'));
            });
        })
        .catch(function(){});

    document.getElementById('btnStartChat').addEventListener('click', function(){
        var subject = (document.getElementById('chatSubject').value || '').trim() || 'Destek talebi';
        fetch(base + '/chat/start', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ subject: subject })
        })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.ok && d.data.id){
                    isClosed = false;
                    document.getElementById('chatInputRow').style.display = '';
                    document.getElementById('chatClosedNotice').style.display = 'none';
                    document.getElementById('btnCloseChat').style.display = 'inline-block';
                    document.getElementById('btnBackToList').style.display = 'none';
                    openPanel(d.data, d.data.messages || []);
                    document.getElementById('chatInput').focus();
                } else alert(d.error && d.error.message ? d.error.message : 'Sohbet başlatılamadı.');
            })
            .catch(function(){ alert('Bağlantı hatası.'); });
    });

    document.getElementById('btnSend').addEventListener('click', function(){ sendMsg(); });
    document.getElementById('chatInput').addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); sendMsg(); } });

    document.getElementById('btnCloseChat').addEventListener('click', function(){
        if (!threadId || isClosed) return;
        if (!confirm('Bu sohbeti sonlandırmak istediğinize emin misiniz?')) return;
        fetch(base + '/chat/close', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ threadId: threadId })
        })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.ok) setThreadClosed();
                else if (d.error && d.error.message) alert(d.error.message);
            });
    });

    function sendMsg(){
        var input = document.getElementById('chatInput');
        var text = (input.value || '').trim();
        if (!text || !threadId || isClosed) return;
        input.value = '';
        fetch(base + '/chat/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ threadId: threadId, text: text })
        })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.ok && d.data) appendMessages([d.data]);
                else if (!d.ok && d.error && d.error.message) alert(d.error.message);
            })
            .catch(function(){ alert('Gönderilemedi.'); });
    }
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';

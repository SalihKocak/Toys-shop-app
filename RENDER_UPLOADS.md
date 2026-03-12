# Render'da Ürün Görsellerinin Kalıcı Olması

Render’da dosya sistemi **geçici**dir: her deploy veya restart’ta `public/uploads` silinir. Bu yüzden admin panelden yüklediğin ürün görselleri kaybolur veya 404 verir.

## Çözüm: Render Persistent Disk

Görsellerin kalıcı olması için Render’da **Disk** ekleyip uygulamanın kullanmasını sağlaman yeterli.

### Adımlar

1. **Render Dashboard** → Servisini seç → **Settings**.
2. **Disks** bölümünde **Add Disk**.
3. **Mount Path** olarak tam olarak şunu yaz: `/data`
4. İstediğin boyutu seç (örn. 1 GB) → **Save**.
5. Servisi **Manual Deploy** veya bir commit ile yeniden deploy et.

### Ne olur?

- Container ayağa kalkarken `scripts/render-start.sh` çalışır.
- `/data` klasörü varsa (yani diski eklediysen) `public/uploads` → `/data/uploads` ile değiştirilir (symlink).
- Yüklenen tüm görseller `/data/uploads` içine yazılır ve **deploy/restart sonrası da kalır**.

### Disk eklemeden çalışır mı?

Evet. Disk yoksa uygulama `public/uploads` kullanmaya devam eder; sadece yüklenen görseller deploy sonrası silinir. Görsellerin kalıcı olması için diski eklemen gerekir.

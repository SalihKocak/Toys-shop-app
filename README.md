# ToyShop

Premium oyuncak e-ticaret uygulaması: müşteri sitesi, admin paneli ve canlı destek (AJAX polling).

## Gereksinimler

- PHP 8.2+
- Composer
- MongoDB Atlas hesabı (veya yerel MongoDB)

## Kurulum

1. **Bağımlılıkları yükleyin**
   ```bash
   composer install
   ```

2. **Ortam dosyasını oluşturun**
   ```bash
   copy .env.example .env
   ```
   Windows’ta: `copy .env.example .env`  
   Linux/macOS: `cp .env.example .env`

3. **`.env` içinde şunları ayarlayın**
   - `MONGODB_URI`: MongoDB bağlantı string’iniz (Atlas veya yerel). **Bu değeri asla koda yazmayın; sadece `.env` içinde tutun.**
   - `MONGODB_DB`: Veritabanı adı (örn. `toyshop_app`)
   - İsteğe bağlı: `APP_URL` (örn. `http://localhost:8000`), `SESSION_NAME`, `APP_ENV`

4. **Veritabanını seed’leyin**
   ```bash
   php scripts/seed.php
   ```
   Bu işlem:
   - Gerekli index’leri oluşturur
   - Admin kullanıcı ekler: **admin123@gmail.com** / **admin123**
   - 6 örnek ürün (LEGO, figür, koleksiyon) ekler
   - 1 örnek canlı destek thread’i ve birkaç mesaj ekler

5. **Yerel sunucuyu başlatın**
   ```bash
   php -S localhost:8000 -t public
   ```

Tarayıcıda:

- **Site:** http://localhost:8000  
- **Admin panel:** http://localhost:8000/admin/login (admin123@gmail.com / admin123)

---

## Başka bilgisayarda (arkadaşında) çalıştırma

**Sadece klasörü kopyalayınca uygulama kendiliğinden çalışmaz.** O bilgisayarda şunlar gerekir:

1. **PHP 8.2+** (Laragon kullanıyorsa zaten var.)
2. **MongoDB** (yerel kurulum veya **MongoDB Atlas** ücretsiz hesabı).
3. **.env ayarı** – Projeyi verirken **.env dosyasını paylaşmayın** (içinde sizin MongoDB şifreniz var). Arkadaş kendi `.env` dosyasını oluşturmalı:
   - Proje klasöründe `copy .env.example .env` (Windows) veya `cp .env.example .env` (Mac/Linux)
   - `.env` içinde `MONGODB_URI` ve `MONGODB_DB` kendi MongoDB’sine göre düzenlenmeli (Atlas kullanıyorsa kendi connection string’i).
4. **Composer** – Eğer projeyi **vendor klasörü olmadan** gönderdiyseniz, arkadaş proje klasöründe `composer install` çalıştırmalı. **vendor’ı da gönderdiyseniz** bu adım gerekmez.
5. **Seed** – İlk kullanımda: `php scripts/seed.php` (admin + örnek ürünler/siparişler oluşur).

### Laragon ile çalıştırma

- Laragon’u açıp **Start All** (Apache/Nginx + MySQL) yeterli değil; bu uygulama **MySQL kullanmıyor**, **MongoDB** kullanıyor.
- Laragon sadece **PHP** sağlar. Proje klasörüne (ör. `C:\laragon\www\ToyShop_App`) gidin, **Laragon Terminal** (veya CMD) açın ve:
  ```bash
  cd C:\laragon\www\ToyShop_App
  php -S localhost:8000 -t public
  ```
- MongoDB için: Laragon’a **MongoDB eklentisi** kurulabilir veya arkadaş **MongoDB Atlas** (ücretsiz) kullanıp `.env` içine kendi bağlantı adresini yazar.
- Tarayıcıda: **http://localhost:8000**

**Kısa özet:** Dosyayı atınca otomatik çalışmaz; PHP + MongoDB + `.env` + (isteğe bağlı) `composer install` ve `php scripts/seed.php` gerekir. Laragon terminalden `php -S localhost:8000 -t public` ile uygulama çalışır, ama MongoDB’nin de o bilgisayarda/Atlas’ta hazır olması gerekir.

---

## MongoDB Atlas

Atlas kullanıyorsanız:

- **Network Access:** IP whitelist’e kendi IP’nizi veya `0.0.0.0/0` (tüm IP’ler) ekleyin; aksi halde bağlantı reddedilir.
- **Bağlantı string:** Cluster → Connect → “Drivers” → Connection string’i kopyalayıp `.env` içindeki `MONGODB_URI` değişkenine yapıştırın. Şifreyi kendiniz yazın.

## Proje yapısı

- `public/` — Giriş noktası (`index.php`), `assets/`, `uploads/`
- `src/Infrastructure/` — Env, Mongo, Logger, Response
- `src/Middleware/` — AuthMiddleware, AdminMiddleware
- `src/Controllers/` — Auth, Product, Cart, Order, Chat
- `src/Controllers/Admin/` — Admin Auth, Dashboard, Product, Order, Chat
- `src/Services/` — Auth, Product, Chat, Order
- `src/Views/` — Layout + sayfalar; `admin/` altında admin şablonları
- `storage/logs/` — Uygulama logları (`app.log`)
- `scripts/` — `seed.php`

## Canlı destek

- Müşteri: `/support` — Sohbeti başlatır, mesaj gönderir; 2 saniyede bir yeni mesajlar için polling yapılır.
- Admin: `/admin/chats` — Açık thread listesi; `/admin/chats/{id}` — Sohbet ekranı (polling + gönder + kapat).

JSON yanıt sözleşmesi: Başarı `{ "ok": true, "data": ... }`, Hata `{ "ok": false, "error": { "code": "...", "message": "..." } }`.

## Güvenlik

- MongoDB URI ve diğer hassas değerler yalnızca `.env` içinde tutulur; kodda sabit string yok.
- Şifreler `password_hash` / `password_verify` ile saklanır.
- Oturum tabanlı auth; admin için `role=admin` kontrolü yapılır.

## Render ile Deploy

Bu repo Docker tabanli Render Web Service olarak calisacak sekilde hazirlandi.

### Eklenen dosyalar

- `Dockerfile` - PHP 8.2 container, MongoDB extension ve production build
- `scripts/render-start.sh` - Render `PORT` degiskeni ile uygulamayi baslatir
- `render.yaml` - Render blueprint tanimi
- `.dockerignore` - image icine gereksiz veya hassas dosyalari almaz
- `.env.example` - production uyumlu ornek ortam degiskenleri

### Render environment variables

Render tarafinda su degiskenleri tanimlayin:

- `APP_ENV=production`
- `APP_URL=https://<render-servis-adiniz>.onrender.com`
- `SESSION_NAME=toyshop_session`
- `MONGODB_URI=<MongoDB Atlas connection string>`
- `MONGODB_DB=toyshop_app`

### Health check

- Uygulama `/healthz` endpoint'i uzerinden saglik kontrolu verir.
- Endpoint MongoDB Atlas baglantisini da dogrular.
- Render health check path olarak `/healthz` kullanabilir.

### Deploy adimlari

1. Repoyu Render'a baglayin.
2. `New +` -> `Web Service` secin.
3. Runtime olarak `Docker` kullanin.
4. Environment variables alanina yukaridaki degerleri girin.
5. Health Check Path olarak `/healthz` ayarlayin.
6. Deploy edin.

### Notlar

- Uygulama MongoDB Atlas kullanmaya devam eder; Render icinde ayri veritabani kurulmaz.
- `.env` dosyasi Render'a yuklenmez; production'da degiskenler Render panelinden gelir.
- Ilk demo veriler istenirse deploy sonrasi shell uzerinden `php scripts/seed.php` calistirilabilir.
